<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Exports\InvoicesExport;
use App\Exports\LeadsExport;
use App\Models\Report;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use ApiResponse;

    /** The 8 report types the frontend catalog (Reports.jsx) lists. */
    private const TYPES = [
        'revenue' => 'Revenue Report',
        'sales' => 'Sales Report',
        'lead' => 'Lead Report',
        'customer' => 'Customer Report',
        'task' => 'Task Report',
        'project' => 'Project Report',
        'invoice' => 'Invoice Report',
        'employee' => 'Employee Report',
    ];

    public function catalog()
    {
        return $this->success(
            collect(self::TYPES)->map(fn ($name, $type) => ['type' => $type, 'name' => $name])->values()
        );
    }

    public function exportExcel(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $export = match ($type) {
            'lead' => new LeadsExport,
            'customer' => new CustomersExport,
            'invoice' => new InvoicesExport,
            default => abort(501, 'Excel export for this report type is not implemented yet'),
        };

        return Excel::download($export, self::TYPES[$type].'.xlsx');
    }

    public function saved(Request $request)
    {
        return $this->success(
            Report::where('created_by', $request->user()->id)
                ->orWhere('is_shared', true)
                ->latest()
                ->get()
        );
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:'.implode(',', array_keys(self::TYPES))],
            'filters' => ['nullable', 'array'],
            'is_shared' => ['nullable', 'boolean'],
        ]);

        $report = Report::create([...$data, 'created_by' => $request->user()->id]);

        return $this->success($report, 'Report saved', 201);
    }
}
