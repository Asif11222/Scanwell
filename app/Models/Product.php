<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'barcode',
        'category',
        'status',
        'verified',
        'flags_count',
        'country',
        'serving_size',
        'manufacturer',
        'source',
        'ingredients',
        'nutrition',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'nutrition' => 'array',
            'verified' => 'boolean',
            'flags_count' => 'integer',
        ];
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(ProductCorrection::class, 'product_id');
    }

    public function duplicatesA(): HasMany
    {
        return $this->hasMany(ProductDuplicate::class, 'product_a_id');
    }

    public function duplicatesB(): HasMany
    {
        return $this->hasMany(ProductDuplicate::class, 'product_b_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'Published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('verified', false);
    }
}
