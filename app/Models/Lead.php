<?php

namespace App\Models;

use App\Traits\HasActivities;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes, HasNotes, HasActivities, HasAttachments, HasTags;

    protected $fillable = [
        'name', 'company_name', 'email', 'phone', 'lead_source_id', 'lead_status_id',
        'priority', 'score', 'assigned_to', 'expected_revenue', 'industry', 'website',
        'address', 'city', 'country', 'last_contacted_at', 'next_follow_up_at',
        'internal_notes', 'custom_fields',
    ];

    protected function casts(): array
    {
        return [
            'expected_revenue' => 'decimal:2',
            'last_contacted_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'converted_at' => 'datetime',
            'custom_fields' => 'array',
        ];
    }

    // ---------- Relationships ----------

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    // ---------- Scoring ----------

    /** Hot (>=75) / Warm (>=45) / Cold — matches the frontend's LeadScore component. */
    public function scoreTier(): string
    {
        return match (true) {
            $this->score >= 75 => 'hot',
            $this->score >= 45 => 'warm',
            default => 'cold',
        };
    }

    // ---------- Conversion ----------

    /**
     * Convert this lead into a real Customer record.
     * Called from LeadController::convert().
     */
    public function convertToCustomer(): Customer
    {
        $customer = Customer::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'industry' => $this->industry,
            'owner_id' => $this->assigned_to,
        ]);

        $this->update([
            'converted_customer_id' => $customer->id,
            'converted_at' => now(),
        ]);

        $this->logActivity('lead_converted', "Lead \"{$this->name}\" converted to customer");

        return $customer;
    }
}
