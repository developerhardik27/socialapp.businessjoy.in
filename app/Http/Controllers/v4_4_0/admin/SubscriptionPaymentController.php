<?php

namespace App\Http\Controllers\v4_4_0\admin;

use App\Http\Controllers\Controller;

class SubscriptionPaymentController extends Controller
{
    public function index()
    {
        return view('v4_4_0.admin.Subscription.subscriptionpayments');
    }
}
