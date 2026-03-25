<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestItem extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'request_id',
        'item_id',
        'qty',
        'discount',
        'tax',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
        ];
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
