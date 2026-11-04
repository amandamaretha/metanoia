<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSampah extends Model
{
    use HasFactory;

    protected $table = 'jenis_sampah';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'kategori',
        'gambar',
    ];

    public function hargaSampah(): HasMany
    {
        return $this->hasMany(HargaSampah::class, 'jenis_sampah_id');
    }

    public function hargaTerbaru(): HasMany
    {
        return $this->hasMany(HargaSampah::class, 'jenis_sampah_id')
            ->whereNull('tanggal_berakhir');
    }

    public function getHarga(string $kondisi): ?HargaSampah
    {
        return $this->hargaSampah
            ->where('kondisi', $kondisi)
            ->whereNull('tanggal_berakhir')
            ->first();
    }

    public function setoran(): HasMany
    {
        return $this->hasMany(Setoran::class, 'jenis_sampah_id');
    }
}
