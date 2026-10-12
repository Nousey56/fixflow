<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceActivity extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'old_status',
        'new_status',
        'old_priority',
        'new_priority',
        'work_notes',
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }
}