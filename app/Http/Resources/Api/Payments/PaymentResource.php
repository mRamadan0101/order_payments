<?php

namespace App\Http\Resources\Api\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'order_id' => (int) $this->order_id,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'payment_id' => (string) $this->payment_id,
            'gateway_response' => (array) $this->gateway_response,
        ];
    }
}
