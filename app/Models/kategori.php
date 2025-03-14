<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class kategori extends Model

{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'slug',
        'image',
        'is_active',
    ];

    public function produks():HasMany
    {
        return $this->hasMany(Produk::class);
    }
    public static function generateUniqueSlug(string $nama_kategori): string
    {
        $slug = Str::slug($nama_kategori);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;

}
}
