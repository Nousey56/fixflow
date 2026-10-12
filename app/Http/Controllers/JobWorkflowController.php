<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobWorkflowController extends Controller
{
    /**
     * Check whether the current user can work on this job.
     */
    private function authorizeJob(
        Request $request,
        MaintenanceRequest $maintenanceRequest
    ): void {
        $maintenanceRequest->loadMissing('unit.property');

        $companyId = $maintenanceRequest
            ->unit?->property?->company_id;

        abort_unless($companyId, 404);

        $allowed = CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('user_id', $request->user()->id)
            ->where('role', 'technician')
            ->where('status', 'active')
            ->exists();

        abort_unless(
            $allowed &&
            (int) $maintenanceRequest->assigned_to ===
                (int) $request->user()->id,
            403,
            'This job is not assigned to you.'
        );
    }

    /**
     * Start an assigned maintenance job.
     */
    public function start(
        Request $request,
        MaintenanceRequest $maintenanceRequest
    ) {
        DB::transaction(function () use (
            $request,
            $maintenanceRequest
        ) {
            $job = MaintenanceRequest::query()
                ->lockForUpdate()
                ->findOrFail($maintenanceRequest->id);

            $this->authorizeJob($request, $job);

            abort_unless(
                in_array($job->status, ['open', 'assigned'], true),
                422,
                'This job cannot be started.'
            );

            $oldStatus = $job->status;

            DB::table('maintenance_requests')
                ->where('id', $job->id)
                ->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'completed_at' => null,
                    'completion_notes' => null,
                    'updated_at' => now(),
                ]);

            $job->activities()->create([
                'old_status' => $oldStatus,
                'new_status' => 'in_progress',
                'old_priority' => $job->priority,
                'new_priority' => $job->priority,
                'work_notes' => 'Technician started work.',
            ]);
        });

        return redirect()
            ->route('technician.index')
            ->with('success', 'Job started successfully.');
    }

    /**
     * Complete a job with mandatory repair notes.
     */
    public function complete(
        Request $request,
        MaintenanceRequest $maintenanceRequest
    ) {
        $validated = $request->validate([
            'completion_notes' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $maintenanceRequest,
            $validated
        ) {
            $job = MaintenanceRequest::query()
                ->lockForUpdate()
                ->findOrFail($maintenanceRequest->id);

            $this->authorizeJob($request, $job);

            abort_unless(
                $job->status === 'in_progress',
                422,
                'Only jobs in progress can be completed.'
            );

            $notes = $validated['completion_notes'];

            DB::table('maintenance_requests')
                ->where('id', $job->id)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completion_notes' => $notes,
                    'work_notes' => $notes,
                    'updated_at' => now(),
                ]);

            $job->activities()->create([
                'old_status' => 'in_progress',
                'new_status' => 'completed',
                'old_priority' => $job->priority,
                'new_priority' => $job->priority,
                'work_notes' => $notes,
            ]);
        });

        return redirect()
            ->route('technician.index')
            ->with('success', 'Job completed successfully.');
    }
}
