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
                $table->decimal('diskon')->nullable();
                $table->decimal('total_harga')->nullable();
                $table->decimal('uang_pembayaran')->nullable();
                $table->decimal('kembalian')->nullable();
                $table->foreignId('pembayaran_id')
                ->nullable()
                ->constrained('pembayarans')
                ->nullOnDelete();
                $table->timestamps();


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
