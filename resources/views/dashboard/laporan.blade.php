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
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">Ammirudin suep</h2>
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
            <div class="flex w-full max-w-5xl mx-auto mb-6 rounded-[10px] border-[1.5px] border-gray-300 bg-white shadow-sm">
                <a href="{{ route('pengelola.laporan') }}" class="w-1/2 text-center py-3 text-[22px] font-bold text-white bg-[#5C8A8A] shadow-[inset_0_4px_8px_rgba(0,0,0,0.3)] transition">
                    Setoran
                </a>
                <!-- Tambahkan route pengelola.laporan.penjualan di href di bawah ini -->
                <a href="{{ route('pengelola.laporan.penjualan') }}" class="w-1/2 text-center py-3 text-[22px] font-bold text-black bg-white hover:bg-gray-100 transition border-l border-gray-300">
                    Penjualan
                </a>
            </div>
            <!-- KARTU LAPORAN UTAMA -->
            <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-5xl mx-auto mb-10">

                <!-- Header Kartu -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-black">Laporan Setoran Nasabah</h2>
                    <p class="text-gray-500 text-sm">Ringkasan sampah yang disetorkan nasabah ke BSSE dalam satu pekan</p>
                </div>

                <!-- 4 KARTU STATISTIK (Grid) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <!-- Stat 1: Jumlah Transaksi -->
                    <div class="bg-[#EAF5E5] border border-[#d2e8cb] rounded-lg p-4 flex items-center justify-between shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-700 text-sm font-semibold mb-1">Jumlah Transaksi</span>
                            <span class="text-3xl font-extrabold text-black">134</span>
                            <span class="text-[10px] text-gray-500 mt-2"><span class="text-green-600 font-bold"><i class="fa-solid fa-chevron-up"></i> 12%</span><br>Dari Pekan Sebelumnya</span>
                        </div>
                        <i class="fa-regular fa-file-lines text-[#A5D6A7] text-5xl opacity-50 mr-2"></i>
                    </div>

                    <!-- Stat 2: Total Berat -->
                    <div class="bg-[#E0F7FA] border border-[#b2ebf2] rounded-lg p-4 flex items-center justify-between shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-700 text-sm font-semibold mb-1">Total Berat Sampah</span>
                            <span class="text-2xl font-extrabold text-black">543,5 Kg</span>
                            <span class="text-[10px] text-gray-500 mt-2"><span class="text-green-600 font-bold"><i class="fa-solid fa-chevron-up"></i> 8%</span><br>Dari Pekan Sebelumnya</span>
                        </div>
                    </div>

                    <!-- Stat 3: Nilai Setoran -->
                    <div class="bg-[#FFF3E0] border border-[#ffe0b2] rounded-lg p-4 flex items-center justify-between shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-700 text-sm font-semibold mb-1">Nilai Setoran (100%)</span>
                            <span class="text-xl font-extrabold text-black mt-1">Rp. 10.300.000</span>
                            <span class="text-[10px] text-gray-500 mt-2"><span class="text-green-600 font-bold"><i class="fa-solid fa-chevron-up"></i> 15%</span><br>Dari Pekan Sebelumnya</span>
                        </div>
                    </div>

                    <!-- Stat 4: Bagian Nasabah -->
                    <div class="bg-[#FFEBEE] border border-[#ffcdd2] rounded-lg p-4 flex items-center justify-between shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-700 text-sm font-semibold mb-1">Bagian Nasabah (90%)</span>
                            <span class="text-xl font-extrabold text-black mt-1">Rp. 1.300.000</span>
                            <span class="text-[10px] text-gray-500 mt-2"><span class="text-green-600 font-bold"><i class="fa-solid fa-chevron-up"></i> 15%</span><br>Dari Pekan Sebelumnya</span>
                        </div>
                        <i class="fa-regular fa-user text-[#ef9a9a] text-5xl opacity-50 mr-2"></i>
                    </div>
                </div>

                <!-- AREA GRAFIK (2 Kolom) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Grafik Batang -->
                    <div class="border border-gray-200 rounded-lg p-4 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-800 mb-4">Nilai Setoran per Kategori</h3>
                        <div class="h-64 w-full">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>

                    <!-- Grafik Lingkaran (Donut) -->
                    <div class="border border-gray-200 rounded-lg p-4 shadow-sm relative">
                        <h3 class="text-sm font-bold text-gray-800 mb-4">Persentase Berat per Kategori</h3>
                        <div class="h-64 w-full flex justify-center">
                            <canvas id="donutChart"></canvas>
                        </div>
                        <!-- Custom Legend / Labels di kanan khusus Donut Chart -->
                        <div class="absolute right-4 top-1/2 transform -translate-y-1/2 flex flex-col gap-2 text-xs font-semibold text-gray-700">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#7C4DFF]"></span> Plastik</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#1DE9B6]"></span> Kertas</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#FF8A65]"></span> Logam</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#FFCDD2]"></span> Kaca</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#FFB74D]"></span> Lainnya</div>
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="overflow-x-auto border border-gray-300 rounded-lg">
                    <table id="laporanTable" class="w-full text-left text-[11px] border-collapse">
                        <thead>
                            <tr class="bg-[#D0EBE8] border-b border-gray-300">
                                <th class="py-2 px-2 text-center text-black font-bold border-r border-gray-300 w-8">No</th>
                                <th class="py-2 px-3 text-center text-black font-bold border-r border-gray-300">Jenis Sampah</th>
                                <th class="py-2 px-3 text-center text-black font-bold border-r border-gray-300 w-20">Kategori</th>
                                <th class="py-2 px-2 text-center text-black font-bold border-r border-gray-300">Jumlah Transaksi</th>
                                <th class="py-2 px-2 text-center text-black font-bold border-r border-gray-300">Total Berat (Kg)</th>
                                <th class="py-2 px-2 text-center text-black font-bold border-r border-gray-300">Harga/Kg (Rp)</th>
                                <th class="py-2 px-2 text-center text-black font-bold border-r border-gray-300">Nilai Setoran (Rp)</th>
                                <th class="py-2 px-2 text-center text-black font-bold border-r border-gray-300">Bagian Nasabah<br>90% (Rp)</th>
                                <th class="py-2 px-2 text-center text-black font-bold">Bagian BSSE 10%<br>(Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Baris 1 -->
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2 px-2 text-center border-r border-gray-200">1</td>
                                <td class="py-2 px-3 border-r border-gray-200">Botol Putih (P06)</td>
                                <td class="py-2 px-3 text-center border-r border-gray-200"><span class="bg-[#7C4DFF] text-white py-1 px-2 rounded-md block">Plastik</span></td>
                                <td class="py-2 px-2 text-center border-r border-gray-200">16</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">65,4</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">2.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">135.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">117.720</td>
                                <td class="py-2 px-2 text-right">13.080</td>
                            </tr>
                            <!-- Baris 2 -->
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2 px-2 text-center border-r border-gray-200">2</td>
                                <td class="py-2 px-3 border-r border-gray-200">Pet Berwarna (P14)</td>
                                <td class="py-2 px-3 text-center border-r border-gray-200"><span class="bg-[#7C4DFF] text-white py-1 px-2 rounded-md block">Plastik</span></td>
                                <td class="py-2 px-2 text-center border-r border-gray-200">16</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">65,4</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">2.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">135.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">117.720</td>
                                <td class="py-2 px-2 text-right">13.080</td>
                            </tr>
                            <!-- Baris 3 -->
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2 px-2 text-center border-r border-gray-200">3</td>
                                <td class="py-2 px-3 border-r border-gray-200">Majalah/Duplek/Karton (K05)</td>
                                <td class="py-2 px-3 text-center border-r border-gray-200"><span class="bg-[#1DE9B6] text-white py-1 px-2 rounded-md block">Kertas</span></td>
                                <td class="py-2 px-2 text-center border-r border-gray-200">16</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">65,4</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">2.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">135.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">117.720</td>
                                <td class="py-2 px-2 text-right">13.080</td>
                            </tr>
                            <!-- Baris 4 -->
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2 px-2 text-center border-r border-gray-200">4</td>
                                <td class="py-2 px-3 border-r border-gray-200">Besi (L01)</td>
                                <td class="py-2 px-3 text-center border-r border-gray-200"><span class="bg-[#FF8A65] text-white py-1 px-2 rounded-md block">Logam</span></td>
                                <td class="py-2 px-2 text-center border-r border-gray-200">16</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">65,4</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">2.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">135.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">117.720</td>
                                <td class="py-2 px-2 text-right">13.080</td>
                            </tr>
                            <!-- Baris 5 -->
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2 px-2 text-center border-r border-gray-200">5</td>
                                <td class="py-2 px-3 border-r border-gray-200">Gelas Sablon/Ale Ale (P09)</td>
                                <td class="py-2 px-3 text-center border-r border-gray-200"><span class="bg-[#7C4DFF] text-white py-1 px-2 rounded-md block">Plastik</span></td>
                                <td class="py-2 px-2 text-center border-r border-gray-200">16</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">65,4</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">2.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">135.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">117.720</td>
                                <td class="py-2 px-2 text-right">13.080</td>
                            </tr>
                            <!-- Baris 6 -->
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2 px-2 text-center border-r border-gray-200">6</td>
                                <td class="py-2 px-3 border-r border-gray-200">Accu (C01)</td>
                                <td class="py-2 px-3 text-center border-r border-gray-200"><span class="bg-[#FFB74D] text-white py-1 px-2 rounded-md block">Lainnya</span></td>
                                <td class="py-2 px-2 text-center border-r border-gray-200">16</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">65,4</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">2.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">135.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">117.720</td>
                                <td class="py-2 px-2 text-right">13.080</td>
                            </tr>
                            <!-- Baris 7 -->
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-2 px-2 text-center border-r border-gray-200">7</td>
                                <td class="py-2 px-3 border-r border-gray-200">Botol Kecap (B11)</td>
                                <td class="py-2 px-3 text-center border-r border-gray-200"><span class="bg-[#4DD0E1] text-white py-1 px-2 rounded-md block">Kaca</span></td>
                                <td class="py-2 px-2 text-center border-r border-gray-200">16</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">65,4</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">2.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">135.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">117.720</td>
                                <td class="py-2 px-2 text-right">13.080</td>
                            </tr>
                            <!-- Baris 8 -->
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-2 text-center border-r border-gray-200">8</td>
                                <td class="py-2 px-3 border-r border-gray-200">Per Kasur/Kawat (502)</td>
                                <td class="py-2 px-3 text-center border-r border-gray-200"><span class="bg-[#FF8A65] text-white py-1 px-2 rounded-md block">Logam</span></td>
                                <td class="py-2 px-2 text-center border-r border-gray-200">16</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">65,4</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">2.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">135.000</td>
                                <td class="py-2 px-2 text-right border-r border-gray-200">117.720</td>
                                <td class="py-2 px-2 text-right">13.080</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tombol Download -->
                <div class="flex justify-end mt-6">
                    <button onclick="downloadCSVLaporan()" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition-colors text-sm">
                        Download Data
                    </button>
                </div>

            </div> <!-- End Card Utama -->
        </main>
    </div>

    <!-- SCRIPT UNTUK GRAFIK & DOWNLOAD DATA -->
    <script>
        // INISIALISASI GRAFIK BATANG (BAR CHART)
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Plastik', 'Kertas', 'Logam', 'Kaca', 'Lainnya'],
                datasets: [{
                    label: 'Nilai Setoran',
                    data: [20, 38, 78, 21, 80], // Data mockup sesuai gambar
                    backgroundColor: '#8C9EFF', // Warna ungu muda
                    borderRadius: 4,
                    barThickness: 35
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
                        max: 100,
                        ticks: { stepSize: 20, color: '#9CA3AF' },
                        grid: { borderDash: [5, 5] } // Garis putus-putus
                    },
                    x: {
                        ticks: { color: '#6B7280', font: { weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            }
        });

        // INISIALISASI GRAFIK LINGKARAN (DONUT CHART)
        const ctxDonut = document.getElementById('donutChart').getContext('2d');

        // Plugin kustom untuk teks di tengah donut chart
        const centerTextPlugin = {
            id: 'centerText',
            beforeDraw: function(chart) {
                if (chart.config.type === 'doughnut') {
                    let width = chart.width, height = chart.height, ctx = chart.ctx;
                    ctx.restore();
                    let fontSize = (height / 120).toFixed(2);
                    ctx.textBaseline = "middle";
                    ctx.textAlign = "center";

                    // Teks Baris 1
                    ctx.font = "bold " + fontSize + "em sans-serif";
                    ctx.fillStyle = "#000";
                    ctx.fillText("543,5 Kg", width / 2, height / 2 - 10);

                    // Teks Baris 2
                    ctx.font = "normal " + (fontSize * 0.6) + "em sans-serif";
                    ctx.fillStyle = "#6B7280";
                    ctx.fillText("Total Berat", width / 2, height / 2 + 15);
                    ctx.save();
                }
            }
        };

        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Plastik', 'Kertas', 'Logam', 'Kaca', 'Lainnya'],
                datasets: [{
                    data: [38.45, 7.8, 17.92, 3.4, 32.43], // Persentase mockup
                    backgroundColor: [
                        '#7C4DFF', // Plastik (Ungu)
                        '#1DE9B6', // Kertas (Tosca)
                        '#FFB74D', // Lainnya (Orange)
                        '#FFCDD2', // Kaca (Pink Muda)
                        '#FF8A65'  // Logam (Merah/Orange)
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%', // Besaran lubang tengah
                plugins: {
                    legend: { display: false } // Legend dinonaktifkan karena kita buat custom HTML legend di atas
                }
            },
            plugins: [centerTextPlugin]
        });

        // FUNGSI DOWNLOAD DATA TABEL KE CSV
        function downloadCSVLaporan() {
            let csv = [];
            let table = document.getElementById("laporanTable");
            let rows = table.querySelectorAll("tr");

            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 0; j < cols.length; j++) {
                    // Bersihkan spasi berlebih, newline, dan koma dari dalam teks agar CSV rapi
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/,/g, "");
                    row.push('"' + data.trim() + '"');
                }
                csv.push(row.join(","));
            }

            let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
            let downloadLink = document.createElement("a");
            downloadLink.download = "Laporan_Setoran_Metanoia.csv";
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }
    </script>
</body>
</html>
