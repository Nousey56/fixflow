<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    /**
     * Attributes that can be mass assigned.
     */
    protected $fillable = [
        'property_id',
        'unit_number',
        'floor',
    ];

    /**
     * The property this unit belongs to.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Maintenance requests associated with this unit.
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
