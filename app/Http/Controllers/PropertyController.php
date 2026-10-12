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
     * Company ownership is deliberately excluded
     * from editing to prevent accidental transfers
     * between companies.
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
     * Display properties with optional search.
     */
    public function index(Request $request)
    {
        $companyIds = $this->manageableCompanyIds($request);

        abort_if(
            $companyIds->isEmpty(),
            403,
            'Only administrators and managers can view properties.'
        );

        $search = trim((string) $request->query('search', ''));

        $properties = Property::query()
            ->whereIn('company_id', $companyIds)
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
                    )->whereIn('maintenance_requests.status', [
                        'open',
                        'assigned',
                        'in_progress',
                    ]);
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

        return view('properties.index', [
            'properties' => $properties,
            'search' => $search,
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

        $property->load('company');

        return view('properties.edit', [
            'property' => $property,
        ]);
    }

    /**
     * Validate and update an existing property.
     */
    public function update(
        Request $request,
        Property $property
    ) {
        $this->authorizeProperty($request, $property);

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
}
