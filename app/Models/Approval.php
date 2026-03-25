<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'request_id',
        'user_approval_id',
        'status',
    ];

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_approval_id');
    }
}
