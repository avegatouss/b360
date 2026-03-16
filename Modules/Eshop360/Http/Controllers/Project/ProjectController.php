<?php

namespace Modules\Eshop360\Http\Controllers\Project;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Project;
use Modules\Eshop360\Models\Task;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $projects = Project::where('instance_id', $instance->id)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn($q, $p) => $q->where('priority', $p))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks' => fn($q) => $q->where('status', 'done')])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Project::where('instance_id', $instance->id)->count(),
            'active' => Project::where('instance_id', $instance->id)->where('status', 'active')->count(),
            'completed' => Project::where('instance_id', $instance->id)->where('status', 'completed')->count(),
            'overdue' => Project::where('instance_id', $instance->id)->overdue()->count(),
        ];

        return view('eshop360::projects.index', compact('projects', 'stats'));
    }

    public function create()
    {
        $instance = CurrentInstance::get();
        $customers = Customer::where('instance_id', $instance->id)->orderBy('name')->get();

        return view('eshop360::projects.create', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,on_hold,completed,cancelled',
            'priority'    => 'required|in:low,medium,high,urgent',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'budget'      => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:eshop_customers,id',
        ]);

        $instance = CurrentInstance::get();

        $project = Project::create([
            ...$validated,
            'instance_id' => $instance->id,
            'created_by' => auth()->id(),
            'manager_id' => auth()->id(),
        ]);

        return redirect()->route('eshop360.projects.show', ['project' => $project])
            ->with('success', "Projet \"{$project->name}\" créé.");
    }

    public function show(Project $project)
    {
        $project->load(['tasks' => fn($q) => $q->whereNull('parent_task_id')->orderBy('sort_order'), 'customer']);
        $project->loadCount(['tasks', 'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'done')]);

        $tasksByStatus = [
            'todo'        => $project->tasks->where('status', 'todo'),
            'in_progress' => $project->tasks->where('status', 'in_progress'),
            'review'      => $project->tasks->where('status', 'review'),
            'done'        => $project->tasks->where('status', 'done'),
        ];

        return view('eshop360::projects.show', compact('project', 'tasksByStatus'));
    }

    public function edit(Project $project)
    {
        $instance = CurrentInstance::get();
        $customers = Customer::where('instance_id', $instance->id)->orderBy('name')->get();

        return view('eshop360::projects.edit', compact('project', 'customers'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,on_hold,completed,cancelled',
            'priority'    => 'required|in:low,medium,high,urgent',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'budget'      => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:eshop_customers,id',
            'progress'    => 'nullable|integer|min:0|max:100',
        ]);

        $project->update($validated);

        return redirect()->route('eshop360.projects.show', ['project' => $project])
            ->with('success', 'Projet mis à jour.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->tasks()->each(fn($t) => $t->comments()->delete());
        $project->tasks()->delete();
        $project->delete();

        return redirect()->route('eshop360.projects.index')
            ->with('success', "Projet supprimé.");
    }

    /**
     * Calendar view of tasks for this project.
     */
    public function calendar(Project $project)
    {
        $tasks = $project->tasks()
            ->whereNotNull('due_date')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'start' => $t->start_date?->toIso8601String() ?? $t->due_date->toIso8601String(),
                'end' => $t->due_date->toIso8601String(),
                'color' => match ($t->priority) {
                    'urgent' => '#ef4444',
                    'high' => '#f97316',
                    'medium' => '#3b82f6',
                    default => '#6b7280',
                },
                'className' => 'status-' . $t->status,
            ]);

        return view('eshop360::projects.calendar', compact('project', 'tasks'));
    }
}
