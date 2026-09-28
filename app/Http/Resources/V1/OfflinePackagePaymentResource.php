<?php

namespace App\Http\Resources\V1;

use App\Models\OfflinePackagePayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OfflinePackagePayment */
class OfflinePackagePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'package_type' => $this->package_type,
            'package_name' => $this->package_name,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'approval' => $this->approval,
            'user' => $this->whenLoaded('user', fn () => $this->user === null ? null : [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'receipt' => $this->whenLoaded('receipt', fn () => $this->receipt === null ? null : new UploadResource($this->receipt)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
