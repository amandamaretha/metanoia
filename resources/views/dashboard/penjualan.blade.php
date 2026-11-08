<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Metanoia</title>
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
                    <a href="{{ route('pengelola.penimbangan') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.penimbangan') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-scale-balanced w-6"></i>
                        <span>Penimbangan</span>
                    </a>
                    <a href="{{ route('pengelola.detail-sampah') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-trash w-6"></i>
                        <span>Detail Sampah</span>
                    </a>
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

        <!-- MAIN CONTENT CONTAINER -->
        <main class="flex-1 p-10 overflow-y-auto relative z-0 flex flex-col">

            <!-- ============================================== -->
            <!-- VIEW 1: HALAMAN UTAMA LAPORAN PENJUALAN -->
            <!-- ============================================== -->
            <div id="mainView">
                <h1 class="text-[36px] font-extrabold text-black mb-6">Laporan</h1>

                <!-- TAB MENU -->
                <div class="flex w-full max-w-5xl mx-auto mb-6 rounded-[10px] border-[1.5px] border-gray-300 overflow-hidden bg-white shadow-sm">
                    <a href="{{ route('pengelola.laporan') }}" class="w-1/2 text-center py-3 text-[22px] font-bold text-black bg-white hover:bg-gray-100 transition border-r border-gray-300">
                        Setoran
                    </a>
                    <a href="{{ route('pengelola.laporan.penjualan') }}" class="w-1/2 text-center py-3 text-[22px] font-bold text-white bg-[#5C8A8A] shadow-[inset_0_4px_8px_rgba(0,0,0,0.3)] transition">
                        Penjualan
                    </a>
                </div>

                <!-- KARTU LAPORAN UTAMA -->
                <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-5xl mx-auto mb-10">

                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-black">Penjualan ke BSI</h2>
                        <p class="text-gray-500 text-sm">Catatan pengangkutan sampah dari BSSE ke Bank Sampah Induk (BSI)</p>
                    </div>

                    <!-- KARTU PERSIAPAN PENJUALAN -->
                    <div class="bg-[#FDF3DE] border-2 border-[#F3C78B] rounded-xl p-6 flex flex-col md:flex-row gap-6 items-stretch mb-6">
                        <div class="w-full md:w-5/12 flex flex-col justify-center">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fa-solid fa-truck text-lg text-black"></i>
                                <h3 class="font-bold text-black text-lg">Persiapan Penjualan ke BSI</h3>
                            </div>
                            <div class="mb-2">
                                <span class="text-3xl font-extrabold text-black">85 Kg</span>
                                <span class="text-3xl font-bold text-gray-400"> / 100 Kg</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-5 mb-2 border border-gray-300">
                                <div class="bg-[#81C784] h-full rounded-full" style="width: 85%"></div>
                            </div>
                            <p class="text-[11px] text-gray-600 font-medium">Masih membutuhkan 15 Kg untuk mencapai batas minimum pengangkutan</p>
                        </div>

                        <div class="w-full md:w-7/12 bg-white border border-[#F3C78B] rounded-xl p-4 flex flex-col">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-bold text-black text-sm">Rincian stok saat ini</h4>
                                <h4 class="font-bold text-black text-sm">Total : 85 Kg</h4>
                            </div>
                            <div class="grid grid-cols-5 gap-2 flex-1">
                                <div class="bg-[#D3D4FA] rounded-md p-2 flex flex-col items-center justify-center text-center">
                                    <span class="text-xs text-gray-700 font-semibold mb-1">Plastik</span>
                                    <span class="text-sm font-extrabold text-black">32,5 Kg</span>
                                </div>
                                <div class="bg-[#D3EFE4] rounded-md p-2 flex flex-col items-center justify-center text-center">
                                    <span class="text-xs text-gray-700 font-semibold mb-1">Kertas</span>
                                    <span class="text-sm font-extrabold text-black">18 Kg</span>
                                </div>
                                <div class="bg-[#FCE6E6] rounded-md p-2 flex flex-col items-center justify-center text-center">
                                    <span class="text-xs text-gray-700 font-semibold mb-1">Logam</span>
                                    <span class="text-sm font-extrabold text-black">20 Kg</span>
                                </div>
                                <div class="bg-[#D3F0F4] rounded-md p-2 flex flex-col items-center justify-center text-center">
                                    <span class="text-xs text-gray-700 font-semibold mb-1">Kaca</span>
                                    <span class="text-sm font-extrabold text-black">10 Kg</span>
                                </div>
                                <div class="bg-[#FDF1D9] rounded-md p-2 flex flex-col items-center justify-center text-center">
                                    <span class="text-xs text-gray-700 font-semibold mb-1">Lainnya</span>
                                    <span class="text-sm font-extrabold text-black">4,5 Kg</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU RIWAYAT PENJUALAN -->
                    <div class="border-2 border-gray-200 rounded-xl p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                            <div>
                                <h2 class="text-xl font-bold text-black">Riwayat Penjualan ke BSI</h2>
                                <p class="text-gray-500 text-xs">Daftar pengangkutan dan detail sampah yang dijual ke Bank Sampah Induk (BSI)</p>
                            </div>

                            <div class="flex items-center gap-3 mt-4 md:mt-0">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                                    </span>
                                    <input type="text" id="searchInput" onkeyup="filterPenjualan()" placeholder="Cari nama nasabah disini"
                                        class="w-64 pl-9 pr-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-400 text-xs">
                                </div>
                                <button onclick="openModalCatat()" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md shadow-sm transition-colors text-xs flex items-center gap-2">
                                    <i class="fa-solid fa-plus"></i> Catat Penjualan
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table id="penjualanTable" class="w-full text-center text-sm border-collapse">
                                <thead>
                                    <tr class="bg-[#A0C4C4] border-b border-gray-300">
                                        <th class="py-3 px-2 text-black font-bold w-12">No</th>
                                        <th class="py-3 px-4 text-black font-bold">No Transaksi</th>
                                        <th class="py-3 px-4 text-black font-bold">Tanggal Pengangkutan</th>
                                        <th class="py-3 px-4 text-black font-bold">Total Berat (Kg)</th>
                                        <th class="py-3 px-4 text-black font-bold">Total Nilai (Rp)</th>
                                        <th class="py-3 px-4 text-black font-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Menambahkan event onClick openDetail(this) dan openHapus(this) -->
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 penjualan-row">
                                        <td class="py-3 px-2">1</td>
                                        <td class="py-3 px-4 no-transaksi">T00001</td>
                                        <td class="py-3 px-4 td-tanggal">28/07/2026</td>
                                        <td class="py-3 px-4 td-berat">125,5</td>
                                        <td class="py-3 px-4 td-nilai">400.000</td>
                                        <td class="py-3 px-4 flex justify-center gap-2">
                                            <button onclick="openDetail(this)" class="bg-[#5FA8DC] text-white text-xs font-bold py-1.5 px-4 rounded">Detail</button>
                                            <button onclick="openHapus(this)" class="bg-[#F86363] text-white text-xs font-bold py-1.5 px-4 rounded">Hapus</button>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 penjualan-row">
                                        <td class="py-3 px-2">2</td>
                                        <td class="py-3 px-4 no-transaksi">T00002</td>
                                        <td class="py-3 px-4 td-tanggal">05/08/2026</td>
                                        <td class="py-3 px-4 td-berat">110</td>
                                        <td class="py-3 px-4 td-nilai">340.000</td>
                                        <td class="py-3 px-4 flex justify-center gap-2">
                                            <button onclick="openDetail(this)" class="bg-[#5FA8DC] text-white text-xs font-bold py-1.5 px-4 rounded">Detail</button>
                                            <button onclick="openHapus(this)" class="bg-[#F86363] text-white text-xs font-bold py-1.5 px-4 rounded">Hapus</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!-- END MAIN VIEW -->

            <!-- ============================================== -->
            <!-- VIEW 2: HALAMAN FORM DETAIL PENJUALAN (Hidden by default) -->
            <!-- ============================================== -->
            <div id="detailView" class="hidden max-w-[850px] w-full mx-auto mt-4">
                <h1 class="text-[34px] font-extrabold text-black mb-6">Detail Penjualan</h1>

                <div class="bg-white rounded-xl shadow-md border border-gray-300 w-full mb-10 overflow-hidden">
                    <form class="space-y-0">
                        <!-- No Transaksi -->
                        <div class="flex items-center px-10 py-6">
                            <label class="w-1/3 text-gray-700 font-medium">No Transaksi</label>
                            <div class="w-2/3">
                                <input type="text" id="detailNoTransaksi" readonly class="border border-gray-200 bg-gray-50 text-gray-400 rounded-md px-4 py-2.5 w-full max-w-sm focus:outline-none cursor-not-allowed">
                            </div>
                        </div>

                        <!-- Tanggal Pengangkutan -->
                        <div class="flex items-center border-t border-gray-200 px-10 py-6">
                            <label class="w-1/3 text-gray-700 font-medium">Tanggal Pengangkutan</label>
                            <div class="w-2/3">
                                <input type="text" id="detailTanggal" class="border border-gray-300 rounded-md px-4 py-2.5 w-full max-w-sm focus:outline-none focus:border-[#5C8A8A]">
                            </div>
                        </div>

                        <!-- Berat -->
                        <div class="flex items-center border-t border-gray-200 px-10 py-6">
                            <label class="w-1/3 text-gray-700 font-medium">Berat</label>
                            <div class="w-2/3 flex items-center gap-3">
                                <input type="text" id="detailBerat" class="border border-gray-300 rounded-md px-4 py-2.5 w-32 focus:outline-none focus:border-[#5C8A8A]">
                                <span class="text-gray-700 font-medium">Kg</span>
                            </div>
                        </div>

                        <!-- Total Nilai -->
                        <div class="flex items-center border-t border-b border-gray-200 px-10 py-6">
                            <label class="w-1/3 text-gray-700 font-medium">Total Nilai</label>
                            <div class="w-2/3 flex items-center gap-3">
                                <span class="text-gray-700 font-medium">Rp</span>
                                <input type="text" id="detailNilai" class="border border-gray-300 rounded-md px-4 py-2.5 w-full max-w-[290px] focus:outline-none focus:border-[#5C8A8A]">
                            </div>
                        </div>
                    </form>

                    <!-- Tombol Action -->
                    <div class="flex justify-center gap-6 mt-8 mb-10">
                        <button type="button" onclick="simpanDetailTabel()" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-3 px-12 rounded-lg shadow-sm transition-colors text-lg">
                            Simpan
                        </button>
                        <button type="button" onclick="batalDetail()" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-3 px-12 rounded-lg shadow-sm transition-colors text-lg">
                            Batal
                        </button>
                    </div>
                </div>
            </div> <!-- END DETAIL VIEW -->

        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL POP UP - CATAT PENJUALAN -->
    <!-- ============================================== -->
    <div id="modalCatat" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60 transition-opacity" onclick="closeModalCatat()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden z-10">
            <div class="h-12 bg-[#5C8A8A]"></div>
            <div class="px-12 py-10">
                <h2 class="text-3xl font-extrabold text-center text-black mb-10">Catat Penjualan Manual</h2>
                <form class="space-y-0">
                    <div class="flex items-center border-t border-gray-200 py-6">
                        <label class="w-1/2 text-gray-700 font-medium">Tanggal Pengangkutan</label>
                        <div class="w-1/2 flex h-11">
                            <div class="bg-gray-200 border border-gray-300 border-r-0 rounded-l-md px-4 flex items-center justify-center">
                                <i class="fa-regular fa-calendar text-gray-600 text-lg"></i>
                            </div>
                            <input type="date" id="inputTanggal" class="w-full border border-gray-300 rounded-r-md px-3 text-gray-700 focus:outline-none focus:border-[#5C8A8A]">
                        </div>
                    </div>
                    <div class="flex items-center border-t border-gray-200 py-6">
                        <label class="w-1/2 text-gray-700 font-medium">Total Berat</label>
                        <div class="w-1/2 flex items-center gap-3">
                            <input type="number" id="inputBerat" class="w-32 border border-gray-300 rounded-md px-3 py-2 h-11 focus:outline-none focus:border-[#5C8A8A]">
                            <span class="text-gray-700 font-medium">Kg</span>
                        </div>
                    </div>
                    <div class="flex items-center border-t border-b border-gray-200 py-6">
                        <label class="w-1/2 text-gray-700 font-medium">Total Nilai</label>
                        <div class="w-1/2 flex items-center gap-3">
                            <span class="text-gray-700 font-medium">Rp</span>
                            <input type="text" id="inputNilai" class="w-full border border-gray-300 rounded-md px-3 py-2 h-11 focus:outline-none focus:border-[#5C8A8A]">
                        </div>
                    </div>
                </form>
                <div class="flex justify-center gap-6 mt-10">
                    <button type="button" onclick="simpanPenjualanKeTabel()" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-3 px-10 rounded-lg shadow-md transition-colors text-lg">
                        Simpan
                    </button>
                    <button type="button" onclick="closeModalCatat()" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-3 px-10 rounded-lg shadow-md transition-colors text-lg">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL POP UP - KONFIRMASI HAPUS -->
    <!-- ============================================== -->
    <div id="modalHapus" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60 transition-opacity" onclick="closeHapus()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden z-10 pt-10 pb-8 px-8 text-center">

            <!-- Ikon Lingkaran Merah Tanda Seru -->
            <div class="w-24 h-24 rounded-full border-4 border-[#FF6B6B] flex items-center justify-center mx-auto mb-6">
                <span class="text-6xl text-[#FF6B6B] font-bold">!</span>
            </div>

            <h2 class="text-[28px] font-bold text-[#FF6B6B] mb-2">Konfirmasi Hapus</h2>
            <p class="text-gray-700 text-[15px] mb-10">Apakah anda yakin ingin menghapus transaksi "<span id="hapusNoTransaksi" class="font-bold"></span>"?</p>

            <div class="flex justify-center gap-4">
                <button type="button" onclick="konfirmasiHapusData()" class="bg-[#5FA8DC] hover:bg-blue-600 text-white font-bold py-2.5 px-12 rounded-lg shadow-sm transition-colors text-lg">
                    Ya
                </button>
                <button type="button" onclick="closeHapus()" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-2.5 px-12 rounded-lg shadow-sm transition-colors text-lg">
                    Tidak
                </button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT UNTUK SEARCH, TAMBAH, DETAIL, DAN HAPUS -->
    <script>
        let activeRow = null; // Menyimpan baris (tr) yang sedang dipilih

        // --- FITUR SEARCH ---
        function filterPenjualan() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let table = document.getElementById("penjualanTable");
            let tr = table.getElementsByClassName("penjualan-row");

            for (let i = 0; i < tr.length; i++) {
                let td = tr[i].getElementsByClassName("no-transaksi")[0];
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

        // --- FITUR TAMBAH PENJUALAN ---
        function openModalCatat() { document.getElementById('modalCatat').classList.remove('hidden'); }
        function closeModalCatat() {
            document.getElementById('modalCatat').classList.add('hidden');
            document.getElementById('inputTanggal').value = '';
            document.getElementById('inputBerat').value = '';
            document.getElementById('inputNilai').value = '';
        }

        function simpanPenjualanKeTabel() {
            const rawTanggal = document.getElementById('inputTanggal').value;
            const rawBerat = document.getElementById('inputBerat').value;
            const rawNilai = document.getElementById('inputNilai').value;

            if (!rawTanggal || !rawBerat || !rawNilai) {
                alert('Silakan isi seluruh kolom terlebih dahulu!');
                return;
            }

            const tbody = document.querySelector('#penjualanTable tbody');
            const barisSekarang = tbody.querySelectorAll('tr.penjualan-row').length;
            const noBaru = barisSekarang + 1;
            const noTransaksiBaru = "T" + String(noBaru).padStart(5, '0');

            const pecahTanggal = rawTanggal.split('-');
            const tanggalFormat = pecahTanggal[2] + '/' + pecahTanggal[1] + '/' + pecahTanggal[0];
            const nilaiFormat = Number(rawNilai.replace(/[^0-9]/g, '')).toLocaleString('id-ID');
            const beratFormat = rawBerat.replace('.', ',');

            const trBaru = document.createElement('tr');
            trBaru.className = 'border-b border-gray-200 hover:bg-gray-50 penjualan-row';

            // Perhatikan bahwa event onclick openDetail dan openHapus juga disertakan pada data baru
            trBaru.innerHTML = `
                <td class="py-3 px-2">${noBaru}</td>
                <td class="py-3 px-4 no-transaksi">${noTransaksiBaru}</td>
                <td class="py-3 px-4 td-tanggal">${tanggalFormat}</td>
                <td class="py-3 px-4 td-berat">${beratFormat}</td>
                <td class="py-3 px-4 td-nilai">${nilaiFormat}</td>
                <td class="py-3 px-4 flex justify-center gap-2">
                    <button onclick="openDetail(this)" class="bg-[#5FA8DC] text-white text-xs font-bold py-1.5 px-4 rounded">Detail</button>
                    <button onclick="openHapus(this)" class="bg-[#F86363] text-white text-xs font-bold py-1.5 px-4 rounded">Hapus</button>
                </td>
            `;

            tbody.appendChild(trBaru);
            closeModalCatat();
        }

        // --- FITUR DETAIL / UBAH DATA ---
        function openDetail(btn) {
            // Ambil tr terdekat dari tombol yang diklik
            activeRow = btn.closest('tr');

            // Ambil teks dari kolom yang bersangkutan
            let noTrans = activeRow.querySelector('.no-transaksi').innerText;
            let tanggal = activeRow.querySelector('.td-tanggal').innerText;
            let berat = activeRow.querySelector('.td-berat').innerText;
            let nilai = activeRow.querySelector('.td-nilai').innerText;

            // Masukkan ke dalam input form Detail Penjualan
            document.getElementById('detailNoTransaksi').value = noTrans;
            document.getElementById('detailTanggal').value = tanggal;
            document.getElementById('detailBerat').value = berat;
            document.getElementById('detailNilai').value = nilai.replace(/\./g, ''); // Hapus titik format uang

            // Ganti Tampilan
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailView').classList.remove('hidden');
        }

        function batalDetail() {
            document.getElementById('detailView').classList.add('hidden');
            document.getElementById('mainView').classList.remove('hidden');
        }

        function simpanDetailTabel() {
            // Masukkan nilai inputan baru kembali ke tabel
            activeRow.querySelector('.td-tanggal').innerText = document.getElementById('detailTanggal').value;
            activeRow.querySelector('.td-berat').innerText = document.getElementById('detailBerat').value;

            let nilaiBaru = document.getElementById('detailNilai').value;
            activeRow.querySelector('.td-nilai').innerText = Number(nilaiBaru).toLocaleString('id-ID'); // Format ulang jadi Rp.

            batalDetail(); // Kembali ke halaman utama
        }

        // --- FITUR HAPUS DATA ---
        function openHapus(btn) {
            activeRow = btn.closest('tr');
            let noTrans = activeRow.querySelector('.no-transaksi').innerText;

            // Ubah teks di modal agar sesuai transaksi yang diklik
            document.getElementById('hapusNoTransaksi').innerText = noTrans;
            document.getElementById('modalHapus').classList.remove('hidden');
        }

        function closeHapus() {
            document.getElementById('modalHapus').classList.add('hidden');
        }

        function konfirmasiHapusData() {
            if(activeRow) {
                activeRow.remove(); // Hapus baris tabel tersebut
            }
            closeHapus(); // Tutup modal
        }
    </script>
</body>
</html>
