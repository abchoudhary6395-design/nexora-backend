<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('customers.view') || $user->hasAnyRole(['super-admin', 'admin', 'manager', 'sales-executive', 'support-executive']);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->viewAny($user) || $customer->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('customers.create') || $user->hasAnyRole(['super-admin', 'admin', 'manager', 'sales-executive']);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customers.update')
            || $user->hasAnyRole(['super-admin', 'admin', 'manager'])
            || $customer->owner_id === $user->id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customers.delete') || $user->hasAnyRole(['super-admin', 'admin']);
    }
}
