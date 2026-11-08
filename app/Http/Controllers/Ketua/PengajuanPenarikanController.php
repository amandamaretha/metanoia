<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use App\Services\PenarikanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

class PengajuanPenarikanController extends Controller
{
    public function __construct(
        protected PenarikanService $penarikanService
    ) {}

    /**
     * Tampilkan daftar pengajuan penarikan beserta ringkasan, pencarian, dan filter status.
     */
    public function index(Request $request): View
    {
        $ringkasan = [
            'total' => Penarikan::count(),
            'menunggu' => Penarikan::where('status', 'menunggu')->count(),
            'disetujui' => Penarikan::where('status', 'disetujui')->count(),
            'ditolak' => Penarikan::where('status', 'ditolak')->count(),
        ];

        $query = Penarikan::with('nasabah')->latest('tanggal_pengajuan');

        // Filter status
        $status = $request->query('status');
        if ($status && in_array($status, ['menunggu', 'disetujui', 'ditolak'], true)) {
            $query->where('status', $status);
        }

        // Pencarian nama atau NIK nasabah
        $search = $request->query('search');
        if ($search) {
            $query->whereHas('nasabah', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $pengajuanList = $query->paginate(15)->withQueryString();

        return view('ketua.pengajuan-penarikan', compact('pengajuanList', 'ringkasan', 'status', 'search'));
    }

    /**
     * Tampilkan detail satu pengajuan penarikan (NIK tampil sebagian).
     */
    public function show(Penarikan $penarikan): View
    {
        $penarikan->load(['nasabah', 'petugas']);

        return view('ketua.detail-penarikan', compact('penarikan'));
    }

    /**
     * Setujui pengajuan penarikan oleh Ketua atau Bendahara.
     */
    public function setujui(Request $request, Penarikan $penarikan): RedirectResponse
    {
        $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $petugasId = (int) Auth::guard('petugas')->id();
        $catatan = $request->input('catatan');

        try {
            $result = $this->penarikanService->setujui($penarikan, $petugasId, $catatan);

            if (! $result['success']) {
                // Saldo tidak mencukupi saat disetujui: tolak persetujuan, status tetap "menunggu"
                return redirect()->route('ketua.pengajuan-penarikan.show', $penarikan)
                    ->with('error', $result['message']);
            }

            return redirect()->route('ketua.pengajuan-penarikan.show', $penarikan)
                ->with('success', 'Pengajuan Berhasil Disetujui!');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('ketua.pengajuan-penarikan.show', $penarikan)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Tolak pengajuan penarikan oleh Ketua atau Bendahara.
     */
    public function tolak(Request $request, Penarikan $penarikan): RedirectResponse
    {
        $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $petugasId = (int) Auth::guard('petugas')->id();
        $catatan = $request->input('catatan');

        try {
            $result = $this->penarikanService->tolak($penarikan, $petugasId, $catatan);

            return redirect()->route('ketua.pengajuan-penarikan.show', $penarikan)
                ->with('warning', 'Mohon Maaf Pengajuan ditolak!');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('ketua.pengajuan-penarikan.show', $penarikan)
                ->with('error', $e->getMessage());
        }
    }
}
