<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoLoginTest extends TestCase
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
                'Demo login tests require an in-memory SQLite database.'
            );
        }
    }

    private function createDemoUser(
        string $role = 'demo_viewer',
        string $status = 'active'
    ): User {
        $company = Company::create([
            'name' => 'Demo Login Test Company',
            'slug' => 'demo-login-' . Str::lower(Str::random(10)),
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'email' => 'demo@fixflow.com',
        ]);

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => $status,
        ]);

        return $user;
    }

    public function test_login_page_displays_demo_button(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Try Live Demo')
            ->assertSee(route('demo.login'));
    }

    public function test_active_demo_viewer_can_log_in_without_password(): void
    {
        $user = $this->createDemoUser();

        $this->post(route('demo.login'))
            ->assertRedirect(route('maintenance.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_demo_login_fails_when_account_does_not_exist(): void
    {
        $this->post(route('demo.login'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('demo');

        $this->assertGuest();
    }

    public function test_inactive_demo_viewer_cannot_log_in(): void
    {
        $this->createDemoUser('demo_viewer', 'inactive');

        $this->post(route('demo.login'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('demo');

        $this->assertGuest();
    }

    public function test_non_demo_role_cannot_use_demo_login(): void
    {
        $this->createDemoUser('company_admin');

        $this->post(route('demo.login'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('demo');

        $this->assertGuest();
    }

    public function test_regular_user_cannot_become_demo_viewer(): void
    {
        $company = Company::create([
            'name' => 'Regular Login Test Company',
            'slug' => 'regular-login-' . Str::lower(Str::random(10)),
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'email' => 'regular@example.com',
        ]);

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $this->post(route('demo.login'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('demo');

        $this->assertGuest();
    }

    public function test_demo_login_route_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('demo.login'));
        }

        $this->post(route('demo.login'))
            ->assertStatus(429);

        $this->assertGuest();
    }
}
