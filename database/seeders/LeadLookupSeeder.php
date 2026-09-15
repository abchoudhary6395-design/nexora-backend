<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use App\Models\LeadStatus;
use Illuminate\Database\Seeder;

class LeadLookupSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'New', 'color' => '#6E7490', 'sort_order' => 1],
            ['name' => 'Contacted', 'color' => '#4F5EFF', 'sort_order' => 2],
            ['name' => 'Qualified', 'color' => '#1FAE6B', 'sort_order' => 3],
            ['name' => 'Proposal Sent', 'color' => '#F5A524', 'sort_order' => 4],
            ['name' => 'Negotiation', 'color' => '#D6890F', 'sort_order' => 5],
            ['name' => 'Won', 'color' => '#1FAE6B', 'sort_order' => 6],
            ['name' => 'Lost', 'color' => '#E5484D', 'sort_order' => 7],
            ['name' => 'Archived', 'color' => '#9BA1B5', 'sort_order' => 8],
        ];

        foreach ($statuses as $status) {
            LeadStatus::firstOrCreate(['name' => $status['name']], $status);
        }

        $sources = [
            'Website', 'Landing Page', 'Facebook', 'Instagram', 'LinkedIn',
            'Google Ads', 'Referral', 'Walk-In', 'Cold Call', 'Email Campaign', 'Manual Entry',
        ];

        foreach ($sources as $source) {
            LeadSource::firstOrCreate(['name' => $source]);
        }
    }
}
