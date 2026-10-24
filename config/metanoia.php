<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Daftar Role Aplikasi
    |--------------------------------------------------------------------------
    |
    | Mendefinisikan role akun yang didukung di aplikasi Metanoia.
    |
    */
    'roles' => [
        'pengelola' => 'pengelola',
        'bendahara' => 'bendahara',
        'nasabah'   => 'nasabah',
    ],

    /*
    |--------------------------------------------------------------------------
    | Label Tampilan Role
    |--------------------------------------------------------------------------
    */
    'role_labels' => [
        'pengelola' => 'Pengelola',
        'bendahara' => 'Bendahara',
        'nasabah'   => 'Nasabah',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Kolom Autentikasi dan Role
    |--------------------------------------------------------------------------
    |
    | Kolom kredensial login dan role agar tidak ada literal string acak di kode.
    |
    */
    'columns' => [
        'login' => 'telepon',
        'role'  => 'role',
    ],
];
