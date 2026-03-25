<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'po_number' => $this->po_number,
            'status' => $this->status,
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'code' => $this->vendor->code,
            ]),
        ];
    }
}
