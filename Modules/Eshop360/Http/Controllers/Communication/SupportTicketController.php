<?php

namespace Modules\Eshop360\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\SupportTicket;
use Modules\Eshop360\Models\TicketMessage;
use Modules\Core\Support\CurrentInstance;

class SupportTicketController extends Controller
{
    public function index()
    {
        $instance = CurrentInstance::get();
        $tickets = SupportTicket::where('instance_id', $instance->id)
            ->with('customer')
            ->latest()
            ->paginate(20);
        return view('eshop360::communication.tickets.index', compact('tickets'));
    }

    public function show(string $slug, SupportTicket $ticket)
    {
        $ticket->load('customer', 'messages.user');
        return view('eshop360::communication.tickets.show', compact('ticket'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:eshop_customers,id',
            'subject' => 'required|string|max:255',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        SupportTicket::create($validated);
        return redirect()->back()->with('success', 'Ticket created.');
    }

    public function reply(Request $request, string $slug, SupportTicket $ticket)
    {
        $request->merge([
            'message' => $request->input('message', $request->input('body')),
        ]);

        $validated = $request->validate(['message' => 'required|string']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);
        return redirect()->back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, string $slug, SupportTicket $ticket)
    {
        $validated = $request->validate(['status' => 'required|in:open,in_progress,resolved,closed']);
        $ticket->update($validated);
        return redirect()->back()->with('success', 'Ticket status updated.');
    }
}
