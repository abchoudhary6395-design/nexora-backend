<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    use ApiResponse;

    /** Matches src/pages/Analytics/Analytics.jsx — one payload for every chart on the page. */
    public function index()
    {
        return $this->success([
            'lead_sources' => $this->leadSources(),
            'funnel' => $this->conversionFunnel(),
            'top_salespeople' => $this->topSalespeople(),
            'monthly_comparison' => $this->monthlyComparison(),
        ]);
    }

    /** Matches src/components/Analytics/MonthlyComparisonChart.jsx */
    private function monthlyComparison(int $months = 7): array
    {
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $thisMonth = \Illuminate\Support\Carbon::now()->subMonths($i);
            $lastYearMonth = $thisMonth->copy()->subYear();

            $result[] = [
                'month' => $thisMonth->format('M'),
                'thisYear' => (float) Invoice::where('status', 'Paid')
                    ->whereYear('issue_date', $thisMonth->year)
                    ->whereMonth('issue_date', $thisMonth->month)
                    ->sum('total'),
                'lastYear' => (float) Invoice::where('status', 'Paid')
                    ->whereYear('issue_date', $lastYearMonth->year)
                    ->whereMonth('issue_date', $lastYearMonth->month)
                    ->sum('total'),
            ];
        }
        return $result;
    }

    private function leadSources(): array
    {
        return LeadSource::withCount('leads')
            ->having('leads_count', '>', 0)
            ->get()
            ->map(fn ($s) => ['name' => $s->name, 'value' => $s->leads_count])
            ->values()
            ->all();
    }

    private function conversionFunnel(): array
    {
        return [
            ['stage' => 'Leads', 'count' => Lead::count()],
            ['stage' => 'Qualified', 'count' => Lead::whereHas('status', fn ($q) => $q->where('name', 'Qualified'))->count()],
            ['stage' => 'Proposal', 'count' => Deal::whereHas('stage', fn ($q) => $q->where('key', 'proposal'))->count()],
            ['stage' => 'Negotiation', 'count' => Deal::whereHas('stage', fn ($q) => $q->where('key', 'negotiation'))->count()],
            ['stage' => 'Won', 'count' => Deal::whereHas('stage', fn ($q) => $q->where('is_won', true))->count()],
        ];
    }

    private function topSalespeople(int $limit = 5): array
    {
        return Deal::join('deal_stages', 'deals.deal_stage_id', '=', 'deal_stages.id')
            ->join('users', 'deals.owner_id', '=', 'users.id')
            ->where('deal_stages.is_won', true)
            ->select('users.name', DB::raw('SUM(deals.value) as revenue'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'revenue' => (float) $row->revenue])
            ->all();
    }
}
