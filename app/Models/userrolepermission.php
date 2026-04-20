<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userrolepermission extends Model
{
    use HasFactory;
      
    protected $table = 'user_role_permissions';

    protected $guarded = [];
}
