<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Petugas extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'petugas';

    /**
     * The attributes that are mass assignable.
     * Fillable TANPA 'jabatan' demi keamanan.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'no_telp',
        'password',
        'foto',
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
        ];
    }

    public function setoran(): HasMany
    {
        return $this->hasMany(Setoran::class, 'petugas_id');
    }

    public function penarikanDiputuskan(): HasMany
    {
        return $this->hasMany(Penarikan::class, 'diputuskan_oleh');
    }

    public function transaksiKeuangan(): HasMany
    {
        return $this->hasMany(TransaksiKeuangan::class, 'petugas_id');
    }
}
