<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\AuthenticatesApiUsers;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use AuthenticatesApiUsers, RefreshDatabase;

    public function test_user_can_create_order_with_calculated_total(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'items' => [
                ['product_name' => 'Laptop', 'quantity' => 1, 'price' => 999.99],
                ['product_name' => 'Mouse', 'quantity' => 2, 'price' => 25.50],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.customer_name', 'Jane Doe')
            ->assertJsonPath('data.total', '1050.99')
            ->assertJsonCount(2, 'data.items');

        $this->assertDatabaseHas('orders', [
            'customer_email' => 'jane@example.com',
            'total' => 1050.99,
        ]);
    }

    public function test_user_can_list_orders_filtered_by_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->authenticate($user);

        Order::factory()->for($user)->create(['status' => OrderStatus::Pending]);
        Order::factory()->for($user)->confirmed()->create();
        Order::factory()->for($otherUser)->create();

        $response = $this->getJson('/api/orders?status=confirmed');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_update_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $this->authenticate($user);

        $response = $this->putJson("/api/orders/{$order->id}", [
            'status' => OrderStatus::Confirmed->value,
            'customer_name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.customer_name', 'Updated Name');
    }

    public function test_user_can_delete_order_without_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $this->authenticate($user);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_user_cannot_delete_order_with_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->confirmed()->create();
        Payment::factory()->for($order)->create();
        $this->authenticate($user);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Orders with associated payments cannot be deleted.');
    }

    public function test_user_cannot_access_another_users_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $this->authenticate($user);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertNotFound();
    }
}
