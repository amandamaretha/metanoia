<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penarikan extends Model
{
    use HasFactory;

    protected $table = 'penarikan';

    protected $fillable = [
        'nasabah_id',
        'nominal',
        'status',
        'catatan',
        'diputuskan_oleh',
        'tanggal_pengajuan',
        'tanggal_keputusan',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'integer',
            'tanggal_pengajuan' => 'datetime',
            'tanggal_keputusan' => 'datetime',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'diputuskan_oleh');
    }
}
