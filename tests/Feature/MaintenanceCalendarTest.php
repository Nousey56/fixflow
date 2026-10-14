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

class MaintenanceCalendarTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(): Company
    {
        return Company::create([
            'name' => 'Calendar Test Company',
            'slug' => 'calendar-company-' . uniqid(),
            'status' => 'active',
        ]);
    }

    private function createMember(
        Company $company,
        string $role,
        string $status = 'active'
    ): User {
        $user = User::factory()->create();

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => $status,
        ]);

        return $user;
    }

    private function createWorkOrder(
        Company $company,
        ?string $start = null,
        ?string $end = null,
        string $status = 'open',
        ?User $technician = null,
        string $title = 'Calendar Test Repair'
    ): MaintenanceRequest {
        $property = Property::create([
            'company_id' => $company->id,
            'name' => 'Calendar Test Property',
            'address' => '100 Main Street',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
            'status' => 'active',
        ]);

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'floor' => 1,
        ]);

        return MaintenanceRequest::create([
            'unit_id' => $unit->id,
            'title' => $title,
            'description' => 'Maintenance calendar test.',
            'priority' => 'medium',
            'status' => $status,
            'assigned_to' => $technician?->id,
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $end,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('calendar.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_administrator_can_view_calendar(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $response = $this->actingAs($admin)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertOk();
        $response->assertViewIs('calendar.index');
        $response->assertSee('October 2026');
    }

    public function test_manager_can_view_calendar(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');

        $response = $this->actingAs($manager)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertOk();
        $response->assertViewIs('calendar.index');
    }

    public function test_technician_cannot_view_calendar(): void
    {
        $company = $this->createCompany();
        $technician = $this->createMember($company, 'technician');

        $response = $this->actingAs($technician)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertForbidden();
    }

    public function test_inactive_manager_cannot_view_calendar(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember(
            $company,
            'manager',
            'inactive'
        );

        $response = $this->actingAs($manager)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertForbidden();
    }

    public function test_calendar_only_displays_own_company_work_orders(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();

        $admin = $this->createMember($companyA, 'company_admin');

        $this->createWorkOrder(
            $companyA,
            '2026-10-14 09:00:00',
            '2026-10-14 12:00:00',
            'assigned',
            null,
            'Authorized Calendar Repair'
        );

        $this->createWorkOrder(
            $companyB,
            '2026-10-15 09:00:00',
            '2026-10-15 12:00:00',
            'assigned',
            null,
            'Private Company Repair'
        );

        $response = $this->actingAs($admin)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertOk();
        $response->assertSee('Authorized Calendar Repair');
        $response->assertDontSee('Private Company Repair');

        $this->assertCount(
            1,
            $response->viewData('appointments')
        );
    }

    public function test_calendar_only_loads_selected_month_appointments(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $this->createWorkOrder(
            $company,
            '2026-10-14 09:00:00',
            '2026-10-14 12:00:00',
            'open',
            null,
            'October Repair'
        );

        $this->createWorkOrder(
            $company,
            '2026-11-14 09:00:00',
            '2026-11-14 12:00:00',
            'open',
            null,
            'November Repair'
        );

        $response = $this->actingAs($admin)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertOk();
        $response->assertSee('October Repair');
        $response->assertDontSee('November Repair');

        $this->assertCount(
            1,
            $response->viewData('appointments')
        );
    }

    public function test_multiday_appointment_appears_on_every_scheduled_day(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $job = $this->createWorkOrder(
            $company,
            '2026-10-14 09:00:00',
            '2026-10-16 17:00:00',
            'assigned'
        );

        $response = $this->actingAs($admin)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertOk();

        $appointmentsByDate = $response->viewData(
            'appointmentsByDate'
        );

        foreach ([
            '2026-10-14',
            '2026-10-15',
            '2026-10-16',
        ] as $date) {
            $this->assertArrayHasKey(
                $date,
                $appointmentsByDate
            );

            $this->assertContains(
                $job->id,
                array_map(
                    fn ($appointment) => $appointment->id,
                    $appointmentsByDate[$date]
                )
            );
        }

        $this->assertArrayNotHasKey(
            '2026-10-17',
            $appointmentsByDate
        );
    }

    public function test_appointment_overlapping_month_boundary_is_included(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $job = $this->createWorkOrder(
            $company,
            '2026-09-30 18:00:00',
            '2026-10-02 10:00:00',
            'assigned'
        );

        $response = $this->actingAs($admin)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertOk();

        $appointments = $response->viewData('appointments');

        $this->assertTrue(
            $appointments->contains('id', $job->id)
        );

        $appointmentsByDate = $response->viewData(
            'appointmentsByDate'
        );

        $this->assertArrayHasKey(
            '2026-10-01',
            $appointmentsByDate
        );

        $this->assertArrayHasKey(
            '2026-10-02',
            $appointmentsByDate
        );
    }

    public function test_unscheduled_work_orders_are_excluded(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $this->createWorkOrder(
            $company,
            null,
            null,
            'open',
            null,
            'Unscheduled Repair'
        );

        $response = $this->actingAs($admin)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertOk();
        $response->assertDontSee('Unscheduled Repair');

        $this->assertCount(
            0,
            $response->viewData('appointments')
        );
    }

    public function test_calendar_statistics_are_correct(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $technician = $this->createMember($company, 'technician');

        $this->createWorkOrder(
            $company,
            '2026-10-10 09:00:00',
            '2026-10-10 12:00:00',
            'assigned',
            $technician
        );

        $this->createWorkOrder(
            $company,
            '2026-10-12 09:00:00',
            '2026-10-12 12:00:00',
            'completed',
            $technician
        );

        $this->createWorkOrder(
            $company,
            '2026-10-15 09:00:00',
            '2026-10-15 12:00:00',
            'open'
        );

        $response = $this->actingAs($admin)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertOk();

        $stats = $response->viewData('stats');

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['scheduled']);
        $this->assertEquals(1, $stats['completed']);
        $this->assertEquals(1, $stats['unassigned']);
    }

    public function test_calendar_provides_previous_and_next_month(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $response = $this->actingAs($admin)->get(
            route('calendar.index', ['month' => '2026-10'])
        );

        $response->assertOk();

        $this->assertEquals(
            '2026-09',
            $response->viewData('previousMonth')
        );

        $this->assertEquals(
            '2026-11',
            $response->viewData('nextMonth')
        );
    }

    public function test_invalid_month_is_rejected(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $response = $this->actingAs($admin)->get(
            route('calendar.index', ['month' => 'not-a-month'])
        );

        $response->assertSessionHasErrors('month');
    }
}
