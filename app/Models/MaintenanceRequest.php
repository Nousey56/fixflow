<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'unit_id',
        'title',
        'description',
        'priority',
        'status',
        'work_notes',
        'assigned_to',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities()
    {
        return $this->hasMany(MaintenanceActivity::class)
            ->latest();
    }
}

