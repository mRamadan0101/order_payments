<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Filters\PaymentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Payment\StorePaymentRequest;
use App\Http\Resources\Api\Payments\PaymentResource;
use App\Http\Resources\Api\Payments\PaymentResourceCollection;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentGateways\PaymentGatewayManager;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(PaymentFilter $filter)
    {
        $payments = Payment::filter($filter)->paginate(10);

        return $this->setCode(200)
            ->setSuccess('Payments retrieved successfully')
            ->setData(new PaymentResourceCollection($payments))
            ->send();
    }

    public function show(int $payment)
    {
        $payment = Payment::find($payment);

        if (!$payment) {
            return $this->setError('Payment not found')
                ->setCode(404)
                ->send();
        }

        return $this->setCode(200)
            ->setSuccess('Payment retrieved successfully')
            ->setData(new PaymentResource($payment))
            ->send();
    }

    public function store(StorePaymentRequest $request)
    {
        $validated = $request->validated();

        $order = Order::find($validated['order_id']);
        $gatewayManager = new PaymentGatewayManager();
        $method = PaymentMethod::from($validated['payment_method']);
        $gateway = $gatewayManager->resolve($method);
        $result = $gateway->process($order, $validated['gateway_payload'] ?? []);

        $payment = Payment::create([
            'payment_id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'status' => $result->status,
            'payment_method' => $method,
            'amount' => $order->total_amount,
            'gateway_response' => [
                'transaction_reference' => $result->transactionReference,
                'metadata' => $result->metadata,
                'failure_reason' => $result->failureReason,
            ],
        ]);

        return $this->setCode(201)
            ->setSuccess('Payment created successfully')
            ->setData(new PaymentResource($payment))
            ->send();
    }
}
