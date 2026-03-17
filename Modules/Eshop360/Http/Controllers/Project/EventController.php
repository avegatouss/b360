<?php

namespace Modules\Eshop360\Http\Controllers\Project;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Event;

class EventController extends Controller
{
    /**
     * Calendar view page.
     */
    public function index()
    {
        return view('eshop360::projects.events-calendar');
    }

    /**
     * JSON API for FullCalendar — filter by date range.
     */
    public function events(Request $request): JsonResponse
    {
        $instance = CurrentInstance::get();

        $events = Event::where('instance_id', $instance->id)
            ->when($request->start, fn ($q, $s) => $q->where('start_at', '>=', $s))
            ->when($request->end, fn ($q, $e) => $q->where('start_at', '<=', $e))
            ->get()
            ->map(fn (Event $event) => [
                'id'          => $event->id,
                'title'       => $event->title,
                'start'       => $event->start_at->toIso8601String(),
                'end'         => $event->end_at?->toIso8601String(),
                'allDay'      => $event->all_day,
                'color'       => $event->color ?? '#3b82f6',
                'description' => $event->description,
            ]);

        return response()->json($events);
    }

    /**
     * Store a new event (AJAX).
     */
    public function store(Request $request): JsonResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_at'    => 'required|date',
            'end_at'      => 'nullable|date|after_or_equal:start_at',
            'all_day'     => 'nullable|boolean',
            'color'       => 'nullable|string|max:7',
        ]);

        $event = Event::create([
            ...$validated,
            'instance_id' => $instance->id,
            'user_id'     => auth()->id(),
            'all_day'     => $request->boolean('all_day'),
        ]);

        return response()->json([
            'id'     => $event->id,
            'title'  => $event->title,
            'start'  => $event->start_at->toIso8601String(),
            'end'    => $event->end_at?->toIso8601String(),
            'allDay' => $event->all_day,
            'color'  => $event->color ?? '#3b82f6',
        ], 201);
    }

    /**
     * Update an event (AJAX — supports drag & drop).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_at'    => 'sometimes|required|date',
            'end_at'      => 'nullable|date',
            'all_day'     => 'nullable|boolean',
            'color'       => 'nullable|string|max:7',
        ]);

        if ($request->has('all_day')) {
            $validated['all_day'] = $request->boolean('all_day');
        }

        $event->update($validated);

        return response()->json(['success' => true]);
    }

    /**
     * Delete an event (AJAX).
     */
    public function destroy($id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(['success' => true]);
    }
}
