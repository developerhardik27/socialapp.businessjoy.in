<?php

namespace App\Models\v4_4_0;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class data_formate extends Model
{
    use HasFactory;
    protected $connection = 'dynamic_connection';

    protected $table = 'data_formate';

    protected $guarded = [];
}
