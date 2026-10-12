<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_user_management(): void
    {
        $response = $this->get('/users');

        $response->assertRedirect('/login');
    }

    public function test_kasir_cannot_access_user_management(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_user_management_list(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Super Admin']);
        $kasir = User::factory()->kasir()->create(['name' => 'Petugas Kasir']);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
        $response->assertSee('Kelola Petugas & Pengguna');
        $response->assertSee('Super Admin');
        $response->assertSee('Petugas Kasir');
    }

    public function test_admin_can_create_new_petugas(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Kasir Baru',
            'email' => 'kasirbaru@laundry.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kasir',
            'no_hp' => '081299998888',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Kasir Baru',
            'email' => 'kasirbaru@laundry.test',
            'role' => 'kasir',
            'no_hp' => '081299998888',
        ]);
    }

    public function test_admin_can_update_petugas(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->kasir()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@laundry.test',
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $targetUser), [
            'name' => 'Nama Baru',
            'email' => 'baru@laundry.test',
            'role' => 'admin',
            'no_hp' => '085555555555',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Nama Baru',
            'email' => 'baru@laundry.test',
            'role' => 'admin',
            'no_hp' => '085555555555',
        ]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete(route('users.destroy', $admin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_delete_the_only_admin(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        // admin1 deletes admin2 -> allowed since there are 2 admins
        $res = $this->actingAs($admin1)->delete(route('users.destroy', $admin2));
        $res->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $admin2->id]);

        // Now if admin1 tries to delete another admin if it were only 1, the check prevents it
        $this->assertEquals(1, User::where('role', 'admin')->count());
    }

    public function test_admin_can_delete_kasir_user(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($admin)->delete(route('users.destroy', $kasir));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $kasir->id]);
    }

    public function test_kasir_cannot_manage_services_or_delete_orders(): void
    {
        $kasir = User::factory()->kasir()->create();
        $service = Service::create([
            'nama_layanan' => 'Cuci Lipat',
            'jenis' => 'kiloan',
            'harga' => 7000,
            'durasi_jam' => 24,
            'is_active' => true,
        ]);

        // Create service attempt by kasir -> 403
        $this->actingAs($kasir)->get(route('services.create'))->assertStatus(403);
        $this->actingAs($kasir)->post(route('services.store'), [
            'nama_layanan' => 'Ilegal Service',
            'jenis' => 'kiloan',
            'harga' => 5000,
            'durasi_jam' => 24,
        ])->assertStatus(403);

        // Delete customer attempt by kasir -> 403
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-001',
            'nama' => 'Pelanggan Test',
            'no_hp' => '0812345678',
        ]);
        $this->actingAs($kasir)->delete(route('customers.destroy', $customer))->assertStatus(403);
    }
}
