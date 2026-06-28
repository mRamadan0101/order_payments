<?php

namespace App\Filters;

class OrderFilter extends Filters
{
    public function user_id(int $value)
    {
        if (!empty($value)) {
            $this->query->where('user_id', $value);
        }
    }

    public function status(string $value)
    {
        if (!empty($value)) {
            $this->query->where('status', $value);
        }
    }

    public function total_amount(float $value)
    {
        if (!empty($value)) {
            $this->query->where('total_amount', $value);
        }
    }

    public function order_number(string $value)
    {
        if (!empty($value)) {
            $this->query->where('order_number', 'like', '%'.$value.'%');
        }
    }
}
