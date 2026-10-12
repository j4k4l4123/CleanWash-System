<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_pekerja_can_login_and_is_redirected_to_orders_index(): void
    {
        $pekerja = User::factory()->pekerja()->create([
            'email' => 'pekerja@laundry.test',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'pekerja@laundry.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertAuthenticatedAs($pekerja);
    }

    public function test_pekerja_accessing_dashboard_is_redirected_to_orders_index(): void
    {
        $pekerja = User::factory()->pekerja()->create();

        $response = $this->actingAs($pekerja)->get('/dashboard');
        $response->assertRedirect(route('orders.index'));

        $rootResponse = $this->actingAs($pekerja)->get('/');
        $rootResponse->assertRedirect(route('orders.index'));
    }

    public function test_pekerja_cannot_access_restricted_modules(): void
    {
        $pekerja = User::factory()->pekerja()->create();

        $this->actingAs($pekerja)->get('/customers')->assertStatus(403);
        $this->actingAs($pekerja)->get('/services')->assertStatus(403);
        $this->actingAs($pekerja)->get('/payments')->assertStatus(403);
        $this->actingAs($pekerja)->get('/users')->assertStatus(403);
        $this->actingAs($pekerja)->get('/orders/create')->assertStatus(403);
    }
}
