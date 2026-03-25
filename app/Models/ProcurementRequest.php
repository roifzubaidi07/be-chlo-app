<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementRequest extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_SUBMITTED = 'SUBMITTED';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_IN_PROCUREMENT = 'IN_PROCUREMENT';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_CANCELLED = 'CANCELLED';

    protected $table = 'requests';

    const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'user_request_id',
        'status',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_request_id');
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(RequestItem::class, 'request_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'request_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'request_id');
    }

    public function procurementOrders(): HasMany
    {
        return $this->hasMany(ProcurementOrder::class, 'request_id');
    }
}
