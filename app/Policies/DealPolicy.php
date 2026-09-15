<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('deals.view') || $user->hasAnyRole(['super-admin', 'admin', 'manager', 'sales-executive']);
    }

    public function view(User $user, Deal $deal): bool
    {
        return $this->viewAny($user) || $deal->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('deals.create') || $user->hasAnyRole(['super-admin', 'admin', 'manager', 'sales-executive']);
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->hasPermission('deals.update')
            || $user->hasAnyRole(['super-admin', 'admin', 'manager'])
            || $deal->owner_id === $user->id;
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $user->hasPermission('deals.delete') || $user->hasAnyRole(['super-admin', 'admin']);
    }
}
