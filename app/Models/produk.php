<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori_id',
        'nama_produk',
        'harga_beli',
        'harga_jual',
        'stok',
        'slug',
        'image',
        'is_active',
        'barcode',
    ];

    protected $appends = ['image_url'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public static function generateUniqueSlug(string $nama_produk): string
    {
        $slug = Str::slug($nama_produk);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function getImageUrlAttribute()
    {
        // Pastikan kolom `image` menyimpan path relatif ke storage
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        // Jika tidak ada gambar, kembalikan URL gambar default
        return asset('images/default-product.png'); // Ganti dengan path gambar default Anda
    }

    public function scopeSearch($query, $value)
    {
        $query->where("nama_produk", "like", "%{$value}%");
    }

    public function generateBarcode()
    {
        return DNS1D::getBarcodeHTML($this->barcode, 'C128'); // Format barcode C128
    }
}
