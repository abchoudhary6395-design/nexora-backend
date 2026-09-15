<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['customer', 'company'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->search, function ($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$request->search}%"));
            });

        return $this->paginated($query->latest()->paginate($request->per_page ?? 15));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = Invoice::create([
            'invoice_number' => Invoice::nextInvoiceNumber(),
            'customer_id' => $request->customer_id,
            'company_id' => $request->company_id,
            'tax_rate' => $request->tax_rate ?? 0,
            'discount' => $request->discount ?? 0,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'status' => 'Pending',
            'created_by' => $request->user()->id,
        ]);

        foreach ($request->items as $item) {
            $invoice->items()->create($item); // InvoiceItem model events recalc the parent total
        }

        return $this->success($invoice->fresh(['customer', 'items']), 'Invoice created', 201);
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return $this->success($invoice->load(['customer', 'company', 'items', 'payments', 'creator']));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->update($request->validate([
            'status' => ['sometimes', 'in:Paid,Pending,Overdue,Cancelled,Refunded'],
            'due_date' => ['sometimes', 'date'],
            'notes' => ['nullable', 'string'],
        ]));

        return $this->success($invoice->fresh(), 'Invoice updated');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        $invoice->delete();

        return $this->success(null, 'Invoice deleted');
    }

    /** Renders the invoice through the same layout as the frontend's print view, as a PDF. */
    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice->load(['customer', 'items'])]);

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }
}
