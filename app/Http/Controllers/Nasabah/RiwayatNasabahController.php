<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\Nota;
use App\Models\Setoran;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiwayatNasabahController extends Controller
{
    /**
     * Tampilkan riwayat setoran sampah nasabah yang sedang login.
     */
    public function index(): View
    {
        $nasabah = Auth::guard('nasabah')->user();

        $setoranList = Setoran::with(['jenisSampah', 'petugas', 'nota'])
            ->where('nasabah_id', $nasabah->id)
            ->latest('tanggal')
            ->get();

        return view('nasabah.riwayat', compact('setoranList', 'nasabah'));
    }

    /**
     * Tampilkan nota setoran milik nasabah yang sedang login.
     * Cek kepemilikan di server: jika bukan pemiliknya, kembalikan 403 Forbidden.
     */
    public function showNota(Nota $nota): View
    {
        $nasabah = Auth::guard('nasabah')->user();

        // Validasi kepemilikan nota melalui relasi setoran
        if ($nota->setoran->nasabah_id !== $nasabah->id) {
            abort(403, 'Akses ditolak: Anda tidak memiliki akses ke nota ini.');
        }

        $nota->load(['setoran.jenisSampah', 'setoran.petugas', 'setoran.nasabah']);

        return view('nasabah.nota', compact('nota', 'nasabah'));
    }
}
