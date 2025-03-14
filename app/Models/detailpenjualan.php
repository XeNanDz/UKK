<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class detailpenjualan extends Model

{
    use HasFactory;

    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'harga_jual',
        'qty',
        'sub_total',
    ];

    // Relasi ke tabel penjualan
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    // Relasi ke tabel produk
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
