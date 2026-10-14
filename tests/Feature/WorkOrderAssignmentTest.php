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

class WorkOrderAssignmentTest extends TestCase
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
        string $role
    ): User {
        $user = User::factory()->create();

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => 'active',
        ]);

        return $user;
    }

    private function createWorkOrder(
        Company $company,
        string $status = 'open'
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
            'title' => 'Repair leaking sink',
            'description' => 'Kitchen sink is leaking.',
            'priority' => 'medium',
            'status' => $status,
        ]);
    }

    public function test_guest_cannot_assign_technician(): void
    {
        $company = $this->createCompany();
        $job = $this->createWorkOrder($company);
        $technician = $this->createMember($company, 'technician');

        $response = $this->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $technician->id]
        );

        $response->assertRedirect(route('login'));

        $this->assertNull($job->fresh()->assigned_to);
    }

    public function test_administrator_can_assign_technician(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $technician = $this->createMember($company, 'technician');
        $job = $this->createWorkOrder($company);

        $response = $this->actingAs($admin)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $technician->id]
        );

        $response->assertRedirect(
            route('dispatch.index', ['view' => 'all'])
        );

        $this->assertDatabaseHas('maintenance_requests', [
            'id' => $job->id,
            'assigned_to' => $technician->id,
            'status' => 'assigned',
        ]);

        $this->assertDatabaseHas('maintenance_activities', [
            'maintenance_request_id' => $job->id,
            'old_status' => 'open',
            'new_status' => 'assigned',
        ]);
    }

    public function test_manager_can_assign_technician(): void
    {
        $company = $this->createCompany();
        $manager = $this->createMember($company, 'manager');
        $technician = $this->createMember($company, 'technician');
        $job = $this->createWorkOrder($company);

        $response = $this->actingAs($manager)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $technician->id]
        );

        $response->assertSessionHasNoErrors();

        $this->assertEquals(
            $technician->id,
            $job->fresh()->assigned_to
        );
    }

    public function test_technician_cannot_assign_work_orders(): void
    {
        $company = $this->createCompany();
        $technician = $this->createMember($company, 'technician');
        $job = $this->createWorkOrder($company);

        $response = $this->actingAs($technician)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $technician->id]
        );

        $response->assertNotFound();

        $this->assertNull($job->fresh()->assigned_to);
    }

    public function test_administrator_cannot_assign_another_companys_job(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();

        $admin = $this->createMember($companyA, 'company_admin');
        $technician = $this->createMember($companyA, 'technician');
        $job = $this->createWorkOrder($companyB);

        $response = $this->actingAs($admin)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $technician->id]
        );

        $response->assertNotFound();

        $this->assertNull($job->fresh()->assigned_to);
    }

    public function test_cannot_assign_technician_from_another_company(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();

        $admin = $this->createMember($companyA, 'company_admin');
        $otherTechnician = $this->createMember($companyB, 'technician');
        $job = $this->createWorkOrder($companyA);

        $response = $this->actingAs($admin)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $otherTechnician->id]
        );

        $response->assertSessionHasErrors('technician_id');

        $this->assertNull($job->fresh()->assigned_to);
    }

    public function test_completed_work_order_cannot_be_reassigned(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $technician = $this->createMember($company, 'technician');
        $job = $this->createWorkOrder($company, 'completed');

        $response = $this->actingAs($admin)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $technician->id]
        );

        $response->assertSessionHasErrors('technician_id');

        $this->assertNull($job->fresh()->assigned_to);
    }

    public function test_unassigning_technician_returns_job_to_open(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $technician = $this->createMember($company, 'technician');
        $job = $this->createWorkOrder($company, 'assigned');

        $job->update([
            'assigned_to' => $technician->id,
        ]);

        $response = $this->actingAs($admin)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => '']
        );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('maintenance_requests', [
            'id' => $job->id,
            'assigned_to' => null,
            'status' => 'open',
        ]);
    }

    public function test_reassignment_preserves_existing_status(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $firstTechnician = $this->createMember($company, 'technician');
        $secondTechnician = $this->createMember($company, 'technician');
        $job = $this->createWorkOrder($company, 'in_progress');

        $job->update([
            'assigned_to' => $firstTechnician->id,
        ]);

        $response = $this->actingAs($admin)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $secondTechnician->id]
        );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('maintenance_requests', [
            'id' => $job->id,
            'assigned_to' => $secondTechnician->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_reassignment_creates_activity_history(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $firstTechnician = $this->createMember($company, 'technician');
        $secondTechnician = $this->createMember($company, 'technician');
        $job = $this->createWorkOrder($company, 'assigned');

        $job->update([
            'assigned_to' => $firstTechnician->id,
        ]);

        $this->actingAs($admin)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $secondTechnician->id]
        );

        $this->assertEquals(
            1,
            $job->activities()->count()
        );

        $activity = $job->activities()->first();

        $this->assertStringContainsString(
            'Technician reassigned',
            $activity->work_notes
        );

        $this->assertStringContainsString(
            $firstTechnician->name,
            $activity->work_notes
        );

        $this->assertStringContainsString(
            $secondTechnician->name,
            $activity->work_notes
        );
    }

    public function test_assigning_same_technician_does_not_duplicate_history(): void
    {
        $company = $this->createCompany();
        $admin = $this->createMember($company, 'company_admin');
        $technician = $this->createMember($company, 'technician');
        $job = $this->createWorkOrder($company, 'assigned');

        $job->update([
            'assigned_to' => $technician->id,
        ]);

        $this->actingAs($admin)->patch(
            route('maintenance.assignment.update', $job),
            ['technician_id' => $technician->id]
        );

        $this->assertEquals(
            0,
            $job->activities()->count()
        );
    }
}
