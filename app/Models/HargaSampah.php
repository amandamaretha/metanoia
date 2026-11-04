<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaSampah extends Model
{
    use HasFactory;

    protected $table = 'harga_sampah';

    protected $fillable = [
        'jenis_sampah_id',
        'kondisi',
        'harga_per_kg',
        'tanggal_mulai',
        'tanggal_berakhir',
    ];

    protected function casts(): array
    {
        return [
            'harga_per_kg' => 'integer',
            'tanggal_mulai' => 'date',
            'tanggal_berakhir' => 'date',
        ];
    }

    public function jenisSampah(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_sampah_id');
    }
}
