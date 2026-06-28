<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentGateways\PaymentGatewayManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(private readonly PaymentGatewayManager $gatewayManager)
    {
    }

    public function list(User $user, ?int $orderId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Payment::query()
            ->with('order')
            ->whereHas('order', fn ($query) => $query->where('user_id', $user->id))
            ->when($orderId, fn ($query) => $query->where('order_id', $orderId))
            ->latest()
            ->paginate($perPage);
    }

    public function findForUser(User $user, int $paymentId): Payment
    {
        return Payment::query()
            ->with('order')
            ->whereHas('order', fn ($query) => $query->where('user_id', $user->id))
            ->findOrFail($paymentId);
    }

    public function process(User $user, array $data): Payment
    {
        $order = Order::query()
            ->with('items')
            ->where('user_id', $user->id)
            ->findOrFail($data['order_id']);

        if (! $order->isConfirmed()) {
            throw new BusinessRuleException('Payments can only be processed for confirmed orders.');
        }

        $method = PaymentMethod::from($data['payment_method']);
        $gateway = $this->gatewayManager->resolve($method);
        $result = $gateway->process($order, $data['gateway_payload'] ?? []);

        return Payment::create([
            'payment_id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'status' => $result->status,
            'payment_method' => $method,
            'amount' => $order->total,
            'gateway_response' => [
                'transaction_reference' => $result->transactionReference,
                'metadata' => $result->metadata,
                'failure_reason' => $result->failureReason,
            ],
        ]);
    }

    /** @return array<int, string> */
    public function availableGateways(): array
    {
        return $this->gatewayManager->availableGateways();
    }
}
