<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Filters\OrderFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Orders\StoreOrderRequest;
use App\Http\Requests\Api\Orders\UpdateOrderRequest;
use App\Http\Resources\Api\Orders\OrderResource;
use App\Http\Resources\Api\Orders\OrderResourceCollection;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function index(OrderFilter $filter)
    {
        $orders = Order::filter($filter)->paginate(10);

        return $this->setCode(200)
        ->setSuccess('Orders retrieved successfully')
        ->setData(new OrderResourceCollection($orders))
        ->send();
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();
        $order = $user->orders()->create([
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'status' => OrderStatus::Pending->value,
            'total_amount' => collect($validated['order_details'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            }),
        ]);

        $order->orderDetails()->createMany($validated['order_details']);

        return $this->setCode(200)
        ->setSuccess('Order created successfully')
        ->setData(new OrderResource($order->load('orderDetails')))
        ->send();
    }

    public function show(int $order): JsonResponse
    {
        $order = Order::find($order);
        if (!$order) {
            return $this->setError('Order not found')
                        ->setCode(404)
                        ->send();
        }

        return $this->setCode(200)
        ->setSuccess('Order retrieved successfully')
        ->setData(new OrderResource($order->load('orderDetails')))
        ->send();
    }

    public function update(UpdateOrderRequest $request, int $order): JsonResponse
    {
        $validated = $request->validated();
        $order = Order::find($order);
        if (!$order) {
            return $this->setError('Order not found')
                        ->setCode(404)
                        ->send();
        }
        $order->orderDetails()->delete();

        $order->orderDetails()->createMany($validated['order_details']);
        $order->update([
            'total_amount' => collect($validated['order_details'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            }),
        ]);

        return $this->setCode(200)
        ->setSuccess('Order updated successfully')
        ->setData(new OrderResource($order->load('orderDetails')))
        ->send();
    }

    public function destroy(int $order)
    {
        $order = Order::find($order);
        if ($order) {
            if ($order->status === OrderStatus::Confirmed->value || $order->payments()->exists()) {
                return $this->setError('Completed orders cannot be deleted')
                ->setCode(400)
                ->send();
            }
            $order->orderDetails()->delete();
            $order->delete();

            return $this->setCode(200)
            ->setSuccess('Order deleted successfully')
            ->send();
        }

        return $this->setError('Order not found')
                        ->setCode(400)
                        ->send();
    }
}
