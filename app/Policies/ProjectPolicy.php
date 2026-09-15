<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'manager'])
            || $project->owner_id === $user->id
            || $project->members->contains('id', $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'manager']) || $project->owner_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }
}
