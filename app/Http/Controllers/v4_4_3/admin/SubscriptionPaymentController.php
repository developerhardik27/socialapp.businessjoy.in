<?php

namespace App\Http\Controllers\v4_4_3\admin;

use App\Http\Controllers\Controller;

class SubscriptionPaymentController extends Controller
{
    public function index()
    {
        return view('v4_4_1.admin.Subscription.subscriptionpayments');
    }
}
