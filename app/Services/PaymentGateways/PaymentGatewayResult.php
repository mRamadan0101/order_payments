<?php

namespace App\Services\PaymentGateways;

use App\Enums\PaymentStatus;

readonly class PaymentGatewayResult
{
    public function __construct(
        public PaymentStatus $status,
        public string $transactionReference,
        public array $metadata = [],
        public ?string $failureReason = null,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::Successful;
    }
}
