<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PropertyArchiveTest extends TestCase
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
                'Archive tests require an in-memory SQLite database.'
            );
        }
    }

    private function createCompany(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);
    }

    private function createMember(
        Company $company,
        string $role = 'company_admin'
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

    private function createProperty(
        Company $company,
        string $name = 'The Vault Apartments'
    ): Property {
        return Property::create([
            'company_id' => $company->id,
            'name' => $name,
            'address' => '120 Towne Street',
            'city' => 'Stamford',
            'state' => 'CT',
            'zip_code' => '06902',
        ]);
    }

    public function test_admin_can_archive_property(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $admin = $this->createMember($company);
        $property = $this->createProperty($company);

        $response = $this->actingAs($admin)
            ->patch(route('properties.archive', $property));

        $response->assertRedirect();

        $property->refresh();

        $this->assertNotNull($property->archived_at);
    }

    public function test_manager_can_restore_property(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $manager = $this->createMember($company, 'manager');
        $property = $this->createProperty($company);

        $property->update([
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($manager)
            ->patch(route('properties.restore', $property));

        $response->assertRedirect();

        $property->refresh();

        $this->assertNull($property->archived_at);
    }

    public function test_technician_cannot_archive_or_restore_property(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $technician = $this->createMember(
            $company,
            'technician'
        );

        $property = $this->createProperty($company);

        $this->actingAs($technician)
            ->patch(route('properties.archive', $property))
            ->assertNotFound();

        $this->assertNull($property->fresh()->archived_at);

        $property->update([
            'archived_at' => now(),
        ]);

        $this->actingAs($technician)
            ->patch(route('properties.restore', $property))
            ->assertNotFound();

        $this->assertNotNull($property->fresh()->archived_at);
    }

    public function test_user_cannot_archive_another_company_property(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');

        $adminA = $this->createMember($companyA);
        $propertyB = $this->createProperty($companyB);

        $this->actingAs($adminA)
            ->patch(route('properties.archive', $propertyB))
            ->assertNotFound();

        $this->assertNull($propertyB->fresh()->archived_at);
    }

    public function test_user_cannot_restore_another_company_property(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');

        $adminA = $this->createMember($companyA);
        $propertyB = $this->createProperty($companyB);

        $propertyB->update([
            'archived_at' => now(),
        ]);

        $this->actingAs($adminA)
            ->patch(route('properties.restore', $propertyB))
            ->assertNotFound();

        $this->assertNotNull($propertyB->fresh()->archived_at);
    }

    public function test_archiving_preserves_property_units(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $admin = $this->createMember($company);
        $property = $this->createProperty($company);

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => '665',
            'floor' => 7,
        ]);

        $this->actingAs($admin)
            ->patch(route('properties.archive', $property))
            ->assertRedirect();

        $this->assertNotNull($property->fresh()->archived_at);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'property_id' => $property->id,
            'unit_number' => '665',
        ]);
    }

    public function test_archived_property_cannot_be_edited(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $admin = $this->createMember($company);
        $property = $this->createProperty($company);

        $property->update([
            'archived_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('properties.update', $property), [
                'name' => 'Unauthorized Archived Edit',
                'address' => '200 Towne Street',
                'city' => 'Stamford',
                'state' => 'CT',
                'zip_code' => '06902',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'The Vault Apartments',
        ]);
    }

    public function test_archived_property_cannot_receive_new_units(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $admin = $this->createMember($company);
        $property = $this->createProperty($company);

        $property->update([
            'archived_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('units.store', $property), [
                'unit_number' => '700',
                'floor' => 7,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('units', [
            'property_id' => $property->id,
            'unit_number' => '700',
        ]);
    }
}
