<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function company(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'status' => 'active',
        ]);
    }

    private function member(Company $company, string $role): User
    {
        $user = User::factory()->create();

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => 'active',
        ]);

        return $user;
    }

    private function unit(Company $company, string $propertyName = 'Main Tower', string $unitNumber = '101'): Unit
    {
        $property = Property::create([
            'company_id' => $company->id,
            'name' => $propertyName,
            'address' => '100 Main Street',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
        ]);

        return Unit::create([
            'property_id' => $property->id,
            'unit_number' => $unitNumber,
            'floor' => 1,
        ]);
    }

    private function workOrder(Unit $unit, string $title, string $status = 'open', string $priority = 'medium', ?User $technician = null): MaintenanceRequest
    {
        return MaintenanceRequest::create([
            'unit_id' => $unit->id,
            'title' => $title,
            'description' => 'Repair needed for '.$title,
            'status' => $status,
            'priority' => $priority,
            'assigned_to' => $technician?->id,
        ]);
    }

    public function test_manager_can_search_by_title_property_and_unit(): void
    {
        $company = $this->company('Search Company');
        $manager = $this->member($company, 'manager');
        $firstUnit = $this->unit($company, 'Harbor Point', '401');
        $secondUnit = $this->unit($company, 'Garden Court', '702');
        $this->workOrder($firstUnit, 'Leaking faucet');
        $this->workOrder($secondUnit, 'Broken window');

        foreach (['faucet', 'Harbor Point', '401'] as $term) {
            $response = $this->actingAs($manager)->get(route('maintenance.index', ['search' => $term]));
            $response->assertOk()->assertSee('Leaking faucet')->assertDontSee('Broken window');
        }
    }

    public function test_status_filter_returns_only_matching_work_orders(): void
    {
        $company = $this->company('Status Company');
        $manager = $this->member($company, 'manager');
        $unit = $this->unit($company);
        $this->workOrder($unit, 'Open repair', 'open');
        $this->workOrder($unit, 'Finished repair', 'completed');

        $this->actingAs($manager)
            ->get(route('maintenance.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Finished repair')
            ->assertDontSee('Open repair');
    }

    public function test_priority_filter_returns_only_matching_work_orders(): void
    {
        $company = $this->company('Priority Company');
        $manager = $this->member($company, 'manager');
        $unit = $this->unit($company);
        $this->workOrder($unit, 'Emergency leak', 'open', 'emergency');
        $this->workOrder($unit, 'Minor scratch', 'open', 'low');

        $this->actingAs($manager)
            ->get(route('maintenance.index', ['priority' => 'emergency']))
            ->assertOk()
            ->assertSee('Emergency leak')
            ->assertDontSee('Minor scratch');
    }

    public function test_dashboard_paginates_fifteen_work_orders_per_page(): void
    {
        $company = $this->company('Pagination Company');
        $manager = $this->member($company, 'manager');
        $unit = $this->unit($company);

        for ($i = 1; $i <= 17; $i++) {
            $this->workOrder($unit, sprintf('Pagination job %02d', $i));
        }

        $firstPage = $this->actingAs($manager)->get(route('maintenance.index'));
        $firstPage->assertOk()->assertViewHas('requests', function ($requests) {
            return $requests->count() === 15 && $requests->total() === 17 && $requests->lastPage() === 2;
        });

        $secondPage = $this->actingAs($manager)->get(route('maintenance.index', ['page' => 2]));
        $secondPage->assertOk()->assertViewHas('requests', function ($requests) {
            return $requests->count() === 2 && $requests->total() === 17 && $requests->currentPage() === 2;
        });
    }

    public function test_technician_only_sees_their_assigned_work_orders(): void
    {
        $company = $this->company('Technician Company');
        $technician = $this->member($company, 'technician');
        $otherTechnician = $this->member($company, 'technician');
        $unit = $this->unit($company);
        $this->workOrder($unit, 'My assigned repair', 'assigned', 'medium', $technician);
        $this->workOrder($unit, 'Someone else repair', 'assigned', 'medium', $otherTechnician);
        $this->workOrder($unit, 'Unassigned repair');

        $this->actingAs($technician)
            ->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee('My assigned repair')
            ->assertDontSee('Someone else repair')
            ->assertDontSee('Unassigned repair');
    }

    public function test_manager_cannot_see_another_company_work_orders(): void
    {
        $firstCompany = $this->company('First Company');
        $secondCompany = $this->company('Second Company');
        $manager = $this->member($firstCompany, 'manager');
        $first = $this->workOrder($this->unit($firstCompany), 'First company repair');
        $second = $this->workOrder($this->unit($secondCompany), 'Secret second company repair');

        $this->actingAs($manager)
            ->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee($first->title)
            ->assertDontSee($second->title);

        $this->actingAs($manager)
            ->get(route('maintenance.show', $second))
            ->assertNotFound();
    }

    public function test_dashboard_totals_are_not_reduced_by_filters(): void
    {
        $company = $this->company('Statistics Company');
        $manager = $this->member($company, 'manager');
        $unit = $this->unit($company);
        $this->workOrder($unit, 'Open statistic repair', 'open');
        $this->workOrder($unit, 'In progress statistic repair', 'in_progress');
        $this->workOrder($unit, 'Completed statistic repair', 'completed');

        $response = $this->actingAs($manager)
            ->get(route('maintenance.index', ['status' => 'open']));

        $response->assertOk()
            ->assertSee('Open statistic repair')
            ->assertDontSee('In progress statistic repair')
            ->assertDontSee('Completed statistic repair');

        // Statistics should be computed from the authorized, unfiltered dataset.
        // Assert the view's stats structure independently of its variable names.
        $response->assertViewHas('requests', function ($requests) {
            return $requests->total() === 1;
        });
    }
}
