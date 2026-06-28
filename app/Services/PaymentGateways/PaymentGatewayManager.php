<?php

namespace App\Services\PaymentGateways;

use App\Enums\PaymentMethod;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways = [];

    public function __construct()
    {
        $this->register(new CreditCardGateway());
        $this->register(new PayPalGateway());
    }

    public function register(PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$gateway->getName()] = $gateway;
    }

    public function resolve(PaymentMethod|string $method): PaymentGatewayInterface
    {
        $name = $method instanceof PaymentMethod ? $method->value : $method;

        if (! isset($this->gateways[$name])) {
            throw new InvalidArgumentException("Payment gateway [{$name}] is not registered.");
        }

        return $this->gateways[$name];
    }

    /** @return array<int, string> */
    public function availableGateways(): array
    {
        return array_keys($this->gateways);
    }
}
