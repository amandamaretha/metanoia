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
    public function edit(JenisSampah $jenisSampah): View
    {
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
                    ->orderByDesc('tanggal_mulai')
                    ->orderByDesc('id')
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
                    $isCreatedToday = $hargaAktif->tanggal_mulai && \Carbon\Carbon::parse($hargaAktif->tanggal_mulai)->toDateString() === $today;
                    if ($isCreatedToday) {
                        // Jika sudah ada harga yang mulai hari ini, perbarui nilainya langsung agar tidak menyisakan dua harga aktif di hari yang sama
                        $hargaAktif->update([
                            'harga_per_kg' => $hargaBaru,
                        ]);
                    } else {
                        // Tutup baris lama dengan tanggal berakhir kemarin agar hari ini hanya harga baru yang aktif
                        $hargaAktif->update([
                            'tanggal_berakhir' => now()->subDay()->toDateString(),
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
            }
        });

        return redirect()->route('pengelola.detail-sampah')
            ->with('success', 'Produk Berhasil di ubah!');
    }
}
