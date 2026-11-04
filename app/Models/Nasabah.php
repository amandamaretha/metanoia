<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Nasabah extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'nasabah';

    /**
     * The attributes that are mass assignable.
     * Fillable TANPA 'status' dan 'saldo' demi keamanan integritas data.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'nik',
        'alamat',
        'no_telp',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'saldo' => 'integer',
        ];
    }

    public function setoran(): HasMany
    {
        return $this->hasMany(Setoran::class, 'nasabah_id');
    }

    public function penarikan(): HasMany
    {
        return $this->hasMany(Penarikan::class, 'nasabah_id');
    }

    public function mutasiSaldo(): HasMany
    {
        return $this->hasMany(MutasiSaldo::class, 'nasabah_id');
    }

    /**
     * Tampilkan NIK sebagian (6 digit awal, 4 akhir, sisanya sensor bintang).
     */
    public function getMaskedNikAttribute(): string
    {
        $nik = (string) $this->nik;
        $len = strlen($nik);

        if ($len <= 10) {
            return $nik;
        }

        return substr($nik, 0, 6).str_repeat('*', $len - 10).substr($nik, -4);
    }
}
