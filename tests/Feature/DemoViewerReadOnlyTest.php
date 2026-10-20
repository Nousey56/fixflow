<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoViewerReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new \RuntimeException(
                'Demo Viewer tests require an in-memory SQLite database.'
            );
        }
    }

    private function createCompany(string $name = 'FixFlow Demo Company'): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);
    }

    private function createViewer(Company $company): User
    {
        $user = User::factory()->create();

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => 'demo_viewer',
            'status' => 'active',
        ]);

        return $user;
    }

    private function createWorkOrder(
        Company $company,
        string $title = 'Repair leaking sink'
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
            'title' => $title,
            'description' => 'Kitchen sink is leaking.',
            'priority' => 'medium',
            'status' => 'open',
        ]);
    }

    public function test_demo_viewer_cannot_create_maintenance_requests(): void
    {
        $viewer = $this->createViewer($this->createCompany());

        $this->actingAs($viewer)
            ->post(route('maintenance.store'), [])
            ->assertForbidden();
    }

    public function test_demo_viewer_cannot_create_properties(): void
    {
        $viewer = $this->createViewer($this->createCompany());

        $this->actingAs($viewer)
            ->post(route('properties.store'), [])
            ->assertForbidden();
    }

    public function test_demo_viewer_cannot_modify_team_members(): void
    {
        $viewer = $this->createViewer($this->createCompany());

        $this->actingAs($viewer)
            ->post(route('team.store'), [])
            ->assertForbidden();
    }

    public function test_demo_viewer_can_log_out(): void
    {
        $viewer = $this->createViewer($this->createCompany());

        $this->actingAs($viewer)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_demo_viewer_can_view_own_company_work_order(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);
        $job = $this->createWorkOrder($company);

        $this->actingAs($viewer)
            ->get(route('maintenance.show', $job))
            ->assertOk();
    }

    public function test_demo_viewer_cannot_view_another_company_work_order(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');

        $viewer = $this->createViewer($companyA);
        $job = $this->createWorkOrder($companyB);

        $this->actingAs($viewer)
            ->get(route('maintenance.show', $job))
            ->assertNotFound();
    }

    public function test_demo_viewer_sees_only_own_company_work_orders_in_list(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');

        $viewer = $this->createViewer($companyA);

        $this->createWorkOrder($companyA, 'Own company sink repair');
        $this->createWorkOrder($companyB, 'Other company secret repair');

        $this->actingAs($viewer)
            ->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee('Own company sink repair')
            ->assertDontSee('Other company secret repair');
    }

    public function test_demo_viewer_cannot_update_own_company_work_order(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);
        $job = $this->createWorkOrder($company);

        $this->actingAs($viewer)
            ->patch(route('maintenance.update', $job), [
                'status' => 'completed',
            ])
            ->assertForbidden();

        $this->assertSame('open', $job->fresh()->status);
    }

    public function test_demo_viewer_sees_read_only_notice(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);
        $job = $this->createWorkOrder($company);

        $this->actingAs($viewer)
            ->get(route('maintenance.show', $job))
            ->assertOk()
            ->assertSee('Read-only demo');
    }

    public function test_demo_viewer_does_not_see_editing_controls(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);
        $job = $this->createWorkOrder($company);

        $this->actingAs($viewer)
            ->get(route('maintenance.show', $job))
            ->assertOk()
            ->assertDontSee('Save Changes')
            ->assertDontSee('Save Schedule')
            ->assertDontSee('Go to My Jobs');
    }


    public function test_demo_viewer_can_open_dispatch_dashboard(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);

        $this->createWorkOrder($company);

        $this->actingAs($viewer)
            ->get(route('dispatch.index', ['view' => 'all']))
            ->assertOk();
    }

    public function test_demo_viewer_can_open_calendar_dashboard(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);

        $this->actingAs($viewer)
            ->get(route('calendar.index'))
            ->assertOk();
    }

    public function test_demo_viewer_can_open_workload_dashboard(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);

        $this->actingAs($viewer)
            ->get(route('workload.index'))
            ->assertOk();
    }

    public function test_demo_viewer_can_open_analytics_dashboard(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);

        $this->createWorkOrder($company);

        $this->actingAs($viewer)
            ->get(route('analytics.index'))
            ->assertOk();
    }

    public function test_demo_viewer_dispatch_excludes_other_company_jobs(): void
    {
        $companyA = $this->createCompany('Demo Company A');
        $companyB = $this->createCompany('Demo Company B');

        $viewer = $this->createViewer($companyA);

        $this->createWorkOrder(
            $companyA,
            'Authorized demo maintenance'
        );

        $this->createWorkOrder(
            $companyB,
            'Confidential other company maintenance'
        );

        $this->actingAs($viewer)
            ->get(route('dispatch.index', ['view' => 'all']))
            ->assertOk()
            ->assertSee('Authorized demo maintenance')
            ->assertDontSee('Confidential other company maintenance');
    }


    public function test_demo_viewer_can_view_own_company_property(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);
        $job = $this->createWorkOrder($company);
        $property = $job->unit->property;

        $this->actingAs($viewer)
            ->get(route('properties.index'))
            ->assertOk();

        $this->actingAs($viewer)
            ->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee($property->name);
    }

    public function test_demo_viewer_cannot_view_another_company_property(): void
    {
        $companyA = $this->createCompany('Viewer Company');
        $companyB = $this->createCompany('Private Company');

        $viewer = $this->createViewer($companyA);
        $job = $this->createWorkOrder($companyB);

        $this->actingAs($viewer)
            ->get(route('properties.show', $job->unit->property))
            ->assertNotFound();
    }

    public function test_demo_viewer_cannot_see_property_editing_buttons(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);
        $job = $this->createWorkOrder($company);
        $property = $job->unit->property;

        $this->actingAs($viewer)
            ->get(route('properties.index'))
            ->assertOk()
            ->assertSee('Read-only demo')
            ->assertDontSee('Add Property')
            ->assertDontSee('Create Your First Property');

        $this->actingAs($viewer)
            ->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee('Read-only demo')
            ->assertDontSee('Edit Property')
            ->assertDontSee('Archive Property')
            ->assertDontSee('Restore Property')
            ->assertDontSee('Add Unit')
            ->assertDontSee('Edit Unit');
    }

    public function test_demo_viewer_cannot_open_property_or_unit_edit_forms(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);
        $job = $this->createWorkOrder($company);
        $property = $job->unit->property;
        $unit = $job->unit;

        $response = $this->actingAs($viewer)
            ->get(route('properties.create'))
            ;

        $this->assertContains(
            $response->getStatusCode(),
            [403, 404],
            'Demo Viewer must be denied access.'
        );

        $response = $this->actingAs($viewer)
            ->get(route('properties.edit', $property))
            ;

        $this->assertContains(
            $response->getStatusCode(),
            [403, 404],
            'Demo Viewer must be denied access.'
        );

        $response = $this->actingAs($viewer)
            ->get(route('units.create', $property))
            ;

        $this->assertContains(
            $response->getStatusCode(),
            [403, 404],
            'Demo Viewer must be denied access.'
        );

        $response = $this->actingAs($viewer)
            ->get(route('units.edit', [$property, $unit]))
            ;

        $this->assertContains(
            $response->getStatusCode(),
            [403, 404],
            'Demo Viewer must be denied access.'
        );
    }

    public function test_demo_viewer_cannot_archive_property_or_modify_unit(): void
    {
        $company = $this->createCompany();
        $viewer = $this->createViewer($company);
        $job = $this->createWorkOrder($company);
        $property = $job->unit->property;
        $unit = $job->unit;

        $this->actingAs($viewer)
            ->patch(route('properties.archive', $property))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('units.store', $property), [
                'unit_number' => '999',
            ])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->patch(route('units.update', [$property, $unit]), [
                'unit_number' => '999',
            ])
            ->assertForbidden();

        $this->assertSame('101', $unit->fresh()->unit_number);
        $this->assertFalse($property->fresh()->isArchived());
    }

}
