<?php

use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('login');
});

// Halaman login role (Pengelola / Bendahara)
Route::get('/login/{role}', function (string $role) {
    return view('login-role', [
        'role' => config("metanoia.portal_labels.{$role}", ucfirst($role)),
    ]);
})->whereIn('role', [
    config('metanoia.portals.pengelola'),
    config('metanoia.portals.bendahara'),
])->name('login.show');

// ==========================================
// KODE UNTUK TESTING (TANPA DATABASE) & DASHBOARD ROUTE
// ==========================================
// Proses login mock pengelola & bendahara
Route::post('/login/{role}', function (Request $request, string $role) {
    if ($role === config('metanoia.portals.pengelola')) {
        return redirect()->route('pengelola.dashboard');
    }

    if ($role === config('metanoia.portals.bendahara')) {
        $request->validate([
            config('metanoia.phone_field', 'telepon') => 'required',
            'password' => 'required',
        ]);

        return back()->with('success', 'Form login bendahara berhasil dikirim.');
    }

    abort(404);
})->whereIn('role', [
    config('metanoia.portals.pengelola'),
    config('metanoia.portals.bendahara'),
])->name('login.attempt');

// Route Dashboard Pengelola (Folder: resources/views/dashboard/pengelola.blade.php)
Route::get('/pengelola/dashboard', function () {
    return view('dashboard.dashboard-pengelola');
})->name('pengelola.dashboard');

// Route Data Nasabah Pengelola (Folder: resources/views/dashboard/nasabah.blade.php)
Route::get('/pengelola/nasabah', function () {
    return view('dashboard.nasabah');
})->name('pengelola.nasabah');

// Route Pada Dashboard Halaman Edit Nasabah
Route::get('/pengelola/nasabah/edit', function () {
    return view('dashboard.nasabah-edit');
})->name('pengelola.nasabah.edit');

// Halaman Riwayat Transaksi Pengelola
Route::get('/pengelola/transaksi', function () {
    return view('dashboard.transaksi');
})->name('pengelola.transaksi');

// Halaman Detail Sampah Pengelola
Route::get('/pengelola/detail-sampah', function () {
    return view('dashboard.detail-sampah');
})->name('pengelola.detail-sampah');

// Halaman Form Tambah Produk
Route::get('/pengelola/detail-sampah/tambah', function () {
    return view('dashboard.detail-sampah-tambah');
})->name('pengelola.detail-sampah.tambah');

// Halaman Form Ubah Produk
Route::get('/pengelola/detail-sampah/ubah', function () {
    return view('dashboard.detail-sampah-edit');
})->name('pengelola.detail-sampah.ubah');

// Halaman Laporan Tab Setoran
Route::get('/pengelola/laporan', function () {
    return view('dashboard.laporan');
})->name('pengelola.laporan');

// Halaman Laporan Tab Penjualan (BARU)
Route::get('/pengelola/laporan/penjualan', function () {
    return view('dashboard.penjualan');
})->name('pengelola.laporan.penjualan');

// Halaman Pengaturan Pengelola
Route::get('/pengelola/pengaturan', function () {
    return view('dashboard.pengaturan');
})->name('pengelola.pengaturan');

// Halaman Penimbangan
Route::get('/pengelola/penimbangan', function () {
    return view('dashboard.penimbangan');
})->name('pengelola.penimbangan');
// ==========================================


Route::get('/lupa-password', [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/lupa-password', [PasswordResetController::class, 'sendOtp'])->name('password.send');
Route::get('/kode-verifikasi', [PasswordResetController::class, 'showVerificationForm'])->name('verification.page');
Route::post('/kode-verifikasi', [PasswordResetController::class, 'checkOtp'])->name('verification.check');
Route::get('/kata-sandi-baru', [PasswordResetController::class, 'showResetForm'])->name('password.new');
Route::post('/kata-sandi-baru', [PasswordResetController::class, 'updatePassword'])->name('password.update');
