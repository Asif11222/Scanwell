<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'headline',
        'copy',
        'cta',
        'cta_url',
        'placement',
        'start_at',
        'end_at',
        'priority',
        'frequency_cap',
        'status',
        'region',
        'segment',
        'health_target',
        'theme',
        'impressions',
        'clicks',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'priority' => 'integer',
            'frequency_cap' => 'integer',
            'impressions' => 'integer',
            'clicks' => 'integer',
        ];
    }

    public function getCtrAttribute(): float
    {
        if ($this->impressions <= 0) {
            return 0.0;
        }
        return round(($this->clicks / $this->impressions) * 100, 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
