<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Policies\CalendarEventPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DealPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LeadPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Explicit model -> policy map. Laravel can auto-discover these by
     * naming convention, but listing them keeps the RBAC surface obvious
     * to any new developer reading this file.
     */
    protected $policies = [
        Lead::class => LeadPolicy::class,
        Customer::class => CustomerPolicy::class,
        Company::class => CompanyPolicy::class,
        Deal::class => DealPolicy::class,
        Task::class => TaskPolicy::class,
        Project::class => ProjectPolicy::class,
        Invoice::class => InvoicePolicy::class,
        CalendarEvent::class => CalendarEventPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
