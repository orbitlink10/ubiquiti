<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_served_from_login_php(): void
    {
        $response = $this->get('/login.php');

        $response->assertOk();
        $response->assertSee('Login');
    }

    public function test_login_route_redirects_to_login_php(): void
    {
        $response = $this->get('/login');

        $response->assertRedirect('/login.php');
    }

    public function test_admin_register_page_is_served_from_admin_register_php(): void
    {
        $response = $this->get('/admin/register.php');

        $response->assertOk();
        $response->assertSee('Admin Registration');
        $response->assertSee('Create Admin Account');
    }

    public function test_admin_register_route_redirects_to_admin_register_php(): void
    {
        $response = $this->get('/admin/register');

        $response->assertRedirect('/admin/register.php');
    }

    public function test_admin_is_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login.php', [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_registration_creates_admin_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/admin/register.php', [
            'name' => 'New Admin',
            'email' => 'new-admin@example.com',
            'phone' => '+254700123456',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-admin@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertAuthenticated();
    }

    public function test_admin_can_return_from_public_homepage_and_logout(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $homeResponse = $this->actingAs($admin)->get('/');

        $homeResponse->assertOk();
        $homeResponse->assertDontSee('Admin Dashboard');
        $homeResponse->assertDontSee('Logout');
        $homeResponse->assertSee('Login');

        $this->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();

        $this->get('/')
            ->assertOk()
            ->assertSee('Login')
            ->assertDontSee('Register');
    }
}
