<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('invoices.view') || $user->hasAnyRole(['super-admin', 'admin', 'manager', 'accountant', 'sales-executive']);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('invoices.create') || $user->hasAnyRole(['super-admin', 'admin', 'accountant']);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoices.update') || $user->hasAnyRole(['super-admin', 'admin', 'accountant']);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /** Separate ability for the "Approve Invoices" permission called out in Part 5. */
    public function approve(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoices.approve') || $user->hasAnyRole(['super-admin', 'admin', 'accountant']);
    }
}
