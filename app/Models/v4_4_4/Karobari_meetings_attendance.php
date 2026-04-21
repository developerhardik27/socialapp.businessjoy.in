<?php

namespace App\Models\v4_4_4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karobari_meetings_attendance extends Model
{
    use HasFactory;
    protected $connection = 'dynamic_connection';

    protected $table = 'karobari_meetings_attendance';

    public $guarded = [];
}
