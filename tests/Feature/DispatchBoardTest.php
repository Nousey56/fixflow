<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DispatchBoardTest extends TestCase
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

    private function unit(
        Company $company,
        string $propertyName = 'Main Tower',
        string $unitNumber = '101'
    ): Unit {
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

    private function workOrder(
        Unit $unit,
        string $title,
        ?string $scheduledStart = null,
        string $status = 'open',
        string $priority = 'medium',
        ?User $technician = null
    ): MaintenanceRequest {
        return MaintenanceRequest::create([
            'unit_id' => $unit->id,
            'title' => $title,
            'description' => 'Repair needed for ' . $title,
            'status' => $status,
            'priority' => $priority,
            'assigned_to' => $technician?->id,
            'scheduled_start_at' => $scheduledStart,
            'scheduled_end_at' => $scheduledStart
                ? Carbon::parse($scheduledStart)->addHour()
                : null,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dispatch.index'))
            ->assertRedirect(route('login'));
    }

    public function test_company_administrator_can_view_dispatch_board(): void
    {
        $company = $this->company('Admin Company');
        $admin = $this->member($company, 'company_admin');

        $this->actingAs($admin)
            ->get(route('dispatch.index'))
            ->assertOk()
            ->assertSee('Dispatch Board');
    }

    public function test_manager_can_view_dispatch_board(): void
    {
        $company = $this->company('Manager Company');
        $manager = $this->member($company, 'manager');

        $this->actingAs($manager)
            ->get(route('dispatch.index'))
            ->assertOk()
            ->assertSee('Manage dispatch');
    }

    public function test_technician_cannot_access_dispatch_board(): void
    {
        $company = $this->company('Technician Company');
        $technician = $this->member($company, 'technician');

        $this->actingAs($technician)
            ->get(route('dispatch.index'))
            ->assertForbidden();
    }

    public function test_manager_cannot_see_other_company_work_orders(): void
    {
        $firstCompany = $this->company('First Dispatch Company');
        $secondCompany = $this->company('Second Dispatch Company');

        $manager = $this->member($firstCompany, 'manager');

        $this->workOrder(
            $this->unit($firstCompany),
            'My company repair',
            now()->addDays(2)->toDateTimeString()
        );

        $this->workOrder(
            $this->unit($secondCompany),
            'Secret company repair',
            now()->addDays(2)->toDateTimeString()
        );

        $this->actingAs($manager)
            ->get(route('dispatch.index'))
            ->assertOk()
            ->assertSee('My company repair')
            ->assertDontSee('Secret company repair');
    }

    public function test_upcoming_view_only_shows_future_active_appointments(): void
    {
        $company = $this->company('Upcoming Company');
        $manager = $this->member($company, 'manager');
        $unit = $this->unit($company);

        $this->workOrder(
            $unit,
            'Future appointment',
            now()->addDays(2)->toDateTimeString()
        );

        $this->workOrder(
            $unit,
            'Past appointment',
            now()->subDays(2)->toDateTimeString()
        );

        $this->workOrder(
            $unit,
            'Unscheduled appointment'
        );

        $this->workOrder(
            $unit,
            'Completed future appointment',
            now()->addDays(3)->toDateTimeString(),
            'completed'
        );

        $this->actingAs($manager)
            ->get(route('dispatch.index'))
            ->assertOk()
            ->assertSee('Future appointment')
            ->assertDontSee('Past appointment')
            ->assertDontSee('Unscheduled appointment')
            ->assertDontSee('Completed future appointment');
    }

    public function test_unscheduled_view_only_shows_active_unscheduled_jobs(): void
    {
        $company = $this->company('Unscheduled Company');
        $manager = $this->member($company, 'manager');
        $unit = $this->unit($company);

        $this->workOrder($unit, 'Needs scheduling');

        $this->workOrder(
            $unit,
            'Already scheduled',
            now()->addDay()->toDateTimeString()
        );

        $this->workOrder(
            $unit,
            'Completed unscheduled',
            null,
            'completed'
        );

        $this->actingAs($manager)
            ->get(route('dispatch.index', ['view' => 'unscheduled']))
            ->assertOk()
            ->assertSee('Needs scheduling')
            ->assertDontSee('Already scheduled')
            ->assertDontSee('Completed unscheduled');
    }

    public function test_search_filters_by_title_property_and_unit(): void
    {
        $company = $this->company('Search Dispatch Company');
        $manager = $this->member($company, 'manager');

        $firstUnit = $this->unit($company, 'Harbor Point', '401');
        $secondUnit = $this->unit($company, 'Garden Court', '702');

        $this->workOrder($firstUnit, 'Leaking faucet');
        $this->workOrder($secondUnit, 'Broken window');

        foreach (['faucet', 'Harbor Point', '401'] as $term) {
            $this->actingAs($manager)
                ->get(route('dispatch.index', [
                    'view' => 'all',
                    'search' => $term,
                ]))
                ->assertOk()
                ->assertSee('Leaking faucet')
                ->assertDontSee('Broken window');
        }
    }

    public function test_priority_and_status_filters_work(): void
    {
        $company = $this->company('Filter Dispatch Company');
        $manager = $this->member($company, 'manager');
        $unit = $this->unit($company);

        $this->workOrder(
            $unit,
            'Emergency active repair',
            null,
            'open',
            'emergency'
        );

        $this->workOrder(
            $unit,
            'Low priority completed repair',
            null,
            'completed',
            'low'
        );

        $this->actingAs($manager)
            ->get(route('dispatch.index', [
                'view' => 'all',
                'status' => 'open',
                'priority' => 'emergency',
            ]))
            ->assertOk()
            ->assertSee('Emergency active repair')
            ->assertDontSee('Low priority completed repair');
    }

    public function test_technician_filter_only_shows_assigned_jobs(): void
    {
        $company = $this->company('Assignment Filter Company');
        $manager = $this->member($company, 'manager');
        $firstTechnician = $this->member($company, 'technician');
        $secondTechnician = $this->member($company, 'technician');
        $unit = $this->unit($company);

        $this->workOrder(
            $unit,
            'First technician job',
            null,
            'assigned',
            'medium',
            $firstTechnician
        );

        $this->workOrder(
            $unit,
            'Second technician job',
            null,
            'assigned',
            'medium',
            $secondTechnician
        );

        $this->actingAs($manager)
            ->get(route('dispatch.index', [
                'view' => 'all',
                'technician' => $firstTechnician->id,
            ]))
            ->assertOk()
            ->assertSee('First technician job')
            ->assertDontSee('Second technician job');
    }

    public function test_dispatch_board_paginates_fifteen_work_orders(): void
    {
        $company = $this->company('Pagination Dispatch Company');
        $manager = $this->member($company, 'manager');
        $unit = $this->unit($company);

        for ($i = 1; $i <= 17; $i++) {
            $this->workOrder(
                $unit,
                sprintf('Dispatch job %02d', $i)
            );
        }

        $this->actingAs($manager)
            ->get(route('dispatch.index', ['view' => 'all']))
            ->assertOk()
            ->assertViewHas('workOrders', function ($workOrders) {
                return $workOrders->count() === 15
                    && $workOrders->total() === 17
                    && $workOrders->lastPage() === 2;
            });

        $this->actingAs($manager)
            ->get(route('dispatch.index', [
                'view' => 'all',
                'page' => 2,
            ]))
            ->assertOk()
            ->assertViewHas('workOrders', function ($workOrders) {
                return $workOrders->count() === 2
                    && $workOrders->total() === 17
                    && $workOrders->currentPage() === 2;
            });
    }

    public function test_dispatch_statistics_are_not_reduced_by_filters(): void
    {
        $company = $this->company('Statistics Dispatch Company');
        $manager = $this->member($company, 'manager');
        $unit = $this->unit($company);

        $this->workOrder(
            $unit,
            'Scheduled repair',
            now()->addDays(2)->toDateTimeString()
        );

        $this->workOrder(
            $unit,
            'Unscheduled repair'
        );

        $response = $this->actingAs($manager)
            ->get(route('dispatch.index', [
                'view' => 'all',
                'search' => 'Scheduled repair',
            ]));

        $response->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['total'] === 2
                    && $stats['scheduled'] === 1
                    && $stats['upcoming'] === 1
                    && $stats['unscheduled'] === 1
                    && $stats['unassigned'] === 2;
            });
    }
}
