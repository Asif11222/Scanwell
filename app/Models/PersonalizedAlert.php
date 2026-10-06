<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalizedAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'condition',
        'trigger',
        'severity',
        'title',
        'message',
        'recommendation',
        'priority',
        'status',
        'destination',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }
}
