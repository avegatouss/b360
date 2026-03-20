<?php

namespace Modules\Eshop360\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\Message;
use Modules\Core\Support\CurrentInstance;

class MessageController extends Controller
{
    public function inbox()
    {
        $messages = Message::where('to_user_id', auth()->id())
            ->with('sender')
            ->latest()
            ->paginate(20);
        $users = $this->getRecipients();
        $unreadCount = Message::where('to_user_id', auth()->id())->whereNull('read_at')->count();
        return view('eshop360::communication.inbox', compact('messages', 'users', 'unreadCount'));
    }

    public function sent()
    {
        $messages = Message::where('from_user_id', auth()->id())
            ->with('receiver')
            ->latest()
            ->paginate(20);
        $users = $this->getRecipients();
        return view('eshop360::communication.sent', compact('messages', 'users'));
    }

    private function getRecipients()
    {
        $instance = CurrentInstance::get();
        $userIds = DB::connection('system')->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->where('user_id', '!=', auth()->id())
            ->pluck('user_id');

        return User::whereIn('id', $userIds)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email']);
    }

    public function show(Message $message)
    {
        if ($message->to_user_id === auth()->id() && !$message->read_at) {
            $message->update(['read_at' => now()]);
        }
        return view('eshop360::communication.show', compact('message'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        $validated['from_user_id'] = auth()->id();
        Message::create($validated);
        return redirect()->back()->with('success', 'Message sent.');
    }

    public function destroy(Message $message)
    {
        $message->delete();
        return redirect()->back()->with('success', 'Message deleted.');
    }
}
