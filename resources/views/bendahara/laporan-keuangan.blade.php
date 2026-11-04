<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - Metanoia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }
    </style>
</head>
<body class="bg-[#EAEAEA] h-screen font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <img src="https://i.pravatar.cc/150?img=33" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">{{ Auth::guard('petugas')->user()->nama ?? 'Bendahara' }}</h2>
                    <span class="text-xs bg-white/20 px-3 py-1 rounded-full uppercase tracking-wider mt-1">Bendahara</span>
                </div>

                <nav class="px-4 space-y-2">
                    <a href="{{ route('bendahara.beranda') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-house w-6"></i>
                        <span>Beranda</span>
                    </a>
                    <a href="{{ route('bendahara.transaksi') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-money-bill-transfer w-6"></i>
                        <span>Tambah Transaksi</span>
                    </a>
                    <a href="{{ route('bendahara.riwayat') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-clock-rotate-left w-6"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                    <a href="{{ route('bendahara.laporan-keuangan') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors">
                        <i class="fa-solid fa-file-invoice-dollar w-6"></i>
                        <span>Laporan Keuangan</span>
                    </a>
                </nav>
            </div>

            <div class="px-4 mb-6">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 hover:bg-red-600/80 rounded-lg font-semibold transition-colors text-white">
                        <i class="fa-solid fa-right-from-bracket w-6"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto space-y-6">
                <!-- HEADER & PERIODE FILTER -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h1 class="text-3xl font-extrabold text-black">Laporan Keuangan</h1>
                    <div>
                        <a href="{{ route('bendahara.laporan-keuangan.cetak', ['tanggal_mulai' => $startDate, 'tanggal_selesai' => $endDate]) }}" target="_blank"
                            class="inline-flex items-center gap-2 bg-[#D9534F] hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition text-sm">
                            <i class="fa-solid fa-file-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>

                <!-- FILTER FORM -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <form action="{{ route('bendahara.laporan-keuangan') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4">
                        <div class="flex-1 w-full">
                            <label for="tanggal_mulai" class="block text-xs font-bold text-gray-600 uppercase mb-1">Tanggal Awal</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ $startDate }}" required
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                        </div>
                        <div class="flex-1 w-full">
                            <label for="tanggal_selesai" class="block text-xs font-bold text-gray-600 uppercase mb-1">Tanggal Akhir</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ $endDate }}" required
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                        </div>
                        <div>
                            <button type="submit" class="w-full md:w-auto bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold py-2.5 px-8 rounded-lg shadow-sm transition text-sm">
                                Tampilkan
                            </button>
                        </div>
                    </form>
                    @if ($errors->any())
                        <div class="mt-3 text-red-500 text-xs">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- KARTU RINGKASAN SALDO -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                        <span class="text-xs text-gray-500 font-bold block uppercase tracking-wider">Saldo Awal</span>
                        <span class="text-2xl font-extrabold text-gray-800 mt-2 block">
                            Rp {{ number_format($saldoAwal, 0, ',', '.') }}
                        </span>
                        <span class="text-[11px] text-gray-400 mt-1 block">Sebelum {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</span>
                    </div>

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                        <span class="text-xs text-green-600 font-bold block uppercase tracking-wider">Total Pemasukan</span>
                        <span class="text-2xl font-extrabold text-green-700 mt-2 block">
                            + Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                        </span>
                        <span class="text-[11px] text-gray-400 mt-1 block">{{ $rincianPemasukan->count() }} transaksi pemasukan</span>
                    </div>

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                        <span class="text-xs text-red-600 font-bold block uppercase tracking-wider">Total Pengeluaran</span>
                        <span class="text-2xl font-extrabold text-red-700 mt-2 block">
                            - Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                        </span>
                        <span class="text-[11px] text-gray-400 mt-1 block">{{ $rincianPengeluaran->count() }} transaksi pengeluaran</span>
                    </div>

                    <div class="bg-white p-5 rounded-xl shadow-sm border-2 border-[#5C8A8A]">
                        <span class="text-xs text-[#5C8A8A] font-bold block uppercase tracking-wider">Saldo Akhir</span>
                        <span class="text-2xl font-extrabold text-[#5C8A8A] mt-2 block">
                            Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                        </span>
                        <span class="text-[11px] text-gray-400 mt-1 block">Per {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                    </div>
                </div>

                <!-- RINCIAN PEMASUKAN & PENGELUARAN (DUA KOLOM) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kolom Rincian Pemasukan -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-green-50/70 px-6 py-4 border-b border-green-100 flex items-center justify-between">
                            <h2 class="text-base font-bold text-green-800 flex items-center gap-2">
                                <i class="fa-solid fa-arrow-down"></i> Rincian Pemasukan
                            </h2>
                            <span class="text-xs font-bold text-green-700">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</span>
                        </div>
                        <div class="p-4 overflow-x-auto max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 text-gray-600 font-semibold">
                                        <th class="py-2 px-2">Tanggal</th>
                                        <th class="py-2 px-2">Keterangan</th>
                                        <th class="py-2 px-2 text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($rincianPemasukan as $p)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-2 text-gray-600">{{ $p->tanggal->format('d/m/Y') }}</td>
                                        <td class="py-2 px-2 text-gray-800">{{ $p->keterangan }}</td>
                                        <td class="py-2 px-2 text-right font-semibold text-green-700">
                                            Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-400">Tidak ada transaksi pemasukan pada periode ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Kolom Rincian Pengeluaran -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-red-50/70 px-6 py-4 border-b border-red-100 flex items-center justify-between">
                            <h2 class="text-base font-bold text-red-800 flex items-center gap-2">
                                <i class="fa-solid fa-arrow-up"></i> Rincian Pengeluaran
                            </h2>
                            <span class="text-xs font-bold text-red-700">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span>
                        </div>
                        <div class="p-4 overflow-x-auto max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 text-gray-600 font-semibold">
                                        <th class="py-2 px-2">Tanggal</th>
                                        <th class="py-2 px-2">Keterangan</th>
                                        <th class="py-2 px-2 text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($rincianPengeluaran as $k)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-2 text-gray-600">{{ $k->tanggal->format('d/m/Y') }}</td>
                                        <td class="py-2 px-2 text-gray-800">{{ $k->keterangan }}</td>
                                        <td class="py-2 px-2 text-right font-semibold text-red-700">
                                            Rp {{ number_format($k->nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-400">Tidak ada transaksi pengeluaran pada periode ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
