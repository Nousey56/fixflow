<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    /**
     * Get company IDs the current user can manage.
     */
    private function manageableCompanyIds(Request $request)
    {
        return CompanyMembership::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->whereIn('role', [
                'company_admin',
                'manager',
            ])
            ->pluck('company_id');
    }

    /**
     * Authorize access to a specific property.
     */
    private function authorizeProperty(
        Request $request,
        Property $property
    ): void {
        $authorized = CompanyMembership::query()
            ->where('user_id', $request->user()->id)
            ->where('company_id', $property->company_id)
            ->where('status', 'active')
            ->whereIn('role', [
                'company_admin',
                'manager',
            ])
            ->exists();

        abort_unless(
            $authorized,
            404,
            'Property not found.'
        );
    }

    /**
     * Shared validation for property details.
     *
     * Company ownership cannot be changed here.
     */
    private function propertyRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'address' => [
                'required',
                'string',
                'max:255',
            ],
            'city' => [
                'required',
                'string',
                'max:255',
            ],
            'state' => [
                'required',
                'string',
                'max:100',
            ],
            'zip_code' => [
                'required',
                'string',
                'max:20',
            ],
        ];
    }

    /**
     * Display active or archived properties.
     *
     * Supported filters:
     * ?status=active
     * ?status=archived
     *
     * Search works with either filter.
     */
    public function index(Request $request)
    {
        $companyIds = $this->manageableCompanyIds($request);

        abort_if(
            $companyIds->isEmpty(),
            403,
            'Only administrators and managers can view properties.'
        );

        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'nullable',
                Rule::in(['active', 'archived']),
            ],
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? 'active';

        $properties = Property::query()
            ->whereIn('company_id', $companyIds)
            ->when(
                $status === 'archived',
                fn ($query) => $query->archived(),
                fn ($query) => $query->active()
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%')
                        ->orWhere('city', 'like', '%' . $search . '%')
                        ->orWhere('state', 'like', '%' . $search . '%')
                        ->orWhere('zip_code', 'like', '%' . $search . '%');
                });
            })
            ->with('company')
            ->withCount([
                'units',
                'units as active_requests_count' => function ($query) {
                    $query->join(
                        'maintenance_requests',
                        'maintenance_requests.unit_id',
                        '=',
                        'units.id'
                    )->whereIn(
                        'maintenance_requests.status',
                        [
                            'open',
                            'assigned',
                            'in_progress',
                        ]
                    );
                },
                'units as completed_requests_count' => function ($query) {
                    $query->join(
                        'maintenance_requests',
                        'maintenance_requests.unit_id',
                        '=',
                        'units.id'
                    )->where(
                        'maintenance_requests.status',
                        'completed'
                    );
                },
            ])
            ->orderBy('name')
            ->get();

        $activeCount = Property::query()
            ->whereIn('company_id', $companyIds)
            ->active()
            ->count();

        $archivedCount = Property::query()
            ->whereIn('company_id', $companyIds)
            ->archived()
            ->count();

        return view('properties.index', [
            'properties' => $properties,
            'search' => $search,
            'status' => $status,
            'activeCount' => $activeCount,
            'archivedCount' => $archivedCount,
        ]);
    }

    /**
     * Display the Add Property form.
     */
    public function create(Request $request)
    {
        $companyIds = $this->manageableCompanyIds($request);

        abort_if(
            $companyIds->isEmpty(),
            403,
            'Only administrators and managers can create properties.'
        );

        $companies = Company::query()
            ->whereIn('id', $companyIds)
            ->orderBy('name')
            ->get();

        return view('properties.create', [
            'companies' => $companies,
        ]);
    }

    /**
     * Validate and save a new property.
     */
    public function store(Request $request)
    {
        $companyIds = $this->manageableCompanyIds($request);

        abort_if(
            $companyIds->isEmpty(),
            403,
            'Only administrators and managers can create properties.'
        );

        $validated = $request->validate(
            array_merge(
                [
                    'company_id' => [
                        'required',
                        'integer',
                        Rule::in($companyIds->all()),
                    ],
                ],
                $this->propertyRules()
            )
        );

        $property = Property::create($validated);

        return redirect()
            ->route('properties.show', $property)
            ->with(
                'success',
                'Property created successfully.'
            );
    }

    /**
     * Display a single property and its units.
     *
     * Archived properties remain accessible
     * to authorized managers for recordkeeping.
     */
    public function show(
        Request $request,
        Property $property
    ) {
        $this->authorizeProperty($request, $property);

        $property->load([
            'company',
            'units.maintenanceRequests.assignedTechnician',
        ]);

        return view('properties.show', [
            'property' => $property,
        ]);
    }

    /**
     * Display the Edit Property form.
     */
    public function edit(
        Request $request,
        Property $property
    ) {
        $this->authorizeProperty($request, $property);

        abort_if(
            $property->isArchived(),
            409,
            'Restore this property before editing it.'
        );

        $property->load('company');

        return view('properties.edit', [
            'property' => $property,
        ]);
    }

    /**
     * Validate and update an active property.
     */
    public function update(
        Request $request,
        Property $property
    ) {
        $this->authorizeProperty($request, $property);

        abort_if(
            $property->isArchived(),
            409,
            'Restore this property before editing it.'
        );

        $validated = $request->validate(
            $this->propertyRules()
        );

        $property->update($validated);

        return redirect()
            ->route('properties.show', $property)
            ->with(
                'success',
                'Property information updated successfully.'
            );
    }

    /**
     * Archive an active property.
     *
     * No units or maintenance records are deleted.
     */
    public function archive(
        Request $request,
        Property $property
    ) {
        $this->authorizeProperty($request, $property);

        if ($property->isArchived()) {
            return redirect()
                ->route('properties.show', $property)
                ->with(
                    'success',
                    'This property is already archived.'
                );
        }

        $property->archive();

        return redirect()
            ->route('properties.index', [
                'status' => 'archived',
            ])
            ->with(
                'success',
                'Property archived successfully. All units and maintenance history have been preserved.'
            );
    }

    /**
     * Restore an archived property.
     */
    public function restore(
        Request $request,
        Property $property
    ) {
        $this->authorizeProperty($request, $property);

        if (! $property->isArchived()) {
            return redirect()
                ->route('properties.show', $property)
                ->with(
                    'success',
                    'This property is already active.'
                );
        }

        $property->restoreFromArchive();

        return redirect()
            ->route('properties.show', $property)
            ->with(
                'success',
                'Property restored successfully.'
            );
    }
}
