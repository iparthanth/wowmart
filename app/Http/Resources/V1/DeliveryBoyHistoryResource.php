<?php

namespace App\Http\Resources\V1;

use App\Models\DeliveryBoyCollection;
use App\Models\DeliveryBoyPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A row from either history (payment or collection). `recorded_at` is whichever
 * timestamp the row carries, so consumers read one shape.
 *
 * @mixin DeliveryBoyPayment|DeliveryBoyCollection
 */
class DeliveryBoyHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $recorded = $this->paid_at ?? $this->collected_at;

        return [
            'id' => $this->id,
            'delivery_boy_id' => $this->delivery_boy_id,
            'delivery_boy' => $this->whenLoaded('deliveryBoy', fn () => [
                'id' => $this->deliveryBoy->id,
                'name' => $this->deliveryBoy->name,
            ]),
            'amount' => (float) $this->amount,
            'note' => $this->note,
            'kind' => $this->paid_at !== null ? 'payment' : 'collection',
            'recorded_at' => $recorded?->toIso8601String(),
        ];
    }
}
