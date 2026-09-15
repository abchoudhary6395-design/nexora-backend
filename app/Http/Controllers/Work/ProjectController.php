<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Http\Requests\Work\StoreProjectRequest;
use App\Http\Requests\Work\UpdateProjectRequest;
use App\Models\Project;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Project::with(['customer', 'owner', 'members'])
            ->withCount('tasks')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"));

        return $this->paginated($query->latest()->paginate($request->per_page ?? 12));
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->safe()->except('member_ids'));

        if ($request->filled('member_ids')) {
            $project->members()->attach($request->member_ids);
        }

        return $this->success($project->load(['customer', 'owner', 'members']), 'Project created', 201);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return $this->success($project->load([
            'customer', 'owner', 'members', 'milestones', 'tasks.assignee',
            'notes.user', 'activities.user', 'attachments',
        ]));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return $this->success($project->fresh(['customer', 'owner', 'members']), 'Project updated');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();

        return $this->success(null, 'Project deleted');
    }
}
