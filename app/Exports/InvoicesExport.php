<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Invoice::with('customer')->get();
    }

    public function headings(): array
    {
        return ['Invoice #', 'Customer', 'Issue Date', 'Due Date', 'Status', 'Subtotal', 'Tax', 'Total'];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->customer?->name,
            $invoice->issue_date->format('Y-m-d'),
            $invoice->due_date->format('Y-m-d'),
            $invoice->status,
            $invoice->subtotal,
            $invoice->tax_amount,
            $invoice->total,
        ];
    }
}
