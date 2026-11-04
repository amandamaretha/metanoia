<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\HargaSampah;
use App\Models\JenisSampah;
use App\Models\Nasabah;
use App\Services\QrSessionService;
use App\Services\SetoranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class PenimbanganController extends Controller
{
    public function __construct(
        protected SetoranService $setoranService,
        protected QrSessionService $qrSessionService
    ) {}

    /**
     * Tampilkan halaman utama penimbangan (Cari Nasabah, Pilih Jenis, Timbang).
     */
    public function index(): View
    {
        $today = now()->toDateString();

        $nasabahList = Nasabah::orderBy('nama')->get(['id', 'nama', 'nik', 'alamat', 'no_telp', 'saldo', 'status']);

        $jenisSampahList = JenisSampah::with(['hargaSampah' => function ($q) use ($today) {
            $q->where('tanggal_mulai', '<=', $today)
                ->where(function ($sub) use ($today) {
                    $sub->whereNull('tanggal_berakhir')
                        ->orWhere('tanggal_berakhir', '>=', $today);
                })
                ->orderByDesc('tanggal_mulai')
                ->orderByDesc('id');
        }])->get();

        // Token idempotensi sekali pakai untuk form penimbangan
        $idempotencyToken = (string) Str::uuid();
        Cache::put("idempotency:setoran:{$idempotencyToken}", true, now()->addHours(2));

        return view('dashboard.penimbangan', compact('nasabahList', 'jenisSampahList', 'idempotencyToken'));
    }

    /**
     * Periksa status akun nasabah saat dipilih.
     */
    public function checkNasabah(Nasabah $nasabah): JsonResponse
    {
        if ($nasabah->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun nasabah tidak aktif. Transaksi penimbangan tidak dapat dilanjutkan.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'nasabah' => [
                'id' => $nasabah->id,
                'nama' => $nasabah->nama,
                'nik' => $nasabah->masked_nik,
                'no_telp' => $nasabah->no_telp,
                'alamat' => $nasabah->alamat,
                'saldo' => $nasabah->saldo,
                'status' => $nasabah->status,
            ],
        ]);
    }

    /**
     * Dapatkan harga per kg aktif untuk jenis dan kondisi sampah pada hari ini.
     */
    public function getHarga(Request $request): JsonResponse
    {
        $request->validate([
            'jenis_sampah_id' => ['required', 'integer', 'exists:jenis_sampah,id'],
            'kondisi' => ['required', 'string', 'in:sudah_dipilah,belum_dipilah,campuran'],
        ]);

        $today = now()->toDateString();
        $harga = HargaSampah::where('jenis_sampah_id', $request->input('jenis_sampah_id'))
            ->where('kondisi', $request->input('kondisi'))
            ->where('tanggal_mulai', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_berakhir')
                    ->orWhere('tanggal_berakhir', '>=', $today);
            })
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();

        if (! $harga) {
            return response()->json([
                'success' => false,
                'message' => 'Harga aktif tidak ditemukan untuk jenis dan kondisi sampah ini.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'harga_per_kg' => $harga->harga_per_kg,
        ]);
    }

    /**
     * Simpan setoran penimbangan sampah dan terbitkan nota.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nasabah_id' => ['required', 'integer', 'exists:nasabah,id'],
            'jenis_sampah_id' => ['required', 'integer', 'exists:jenis_sampah,id'],
            'kondisi' => ['required', 'string', 'in:sudah_dipilah,belum_dipilah,campuran'],
            'berat' => ['required', 'numeric', 'gt:0'],
        ], [
            'jenis_sampah_id.exists' => 'Jenis sampah yang dipilih tidak valid.',
            'kondisi.in' => 'Kondisi sampah tidak valid.',
            'berat.gt' => 'Berat sampah harus lebih besar dari 0 kilogram.',
        ]);

        // Cek dan konsumsi token idempotensi atomik
        $idempotencyToken = $request->input('idempotency_token');
        if (! $idempotencyToken || ! Cache::pull("idempotency:setoran:{$idempotencyToken}")) {
            return response()->json([
                'success' => false,
                'message' => 'Setoran ini sudah diproses.',
            ], 422);
        }

        $petugasId = (int) Auth::guard('petugas')->id();

        try {
            $result = $this->setoranService->processSetoran(
                (int) $validated['nasabah_id'],
                $petugasId,
                (int) $validated['jenis_sampah_id'],
                $validated['kondisi'],
                $validated['berat']
            );

            // Buat token baru untuk transaksi berikutnya jika ada
            $nextToken = (string) Str::uuid();
            Cache::put("idempotency:setoran:{$nextToken}", true, now()->addHours(2));

            return response()->json([
                'success' => true,
                'message' => 'Setoran Berhasil',
                'data' => [
                    'setoran_id' => $result['setoran']->id,
                    'nomor_nota' => $result['nota']->nomor,
                    'total_nilai' => $result['setoran']->total_nilai,
                    'saldo_terbaru' => $result['nasabah']->saldo,
                    'next_idempotency_token' => $nextToken,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            // Pulihkan token jika gagal validasi domain logic agar dapat dicoba lagi
            Cache::put("idempotency:setoran:{$idempotencyToken}", true, now()->addHours(2));

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Terbitkan token QR baru berbatas waktu 120 detik.
     */
    public function createQr(): JsonResponse
    {
        $petugasId = (int) Auth::guard('petugas')->id();
        $session = $this->qrSessionService->createSession($petugasId);

        return response()->json($session);
    }

    /**
     * Perbarui token QR: terbitkan yang baru dan matikan yang lama.
     */
    public function refreshQr(): JsonResponse
    {
        $petugasId = (int) Auth::guard('petugas')->id();
        $session = $this->qrSessionService->refreshSession($petugasId);

        return response()->json($session);
    }

    /**
     * Cek status pemindaian token QR oleh nasabah.
     */
    public function checkQrStatus(Request $request): JsonResponse
    {
        $token = $request->query('token');
        if (! $token) {
            return response()->json(['status' => 'invalid'], 400);
        }

        $petugasId = (int) Auth::guard('petugas')->id();
        $result = $this->qrSessionService->checkSession($token, $petugasId);

        return response()->json($result);
    }

    /**
     * Batalkan sesi QR oleh petugas.
     */
    public function cancelQr(Request $request): JsonResponse
    {
        $petugasId = (int) Auth::guard('petugas')->id();
        $token = $request->input('token');
        $this->qrSessionService->cancelSession($petugasId, $token);

        return response()->json(['success' => true]);
    }
}
