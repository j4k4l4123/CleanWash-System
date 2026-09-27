#!/bin/bash
set -e

echo "=================================================="
echo " Starting Laundry Kelompok 2 Environment Setup... "
echo "=================================================="

cd /var/www/html

# 1. Pastikan file .env ada
if [ ! -f .env ]; then
    if [ -f .env.docker.example ]; then
        echo "[1/6] File .env tidak ditemukan. Menyalin dari .env.docker.example..."
        cp .env.docker.example .env
    elif [ -f .env.example ]; then
        echo "[1/6] File .env tidak ditemukan. Menyalin dari .env.example..."
        cp .env.example .env
    fi
else
    echo "[1/6] File .env sudah tersedia."
fi

# 2. Pastikan Composer dependencies terinstall
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "[2/6] Menginstal dependensi Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
else
    echo "[2/6] Composer dependensi sudah terinstall."
fi

# 3. Generate Application Key jika belum ada
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "[3/6] Menghasilkan APP_KEY baru..."
    php artisan key:generate --force
else
    echo "[3/6] APP_KEY sudah tersedia."
fi

# 4. Build asset frontend (Vite & Tailwind) jika belum ada
if [ ! -d "public/build" ]; then
    echo "[4/6] Menyiapkan asset frontend (npm install & build)..."
    if [ ! -d "node_modules" ]; then
        npm install --no-audit
    fi
    npm run build
else
    echo "[4/6] Asset frontend (public/build) sudah siap."
fi

# 5. Tunggu database PostgreSQL siap
echo "[5/6] Memeriksa koneksi database ($DB_HOST:$DB_PORT)..."
MAX_RETRIES=30
COUNT=0
until nc -z -v -w3 "$DB_HOST" "$DB_PORT" 2>/dev/null || [ $COUNT -ge $MAX_RETRIES ]; do
    echo "Menunggu database PostgreSQL ($DB_HOST:$DB_PORT) siap... ($COUNT/$MAX_RETRIES)"
    sleep 2
    COUNT=$((COUNT+1))
done

if [ $COUNT -ge $MAX_RETRIES ]; then
    echo "PERINGATAN: Database belum merespons setelah 60 detik. Melanjutkan proses..."
else
    echo "Database PostgreSQL siap terhubung!"
fi

# 6. Jalankan migrasi dan seeding data awal jika diperlukan
echo "[6/6] Menjalankan migrasi database..."
php artisan migrate --force

USER_COUNT=$(php artisan tinker --execute="try { echo \App\Models\User::count(); } catch (\Throwable \$e) { echo '0'; }" 2>/dev/null | tr -dc '0-9' || echo "0")
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Data awal kosong, menjalankan database seeder..."
    php artisan db:seed --force
    echo "Seeding data default selesai!"
else
    echo "Database sudah berisi data ($USER_COUNT pengguna terdaftar)."
fi

# Pastikan permission folder storage dan cache aman
mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "=================================================="
echo " Project Laundry Kelompok 2 siap digunakan!       "
echo " Akses Web    : http://localhost:8000             "
echo " Akses pgAdmin: http://localhost:5050             "
echo "=================================================="

exec "$@"
