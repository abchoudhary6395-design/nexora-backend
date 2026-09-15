<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['invoice_id', 'amount', 'method', 'reference', 'paid_at', 'status'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'date'];
    }

    protected static function booted(): void
    {
        // Marking a payment as Paid that covers the full invoice total
        // flips the parent invoice's status to Paid automatically.
        static::saved(function (Payment $payment) {
            $invoice = $payment->invoice;
            if (! $invoice) return;

            $totalPaid = $invoice->payments()->where('status', 'Paid')->sum('amount');
            if ($totalPaid >= $invoice->total && $invoice->status !== 'Paid') {
                $invoice->update(['status' => 'Paid']);
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
