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
        'pengelola' => 'pengelola',
        'bendahara' => 'bendahara',
        'nasabah'   => 'nasabah',
    ],

    /*
    |--------------------------------------------------------------------------
    | Label Tampilan Portal
    |--------------------------------------------------------------------------
    */
    'portal_labels' => [
        'pengelola' => 'Pengelola',
        'bendahara' => 'Bendahara',
        'nasabah'   => 'Nasabah',
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
    | - driver: driver pengiriman ('log' atau 'null').
    |
    */
    'otp' => [
        'ttl'             => 300,
        'max_attempts'    => 5,
        'resend_cooldown' => 60,
        'driver'          => env('OTP_DRIVER', 'log'),
    ],
];
