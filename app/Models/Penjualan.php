<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

class Penjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penjualan';

    protected $fillable = [
        'nomor',
        'tanggal_pengangkutan',
        'total_berat',
        'total_nilai',
        'dihapus_oleh',
    ];

    protected function casts(): array
    {
        return [
            'total_berat' => 'decimal:2',
            'total_nilai' => 'integer',
            'tanggal_pengangkutan' => 'date',
        ];
    }

    public function petugasPenghapus(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'dihapus_oleh');
    }

    protected static function booted(): void
    {
        static::created(function (Penjualan $penjualan) {
            if (empty($penjualan->nomor)) {
                $penjualan->nomor = 'T'.str_pad((string) $penjualan->id, 5, '0', STR_PAD_LEFT);
                $penjualan->saveQuietly();
            }
        });

        static::updating(function (Penjualan $penjualan) {
            if ($penjualan->isDirty('nomor') && $penjualan->getOriginal('nomor') !== null) {
                throw new InvalidArgumentException('Nomor transaksi penjualan tidak dapat diubah.');
            }
        });
    }
}
