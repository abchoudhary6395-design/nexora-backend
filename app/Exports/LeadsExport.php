<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeadsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Lead::with(['source', 'status', 'assignee'])->get();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Company', 'Email', 'Source', 'Status', 'Score', 'Expected Revenue', 'Owner', 'Created'];
    }

    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->name,
            $lead->company_name,
            $lead->email,
            $lead->source?->name,
            $lead->status?->name,
            $lead->score,
            $lead->expected_revenue,
            $lead->assignee?->name,
            $lead->created_at->format('Y-m-d'),
        ];
    }
}
