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
        Schema::create('penjualans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
                $table->decimal('diskon');
                $table->decimal('total_harga');
                $table->foreignId('pembayaran_id')
                ->nullable()
                ->constrained('pembayarans')
                ->nullOnDelete();
                $table->timestamp('tanggal_penjualan');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
