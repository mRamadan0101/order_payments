<?php

namespace App\Services\PaymentGateways;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Str;

class PayPalGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'paypal';
    }

    public function process(Order $order, array $payload = []): PaymentGatewayResult
    {
        $clientId = config('payment.gateways.paypal.client_id');
        $clientSecret = config('payment.gateways.paypal.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return new PaymentGatewayResult(
                status: PaymentStatus::Failed,
                transactionReference: 'pp_'.Str::uuid(),
                metadata: ['gateway' => $this->getName()],
                failureReason: 'PayPal gateway is not configured.',
            );
        }

        $simulateFailure = $payload['simulate_failure'] ?? false;
        $paypalEmail = $payload['paypal_email'] ?? '';

        if ($simulateFailure || str_contains((string) $paypalEmail, 'fail')) {
            return new PaymentGatewayResult(
                status: PaymentStatus::Failed,
                transactionReference: 'pp_'.Str::uuid(),
                metadata: [
                    'gateway' => $this->getName(),
                    'processor' => 'simulated_paypal',
                ],
                failureReason: 'PayPal authorization was denied.',
            );
        }

        return new PaymentGatewayResult(
            status: PaymentStatus::Successful,
            transactionReference: 'pp_'.Str::uuid(),
            metadata: [
                'gateway' => $this->getName(),
                'processor' => 'simulated_paypal',
                'paypal_email' => $paypalEmail ?: 'buyer@example.com',
            ],
        );
    }
}
