<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\AuthenticatesApiUsers;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use AuthenticatesApiUsers, RefreshDatabase;

    public function test_user_can_process_payment_for_confirmed_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->confirmed()->create(['total' => 150.00]);
        $this->authenticate($user);

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'gateway_payload' => [
                'card_number' => '4242424242424242',
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::Successful->value)
            ->assertJsonPath('data.amount', '150.00');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => PaymentStatus::Successful->value,
        ]);
    }

    public function test_payment_fails_for_non_confirmed_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['status' => OrderStatus::Pending]);
        $this->authenticate($user);

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'gateway_payload' => [
                'card_number' => '4242424242424242',
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Payments can only be processed for confirmed orders.');
    }

    public function test_credit_card_gateway_can_simulate_failure(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->confirmed()->create(['total' => 75.00]);
        $this->authenticate($user);

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'gateway_payload' => [
                'card_number' => '4000000000000000',
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::Failed->value);
    }

    public function test_user_can_list_payments_for_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->confirmed()->create();
        Payment::factory()->count(2)->for($order)->create();
        $this->authenticate($user);

        $response = $this->getJson("/api/payments?order_id={$order->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_list_available_gateways(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->getJson('/api/payments/gateways');

        $response->assertOk()
            ->assertJsonFragment(['gateways' => ['credit_card', 'paypal']]);
    }
}
