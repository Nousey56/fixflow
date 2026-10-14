<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkOrderAssignmentController extends Controller
{
    public function update(
        Request $request,
        MaintenanceRequest $maintenanceRequest
    ) {
        $user = $request->user();

        $maintenanceRequest->loadMissing('unit.property');

        $companyId = $maintenanceRequest->unit?->property?->company_id;

        abort_unless(
            $companyId,
            404,
            'Work order not found.'
        );

        // Only company administrators and managers may assign jobs.
        $authorized = CompanyMembership::query()
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereIn('role', ['company_admin', 'manager'])
            ->exists();

        abort_unless(
            $authorized,
            404,
            'Work order not found.'
        );

        $validated = $request->validate([
            'technician_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ]);

        $technicianId = $validated['technician_id'] ?? null;

        // The selected technician must be active in this company.
        if ($technicianId !== null) {
            $isCompanyTechnician = CompanyMembership::query()
                ->where('user_id', $technicianId)
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->where('role', 'technician')
                ->exists();

            if (!$isCompanyTechnician) {
                throw ValidationException::withMessages([
                    'technician_id' =>
                        'The selected technician is not an active technician for this company.',
                ]);
            }
        }

        DB::transaction(function () use (
            $maintenanceRequest,
            $technicianId
        ) {
            $job = MaintenanceRequest::query()
                ->lockForUpdate()
                ->findOrFail($maintenanceRequest->id);

            if (in_array($job->status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'technician_id' =>
                        'Completed or cancelled work orders cannot be reassigned.',
                ]);
            }

            $oldTechnicianId = $job->assigned_to;

            // Avoid creating duplicate activity entries.
            if (
                ($oldTechnicianId === null && $technicianId === null) ||
                ($oldTechnicianId !== null &&
                    (int) $oldTechnicianId === (int) $technicianId)
            ) {
                return;
            }

            $oldTechnicianName = $job->assignedTechnician?->name
                ?? 'Unassigned';

            $newTechnicianName = $technicianId
                ? \App\Models\User::findOrFail($technicianId)->name
                : 'Unassigned';

            $oldStatus = $job->status;

            // Assigning an open work order moves it to Assigned.
            // Unassigning an assigned work order moves it to Open.
            $newStatus = $oldStatus;

            if ($technicianId !== null && $oldStatus === 'open') {
                $newStatus = 'assigned';
            }

            if ($technicianId === null && $oldStatus === 'assigned') {
                $newStatus = 'open';
            }

            $job->update([
                'assigned_to' => $technicianId,
                'status' => $newStatus,
            ]);

            $message = $technicianId === null
                ? 'Technician unassigned.'
                : ($oldTechnicianId === null
                    ? 'Technician assigned.'
                    : 'Technician reassigned.');

            $message .= "\nPrevious: " . $oldTechnicianName;
            $message .= "\nCurrent: " . $newTechnicianName;

            $job->activities()->create([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_priority' => $job->priority,
                'new_priority' => $job->priority,
                'work_notes' => $message,
            ]);
        });

        return redirect()
            ->route('dispatch.index', [
                'view' => 'all',
            ])
            ->with('success', 'Technician assignment updated successfully.');
    }
}
