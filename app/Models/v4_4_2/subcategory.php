<?php

namespace App\Models\v4_4_2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class subcategory extends Model
{
    use HasFactory;
    protected $connection = 'dynamic_connection';

    protected $table = 'subcategory';

    public $guarded = [];
}
