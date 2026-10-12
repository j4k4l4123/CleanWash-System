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
        Schema::table('orders', function (Blueprint $table) {
            $table->index('tgl_masuk');
            $table->index(['status_cucian', 'tgl_masuk']);
            $table->index(['status_pembayaran', 'tgl_masuk']);
            $table->index(['customer_id', 'tgl_masuk']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('nama');
            $table->index('no_hp');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('tgl_bayar');
            $table->index(['order_id', 'tgl_bayar']);
        });

        Schema::table('order_status_logs', function (Blueprint $table) {
            $table->index(['order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['tgl_masuk']);
            $table->dropIndex(['status_cucian', 'tgl_masuk']);
            $table->dropIndex(['status_pembayaran', 'tgl_masuk']);
            $table->dropIndex(['customer_id', 'tgl_masuk']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['nama']);
            $table->dropIndex(['no_hp']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['tgl_bayar']);
            $table->dropIndex(['order_id', 'tgl_bayar']);
        });

        Schema::table('order_status_logs', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'created_at']);
        });
    }
};
