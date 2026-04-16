<?php

namespace App\Models\v4_4_0;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCycleNote extends Model
{
    use HasFactory;

    protected $connection = 'dynamic_connection';

    protected $table = 'billing_cycle_notes';

    public $guarded = [];
}
