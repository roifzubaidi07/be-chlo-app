<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusHistory extends Model
{
    use HasFactory;

    protected $table = 'status_histories';

    const UPDATED_AT = null;

    protected $fillable = [
        'request_id',
        'changed_by_user_id',
        'from_status',
        'to_status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'request_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
