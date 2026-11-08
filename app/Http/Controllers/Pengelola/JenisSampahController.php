<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJenisSampahRequest;
use App\Http\Requests\UpdateJenisSampahRequest;
use App\Models\HargaSampah;
use App\Models\JenisSampah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class JenisSampahController extends Controller
{
    /**
     * Tampilkan daftar produk sampah.
     */
    public function index(Request $request): View
    {
        $jenisSampah = JenisSampah::with('hargaTerbaru')->orderBy('nama')->get();
        $modeUbah = $request->query('mode') === 'ubah';

        return view('dashboard.detail-sampah', compact('jenisSampah', 'modeUbah'));
    }

    /**
     * Tampilkan formulir tambah produk sampah.
     */
    public function create(): View
    {
        return view('dashboard.detail-sampah-tambah');
    }

    /**
     * Simpan produk sampah baru beserta 3 harga kondisi per kg.
     */
    public function store(StoreJenisSampahRequest $request): RedirectResponse
    {
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('sampah', 'public');
        }

        DB::transaction(function () use ($request, $gambarPath) {
            $jenisSampah = JenisSampah::create([
                'kode' => $request->validated('kode'),
                'nama' => $request->validated('nama'),
                'deskripsi' => $request->validated('deskripsi'),
                'kategori' => $request->validated('kategori'),
                'gambar' => $gambarPath,
            ]);

            $conditions = [
                'sudah_dipilah' => (int) $request->validated('harga_sudah_dipilah'),
                'belum_dipilah' => (int) $request->validated('harga_belum_dipilah'),
                'campuran' => (int) $request->validated('harga_campuran'),
            ];

            $today = now()->toDateString();

            foreach ($conditions as $kondisi => $harga) {
                HargaSampah::create([
                    'jenis_sampah_id' => $jenisSampah->id,
                    'kondisi' => $kondisi,
                    'harga_per_kg' => $harga,
                    'tanggal_mulai' => $today,
                    'tanggal_berakhir' => null,
                ]);
            }
        });

        return redirect()->route('pengelola.detail-sampah')
            ->with('success', 'Produk Berhasil Ditambahkan');
    }

    /**
     * Tampilkan formulir ubah produk sampah.
     */
    public function edit($jenis_sampah): View
    {
        $id = $jenis_sampah instanceof JenisSampah ? $jenis_sampah->id : $jenis_sampah;
        $jenisSampah = JenisSampah::findOrFail($id);
        $jenisSampah->load('hargaTerbaru');

        return view('dashboard.detail-sampah-edit', compact('jenisSampah'));
    }

    /**
     * Perbarui data produk dan catat riwayat harga jika ada perubahan.
     */
    public function update(UpdateJenisSampahRequest $request, JenisSampah $jenisSampah): RedirectResponse
    {
        DB::transaction(function () use ($request, $jenisSampah) {
            if ($request->hasFile('gambar')) {
                if ($jenisSampah->gambar && Storage::disk('public')->exists($jenisSampah->gambar)) {
                    Storage::disk('public')->delete($jenisSampah->gambar);
                }
                $jenisSampah->gambar = $request->file('gambar')->store('sampah', 'public');
            }

            $jenisSampah->nama = $request->validated('nama');
            $jenisSampah->kode = $request->validated('kode');
            $jenisSampah->deskripsi = $request->validated('deskripsi');
            $jenisSampah->kategori = $request->validated('kategori');
            $jenisSampah->save();

            $newPrices = [
                'sudah_dipilah' => (int) $request->validated('harga_sudah_dipilah'),
                'belum_dipilah' => (int) $request->validated('harga_belum_dipilah'),
                'campuran' => (int) $request->validated('harga_campuran'),
            ];

            $today = now()->toDateString();

            foreach ($newPrices as $kondisi => $hargaBaru) {
                $hargaAktif = HargaSampah::where('jenis_sampah_id', $jenisSampah->id)
                    ->where('kondisi', $kondisi)
                    ->whereNull('tanggal_berakhir')
                    ->latest('tanggal_mulai')
                    ->first();

                if (! $hargaAktif) {
                    HargaSampah::create([
                        'jenis_sampah_id' => $jenisSampah->id,
                        'kondisi' => $kondisi,
                        'harga_per_kg' => $hargaBaru,
                        'tanggal_mulai' => $today,
                        'tanggal_berakhir' => null,
                    ]);
                } elseif ($hargaAktif->harga_per_kg !== $hargaBaru) {
                    // Tutup baris lama dengan mengisi tanggal_berakhir
                    $hargaAktif->update([
                        'tanggal_berakhir' => $today,
                    ]);

                    // Buat baris baru untuk riwayat harga
                    HargaSampah::create([
                        'jenis_sampah_id' => $jenisSampah->id,
                        'kondisi' => $kondisi,
                        'harga_per_kg' => $hargaBaru,
                        'tanggal_mulai' => $today,
                        'tanggal_berakhir' => null,
                    ]);
                }
            }
        });

        return redirect()->route('pengelola.detail-sampah')
            ->with('success', 'Produk Berhasil di ubah!');
    }

    /**
     * Hapus data produk sampah.
     * Ditolak bila jenis sampah sudah memiliki riwayat setoran.
     */
    public function destroy($jenis_sampah): RedirectResponse
    {
        $id = $jenis_sampah instanceof JenisSampah ? $jenis_sampah->id : $jenis_sampah;
        $jenisSampah = JenisSampah::findOrFail($id);

        if ($jenisSampah->setoran()->exists()) {
            return redirect()->route('pengelola.detail-sampah')
                ->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat setoran.');
        }

        if ($jenisSampah->gambar && Storage::disk('public')->exists($jenisSampah->gambar)) {
            Storage::disk('public')->delete($jenisSampah->gambar);
        }

        $jenisSampah->delete();

        return redirect()->route('pengelola.detail-sampah')
            ->with('success', 'Produk berhasil dihapus!');
    }
}
