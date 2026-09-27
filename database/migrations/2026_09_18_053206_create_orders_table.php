<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode_order')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('berat_atau_jumlah', 8, 2);
            $table->decimal('harga_per_satuan', 12, 2);
            $table->decimal('total_harga', 12, 2);
            $table->dateTime('tgl_masuk');
            $table->dateTime('estimasi_selesai');
            $table->dateTime('tgl_selesai')->nullable();
            $table->dateTime('tgl_diambil')->nullable();
            $table->string('status_cucian')->default('diterima'); // diterima, siap_diambil, selesai
            $table->string('status_pembayaran')->default('belum_lunas'); // belum_lunas, lunas
            $table->string('metode_pembayaran')->nullable(); // tunai, transfer, qris
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
