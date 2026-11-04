<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN untuk Grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }
    </style>
</head>
<body class="bg-[#EAEAEA] h-screen font-sans relative">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10 flex-shrink-0">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <img src="{{ Auth::guard('petugas')->user()?->foto ? (filter_var(Auth::guard('petugas')->user()->foto, FILTER_VALIDATE_URL) ? Auth::guard('petugas')->user()->foto : asset(Auth::guard('petugas')->user()->foto)) : 'https://i.pravatar.cc/150?img=12' }}" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">{{ Auth::guard('petugas')->user()->nama ?? 'Pengelola' }}</h2>
                </div>

                <nav class="px-4 space-y-2">
                    <a href="{{ route('pengelola.dashboard') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-house w-6"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('pengelola.nasabah') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-users w-6"></i>
                        <span>Nasabah</span>
                    </a>
                    <a href="{{ route('pengelola.transaksi') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-money-bill-transfer w-6"></i>
                        <span>Transaksi</span>
                    </a>
                    <a href="{{ route('pengelola.penimbangan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-scale-balanced w-6"></i>
                        <span>Penimbangan</span>
                    </a>
                    <a href="{{ route('pengelola.detail-sampah') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-trash w-6"></i>
                        <span>Detail Sampah</span>
                    </a>

                    <!-- Menu Laporan Aktif -->
                    <a href="{{ route('pengelola.laporan') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors">
                        <i class="fa-solid fa-file-lines w-6"></i>
                        <span>Laporan</span>
                    </a>
                </nav>
            </div>

            <div class="p-4 mb-4">
                <a href="{{ route('pengelola.pengaturan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                    <i class="fa-solid fa-gear w-6"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-10 overflow-y-auto relative z-0 flex flex-col">

            <h1 class="text-[36px] font-extrabold text-black mb-6">Laporan</h1>

            <!-- TAB MENU (Setoran & Penjualan) -->
            <div class="flex w-full max-w-5xl mx-auto mb-6 rounded-[10px] border-[1.5px] border-gray-300 bg-white shadow-sm overflow-hidden">
                <a href="{{ route('pengelola.laporan') }}" class="w-1/2 text-center py-3 text-[22px] font-bold text-white bg-[#5C8A8A] shadow-[inset_0_4px_8px_rgba(0,0,0,0.3)] transition">
                    Setoran
                </a>
                <a href="{{ route('pengelola.laporan.penjualan') }}" class="w-1/2 text-center py-3 text-[22px] font-bold text-black bg-white hover:bg-gray-100 transition border-l border-gray-300">
                    Penjualan
                </a>
            </div>

            <!-- FILTER PERIODE TANGGAL -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 w-full max-w-5xl mx-auto mb-6">
                <form action="{{ route('pengelola.laporan') }}" method="GET" class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                        <span class="text-xs font-bold text-gray-600 uppercase flex items-center gap-1.5 whitespace-nowrap">
                            <i class="fa-regular fa-calendar"></i> Periode:
                        </span>
                        <input type="date" name="tanggal_mulai" value="{{ $startDate }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 focus:outline-none focus:border-[#5C8A8A]">
                        <span class="text-xs text-gray-500 font-bold">s/d</span>
                        <input type="date" name="tanggal_selesai" value="{{ $endDate }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 focus:outline-none focus:border-[#5C8A8A]">
                        <button type="submit" class="bg-[#5C8A8A] hover:bg-[#4a7070] text-white px-4 py-2 rounded-lg text-xs font-bold transition-colors">
                            Filter
                        </button>
                    </div>

                    @if ($startDate !== now()->startOfMonth()->toDateString() || $endDate !== now()->endOfMonth()->toDateString())
                        <a href="{{ route('pengelola.laporan') }}" class="text-xs font-bold text-gray-500 hover:text-gray-700">
                            Reset ke Bulan Berjalan
                        </a>
                    @endif
                </form>
            </div>

            <!-- KARTU LAPORAN UTAMA -->
            <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-5xl mx-auto mb-10">

                <!-- Header Kartu -->
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-2xl font-bold text-black">Laporan Setoran Nasabah</h2>
                        <p class="text-gray-500 text-sm">Ringkasan sampah yang disetorkan nasabah pada periode terpilih</p>
                    </div>
                </div>

                <!-- 4 KARTU STATISTIK (Ringkasan Setoran) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <!-- Stat 1: Jumlah Transaksi -->
                    <div class="bg-[#EAF5E5] border border-[#d2e8cb] rounded-lg p-4 flex items-center justify-between shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-700 text-sm font-semibold mb-1">Jumlah Transaksi</span>
                            <span class="text-3xl font-extrabold text-black">{{ number_format($ringkasan['jumlah_transaksi'], 0, ',', '.') }}</span>
                            <span class="text-[10px] text-gray-500 mt-2">Transaksi Tercatat</span>
                        </div>
                        <i class="fa-regular fa-file-lines text-[#A5D6A7] text-5xl opacity-50 mr-2"></i>
                    </div>

                    <!-- Stat 2: Total Berat -->
                    <div class="bg-[#E0F7FA] border border-[#b2ebf2] rounded-lg p-4 flex items-center justify-between shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-700 text-sm font-semibold mb-1">Total Berat Sampah</span>
                            <span class="text-2xl font-extrabold text-black">{{ number_format($ringkasan['total_berat'], 2, ',', '.') }} Kg</span>
                            <span class="text-[10px] text-gray-500 mt-2">Bobot Terkumpul</span>
                        </div>
                        <i class="fa-solid fa-weight-scale text-[#80deea] text-5xl opacity-40 mr-2"></i>
                    </div>

                    <!-- Stat 3: Nilai Setoran -->
                    <div class="bg-[#FFF3E0] border border-[#ffe0b2] rounded-lg p-4 flex items-center justify-between shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-700 text-sm font-semibold mb-1">Nilai Setoran</span>
                            <span class="text-xl font-extrabold text-black mt-1">Rp. {{ number_format($ringkasan['nilai_setoran'], 0, ',', '.') }}</span>
                            <span class="text-[10px] text-gray-500 mt-2">Total Tabungan Nasabah</span>
                        </div>
                        <i class="fa-solid fa-money-bill-wave text-[#ffcc80] text-5xl opacity-40 mr-2"></i>
                    </div>

                    <!-- Stat 4: Bagian Nasabah (KEPUTUSAN DEFAULT: "-") -->
                    <div class="bg-[#FFEBEE] border border-[#ffcdd2] rounded-lg p-4 flex items-center justify-between shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-700 text-sm font-semibold mb-1">Bagian Nasabah</span>
                            <span class="text-2xl font-extrabold text-black mt-1">{{ $ringkasan['bagian_nasabah'] }}</span>
                            <span class="text-[10px] text-gray-400 mt-2">* Tidak diatur dokumen</span>
                        </div>
                        <i class="fa-regular fa-user text-[#ef9a9a] text-5xl opacity-50 mr-2"></i>
                    </div>
                </div>

                <!-- AREA GRAFIK (2 Kolom) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Grafik Batang: Nilai Setoran per Kategori -->
                    <div class="border border-gray-200 rounded-lg p-4 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-800 mb-4">Nilai Setoran per Kategori</h3>
                        <div class="h-64 w-full">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>

                    <!-- Grafik Lingkaran (Donut): Persentase Berat per Kategori -->
                    <div class="border border-gray-200 rounded-lg p-4 shadow-sm relative">
                        <h3 class="text-sm font-bold text-gray-800 mb-4">Persentase Berat per Kategori</h3>
                        <div class="h-64 w-full flex justify-center">
                            <canvas id="donutChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA RINCIAN PER JENIS SAMPAH -->
                <div class="overflow-x-auto border border-gray-300 rounded-lg mb-6">
                    <table id="laporanTable" class="w-full text-left text-[11px] border-collapse">
                        <thead>
                            <tr class="bg-[#D0EBE8] border-b border-gray-300">
                                <th class="py-2.5 px-2 text-center text-black font-bold border-r border-gray-300 w-8">No</th>
                                <th class="py-2.5 px-3 text-center text-black font-bold border-r border-gray-300">Jenis Sampah</th>
                                <th class="py-2.5 px-3 text-center text-black font-bold border-r border-gray-300 w-24">Kategori</th>
                                <th class="py-2.5 px-2 text-center text-black font-bold border-r border-gray-300">Jumlah Transaksi</th>
                                <th class="py-2.5 px-2 text-center text-black font-bold border-r border-gray-300">Total Berat (Kg)</th>
                                <th class="py-2.5 px-2 text-center text-black font-bold border-r border-gray-300">Harga/Kg (Rp)</th>
                                <th class="py-2.5 px-2 text-center text-black font-bold border-r border-gray-300">Nilai Setoran (Rp)</th>
                                <th class="py-2.5 px-2 text-center text-black font-bold border-r border-gray-300">Bagian Nasabah</th>
                                <th class="py-2.5 px-2 text-center text-black font-bold">Bagian BSSE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rincianJenis as $item)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-2 px-2 text-center border-r border-gray-200">{{ $loop->iteration }}</td>
                                    <td class="py-2 px-3 border-r border-gray-200 font-semibold">{{ $item->nama }} ({{ $item->kode }})</td>
                                    <td class="py-2 px-3 text-center border-r border-gray-200">
                                        <span class="bg-[#5C8A8A] text-white py-0.5 px-2 rounded text-[10px] font-bold block truncate">
                                            {{ $item->kategori }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-2 text-center border-r border-gray-200">{{ $item->jumlah_transaksi }}</td>
                                    <td class="py-2 px-2 text-right border-r border-gray-200 font-mono">{{ number_format($item->total_berat, 2, ',', '.') }}</td>
                                    <td class="py-2 px-2 text-right border-r border-gray-200 font-mono">{{ number_format($item->harga_per_kg, 0, ',', '.') }}</td>
                                    <td class="py-2 px-2 text-right border-r border-gray-200 font-mono font-bold">{{ number_format($item->nilai_setoran, 0, ',', '.') }}</td>
                                    <td class="py-2 px-2 text-center border-r border-gray-200 text-gray-400 font-bold">-</td>
                                    <td class="py-2 px-2 text-center text-gray-400 font-bold">-</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-gray-500 font-medium">
                                        Tidak ada data setoran sampah pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tombol Download Data (CSV Streaming Backend) -->
                <div class="flex justify-end">
                    <a href="{{ route('pengelola.laporan.download', ['tanggal_mulai' => $startDate, 'tanggal_selesai' => $endDate]) }}"
                        class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors text-sm flex items-center gap-2">
                        <i class="fa-solid fa-download"></i>
                        <span>Download Data (CSV)</span>
                    </a>
                </div>

            </div> <!-- End Card Utama -->
        </main>
    </div>

    <!-- SCRIPT UNTUK GRAFIK DENGAN DATA SERVER NYATA -->
    <script>
        const kategoriLabels = @json($kategoriLabels);
        const kategoriNilai = @json($kategoriNilai);
        const kategoriPersen = @json($kategoriPersenBerat);
        const totalBeratFormatted = "{{ number_format($ringkasan['total_berat'], 1, ',', '.') }} Kg";

        // Warna tema kategori
        const palette = ['#7C4DFF', '#1DE9B6', '#FF8A65', '#4DD0E1', '#FFB74D', '#E91E63', '#9C27B0'];

        // 1. GRAFIK BATANG (BAR CHART) NILAI SETORAN
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: kategoriLabels.length > 0 ? kategoriLabels : ['Tidak Ada Data'],
                datasets: [{
                    label: 'Nilai Setoran (Rp)',
                    data: kategoriNilai.length > 0 ? kategoriNilai : [0],
                    backgroundColor: '#8C9EFF',
                    borderRadius: 4,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) {
                                return 'Rp ' + Number(val).toLocaleString('id-ID');
                            },
                            color: '#9CA3AF'
                        },
                        grid: { borderDash: [4, 4] }
                    },
                    x: {
                        ticks: { color: '#4B5563', font: { weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            }
        });

        // 2. GRAFIK DONAT (DONUT CHART) PERSENTASE BERAT
        const ctxDonut = document.getElementById('donutChart').getContext('2d');

        const centerTextPlugin = {
            id: 'centerText',
            beforeDraw: function(chart) {
                if (chart.config.type === 'doughnut') {
                    const width = chart.width, height = chart.height, ctx = chart.ctx;
                    ctx.restore();
                    const fontSize = (height / 120).toFixed(2);
                    ctx.textBaseline = "middle";
                    ctx.textAlign = "center";

                    ctx.font = "bold " + fontSize + "em sans-serif";
                    ctx.fillStyle = "#111827";
                    ctx.fillText(totalBeratFormatted, width / 2, height / 2 - 8);

                    ctx.font = "normal " + (fontSize * 0.55) + "em sans-serif";
                    ctx.fillStyle = "#6B7280";
                    ctx.fillText("Total Berat", width / 2, height / 2 + 14);
                    ctx.save();
                }
            }
        };

        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: kategoriLabels.length > 0 ? kategoriLabels : ['Tidak Ada Data'],
                datasets: [{
                    data: kategoriPersen.length > 0 ? kategoriPersen : [100],
                    backgroundColor: kategoriLabels.length > 0 ? palette.slice(0, kategoriLabels.length) : ['#E5E7EB'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: { size: 11, weight: 'bold' }
                        }
                    }
                }
            },
            plugins: [centerTextPlugin]
        });
    </script>
</body>
</html>
