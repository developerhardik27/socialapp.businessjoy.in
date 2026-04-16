<?php

namespace App\Models\v4_4_4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class generate_letter extends Model
{
    use HasFactory;
    protected $connection = 'dynamic_connection';

    protected $table = 'generate_letter';

    protected $guarded = [];
}
