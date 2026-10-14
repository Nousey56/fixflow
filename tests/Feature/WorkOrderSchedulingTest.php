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

class WorkOrderSchedulingTest extends TestCase
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

    private function unit(Company $company): Unit
    {
        $property = Property::create([
            'company_id' => $company->id,
            'name' => 'Main Tower',
            'address' => '100 Main Street',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
        ]);

        return Unit::create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'floor' => 1,
        ]);
    }

    private function workOrder(
        Unit $unit,
        string $status = 'open'
    ): MaintenanceRequest {
        return MaintenanceRequest::create([
            'unit_id' => $unit->id,
            'title' => 'Repair leaking faucet',
            'description' => 'Kitchen faucet needs repair.',
            'status' => $status,
            'priority' => 'medium',
        ]);
    }

    private function scheduleData(): array
    {
        return [
            'scheduled_start_at' => '2026-11-10 10:00:00',
            'scheduled_end_at' => '2026-11-10 12:00:00',
            'scheduling_notes' => 'Contact tenant before arrival.',
        ];
    }

    public function test_company_admin_can_schedule_work_order(): void
    {
        $company = $this->company('Admin Scheduling');
        $admin = $this->member($company, 'company_admin');
        $job = $this->workOrder($this->unit($company));

        $this->actingAs($admin)
            ->patch(route('maintenance.schedule.update', $job), $this->scheduleData())
            ->assertRedirect(route('maintenance.show', $job))
            ->assertSessionHas('success');

        $job->refresh();

        $this->assertSame(
            '2026-11-10 10:00:00',
            $job->scheduled_start_at->format('Y-m-d H:i:s')
        );

        $this->assertSame(
            '2026-11-10 12:00:00',
            $job->scheduled_end_at->format('Y-m-d H:i:s')
        );

        $this->assertSame(
            'Contact tenant before arrival.',
            $job->scheduling_notes
        );
    }

    public function test_manager_can_reschedule_existing_appointment(): void
    {
        $company = $this->company('Manager Scheduling');
        $manager = $this->member($company, 'manager');
        $job = $this->workOrder($this->unit($company));

        $job->update([
            'scheduled_start_at' => '2026-11-10 08:00:00',
            'scheduled_end_at' => '2026-11-10 09:00:00',
        ]);

        $this->actingAs($manager)
            ->patch(route('maintenance.schedule.update', $job), $this->scheduleData())
            ->assertRedirect(route('maintenance.show', $job));

        $job->refresh();

        $this->assertSame(
            '2026-11-10 10:00:00',
            $job->scheduled_start_at->format('Y-m-d H:i:s')
        );

        $this->assertSame(
            '2026-11-10 12:00:00',
            $job->scheduled_end_at->format('Y-m-d H:i:s')
        );
    }

    public function test_technician_cannot_schedule_work_order(): void
    {
        $company = $this->company('Technician Scheduling');
        $technician = $this->member($company, 'technician');
        $job = $this->workOrder($this->unit($company));

        $this->actingAs($technician)
            ->patch(route('maintenance.schedule.update', $job), $this->scheduleData())
            ->assertNotFound();

        $job->refresh();

        $this->assertNull($job->scheduled_start_at);
        $this->assertNull($job->scheduled_end_at);
    }

    public function test_manager_cannot_schedule_another_company_work_order(): void
    {
        $firstCompany = $this->company('First Scheduling Company');
        $secondCompany = $this->company('Second Scheduling Company');

        $manager = $this->member($firstCompany, 'manager');
        $job = $this->workOrder($this->unit($secondCompany));

        $this->actingAs($manager)
            ->patch(route('maintenance.schedule.update', $job), $this->scheduleData())
            ->assertNotFound();

        $this->assertNull($job->fresh()->scheduled_start_at);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $company = $this->company('Date Validation Company');
        $manager = $this->member($company, 'manager');
        $job = $this->workOrder($this->unit($company));

        $data = $this->scheduleData();
        $data['scheduled_end_at'] = '2026-11-10 09:00:00';

        $this->actingAs($manager)
            ->from(route('maintenance.show', $job))
            ->patch(route('maintenance.schedule.update', $job), $data)
            ->assertRedirect(route('maintenance.show', $job))
            ->assertSessionHasErrors('scheduled_end_at');

        $this->assertNull($job->fresh()->scheduled_start_at);
    }

    public function test_completed_work_order_cannot_be_rescheduled(): void
    {
        $company = $this->company('Completed Scheduling Company');
        $manager = $this->member($company, 'manager');
        $job = $this->workOrder($this->unit($company), 'completed');

        $this->actingAs($manager)
            ->from(route('maintenance.show', $job))
            ->patch(route('maintenance.schedule.update', $job), $this->scheduleData())
            ->assertRedirect(route('maintenance.show', $job))
            ->assertSessionHasErrors('scheduled_start_at');

        $this->assertNull($job->fresh()->scheduled_start_at);
    }

    public function test_scheduling_creates_activity_history(): void
    {
        $company = $this->company('Activity Scheduling Company');
        $manager = $this->member($company, 'manager');
        $job = $this->workOrder($this->unit($company));

        $this->actingAs($manager)
            ->patch(route('maintenance.schedule.update', $job), $this->scheduleData())
            ->assertRedirect(route('maintenance.show', $job));

        $activity = $job->activities()->first();

        $this->assertNotNull($activity);
        $this->assertSame('open', $activity->old_status);
        $this->assertSame('open', $activity->new_status);
        $this->assertStringContainsString(
            'Work order scheduled.',
            $activity->work_notes
        );
        $this->assertStringContainsString(
            'Contact tenant before arrival.',
            $activity->work_notes
        );
    }
}
