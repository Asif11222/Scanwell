<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSubmission extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'product_name',
        'brand',
        'barcode',
        'contributor',
        'contributor_id',
        'confidence',
        'status',
        'duplicate_check',
        'low_fields',
        'extracted_fields',
        'label_image',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'integer',
            'low_fields' => 'array',
            'extracted_fields' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function contributorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'Review');
    }
}
