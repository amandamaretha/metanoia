<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\TransaksiKeuangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiKeuanganController extends Controller
{
    /**
     * Hitung saldo kas dinamis dari transaksi (total pemasukan dikurangi total pengeluaran).
     */
    public function hitungSaldoKas(): int
    {
        $totalPemasukan = (int) TransaksiKeuangan::where('jenis', 'pemasukan')->sum('nominal');
        $totalPengeluaran = (int) TransaksiKeuangan::where('jenis', 'pengeluaran')->sum('nominal');

        return $totalPemasukan - $totalPengeluaran;
    }

    /**
     * Formulir Tambah Transaksi Keuangan (Menu: Transaksi).
     */
    public function indexTambah(): View
    {
        $saldoKas = $this->hitungSaldoKas();

        return view('bendahara.transaksi', compact('saldoKas'));
    }

    /**
     * Simpan Transaksi Keuangan Baru.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->has('nominal') && is_string($request->nominal)) {
            $request->merge([
                'nominal' => str_replace(['.', ','], '', $request->nominal),
            ]);
        }

        $validated = $request->validate([
            'nominal' => ['required', 'integer', 'gt:0'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['required', 'string'],
            'jenis' => ['required', 'in:pemasukan,pengeluaran'],
            'lampiran' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:5120', // Maks 5 MB
            ],
        ], [
            'nominal.required' => 'Nominal transaksi wajib diisi.',
            'nominal.integer' => 'Nominal transaksi harus berupa angka.',
            'nominal.gt' => 'Nominal transaksi harus lebih besar dari 0.',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.date' => 'Format tanggal transaksi tidak valid.',
            'keterangan.required' => 'Keterangan transaksi wajib diisi.',
            'jenis.required' => 'Jenis transaksi wajib dipilih.',
            'jenis.in' => 'Pilihan jenis transaksi tidak valid.',
            'lampiran.file' => 'Lampiran harus berupa file yang valid.',
            'lampiran.mimes' => 'Lampiran bukti harus bertipe JPG, PNG, atau PDF.',
            'lampiran.mimetypes' => 'Tipe konten lampiran harus berupa JPG, PNG, atau PDF.',
            'lampiran.max' => 'Ukuran file lampiran maksimal 5 MB.',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('lampiran_transaksi', 'local');
        }

        DB::transaction(function () use ($validated, $lampiranPath) {
            TransaksiKeuangan::create([
                'petugas_id' => Auth::guard('petugas')->id(),
                'tanggal' => $validated['tanggal'],
                'jenis' => $validated['jenis'],
                'nominal' => $validated['nominal'],
                'keterangan' => $validated['keterangan'],
                'lampiran' => $lampiranPath,
            ]);
        });

        return redirect()->route('bendahara.transaksi')
            ->with('success', 'Transaksi berhasil disimpan');
    }

    /**
     * Tabel Riwayat Transaksi Keuangan (Menu: Riwayat).
     */
    public function riwayat(Request $request): View
    {
        $saldoKas = $this->hitungSaldoKas();

        $query = TransaksiKeuangan::latest('tanggal')->latest('id');

        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%")
                    ->orWhere('tanggal', 'like', "%{$search}%")
                    ->orWhere('nominal', 'like', "%{$search}%");
            });
        }

        $riwayatTransaksi = $query->paginate(15)->withQueryString();

        return view('bendahara.riwayat', compact('saldoKas', 'riwayatTransaksi', 'search'));
    }

    /**
     * Tampilkan Halaman Edit Transaksi Keuangan.
     */
    public function edit(TransaksiKeuangan $transaksi): View
    {
        return view('bendahara.edit-transaksi', compact('transaksi'));
    }

    /**
     * Simpan Perubahan Transaksi Keuangan.
     */
    public function update(Request $request, TransaksiKeuangan $transaksi): RedirectResponse
    {
        if ($request->has('nominal') && is_string($request->nominal)) {
            $request->merge([
                'nominal' => str_replace(['.', ','], '', $request->nominal),
            ]);
        }

        $validated = $request->validate([
            'nominal' => ['required', 'integer', 'gt:0'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['required', 'string'],
            'jenis' => ['required', 'in:pemasukan,pengeluaran'],
            'lampiran' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:5120',
            ],
        ], [
            'nominal.required' => 'Nominal transaksi wajib diisi.',
            'nominal.integer' => 'Nominal transaksi harus berupa angka.',
            'nominal.gt' => 'Nominal transaksi harus lebih besar dari 0.',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.date' => 'Format tanggal transaksi tidak valid.',
            'keterangan.required' => 'Keterangan transaksi wajib diisi.',
            'jenis.required' => 'Jenis transaksi wajib dipilih.',
            'jenis.in' => 'Pilihan jenis transaksi tidak valid.',
            'lampiran.file' => 'Lampiran harus berupa file yang valid.',
            'lampiran.mimes' => 'Lampiran bukti harus bertipe JPG, PNG, atau PDF.',
            'lampiran.mimetypes' => 'Tipe konten lampiran harus berupa JPG, PNG, atau PDF.',
            'lampiran.max' => 'Ukuran file lampiran maksimal 5 MB.',
        ]);

        DB::transaction(function () use ($request, $validated, $transaksi) {
            /** @var TransaksiKeuangan $locked */
            $locked = TransaksiKeuangan::where('id', $transaksi->id)->lockForUpdate()->firstOrFail();

            if ($request->hasFile('lampiran')) {
                // Hapus berkas lampiran lama dari disk privat jika ada
                if ($locked->lampiran && Storage::disk('local')->exists($locked->lampiran)) {
                    Storage::disk('local')->delete($locked->lampiran);
                }
                $locked->lampiran = $request->file('lampiran')->store('lampiran_transaksi', 'local');
            }

            $locked->tanggal = $validated['tanggal'];
            $locked->jenis = $validated['jenis'];
            $locked->nominal = $validated['nominal'];
            $locked->keterangan = $validated['keterangan'];
            $locked->save();
        });

        return redirect()->route('bendahara.riwayat')
            ->with('success', 'Transaksi berhasil disimpan');
    }

    /**
     * Hapus Transaksi Keuangan (Soft Delete + Catat Petugas Penghapus).
     */
    public function destroy(TransaksiKeuangan $transaksi): RedirectResponse
    {
        DB::transaction(function () use ($transaksi) {
            /** @var TransaksiKeuangan $locked */
            $locked = TransaksiKeuangan::where('id', $transaksi->id)->lockForUpdate()->firstOrFail();
            $locked->dihapus_oleh = Auth::guard('petugas')->id();
            $locked->save();
            $locked->delete();
        });

        return redirect()->route('bendahara.riwayat')
            ->with('success', 'Transaksi berhasil dihapus');
    }

    /**
     * Sajikan Berkas Lampiran dari Disk Privat (Hanya untuk Jabatan Bendahara).
     */
    public function lampiran(TransaksiKeuangan $transaksi): BinaryFileResponse
    {
        if (! $transaksi->lampiran || ! Storage::disk('local')->exists($transaksi->lampiran)) {
            abort(404, 'Berkas lampiran tidak ditemukan.');
        }

        $filePath = Storage::disk('local')->path($transaksi->lampiran);
        $mimeType = Storage::disk('local')->mimeType($transaksi->lampiran) ?: 'application/octet-stream';

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($filePath).'"',
        ]);
    }

    /**
     * Download Data Riwayat Transaksi (CSV Streaming dengan Proteksi CSV Injection).
     */
    public function downloadCsv(Request $request): StreamedResponse
    {
        $query = TransaksiKeuangan::with('petugas')->latest('tanggal')->latest('id');

        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%")
                    ->orWhere('tanggal', 'like', "%{$search}%")
                    ->orWhere('nominal', 'like', "%{$search}%");
            });
        }

        $transaksiList = $query->get();

        $today = now()->toDateString();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"riwayat-transaksi-keuangan-{$today}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($transaksiList) {
            $handle = fopen('php://output', 'w');

            // BOM untuk UTF-8 compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Baris Header CSV
            fputcsv($handle, [
                $this->sanitizeCsvCell('No'),
                $this->sanitizeCsvCell('Tanggal'),
                $this->sanitizeCsvCell('Jenis Transaksi'),
                $this->sanitizeCsvCell('Keterangan'),
                $this->sanitizeCsvCell('Nominal (Rp)'),
                $this->sanitizeCsvCell('Lampiran'),
                $this->sanitizeCsvCell('Dicatat Oleh'),
            ]);

            $no = 1;
            foreach ($transaksiList as $t) {
                fputcsv($handle, [
                    $this->sanitizeCsvCell($no++),
                    $this->sanitizeCsvCell($t->tanggal->format('d/m/Y')),
                    $this->sanitizeCsvCell(ucfirst($t->jenis)),
                    $this->sanitizeCsvCell($t->keterangan),
                    $this->sanitizeCsvCell(number_format($t->nominal, 0, ',', '.')),
                    $this->sanitizeCsvCell($t->lampiran ? 'Ada' : 'Tidak Ada'),
                    $this->sanitizeCsvCell($t->petugas->nama ?? '-'),
                ]);
            }

            fclose($handle);
        }, "riwayat-transaksi-keuangan-{$today}.csv", $headers);
    }

    /**
     * Perlindungan CSV Injection: Sel diawali =, +, -, @ diberi kutip tunggal (').
     */
    public function sanitizeCsvCell(mixed $value): string
    {
        $str = (string) $value;

        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@'], true)) {
            return "'".$str;
        }

        return $str;
    }

    /**
     * Halaman Laporan Keuangan (Menu: Laporan).
     */
    public function laporan(Request $request): View
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $startDate = $validated['tanggal_mulai'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['tanggal_selesai'] ?? now()->endOfMonth()->toDateString();

        // 1. Saldo Awal: Saldo sebelum tanggal awal
        $pemasukanSebelum = (int) TransaksiKeuangan::where('jenis', 'pemasukan')
            ->whereDate('tanggal', '<', $startDate)
            ->sum('nominal');

        $pengeluaranSebelum = (int) TransaksiKeuangan::where('jenis', 'pengeluaran')
            ->whereDate('tanggal', '<', $startDate)
            ->sum('nominal');

        $saldoAwal = $pemasukanSebelum - $pengeluaranSebelum;

        // 2. Rincian Pemasukan dalam Periode
        $rincianPemasukan = TransaksiKeuangan::where('jenis', 'pemasukan')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $totalPemasukan = (int) $rincianPemasukan->sum('nominal');

        // 3. Rincian Pengeluaran dalam Periode
        $rincianPengeluaran = TransaksiKeuangan::where('jenis', 'pengeluaran')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $totalPengeluaran = (int) $rincianPengeluaran->sum('nominal');

        // 4. Saldo Akhir
        $saldoAkhir = $saldoAwal + $totalPemasukan - $totalPengeluaran;

        return view('bendahara.laporan-keuangan', compact(
            'startDate',
            'endDate',
            'saldoAwal',
            'rincianPemasukan',
            'totalPemasukan',
            'rincianPengeluaran',
            'totalPengeluaran',
            'saldoAkhir'
        ));
    }

    /**
     * Halaman Siap Cetak (Print View) untuk "Download PDF".
     */
    public function cetakLaporan(Request $request): View
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $startDate = $validated['tanggal_mulai'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['tanggal_selesai'] ?? now()->endOfMonth()->toDateString();

        $pemasukanSebelum = (int) TransaksiKeuangan::where('jenis', 'pemasukan')
            ->whereDate('tanggal', '<', $startDate)
            ->sum('nominal');

        $pengeluaranSebelum = (int) TransaksiKeuangan::where('jenis', 'pengeluaran')
            ->whereDate('tanggal', '<', $startDate)
            ->sum('nominal');

        $saldoAwal = $pemasukanSebelum - $pengeluaranSebelum;

        $rincianPemasukan = TransaksiKeuangan::where('jenis', 'pemasukan')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $totalPemasukan = (int) $rincianPemasukan->sum('nominal');

        $rincianPengeluaran = TransaksiKeuangan::where('jenis', 'pengeluaran')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $totalPengeluaran = (int) $rincianPengeluaran->sum('nominal');

        $saldoAkhir = $saldoAwal + $totalPemasukan - $totalPengeluaran;

        return view('bendahara.laporan-keuangan-cetak', compact(
            'startDate',
            'endDate',
            'saldoAwal',
            'rincianPemasukan',
            'totalPemasukan',
            'rincianPengeluaran',
            'totalPengeluaran',
            'saldoAkhir'
        ));
    }
}
