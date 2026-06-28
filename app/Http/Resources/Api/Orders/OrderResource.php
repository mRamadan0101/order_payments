<?php


namespace App\Http\Resources\Api\Orders;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user_id' => (int) $this->user_id,
            'total_amount' => (float) $this->total_amount,
            'status' => (string) $this->status,
            'order_number' => (string) $this->order_number,
            'order_details' => OrderDetailsResource::collection($this->whenLoaded('orderDetails')),
        ];
    }
}
