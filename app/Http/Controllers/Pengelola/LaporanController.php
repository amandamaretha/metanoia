<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Setoran;
use App\Models\TransaksiKeuangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    /**
     * Tampilkan Laporan Setoran Nasabah (Tab 1: Setoran).
     */
    public function index(Request $request): View
    {
        $startDate = $request->query('tanggal_mulai', now()->startOfMonth()->toDateString());
        $endDate = $request->query('tanggal_selesai', now()->endOfMonth()->toDateString());

        // 1. Ringkasan Laporan Setoran
        $setoranQuery = Setoran::whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate);

        $jumlahTransaksi = $setoranQuery->count();
        $totalBerat = (float) $setoranQuery->sum('berat');
        $nilaiSetoran = (int) $setoranQuery->sum('total_nilai');
        // KEPUTUSAN DEFAULT: "bagian nasabah" tampil "-" (tidak dijelaskan di dokumen)
        $bagianNasabah = '-';

        $ringkasan = [
            'jumlah_transaksi' => $jumlahTransaksi,
            'total_berat' => $totalBerat,
            'nilai_setoran' => $nilaiSetoran,
            'bagian_nasabah' => $bagianNasabah,
        ];

        // 2. Agregat per Kategori (Query, tanpa hitung di Blade)
        $kategoriAggregates = DB::table('setoran')
            ->join('jenis_sampah', 'setoran.jenis_sampah_id', '=', 'jenis_sampah.id')
            ->whereDate('setoran.tanggal', '>=', $startDate)
            ->whereDate('setoran.tanggal', '<=', $endDate)
            ->select(
                'jenis_sampah.kategori',
                DB::raw('COUNT(setoran.id) as jumlah_transaksi'),
                DB::raw('SUM(setoran.berat) as total_berat'),
                DB::raw('SUM(setoran.total_nilai) as total_nilai')
            )
            ->groupBy('jenis_sampah.kategori')
            ->orderBy('jenis_sampah.kategori')
            ->get();

        $kategoriLabels = [];
        $kategoriNilai = [];
        $kategoriPersenBerat = [];

        foreach ($kategoriAggregates as $ka) {
            $kategoriLabels[] = $ka->kategori;
            $kategoriNilai[] = (int) $ka->total_nilai;
            $persen = $totalBerat > 0 ? round(($ka->total_berat / $totalBerat) * 100, 2) : 0;
            $kategoriPersenBerat[] = $persen;
        }

        // 3. Tabel Rincian per Jenis Sampah (Query, tanpa hitung di Blade)
        $rincianJenis = DB::table('setoran')
            ->join('jenis_sampah', 'setoran.jenis_sampah_id', '=', 'jenis_sampah.id')
            ->whereDate('setoran.tanggal', '>=', $startDate)
            ->whereDate('setoran.tanggal', '<=', $endDate)
            ->select(
                'jenis_sampah.id',
                'jenis_sampah.kode',
                'jenis_sampah.nama',
                'jenis_sampah.kategori',
                DB::raw('COUNT(setoran.id) as jumlah_transaksi'),
                DB::raw('SUM(setoran.berat) as total_berat'),
                DB::raw('ROUND(AVG(setoran.harga_per_kg)) as harga_per_kg'),
                DB::raw('SUM(setoran.total_nilai) as nilai_setoran')
            )
            ->groupBy('jenis_sampah.id', 'jenis_sampah.kode', 'jenis_sampah.nama', 'jenis_sampah.kategori')
            ->orderBy('jenis_sampah.nama')
            ->get();

        return view('dashboard.laporan', compact(
            'ringkasan',
            'kategoriAggregates',
            'kategoriLabels',
            'kategoriNilai',
            'kategoriPersenBerat',
            'rincianJenis',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Download Data: CSV streaming dengan perlindungan CSV injection.
     * Sel yang diawali karakter =, +, -, atau @ diberi awalan tanda kutip tunggal (').
     */
    public function downloadCsv(Request $request): StreamedResponse
    {
        $startDate = $request->query('tanggal_mulai', now()->startOfMonth()->toDateString());
        $endDate = $request->query('tanggal_selesai', now()->endOfMonth()->toDateString());

        $rincianJenis = DB::table('setoran')
            ->join('jenis_sampah', 'setoran.jenis_sampah_id', '=', 'jenis_sampah.id')
            ->whereDate('setoran.tanggal', '>=', $startDate)
            ->whereDate('setoran.tanggal', '<=', $endDate)
            ->select(
                'jenis_sampah.kode',
                'jenis_sampah.nama',
                'jenis_sampah.kategori',
                DB::raw('COUNT(setoran.id) as jumlah_transaksi'),
                DB::raw('SUM(setoran.berat) as total_berat'),
                DB::raw('ROUND(AVG(setoran.harga_per_kg)) as harga_per_kg'),
                DB::raw('SUM(setoran.total_nilai) as nilai_setoran')
            )
            ->groupBy('jenis_sampah.kode', 'jenis_sampah.nama', 'jenis_sampah.kategori')
            ->orderBy('jenis_sampah.nama')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"laporan-setoran-{$startDate}-sd-{$endDate}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($rincianJenis) {
            $handle = fopen('php://output', 'w');

            // BOM untuk UTF-8 compatibility di Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Baris Judul Header
            fputcsv($handle, [
                $this->sanitizeCsvCell('No'),
                $this->sanitizeCsvCell('Jenis Sampah'),
                $this->sanitizeCsvCell('Kategori'),
                $this->sanitizeCsvCell('Jumlah Transaksi'),
                $this->sanitizeCsvCell('Total Berat (Kg)'),
                $this->sanitizeCsvCell('Harga/Kg (Rp)'),
                $this->sanitizeCsvCell('Nilai Setoran (Rp)'),
                $this->sanitizeCsvCell('Bagian Nasabah'),
                $this->sanitizeCsvCell('Bagian BSSE'),
            ]);

            $no = 1;
            foreach ($rincianJenis as $row) {
                fputcsv($handle, [
                    $this->sanitizeCsvCell($no++),
                    $this->sanitizeCsvCell($row->nama.' ('.$row->kode.')'),
                    $this->sanitizeCsvCell($row->kategori),
                    $this->sanitizeCsvCell($row->jumlah_transaksi),
                    $this->sanitizeCsvCell(number_format((float) $row->total_berat, 2, ',', '.')),
                    $this->sanitizeCsvCell(number_format((float) $row->harga_per_kg, 0, ',', '.')),
                    $this->sanitizeCsvCell(number_format((float) $row->nilai_setoran, 0, ',', '.')),
                    $this->sanitizeCsvCell('-'), // KEPUTUSAN DEFAULT
                    $this->sanitizeCsvCell('-'),
                ]);
            }

            fclose($handle);
        }, "laporan-setoran-{$startDate}-sd-{$endDate}.csv", $headers);
    }

    /**
     * Sanitasi sel CSV untuk perlindungan CSV Injection:
     * Sel yang diawali = + - @ diberi awalan tanda kutip tunggal (').
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
     * Tampilkan Laporan Penjualan ke BSI (Tab 2: Penjualan).
     */
    public function penjualanIndex(Request $request): View
    {
        $startDate = $request->query('tanggal_mulai', now()->startOfMonth()->toDateString());
        $endDate = $request->query('tanggal_selesai', now()->endOfMonth()->toDateString());

        $batasMinimumKg = (float) config('metanoia.penjualan.batas_minimum_kg', 100);

        // KEPUTUSAN DEFAULT: Stok = total setoran dikurangi penjualan, ditampilkan sebagai total
        $totalBeratSetoran = (float) Setoran::sum('berat');
        $totalBeratPenjualan = (float) Penjualan::sum('total_berat');

        $stokSaatIni = max(0.0, round($totalBeratSetoran - $totalBeratPenjualan, 2));

        $progresPersen = $batasMinimumKg > 0 ? min(100.0, round(($stokSaatIni / $batasMinimumKg) * 100, 1)) : 0.0;
        $kurangKg = max(0.0, round($batasMinimumKg - $stokSaatIni, 2));

        // Query Riwayat Penjualan dengan Filter Periode & Pencarian
        $query = Penjualan::whereDate('tanggal_pengangkutan', '>=', $startDate)
            ->whereDate('tanggal_pengangkutan', '<=', $endDate)
            ->latest('tanggal_pengangkutan')
            ->latest('id');

        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor', 'like', "%{$search}%")
                    ->orWhere('tanggal_pengangkutan', 'like', "%{$search}%");
            });
        }

        $riwayatPenjualan = $query->paginate(15)->withQueryString();

        // Rincian stok berdasarkan kategori jenis sampah
        $rincianStokRaw = DB::table('setoran')
            ->join('jenis_sampah', 'setoran.jenis_sampah_id', '=', 'jenis_sampah.id')
            ->select('jenis_sampah.kategori', DB::raw('SUM(setoran.berat) as total_berat'))
            ->groupBy('jenis_sampah.kategori')
            ->pluck('total_berat', 'jenis_sampah.kategori')
            ->toArray();

        $knownCategories = ['Plastik', 'Kertas', 'Logam', 'Kaca'];
        $lainnyaBerat = 0.0;
        foreach ($rincianStokRaw as $kat => $berat) {
            if (! in_array($kat, $knownCategories, true)) {
                $lainnyaBerat += (float) $berat;
            }
        }

        $rincianStok = [
            'Plastik' => (float) ($rincianStokRaw['Plastik'] ?? 0.0),
            'Kertas' => (float) ($rincianStokRaw['Kertas'] ?? 0.0),
            'Logam' => (float) ($rincianStokRaw['Logam'] ?? 0.0),
            'Kaca' => (float) ($rincianStokRaw['Kaca'] ?? 0.0),
            'Lainnya' => $lainnyaBerat,
        ];

        return view('dashboard.penjualan', compact(
            'stokSaatIni',
            'batasMinimumKg',
            'progresPersen',
            'kurangKg',
            'riwayatPenjualan',
            'rincianStok',
            'search',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Catat Penjualan Manual:
     * Simpan penjualan, nomor T00001 dibuat otomatis dari id setelah insert.
     */
    public function storePenjualan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_pengangkutan' => ['required', 'date'],
            'total_berat' => ['required', 'numeric', 'gt:0'],
            'total_nilai' => ['required', 'integer', 'min:0'],
        ], [
            'tanggal_pengangkutan.required' => 'Tanggal pengangkutan wajib diisi.',
            'total_berat.required' => 'Total berat sampah wajib diisi.',
            'total_berat.gt' => 'Total berat harus lebih besar dari 0 kg.',
            'total_nilai.required' => 'Total nilai penjualan wajib diisi.',
            'total_nilai.min' => 'Total nilai tidak boleh bernilai negatif.',
        ]);

        $penjualan = DB::transaction(function () use ($validated) {
            $penjualan = Penjualan::create([
                'tanggal_pengangkutan' => $validated['tanggal_pengangkutan'],
                'total_berat' => $validated['total_berat'],
                'total_nilai' => $validated['total_nilai'],
            ]);

            TransaksiKeuangan::create([
                'petugas_id' => Auth::guard('petugas')->id(),
                'tanggal' => $validated['tanggal_pengangkutan'],
                'jenis' => 'pemasukan',
                'nominal' => $validated['total_nilai'],
                'keterangan' => 'Pemasukan dari penjualan sampah nomor ' . $penjualan->nomor,
            ]);

            return $penjualan;
        });

        return redirect()->route('pengelola.laporan.penjualan')
            ->with('success', "Penjualan berhasil dicatat dengan nomor {$penjualan->nomor}.");
    }

    /**
     * Detail Penjualan: Update data penjualan (tanggal, berat, total nilai).
     * Nomor transaksi tidak bisa diubah.
     */
    public function updatePenjualan(Request $request, Penjualan $penjualan): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_pengangkutan' => ['required', 'date'],
            'total_berat' => ['required', 'numeric', 'gt:0'],
            'total_nilai' => ['required', 'integer', 'min:0'],
        ], [
            'tanggal_pengangkutan.required' => 'Tanggal pengangkutan wajib diisi.',
            'total_berat.required' => 'Total berat sampah wajib diisi.',
            'total_berat.gt' => 'Total berat harus lebih besar dari 0 kg.',
            'total_nilai.required' => 'Total nilai penjualan wajib diisi.',
            'total_nilai.min' => 'Total nilai tidak boleh bernilai negatif.',
        ]);

        // Perbarui hanya tanggal, berat, dan total nilai (nomor transaksi tidak bisa diubah)
        $penjualan->update([
            'tanggal_pengangkutan' => $validated['tanggal_pengangkutan'],
            'total_berat' => $validated['total_berat'],
            'total_nilai' => $validated['total_nilai'],
        ]);

        return redirect()->route('pengelola.laporan.penjualan')
            ->with('success', "Data penjualan {$penjualan->nomor} berhasil diperbarui.");
    }

    /**
     * Hapus Penjualan:
     * Soft delete dan catat siapa yang menghapus.
     */
    public function destroyPenjualan(Penjualan $penjualan): RedirectResponse
    {
        $nomor = $penjualan->nomor;

        $penjualan->dihapus_oleh = Auth::guard('petugas')->id();
        $penjualan->save();
        $penjualan->delete();

        return redirect()->route('pengelola.laporan.penjualan')
            ->with('success', "Transaksi penjualan {$nomor} berhasil dihapus.");
    }
}
