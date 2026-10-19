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

class MaintenanceAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(): Company
    {
        return Company::create([
            'name' => 'Test Property Company',
            'slug' => 'company-' . uniqid(),
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
        string $priority = 'medium'
    ): MaintenanceRequest {
        $property = Property::create([
            'company_id' => $company->id,
            'name' => 'Test Property ' . uniqid(),
            'address' => '100 Main Street',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
        ]);

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'floor' => 1,
        ]);

        return MaintenanceRequest::create([
            'unit_id' => $unit->id,
            'title' => 'Repair leaking sink',
            'description' => 'Kitchen sink is leaking.',
            'priority' => $priority,
            'status' => $status,
            'assigned_to' => $technician?->id,
        ]);
    }

    public function test_guest_cannot_access_analytics(): void
    {
        $this->get(route('analytics.index'))
            ->assertRedirect(route('login'));
    }

    public function test_company_admin_can_view_analytics(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');

        $this->actingAs($admin)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewIs('analytics.index');
    }

    public function test_manager_can_view_analytics(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewIs('analytics.index');
    }

    public function test_technician_cannot_view_analytics(): void
    {
        $company = $this->createCompany();
        $technician = $this->createMember($company, 'technician');

        $this->actingAs($technician)
            ->get(route('analytics.index'))
            ->assertForbidden();
    }

    public function test_user_without_membership_cannot_view_analytics(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('analytics.index'))
            ->assertForbidden();
    }

    public function test_inactive_admin_cannot_view_analytics(): void
    {
        $company = $this->createCompany();

        $admin = $this->createMember(
            $company,
            'company_admin',
            'inactive'
        );

        $this->actingAs($admin)
            ->get(route('analytics.index'))
            ->assertForbidden();
    }

    public function test_analytics_only_includes_authorized_company_data(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();

        $manager = $this->createMember($companyA, 'manager');

        $this->createWorkOrder($companyA);
        $this->createWorkOrder($companyB);
        $this->createWorkOrder($companyB);

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['total'] === 1;
            });
    }

    public function test_manager_with_two_active_companies_sees_only_authorized_analytics(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();
        $companyC = $this->createCompany();

        $manager = $this->createMember($companyA, 'manager');

        CompanyMembership::create([
            'company_id' => $companyB->id,
            'user_id' => $manager->id,
            'role' => 'manager',
            'status' => 'active',
        ]);

        $this->createWorkOrder($companyA, null, 'completed', 'high');

        $this->createWorkOrder($companyB, null, 'open', 'medium');
        $this->createWorkOrder($companyB, null, 'assigned', 'emergency');

        $this->createWorkOrder($companyC, null, 'open', 'low');
        $this->createWorkOrder($companyC, null, 'completed', 'low');

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['total'] === 3
                    && $stats['completed'] === 1
                    && $stats['open'] === 1
                    && $stats['assigned'] === 1;
            })
            ->assertViewHas('priorities', function ($priorities) {
                return $priorities['high'] === 1
                    && $priorities['medium'] === 1
                    && $priorities['emergency'] === 1
                    && $priorities['low'] === 0;
            })
            ->assertViewHas('propertyInsights', function ($rows) use ($companyA, $companyB, $companyC) {
                $companyIds = $rows
                    ->pluck('property.company_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                $expected = [$companyA->id, $companyB->id];
                sort($expected);

                return $rows->count() === 3
                    && $companyIds === $expected
                    && !in_array($companyC->id, $companyIds, true);
            });
    }

    public function test_analytics_calculates_work_order_totals(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');

        $this->createWorkOrder($company, null, 'open');
        $this->createWorkOrder($company, null, 'assigned');
        $this->createWorkOrder($company, null, 'in_progress');
        $this->createWorkOrder($company, null, 'completed');

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['total'] === 4
                    && $stats['open'] === 1
                    && $stats['assigned'] === 1
                    && $stats['in_progress'] === 1
                    && $stats['completed'] === 1
                    && $stats['completion_rate'] === 25.0;
            });
    }

    public function test_analytics_calculates_priority_distribution(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');

        $this->createWorkOrder($company, null, 'open', 'emergency');
        $this->createWorkOrder($company, null, 'open', 'high');
        $this->createWorkOrder($company, null, 'open', 'medium');
        $this->createWorkOrder($company, null, 'open', 'low');

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('priorities', function ($priorities) {
                return $priorities['emergency'] === 1
                    && $priorities['high'] === 1
                    && $priorities['medium'] === 1
                    && $priorities['low'] === 1;
            });
    }

    public function test_analytics_tracks_unassigned_work_orders(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');
        $technician = $this->createMember($company, 'technician');

        $this->createWorkOrder($company);
        $this->createWorkOrder($company, $technician, 'assigned');

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['unassigned'] === 1;
            });
    }

    public function test_analytics_calculates_technician_performance(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');
        $technician = $this->createMember($company, 'technician');

        $this->createWorkOrder($company, $technician, 'assigned');
        $this->createWorkOrder($company, $technician, 'completed');

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('technicianPerformance', function ($rows) use ($technician) {
                return $rows->count() === 1
                    && $rows->first()['technician']->id === $technician->id
                    && $rows->first()['total'] === 2
                    && $rows->first()['completed'] === 1
                    && $rows->first()['active'] === 1
                    && $rows->first()['completion_rate'] === 50.0;
            });
    }

    public function test_analytics_includes_property_insights(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');

        $this->createWorkOrder($company, null, 'open', 'emergency');

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('propertyInsights', function ($rows) {
                return $rows->count() === 1
                    && $rows->first()['total'] === 1
                    && $rows->first()['active'] === 1
                    && $rows->first()['emergency'] === 1;
            });
    }

    public function test_analytics_handles_no_work_orders(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['total'] === 0
                    && $stats['completed'] === 0
                    && $stats['completion_rate'] === 0;
            });
    }

    public function test_analytics_provides_six_months_of_trends(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('monthlyTrends', function ($trends) {
                return $trends->count() === 6;
            });
    }
}
