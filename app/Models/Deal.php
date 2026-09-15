<?php

namespace App\Models;

use App\Traits\HasActivities;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes, HasNotes, HasActivities, HasAttachments;

    protected $fillable = [
        'title', 'customer_id', 'company_id', 'value', 'currency', 'probability',
        'deal_stage_id', 'owner_id', 'expected_close_date', 'closed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'expected_close_date' => 'date',
            'closed_at' => 'date',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(DealStage::class, 'deal_stage_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(DealProduct::class);
    }

    /**
     * Move this deal to a new pipeline stage, auto-updating probability
     * and firing an activity log entry. Called from DealController::updateStage().
     */
    public function moveToStage(DealStage $stage): void
    {
        $this->update([
            'deal_stage_id' => $stage->id,
            'probability' => $stage->default_probability,
            'closed_at' => ($stage->is_won || $stage->is_lost) ? now() : null,
        ]);

        $this->logActivity(
            $stage->is_won ? 'deal_won' : ($stage->is_lost ? 'deal_lost' : 'deal_stage_changed'),
            "Deal \"{$this->title}\" moved to {$stage->label}"
        );
    }
}
