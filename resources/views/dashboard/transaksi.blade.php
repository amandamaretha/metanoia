<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
                <!-- Profile Section -->
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <!-- Ganti dengan image profile asli -->
                        <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">Ammirudin suep</h2>
                </div>

                <!-- Navigation Menu -->
                <nav class="px-4 space-y-2">
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

                    <!-- Menu Penimbangan (Belum ada route) -->
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

                    <!-- Menu Laporan (Belum ada route) -->
                    <a href="{{ route('pengelola.laporan') }}"
                       class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.laporan') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-file-lines w-6"></i>
                        <span>Laporan</span>
                    </a>
                </nav>
            </div>

            <div class="p-4 mb-4">
                <a href="{{ route('pengelola.pengaturan') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.pengaturan') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                    <i class="fa-solid fa-gear w-6"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-10 overflow-y-auto relative z-0">
            <h1 class="text-[40px] font-extrabold text-black mb-8">Daftar Riwayat Transaksi</h1>

            <!-- PENCARIAN & TABEL CONTAINER -->
            <div class="space-y-6">

                <!-- Box Pencarian -->
                <div class="bg-white p-4 rounded-xl shadow-sm flex items-center justify-end">
                    <div class="flex items-center gap-3 w-full max-w-md">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <!-- Input Search memanggil fungsi filterTable() saat diketik -->
                            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari riwayat nasabah"
                                class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm">
                        </div>
                        <button onclick="filterTable()" class="bg-[#5FA8DC] hover:bg-blue-600 text-white px-8 py-2.5 rounded-lg text-sm font-semibold transition">
                            Cari
                        </button>
                    </div>
                </div>

                <!-- Box Tabel -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="overflow-x-auto">
                        <table id="transaksiTable" class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="border-b-2 border-gray-200">
                                    <th class="py-3 px-4 text-black font-bold w-12">No</th>
                                    <th class="py-3 px-4 text-black font-bold">Nama Nasabah</th>
                                    <th class="py-3 px-4 text-black font-bold">Jenis Sampah</th>
                                    <th class="py-3 px-4 text-black font-bold">Berat(Kg)</th>
                                    <th class="py-3 px-4 text-black font-bold">Nilai</th>
                                    <th class="py-3 px-4 text-black font-bold">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data Asli -->
                                <tr class="bg-gray-200/60 transaction-row">
                                    <td class="py-3 px-4">1</td>
                                    <td class="py-3 px-4 nasabah-name">Asep Suroso Yandimar Dinata</td>
                                    <td class="py-3 px-4">Ember warna campur (P07)</td>
                                    <td class="py-3 px-4">6</td>
                                    <td class="py-3 px-4">75.000</td>
                                    <td class="py-3 px-4">19/10/2026</td>
                                </tr>
                                <tr class="transaction-row hover:bg-gray-50 transition">
                                    <td class="py-3 px-4">2</td>
                                    <td class="py-3 px-4 nasabah-name">Gofar Hilman Anggoro</td>
                                    <td class="py-3 px-4">Ember Hitam (P0B)</td>
                                    <td class="py-3 px-4">19</td>
                                    <td class="py-3 px-4">160.000</td>
                                    <td class="py-3 px-4">19/10/2026</td>
                                </tr>
                                <tr class="bg-gray-200/60 transaction-row">
                                    <td class="py-3 px-4">3</td>
                                    <td class="py-3 px-4 nasabah-name">Muhammad Amba Wedyo Diningrat</td>
                                    <td class="py-3 px-4">Botol Putih (P20)</td>
                                    <td class="py-3 px-4">23</td>
                                    <td class="py-3 px-4">230.000</td>
                                    <td class="py-3 px-4">19/10/2026</td>
                                </tr>
                                <tr class="transaction-row hover:bg-gray-50 transition">
                                    <td class="py-3 px-4">4</td>
                                    <td class="py-3 px-4 nasabah-name">Rusdi Malik Shidiq</td>
                                    <td class="py-3 px-4">Pet Bening Bersih (P11)</td>
                                    <td class="py-3 px-4">5</td>
                                    <td class="py-3 px-4">50.000</td>
                                    <td class="py-3 px-4">19/10/2026</td>
                                </tr>
                                <tr class="bg-gray-200/60 transaction-row">
                                    <td class="py-3 px-4">5</td>
                                    <td class="py-3 px-4 nasabah-name">Fuad Abdurohman Haikal</td>
                                    <td class="py-3 px-4">Pet Berwarna (P14)</td>
                                    <td class="py-3 px-4">3</td>
                                    <td class="py-3 px-4">25.000</td>
                                    <td class="py-3 px-4">19/10/2026</td>
                                </tr>

                                <!-- Placeholder Baris Kosong (Abu-abu) -->
                                <tr class="h-8 bg-gray-200/60 border-y-[6px] border-white no-export"><td colspan="6"></td></tr>
                                <tr class="h-8 bg-gray-200/60 border-b-[6px] border-white no-export"><td colspan="6"></td></tr>
                                <tr class="h-8 bg-gray-200/60 border-b-[6px] border-white no-export"><td colspan="6"></td></tr>
                                <tr class="h-8 bg-gray-200/60 border-b-[6px] border-white no-export"><td colspan="6"></td></tr>
                                <tr class="h-8 bg-gray-200/60 border-b-[6px] border-white no-export"><td colspan="6"></td></tr>
                                <tr class="h-8 bg-gray-200/60 border-b-[6px] border-white no-export"><td colspan="6"></td></tr>
                                <tr class="h-8 bg-gray-200/60 border-b-[6px] border-white no-export"><td colspan="6"></td></tr>
                                <tr class="h-8 bg-gray-200/60 no-export"><td colspan="6"></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TOMBOL DOWNLOAD DATA -->
                <div class="flex justify-end mt-4">
                    <button onclick="downloadCSV()" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-3 px-8 rounded-lg shadow-sm transition-colors text-[16px]">
                        Download Data
                    </button>
                </div>
            </div>

        </main>
    </div>

    <!-- JAVASCRIPT UNTUK FITUR SEARCH & DOWNLOAD -->
    <script>
        function filterTable() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let table = document.getElementById("transaksiTable");
            let tr = table.getElementsByClassName("transaction-row");

            for (let i = 0; i < tr.length; i++) {
                let td = tr[i].getElementsByClassName("nasabah-name")[0];
                if (td) {
                    let textValue = td.textContent || td.innerText;
                    if (textValue.toLowerCase().indexOf(input) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function downloadCSV() {
            let csv = [];
            let table = document.getElementById("transaksiTable");
            let rows = table.querySelectorAll("tr");

            for (let i = 0; i < rows.length; i++) {
                if(rows[i].classList.contains('no-export')) continue;

                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 0; j < cols.length; j++) {
                    let data = cols[j].innerText.replace(/,/g, "");
                    row.push('"' + data + '"');
                }
                csv.push(row.join(","));
            }

            let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
            let downloadLink = document.createElement("a");
            downloadLink.download = "Riwayat_Transaksi_Metanoia.csv";
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }
    </script>
</body>
</html>
