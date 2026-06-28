<?php

namespace App\Http\Requests\Api\Payment;

use App\Enums\PaymentMethod;
use App\Http\Requests\Api\BaseRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'gateway_payload' => ['sometimes', 'array'],
            'gateway_payload.card_number' => ['required_if:payment_method,credit_card', 'string'],
            'gateway_payload.paypal_email' => ['required_if:payment_method,paypal', 'email'],
            'gateway_payload.simulate_failure' => ['sometimes', 'boolean'],
        ];
    }
}
