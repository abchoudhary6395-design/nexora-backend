<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    use ApiResponse;

    /** Matches the KPI cards on src/pages/Dashboard/Dashboard.jsx */
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $revenueThisMonth = Invoice::where('status', 'Paid')->where('issue_date', '>=', $startOfMonth)->sum('total');
        $revenueLastMonth = Invoice::where('status', 'Paid')->whereBetween('issue_date', [$startOfLastMonth, $endOfLastMonth])->sum('total');

        $activeCustomers = Customer::count();
        $openDeals = Deal::whereHas('stage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))->count();
        $tasksDueToday = Task::whereDate('due_date', today())->where('status', '!=', 'done')->count();

        return $this->success([
            'kpis' => [
                'revenue_mtd' => (float) $revenueThisMonth,
                'revenue_delta_pct' => $revenueLastMonth > 0
                    ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
                    : 0,
                'active_customers' => $activeCustomers,
                'open_deals' => $openDeals,
                'tasks_due_today' => $tasksDueToday,
            ],
            'revenue_by_month' => $this->revenueByMonth(),
        ]);
    }

    private function revenueByMonth(int $months = 7): array
    {
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $result[] = [
                'month' => $month->format('M'),
                'revenue' => (float) Invoice::where('status', 'Paid')
                    ->whereYear('issue_date', $month->year)
                    ->whereMonth('issue_date', $month->month)
                    ->sum('total'),
            ];
        }
        return $result;
    }
}
