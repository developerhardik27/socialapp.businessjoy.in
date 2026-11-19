<?php

namespace App\Models\v4_4_4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyPerson extends Model
{
    use HasFactory;
    protected $connection = 'dynamic_connection';

    protected $table = 'family_person';

    protected $guarded = [];

    public function shakhMaster()
    {
        return $this->belongsTo(Shakh::class, 'shakh_id');
    }

    /**
     * `shakh` resolves to the master list name when a valid shakh_id exists.
     * Falls back to the stored free-text value (legacy / unmatched records).
     */
    public function getShakhAttribute($value)
    {
        $shakhId = $this->attributes['shakh_id'] ?? null;
        if ($shakhId) {
            $master = $this->shakhMaster;
            if ($master && !empty($master->name)) {
                return $master->name;
            }
        }
        return $value;
    }
}
