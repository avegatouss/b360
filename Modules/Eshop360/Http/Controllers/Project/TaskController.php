<?php

namespace Modules\Eshop360\Http\Controllers\Project;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Projects\Models\Project;
use Modules\Eshop360\Domain\Projects\Models\Task;
use Modules\Eshop360\Domain\Projects\Models\TaskComment;

class TaskController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,review,done,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'parent_task_id' => 'nullable|exists:eshop_tasks,id',
        ]);

        $instance = CurrentInstance::get();

        $task = Task::create([
            ...$validated,
            'instance_id' => $instance->id,
            'project_id' => $project->id,
            'created_by' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'task' => $task]);
        }

        return redirect()->route('eshop360.projects.show', ['project' => $project])
            ->with('success', "Tâche \"{$task->title}\" ajoutée.");
    }

    public function update(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:todo,in_progress,review,done,cancelled',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'actual_hours' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'done' && $task->status !== 'done') {
            $validated['completed_at'] = now();
        }

        $task->update($validated);
        $task->project->updateProgress();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'task' => $task->fresh()]);
        }

        return redirect()->route('eshop360.projects.show', ['project' => $task->project_id])
            ->with('success', 'Tâche mise à jour.');
    }

    public function destroy(string $slug, Task $task): JsonResponse|RedirectResponse
    {
        $projectId = $task->project_id;
        $task->subTasks()->delete();
        $task->comments()->delete();
        $task->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('eshop360.projects.show', ['project' => $projectId])
            ->with('success', 'Tâche supprimée.');
    }

    public function addComment(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(['content' => 'required|string|max:2000']);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        $comment->load('task');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'comment' => $comment]);
        }

        return redirect()->back()->with('success', 'Commentaire ajouté.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:eshop_tasks,id',
            'tasks.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            Task::where('id', $taskData['id'])->update(['sort_order' => $taskData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
