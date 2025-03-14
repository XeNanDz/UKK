<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class penjualan extends Model

{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pelanggan_id',
        'diskon',
        'total_harga',
        'pembayaran_id',
        'tanggal_penjualan',
    ];

    // Relasi ke tabel user (kasir/admin)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke tabel pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pembayarans()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    // Relasi ke tabel detail_penjualan
    public function detailPenjualans()
    {
        return $this->hasMany(DetailPenjualan::class);
    }
}
