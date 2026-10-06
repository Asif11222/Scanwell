<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'audience',
        'schedule_time',
        'deep_link',
        'status',
        'sent_count',
        'opened_count',
    ];

    protected function casts(): array
    {
        return [
            'sent_count' => 'integer',
            'opened_count' => 'integer',
        ];
    }

    public function getOpenRateAttribute(): float
    {
        if ($this->sent_count <= 0) {
            return 0.0;
        }
        return round(($this->opened_count / $this->sent_count) * 100, 1);
    }
}
