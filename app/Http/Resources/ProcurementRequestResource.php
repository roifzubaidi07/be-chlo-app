<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'lock_version' => $this->lock_version,
            'created_at' => $this->created_at?->toIso8601String(),
            'requester' => $this->whenLoaded('requester', fn () => [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
                'email' => $this->requester->email,
            ]),
            'items' => RequestItemResource::collection($this->whenLoaded('requestItems')),
            'approvals' => ApprovalResource::collection($this->whenLoaded('approvals')),
            'procurement_orders' => ProcurementOrderResource::collection($this->whenLoaded('procurementOrders')),
        ];
    }
}
