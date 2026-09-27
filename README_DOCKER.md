# Panduan Menjalankan Project dengan Docker

Project ini sudah dilengkapi konfigurasi **Docker Compose**. Siapapun yang menerima project ini tidak perlu menginstall PHP, Composer, Node.js, ataupun PostgreSQL di komputernya—cukup menginstall **Docker Desktop**.

---

## 📋 Prasyarat

- Pastikan sudah menginstall [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows / Mac / Linux).
- Pastikan Docker Desktop sudah berjalan (*running*).

---

## 🚀 Cara Menjalankan (Otomatis)

1. Buka Terminal / PowerShell di folder project ini.
2. Jalankan perintah berikut:

   ```bash
   docker compose up -d --build
   ```

3. Docker akan secara otomatis:
   - Mengunduh image dan merakit environment PHP 8.4 & Nginx.
   - Menyiapkan file `.env` dari `.env.docker.example`.
   - Menginstall dependensi Composer (`vendor`).
   - Melakukan build asset tampilan frontend (`npm run build`).
   - Membuat key enkripsi aplikasi (`php artisan key:generate`).
   - Menjalankan migrasi database PostgreSQL dan mengisi data awal (*seeding*).

4. Buka browser dan akses aplikasi:
   - **Aplikasi Web**: [http://localhost:8000](http://localhost:8000)
   - **pgAdmin (Manajemen Database)**: [http://localhost:5050](http://localhost:5050)

---

## 🔑 Akun Login Bawaan (Default Seeder)

Aplikasi sudah memiliki data akun awal yang siap langsung diuji:

| Peran (Role) | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@laundry.test` | `password` |
| **Kasir** | `kasir@laundry.test` | `password` |

---

## 🗄️ Mengakses Database via pgAdmin

Jika ingin melihat tabel database secara visual:
1. Buka [http://localhost:5050](http://localhost:5050).
2. Login ke pgAdmin:
   - **Email**: `admin@laundry.test`
   - **Password**: `admin`
3. Klik kanan pada **Servers** -> **Register** -> **Server...**:
   - Tab **General**:
     - *Name*: `Laundry DB`
   - Tab **Connection**:
     - *Host name/address*: `db`
     - *Port*: `5432`
     - *Maintenance database*: `LaundryDB`
     - *Username*: `postgres`
     - *Password*: `secret`
4. Klik **Save**. Seluruh tabel aplikasi laundry dapat dilihat di sana.

---

## 🛠️ Perintah Berguna (Cheat Sheet)

Semua perintah dijalankan dari folder project di terminal:

- **Melihat log container (jika ada kendala):**
  ```bash
  docker compose logs -f
  ```

- **Menghentikan container:**
  ```bash
  docker compose down
  ```

- **Menjalankan perintah Artisan di dalam container:**
  ```bash
  docker compose exec app php artisan [perintah]
  ```
  *Contoh migrasi ulang:*
  ```bash
  docker compose exec app php artisan migrate:fresh --seed
  ```

- **Masuk ke terminal dalam container:**
  ```bash
  docker compose exec app bash
  ```

- **Menjalankan Vite dev (jika ingin live-reload asset):**
  ```bash
  docker compose exec app npm run dev -- --host 0.0.0.0
  ```

---

## 📦 Tips Saat Mengirimkan Project ke Teman (ZIP / Git)

Sebelum folder di-compress jadi ZIP untuk dikirim ke teman, pastikan:
1. **HAPUS / JANGAN masukkan folder berikut** (karena ukurannya sangat besar dan akan otomatis dibuat ulang oleh Docker):
   - `vendor/`
   - `node_modules/`
   - `public/build/`
   - `.phpunit.result.cache`
2. Pastikan file berikut **IKUT TERKIRIM**:
   - `docker/` (seluruh isi folder)
   - `docker-compose.yml`
   - `.dockerignore`
   - `.env.docker.example`
   - `.env.example`
   - `README_DOCKER.md`
