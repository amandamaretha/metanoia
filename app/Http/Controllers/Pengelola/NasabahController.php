<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNasabahRequest;
use App\Models\Nasabah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NasabahController extends Controller
{
    /**
     * Tampilkan daftar nasabah dengan fitur pencarian nama (escape wildcard LIKE).
     */
    public function index(Request $request): View
    {
        $cari = $request->query('cari');
        $query = Nasabah::query();

        if ($cari !== null && trim($cari) !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($cari));
            $query->whereRaw("nama LIKE ? ESCAPE '\\'", ["%{$escaped}%"]);
        }

        $nasabahList = $query->orderBy('nama')->paginate(15)->withQueryString();

        return view('dashboard.nasabah', compact('nasabahList', 'cari'));
    }

    /**
     * Tampilkan formulir edit nasabah.
     */
    public function edit(Nasabah $nasabah): View
    {
        return view('dashboard.nasabah-edit', compact('nasabah'));
    }

    /**
     * Perbarui data nasabah (nama, alamat, no_telp, status).
     * Saldo TIDAK bisa diubah lewat formulir ini.
     * Password TIDAK ditampilkan dan tidak diubah disini.
     */
    public function update(UpdateNasabahRequest $request, Nasabah $nasabah): RedirectResponse
    {
        $nasabah->nama = $request->validated('nama');
        $nasabah->alamat = $request->validated('alamat');
        $nasabah->no_telp = $request->validated('no_telp');
        // 'status' tidak ada di $fillable demi integritas, sehingga diisi eksplisit
        $nasabah->status = $request->validated('status');
        $nasabah->save();

        return redirect()->route('pengelola.nasabah')
            ->with('success', 'Data nasabah berhasil diperbarui!');
    }

    /**
     * Hapus data nasabah.
     * DITOLAK bila nasabah memiliki riwayat setoran atau mutasi saldo.
     */
    public function destroy(Nasabah $nasabah): RedirectResponse
    {
        if ($nasabah->setoran()->exists() || $nasabah->mutasiSaldo()->exists()) {
            return redirect()->route('pengelola.nasabah')
                ->with('error', 'Nasabah tidak dapat dihapus karena memiliki riwayat setoran atau transaksi mutasi saldo.');
        }

        $nasabah->delete();

        return redirect()->route('pengelola.nasabah')
            ->with('success', 'Nasabah berhasil dihapus!');
    }
}
