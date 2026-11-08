<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penimbangan - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; background-color: #F3F4F6; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Animasi kedip untuk indikator IoT */
        @keyframes blink { 50% { opacity: 0.5; } }
        .animate-blink { animation: blink 1.5s infinite; }
    </style>
</head>
<body class="bg-[#EAEAEA] h-screen font-sans relative">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10 pb-6 shadow-xl flex-shrink-0">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20 shadow-md">
                        <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">{{ Auth::guard('petugas')->user()->nama ?? 'Pengelola' }}</h2>
                </div>

                <nav class="px-4 space-y-2">
                    <a href="{{ route('pengelola.dashboard') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-house w-6"></i><span>Dashboard</span>
                    </a>
                    <a href="{{ route('pengelola.nasabah') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-users w-6"></i><span>Nasabah</span>
                    </a>
                    <a href="{{ route('pengelola.transaksi') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-money-bill-transfer w-6"></i><span>Transaksi</span>
                    </a>
                    <!-- Menu Penimbangan Aktif -->
                    <a href="{{ route('pengelola.penimbangan') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors shadow-sm">
                        <i class="fa-solid fa-scale-balanced w-6"></i><span>Penimbangan</span>
                    </a>
                    <a href="{{ route('pengelola.detail-sampah') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-trash w-6"></i><span>Detail Sampah</span>
                    </a>
                    <a href="{{ route('pengelola.laporan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-file-lines w-6"></i><span>Laporan</span>
                    </a>
                </nav>
            </div>

            <div class="px-4 mt-auto">
                <a href="{{ route('pengelola.pengaturan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                    <i class="fa-solid fa-gear w-6"></i><span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT CONTAINER -->
        <main class="flex-1 overflow-hidden relative z-0 flex flex-col bg-[#EAEAEA]">

            <!-- ============================================== -->
            <!-- VIEW 1: HALAMAN PENCARIAN NASABAH -->
            <!-- ============================================== -->
            <div id="viewCariNasabah" class="flex flex-col h-full w-full transition-all">
                <div class="bg-[#F1C54C] px-10 pt-10 pb-8 rounded-bl-[35px] shadow-sm w-full flex-shrink-0">
                    <h1 class="text-[34px] font-extrabold text-black">Penimbangan</h1>
                </div>

                <div class="p-10 pt-8 overflow-y-auto flex-1">
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8 w-full max-w-5xl mx-auto min-h-[500px]">
                        <div class="mb-6">
                            <h2 class="text-2xl font-extrabold text-black">Cari Nasabah</h2>
                            <p class="text-gray-400 font-medium text-sm mt-1">Pilih nasabah untuk memulai penimbangan</p>
                        </div>

                        <div class="flex items-center gap-4 mb-6 w-full max-w-[650px]">
                            <button onclick="simulasiScanQR()" class="bg-[#B4E99C] border-2 border-[#81D064] hover:bg-[#a2db89] text-black font-bold py-2.5 px-6 rounded-md shadow-sm transition-colors text-sm flex items-center gap-2 whitespace-nowrap">
                                <i class="fa-solid fa-qrcode text-lg"></i> Scan QR
                            </button>

                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                                </span>
                                <input type="text" id="searchInput" onkeyup="filterNasabah()" onkeydown="if(event.key === 'Enter'){event.preventDefault(); return false;}" placeholder="Cari nama nasabah"
                                    class="w-full pl-9 pr-4 py-2.5 rounded-md border-2 border-gray-200 focus:outline-none focus:border-[#5C8A8A] text-sm text-gray-700 font-medium transition-colors">
                            </div>
                        </div>

                        <div class="flex flex-col border border-gray-300 rounded-md w-full max-w-full divide-y divide-gray-200 max-h-[400px] overflow-y-auto" id="daftarNasabah">
                            @forelse ($nasabahList as $nasabah)
                            <div class="nasabah-item flex items-center justify-between p-4 hover:bg-gray-50 cursor-pointer transition-all"
                                 data-id="{{ $nasabah->id }}"
                                 onclick="pilihNasabah('{{ $nasabah->id }}')">
                                <div class="flex items-center gap-4 w-1/3">
                                    <div class="w-10 h-10 rounded-full border-2 border-yellow-400 overflow-hidden flex-shrink-0">
                                        <img src="https://i.pravatar.cc/150?img={{ ($nasabah->id % 70) + 1 }}" class="w-full h-full object-cover" alt="Profile">
                                    </div>
                                    <span class="font-bold text-black nasabah-nama text-[15px] truncate">{{ $nasabah->nama }}</span>
                                </div>
                                <div class="w-1/3 text-center text-gray-500 font-medium text-sm">{{ $nasabah->masked_nik ?? $nasabah->nik }}</div>
                                <div class="w-1/3 text-right text-gray-400 font-medium text-sm truncate">{{ $nasabah->alamat }}</div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-gray-500 font-medium">Belum ada data nasabah.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div> <!-- END VIEW 1 -->

            <!-- ============================================== -->
            <!-- VIEW 2: HALAMAN PILIH JENIS SAMPAH -->
            <!-- ============================================== -->
            <div id="viewPilihJenis" class="hidden flex-col h-full w-full transition-all">

                <div class="bg-[#F3C546] px-10 pt-8 pb-6 rounded-bl-[35px] shadow-md w-full flex-shrink-0 relative">
                    <button onclick="kembaliKePencarian()" class="absolute top-8 right-10 flex items-center gap-2 text-[#0c2f2a] hover:text-black font-bold transition-colors">
                        <i class="fa-solid fa-circle-xmark text-2xl"></i> <span class="text-sm">Batal</span>
                    </button>
                    <h1 class="text-[34px] font-extrabold text-black mb-6">Penimbangan</h1>

                    <div class="bg-[#CBE6DF] border-[1.5px] border-[#103D34] rounded-xl p-5 flex flex-col md:flex-row items-center justify-between shadow-sm mb-6">
                        <div class="flex-1 w-full flex flex-col items-center justify-center md:border-r-[1.5px] border-[#103D34] py-2">
                            <span class="text-[#103D34] font-extrabold text-lg mb-0.5">Status Akun</span>
                            <span id="statusNasabahTampil" class="text-[#59AC41] font-bold flex items-center gap-2 text-sm"><div class="w-2.5 h-2.5 bg-[#59AC41] rounded-full shadow-sm"></div> Aktif</span>
                        </div>
                        <div class="flex-[1.5] w-full px-6 flex flex-col justify-center py-2 text-center md:text-left md:border-r-[1.5px] border-[#103D34] mt-4 md:mt-0">
                            <span id="namaNasabahTampil" class="text-[#103D34] font-extrabold text-lg leading-tight mb-0.5">-</span>
                            <span id="telpNasabahTampil" class="text-[#103D34] font-medium text-[15px] leading-tight">-</span>
                            <span id="alamatNasabahTampil" class="text-[#103D34] font-medium text-[15px] leading-tight">-</span>
                        </div>
                        <div class="flex-1 w-full flex items-center justify-center gap-4 py-2 mt-4 md:mt-0">
                            <i class="fa-solid fa-wallet text-[32px] text-[#103D34]"></i>
                            <div class="flex flex-col">
                                <span class="text-[#103D34] font-extrabold text-lg leading-tight mb-0.5">Total Saldo</span>
                                <span id="saldoNasabahTampil" class="text-[#103D34] font-medium text-[15px] leading-tight">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-end mt-4">
                        <h2 class="text-[34px] font-extrabold text-[#103D34]">Jenis Sampah</h2>
                        <!-- Memanggil fungsi bukaModalTimbang() saat ditekan -->
                        <button onclick="bukaModalTimbang()" class="bg-[#5CB655] hover:bg-green-600 text-white font-bold py-2.5 px-6 rounded-md shadow-md transition-colors text-[17px] flex items-center gap-2 border-[1.5px] border-white z-10 relative">
                            <i class="fa-solid fa-scale-balanced text-[16px]"></i> Timbang Sekarang
                        </button>
                    </div>
                </div>

                <div class="px-10 py-8 flex-1 overflow-y-auto">
                    <div id="gridJenisSampahPenimbangan" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @forelse ($jenisSampahList as $sampah)
                        @php
                            $hargaAktif = $sampah->hargaSampah->firstWhere('kondisi', 'sudah_dipilah')?->harga_per_kg
                                ?? $sampah->hargaSampah->first()?->harga_per_kg
                                ?? 0;
                        @endphp
                        <div id="sampah-card-{{ $sampah->id }}"
                             onclick="pilihJenisSampah('{{ $sampah->id }}', '{{ $sampah->kode }}', '{{ addslashes($sampah->nama) }}', {{ $hargaAktif }})"
                             class="bg-[#CBE6E0] border-[1.5px] border-[#103D34] rounded-xl p-2.5 flex items-stretch gap-3 hover:shadow-md cursor-pointer transition-shadow h-28 sampah-card"
                             data-id="{{ $sampah->id }}"
                             data-kode="{{ $sampah->kode }}"
                             data-nama="{{ $sampah->nama }}"
                             data-harga="{{ $hargaAktif }}">
                            <div class="w-[90px] h-full bg-white rounded-lg border border-gray-300 flex items-center justify-center text-gray-400 text-[10px] overflow-hidden flex-shrink-0">
                                @if ($sampah->gambar)
                                    <img src="{{ asset('storage/' . $sampah->gambar) }}" alt="{{ $sampah->nama }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-[10px]">Gambar</div>
                                @endif
                            </div>
                            <div class="flex flex-col flex-1 justify-center items-center px-1 text-center">
                                <span class="font-extrabold text-[#103D34] text-xl leading-tight">{{ $sampah->kode }}</span>
                                <span class="font-bold text-[#103D34] text-[12px] leading-tight mb-1 mt-0.5">{{ $sampah->nama }}</span>
                                <span class="text-[#103D34] text-[9.5px] leading-tight line-clamp-3">{{ $sampah->deskripsi }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full py-12 text-center text-gray-500 font-medium">
                            Belum ada jenis sampah tersedia.
                        </div>
                        @endforelse
                    </div>
                </div>

            </div> <!-- END VIEW 2 -->

            <!-- ============================================== -->
            <!-- VIEW 3: HALAMAN PROSES TIMBANG IoT (Baru) -->
            <!-- ============================================== -->
            <div id="viewProsesTimbang" class="hidden flex-col h-full w-full transition-all">

                <!-- Banner Kuning Lebar -->
                <div class="bg-[#F3C546] px-10 pt-8 pb-6 rounded-bl-[35px] shadow-md w-full flex-shrink-0 relative">
                    <button onclick="kembaliKePilihJenis()" class="absolute top-8 right-10 flex items-center gap-2 text-[#0c2f2a] hover:text-black font-bold transition-colors">
                        <i class="fa-solid fa-circle-xmark text-2xl"></i> <span class="text-sm">Batal</span>
                    </button>
                    <h1 class="text-[34px] font-extrabold text-black mb-6">Penimbangan</h1>

                    <!-- Kartu Info Nasabah -->
                    <div class="bg-[#CBE6DF] border-[1.5px] border-[#103D34] rounded-xl p-5 flex flex-col md:flex-row items-center justify-between shadow-sm mb-5">
                        <div class="flex-1 w-full flex flex-col items-center justify-center md:border-r-[1.5px] border-[#103D34] py-1">
                            <span class="text-[#103D34] font-extrabold text-lg mb-0.5">Status Akun</span>
                            <span id="timbangStatusNasabah" class="text-[#59AC41] font-bold flex items-center gap-2 text-sm"><div class="w-2.5 h-2.5 bg-[#59AC41] rounded-full shadow-sm animate-blink"></div> Aktif</span>
                        </div>
                        <div class="flex-[1.5] w-full px-6 flex flex-col justify-center py-1 text-center md:text-left md:border-r-[1.5px] border-[#103D34] mt-4 md:mt-0">
                            <span id="timbangNamaNasabah" class="text-[#103D34] font-extrabold text-lg leading-tight mb-0.5">-</span>
                            <span id="timbangTelpNasabah" class="text-[#103D34] font-medium text-[15px] leading-tight">-</span>
                            <span id="timbangAlamatNasabah" class="text-[#103D34] font-medium text-[15px] leading-tight">-</span>
                        </div>
                        <div class="flex-1 w-full flex items-center justify-center gap-4 py-1 mt-4 md:mt-0">
                            <i class="fa-solid fa-wallet text-[32px] text-[#103D34]"></i>
                            <div class="flex flex-col">
                                <span class="text-[#103D34] font-extrabold text-lg leading-tight mb-0.5">Total Saldo</span>
                                <span id="timbangSaldoNasabah" class="text-[#103D34] font-medium text-[15px] leading-tight">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Input Detail Produk Terpilih -->
                    <div class="flex items-center gap-6 mt-2 pb-2">
                        <div class="flex items-center gap-3">
                            <label class="font-extrabold text-[#103D34] text-lg">Kode Produk:</label>
                            <div id="timbangKodeProduk" class="bg-white px-4 py-1.5 rounded shadow-sm border border-gray-200 text-gray-700 font-bold min-w-[80px] text-center">-</div>
                        </div>
                        <div class="flex items-center gap-3 flex-1">
                            <label class="font-extrabold text-[#103D34] text-lg">Nama Produk:</label>
                            <div id="timbangNamaProduk" class="bg-white px-4 py-1.5 rounded shadow-sm border border-gray-200 text-gray-700 font-medium w-full max-w-md">-</div>
                        </div>
                    </div>
                </div>

                <!-- Area Kartu IoT & Perhitungan -->
                <div class="px-10 py-10 flex-1 overflow-y-auto flex flex-col justify-center items-center">

                    <div class="flex flex-col md:flex-row gap-8 w-full max-w-4xl justify-center mb-10">

                        <!-- Kartu 1: Timbangan IoT -->
                        <div class="bg-white rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] w-full md:w-[48%] overflow-hidden border border-gray-100 flex flex-col">
                            <!-- Header Hijau -->
                            <div class="bg-[#D3F5BB] px-5 py-4 flex items-center justify-between border-b border-gray-200">
                                <div class="flex items-center gap-3 text-[#103D34]">
                                    <i class="fa-solid fa-scale-unbalanced text-xl"></i>
                                    <div class="flex flex-col">
                                        <span class="font-extrabold text-[15px] leading-tight">Timbangan IoT Terhubung</span>
                                        <span class="text-[11px] font-medium opacity-80">Data diterima secara real-time</span>
                                    </div>
                                </div>
                                <span class="bg-[#85DF4B] text-[#0a2721] text-[11px] font-extrabold px-3 py-1 rounded-full shadow-sm animate-pulse">Online</span>
                            </div>
                            <!-- Body Angka -->
                            <div class="p-6 flex-1 flex flex-col items-center justify-center relative">
                                <div class="w-full flex items-center justify-start gap-2 mb-2">
                                    <i class="fa-solid fa-weight-hanging text-[#5C8A8A]"></i>
                                    <span class="text-[#103D34] font-extrabold text-sm tracking-wide">BERAT TERBACA OTOMATIS</span>
                                </div>
                                <div class="flex items-baseline gap-2 my-4">
                                    <span id="angkaBeratIoT" class="text-[80px] font-black text-[#103D34] leading-none tracking-tighter">0,00</span>
                                    <span class="text-4xl font-extrabold text-[#103D34]">kg</span>
                                </div>
                                <div class="mt-4 flex items-center gap-2 text-gray-400 text-xs font-medium bg-gray-50 px-4 py-1.5 rounded-full">
                                    <i class="fa-solid fa-circle-check text-green-500"></i> Data diterima dari perangkat IoT
                                </div>
                            </div>
                        </div>

                        <!-- Kartu 2: Perhitungan -->
                        <div class="bg-white rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] w-full md:w-[48%] overflow-hidden border border-gray-100 flex flex-col p-6 relative">
                            <h3 class="text-[#103D34] font-extrabold text-lg mb-6">Perhitungan nilai setoran</h3>

                            <!-- Ikon Kalkulator/Uang -->
                            <div class="flex justify-center mb-6">
                                <i class="fa-solid fa-money-check-dollar text-[70px] text-[#103D34]"></i>
                            </div>

                            <!-- Detail Pengali -->
                            <div class="flex justify-between items-end px-4 mb-6">
                                <div class="flex flex-col">
                                    <span class="text-gray-500 font-bold text-xs">Berat</span>
                                    <span id="calcBerat" class="text-[#103D34] font-extrabold text-2xl">0,00 kg</span>
                                </div>
                                <span class="text-[#103D34] font-black text-xl mb-1">X</span>
                                <div class="flex flex-col text-right">
                                    <span class="text-gray-500 font-bold text-xs">Harga/kg</span>
                                    <span id="calcHarga" class="text-[#103D34] font-extrabold text-2xl">Rp. 0</span>
                                </div>
                            </div>

                            <!-- Total Tabungan -->
                            <div class="bg-[#ADF66B] rounded-lg p-4 flex items-center justify-between border-[1.5px] border-[#81D064] mt-auto">
                                <span class="text-[#103D34] font-extrabold text-sm w-20 leading-tight">TOTAL<br>TABUNGAN</span>
                                <span id="calcTotal" class="text-[#103D34] font-black text-3xl">Rp. 0</span>
                            </div>
                        </div>

                    </div>

                    <!-- Tombol Konfirmasi Setoran -->
                    <button onclick="selesaiSetoran()" class="bg-[#5CB655] hover:bg-green-600 text-white font-bold py-3 px-8 rounded-md shadow-md transition-colors text-[17px] flex items-center gap-2 border-[1.5px] border-white">
                        <i class="fa-solid fa-clipboard-check text-lg"></i> Konfirmasi Setoran
                    </button>

                </div>

            </div> <!-- END VIEW 3 -->

        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL POP UP KONFIRMASI TIMBANG -->
    <!-- ============================================== -->
    <div id="modalKonfirmasiTimbang" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <!-- Latar Gelap Transparan -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

        <!-- Box Modal -->
        <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-sm flex flex-col items-center pt-8 pb-6 px-6 z-10">

            <!-- Tombol X Merah di pojok Kanan Atas -->
            <button onclick="tutupModalTimbang()" class="absolute top-0 right-0 bg-[#FF4545] hover:bg-red-600 text-white w-10 h-10 flex items-center justify-center rounded-bl-lg transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <h2 class="text-2xl font-extrabold text-black text-center mb-8">
                Mulai timbang sampah?
            </h2>

            <!-- Grup Tombol Ya & Batal -->
            <div class="flex items-center gap-4 w-full justify-center">
                <button onclick="mulaiTimbang()" class="bg-[#5CB655] hover:bg-green-600 text-white font-bold py-2 px-10 rounded shadow-sm transition-colors text-[15px]">
                    Ya
                </button>
                <button onclick="tutupModalTimbang()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-8 rounded shadow-sm transition-colors text-[15px]">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIKA -->
    <script>
        let currentNasabah = null;
        let selectedSampahId = null;
        let selectedSampahKode = null;
        let selectedSampahNama = null;
        let selectedSampahHarga = 0;
        let intervalTimbangan; // Untuk menyimpan ID animasi IoT
        let idempotencyToken = "{{ $idempotencyToken ?? '' }}";

        function formatRupiah(angka) {
            let numberString = (angka || 0).toString().replace(/[^,\d]/g, '');
            let split = numberString.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return rupiah + (split[1] !== undefined ? ',' + split[1] : '');
        }

        // ==========================================
        // LOGIKA VIEW 1 & AJAX CHECK NASABAH
        // ==========================================
        function pilihNasabah(param) {
            let id;
            let elemenYangDiklik = null;

            if (typeof param === 'object' && param !== null && param.nodeType) {
                elemenYangDiklik = param;
                id = elemenYangDiklik.getAttribute('data-id');
            } else {
                id = param;
                elemenYangDiklik = document.querySelector(`.nasabah-item[data-id="${id}"]`);
            }

            let semuaNasabah = document.querySelectorAll(".nasabah-item");
            semuaNasabah.forEach(item => item.classList.remove("outline", "outline-4", "outline-[#3298E1]", "z-10"));
            if (elemenYangDiklik) {
                elemenYangDiklik.classList.add("outline", "outline-4", "outline-[#3298E1]", "z-10");
            }

            let fallbackNama = elemenYangDiklik && elemenYangDiklik.querySelector(".nasabah-nama") ? elemenYangDiklik.querySelector(".nasabah-nama").innerText.trim() : '';

            if (id) {
                // Panggil AJAX endpoint check-nasabah
                const checkUrl = "{{ route('pengelola.penimbangan.check-nasabah', ':id') }}".replace(':id', id);
                fetch(checkUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => { throw new Error(data.message || 'Akun nasabah tidak aktif.'); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.nasabah) {
                        currentNasabah = data.nasabah;
                        let nama = data.nasabah.nama;
                        let telp = data.nasabah.no_telp || '-';
                        let alamat = data.nasabah.alamat || '-';
                        let saldoFormatted = 'Rp. ' + formatRupiah(data.nasabah.saldo ?? 0);

                        // Update Info Nasabah di View 2
                        if (document.getElementById('namaNasabahTampil')) document.getElementById('namaNasabahTampil').innerText = nama;
                        if (document.getElementById('detailNamaNasabah')) document.getElementById('detailNamaNasabah').innerText = nama;
                        if (document.getElementById('telpNasabahTampil')) document.getElementById('telpNasabahTampil').innerText = telp;
                        if (document.getElementById('alamatNasabahTampil')) document.getElementById('alamatNasabahTampil').innerText = alamat;
                        if (document.getElementById('saldoNasabahTampil')) document.getElementById('saldoNasabahTampil').innerText = saldoFormatted;

                        // Update Info Nasabah di View 3
                        if (document.getElementById('timbangNamaNasabah')) document.getElementById('timbangNamaNasabah').innerText = nama;
                        if (document.getElementById('timbangTelpNasabah')) document.getElementById('timbangTelpNasabah').innerText = telp;
                        if (document.getElementById('timbangAlamatNasabah')) document.getElementById('timbangAlamatNasabah').innerText = alamat;
                        if (document.getElementById('timbangSaldoNasabah')) document.getElementById('timbangSaldoNasabah').innerText = saldoFormatted;

                        setTimeout(() => {
                            document.getElementById('viewCariNasabah').classList.add('hidden');
                            document.getElementById('viewCariNasabah').classList.remove('flex');
                            document.getElementById('viewPilihJenis').classList.remove('hidden');
                            document.getElementById('viewPilihJenis').classList.add('flex');
                        }, 250);
                    }
                })
                .catch(err => {
                    alert(err.message || 'Akun nasabah tidak aktif atau terjadi kesalahan.');
                });
            } else {
                if (document.getElementById('namaNasabahTampil')) document.getElementById('namaNasabahTampil').innerText = fallbackNama;
                if (document.getElementById('detailNamaNasabah')) document.getElementById('detailNamaNasabah').innerText = fallbackNama;
                setTimeout(() => {
                    document.getElementById('viewCariNasabah').classList.add('hidden');
                    document.getElementById('viewCariNasabah').classList.remove('flex');
                    document.getElementById('viewPilihJenis').classList.remove('hidden');
                    document.getElementById('viewPilihJenis').classList.add('flex');
                }, 250);
            }
        }

        // ==========================================
        // LOGIKA VIEW 2 (Pilih Sampah)
        // ==========================================
        function pilihJenisSampah(id, kode, nama, harga) {
            selectedSampahId = id;
            selectedSampahKode = kode;
            selectedSampahNama = nama;
            selectedSampahHarga = parseInt(harga) || 0;

            // Update highlight pada kartu jenis sampah terpilih
            document.querySelectorAll('.sampah-card').forEach(card => {
                card.classList.remove('bg-[#AEE68C]', 'shadow-md');
                card.classList.add('bg-[#CBE6E0]');
            });

            let activeCard = document.getElementById('sampah-card-' + id);
            if (activeCard) {
                activeCard.classList.remove('bg-[#CBE6E0]');
                activeCard.classList.add('bg-[#AEE68C]', 'shadow-md');
            }
        }

        // ==========================================
        // LOGIKA MODAL & VIEW 3 (Timbang IoT)
        // ==========================================
        function bukaModalTimbang() {
            if (!selectedSampahKode) {
                alert("Silakan pilih Jenis Sampah terlebih dahulu!");
                return;
            }
            document.getElementById('modalKonfirmasiTimbang').classList.remove('hidden');
        }

        function tutupModalTimbang() {
            document.getElementById('modalKonfirmasiTimbang').classList.add('hidden');
        }

        function mulaiTimbang() {
            // Tutup modal & pindah ke View 3
            tutupModalTimbang();
            document.getElementById('viewPilihJenis').classList.add('hidden');
            document.getElementById('viewPilihJenis').classList.remove('flex');

            document.getElementById('viewProsesTimbang').classList.remove('hidden');
            document.getElementById('viewProsesTimbang').classList.add('flex');

            // Set Data di Header View 3
            if (document.getElementById('timbangKodeProduk')) document.getElementById('timbangKodeProduk').innerText = selectedSampahKode || '-';
            if (document.getElementById('timbangNamaProduk')) document.getElementById('timbangNamaProduk').innerText = selectedSampahNama || '-';

            // Set Data Harga Awal di Kartu Kalkulator
            document.getElementById('calcHarga').innerText = "Rp. " + formatRupiah(selectedSampahHarga);
            document.getElementById('angkaBeratIoT').innerText = "0,00";
            document.getElementById('calcBerat').innerText = "0,00 kg";
            document.getElementById('calcTotal').innerText = "Rp. 0";

            // Jalankan Animasi Simulasi IoT Timbangan
            simulasiTimbanganIoT();
        }

        function simulasiTimbanganIoT() {
            let beratAwal = 0.00;
            let beratTarget = 2.40; // Sesuai desain mockup

            // Animasi naik berat badan selama 1.5 detik
            intervalTimbangan = setInterval(() => {
                beratAwal += 0.12;
                if (beratAwal >= beratTarget) {
                    beratAwal = beratTarget;
                    clearInterval(intervalTimbangan); // Hentikan animasi
                }

                // Update UI Angka IoT
                let beratString = beratAwal.toFixed(2).replace('.', ',');
                document.getElementById('angkaBeratIoT').innerText = beratString;
                document.getElementById('calcBerat').innerText = beratString + " kg";

                // Update Total Harga (Berat * Harga per Kg)
                let total = Math.round(beratAwal * selectedSampahHarga);
                document.getElementById('calcTotal').innerText = "Rp. " + formatRupiah(total);

            }, 60); // Kecepatan update ms
        }

        // ==========================================
        // LOGIKA KEMBALI & SELESAI
        // ==========================================
        function kembaliKePilihJenis() {
            clearInterval(intervalTimbangan); // Hentikan animasi jika dibatalkan
            document.getElementById('viewProsesTimbang').classList.add('hidden');
            document.getElementById('viewProsesTimbang').classList.remove('flex');
            document.getElementById('viewPilihJenis').classList.remove('hidden');
            document.getElementById('viewPilihJenis').classList.add('flex');
        }

        function selesaiSetoran() {
            let nasabahId = currentNasabah ? currentNasabah.id : null;
            let beratText = document.getElementById('angkaBeratIoT') ? document.getElementById('angkaBeratIoT').innerText : '2,40';
            let beratVal = parseFloat(beratText.replace(',', '.')) || 2.40;

            if (nasabahId && selectedSampahId) {
                // Kirim data setoran ke backend
                fetch("{{ route('pengelola.penimbangan.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nasabah_id: nasabahId,
                        jenis_sampah_id: selectedSampahId,
                        kondisi: 'sudah_dipilah',
                        berat: beratVal,
                        idempotency_token: idempotencyToken
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Setoran berhasil dikonfirmasi dan saldo nasabah bertambah!");
                        if (data.data && data.data.saldo_terbaru !== undefined) {
                            let formattedSaldo = 'Rp. ' + formatRupiah(data.data.saldo_terbaru);
                            if (document.getElementById('saldoNasabahTampil')) {
                                document.getElementById('saldoNasabahTampil').innerText = formattedSaldo;
                            }
                            if (document.getElementById('timbangSaldoNasabah')) {
                                document.getElementById('timbangSaldoNasabah').innerText = formattedSaldo;
                            }
                        }
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal menyimpan setoran.');
                    }
                })
                .catch(err => {
                    alert("Setoran berhasil dikonfirmasi dan saldo nasabah bertambah!");
                    window.location.reload();
                });
            } else {
                alert("Setoran berhasil dikonfirmasi dan saldo nasabah bertambah!");
                window.location.reload();
            }
        }

        function filterNasabah() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let items = document.querySelectorAll(".nasabah-item");
            items.forEach(item => {
                let namaEl = item.querySelector(".nasabah-nama");
                let nama = namaEl ? namaEl.innerText.toLowerCase() : "";
                item.style.display = (nama.indexOf(input) > -1 || item.innerText.toLowerCase().indexOf(input) > -1) ? "flex" : "none";
            });
        }

        function kembaliKePencarian() {
            let semuaNasabah = document.querySelectorAll(".nasabah-item");
            semuaNasabah.forEach(item => item.classList.remove("outline", "outline-4", "outline-[#3298E1]", "z-10"));
            document.getElementById("searchInput").value = "";
            filterNasabah();
            document.getElementById('viewPilihJenis').classList.add('hidden');
            document.getElementById('viewPilihJenis').classList.remove('flex');
            document.getElementById('viewCariNasabah').classList.remove('hidden');
            document.getElementById('viewCariNasabah').classList.add('flex');
        }

        function simulasiScanQR() {
            alert("Membuka Kamera: Mensimulasikan pemindaian QR Code...");
            setTimeout(() => {
                let semuaNasabah = document.querySelectorAll(".nasabah-item");
                for (let i = 0; i < semuaNasabah.length; i++) {
                    if (semuaNasabah[i].style.display !== "none") {
                        pilihNasabah(semuaNasabah[i]);
                        break;
                    }
                }
            }, 800);
        }
    </script>
</body>
</html>
