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

class TechnicianWorkloadTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(): Company
    {
        return Company::create([
            'name' => 'Workload Test Company',
            'slug' => 'workload-company-' . uniqid(),
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
        ?User $technician = null,
        string $status = 'open',
        ?string $start = null,
        ?string $end = null
    ): MaintenanceRequest {
        $property = Property::create([
            'company_id' => $company->id,
            'name' => 'Test Property',
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
            'title' => 'Test Maintenance Job',
            'description' => 'Test workload calculation.',
            'priority' => 'medium',
            'status' => $status,
            'assigned_to' => $technician?->id,
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $end,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('workload.index'))
            ->assertRedirect(route('login'));
    }

    public function test_administrator_can_view_workload_dashboard(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $this->actingAs($admin)
            ->get(route('workload.index'))
            ->assertOk()
            ->assertViewIs('workload.index')
            ->assertSee('Technician Workload');
    }

    public function test_manager_can_view_workload_dashboard(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');

        $this->actingAs($manager)
            ->get(route('workload.index'))
            ->assertOk();
    }

    public function test_technician_cannot_view_workload_dashboard(): void
    {
        $company = $this->createCompany();
        $technician = $this->createMember($company, 'technician');

        $this->actingAs($technician)
            ->get(route('workload.index'))
            ->assertForbidden();
    }

    public function test_inactive_manager_cannot_view_workload_dashboard(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember(
            $company,
            'manager',
            'inactive'
        );

        $this->actingAs($manager)
            ->get(route('workload.index'))
            ->assertForbidden();
    }

    public function test_dashboard_lists_active_company_technicians(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $technician = $this->createMember($company, 'technician');

        $response = $this->actingAs($admin)
            ->get(route('workload.index'));

        $response->assertOk();

        $workloads = $response->viewData('workloads');

        $this->assertCount(1, $workloads);
        $this->assertEquals(
            $technician->id,
            $workloads->first()['technician']->id
        );
    }

    public function test_dashboard_excludes_inactive_technicians(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $this->createMember($company, 'technician', 'inactive');

        $response = $this->actingAs($admin)
            ->get(route('workload.index'));

        $response->assertOk();

        $this->assertCount(
            0,
            $response->viewData('workloads')
        );
    }

    public function test_dashboard_does_not_show_other_company_technicians(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();

        $admin = $this->createMember($companyA, 'company_admin');

        $ownTechnician = $this->createMember(
            $companyA,
            'technician'
        );

        $otherTechnician = $this->createMember(
            $companyB,
            'technician'
        );

        $response = $this->actingAs($admin)
            ->get(route('workload.index'));

        $response->assertOk();

        $workloads = $response->viewData('workloads');

        $this->assertTrue(
            $workloads->contains(
                fn ($row) => $row['technician']->id === $ownTechnician->id
            )
        );

        $this->assertFalse(
            $workloads->contains(
                fn ($row) => $row['technician']->id === $otherTechnician->id
            )
        );
    }

    public function test_workload_counts_are_calculated_correctly(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $technician = $this->createMember($company, 'technician');

        $this->createWorkOrder($company, $technician, 'open');
        $this->createWorkOrder($company, $technician, 'assigned');
        $this->createWorkOrder($company, $technician, 'in_progress');
        $this->createWorkOrder($company, $technician, 'completed');

        $response = $this->actingAs($admin)
            ->get(route('workload.index'));

        $response->assertOk();

        $row = $response->viewData('workloads')->first();

        $this->assertEquals(1, $row['open']);
        $this->assertEquals(1, $row['assigned']);
        $this->assertEquals(1, $row['in_progress']);
        $this->assertEquals(1, $row['completed']);
        $this->assertEquals(3, $row['active']);
        $this->assertEquals(4, $row['total']);
    }

    public function test_workload_levels_use_active_job_thresholds(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $light = $this->createMember($company, 'technician');
        $moderate = $this->createMember($company, 'technician');
        $heavy = $this->createMember($company, 'technician');

        foreach (range(1, 2) as $number) {
            $this->createWorkOrder($company, $light, 'assigned');
        }

        foreach (range(1, 5) as $number) {
            $this->createWorkOrder($company, $moderate, 'assigned');
        }

        foreach (range(1, 8) as $number) {
            $this->createWorkOrder($company, $heavy, 'assigned');
        }

        $response = $this->actingAs($admin)
            ->get(route('workload.index'));

        $response->assertOk();

        $workloads = $response->viewData('workloads');

        $levels = $workloads->mapWithKeys(
            fn ($row) => [
                $row['technician']->id => $row['workload_level'],
            ]
        );

        $this->assertEquals('light', $levels[$light->id]);
        $this->assertEquals('moderate', $levels[$moderate->id]);
        $this->assertEquals('heavy', $levels[$heavy->id]);
    }

    public function test_technicians_are_sorted_by_active_workload(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $lessBusy = $this->createMember($company, 'technician');
        $moreBusy = $this->createMember($company, 'technician');

        $this->createWorkOrder($company, $lessBusy, 'assigned');

        foreach (range(1, 3) as $number) {
            $this->createWorkOrder($company, $moreBusy, 'assigned');
        }

        $response = $this->actingAs($admin)
            ->get(route('workload.index'));

        $response->assertOk();

        $workloads = $response->viewData('workloads');

        $this->assertEquals(
            $moreBusy->id,
            $workloads->first()['technician']->id
        );
    }

    public function test_dashboard_statistics_are_correct(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $technician = $this->createMember($company, 'technician');

        $this->createWorkOrder($company, $technician, 'assigned');
        $this->createWorkOrder($company, $technician, 'completed');
        $this->createWorkOrder($company, null, 'open');

        $response = $this->actingAs($admin)
            ->get(route('workload.index'));

        $response->assertOk();

        $stats = $response->viewData('stats');

        $this->assertEquals(1, $stats['technicians']);
        $this->assertEquals(1, $stats['active_jobs']);
        $this->assertEquals(1, $stats['completed_jobs']);
        $this->assertEquals(0, $stats['heavy_workloads']);
        $this->assertEquals(1, $stats['unassigned_jobs']);
    }

    public function test_next_appointment_is_earliest_future_active_job(): void
    {
        Carbon::setTestNow('2026-10-13 12:00:00');

        try {
            $company = $this->createCompany();
            $admin = $this->createMember($company, 'company_admin');
            $technician = $this->createMember($company, 'technician');

            $this->createWorkOrder(
                $company,
                $technician,
                'assigned',
                '2026-10-20 09:00:00',
                '2026-10-20 12:00:00'
            );

            $nextJob = $this->createWorkOrder(
                $company,
                $technician,
                'assigned',
                '2026-10-15 09:00:00',
                '2026-10-15 12:00:00'
            );

            $this->createWorkOrder(
                $company,
                $technician,
                'completed',
                '2026-10-14 09:00:00',
                '2026-10-14 12:00:00'
            );

            $response = $this->actingAs($admin)
                ->get(route('workload.index'));

            $response->assertOk();

            $row = $response->viewData('workloads')->first();

            $this->assertEquals(
                $nextJob->id,
                $row['next_appointment']->id
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_other_company_work_orders_do_not_affect_statistics(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();

        $admin = $this->createMember($companyA, 'company_admin');

        $ownTechnician = $this->createMember(
            $companyA,
            'technician'
        );

        $otherTechnician = $this->createMember(
            $companyB,
            'technician'
        );

        $this->createWorkOrder(
            $companyA,
            $ownTechnician,
            'assigned'
        );

        $this->createWorkOrder(
            $companyB,
            $otherTechnician,
            'assigned'
        );

        $this->createWorkOrder(
            $companyB,
            null,
            'open'
        );

        $response = $this->actingAs($admin)
            ->get(route('workload.index'));

        $response->assertOk();

        $stats = $response->viewData('stats');

        $this->assertEquals(1, $stats['technicians']);
        $this->assertEquals(1, $stats['active_jobs']);
        $this->assertEquals(0, $stats['unassigned_jobs']);
    }
}
