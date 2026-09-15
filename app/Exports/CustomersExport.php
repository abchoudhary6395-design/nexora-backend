<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Customer::with(['company', 'owner'])->get();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Email', 'Company', 'Industry', 'Lifetime Value', 'Owner', 'Last Activity'];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->name,
            $customer->email,
            $customer->company?->name,
            $customer->industry,
            $customer->lifetime_value,
            $customer->owner?->name,
            $customer->last_activity_at?->format('Y-m-d'),
        ];
    }
}
