<?php

namespace App\Models\v4_4_0;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $table = 'subscription_payments';

    protected $guarded = [
        
    ];

    protected $casts = [
        'payment_start_date' => 'date',
        'payment_end_date' => 'date',
        'next_billing_date' => 'date',
    ];
}
