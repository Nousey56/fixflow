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

class WorkOrderSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(): Company
    {
        return Company::create([
            'name' => 'Security Test Company',
            'slug' => 'security-' . uniqid(),
            'status' => 'active',
        ]);
    }

    private function createMember(
        Company $company,
        string $role,
        string $membershipStatus = 'active'
    ): User {
        $user = User::factory()->create();

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => $membershipStatus,
        ]);

        return $user;
    }

    private function createWorkOrder(
        Company $company,
        string $status = 'open',
        ?User $technician = null
    ): MaintenanceRequest {
        $property = Property::create([
            'company_id' => $company->id,
            'name' => 'Security Test Property',
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
            'title' => 'Repair leaking sink',
            'description' => 'Kitchen sink is leaking.',
            'priority' => 'medium',
            'status' => $status,
            'assigned_to' => $technician?->id,
        ]);
    }

    public function test_guest_cannot_update_work_order(): void
    {
        $company = $this->createCompany();
        $job = $this->createWorkOrder($company);

        $this->patch(
            route('maintenance.update', $job),
            ['status' => 'completed']
        )->assertRedirect(route('login'));

        $this->assertSame('open', $job->fresh()->status);
    }

    public function test_user_without_membership_cannot_update_work_order(): void
    {
        $company = $this->createCompany();
        $user = User::factory()->create();
        $job = $this->createWorkOrder($company);

        $this->actingAs($user)
            ->patch(
                route('maintenance.update', $job),
                ['status' => 'completed']
            )
            ->assertNotFound();

        $this->assertSame('open', $job->fresh()->status);
    }

    public function test_manager_cannot_update_another_companys_work_order(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();

        $manager = $this->createMember($companyA, 'manager');
        $job = $this->createWorkOrder($companyB);

        $this->actingAs($manager)
            ->patch(
                route('maintenance.update', $job),
                ['status' => 'completed']
            )
            ->assertNotFound();

        $this->assertSame('open', $job->fresh()->status);
    }

    public function test_inactive_manager_cannot_update_work_order(): void
    {
        $company = $this->createCompany();

        $manager = $this->createMember(
            $company,
            'manager',
            'inactive'
        );

        $job = $this->createWorkOrder($company);

        $this->actingAs($manager)
            ->patch(
                route('maintenance.update', $job),
                ['status' => 'completed']
            )
            ->assertNotFound();

        $this->assertSame('open', $job->fresh()->status);
    }

    public function test_technician_cannot_change_assigned_job_back_to_open(): void
    {
        $company = $this->createCompany();
        $technician = $this->createMember($company, 'technician');

        $job = $this->createWorkOrder(
            $company,
            'assigned',
            $technician
        );

        $response = $this->actingAs($technician)
            ->patch(
                route('maintenance.update', $job),
                ['status' => 'open']
            );

        $this->assertTrue(
            in_array($response->status(), [403, 422], true),
            'Technician status change must be rejected.'
        );

        $this->assertSame('assigned', $job->fresh()->status);
    }

    public function test_technician_cannot_change_work_order_priority(): void
    {
        $company = $this->createCompany();
        $technician = $this->createMember($company, 'technician');

        $job = $this->createWorkOrder(
            $company,
            'assigned',
            $technician
        );

        $response = $this->actingAs($technician)
            ->patch(
                route('maintenance.update', $job),
                [
                    'status' => 'assigned',
                    'priority' => 'emergency',
                ]
            );

        $this->assertTrue(
            in_array($response->status(), [403, 422], true),
            'Technician priority change must be rejected.'
        );

        $this->assertSame('medium', $job->fresh()->priority);
    }

    public function test_unassigned_technician_cannot_update_another_technicians_job(): void
    {
        $company = $this->createCompany();

        $assignedTechnician = $this->createMember(
            $company,
            'technician'
        );

        $otherTechnician = $this->createMember(
            $company,
            'technician'
        );

        $job = $this->createWorkOrder(
            $company,
            'assigned',
            $assignedTechnician
        );

        $this->actingAs($otherTechnician)
            ->patch(
                route('maintenance.update', $job),
                ['status' => 'open']
            )
            ->assertNotFound();

        $this->assertSame('assigned', $job->fresh()->status);
    }
}
