<?php

namespace Tests\Unit;

use App\Services\PaymentGateways\CreditCardGateway;
use App\Services\PaymentGateways\PaymentGatewayManager;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentGatewayManagerTest extends TestCase
{
    public function test_manager_resolves_registered_gateways(): void
    {
        $manager = new PaymentGatewayManager();

        $this->assertInstanceOf(CreditCardGateway::class, $manager->resolve('credit_card'));
        $this->assertSame(['credit_card', 'paypal'], $manager->availableGateways());
    }

    public function test_manager_throws_for_unknown_gateway(): void
    {
        $manager = new PaymentGatewayManager();

        $this->expectException(InvalidArgumentException::class);
        $manager->resolve('stripe');
    }
}
