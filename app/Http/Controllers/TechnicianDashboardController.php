<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class TechnicianDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $companyIds = $user->companies()
            ->wherePivot('status', 'active')
            ->wherePivot('role', 'technician')
            ->pluck('companies.id');

        $requests = MaintenanceRequest::query()
            ->where('assigned_to', $user->id)
            ->whereHas('unit.property', function ($query) use ($companyIds) {
                $query->whereIn('company_id', $companyIds);
            })
            ->with(['unit.property', 'assignedTechnician'])
            ->latest()
            ->get();

        return view('technician.index', [
            'requests' => $requests,
        ]);
    }
}
