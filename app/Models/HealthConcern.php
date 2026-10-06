<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthConcern extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'description',
        'mapped_nutrients',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'mapped_nutrients' => 'array',
            'active' => 'boolean',
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(HealthRule::class, 'health_concern_id');
    }
}
