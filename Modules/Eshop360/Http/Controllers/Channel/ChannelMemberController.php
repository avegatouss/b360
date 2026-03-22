<?php

namespace Modules\Eshop360\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Http\Middleware\ChannelRole;
use Modules\Eshop360\Models\ChannelUser;
use Modules\Eshop360\Models\DistributionChannel;

class ChannelMemberController extends Controller
{
    public function index(string $slug, DistributionChannel $channel)
    {
        $members = ChannelUser::where('channel_id', $channel->id)
            ->with('user')
            ->orderByRaw("FIELD(role, 'admin', 'manager', 'operator', 'cashier', 'viewer', 'client')")
            ->get();

        $instance = CurrentInstance::get();

        // Available users not yet assigned to this channel
        $assignedUserIds = $members->pluck('user_id');
        $availableUserIds = DB::connection('system')->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->whereNotIn('user_id', $assignedUserIds)
            ->pluck('user_id');

        $availableUsers = User::whereIn('id', $availableUserIds)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email']);

        $roles = ChannelRole::HIERARCHY;
        $roleLabels = ChannelRole::LABELS;

        return view('eshop360::channels.members', compact('channel', 'members', 'availableUsers', 'roles', 'roleLabels'));
    }

    public function store(Request $request, string $slug, DistributionChannel $channel)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|in:' . implode(',', ChannelRole::all()),
        ]);

        // Check not already assigned
        if (ChannelUser::where('channel_id', $channel->id)->where('user_id', $validated['user_id'])->exists()) {
            return redirect()->back()->with('error', __('Cet utilisateur est deja membre de ce canal.'));
        }

        ChannelUser::create([
            'channel_id' => $channel->id,
            'user_id'    => $validated['user_id'],
            'role'       => $validated['role'],
        ]);

        return redirect()->back()->with('success', __('Membre ajoute au canal.'));
    }

    public function update(Request $request, string $slug, DistributionChannel $channel, ChannelUser $member)
    {
        $validated = $request->validate([
            'role' => 'required|in:' . implode(',', ChannelRole::all()),
        ]);

        $member->update(['role' => $validated['role']]);

        return redirect()->back()->with('success', __('Role mis a jour.'));
    }

    public function destroy(string $slug, DistributionChannel $channel, ChannelUser $member)
    {
        $member->delete();
        return redirect()->back()->with('success', __('Membre retire du canal.'));
    }
}
