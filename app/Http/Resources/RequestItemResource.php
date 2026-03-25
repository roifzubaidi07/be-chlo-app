<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'qty' => $this->qty,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'item' => $this->whenLoaded('item', fn () => [
                'id' => $this->item->id,
                'name' => $this->item->name,
                'code' => $this->item->code,
                'price' => $this->item->price,
            ]),
        ];
    }
}
