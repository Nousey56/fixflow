<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MaintenanceRequestController extends Controller
{
    /**
     * Active company memberships for the current user.
     */
    private function memberships(Request $request)
    {
        return CompanyMembership::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Company IDs where the user is an administrator
     * or manager.
     */
    private function managementCompanyIds(Request $request)
    {
        return $this->memberships($request)
            ->whereIn('role', ['company_admin', 'manager'])
            ->pluck('company_id');
    }

    /**
     * Company IDs where the user is a technician.
     */
    private function technicianCompanyIds(Request $request)
    {
        return $this->memberships($request)
            ->where('role', 'technician')
            ->pluck('company_id');
    }

    /**
     * Maintenance requests accessible to the user.
     *
     * Administrators and managers see company requests.
     * Technicians see only their assigned requests.
     */
    private function accessibleRequests(Request $request)
    {
        $managementIds = $this->managementCompanyIds($request);
        $technicianIds = $this->technicianCompanyIds($request);
        $userId = $request->user()->id;

        return MaintenanceRequest::query()
            ->where(function ($query) use (
                $managementIds,
                $technicianIds,
                $userId
            ) {
                $query->whereHas(
                    'unit.property',
                    function ($propertyQuery) use ($managementIds) {
                        $propertyQuery->whereIn(
                            'company_id',
                            $managementIds
                        );
                    }
                );

                $query->orWhere(function ($technicianQuery) use (
                    $technicianIds,
                    $userId
                ) {
                    $technicianQuery
                        ->where('assigned_to', $userId)
                        ->whereHas(
                            'unit.property',
                            function ($propertyQuery) use ($technicianIds) {
                                $propertyQuery->whereIn(
                                    'company_id',
                                    $technicianIds
                                );
                            }
                        );
                });
            });
    }

    /**
     * Check that the user can access this request.
     */
    private function authorizeRequest(
        Request $request,
        MaintenanceRequest $maintenanceRequest
    ): void {
        abort_unless(
            $this->accessibleRequests($request)
                ->whereKey($maintenanceRequest->id)
                ->exists(),
            404
        );
    }

    /**
     * Get the user's role in a particular company.
     */
    private function roleForCompany(
        Request $request,
        int $companyId
    ): ?string {
        $memberships = $this->memberships($request)
            ->where('company_id', $companyId);

        foreach (['company_admin', 'manager', 'technician'] as $role) {
            if ($memberships->contains('role', $role)) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Check if user can manage company requests.
     */
    private function canManage(
        Request $request,
        int $companyId
    ): bool {
        return in_array(
            $this->roleForCompany($request, $companyId),
            ['company_admin', 'manager'],
            true
        );
    }

    /**
     * Check if user is a company administrator.
     */
    private function isCompanyAdmin(
        Request $request,
        int $companyId
    ): bool {
        return $this->roleForCompany(
            $request,
            $companyId
        ) === 'company_admin';
    }

    /**
     * Get active technicians belonging to a company.
     */
    private function techniciansForCompany(int $companyId)
    {
        $technicianIds = CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('role', 'technician')
            ->where('status', 'active')
            ->pluck('user_id');

        return User::query()
            ->whereIn('id', $technicianIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Display accessible maintenance requests.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['open', 'assigned', 'in_progress', 'completed', 'cancelled'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'emergency'])],
        ]);

        // Always start from the access-controlled query, including for statistics.
        $accessible = $this->accessibleRequests($request);

        $statistics = [
            'total' => (clone $accessible)->count(),
            'open' => (clone $accessible)->where('status', 'open')->count(),
            'in_progress' => (clone $accessible)->where('status', 'in_progress')->count(),
            'completed' => (clone $accessible)->where('status', 'completed')->count(),
        ];

        $query = clone $accessible;
        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($matching) use ($search) {
                $matching->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('unit', function ($units) use ($search) {
                        $units->where('unit_number', 'like', "%{$search}%")
                            ->orWhereHas('property', function ($properties) use ($search) {
                                $properties->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('assignedTechnician', function ($technicians) use ($search) {
                        $technicians->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $requests = $query
            ->with(['unit.property', 'assignedTechnician'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('maintenance.index', [
            'requests' => $requests,
            'statistics' => $statistics,
            'filters' => [
                'search' => $search,
                'status' => $filters['status'] ?? '',
                'priority' => $filters['priority'] ?? '',
            ],
        ]);
    }

    /**
     * Show the create maintenance request form.
     */
    public function create(Request $request)
    {
        $companyIds = $this->managementCompanyIds($request);

        abort_if(
            $companyIds->isEmpty(),
            403,
            'Only administrators and managers can create requests.'
        );

        $properties = Property::query()
            ->whereIn('company_id', $companyIds)
            ->with('units')
            ->get();

        return view('maintenance.create', [
            'properties' => $properties,
        ]);
    }

    /**
     * Store a new maintenance request.
     */
    public function store(Request $request)
    {
        $companyIds = $this->managementCompanyIds($request);

        abort_if(
            $companyIds->isEmpty(),
            403,
            'Only administrators and managers can create requests.'
        );

        $validated = $request->validate([
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')
                    ->whereIn(
                        'property_id',
                        Property::query()
                            ->whereIn('company_id', $companyIds)
                            ->select('id')
                    ),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
            ],
            'priority' => [
                'required',
                'in:low,medium,high,emergency',
            ],
        ]);

        $validated['status'] = 'open';

        MaintenanceRequest::create($validated);

        return redirect()
            ->route('maintenance.index')
            ->with(
                'success',
                'Maintenance request created successfully.'
            );
    }

    /**
     * Show an authorized maintenance request.
     */
    public function show(
        Request $request,
        MaintenanceRequest $maintenanceRequest
    ) {
        $this->authorizeRequest(
            $request,
            $maintenanceRequest
        );

        $maintenanceRequest->load([
            'unit.property',
            'assignedTechnician',
            'activities',
        ]);

        $companyId = (int) $maintenanceRequest
            ->unit
            ->property
            ->company_id;

        $canAssign = $this->isCompanyAdmin(
            $request,
            $companyId
        );

        $canEditPriority = $this->canManage(
            $request,
            $companyId
        );

        $technicians = $canAssign
            ? $this->techniciansForCompany($companyId)
            : collect();

        return view('maintenance.show', [
            'request' => $maintenanceRequest,
            'technicians' => $technicians,
            'canAssign' => $canAssign,
            'canEditPriority' => $canEditPriority,
        ]);
    }

    /**
     * Update a maintenance request according to
     * the user's company role.
     */
    public function update(
        Request $request,
        MaintenanceRequest $maintenanceRequest
    ) {
        $this->authorizeRequest($request, $maintenanceRequest);

        $maintenanceRequest->load('unit.property');
        $companyId = (int) $maintenanceRequest->unit->property->company_id;
        $canAssign = $this->isCompanyAdmin($request, $companyId);
        $canEditPriority = $this->canManage($request, $companyId);

        $validated = $request->validate([
            'status' => ['required', 'in:open,assigned,in_progress,completed'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high,emergency'],
            'work_notes' => ['nullable', 'string', 'max:5000'],
            'completion_notes' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['sometimes', 'nullable', 'integer'],
        ]);

        if ($request->exists('assigned_to') && !$canAssign) {
            abort(403, 'Only company administrators can assign technicians.');
        }

        if (
            !$canEditPriority &&
            array_key_exists('priority', $validated) &&
            $validated['priority'] !== $maintenanceRequest->priority
        ) {
            abort(403, 'You do not have permission to change priority.');
        }

        if (
            $canAssign &&
            array_key_exists('assigned_to', $validated) &&
            $validated['assigned_to'] !== null
        ) {
            $validTechnician = CompanyMembership::query()
                ->where('company_id', $companyId)
                ->where('user_id', $validated['assigned_to'])
                ->where('role', 'technician')
                ->where('status', 'active')
                ->exists();

            if (!$validTechnician) {
                throw ValidationException::withMessages([
                    'assigned_to' => 'Please select an active technician from this company.',
                ]);
            }
        }

        DB::transaction(function () use (
            $request,
            $maintenanceRequest,
            $validated,
            $canAssign,
            $canEditPriority,
            $companyId
        ) {
            $job = MaintenanceRequest::query()
                ->lockForUpdate()
                ->findOrFail($maintenanceRequest->id);

            $this->authorizeRequest($request, $job);

            $oldStatus = $job->status;
            $oldPriority = $job->priority;
            $oldAssignedTo = $job->assigned_to;
            $newStatus = $validated['status'];
            $newPriority = $canEditPriority
                ? ($validated['priority'] ?? $oldPriority)
                : $oldPriority;

            if (
                !$canEditPriority &&
                array_key_exists('priority', $validated) &&
                $validated['priority'] !== $oldPriority
            ) {
                abort(403, 'You do not have permission to change priority.');
            }

            $newAssignedTo = $oldAssignedTo;
            if ($canAssign && array_key_exists('assigned_to', $validated)) {
                $newAssignedTo = $validated['assigned_to'];

                if ($newAssignedTo !== null) {
                    $stillValid = CompanyMembership::query()
                        ->where('company_id', $companyId)
                        ->where('user_id', $newAssignedTo)
                        ->where('role', 'technician')
                        ->where('status', 'active')
                        ->exists();

                    if (!$stillValid) {
                        throw ValidationException::withMessages([
                            'assigned_to' => 'The selected technician is no longer active in this company.',
                        ]);
                    }
                }
            }

            $assignmentChanged =
                (string) ($oldAssignedTo ?? '') !==
                (string) ($newAssignedTo ?? '');

            $newNotes = $validated['work_notes'] ?? null;
            $completionNotes = $validated['completion_notes'] ?? null;
            $updates = [
                'status' => $newStatus,
                'priority' => $newPriority,
                'updated_at' => now(),
            ];

            // Technicians must use the dedicated job workflow.
            // Reject every status change through the general update endpoint.
            if (!$canEditPriority && $newStatus !== $oldStatus) {
                abort(
                    403,
                    'Technicians cannot change status through this form. Use your technician dashboard.'
                );
            }

            // A completed job cannot be silently reopened by a technician.
            if (!$canEditPriority && $oldStatus === 'completed' && $newStatus !== 'completed') {
                abort(403, 'Only a manager or administrator can reopen a completed job.');
            }

            // Completion notes are required for EVERY new completion,
            // including updates submitted by managers or administrators.
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $completionNotes = filled($completionNotes)
                    ? $completionNotes
                    : $newNotes;

                if (!is_string($completionNotes) || mb_strlen(trim($completionNotes)) < 10) {
                    throw ValidationException::withMessages([
                        'completion_notes' => 'Enter at least 10 characters describing the completed repair.',
                    ]);
                }

                $updates['completed_at'] = now();
                $updates['completion_notes'] = $completionNotes;
                $updates['work_notes'] = $completionNotes;
                $newNotes = $completionNotes;
            } elseif (filled($newNotes)) {
                $updates['work_notes'] = $newNotes;
            }

            // Track first start, and clear completion information on reopen.
            if ($newStatus === 'in_progress' && $oldStatus !== 'in_progress') {
                if ($oldStatus === 'completed') {
                    $updates['started_at'] = now();
                } elseif ($job->started_at === null) {
                    $updates['started_at'] = now();
                }
            }

            if ($oldStatus === 'completed' && $newStatus !== 'completed') {
                $updates['completed_at'] = null;
                $updates['completion_notes'] = null;
            }

            if ($assignmentChanged) {
                $updates['assigned_to'] = $newAssignedTo;
            }

            $activityNotes = $newNotes;
            if ($assignmentChanged) {
                $oldName = $oldAssignedTo
                    ? (User::find($oldAssignedTo)?->name ?? 'Unknown')
                    : 'Unassigned';
                $newName = $newAssignedTo
                    ? (User::find($newAssignedTo)?->name ?? 'Unknown')
                    : 'Unassigned';
                $assignmentMessage = "Technician assignment changed: {$oldName} → {$newName}.";
                $activityNotes = filled($newNotes)
                    ? $assignmentMessage . "\n\n" . $newNotes
                    : $assignmentMessage;
            }

            $hasChanges =
                $oldStatus !== $newStatus ||
                $oldPriority !== $newPriority ||
                $assignmentChanged ||
                filled($newNotes);

            if ($hasChanges) {
                $job->activities()->create([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'old_priority' => $oldPriority,
                    'new_priority' => $newPriority,
                    'work_notes' => $activityNotes,
                ]);
            }

            // Query builder ensures the new tracking fields are persisted
            // even if the model's $fillable has not yet been expanded.
            DB::table('maintenance_requests')
                ->where('id', $job->id)
                ->update($updates);
        });

        return redirect()
            ->route('maintenance.show', $maintenanceRequest)
            ->with('success', 'Maintenance request updated successfully.');
    }
}
