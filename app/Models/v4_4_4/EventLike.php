<?php

namespace App\Models\v4_4_4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLike extends Model
{
    use HasFactory;
    protected $connection = 'dynamic_connection';

    protected $table = 'events_like';

    public $guarded = [];
}
