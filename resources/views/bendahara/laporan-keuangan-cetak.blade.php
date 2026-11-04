<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Keuangan - Metanoia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-100 p-8 font-sans">
    <div class="max-w-4xl mx-auto bg-white p-10 rounded-xl shadow print:shadow-none print:p-0">
        <!-- TOMBOL CETAK (TIDAK TAMPIL SAAT PRINT) -->
        <div class="no-print mb-6 flex justify-between items-center pb-4 border-b">
            <a href="{{ route('bendahara.laporan-keuangan', ['tanggal_mulai' => $startDate, 'tanggal_selesai' => $endDate]) }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300">
                &larr; Kembali
            </a>
            <div class="flex items-center gap-3">
                <span class="text-xs text-amber-600 bg-amber-50 px-3 py-1 rounded border border-amber-200">
                    Mode Siap Cetak (Simpan PDF via Browser: Print &rarr; Save as PDF)
                </span>
                <button onclick="window.print()" class="px-5 py-2 bg-[#5C8A8A] text-white rounded-lg text-sm font-bold hover:bg-[#4a7272]">
                    Cetak / Simpan PDF
                </button>
            </div>
        </div>

        <!-- KOP LAPORAN -->
        <div class="text-center border-b-2 border-black pb-4 mb-6">
            <h1 class="text-2xl font-bold uppercase tracking-wider">Bank Sampah Metanoia</h1>
            <h2 class="text-lg font-semibold text-gray-800">Laporan Arus Kas Keuangan</h2>
            <p class="text-sm text-gray-600 mt-1">
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </p>
        </div>

        <!-- RINGKASAN SALDO -->
        <div class="mb-8">
            <table class="w-full text-sm border border-gray-300">
                <tr class="bg-gray-50 border-b border-gray-300">
                    <td class="py-2.5 px-4 font-semibold text-gray-700 w-1/2">Saldo Awal (Sebelum {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }})</td>
                    <td class="py-2.5 px-4 text-right font-bold text-gray-900">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-300">
                    <td class="py-2.5 px-4 font-semibold text-green-700">Total Pemasukan Periode Ini</td>
                    <td class="py-2.5 px-4 text-right font-bold text-green-700">+ Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-300">
                    <td class="py-2.5 px-4 font-semibold text-red-700">Total Pengeluaran Periode Ini</td>
                    <td class="py-2.5 px-4 text-right font-bold text-red-700">- Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="py-3 px-4 text-base text-gray-800">Saldo Akhir (Per {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})</td>
                    <td class="py-3 px-4 text-right text-base text-[#5C8A8A]">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- RINCIAN PEMASUKAN -->
        <div class="mb-8">
            <h3 class="text-base font-bold text-gray-800 mb-3 border-b pb-1">1. Rincian Pemasukan</h3>
            <table class="w-full text-xs border border-gray-300">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-300 font-bold">
                        <th class="py-2 px-3 text-center w-10">No</th>
                        <th class="py-2 px-3 text-left">Tanggal</th>
                        <th class="py-2 px-3 text-left">Keterangan</th>
                        <th class="py-2 px-3 text-right">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($rincianPemasukan as $idx => $p)
                    <tr>
                        <td class="py-2 px-3 text-center">{{ $idx + 1 }}</td>
                        <td class="py-2 px-3">{{ $p->tanggal->format('d/m/Y') }}</td>
                        <td class="py-2 px-3">{{ $p->keterangan }}</td>
                        <td class="py-2 px-3 text-right">{{ number_format($p->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-gray-400">Tidak ada transaksi pemasukan pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- RINCIAN PENGELUARAN -->
        <div class="mb-10">
            <h3 class="text-base font-bold text-gray-800 mb-3 border-b pb-1">2. Rincian Pengeluaran</h3>
            <table class="w-full text-xs border border-gray-300">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-300 font-bold">
                        <th class="py-2 px-3 text-center w-10">No</th>
                        <th class="py-2 px-3 text-left">Tanggal</th>
                        <th class="py-2 px-3 text-left">Keterangan</th>
                        <th class="py-2 px-3 text-right">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($rincianPengeluaran as $idx => $k)
                    <tr>
                        <td class="py-2 px-3 text-center">{{ $idx + 1 }}</td>
                        <td class="py-2 px-3">{{ $k->tanggal->format('d/m/Y') }}</td>
                        <td class="py-2 px-3">{{ $k->keterangan }}</td>
                        <td class="py-2 px-3 text-right">{{ number_format($k->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-gray-400">Tidak ada transaksi pengeluaran pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TANDA TANGAN -->
        <div class="grid grid-cols-2 text-center text-xs mt-12">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold">Ketua Bank Sampah</p>
                <div class="h-20"></div>
                <p class="font-semibold underline">( ........................................ )</p>
            </div>
            <div>
                <p>{{ now()->format('d F Y') }}</p>
                <p class="font-bold">Bendahara</p>
                <div class="h-20"></div>
                <p class="font-semibold underline">( {{ Auth::guard('petugas')->user()->nama ?? 'Bendahara' }} )</p>
            </div>
        </div>
    </div>
</body>
</html>
