<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nota extends Model
{
    use HasFactory;

    protected $table = 'nota';

    protected $fillable = [
        'setoran_id',
        'nomor',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
        ];
    }

    public function setoran(): BelongsTo
    {
        return $this->belongsTo(Setoran::class, 'setoran_id');
    }
}
