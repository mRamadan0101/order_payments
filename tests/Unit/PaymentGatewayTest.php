<?php

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\PaymentGateways\CreditCardGateway;
use App\Services\PaymentGateways\PayPalGateway;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    private function makeOrder(float $total = 100): Order
    {
        $order = new Order(['total' => $total]);
        $order->id = 1;

        return $order;
    }

    public function test_credit_card_gateway_succeeds_with_valid_configuration(): void
    {
        config([
            'payment.gateways.credit_card.api_key' => 'test_key',
            'payment.gateways.credit_card.secret' => 'test_secret',
        ]);

        $gateway = new CreditCardGateway();
        $result = $gateway->process($this->makeOrder(), ['card_number' => '4242424242424242']);

        $this->assertTrue($result->isSuccessful());
        $this->assertSame(PaymentStatus::Successful, $result->status);
    }

    public function test_credit_card_gateway_fails_when_not_configured(): void
    {
        config([
            'payment.gateways.credit_card.api_key' => null,
            'payment.gateways.credit_card.secret' => null,
        ]);

        $order = $this->makeOrder();
        $gateway = new CreditCardGateway();

        $result = $gateway->process($order, ['card_number' => '4242424242424242']);

        $this->assertFalse($result->isSuccessful());
        $this->assertSame('Credit card gateway is not configured.', $result->failureReason);
    }

    public function test_paypal_gateway_succeeds_with_valid_configuration(): void
    {
        config([
            'payment.gateways.paypal.client_id' => 'client',
            'payment.gateways.paypal.client_secret' => 'secret',
        ]);

        $gateway = new PayPalGateway();

        $result = $gateway->process($this->makeOrder(), ['paypal_email' => 'buyer@example.com']);

        $this->assertTrue($result->isSuccessful());
    }

    public function test_paypal_gateway_fails_when_email_contains_fail(): void
    {
        config([
            'payment.gateways.paypal.client_id' => 'client',
            'payment.gateways.paypal.client_secret' => 'secret',
        ]);

        $gateway = new PayPalGateway();

        $result = $gateway->process($this->makeOrder(), ['paypal_email' => 'fail@example.com']);

        $this->assertFalse($result->isSuccessful());
    }
}
