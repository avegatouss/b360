<?php

namespace Modules\Eshop360\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        return view('eshop360::communication.inbox', compact('messages'));
    }

    public function sent()
    {
        $messages = Message::where('from_user_id', auth()->id())
            ->with('receiver')
            ->latest()
            ->paginate(20);
        return view('eshop360::communication.sent', compact('messages'));
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
