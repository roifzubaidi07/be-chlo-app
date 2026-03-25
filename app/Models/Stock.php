<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stocks';

    const CREATED_AT = null;

    protected $fillable = [
        'item_id',
        'quantity_on_hand',
        'location',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'integer',
            'updated_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
