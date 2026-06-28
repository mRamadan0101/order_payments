<?php

namespace App\Filters;

class PaymentFilter extends Filters
{
    public function order_id(int $value)
    {
        if (!empty($value)) {
            $this->query->where('order_id', $value);
        }
    }

    public function payment_method(string $value)
    {
        if (!empty($value)) {
            $this->query->where('payment_method', $value);
        }
    }

    public function payment_id(string $value)
    {
        if (!empty($value)) {
            $this->query->where('payment_id', 'like', '%'.$value.'%');
        }
    }
}
