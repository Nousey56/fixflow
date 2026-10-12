<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'status',
    ];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function memberships()
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'company_memberships'
        )->withPivot('role', 'status')
         ->withTimestamps();
    }
}

