<?php

namespace Modules\Eshop360\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Communication\Models\Message;
use Modules\Eshop360\Domain\Communication\Models\SupportTeam;
use Modules\Eshop360\Domain\CRM\Models\Customer;

class MessageController extends Controller
{
    public function inbox(Request $request)
    {
        $user = auth()->user();
        $instance = CurrentInstance::get();
        $isClient = $this->isClientUser($user, $instance);

        $query = Message::where('to_user_id', $user->id)
            ->with('sender')
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('subject', 'like', "%{$s}%")->orWhere('body', 'like', "%{$s}%");
            }))
            ->when($request->unread === '1', fn ($q) => $q->whereNull('read_at'))
            ->when($request->from_user_id, fn ($q, $f) => $q->where('from_user_id', $f));

        $totalMessages = Message::where('to_user_id', $user->id)->count();
        $unreadCount = Message::where('to_user_id', $user->id)->unread()->count();
        $sentCount = Message::where('from_user_id', $user->id)->count();

        $conversations = Message::where(function ($q) use ($user) {
            $q->where('to_user_id', $user->id)->orWhere('from_user_id', $user->id);
        })
            ->selectRaw('
                CASE WHEN from_user_id = ? THEN to_user_id ELSE from_user_id END as correspondent_id,
                MAX(created_at) as last_message_at,
                COUNT(*) as message_count,
                SUM(CASE WHEN to_user_id = ? AND read_at IS NULL THEN 1 ELSE 0 END) as unread_count
            ', [$user->id, $user->id])
            ->groupByRaw('CASE WHEN from_user_id = ? THEN to_user_id ELSE from_user_id END', [$user->id])
            ->orderByDesc('last_message_at')
            ->limit(10)
            ->get();

        $correspondents = User::whereIn('id', $conversations->pluck('correspondent_id'))->get()->keyBy('id');
        $messages = $query->latest()->paginate(20)->withQueryString();
        $recipients = $this->getRecipients($user, $instance);

        return view('eshop360::communication.inbox', compact(
            'messages', 'unreadCount', 'totalMessages', 'sentCount',
            'recipients', 'isClient', 'conversations', 'correspondents'
        ));
    }

    public function sent(Request $request)
    {
        $user = auth()->user();
        $instance = CurrentInstance::get();
        $isClient = $this->isClientUser($user, $instance);
        $messages = Message::where('from_user_id', $user->id)->with('receiver')
            ->when($request->search, fn ($q, $s) => $q->where('subject', 'like', "%{$s}%"))
            ->latest()->paginate(20)->withQueryString();
        $recipients = $this->getRecipients($user, $instance);

        return view('eshop360::communication.sent', compact('messages', 'recipients', 'isClient'));
    }

    public function show(Message $message)
    {
        $user = auth()->user();
        abort_unless((int) $message->from_user_id === (int) $user->id || (int) $message->to_user_id === (int) $user->id, 403);

        if ((int) $message->to_user_id === (int) $user->id && ! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        $instance = CurrentInstance::get();
        $isClient = $this->isClientUser($user, $instance);
        $correspondentId = (int) $message->from_user_id === (int) $user->id ? $message->to_user_id : $message->from_user_id;

        $thread = Message::where(function ($q) use ($user, $correspondentId) {
            $q->where(function ($qq) use ($user, $correspondentId) {
                $qq->where('from_user_id', $user->id)->where('to_user_id', $correspondentId);
            })->orWhere(function ($qq) use ($user, $correspondentId) {
                $qq->where('from_user_id', $correspondentId)->where('to_user_id', $user->id);
            });
        })->with('sender', 'receiver')->orderBy('created_at')->limit(50)->get();

        Message::where('from_user_id', $correspondentId)->where('to_user_id', $user->id)
            ->whereNull('read_at')->update(['read_at' => now()]);

        $correspondent = User::find($correspondentId);

        return view('eshop360::communication.show', compact('message', 'isClient', 'thread', 'correspondent'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $instance = CurrentInstance::get();
        $isClient = $this->isClientUser($user, $instance);
        $validated = $request->validate([
            'to_user_id' => 'nullable|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        $validated['instance_id'] = $instance->id;
        $validated['from_user_id'] = $user->id;

        if ($isClient) {
            $customer = $this->getCustomer($user, $instance);
            $team = $customer?->support_team_id ? SupportTeam::find($customer->support_team_id) : null;
            if (! $team) {
                $team = SupportTeam::getDefault($instance->id);
            }
            if ($team) {
                $validated['to_user_id'] = $team->getRecipientUserId() ?? $this->getAdminUserId($instance);
                $validated['support_team_id'] = $team->id;
            } else {
                $validated['to_user_id'] = $this->getAdminUserId($instance);
            }
        }

        if (empty($validated['to_user_id'])) {
            return redirect()->back()->with('error', __('Veuillez selectionner un destinataire.'));
        }

        Message::create($validated);

        return redirect()->back()->with('success', __('Message envoye.'));
    }

    public function destroy(Message $message)
    {
        $user = auth()->user();
        abort_unless((int) $message->from_user_id === (int) $user->id || (int) $message->to_user_id === (int) $user->id, 403);
        $message->delete();

        return redirect()->back()->with('success', __('Message supprime.'));
    }

    private function getRecipients($user, $instance): array
    {
        if ($this->isClientUser($user, $instance)) {
            $customer = $this->getCustomer($user, $instance);
            $team = $customer?->support_team_id ? SupportTeam::find($customer->support_team_id) : null;
            if (! $team) {
                $team = SupportTeam::getDefault($instance->id);
            }

            return $team ? [['id' => 'team', 'name' => $team->name, 'is_team' => true]] : [['id' => 'admin', 'name' => __('Support'), 'is_team' => true]];
        }
        $userIds = DB::connection('system')->table('instance_user')
            ->where('instance_id', $instance->id)->where('status', 'active')
            ->where('user_id', '!=', $user->id)->pluck('user_id');

        return User::whereIn('id', $userIds)->where('is_active', true)->orderBy('full_name')
            ->get(['id', 'full_name', 'email'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->full_name.' ('.$u->email.')', 'is_team' => false])
            ->toArray();
    }

    private function isClientUser($user, $instance): bool
    {
        return $user && $instance && $user->hasRole('user') && ! $user->hasRole('instance-admin') && ! $user->hasRole('super-admin');
    }

    private function getCustomer($user, $instance): ?Customer
    {
        return Customer::withoutGlobalScopes()->where('instance_id', $instance->id)
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('email', $user->email))->first();
    }

    private function getAdminUserId($instance): ?int
    {
        $ids = DB::connection('system')->table('instance_user')->where('instance_id', $instance->id)->where('status', 'active')->pluck('user_id');

        return User::whereIn('id', $ids)->where('is_active', true)->whereHas('roles', fn ($q) => $q->where('name', 'instance-admin'))->value('id')
            ?? User::whereIn('id', $ids)->where('is_active', true)->value('id');
    }
}
