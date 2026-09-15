<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('companies.view') || $user->hasAnyRole(['super-admin', 'admin', 'manager', 'sales-executive']);
    }

    public function view(User $user, Company $company): bool
    {
        return $this->viewAny($user) || $company->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('companies.create') || $user->hasAnyRole(['super-admin', 'admin', 'manager', 'sales-executive']);
    }

    public function update(User $user, Company $company): bool
    {
        return $user->hasPermission('companies.update')
            || $user->hasAnyRole(['super-admin', 'admin', 'manager'])
            || $company->owner_id === $user->id;
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->hasPermission('companies.delete') || $user->hasAnyRole(['super-admin', 'admin']);
    }
}
