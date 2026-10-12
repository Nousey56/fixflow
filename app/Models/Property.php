<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'address',
        'city',
        'state',
        'zip_code',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}

