<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that login screen can be rendered.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('CleanWash Laundry');
        $response->assertSee('Masuk ke Sistem');
        $response->assertDontSee('Akun Default Demo:');
        $response->assertDontSee('admin@laundry.test');
    }

    /**
     * Test that admin users can authenticate using valid credentials.
     */
    public function test_admin_can_authenticate_using_the_login_screen(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@laundry.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@laundry.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test that non-admin users cannot authenticate using the login screen.
     */
    public function test_non_admin_users_cannot_authenticate_using_the_login_screen(): void
    {
        User::factory()->kasir()->create([
            'email' => 'kasir@laundry.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'kasir@laundry.test',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that users cannot authenticate with invalid password.
     */
    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@laundry.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@laundry.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that authenticated users can log out.
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    /**
     * Test that unauthenticated users are redirected to login.
     */
    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that public tracking page remains accessible without login.
     */
    public function test_public_tracking_is_accessible_without_login(): void
    {
        $response = $this->get('/tracking');

        $response->assertStatus(200);
    }
}
