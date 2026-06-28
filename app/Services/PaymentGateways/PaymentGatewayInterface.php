<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;

interface PaymentGatewayInterface
{
    public function getName(): string;

    public function process(Order $order, array $payload = []): PaymentGatewayResult;
}
