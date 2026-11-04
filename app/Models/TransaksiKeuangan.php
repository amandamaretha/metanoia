<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiKeuangan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaksi_keuangan';

    protected $fillable = [
        'petugas_id',
        'tanggal',
        'jenis',
        'nominal',
        'keterangan',
        'lampiran',
        'dihapus_oleh',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'integer',
            'tanggal' => 'date',
        ];
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'petugas_id');
    }

    public function petugasPenghapus(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'dihapus_oleh');
    }
}
