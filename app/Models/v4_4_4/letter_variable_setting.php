<?php

namespace App\Models\v4_4_4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class letter_variable_setting extends Model
{
    use HasFactory;
    protected $connection = 'dynamic_connection';

    protected $table = 'letter_variable_settings';

    public $guarded = [];
}
