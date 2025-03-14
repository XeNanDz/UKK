<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'Metode_pembayaran',
        'image',
        'is_cash',
    ];

    protected $appends = ['image_url'];

    public function orders(): HasMany
    {
        return $this->hasMany(penjualan::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? url('storage/'. $this->image) : null;
    }
}
