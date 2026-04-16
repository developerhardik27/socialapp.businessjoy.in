<?php

namespace App\Models\v4_4_2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class employee extends Model
{
    use HasFactory;
    protected $connection = 'dynamic_connection';
    protected $table = 'employees';

    protected $guarded = [];

    protected $casts = [
        'id_proofs' => 'array',
        'address_proofs' => 'array',
        'other_attachments' => 'array',
    ];
}
