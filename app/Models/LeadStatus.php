<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStatus extends Model
{
    protected $fillable = ['name', 'color', 'sort_order'];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
