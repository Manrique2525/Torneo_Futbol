<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory, BelongsToTenant;

    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'tenant_id',
        'name',
        'shield',
        'colors',
        'delegado_id',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // ── Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // ── Relaciones
    public function delegado()
    {
        return $this->belongsTo(User::class, 'delegado_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}