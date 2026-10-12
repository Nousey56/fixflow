<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    /**
     * Verify that the current user is an active
     * administrator or manager of the property's company.
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
     * Verify that a unit belongs to the property
     * specified in the URL.
     */
    private function authorizeUnit(
        Property $property,
        Unit $unit
    ): void {
        abort_unless(
            (int) $unit->property_id === (int) $property->id,
            404,
            'Unit not found in this property.'
        );
    }

    /**
     * Display the Add Unit form.
     */
    public function create(
        Request $request,
        Property $property
    ) {
        $this->authorizeProperty($request, $property);

        return view('units.create', [
            'property' => $property,
        ]);
    }

    /**
     * Validate and save a new unit.
     */
    public function store(
        Request $request,
        Property $property
    ) {
        $this->authorizeProperty($request, $property);

        $validated = $request->validate([
            'unit_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'unit_number')
                    ->where('property_id', $property->id),
            ],
            'floor' => [
                'nullable',
                'integer',
                'between:-10,300',
            ],
        ]);

        $unit = $property->units()->create($validated);

        return redirect()
            ->route('properties.show', $property)
            ->with(
                'success',
                "Unit {$unit->unit_number} was added successfully."
            );
    }

    /**
     * Display the Edit Unit form.
     */
    public function edit(
        Request $request,
        Property $property,
        Unit $unit
    ) {
        $this->authorizeProperty($request, $property);
        $this->authorizeUnit($property, $unit);

        return view('units.edit', [
            'property' => $property,
            'unit' => $unit,
        ]);
    }

    /**
     * Validate and save changes to an existing unit.
     */
    public function update(
        Request $request,
        Property $property,
        Unit $unit
    ) {
        $this->authorizeProperty($request, $property);
        $this->authorizeUnit($property, $unit);

        $validated = $request->validate([
            'unit_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'unit_number')
                    ->where('property_id', $property->id)
                    ->ignore($unit->id),
            ],
            'floor' => [
                'nullable',
                'integer',
                'between:-10,300',
            ],
        ]);

        $unit->update($validated);

        return redirect()
            ->route('properties.show', $property)
            ->with(
                'success',
                "Unit {$unit->unit_number} was updated successfully."
            );
    }
}
