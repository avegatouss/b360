<?php

namespace Modules\Eshop360\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Message;
use Modules\Eshop360\Models\SupportTeam;

class MessageController extends Controller
{
    public function inbox()
    {
        $user = auth()->user();
        $instance = CurrentInstance::get();
        $isClient = $this->isClientUser($user, $instance);

        // Clients see messages from their team or admin
        // Staff see all messages sent to them
        $query = Message::where('to_user_id', $user->id)->with('sender');

        if ($isClient) {
            // Also show messages sent to any team member that are in the customer's team context
            $customer = $this->getCustomer($user, $instance);
            if ($customer?->support_team_id) {
                $teamMemberIds = SupportTeam::find($customer->support_team_id)?->getMemberIds() ?? [];
                // Client sees: messages TO them + messages FROM team members TO them
                // This is already covered by to_user_id filter
            }
        }

        $messages = $query->latest()->paginate(20);
        $unreadCount = Message::where('to_user_id', $user->id)->unread()->count();
        $recipients = $this->getRecipients($user, $instance);

        return view('eshop360::communication.inbox', compact('messages', 'unreadCount', 'recipients', 'isClient'));
    }

    public function sent()
    {
        $user = auth()->user();
        $instance = CurrentInstance::get();
        $isClient = $this->isClientUser($user, $instance);

        $messages = Message::where('from_user_id', $user->id)
            ->with('receiver')
            ->latest()
            ->paginate(20);

        $recipients = $this->getRecipients($user, $instance);

        return view('eshop360::communication.sent', compact('messages', 'recipients', 'isClient'));
    }

    public function show(Message $message)
    {
        $user = auth()->user();

        // Only sender or receiver can view
        abort_unless(
            (int) $message->from_user_id === (int) $user->id || (int) $message->to_user_id === (int) $user->id,
            403
        );

        if ((int) $message->to_user_id === (int) $user->id && ! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        $isClient = $this->isClientUser($user, CurrentInstance::get());

        return view('eshop360::communication.show', compact('message', 'isClient'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $instance = CurrentInstance::get();
        $isClient = $this->isClientUser($user, $instance);

        $validated = $request->validate([
            'to_user_id' => 'nullable|exists:users,id',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
        ]);

        $validated['instance_id'] = $instance->id;
        $validated['from_user_id'] = $user->id;

        if ($isClient) {
            // Client: route to assigned team or default team or admin
            $customer = $this->getCustomer($user, $instance);
            $team = null;

            if ($customer?->support_team_id) {
                $team = SupportTeam::find($customer->support_team_id);
            }

            if (! $team) {
                $team = SupportTeam::getDefault($instance->id);
            }

            if ($team) {
                $recipientId = $team->getRecipientUserId();
                $validated['to_user_id'] = $recipientId ?? $this->getAdminUserId($instance);
                $validated['support_team_id'] = $team->id;
            } else {
                // Fallback: send to instance admin
                $validated['to_user_id'] = $this->getAdminUserId($instance);
            }
        }

        // Staff must select a recipient
        if (empty($validated['to_user_id'])) {
            return redirect()->back()->with('error', __('Veuillez selectionner un destinataire.'));
        }

        Message::create($validated);

        return redirect()->back()->with('success', __('Message envoye.'));
    }

    public function destroy(Message $message)
    {
        $user = auth()->user();
        abort_unless(
            (int) $message->from_user_id === (int) $user->id || (int) $message->to_user_id === (int) $user->id,
            403
        );

        $message->delete();

        return redirect()->back()->with('success', __('Message supprime.'));
    }

    /**
     * Get available recipients based on user role.
     */
    private function getRecipients($user, $instance): array
    {
        $isClient = $this->isClientUser($user, $instance);

        if ($isClient) {
            // Client sees only their team name (not individual users)
            $customer = $this->getCustomer($user, $instance);
            $team = null;

            if ($customer?->support_team_id) {
                $team = SupportTeam::find($customer->support_team_id);
            }
            if (! $team) {
                $team = SupportTeam::getDefault($instance->id);
            }

            if ($team) {
                return [['id' => 'team', 'name' => $team->name, 'is_team' => true]];
            }

            // Fallback: "Support" label pointing to admin
            return [['id' => 'admin', 'name' => __('Support'), 'is_team' => true]];
        }

        // Staff: list all instance users
        $userIds = DB::connection('system')->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->where('user_id', '!=', $user->id)
            ->pluck('user_id');

        return User::whereIn('id', $userIds)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->full_name . ' (' . $u->email . ')', 'is_team' => false])
            ->toArray();
    }

    private function isClientUser($user, $instance): bool
    {
        if (! $user || ! $instance) {
            return false;
        }

        return $user->hasRole('user') && ! $user->hasRole('instance-admin') && ! $user->hasRole('super-admin');
    }

    private function getCustomer($user, $instance): ?Customer
    {
        return Customer::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('email', $user->email))
            ->first();
    }

    private function getAdminUserId($instance): ?int
    {
        $adminId = DB::connection('system')->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->pluck('user_id');

        return User::whereIn('id', $adminId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'instance-admin'))
            ->value('id')
            ?? User::whereIn('id', $adminId)->where('is_active', true)->value('id');
    }
}
