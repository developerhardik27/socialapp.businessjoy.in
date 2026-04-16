<?php

namespace App\Models\v4_4_1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class companiesholiday extends Model
{
    use HasFactory;
     protected $connection = 'dynamic_connection';

    protected $table = 'companiesholidays';

    public $guarded = [];
}
