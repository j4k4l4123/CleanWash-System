# Implementasi Role Pekerja Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan role baru `pekerja` yang hanya memiliki akses untuk login/logout dan melihat daftar orderan cucian berstatus "Diterima", membatasi akses fitur lainnya, serta menambahkan data pekerja ke database.

**Architecture:** Middleware-based & controller-scoped access control. Rute transaksional dan manajemen pelanggan dikunci dengan middleware `role:admin,kasir`, rute daftar order (`orders.index`) dan detail (`orders.show`) dibuka untuk pekerja dengan query scoping khusus status `diterima`, serta redirect otomatis dari dashboard ke daftar order.

**Tech Stack:** Laravel 11 / PHP 8.4, Blade, Tailwind CSS, PHPUnit.

---

### Task 1: Model User, Factory, dan Seeder Akun Pekerja

**Files:**
- Modify: `app/Models/User.php`
- Modify: `database/factories/UserFactory.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Unit/UserRoleTest.php`

- [x] **Step 1: Write the failing unit test for User role helpers**

Buat file test `tests/Unit/UserRoleTest.php`:
```php
<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_user_role_methods_and_labels(): void
    {
        $pekerja = new User(['role' => 'pekerja']);
        $admin = new User(['role' => 'admin']);
        $kasir = new User(['role' => 'kasir']);

        $this->assertTrue($pekerja->isPekerja());
        $this->assertFalse($pekerja->isAdmin());
        $this->assertFalse($pekerja->isKasir());

        $this->assertEquals('Pekerja Laundry', $pekerja->role_label);
        $this->assertEquals('Admin / Owner', $admin->role_label);
        $this->assertEquals('Kasir / Petugas', $kasir->role_label);

        $this->assertStringContainsString('amber', $pekerja->role_badge_class);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/UserRoleTest.php`
Expected: FAIL (Call to undefined method `isPekerja()`, role_label returns 'Kasir / Petugas')

- [x] **Step 3: Update User model, UserFactory, and DatabaseSeeder**

Modifikasi `app/Models/User.php`:
```php
    /**
     * Check if the user is a worker (pekerja).
     */
    public function isPekerja(): bool
    {
        return $this->role === 'pekerja';
    }

    /**
     * Get user-friendly role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin / Owner',
            'pekerja' => 'Pekerja Laundry',
            default => 'Kasir / Petugas',
        };
    }

    /**
     * Get Tailwind badge classes for role.
     */
    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'bg-purple-50 text-purple-700 border-purple-200',
            'pekerja' => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }
```

Modifikasi `database/factories/UserFactory.php`:
```php
    /**
     * Indicate that the user is a pekerja.
     */
    public function pekerja(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'pekerja',
        ]);
    }
```

Modifikasi `database/seeders/DatabaseSeeder.php`:
Tambahkan input akun pekerja default:
```php
        // Pekerja Laundry
        User::updateOrCreate(
            ['email' => 'pekerja@laundry.test'],
            [
                'name' => 'Pekerja Laundry',
                'password' => bcrypt('password'),
                'role' => 'pekerja',
                'no_hp' => '081234567899',
            ]
        );
```

- [x] **Step 4: Run unit test to verify it passes**

Run: `php artisan test tests/Unit/UserRoleTest.php`
Expected: PASS

- [x] **Step 5: Run Database Seeder to insert pekerja data**

Run: `php artisan db:seed --class=DatabaseSeeder`
Expected: SUCCESS

- [x] **Step 6: Commit changes**

```bash
git add app/Models/User.php database/factories/UserFactory.php database/seeders/DatabaseSeeder.php tests/Unit/UserRoleTest.php
git commit -m "feat: add isPekerja helper, factory state, and seed data"
```

---

### Task 2: Pembatasan Routing & Redirect Alur Pekerja

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/AuthController.php`
- Modify: `app/Http/Controllers/DashboardController.php`
- Test: `tests/Feature/WorkerRoleTest.php`

- [x] **Step 1: Write failing tests for worker routing and redirect**

Buat file test `tests/Feature/WorkerRoleTest.php`:
```php
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
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/WorkerRoleTest.php`
Expected: FAIL (Redirects to dashboard instead of orders, and 200 OK on restricted modules)

- [x] **Step 3: Update routes and controllers**

Modifikasi `routes/web.php`:
Bagi rute terproteksi menjadi rute bersama (`orders.index`, `orders.show`, `dashboard`), rute `role:admin,kasir`, dan rute `role:admin`:
```php
// Protected Routes (Semua Pengguna Terotentikasi)
Route::middleware('auth')->group(function (): void {
    // Dashboard (Dialihkan ke orders.index jika role pekerja)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Order Cucian (Bisa dilihat pekerja)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Khusus Kasir & Admin (role:admin,kasir)
    Route::middleware('role:admin,kasir')->group(function (): void {
        // Pelanggan
        Route::resource('customers', CustomerController::class)->except(['destroy']);
        Route::post('/api/customers/quick-create', [CustomerController::class, 'quickStore'])->name('customers.quick-store');

        // Layanan (Katalog Kasir & Admin)
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

        // Transaksi & Status Order
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::post('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');

        // Pembayaran
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/orders/{order}/pay', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    });

    // Khusus Admin / Owner (Role: Admin)
    Route::middleware('role:admin')->group(function (): void {
        // Kelola Petugas / Pengguna
        Route::resource('users', UserController::class)->except(['show']);

        // Kelola Tarif & Tambah Layanan
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
        Route::post('/services/{service}/toggle', [ServiceController::class, 'toggle'])->name('services.toggle');

        // Tindakan Penghapusan Transaksi & Pelanggan
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });
});
```

Modifikasi `app/Http/Controllers/AuthController.php` di dalam `login()`:
```php
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $defaultRedirect = Auth::user()->isPekerja() ? route('orders.index') : route('dashboard');
            $intended = session()->pull('url.intended', $defaultRedirect);
            if (is_string($intended) && str_starts_with($intended, 'http://') && ! str_contains($intended, 'localhost') && ! str_contains($intended, '127.0.0.1')) {
                $intended = preg_replace('/^http:/i', 'https:', $intended);
            }

            return redirect()->to($intended)
                ->with('success', 'Selamat datang kembali, '.Auth::user()->name.'!');
        }
```

Modifikasi `app/Http/Controllers/DashboardController.php` di dalam `index()`:
```php
    public function index()
    {
        if (auth()->user()?->isPekerja()) {
            return redirect()->route('orders.index');
        }

        $today = Carbon::today();
        // ... sisa kode dashboard ...
```

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/WorkerRoleTest.php`
Expected: PASS (3 tests pass)

- [x] **Step 5: Commit changes**

```bash
git add routes/web.php app/Http/Controllers/AuthController.php app/Http/Controllers/DashboardController.php tests/Feature/WorkerRoleTest.php
git commit -m "feat: restrict routes and redirect pekerja to orders"
```

---

### Task 3: Scoping Query & Batasan Detail Orderan untuk Pekerja

**Files:**
- Modify: `app/Http/Controllers/OrderController.php`
- Modify: `tests/Feature/WorkerRoleTest.php`

- [x] **Step 1: Write failing tests for order data scoping**

Tambahkan method test baru di `tests/Feature/WorkerRoleTest.php`:
```php
    public function test_pekerja_only_sees_orders_with_status_diterima(): void
    {
        $pekerja = User::factory()->pekerja()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-001',
            'nama' => 'Budi Pelanggan',
            'no_hp' => '0812345678',
        ]);
        $service = Service::create([
            'nama_layanan' => 'Cuci Kering',
            'jenis' => 'kiloan',
            'harga' => 6000,
            'durasi_jam' => 24,
            'is_active' => true,
        ]);

        $orderDiterima = Order::create([
            'kode_order' => 'ORD-TEST-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 6000,
            'total_harga' => 12000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDay(),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        $orderSelesai = Order::create([
            'kode_order' => 'ORD-TEST-002',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 3,
            'harga_per_satuan' => 6000,
            'total_harga' => 18000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDay(),
            'status_cucian' => 'selesai',
            'status_pembayaran' => 'lunas',
        ]);

        $response = $this->actingAs($pekerja)->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('ORD-TEST-001');
        $response->assertDontSee('ORD-TEST-002');

        // Buka detail order Diterima -> 200 OK
        $this->actingAs($pekerja)->get(route('orders.show', $orderDiterima))->assertStatus(200);

        // Buka detail order Selesai -> 403 Forbidden
        $this->actingAs($pekerja)->get(route('orders.show', $orderSelesai))->assertStatus(403);
    }
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_pekerja_only_sees_orders_with_status_diterima`
Expected: FAIL (`ORD-TEST-002` still seen, or status 200 instead of 403 on selesai detail)

- [x] **Step 3: Update OrderController query scoping & detail check**

Modifikasi `app/Http/Controllers/OrderController.php`:
Di `index()`:
```php
        $query = Order::with(['customer', 'service']);

        if ($request->user()?->isPekerja()) {
            $query->where('status_cucian', 'diterima');
        } else {
            if ($statusCucian = $request->input('status_cucian')) {
                $query->where('status_cucian', $statusCucian);
            }
        }
```

Di `show()`:
```php
    public function show(Order $order)
    {
        if (auth()->user()?->isPekerja() && $order->status_cucian !== 'diterima') {
            abort(403, 'Akses ditolak. Pekerja hanya dapat melihat orderan dengan status Diterima.');
        }

        $order->loadMissing(['customer', 'service', 'statusLogs', 'payments']);
        $statuses = Order::listStatuses();

        return view('orders.show', compact('order', 'statuses'));
    }
```

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/WorkerRoleTest.php`
Expected: PASS

- [x] **Step 5: Commit changes**

```bash
git add app/Http/Controllers/OrderController.php tests/Feature/WorkerRoleTest.php
git commit -m "feat: enforce order scoping to diterima status for pekerja"
```

---

### Task 4: Pembaruan Manajemen Petugas (UserController & Form Admin)

**Files:**
- Modify: `app/Http/Controllers/UserController.php`
- Modify: `resources/views/users/create.blade.php`
- Modify: `resources/views/users/edit.blade.php`
- Modify: `resources/views/users/index.blade.php`
- Test: `tests/Feature/WorkerRoleTest.php`

- [x] **Step 1: Write test for admin managing pekerja user**

Tambahkan method test di `tests/Feature/WorkerRoleTest.php`:
```php
    public function test_admin_can_create_and_update_pekerja_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Staf Baru',
            'email' => 'staf@laundry.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pekerja',
            'no_hp' => '081233344455',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'staf@laundry.test',
            'role' => 'pekerja',
        ]);

        $user = User::where('email', 'staf@laundry.test')->first();

        $updateResponse = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => 'Staf Diperbarui',
            'email' => 'staf@laundry.test',
            'role' => 'kasir',
            'no_hp' => '081233344455',
        ]);

        $updateResponse->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'kasir',
        ]);
    }
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_admin_can_create_and_update_pekerja_user`
Expected: FAIL (The selected role is invalid, validation fails on `in:admin,kasir`)

- [x] **Step 3: Update UserController and User views**

Modifikasi `app/Http/Controllers/UserController.php`:
Ubah validasi pada `store()` dan `update()`:
```php
'role' => ['required', 'in:admin,kasir,pekerja'],
```

Modifikasi `resources/views/users/create.blade.php` & `resources/views/users/edit.blade.php`:
Tambahkan opsi radio button ketiga di pilihan peran (role):
```html
<label class="flex items-start gap-3 p-3.5 border rounded-2xl cursor-pointer hover:bg-slate-50 transition border-slate-200">
    <input type="radio" name="role" value="pekerja" class="mt-1 text-brand-600 focus:ring-brand-500" {{ old('role', $user->role ?? '') === 'pekerja' ? 'checked' : '' }}>
    <div>
        <span class="block text-sm font-semibold text-slate-800">Pekerja Laundry</span>
        <span class="block text-xs text-slate-500">Hanya bisa melihat daftar orderan cucian berstatus Diterima. Tidak bisa ubah data atau akses modul lain.</span>
    </div>
</label>
```

Modifikasi `resources/views/users/index.blade.php`:
Tambahkan opsi pada dropdown filter:
```html
<option value="pekerja" {{ $role === 'pekerja' ? 'selected' : '' }}>Pekerja Laundry</option>
```

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=test_admin_can_create_and_update_pekerja_user`
Expected: PASS

- [x] **Step 5: Commit changes**

```bash
git add app/Http/Controllers/UserController.php resources/views/users/create.blade.php resources/views/users/edit.blade.php resources/views/users/index.blade.php tests/Feature/WorkerRoleTest.php
git commit -m "feat: support pekerja role in user management forms"
```

---

### Task 5: Penyesuaian Tampilan UI Antarmuka (Navbar & Order Views)

**Files:**
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/orders/index.blade.php`
- Modify: `resources/views/orders/show.blade.php`
- Test: `tests/Feature/WorkerRoleTest.php`

- [x] **Step 1: Write test for UI restrictions on pekerja**

Tambahkan method test di `tests/Feature/WorkerRoleTest.php`:
```php
    public function test_pekerja_views_do_not_contain_action_buttons(): void
    {
        $pekerja = User::factory()->pekerja()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-002',
            'nama' => 'Pelanggan UI',
            'no_hp' => '0812345678',
        ]);
        $service = Service::create([
            'nama_layanan' => 'Cuci Kering',
            'jenis' => 'kiloan',
            'harga' => 6000,
            'durasi_jam' => 24,
            'is_active' => true,
        ]);

        $order = Order::create([
            'kode_order' => 'ORD-UI-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 6000,
            'total_harga' => 12000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDay(),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        $indexResponse = $this->actingAs($pekerja)->get('/orders');
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('Terima Order Baru');
        $indexResponse->assertDontSee('href="' . route('customers.index') . '"', false);

        $showResponse = $this->actingAs($pekerja)->get(route('orders.show', $order));
        $showResponse->assertStatus(200);
        $showResponse->assertDontSee('Update Status Cucian');
        $showResponse->assertDontSee('Konfirmasi Pembayaran Lunas');
        $showResponse->assertDontSee(route('orders.edit', $order));
    }
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_pekerja_views_do_not_contain_action_buttons`
Expected: FAIL (Elements are still visible)

- [x] **Step 3: Update Blade templates**

Modifikasi `resources/views/layouts/app.blade.php`:
1. Brand Logo URL:
   `href="{{ Auth::check() && Auth::user()->isPekerja() ? route('orders.index') : route('dashboard') }}"`
2. Desktop Nav:
   Sembunyikan link Pelanggan, Layanan jika `Auth::user()->isPekerja()`.
3. Mobile Nav:
   Sembunyikan link Pelanggan, Layanan jika `Auth::user()->isPekerja()`.

Modifikasi `resources/views/orders/index.blade.php`:
1. Sembunyikan tombol "Terima Order Baru" jika `Auth::user()->isPekerja()`:
   `@if(!Auth::user()->isPekerja()) ... @endif`
2. Pada filter bar: sembunyikan dropdown "Status Cucian" untuk pekerja karena selalu terfilter ke "Diterima".
3. Pada baris tabel:
   Jika pekerja, ganti button dengan onclick dropdown menjadi span static badge:
   `@if(Auth::user()->isPekerja())`
   `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $order->status_badge_class }}">{{ $order->status_label }}</span>`
   `@else ... button ... @endif`

Modifikasi `resources/views/orders/show.blade.php`:
1. Sembunyikan tombol "Update Status Cucian" jika `Auth::user()->isPekerja()`.
2. Sembunyikan link "Lihat Profil" pelanggan jika `Auth::user()->isPekerja()`.
3. Sembunyikan form "Konfirmasi Pembayaran Lunas" dan link edit order jika `Auth::user()->isPekerja()`.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=test_pekerja_views_do_not_contain_action_buttons`
Expected: PASS

- [x] **Step 5: Commit changes**

```bash
git add resources/views/layouts/app.blade.php resources/views/orders/index.blade.php resources/views/orders/show.blade.php tests/Feature/WorkerRoleTest.php
git commit -m "feat: adjust views to hide unauthorized actions for pekerja"
```

---

### Task 6: Verifikasi Akhir, Format Pint, dan Uji Keseluruhan

**Files:**
- None (Verifikasi & Formatting)

- [x] **Step 1: Run code formatter Pint**

Run: `vendor/bin/pint --format agent`
Expected: 0 errors / clean formatting

- [x] **Step 2: Run all tests in test suite**

Run: `php artisan test`
Expected: 100% tests PASS (all tests including existing and new ones)

- [x] **Step 3: Verify user account in database**

Run tinker to ensure pekerja user exists:
```bash
php artisan tinker --execute 'echo User::where("role", "pekerja")->first()?->email;'
```
Expected: `pekerja@laundry.test`

- [x] **Step 4: Commit any remaining changes**

```bash
git add .
git commit -m "chore: format code and finalize pekerja role implementation"
```
