<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // every authenticated user can see their own task board
    }

    public function view(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'manager'])
            || $task->assigned_to === $user->id
            || $task->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'manager'])
            || $task->assigned_to === $user->id
            || $task->created_by === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'manager']) || $task->created_by === $user->id;
    }
}
