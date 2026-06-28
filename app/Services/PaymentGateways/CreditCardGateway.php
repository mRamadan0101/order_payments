<?php

namespace App\Services\PaymentGateways;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Str;

class CreditCardGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'credit_card';
    }

    public function process(Order $order, array $payload = []): PaymentGatewayResult
    {
        $apiKey = config('payment.gateways.credit_card.api_key');
        $secret = config('payment.gateways.credit_card.secret');

        if (empty($apiKey) || empty($secret)) {
            return new PaymentGatewayResult(
                status: PaymentStatus::Failed,
                transactionReference: 'cc_'.Str::uuid(),
                metadata: ['gateway' => $this->getName()],
                failureReason: 'Credit card gateway is not configured.',
            );
        }

        $cardNumber = $payload['card_number'] ?? '';
        $simulateFailure = $payload['simulate_failure'] ?? false;

        if ($simulateFailure || str_ends_with((string) $cardNumber, '0000')) {
            return new PaymentGatewayResult(
                status: PaymentStatus::Failed,
                transactionReference: 'cc_'.Str::uuid(),
                metadata: [
                    'gateway' => $this->getName(),
                    'processor' => 'simulated_credit_card',
                ],
                failureReason: 'Card was declined by the issuer.',
            );
        }

        return new PaymentGatewayResult(
            status: PaymentStatus::Successful,
            transactionReference: 'cc_'.Str::uuid(),
            metadata: [
                'gateway' => $this->getName(),
                'processor' => 'simulated_credit_card',
                'last_four' => substr((string) $cardNumber, -4) ?: '4242',
            ],
        );
    }
}
