<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Deal::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'value' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'deal_stage_id' => ['required', 'exists:deal_stages,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'expected_close_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'products' => ['nullable', 'array'],
            'products.*.name' => ['required_with:products', 'string', 'max:150'],
            'products.*.quantity' => ['required_with:products', 'integer', 'min:1'],
            'products.*.unit_price' => ['required_with:products', 'numeric', 'min:0'],
        ];
    }
}
