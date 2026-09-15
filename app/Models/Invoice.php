<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'customer_id', 'company_id', 'subtotal', 'discount',
        'tax_rate', 'tax_amount', 'total', 'status', 'issue_date', 'due_date',
        'payment_method', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Recalculates subtotal/tax/total from line items.
     * Call after items are created/updated/deleted.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total');
        $taxAmount = round($subtotal * ($this->tax_rate / 100), 2);

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $subtotal - $this->discount + $taxAmount,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'Pending' && $this->due_date->isPast();
    }

    /** Generates the next sequential invoice number, e.g. INV-2042. */
    public static function nextInvoiceNumber(): string
    {
        $last = self::query()->latest('id')->first();
        $nextSeq = $last ? ((int) substr($last->invoice_number, 4)) + 1 : 2040;

        return 'INV-'.$nextSeq;
    }
}
