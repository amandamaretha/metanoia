<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\Setoran;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tampilkan ringkasan statistik, chart, dan transaksi terbaru pada dashboard pengelola.
     */
    public function index(): View
    {
        $totalNasabah = Nasabah::count();

        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $setoranPekanIni = Setoran::whereBetween('tanggal', [$startOfWeek, $endOfWeek]);
        $totalSetoranPekanIni = (clone $setoranPekanIni)->count();
        $totalBeratSampah = (float) ((clone $setoranPekanIni)->sum('berat') ?? 0);
        $totalNilaiSetoran = (int) ((clone $setoranPekanIni)->sum('total_nilai') ?? 0);

        // Agregasi Kategori Sampah
        $kategoriAggregates = DB::table('setoran')
            ->join('jenis_sampah', 'setoran.jenis_sampah_id', '=', 'jenis_sampah.id')
            ->whereBetween('setoran.tanggal', [$startOfWeek, $endOfWeek])
            ->select(
                'jenis_sampah.kategori',
                DB::raw('SUM(setoran.berat) as total_berat'),
                DB::raw('SUM(setoran.total_nilai) as total_nilai')
            )
            ->groupBy('jenis_sampah.kategori')
            ->orderBy('jenis_sampah.kategori')
            ->get();

        if ($kategoriAggregates->isEmpty()) {
            $kategoriAggregates = DB::table('setoran')
                ->join('jenis_sampah', 'setoran.jenis_sampah_id', '=', 'jenis_sampah.id')
                ->select(
                    'jenis_sampah.kategori',
                    DB::raw('SUM(setoran.berat) as total_berat'),
                    DB::raw('SUM(setoran.total_nilai) as total_nilai')
                )
                ->groupBy('jenis_sampah.kategori')
                ->orderBy('jenis_sampah.kategori')
                ->get();
        }

        $kategoriLabels = $kategoriAggregates->pluck('kategori')->toArray();
        $kategoriNilai = $kategoriAggregates->pluck('total_berat')->map(fn ($val) => (float) $val)->toArray();

        if (empty($kategoriLabels)) {
            $kategoriLabels = ['Plastik', 'Kertas', 'Logam', 'Kaca', 'Lainnya'];
            $kategoriNilai = [0, 0, 0, 0, 0];
        }

        // Transaksi Terbaru
        $transaksiTerbaru = Setoran::with(['nasabah', 'jenisSampah'])
            ->latest('tanggal')
            ->latest('id')
            ->take(5)
            ->get();

        return view('dashboard.dashboard-pengelola', compact(
            'totalNasabah',
            'totalSetoranPekanIni',
            'totalBeratSampah',
            'totalNilaiSetoran',
            'kategoriLabels',
            'kategoriNilai',
            'transaksiTerbaru'
        ));
    }
}

