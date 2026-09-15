<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if demo data already exists (keeps seeding idempotent).
        if (Company::count() > 0) {
            return;
        }

        $admin = User::first();

        $companyNames = [
            'Northwind Traders', 'Kappa Industries', 'Bluepeak Systems', 'Vertex Retail',
            'Solace Media', 'Ironclad Logistics', 'Harbor & Co', 'Nimbus Cloud',
        ];

        $companies = collect($companyNames)->map(fn ($name) => Company::create([
            'name' => $name,
            'industry' => collect(['SaaS', 'Retail', 'Logistics', 'Healthcare', 'Media'])->random(),
            'country' => collect(['United States', 'United Kingdom', 'India', 'Germany'])->random(),
            'employees' => rand(20, 900),
            'annual_revenue' => rand(200000, 8000000),
            'owner_id' => $admin?->id,
        ]));

        $customers = $companies->map(fn (Company $company) => Customer::create([
            'name' => $company->name.' — Primary Contact',
            'email' => strtolower(str_replace(' ', '', $company->name)).'@example.com',
            'company_id' => $company->id,
            'industry' => $company->industry,
            'lifetime_value' => rand(5000, 180000),
            'owner_id' => $admin?->id,
            'last_activity_at' => now()->subDays(rand(0, 20)),
        ]));

        $statuses = LeadStatus::all();
        $sources = LeadSource::all();

        collect(range(1, 20))->each(function ($i) use ($statuses, $sources, $admin) {
            Lead::create([
                'name' => 'Lead Prospect '.$i,
                'company_name' => 'Prospect Co '.$i,
                'email' => "prospect{$i}@example.com",
                'lead_source_id' => $sources->random()->id,
                'lead_status_id' => $statuses->random()->id,
                'priority' => collect(['Low', 'Medium', 'High', 'Urgent'])->random(),
                'score' => rand(0, 100),
                'assigned_to' => $admin?->id,
                'expected_revenue' => rand(2000, 50000),
            ]);
        });

        $stages = DealStage::all();
        $customers->each(function (Customer $customer) use ($stages, $admin) {
            Deal::create([
                'title' => $customer->name.' — Enterprise Plan',
                'customer_id' => $customer->id,
                'company_id' => $customer->company_id,
                'value' => rand(3000, 85000),
                'probability' => $stages->random()->default_probability,
                'deal_stage_id' => $stages->random()->id,
                'owner_id' => $admin?->id,
                'expected_close_date' => now()->addDays(rand(5, 60)),
            ]);
        });
    }
}
