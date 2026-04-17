<?php

namespace App\Models\v4_4_4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class karobarimember extends Model
{
    use HasFactory;
    
    protected $connection = 'dynamic_connection';

    protected $table = 'kraobari_member';

    public $guarded = [];
}
