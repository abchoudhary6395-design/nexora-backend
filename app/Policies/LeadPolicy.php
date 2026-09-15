<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('leads.view') || $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->viewAny($user) || $lead->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('leads.create') || $user->hasAnyRole(['super-admin', 'admin', 'manager', 'sales-executive']);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->hasPermission('leads.update')
            || $user->hasAnyRole(['super-admin', 'admin', 'manager'])
            || $lead->assigned_to === $user->id;
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->hasPermission('leads.delete') || $user->hasAnyRole(['super-admin', 'admin']);
    }
}
