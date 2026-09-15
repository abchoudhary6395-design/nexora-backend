<?php

namespace Database\Seeders;

use App\Models\DealStage;
use Illuminate\Database\Seeder;

class DealStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['key' => 'lead', 'label' => 'Lead', 'color' => '#9BA1B5', 'default_probability' => 10, 'sort_order' => 1],
            ['key' => 'qualified', 'label' => 'Qualified', 'color' => '#4F5EFF', 'default_probability' => 25, 'sort_order' => 2],
            ['key' => 'proposal', 'label' => 'Proposal', 'color' => '#F5A524', 'default_probability' => 50, 'sort_order' => 3],
            ['key' => 'negotiation', 'label' => 'Negotiation', 'color' => '#D6890F', 'default_probability' => 75, 'sort_order' => 4],
            ['key' => 'won', 'label' => 'Won', 'color' => '#1FAE6B', 'default_probability' => 100, 'sort_order' => 5, 'is_won' => true],
            ['key' => 'lost', 'label' => 'Lost', 'color' => '#E5484D', 'default_probability' => 0, 'sort_order' => 6, 'is_lost' => true],
        ];

        foreach ($stages as $stage) {
            DealStage::firstOrCreate(['key' => $stage['key']], $stage);
        }
    }
}
