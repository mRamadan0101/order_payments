<?php

namespace App\Http\Requests\Api\Orders;

use App\Http\Requests\Api\BaseRequest;

class UpdateOrderRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_details' => ['sometimes', 'array', 'min:1'],
            'order_details.*.product_name' => ['required_with:order_details', 'string', 'max:255'],
            'order_details.*.quantity' => ['required_with:order_details', 'numeric', 'min:1'],
            'order_details.*.price' => ['required_with:order_details', 'numeric', 'min:1'],
        ];
    }
}
