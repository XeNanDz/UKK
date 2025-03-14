<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProduksTable extends Migration
{
    public function up()
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')
            ->nullable()
            ->constrained('kategoris')
            ->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('nama_produk');
            $table->decimal('harga_beli');
            $table->decimal('harga_jual');
            $table->smallInteger('stok')->default(1);
            $table->string('barcode')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('produks');
    }
}
