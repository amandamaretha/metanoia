<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\Petugas;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function __construct(
        protected ?OtpService $otpService = null
    ) {
        $this->otpService = $otpService ?? app(OtpService::class);
    }

    /**
     * Halaman Pengaturan Utama & Profil.
     * Dapat diakses oleh semua jabatan petugas (Pengelola, Bendahara, Ketua).
     */
    public function index(): View
    {
        $petugas = Auth::guard('petugas')->user();

        return view('dashboard.pengaturan', compact('petugas'));
    }

    /**
     * Request OTP untuk ganti nomor telepon profil (dikirim ke nomor BARU).
     */
    public function requestOtpGantiTelepon(Request $request): JsonResponse|RedirectResponse
    {
        /** @var Petugas $currentUser */
        $currentUser = Auth::guard('petugas')->user();

        $request->validate([
            'telepon_lama' => ['required', 'string'],
            'telepon_baru' => ['required', 'string'],
        ], [
            'telepon_lama.required' => 'Nomor telepon lama wajib diisi.',
            'telepon_baru.required' => 'Nomor telepon baru wajib diisi.',
        ]);

        try {
            $normalizedOld = OtpService::normalizePhoneNumber($request->input('telepon_lama'));
        } catch (\Throwable $e) {
            return $this->errorResponse($request, 'Nomor telepon lama tidak valid.', 'telepon_lama');
        }

        try {
            $normalizedNew = OtpService::normalizePhoneNumber($request->input('telepon_baru'));
        } catch (\Throwable $e) {
            return $this->errorResponse($request, 'Nomor telepon baru tidak valid.', 'telepon_baru');
        }

        // 1. Nomor lama harus cocok dengan nomor akun saat ini
        if ($normalizedOld !== OtpService::normalizePhoneNumber($currentUser->no_telp)) {
            return $this->errorResponse($request, 'Nomor telepon lama tidak sesuai dengan akun Anda.', 'telepon_lama');
        }

        // 2. Nomor baru tidak boleh sama dengan nomor lama
        if ($normalizedNew === $normalizedOld) {
            return $this->errorResponse($request, 'Nomor telepon baru harus berbeda dengan nomor lama.', 'telepon_baru');
        }

        // 3. Nomor baru harus unik di tabel petugas dan nasabah
        $existsInPetugas = Petugas::where('no_telp', $normalizedNew)->exists();
        $existsInNasabah = Nasabah::where('no_telp', $normalizedNew)->exists();
        if ($existsInPetugas || $existsInNasabah) {
            return $this->errorResponse($request, 'Nomor telepon baru sudah terdaftar dalam sistem.', 'telepon_baru');
        }

        // 4. Terbitkan OTP 6 digit dengan purpose 'ganti_telepon' ke nomor baru
        try {
            $this->otpService->issue('petugas', $normalizedNew, 'ganti_telepon');
        } catch (\Throwable $e) {
            return $this->errorResponse($request, $e->getMessage(), 'telepon_baru');
        }

        // 5. Simpan context verifikasi sementara di session terikat ID user
        session([
            'ganti_telepon_pending' => [
                'petugas_id' => $currentUser->id,
                'telepon_baru' => $normalizedNew,
                'verified' => false,
            ],
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kode verifikasi telah dikirim ke nomor baru.',
                'telepon_baru' => $normalizedNew,
            ]);
        }

        return back()->with('otp_sent', true)->with('telepon_baru', $normalizedNew);
    }

    /**
     * Verifikasi kode OTP ganti nomor telepon.
     */
    public function verifyOtpGantiTelepon(Request $request): JsonResponse|RedirectResponse
    {
        /** @var Petugas $currentUser */
        $currentUser = Auth::guard('petugas')->user();

        $request->validate([
            'kode' => ['required', 'string', 'size:6'],
        ], [
            'kode.required' => 'Kode verifikasi 6 digit wajib diisi.',
            'kode.size' => 'Kode verifikasi harus tepat 6 digit.',
        ]);

        $pending = session('ganti_telepon_pending');
        if (! is_array($pending) || ($pending['petugas_id'] ?? null) !== $currentUser->id || empty($pending['telepon_baru'])) {
            return $this->errorResponse($request, 'Sesi verifikasi tidak valid atau telah kedaluwarsa. Silakan ulangi.', 'kode');
        }

        $teleponBaru = $pending['telepon_baru'];
        $isMatch = $this->otpService->verify('petugas', $teleponBaru, $request->input('kode'), 'ganti_telepon');

        if (! $isMatch) {
            return $this->errorResponse($request, 'Kode verifikasi salah atau telah kedaluwarsa.', 'kode');
        }

        // Tandai nomor baru sudah terverifikasi
        session([
            'ganti_telepon_pending' => [
                'petugas_id' => $currentUser->id,
                'telepon_baru' => $teleponBaru,
                'verified' => true,
            ],
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Verifikasi Berhasil!',
                'telepon_baru' => $teleponBaru,
            ]);
        }

        return back()->with('verified_telepon_baru', $teleponBaru)->with('success', 'Verifikasi Berhasil!');
    }

    /**
     * Simpan Perubahan Profil (Nama dan Nomor Telepon Terverifikasi).
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        /** @var Petugas $currentUser */
        $currentUser = Auth::guard('petugas')->user();

        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
        ]);

        DB::transaction(function () use ($request, $currentUser) {
            /** @var Petugas $petugas */
            $petugas = Petugas::where('id', $currentUser->id)->lockForUpdate()->firstOrFail();
            $petugas->nama = $request->input('nama');

            $inputTelepon = $request->input('telepon');
            if ($inputTelepon) {
                try {
                    $normalizedInput = OtpService::normalizePhoneNumber($inputTelepon);
                } catch (\Throwable $e) {
                    $normalizedInput = $inputTelepon;
                }

                // Jika nomor diubah dari nomor akun yang tersimpan
                if ($normalizedInput !== OtpService::normalizePhoneNumber($petugas->no_telp)) {
                    $pending = session('ganti_telepon_pending');
                    if (
                        ! is_array($pending) ||
                        ($pending['petugas_id'] ?? null) !== $currentUser->id ||
                        ($pending['verified'] ?? false) !== true ||
                        ($pending['telepon_baru'] ?? null) !== $normalizedInput
                    ) {
                        abort(422, 'Perubahan nomor telepon harus diverifikasi melalui kode OTP terlebih dahulu.');
                    }

                    // Pastikan nomor masih belum dipakai akun lain
                    $existsInPetugas = Petugas::where('no_telp', $normalizedInput)->where('id', '!=', $petugas->id)->exists();
                    $existsInNasabah = Nasabah::where('no_telp', $normalizedInput)->exists();
                    if ($existsInPetugas || $existsInNasabah) {
                        abort(422, 'Nomor telepon baru sudah terdaftar dalam sistem.');
                    }

                    $petugas->no_telp = $normalizedInput;
                    session()->forget('ganti_telepon_pending');
                }
            }

            $petugas->save();
        });

        return redirect()->route('pengelola.pengaturan')
            ->with('success', 'Perubahan profil berhasil disimpan!');
    }

    /**
     * Ubah Password untuk Petugas (semua jabatan).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var Petugas $currentUser */
        $currentUser = Auth::guard('petugas')->user();

        $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'same:konfirmasi_password'],
            'konfirmasi_password' => ['required', 'string'],
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 8 karakter.',
            'password_baru.same' => 'Password baru dan konfirmasi password tidak sama.',
            'konfirmasi_password.required' => 'Konfirmasi password wajib diisi.',
        ]);

        if (! Hash::check($request->input('password_lama'), $currentUser->password)) {
            return back()->withErrors(['password_lama' => 'Password lama yang Anda masukkan salah.'])->withInput();
        }

        DB::transaction(function () use ($currentUser, $request) {
            /** @var Petugas $petugas */
            $petugas = Petugas::where('id', $currentUser->id)->lockForUpdate()->firstOrFail();
            $petugas->password = Hash::make($request->input('password_baru'));
            $petugas->save();
        });

        return redirect()->route('pengelola.pengaturan')
            ->with('success', 'Password Anda berhasil diperbarui!');
    }

    /**
     * Formulir Tambah Akun Petugas (Judul: "Tambah Akun Pengelola").
     * Khusus jabatan Pengelola.
     */
    public function tambahAkun(): View
    {
        return view('dashboard.tambah-akun-petugas');
    }

    /**
     * Simpan Petugas Baru (Khusus Pengelola).
     * Jabatan diisi via forceFill.
     */
    public function storeAkun(Request $request): RedirectResponse
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_telp' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'same:konfirmasi_password'],
            'konfirmasi_password' => ['required', 'string'],
            'jabatan' => ['required', 'in:pengelola,bendahara,ketua'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.same' => 'Password dan konfirmasi password tidak sama.',
            'konfirmasi_password.required' => 'Konfirmasi password wajib diisi.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in' => 'Pilihan jabatan tidak valid.',
        ]);

        try {
            $normalizedPhone = OtpService::normalizePhoneNumber($request->input('no_telp'));
        } catch (\Throwable $e) {
            return back()->withErrors(['no_telp' => 'Format nomor telepon tidak valid.'])->withInput();
        }

        $existsInPetugas = Petugas::where('no_telp', $normalizedPhone)->exists();
        $existsInNasabah = Nasabah::where('no_telp', $normalizedPhone)->exists();
        if ($existsInPetugas || $existsInNasabah) {
            return back()->withErrors(['no_telp' => 'Nomor telepon sudah terdaftar dalam sistem.'])->withInput();
        }

        DB::transaction(function () use ($request, $normalizedPhone) {
            $petugas = new Petugas([
                'nama' => $request->input('nama'),
                'no_telp' => $normalizedPhone,
                'password' => Hash::make($request->input('password')),
            ]);

            // Kolom jabatan tidak mass-assignable, harus via forceFill
            $petugas->forceFill([
                'jabatan' => $request->input('jabatan'),
            ])->save();
        });

        return redirect()->route('pengelola.pengaturan')
            ->with('success', 'Anda berhasil menambahkan akun!');
    }

    /**
     * Halaman Tabel Kelola Akun Petugas.
     * Khusus jabatan Pengelola.
     */
    public function kelolaAkun(Request $request): View
    {
        $query = Petugas::query();

        $search = $request->query('search');
        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $petugasList = $query->orderBy('nama')->paginate(10)->withQueryString();

        return view('dashboard.kelola-akun-petugas', compact('petugasList', 'search'));
    }

    /**
     * Halaman Detail Edit Akun Petugas.
     * Khusus jabatan Pengelola.
     */
    public function editAkun(Petugas $petugas): View
    {
        return view('dashboard.edit-akun-petugas', compact('petugas'));
    }

    /**
     * Simpan Perubahan Akun Petugas.
     * Khusus jabatan Pengelola.
     */
    public function updateAkun(Request $request, Petugas $petugas): RedirectResponse
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_telp' => ['required', 'string'],
            'jabatan' => ['required', 'in:pengelola,bendahara,ketua'],
            'password' => ['nullable', 'string', 'min:8', 'same:konfirmasi_password'],
            'konfirmasi_password' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama petugas wajib diisi.',
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in' => 'Jabatan tidak valid.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.same' => 'Password baru dan konfirmasi tidak sama.',
        ]);

        try {
            $normalizedPhone = OtpService::normalizePhoneNumber($request->input('no_telp'));
        } catch (\Throwable $e) {
            return back()->withErrors(['no_telp' => 'Format nomor telepon tidak valid.'])->withInput();
        }

        // Cek keunikan nomor telepon (selain akun ini sendiri)
        $existsInPetugas = Petugas::where('no_telp', $normalizedPhone)->where('id', '!=', $petugas->id)->exists();
        $existsInNasabah = Nasabah::where('no_telp', $normalizedPhone)->exists();
        if ($existsInPetugas || $existsInNasabah) {
            return back()->withErrors(['no_telp' => 'Nomor telepon sudah terdaftar pada akun lain.'])->withInput();
        }

        // TOLAK menurunkan jabatan Pengelola terakhir
        $newJabatan = $request->input('jabatan');
        if ($petugas->jabatan === 'pengelola' && $newJabatan !== 'pengelola') {
            $pengelolaCount = Petugas::where('jabatan', 'pengelola')->count();
            if ($pengelolaCount <= 1) {
                return back()->withErrors(['jabatan' => 'Tidak dapat mengubah jabatan Pengelola terakhir. Sistem membutuhkan setidaknya satu akun Pengelola aktif.'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $petugas, $normalizedPhone, $newJabatan) {
            /** @var Petugas $locked */
            $locked = Petugas::where('id', $petugas->id)->lockForUpdate()->firstOrFail();
            $locked->nama = $request->input('nama');
            $locked->no_telp = $normalizedPhone;

            if ($request->filled('password')) {
                $locked->password = Hash::make($request->input('password'));
            }

            $locked->forceFill([
                'jabatan' => $newJabatan,
            ])->save();
        });

        return redirect()->route('pengelola.petugas.index')
            ->with('success', 'Akun petugas berhasil diperbarui!');
    }

    /**
     * Hapus Akun Petugas (Soft Delete).
     * Khusus jabatan Pengelola.
     */
    public function destroyAkun(Petugas $petugas): RedirectResponse
    {
        // 1. TOLAK menghapus diri sendiri
        if ($petugas->id === Auth::guard('petugas')->id()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus akun Anda sendiri yang sedang aktif.']);
        }

        // 2. TOLAK menghapus Pengelola terakhir
        if ($petugas->jabatan === 'pengelola') {
            $pengelolaCount = Petugas::where('jabatan', 'pengelola')->count();
            if ($pengelolaCount <= 1) {
                return back()->withErrors(['error' => 'Tidak dapat menghapus akun Pengelola terakhir.']);
            }
        }

        DB::transaction(function () use ($petugas) {
            /** @var Petugas $locked */
            $locked = Petugas::where('id', $petugas->id)->lockForUpdate()->firstOrFail();
            $locked->delete();
        });

        return redirect()->route('pengelola.petugas.index')
            ->with('success', "Akun '{$petugas->nama}' berhasil dihapus!");
    }

    /**
     * Helper response error JSON atau Back dengan Error.
     */
    protected function errorResponse(Request $request, string $message, string $field = 'error'): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'field' => $field,
            ], 422);
        }

        return back()->withErrors([$field => $message])->withInput();
    }
}
