<?php

namespace App\Models\v4_4_3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class consignor extends Model
{
    use HasFactory;

    protected $connection = 'dynamic_connection';

    protected $table = 'customers';

    public $guarded = [];
}
