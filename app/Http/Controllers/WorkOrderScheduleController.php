<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkOrderScheduleController extends Controller
{
    private function authorizeManager(
        Request $request,
        MaintenanceRequest $maintenanceRequest
    ): void {
        $maintenanceRequest->loadMissing('unit.property');

        $companyId = $maintenanceRequest->unit?->property?->company_id;

        abort_unless($companyId, 404, 'Work order not found.');

        $authorized = CompanyMembership::query()
            ->where('user_id', $request->user()->id)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereIn('role', ['company_admin', 'manager'])
            ->exists();

        abort_unless($authorized, 404, 'Work order not found.');
    }

    public function update(
        Request $request,
        MaintenanceRequest $maintenanceRequest
    ) {
        $this->authorizeManager($request, $maintenanceRequest);

        $validated = $request->validate([
            'scheduled_start_at' => ['required', 'date'],
            'scheduled_end_at' => [
                'required',
                'date',
                'after:scheduled_start_at',
            ],
            'scheduling_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(function () use (
            $maintenanceRequest,
            $validated
        ) {
            $job = MaintenanceRequest::query()
                ->lockForUpdate()
                ->findOrFail($maintenanceRequest->id);

            if ($job->status === 'completed') {
                throw ValidationException::withMessages([
                    'scheduled_start_at' =>
                        'Completed work orders cannot be scheduled.',
                ]);
            }

            $oldStart = $job->scheduled_start_at;
            $oldEnd = $job->scheduled_end_at;

            $job->update([
                'scheduled_start_at' => $validated['scheduled_start_at'],
                'scheduled_end_at' => $validated['scheduled_end_at'],
                'scheduling_notes' => $validated['scheduling_notes'] ?? null,
            ]);

            $newStart = Carbon::parse(
                $validated['scheduled_start_at']
            );

            $newEnd = Carbon::parse(
                $validated['scheduled_end_at']
            );

            $message = $oldStart
                ? 'Work order rescheduled.'
                : 'Work order scheduled.';

            $message .= "\nStart: " .
                $newStart->format('M j, Y g:i A');

            $message .= "\nEnd: " .
                $newEnd->format('M j, Y g:i A');

            if (!empty($validated['scheduling_notes'])) {
                $message .= "\nNotes: " .
                    $validated['scheduling_notes'];
            }

            $job->activities()->create([
                'old_status' => $job->status,
                'new_status' => $job->status,
                'old_priority' => $job->priority,
                'new_priority' => $job->priority,
                'work_notes' => $message,
            ]);
        });

        return redirect()
            ->route('maintenance.show', $maintenanceRequest)
            ->with('success', 'Work order schedule updated successfully.');
    }
}
