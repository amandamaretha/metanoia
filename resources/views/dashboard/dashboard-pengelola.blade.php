<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengelola</title>
    <!-- Tailwind CSS (Gunakan CDN untuk testing cepat, pastikan sudah install via npm di production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- FontAwesome untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom Colors */
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }
    </style>
</head>
<body class="bg-gray-200 h-screen font-sans">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white">
            <!-- Profile Section -->
            <div class="flex flex-col items-center mt-10 mb-8">
                <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                    <!-- Ganti dengan image profile asli -->
                    <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                </div>
                <h2 class="mt-4 text-lg font-bold">Ammirudin suep</h2>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 space-y-2">
                <!-- Menu Dashboard -->
                    <a href="{{ route('pengelola.dashboard') }}"
                       class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.dashboard') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-house w-6"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Menu Nasabah -->
                    <a href="{{ route('pengelola.nasabah') }}"
                       class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.nasabah*') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-users w-6"></i>
                        <span>Nasabah</span>
                    </a>

                    <!-- Menu Transaksi -->
                    <a href="{{ route('pengelola.transaksi') }}"
                       class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.transaksi') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-money-bill-transfer w-6"></i>
                        <span>Transaksi</span>
                    </a>

                <a href="{{ route('pengelola.penimbangan') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.penimbangan') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                    <i class="fa-solid fa-scale-balanced w-6"></i>
                    <span>Penimbangan</span>
                </a>

                <a href="{{ route('pengelola.detail-sampah') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.detail-sampah') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                    <i class="fa-solid fa-trash w-6"></i>
                    <span>Detail Sampah</span>
                </a>

                <a href="{{ route('pengelola.laporan') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.laporan') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                    <i class="fa-solid fa-file-lines w-6"></i>
                    <span>Laporan</span>
                </a>
            </nav>

            <!-- Settings -->
            <div class="p-4 mb-4">
                <a href="{{ route('pengelola.pengaturan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition-colors">
                    <span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-extrabold text-black mb-8">DASHBOARD</h1>

            <!-- CARDS STATISTIK -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="bg-[#00B4D8] rounded-xl text-white relative overflow-hidden flex flex-col justify-between shadow-md">
                    <div class="p-5 relative z-10">
                        <h3 class="text-4xl font-bold">30</h3>
                        <p class="text-sm mt-1">Total Nasabah</p>
                    </div>
                    <i class="fa-solid fa-users absolute right-4 top-4 text-7xl text-white/20"></i>
                    <a href="{{ route('pengelola.nasabah') }}" class="bg-black/10 py-2 px-5 text-sm flex items-center justify-between hover:bg-black/20 transition">
                        Lihat Detail <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Card 2 -->
                <div class="bg-[#00A86B] rounded-xl text-white relative overflow-hidden flex flex-col justify-between shadow-md">
                    <div class="p-5 relative z-10">
                        <h3 class="text-4xl font-bold">25</h3>
                        <p class="text-sm mt-1">Setoran Pekan Ini</p>
                    </div>
                    <i class="fa-solid fa-wallet absolute right-4 top-4 text-7xl text-white/20"></i>
                    <a href="{{ route('pengelola.transaksi') }}" class="bg-black/10 py-2 px-5 text-sm flex items-center justify-between hover:bg-black/20 transition">
                        Lihat Detail <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Card 3 -->
                <div class="bg-[#F29F05] rounded-xl text-white relative overflow-hidden flex flex-col justify-between shadow-md">
                    <div class="p-5 relative z-10">
                        <h3 class="text-4xl font-bold">45Kg</h3>
                        <p class="text-sm mt-1">Sampah Masuk</p>
                    </div>
                    <i class="fa-solid fa-scale-unbalanced absolute right-4 top-4 text-7xl text-white/20"></i>
                    <a href="{{ route('pengelola.laporan') }}" class="bg-black/10 py-2 px-5 text-sm flex items-center justify-between hover:bg-black/20 transition">
                        Lihat Detail <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Card 4 -->
                <div class="bg-[#D33F3F] rounded-xl text-white relative overflow-hidden flex flex-col justify-between shadow-md">
                    <div class="p-5 relative z-10">
                        <h3 class="text-2xl font-bold mt-2">Rp 10.000.000</h3>
                        <p class="text-sm mt-1">Nilai Setoran Pekan ini</p>
                    </div>
                    <i class="fa-solid fa-sack-dollar absolute right-4 top-4 text-7xl text-white/20"></i>
                    <a href="{{ route('pengelola.laporan') }}" class="bg-black/10 py-2 px-5 text-sm flex items-center justify-between hover:bg-black/20 transition">
                        Lihat Detail <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- CHARTS & TABLE SECTION -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column (Charts) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Chart Kategori Sampah -->
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h4 class="text-gray-500 text-sm mb-4">Kategori Sampah Masuk Pekan Ini</h4>
                        <div class="h-64">
                            <canvas id="kategoriChart"></canvas>
                        </div>
                    </div>

                    <!-- Chart Ringkasan Sampah -->
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h4 class="text-gray-500 text-sm mb-4">Ringkasan Sampah Masuk Bulan September</h4>
                        <div class="h-64">
                            <canvas id="ringkasanChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Table) -->
                <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col">
                    <h4 class="text-gray-500 text-sm mb-4 border-b pb-2">Transaksi Terbaru</h4>

                    <div class="flex-1">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2 font-bold text-black">Nasabah</th>
                                    <th class="py-2 font-bold text-black">Jenis Sampah</th>
                                    <th class="py-2 font-bold text-black text-center">Berat(Kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-gray-100">
                                    <td class="py-2 px-2 rounded-l-md text-gray-700">Asep Suros..</td>
                                    <td class="py-2 text-gray-700">Botol Putih P06</td>
                                    <td class="py-2 px-2 rounded-r-md text-center"><span class="bg-[#F3E5F5] text-purple-700 py-1 px-3 rounded-md font-medium">9</span></td>
                                </tr>
                                <tr class="">
                                    <td class="py-2 px-2 rounded-l-md text-gray-700">Angga badr..</td>
                                    <td class="py-2 text-gray-700">Botol Putih P06</td>
                                    <td class="py-2 px-2 rounded-r-md text-center"><span class="bg-[#F3E5F5] text-purple-700 py-1 px-3 rounded-md font-medium">11</span></td>
                                </tr>
                                <tr class="bg-gray-100">
                                    <td class="py-2 px-2 rounded-l-md text-gray-700">Ggofur saep..</td>
                                    <td class="py-2 text-gray-700">Botol Putih P06</td>
                                    <td class="py-2 px-2 rounded-r-md text-center"><span class="bg-[#F3E5F5] text-purple-700 py-1 px-3 rounded-md font-medium">7</span></td>
                                </tr>
                                <tr class="">
                                    <td class="py-2 px-2 rounded-l-md text-gray-700">Duki rusman..</td>
                                    <td class="py-2 text-gray-700">Botol Putih P06</td>
                                    <td class="py-2 px-2 rounded-r-md text-center"><span class="bg-[#F3E5F5] text-purple-700 py-1 px-3 rounded-md font-medium">10</span></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Placeholder baris kosong -->
                        <div class="mt-4 space-y-2">
                            <div class="bg-gray-200 h-6 rounded w-full"></div>
                            <div class="bg-gray-200 h-6 rounded w-full"></div>
                            <div class="bg-gray-200 h-6 rounded w-full"></div>
                            <div class="bg-gray-200 h-6 rounded w-full"></div>
                            <div class="bg-gray-200 h-6 rounded w-full"></div>
                        </div>
                    </div>

                    <div class="mt-4 text-right pt-4 border-t">
                        <a href="#" class="text-blue-500 text-sm hover:underline">Selengkapnya <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- SCRIPT UNTUK GRAFIK (Chart.js) -->
    <script>
        // Chart Kategori Sampah (Horizontal Bar)
        const ctxKategori = document.getElementById('kategoriChart').getContext('2d');
        new Chart(ctxKategori, {
            type: 'bar',
            data: {
                labels: ['Lainnya', 'Kaca', 'Logam', 'Kertas', 'Plastik'],
                datasets: [{
                    label: 'Satuan (Kg)',
                    data: [11.5, 28.4, 38.2, 97, 97.5],
                    backgroundColor: [
                        '#6EA8FF', // Lainnya
                        '#F8D361', // Kaca
                        '#50C8B5', // Logam
                        '#FF9494', // Kertas
                        '#978CFF'  // Plastik
                    ],
                    borderWidth: 0,
                    barThickness: 12
                }]
            },
            options: {
                indexAxis: 'y', // Membuat bar menjadi horizontal
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { borderDash: [5, 5] },
                        max: 100
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Chart Ringkasan Sampah (Grouped Bar Chart)
        const ctxRingkasan = document.getElementById('ringkasanChart').getContext('2d');
        new Chart(ctxRingkasan, {
            type: 'bar',
            data: {
                labels: ['Pekan 1', 'Pekan 2', 'Pekan 3', 'Pekan 4', 'Pekan 5'],
                datasets: [
                    {
                        label: 'Rabu',
                        data: [45, 88, 93, 78, 100],
                        backgroundColor: '#8B84E6',
                        barPercentage: 0.8,
                        categoryPercentage: 0.4
                    },
                    {
                        label: 'Jumat',
                        data: [20, 85, 88, 22, 85],
                        backgroundColor: '#74D3B6',
                        barPercentage: 0.8,
                        categoryPercentage: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 10 }
                    }
                },
                scales: {
                    y: {
                        grid: { borderDash: [5, 5] },
                        max: 100,
                        beginAtZero: true
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>
