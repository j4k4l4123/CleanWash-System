# Panduan Deployment ke Vercel Serverless

Dokumen ini menjelaskan cara melakukan deployment CleanWash System ke **Vercel** menggunakan serverless PHP runtime (`vercel-php@0.8.0`) dan database PostgreSQL yang di-host di **Supabase**.

---

## 🏗️ Ringkasan Arsitektur

- **Compute/Hosting**: Vercel Serverless Function (PHP 8.4 via `vercel-php@0.8.0`)
- **Frontend Asset**: Vite + Tailwind CSS v4 dikompilasi saat build (`npm run build`)
- **Database**: PostgreSQL di Supabase (AWS ap-southeast-2 Pooler)
- **Session & Cache**: Cookie-based session (`SESSION_DRIVER=cookie`) & array cache (`CACHE_STORE=array`) yang aman untuk environment stateless / serverless.
- **Writable Directory**: Penyimpanan temporary diarahkan ke `/tmp/storage` dan `/tmp/bootstrap/cache` secara otomatis melalui `api/index.php`.

---

## 📋 File Konfigurasi Deployment di Repository

1. **[`vercel.json`](file:///C:/Users/ALVIN%20DWI%20LINTANG%20H/.openclaw/workspace/Project%20an/Project%20Laundry%20Kelompok%202/vercel.json)**:
   - Mendefinisikan function `api/index.php` dengan runtime `vercel-php@0.8.0`.
   - Mengatur routing asset statis (`/css`, `/js`, `/build`, favicon, dll) langsung ke `/public`.
   - Mengarahkan semua request web lainnya ke `api/index.php`.
2. **[`api/index.php`](file:///C:/Users/ALVIN%20DWI%20LINTANG%20H/.openclaw/workspace/Project%20an/Project%20Laundry%20Kelompok%202/api/index.php)**:
   - Membuat direktori temporary di `/tmp` untuk storage dan cache Laravel.
   - Menangani proxy HTTPS agar tidak terjadi isu form submission mixed-content.
3. **[`.env.vercel.example`](file:///C:/Users/ALVIN%20DWI%20LINTANG%20H/.openclaw/workspace/Project%20an/Project%20Laundry%20Kelompok%202/.env.vercel.example)**:
   - Berisi daftar seluruh environment variable yang wajib diinput di Vercel Dashboard.

---

## 🚀 Langkah Deployment

### Opsi 1: Otomatis via Vercel Dashboard (Rekomendasi)

1. Buka [https://vercel.com/new](https://vercel.com/new) dan login dengan akun GitHub Anda.
2. Cari dan klik **Import** pada repositori **`j4k4l4123/CleanWash-System`**.
3. Di halaman **Configure Project**:
   - **Framework Preset**: Biarkan `Other` (atau `None`).
   - **Root Directory**: `./` (default).
   - **Build Command**: Biarkan `npm run build` (atau override jika diperlukan).
   - **Output Directory**: Biarkan `public`.
4. Buka bagian **Environment Variables**, lalu tambahkan variabel berikut (bisa disalin dari file `.env.vercel.example`):

| Key | Value Contoh | Keterangan |
| :--- | :--- | :--- |
| `APP_NAME` | `CleanWash System` | Nama aplikasi |
| `APP_ENV` | `production` | Environment mode |
| `APP_KEY` | `base64:+Qwv0L/CqUJLAdrCDhtpIeI7s3kHooT605F1S4Fjan4=` | Enkripsi app key |
| `APP_DEBUG` | `false` | Matikan debug mode untuk keamanan |
| `APP_URL` | `https://<nama-project>.vercel.app` | URL domain Vercel Anda |
| `DB_CONNECTION` | `pgsql` | Driver database |
| `DB_HOST` | `aws-0-ap-southeast-2.pooler.supabase.com` | Host Supabase |
| `DB_PORT` | `6543` | Port Supabase Pooler |
| `DB_DATABASE` | `postgres` | Nama DB Supabase |
| `DB_USERNAME` | `postgres.duexbbegjgdmmvxzlfgm` | Username DB Supabase |
| `DB_PASSWORD` | `<password-db-anda>` | Password DB Supabase |
| `DB_SSLMODE` | `require` | SSL mode Supabase |
| `SESSION_DRIVER` | `cookie` | Sesi berbasis cookie stateless |
| `SESSION_SECURE_COOKIE` | `true` | Cookie hanya via HTTPS |
| `CACHE_STORE` | `array` | Cache store in-memory stateless |
| `LOG_CHANNEL` | `stderr` | Log langsung ke Vercel runtime log |

5. Klik tombol **Deploy**.
6. Vercel akan otomatis:
   - Menginstall dependensi npm & mengeksekusi `npm run build`
   - Mempersiapkan function serverless PHP 8.4
   - Memberikan URL deployment production (misal `https://clean-wash-system.vercel.app`).

Setiap kali Anda melakukan `git push origin master`, Vercel akan langsung melakukan deployment ulang secara otomatis!

---

### Opsi 2: Menggunakan Vercel CLI

Jika ingin men-deploy langsung dari terminal:

1. Pastikan Anda sudah login ke akun Vercel:
   ```bash
   npx vercel login
   ```
2. Hubungkan proyek lokal dengan project Vercel:
   ```bash
   npx vercel link
   ```
3. Set environment variables atau import file `.env.vercel.example`:
   ```bash
   npx vercel env pull
   ```
4. Jalankan deployment ke production:
   ```bash
   npx vercel --prod
   ```

---

## 🔄 Migrasi & Seeder Database

Karena Vercel adalah lingkungan serverless tanpa shell SSH persisten, migrasi database dijalankan dari lokal (karena lokal terhubung langsung ke database Supabase yang sama):

- **Cek Status Migrasi di Supabase:**
  ```bash
  php artisan migrate:status
  ```
- **Jalankan Migrasi Baru:**
  ```bash
  php artisan migrate --force
  ```
- **Jalankan Seeder (jika database masih kosong):**
  ```bash
  php artisan db:seed --force
  ```
Semua perubahan database yang dijalankan di lokal akan otomatis tercermin di aplikasi yang tayang di Vercel.
