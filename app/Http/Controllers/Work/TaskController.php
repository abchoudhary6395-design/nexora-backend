<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Http\Requests\Work\StoreTaskRequest;
use App\Http\Requests\Work\UpdateTaskRequest;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    /**
     * Supports both List view and Board view — see the frontend's
     * src/pages/Tasks/Tasks.jsx ViewToggle. Board view groups by status.
     */
    public function index(Request $request)
    {
        $query = Task::with(['assignee', 'creator', 'project'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_to, fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->project_id, fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"));

        if ($request->boolean('grouped')) {
            $statuses = ['todo', 'in_progress', 'review', 'done'];
            $tasks = $query->get();

            return $this->success(
                collect($statuses)->map(fn ($status) => [
                    'status' => $status,
                    'tasks' => $tasks->where('status', $status)->values(),
                ])
            );
        }

        return $this->paginated($query->latest()->paginate($request->per_page ?? 15));
    }

    public function store(StoreTaskRequest $request)
    {
        $task = Task::create([...$request->safe()->except('checklist'), 'created_by' => $request->user()->id]);

        foreach ($request->input('checklist', []) as $i => $label) {
            $task->checklistItems()->create(['label' => $label, 'sort_order' => $i]);
        }

        return $this->success($task->load(['assignee', 'checklistItems']), 'Task created', 201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return $this->success($task->load(['assignee', 'creator', 'project', 'checklistItems', 'comments.user']));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        if ($task->status === 'done' && ! $task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        $task->project?->recalculateProgress();

        return $this->success($task->fresh(['assignee', 'project']), 'Task updated');
    }

    /** Called on Kanban drag-and-drop — see components/common/Kanban/TaskBoard.jsx */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        $request->validate(['status' => 'required|in:todo,in_progress,review,done']);

        $task->update([
            'status' => $request->status,
            'completed_at' => $request->status === 'done' ? now() : null,
        ]);

        $task->project?->recalculateProgress();

        return $this->success($task->fresh(), 'Task status updated');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();

        return $this->success(null, 'Task deleted');
    }
}
