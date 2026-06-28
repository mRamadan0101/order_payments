<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    use Filterable;
    protected $guarded = [];
    protected $table = 'payments';
    protected $casts = [
        'amount' => 'float',
        'gateway_response' => 'array',
        'payment_method' => PaymentMethod::class,
    ];
}
