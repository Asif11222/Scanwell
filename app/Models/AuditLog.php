<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'action',
        'detail',
        'user',
        'status',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public static function record(string $action, ?string $detail = null, string $user = 'Admin', string $status = 'Active', ?string $ip = null): static
    {
        return static::create([
            'action' => $action,
            'detail' => $detail,
            'user' => $user,
            'status' => $status,
            'ip_address' => $ip ?? request()->ip(),
            'created_at' => now(),
        ]);
    }
}
