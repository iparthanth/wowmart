<?php

namespace App\Http\Resources\V1;

use App\Models\WalletRecharge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WalletRecharge */
class WalletRechargeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'approval' => $this->approval,
            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ] : null),
            'receipt' => $this->whenLoaded('receipt', fn () => $this->receipt === null ? null : new UploadResource($this->receipt)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
