<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pemetaan Portal ke Auth Guard
    |--------------------------------------------------------------------------
    |
    | Setiap segmen portal ({role}) dipetakan ke guard otentikasi masing-masing
    | (pengelola, bendahara, nasabah) tanpa kolom role di tabel database.
    |
    */
    'portals' => [
        'petugas' => 'petugas',
        'nasabah' => 'nasabah',
    ],

    /*
    |--------------------------------------------------------------------------
    | Label Tampilan Portal
    |--------------------------------------------------------------------------
    */
    'portal_labels' => [
        'petugas' => 'Petugas',
        'nasabah' => 'Nasabah',
    ],

    /*
    |--------------------------------------------------------------------------
    | Jabatan Petugas
    |--------------------------------------------------------------------------
    */
    'jabatan' => [
        'pengelola',
        'bendahara',
        'ketua',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Field Telepon di Form
    |--------------------------------------------------------------------------
    */
    'phone_field' => 'telepon',

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi OTP (One-Time Password)
    |--------------------------------------------------------------------------
    |
    | - ttl: masa berlaku kode dalam detik (300 detik = 5 menit).
    | - max_attempts: batas toleransi percobaan kode salah sebelum hangus.
    | - resend_cooldown: jeda minimal sebelum boleh meminta kirim ulang (60 detik).
    | - max_failures_per_hour: batas toleransi salah verifikasi per jam sebelum akun terkunci.
    | - driver: driver pengiriman ('log' atau 'null').
    |
    */
    'otp' => [
        'ttl' => 300,
        'max_attempts' => 5,
        'resend_cooldown' => 60,
        'max_failures_per_hour' => 10, // Maksimal percobaan verifikasi salah per jam
        'driver' => env('OTP_DRIVER', 'log'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Penjualan ke BSI
    |--------------------------------------------------------------------------
    |
    | Batas minimum berat sampah terkumpul (kg) untuk pengangkutan ke BSI.
    |
    */
    'penjualan' => [
        'batas_minimum_kg' => 100,
    ],
];
