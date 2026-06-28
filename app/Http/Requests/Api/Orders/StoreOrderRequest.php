<?php

namespace App\Http\Requests\Api\Orders;

use App\Http\Requests\Api\BaseRequest;

class StoreOrderRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_details' => ['required', 'array', 'min:1'],
            'order_details.*.product_name' => ['required', 'string', 'max:255'],
            'order_details.*.quantity' => ['required', 'numeric', 'min:1'],
            'order_details.*.price' => ['required', 'numeric', 'min:1'],
        ];
    }
}
