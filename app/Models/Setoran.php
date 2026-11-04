<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Setoran extends Model
{
    use HasFactory;

    protected $table = 'setoran';

    protected $fillable = [
        'nasabah_id',
        'petugas_id',
        'jenis_sampah_id',
        'kondisi',
        'berat',
        'harga_per_kg',
        'total_nilai',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'berat' => 'decimal:2',
            'harga_per_kg' => 'integer',
            'total_nilai' => 'integer',
            'tanggal' => 'datetime',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'petugas_id');
    }

    public function jenisSampah(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_sampah_id');
    }

    public function nota(): HasOne
    {
        return $this->hasOne(Nota::class, 'setoran_id');
    }
}
