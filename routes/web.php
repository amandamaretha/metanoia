<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterNasabahController;
use App\Http\Controllers\Bendahara\TransaksiKeuanganController;
use App\Http\Controllers\Ketua\PengajuanPenarikanController as KetuaPengajuanPenarikanController;
use App\Http\Controllers\Nasabah\PengajuanPenarikanController as NasabahPengajuanPenarikanController;
use App\Http\Controllers\Nasabah\RiwayatNasabahController;
use App\Http\Controllers\Nasabah\ScanQrController;
use App\Http\Controllers\Pengelola\DashboardController;
use App\Http\Controllers\Pengelola\JenisSampahController;
use App\Http\Controllers\Pengelola\LaporanController;
use App\Http\Controllers\Pengelola\NasabahController;
use App\Http\Controllers\Pengelola\PengaturanController;
use App\Http\Controllers\Pengelola\PenimbanganController;
use App\Models\Penarikan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('login');
});

// Pendaftaran nasabah baru
Route::get('/register/nasabah', [RegisterNasabahController::class, 'showRegistrationForm'])->name('nasabah.register');
Route::post('/register/nasabah', [RegisterNasabahController::class, 'register'])->name('nasabah.register.submit');

// Halaman login portal (Petugas / Nasabah)
Route::get('/login/{role}', [LoginController::class, 'showLoginForm'])
    ->whereIn('role', array_keys(config('metanoia.portals', [])))
    ->name('login.show');

// Proses login portal
Route::post('/login/{role}', [LoginController::class, 'login'])
    ->whereIn('role', array_keys(config('metanoia.portals', [])))
    ->name('login.attempt');

// Proses logout (semua guard)
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

// ==========================================
// ROUTE DASHBOARD PENGELOLA (/pengelola/*)
// Proteksi: auth:petugas + jabatan:...
// ==========================================
Route::middleware('auth:petugas')->prefix('pengelola')->group(function () {
    // Semua jabatan: pengaturan/profil/password
    Route::middleware('jabatan:pengelola,bendahara,ketua')->group(function () {
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengelola.pengaturan');
        Route::post('/pengaturan/profil', [PengaturanController::class, 'updateProfil'])->name('pengaturan.profil.update');
        Route::post('/pengaturan/profil/request-otp', [PengaturanController::class, 'requestOtpGantiTelepon'])->name('pengaturan.profil.request-otp');
        Route::post('/pengaturan/profil/verify-otp', [PengaturanController::class, 'verifyOtpGantiTelepon'])->name('pengaturan.profil.verify-otp');
        Route::post('/pengaturan/ubah-password', [PengaturanController::class, 'updatePassword'])->name('pengaturan.password.update');
    });

    // Laporan & Penjualan (Read-Only: Pengelola & Ketua) FASE 7 / DFD 6.0
    Route::middleware('jabatan:pengelola,ketua')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('pengelola.laporan');
        Route::get('/laporan/download', [LaporanController::class, 'downloadCsv'])->name('pengelola.laporan.download');
        Route::get('/laporan/penjualan', [LaporanController::class, 'penjualanIndex'])->name('pengelola.laporan.penjualan');
    });

    // Pengelola: dashboard, nasabah, detail-sampah, aksi penjualan, penimbangan, tambah & kelola akun
    Route::middleware('jabatan:pengelola')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('pengelola.dashboard');

        Route::get('/nasabah', [NasabahController::class, 'index'])->name('pengelola.nasabah');
        Route::get('/nasabah/{nasabah}/edit', [NasabahController::class, 'edit'])->name('pengelola.nasabah.edit');
        Route::put('/nasabah/{nasabah}', [NasabahController::class, 'update'])->name('pengelola.nasabah.update');
        Route::delete('/nasabah/{nasabah}', [NasabahController::class, 'destroy'])->name('pengelola.nasabah.destroy');

        Route::get('/detail-sampah', [JenisSampahController::class, 'index'])->name('pengelola.detail-sampah');
        Route::get('/detail-sampah/tambah', [JenisSampahController::class, 'create'])->name('pengelola.detail-sampah.tambah');
        Route::post('/detail-sampah', [JenisSampahController::class, 'store'])->name('pengelola.detail-sampah.store');
        Route::get('/detail-sampah/{jenis_sampah}/ubah', [JenisSampahController::class, 'edit'])->name('pengelola.detail-sampah.ubah');
        Route::put('/detail-sampah/{jenis_sampah}', [JenisSampahController::class, 'update'])->name('pengelola.detail-sampah.update');
        Route::delete('/detail-sampah/{jenis_sampah}', [JenisSampahController::class, 'destroy'])->name('pengelola.detail-sampah.destroy');

        // Aksi Penjualan (Khusus Pengelola)
        Route::post('/penjualan', [LaporanController::class, 'storePenjualan'])->name('pengelola.penjualan.store');
        Route::put('/penjualan/{penjualan}', [LaporanController::class, 'updatePenjualan'])->name('pengelola.penjualan.update');
        Route::delete('/penjualan/{penjualan}', [LaporanController::class, 'destroyPenjualan'])->name('pengelola.penjualan.destroy');

        // Tambah Akun & Kelola Akun Petugas FASE 9
        Route::get('/pengaturan/tambah-akun', [PengaturanController::class, 'tambahAkun'])->name('pengelola.petugas.tambah');
        Route::post('/pengaturan/tambah-akun', [PengaturanController::class, 'storeAkun'])->name('pengelola.petugas.store');

        Route::get('/pengaturan/kelola-akun', [PengaturanController::class, 'kelolaAkun'])->name('pengelola.petugas.index');
        Route::get('/pengaturan/kelola-akun/{petugas}/edit', [PengaturanController::class, 'editAkun'])->name('pengelola.petugas.edit');
        Route::put('/pengaturan/kelola-akun/{petugas}', [PengaturanController::class, 'updateAkun'])->name('pengelola.petugas.update');
        Route::delete('/pengaturan/kelola-akun/{petugas}', [PengaturanController::class, 'destroyAkun'])->name('pengelola.petugas.destroy');

        // Rute Penimbangan Sampah FASE 5
        Route::get('/penimbangan', [PenimbanganController::class, 'index'])->name('pengelola.penimbangan');
        Route::get('/penimbangan/nasabah/{nasabah}', [PenimbanganController::class, 'checkNasabah'])->name('pengelola.penimbangan.check-nasabah');
        Route::get('/penimbangan/harga', [PenimbanganController::class, 'getHarga'])->name('pengelola.penimbangan.get-harga');
        Route::post('/penimbangan/setoran', [PenimbanganController::class, 'store'])->name('pengelola.penimbangan.store');
        Route::post('/penimbangan/qr/create', [PenimbanganController::class, 'createQr'])->name('pengelola.penimbangan.qr.create');
        Route::post('/penimbangan/qr/refresh', [PenimbanganController::class, 'refreshQr'])->name('pengelola.penimbangan.qr.refresh');
        Route::get('/penimbangan/qr/status', [PenimbanganController::class, 'checkQrStatus'])->name('pengelola.penimbangan.qr.status');
        Route::post('/penimbangan/qr/cancel', [PenimbanganController::class, 'cancelQr'])->name('pengelola.penimbangan.qr.cancel');
    });

    // Bendahara: transaksi
    Route::get('/transaksi', [TransaksiKeuanganController::class, 'indexTambah'])
        ->middleware('jabatan:bendahara')
        ->name('pengelola.transaksi');
});

// ==========================================
// ROUTE BERANDA BENDAHARA & KETUA
// Proteksi: auth:petugas + jabatan:...
// ==========================================
Route::middleware('auth:petugas')->prefix('bendahara')->group(function () {
    // Khusus Bendahara: transaksi, riwayat, CRUD
    Route::middleware('jabatan:bendahara')->group(function () {
        Route::get('/beranda', function () {
            return redirect()->route('bendahara.transaksi');
        })->name('bendahara.beranda');

        // Transaksi Keuangan FASE 8
        Route::get('/transaksi', [TransaksiKeuanganController::class, 'indexTambah'])->name('bendahara.transaksi');
        Route::post('/transaksi', [TransaksiKeuanganController::class, 'store'])->name('bendahara.transaksi.store');

        Route::get('/riwayat', [TransaksiKeuanganController::class, 'riwayat'])->name('bendahara.riwayat');
        Route::get('/riwayat/download', [TransaksiKeuanganController::class, 'downloadCsv'])->name('bendahara.transaksi.download');

        Route::get('/transaksi/{transaksi}/edit', [TransaksiKeuanganController::class, 'edit'])->name('bendahara.transaksi.edit');
        Route::put('/transaksi/{transaksi}', [TransaksiKeuanganController::class, 'update'])->name('bendahara.transaksi.update');
        Route::delete('/transaksi/{transaksi}', [TransaksiKeuanganController::class, 'destroy'])->name('bendahara.transaksi.destroy');

        Route::get('/transaksi/{transaksi}/lampiran', [TransaksiKeuanganController::class, 'lampiran'])->name('bendahara.transaksi.lampiran');
    });

    // Laporan Keuangan (Read-Only: Bendahara & Ketua) DFD 6.0
    Route::middleware('jabatan:bendahara,ketua')->group(function () {
        Route::get('/laporan-keuangan', [TransaksiKeuanganController::class, 'laporan'])->name('bendahara.laporan-keuangan');
        Route::get('/laporan-keuangan/cetak', [TransaksiKeuanganController::class, 'cetakLaporan'])->name('bendahara.laporan-keuangan.cetak');
    });
});

Route::middleware('auth:petugas')->prefix('ketua')->group(function () {
    // Khusus Ketua
    Route::middleware('jabatan:ketua')->group(function () {
        Route::get('/beranda', function () {
            return redirect()->route('ketua.pengajuan-penarikan');
        })->name('ketua.beranda');

        // Rute Pengajuan Penarikan FASE 6
        Route::get('/pengajuan-penarikan', [KetuaPengajuanPenarikanController::class, 'index'])->name('ketua.pengajuan-penarikan');
        Route::get('/pengajuan-penarikan/{penarikan}', [KetuaPengajuanPenarikanController::class, 'show'])->name('ketua.pengajuan-penarikan.show');
    });

    // Dual-Role Approval: Ketua dan Bendahara
    Route::middleware('jabatan:ketua,bendahara')->group(function () {
        Route::post('/pengajuan-penarikan/{penarikan}/setujui', [KetuaPengajuanPenarikanController::class, 'setujui'])->name('ketua.pengajuan-penarikan.setujui');
        Route::post('/pengajuan-penarikan/{penarikan}/tolak', [KetuaPengajuanPenarikanController::class, 'tolak'])->name('ketua.pengajuan-penarikan.tolak');
    });
});

// ==========================================
// ROUTE BERANDA NASABAH
// Proteksi: auth:nasabah
// ==========================================
Route::middleware('auth:nasabah')->prefix('nasabah')->group(function () {
    Route::get('/beranda', function () {
        $nasabah = Auth::guard('nasabah')->user();
        $pengajuanMenunggu = Penarikan::where('nasabah_id', $nasabah->id)
            ->where('status', 'menunggu')
            ->first();
        $riwayatPenarikan = Penarikan::where('nasabah_id', $nasabah->id)
            ->latest('tanggal_pengajuan')
            ->take(5)
            ->get();

        return view('nasabah.beranda', compact('nasabah', 'pengajuanMenunggu', 'riwayatPenarikan'));
    })->name('nasabah.beranda');

    Route::post('/pengajuan-penarikan', [NasabahPengajuanPenarikanController::class, 'store'])->name('nasabah.pengajuan-penarikan');
    Route::post('/scan-qr', [ScanQrController::class, 'scan'])->name('nasabah.scan-qr');
    Route::get('/riwayat', [RiwayatNasabahController::class, 'index'])->name('nasabah.riwayat');
    Route::get('/nota/{nota}', [RiwayatNasabahController::class, 'showNota'])->name('nasabah.nota');
});
// ==========================================

Route::get('/lupa-password', [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/lupa-password', [PasswordResetController::class, 'sendOtp'])->name('password.send');
Route::get('/kode-verifikasi', [PasswordResetController::class, 'showVerificationForm'])->name('verification.page');
Route::post('/kode-verifikasi', [PasswordResetController::class, 'checkOtp'])->name('verification.check');
Route::get('/kata-sandi-baru', [PasswordResetController::class, 'showResetForm'])->name('password.new');
Route::post('/kata-sandi-baru', [PasswordResetController::class, 'updatePassword'])->name('password.update');
