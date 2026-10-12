<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Confirm that tests use an isolated SQLite database.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new \RuntimeException(
                'Property tests require an in-memory SQLite database.'
            );
        }
    }

    /**
     * Create a company for testing.
     */
    private function createCompany(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);
    }

    /**
     * Create a user with an active company membership.
     */
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

    /**
     * Create an existing property.
     */
    private function createProperty(
        Company $company,
        string $name = 'Harbor Point'
    ): Property {
        return Property::create([
            'company_id' => $company->id,
            'name' => $name,
            'address' => '100 Harbor Drive',
            'city' => 'Stamford',
            'state' => 'CT',
            'zip_code' => '06902',
        ]);
    }

    /**
     * Return valid property form information.
     */
    private function validPropertyData(Company $company): array
    {
        return [
            'company_id' => $company->id,
            'name' => 'The Vault',
            'address' => '120 Towne Street',
            'city' => 'Stamford',
            'state' => 'CT',
            'zip_code' => '06902',
        ];
    }

    /**
     * Administrators can create properties.
     */
    public function test_admin_can_create_property(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $admin = $this->createMember($company);

        $response = $this->actingAs($admin)
            ->post(
                route('properties.store'),
                $this->validPropertyData($company)
            );

        $property = Property::where('name', 'The Vault')->firstOrFail();

        $response->assertRedirect(
            route('properties.show', $property)
        );

        $this->assertDatabaseHas('properties', [
            'company_id' => $company->id,
            'name' => 'The Vault',
        ]);
    }

    /**
     * Managers can update property details.
     */
    public function test_manager_can_edit_property(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $manager = $this->createMember($company, 'manager');
        $property = $this->createProperty($company);

        $response = $this->actingAs($manager)
            ->patch(
                route('properties.update', $property),
                [
                    'name' => 'Harbor Point Apartments',
                    'address' => '200 Harbor Drive',
                    'city' => 'Stamford',
                    'state' => 'CT',
                    'zip_code' => '06902',
                ]
            );

        $response->assertRedirect(
            route('properties.show', $property)
        );

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'company_id' => $company->id,
            'name' => 'Harbor Point Apartments',
            'address' => '200 Harbor Drive',
        ]);
    }

    /**
     * Search returns only matching properties.
     */
    public function test_property_search_returns_matching_properties(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $admin = $this->createMember($company);

        $this->createProperty($company, 'Harbor Point');
        $this->createProperty($company, 'The Vault');

        $response = $this->actingAs($admin)
            ->get(route('properties.index', [
                'search' => 'Vault',
            ]));

        $response->assertOk();

        $response->assertViewHas(
            'properties',
            function ($properties) {
                return $properties->count() === 1
                    && $properties->first()->name === 'The Vault';
            }
        );

        $response->assertSeeText('The Vault');
        $response->assertDontSeeText('Harbor Point');
    }

    /**
     * Users cannot access another company's property.
     */
    public function test_user_cannot_access_another_company_property(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');

        $adminA = $this->createMember($companyA);

        $propertyB = $this->createProperty(
            $companyB,
            'Private Building'
        );

        $this->actingAs($adminA)
            ->get(route('properties.show', $propertyB))
            ->assertNotFound();

        $this->actingAs($adminA)
            ->get(route('properties.edit', $propertyB))
            ->assertNotFound();

        $this->actingAs($adminA)
            ->patch(
                route('properties.update', $propertyB),
                [
                    'name' => 'Unauthorized Change',
                    'address' => '200 Example Street',
                    'city' => 'Stamford',
                    'state' => 'CT',
                    'zip_code' => '06902',
                ]
            )
            ->assertNotFound();

        $this->assertDatabaseHas('properties', [
            'id' => $propertyB->id,
            'name' => 'Private Building',
        ]);
    }

    /**
     * Technicians cannot edit property information.
     */
    public function test_technician_cannot_edit_property(): void
    {
        $company = $this->createCompany('FixFlow Management');

        $technician = $this->createMember(
            $company,
            'technician'
        );

        $property = $this->createProperty($company);

        $this->actingAs($technician)
            ->get(route('properties.edit', $property))
            ->assertNotFound();

        $this->actingAs($technician)
            ->patch(
                route('properties.update', $property),
                [
                    'name' => 'Unauthorized Change',
                    'address' => '100 Harbor Drive',
                    'city' => 'Stamford',
                    'state' => 'CT',
                    'zip_code' => '06902',
                ]
            )
            ->assertNotFound();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'Harbor Point',
        ]);
    }

    /**
     * Invalid property information is rejected.
     */
    public function test_invalid_property_information_is_rejected(): void
    {
        $company = $this->createCompany('FixFlow Management');
        $admin = $this->createMember($company);

        $response = $this->actingAs($admin)
            ->post(
                route('properties.store'),
                [
                    'company_id' => $company->id,
                    'name' => '',
                    'address' => '',
                    'city' => '',
                    'state' => '',
                    'zip_code' => '',
                ]
            );

        $response->assertSessionHasErrors([
            'name',
            'address',
            'city',
            'state',
            'zip_code',
        ]);

        $this->assertDatabaseCount('properties', 0);
    }
}
