# Spesifikasi Desain: Implementasi Role Pekerja

- **Tanggal**: 2026-10-13
- **Topik**: Penambahan Role Pekerja dengan Hak Akses Terbatas (Login/Logout & Melihat Order Diterima)
- **Status**: Disetujui

---

## 1. Latar Belakang & Tujuan
Aplikasi Sistem Manajemen Laundry CleanWash saat ini memiliki dua peran pengguna, yaitu `admin` (Owner) dan `kasir`. Dibutuhkan peran baru yaitu **`pekerja`** dengan hak akses yang sangat spesifik dan terbatas:
1. Pekerja hanya dapat melakukan **login** dan **logout**.
2. Pekerja hanya dapat melihat daftar orderan cucian yang berstatus **Diterima** (`status_cucian = 'diterima'`).
3. Pekerja dapat melihat halaman detail cucian (`orders.show`) khusus untuk orderan berstatus Diterima untuk memeriksa rincian pakaian dan catatan pencucian, tanpa wewenang mengubah data, mengubah status cucian, melakukan pembayaran, mencetak nota, ataupun menghapus order.
4. Pekerja tidak diizinkan mengakses menu lain (Dashboard, Pelanggan, Layanan, Pembayaran, Kelola Petugas).
5. Tersedia akun awal pekerja di database untuk keperluan operasional dan pengujian.

---

## 2. Arsitektur & Hak Akses

### 2.1 Matriks Hak Akses Pengguna

| Fitur / Halaman | Admin | Kasir | Pekerja |
| :--- | :---: | :---: | :---: |
| Login & Logout | Ya | Ya | Ya |
| Dashboard (`/`, `/dashboard`) | Ya | Ya | Dialihkan ke `/orders` |
| Kelola Pelanggan (`customers.*`) | Ya | Ya | Tidak (403) |
| Katalog Layanan (`services.index`) | Ya | Ya | Tidak (403) |
| Kelola Tarif Layanan (`services.create`, `edit`, dll.) | Ya | Tidak (403) | Tidak (403) |
| Daftar Order (`orders.index`) | Semua status | Semua status | **Hanya status "Diterima"** |
| Detail Order (`orders.show`) | Semua status | Semua status | **Hanya status "Diterima"** |
| Tambah Order (`orders.create`, `store`) | Ya | Ya | Tidak (403) |
| Edit Order (`orders.edit`, `update`) | Ya | Ya | Tidak (403) |
| Hapus Order (`orders.destroy`) | Ya | Tidak (403) | Tidak (403) |
| Ubah Status Order (`orders.update-status`) | Ya | Ya | Tidak (403) |
| Cetak Nota Order (`orders.print`) | Ya | Ya | Tidak (403) |
| Pembayaran (`payments.*`) | Ya | Ya | Tidak (403) |
| Kelola Petugas (`users.*`) | Ya | Tidak (403) | Tidak (403) |

---

## 3. Rincian Komponen & Perubahan

### 3.1 Model & Database (`App\Models\User`)
- **Helper Method**:
  ```php
  public function isPekerja(): bool
  {
      return $this->role === 'pekerja';
  }
  ```
- **Accessor `role_label`**:
  - `'admin'` &rarr; `'Admin / Owner'`
  - `'kasir'` &rarr; `'Kasir / Petugas'`
  - `'pekerja'` &rarr; `'Pekerja Laundry'`
- **Accessor `role_badge_class`**:
  - `'admin'` &rarr; `'bg-purple-50 text-purple-700 border-purple-200'`
  - `'kasir'` &rarr; `'bg-blue-50 text-blue-700 border-blue-200'`
  - `'pekerja'` &rarr; `'bg-amber-50 text-amber-700 border-amber-200'`

### 3.2 Seeder & Factory
- **`UserFactory`**:
  - Menambahkan method state `pekerja()`:
    ```php
    public function pekerja(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'pekerja',
        ]);
    }
    ```
- **`DatabaseSeeder`**:
  - Menambahkan akun bawaan pekerja:
    - **Nama**: Pekerja Laundry
    - **Email**: `pekerja@laundry.test`
    - **Password**: `password` (terenkripsi)
    - **Role**: `pekerja`
    - **No HP**: `081234567899`

### 3.3 Routing (`routes/web.php`)
- Kelompok rute yang diamankan dengan `role:admin,kasir`:
  - `customers` (resource kecuali destroy, quick-store)
  - `services.index`
  - `orders.create`, `orders.store`, `orders.edit`, `orders.update`, `orders.update-status`, `orders.print`
  - `payments.index`, `payments.store`, `payments.receipt`
- Rute order bersama (admin, kasir, pekerja):
  - `orders.index`
  - `orders.show`
- Rute khusus admin tetap dengan `role:admin`:
  - `users.*`
  - `services.create`, `store`, `edit`, `update`, `destroy`, `toggle`
  - `orders.destroy`
  - `customers.destroy`

### 3.4 Controller Layer
- **`AuthController`**:
  - Pada saat login sukses, jika `Auth::user()->isPekerja()`, arahkan redirect ke `route('orders.index')`.
- **`DashboardController`**:
  - Pada method `index()`, jika `auth()->user()->isPekerja()`, lakukan redirect ke `route('orders.index')`.
- **`OrderController`**:
  - Pada method `index(Request $request)`:
    - Jika `auth()->user()->isPekerja()`, kunci query order:
      ```php
      if ($request->user()->isPekerja()) {
          $query->where('status_cucian', 'diterima');
      }
      ```
      Pekerja tidak dapat mengubah filter status cucian.
  - Pada method `show(Order $order)`:
    - Jika `auth()->user()->isPekerja()` dan `$order->status_cucian !== 'diterima'`:
      ```php
      if ($request->user()->isPekerja() && $order->status_cucian !== 'diterima') {
          abort(403, 'Akses ditolak. Pekerja hanya dapat melihat orderan dengan status Diterima.');
      }
      ```
- **`UserController`**:
  - Perbarui validasi pada `store()` dan `update()`:
    ```php
    'role' => ['required', 'in:admin,kasir,pekerja'],
    ```

### 3.5 Tampilan Antarmuka (Blade Views)
- **`resources/views/layouts/app.blade.php`**:
  - Link logo/brand: jika user pekerja &rarr; `route('orders.index')`, jika bukan &rarr; `route('dashboard')`.
  - Navigasi desktop & mobile:
    - Sembunyikan menu Pelanggan, Layanan, dan Petugas untuk role pekerja.
    - Hanya tampilkan menu **Order Cucian** untuk pekerja.
    - Badge profil menampilkan `Pekerja Laundry` (`bg-amber-50 text-amber-700 border-amber-200`).
- **`resources/views/orders/index.blade.php`**:
  - Sembunyikan tombol "Terima Order Baru" jika `Auth::user()->isPekerja()`.
  - Pada filter form, sembunyikan atau nonaktifkan pilihan status cucian jika `Auth::user()->isPekerja()`.
  - Pada baris tabel, nonaktifkan trigger dropdown ubah status cucian jika `Auth::user()->isPekerja()` (tampilkan sebagai static badge biasa).
- **`resources/views/orders/show.blade.php`**:
  - Sembunyikan tombol "Update Status Cucian", form pelunasan tagihan, link "Edit Data Order", tombol "Hapus Order", dan link "Lihat Profil" pelanggan jika `Auth::user()->isPekerja()`.
- **`resources/views/users/create.blade.php` & `edit.blade.php`**:
  - Tambahkan radio option untuk role `pekerja` dengan label "Pekerja Laundry" beserta penjelasannya.
- **`resources/views/users/index.blade.php`**:
  - Tambahkan opsi filter dropdown untuk role `pekerja`.

---

## 4. Rencana Pengujian (Testing)
1. **`tests/Feature/WorkerRoleTest.php`**:
   - `test_pekerja_can_login_and_is_redirected_to_orders()`
   - `test_pekerja_accessing_dashboard_is_redirected_to_orders()`
   - `test_pekerja_can_logout()`
   - `test_pekerja_cannot_access_customers()`
   - `test_pekerja_cannot_access_services()`
   - `test_pekerja_cannot_access_payments()`
   - `test_pekerja_cannot_access_user_management()`
   - `test_pekerja_cannot_create_or_modify_orders()`
   - `test_pekerja_can_only_view_orders_with_status_diterima()`
   - `test_pekerja_cannot_view_order_detail_of_non_diterima_status()`
   - `test_admin_can_manage_pekerja_role()`
2. Memastikan seluruh test suite (`php artisan test`) lolos 100%.
