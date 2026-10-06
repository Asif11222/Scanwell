<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'target',
        'operator',
        'threshold',
        'unit',
        'severity',
        'health_concern_id',
        'concern',
        'status',
        'version',
        'effective_date',
        'source',
        'message',
        'recommendation',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }

    public function healthConcern(): BelongsTo
    {
        return $this->belongsTo(HealthConcern::class, 'health_concern_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'Published');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'Review');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }
}
