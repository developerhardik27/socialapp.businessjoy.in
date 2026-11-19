<?php

namespace App\Models\v4_4_4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $connection = 'dynamic_connection';

    protected $table = 'notifications';

    protected $guarded = [];
}
