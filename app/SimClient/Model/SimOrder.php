<?php
declare(strict_types=1);

namespace App\SimClient\Model;

use Illuminate\Database\Eloquent\Model;

class SimOrder extends Model
{
    protected $table = 'sim_client_order';

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'meta' => 'array',
    ];

    public function scopeSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId)->where('is_deleted', 0);
    }
}
