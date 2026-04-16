<?php

namespace App\Models\v4_4_0;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoice_commission extends Model
{
    use HasFactory;

    protected $connection = 'dynamic_connection';

    protected $table = 'invoice_commissions';

    protected $guarded = [];
}
