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
                        <img src="{{ Auth::guard('petugas')->user()?->foto ? (filter_var(Auth::guard('petugas')->user()->foto, FILTER_VALIDATE_URL) ? Auth::guard('petugas')->user()->foto : asset(Auth::guard('petugas')->user()->foto)) : 'https://i.pravatar.cc/150?img=12' }}" alt="Profile" class="w-full h-full object-cover">
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

                        <!-- Banner Peringatan Nasabah Tidak Aktif -->
                        <div id="alertNasabahTidakAktif" class="hidden mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm text-sm text-red-800 font-bold flex items-center gap-3">
                            <i class="fa-solid fa-circle-xmark text-red-600 text-xl"></i>
                            <span id="pesanNasabahTidakAktif">Akun nasabah tidak aktif. Transaksi penimbangan tidak dapat dilanjutkan.</span>
                        </div>

                        <div class="flex items-center gap-4 mb-6 w-full max-w-[650px]">
                            <!-- Tombol Buka Modal Scan QR -->
                            <button type="button" onclick="bukaModalQrPetugas()" class="bg-[#B4E99C] border-2 border-[#81D064] hover:bg-[#a2db89] text-black font-bold py-2.5 px-6 rounded-md shadow-sm transition-colors text-sm flex items-center gap-2 whitespace-nowrap">
                                <i class="fa-solid fa-qrcode text-lg"></i> Scan QR
                            </button>

                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                                </span>
                                <input type="text" id="searchInput" onkeyup="filterNasabah()" placeholder="Cari nama atau NIK nasabah"
                                    class="w-full pl-9 pr-4 py-2.5 rounded-md border-2 border-gray-200 focus:outline-none focus:border-[#5C8A8A] text-sm text-gray-700 font-medium transition-colors">
                            </div>
                        </div>

                        <!-- Daftar Nasabah Nyata dari Database -->
                        <div class="flex flex-col border border-gray-300 rounded-md w-full max-w-full divide-y divide-gray-200 max-h-96 overflow-y-auto" id="daftarNasabah">
                            @forelse ($nasabahList as $n)
                                <div class="nasabah-item flex items-center justify-between p-4 hover:bg-gray-50 cursor-pointer transition-all"
                                    data-id="{{ $n->id }}"
                                    data-nama="{{ $n->nama }}"
                                    data-nik="{{ $n->masked_nik }}"
                                    data-telp="{{ $n->no_telp }}"
                                    data-alamat="{{ $n->alamat }}"
                                    data-saldo="{{ $n->saldo }}"
                                    data-status="{{ $n->status }}"
                                    onclick="pilihNasabahElement(this)">
                                    <div class="flex items-center gap-4 w-1/3">
                                        <div class="w-10 h-10 rounded-full border-2 border-yellow-400 overflow-hidden bg-gray-100 flex items-center justify-center font-bold text-gray-700">
                                            {{ substr($n->nama, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-black nasabah-nama text-[15px] block">{{ $n->nama }}</span>
                                            <span class="text-xs {{ $n->status === 'aktif' ? 'text-green-600 font-bold' : 'text-red-500 font-bold' }}">
                                                {{ $n->status === 'aktif' ? '● Aktif' : '● Tidak Aktif' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="w-1/3 text-center text-gray-600 font-mono text-sm font-medium">
                                        {{ $n->masked_nik }}
                                    </div>
                                    <div class="w-1/3 text-right text-gray-500 font-medium text-sm truncate">
                                        {{ $n->alamat }}
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400">
                                    Belum ada data nasabah di sistem.
                                </div>
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
                    <button type="button" onclick="kembaliKePencarian()" class="absolute top-8 right-10 flex items-center gap-2 text-[#0c2f2a] hover:text-black font-bold transition-colors">
                        <i class="fa-solid fa-circle-xmark text-2xl"></i> <span class="text-sm">Batal</span>
                    </button>
                    <h1 class="text-[34px] font-extrabold text-black mb-6">Penimbangan</h1>

                    <!-- KARTU DATA NASABAH AKTIF -->
                    <div class="bg-[#CBE6DF] border-[1.5px] border-[#103D34] rounded-xl p-5 flex flex-col md:flex-row items-center justify-between shadow-sm mb-6">
                        <div class="flex-1 w-full flex flex-col items-center justify-center md:border-r-[1.5px] border-[#103D34] py-2">
                            <span class="text-[#103D34] font-extrabold text-lg mb-0.5">Status Akun</span>
                            <span id="detailStatusNasabah" class="text-[#59AC41] font-bold flex items-center gap-2 text-sm">
                                <div class="w-2.5 h-2.5 bg-[#59AC41] rounded-full shadow-sm"></div> Aktif
                            </span>
                        </div>
                        <div class="flex-[1.5] w-full px-6 flex flex-col justify-center py-2 text-center md:text-left md:border-r-[1.5px] border-[#103D34] mt-4 md:mt-0">
                            <span id="detailNamaNasabah" class="text-[#103D34] font-extrabold text-lg leading-tight mb-0.5">Nama Nasabah</span>
                            <span id="detailTelpNasabah" class="text-[#103D34] font-medium text-[15px] leading-tight">+62 812-3456-7890</span>
                            <span id="detailAlamatNasabah" class="text-[#103D34] font-medium text-[15px] leading-tight">Alamat Nasabah</span>
                        </div>
                        <div class="flex-1 w-full flex items-center justify-center gap-4 py-2 mt-4 md:mt-0">
                            <i class="fa-solid fa-wallet text-[32px] text-[#103D34]"></i>
                            <div class="flex flex-col">
                                <span class="text-[#103D34] font-extrabold text-lg leading-tight mb-0.5">Total Saldo</span>
                                <span id="detailSaldoNasabah" class="text-[#103D34] font-bold text-xl leading-tight">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-end mt-4">
                        <h2 class="text-[34px] font-extrabold text-[#103D34]">Jenis Sampah</h2>
                        <!-- Memanggil fungsi bukaModalTimbang() saat ditekan -->
                        <button type="button" onclick="bukaModalTimbang()" class="bg-[#5CB655] hover:bg-green-600 text-white font-bold py-2.5 px-6 rounded-md shadow-md transition-colors text-[17px] flex items-center gap-2 border-[1.5px] border-white z-10 relative">
                            <i class="fa-solid fa-scale-balanced text-[16px]"></i> Timbang Sekarang
                        </button>
                    </div>
                </div>

                <div class="px-10 py-8 flex-1 overflow-y-auto">
                    <!-- Grid Jenis Sampah dari Database -->
                    <div id="gridJenisSampahPenimbangan" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($jenisSampahList as $js)
                            @php
                                $hargaDefault = $js->hargaSampah->where('kondisi', 'sudah_dipilah')->first()?->harga_per_kg ?? 0;
                            @endphp
                            <div class="jenis-sampah-card bg-[#CBE6E0] border-[1.5px] border-[#103D34] rounded-xl p-3 flex items-stretch gap-3 hover:shadow-md cursor-pointer transition-all h-28"
                                data-id="{{ $js->id }}"
                                data-kode="{{ $js->kode }}"
                                data-nama="{{ $js->nama }}"
                                data-deskripsi="{{ $js->deskripsi }}"
                                data-harga-sudah="{{ $js->hargaSampah->where('kondisi', 'sudah_dipilah')->first()?->harga_per_kg ?? 0 }}"
                                data-harga-belum="{{ $js->hargaSampah->where('kondisi', 'belum_dipilah')->first()?->harga_per_kg ?? 0 }}"
                                data-harga-campuran="{{ $js->hargaSampah->where('kondisi', 'campuran')->first()?->harga_per_kg ?? 0 }}"
                                onclick="pilihJenisSampahCard(this)">
                                <div class="w-[85px] h-full bg-white rounded-lg border border-gray-300 flex items-center justify-center text-gray-500 font-bold text-lg overflow-hidden flex-shrink-0">
                                    @if ($js->gambar)
                                        <img src="{{ asset('storage/' . $js->gambar) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $js->kode }}
                                    @endif
                                </div>
                                <div class="flex flex-col flex-1 justify-center px-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-extrabold text-[#103D34] text-lg leading-tight">{{ $js->kode }}</span>
                                        <span class="text-xs font-bold text-[#297441]">Rp {{ number_format($hargaDefault, 0, ',', '.') }}/kg</span>
                                    </div>
                                    <span class="font-bold text-[#103D34] text-xs leading-tight mb-1 mt-0.5 truncate">{{ $js->nama }}</span>
                                    <span class="text-[#103D34] text-[10px] leading-tight line-clamp-2 opacity-80">{{ $js->deskripsi ?? '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div> <!-- END VIEW 2 -->

            <!-- ============================================== -->
            <!-- VIEW 3: HALAMAN PROSES TIMBANG -->
            <!-- ============================================== -->
            <div id="viewProsesTimbang" class="hidden flex-col h-full w-full transition-all">

                <!-- Banner Kuning Lebar -->
                <div class="bg-[#F3C546] px-10 pt-8 pb-6 rounded-bl-[35px] shadow-md w-full flex-shrink-0 relative">
                    <button type="button" onclick="kembaliKePilihJenis()" class="absolute top-8 right-10 flex items-center gap-2 text-[#0c2f2a] hover:text-black font-bold transition-colors">
                        <i class="fa-solid fa-circle-xmark text-2xl"></i> <span class="text-sm">Batal</span>
                    </button>
                    <h1 class="text-[34px] font-extrabold text-black mb-6">Penimbangan</h1>

                    <!-- Kartu Info Nasabah -->
                    <div class="bg-[#CBE6DF] border-[1.5px] border-[#103D34] rounded-xl p-5 flex flex-col md:flex-row items-center justify-between shadow-sm mb-5">
                        <div class="flex-1 w-full flex flex-col items-center justify-center md:border-r-[1.5px] border-[#103D34] py-1">
                            <span class="text-[#103D34] font-extrabold text-lg mb-0.5">Status Akun</span>
                            <span class="text-[#59AC41] font-bold flex items-center gap-2 text-sm"><div class="w-2.5 h-2.5 bg-[#59AC41] rounded-full shadow-sm animate-blink"></div> Aktif</span>
                        </div>
                        <div class="flex-[1.5] w-full px-6 flex flex-col justify-center py-1 text-center md:text-left md:border-r-[1.5px] border-[#103D34] mt-4 md:mt-0">
                            <span id="timbangNamaNasabah" class="text-[#103D34] font-extrabold text-lg leading-tight mb-0.5">Nama Nasabah</span>
                            <span id="timbangTelpNasabah" class="text-[#103D34] font-medium text-[15px] leading-tight">+62 812-3456-7890</span>
                            <span id="timbangAlamatNasabah" class="text-[#103D34] font-medium text-[15px] leading-tight">Alamat Nasabah</span>
                        </div>
                        <div class="flex-1 w-full flex items-center justify-center gap-4 py-1 mt-4 md:mt-0">
                            <i class="fa-solid fa-wallet text-[32px] text-[#103D34]"></i>
                            <div class="flex flex-col">
                                <span class="text-[#103D34] font-extrabold text-lg leading-tight mb-0.5">Total Saldo</span>
                                <span id="timbangSaldoNasabah" class="text-[#103D34] font-bold text-xl leading-tight">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Produk Terpilih & Pilihan Kondisi -->
                    <div class="flex flex-wrap items-center justify-between gap-6 mt-2 pb-2">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label class="font-extrabold text-[#103D34] text-base">Kode:</label>
                                <div id="timbangKodeProduk" class="bg-white px-3 py-1 rounded shadow-sm border border-gray-200 text-gray-700 font-bold text-sm">POS</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="font-extrabold text-[#103D34] text-base">Produk:</label>
                                <div id="timbangNamaProduk" class="bg-white px-3 py-1 rounded shadow-sm border border-gray-200 text-gray-700 font-semibold text-sm">Gelas Plastik</div>
                            </div>
                        </div>

                        <!-- PILIHAN KONDISI SAMPAH (FASE 5) -->
                        <div class="flex items-center gap-3 bg-white/70 px-4 py-2 rounded-xl border border-[#103D34]/30">
                            <span class="font-extrabold text-[#103D34] text-sm">Kondisi Sampah:</span>
                            <div class="flex gap-2 text-xs font-bold">
                                <label class="flex items-center gap-1.5 cursor-pointer bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-xs hover:bg-gray-50">
                                    <input type="radio" name="kondisiSampah" value="sudah_dipilah" checked onchange="ubahKondisiSampah(this.value)">
                                    <span>Sudah Dipilah</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-xs hover:bg-gray-50">
                                    <input type="radio" name="kondisiSampah" value="belum_dipilah" onchange="ubahKondisiSampah(this.value)">
                                    <span>Belum Dipilah</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-xs hover:bg-gray-50">
                                    <input type="radio" name="kondisiSampah" value="campuran" onchange="ubahKondisiSampah(this.value)">
                                    <span>Campuran</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Area Kartu IoT & Perhitungan -->
                <div class="px-10 py-8 flex-1 overflow-y-auto flex flex-col justify-center items-center">

                    <div class="flex flex-col md:flex-row gap-8 w-full max-w-4xl justify-center mb-8">

                        <!-- Kartu 1: Timbangan (WeighingSource / Manual Input) -->
                        <div class="bg-white rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] w-full md:w-[48%] overflow-hidden border border-gray-100 flex flex-col">
                            <!-- Header Hijau -->
                            <div class="bg-[#D3F5BB] px-5 py-4 flex items-center justify-between border-b border-gray-200">
                                <div class="flex items-center gap-3 text-[#103D34]">
                                    <i class="fa-solid fa-scale-unbalanced text-xl"></i>
                                    <div class="flex flex-col">
                                        <span class="font-extrabold text-[15px] leading-tight">Terhubung ke Timbangan</span>
                                        <span class="text-[11px] font-medium opacity-80">ManualWeighingSource cadangan IoT</span>
                                    </div>
                                </div>
                                <span class="bg-[#85DF4B] text-[#0a2721] text-[11px] font-extrabold px-3 py-1 rounded-full shadow-sm">Siap</span>
                            </div>

                            <!-- Body Angka Timbangan & Input Berat -->
                            <div class="p-6 flex-1 flex flex-col items-center justify-center relative">
                                <div class="w-full flex items-center justify-start gap-2 mb-2">
                                    <i class="fa-solid fa-weight-hanging text-[#5C8A8A]"></i>
                                    <span class="text-[#103D34] font-extrabold text-sm tracking-wide">BERAT DARI TIMBANGAN</span>
                                </div>

                                <div class="flex items-baseline gap-2 my-2">
                                    <span id="angkaBeratIoT" class="text-[72px] font-black text-[#103D34] leading-none tracking-tighter">0,00</span>
                                    <span class="text-3xl font-extrabold text-[#103D34]">kg</span>
                                </div>

                                <div class="w-full mt-4">
                                    <label for="inputBeratKg" class="block text-xs font-bold text-gray-500 mb-1">
                                        Input Berat Timbangan (kg):
                                    </label>
                                    <input type="number" id="inputBeratKg" min="0.01" step="0.01" placeholder="Contoh: 2.40"
                                        oninput="updateBeratManual(this.value)"
                                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-[#5C8A8A] text-lg font-bold text-gray-800 text-center">
                                </div>

                                <div class="mt-4 flex items-center gap-2 text-gray-400 text-xs font-medium bg-gray-50 px-4 py-1.5 rounded-full">
                                    <i class="fa-solid fa-info-circle text-blue-500"></i> Berat dibaca melalui WeighingSource
                                </div>
                            </div>
                        </div>

                        <!-- Kartu 2: Perhitungan Nilai (TransactionCalculator) -->
                        <div class="bg-white rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] w-full md:w-[48%] overflow-hidden border border-gray-100 flex flex-col p-6 relative">
                            <h3 class="text-[#103D34] font-extrabold text-lg mb-4">Perhitungan Nilai Setoran</h3>

                            <!-- Ikon Kalkulator/Uang -->
                            <div class="flex justify-center mb-4">
                                <i class="fa-solid fa-money-check-dollar text-[60px] text-[#103D34]"></i>
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
                                    <span id="calcHarga" class="text-[#103D34] font-extrabold text-2xl">Rp 0</span>
                                </div>
                            </div>

                            <!-- Total Tabungan -->
                            <div class="bg-[#ADF66B] rounded-lg p-4 flex items-center justify-between border-[1.5px] border-[#81D064] mt-auto">
                                <span class="text-[#103D34] font-extrabold text-sm w-20 leading-tight">TOTAL<br>SETORAN</span>
                                <span id="calcTotal" class="text-[#103D34] font-black text-3xl">Rp 0</span>
                            </div>
                        </div>

                    </div>

                    <!-- Tombol Konfirmasi Setoran -->
                    <button type="button" id="btnKonfirmasiSetoran" onclick="konfirmasiSetoran()" class="bg-[#5CB655] hover:bg-green-600 text-white font-bold py-3 px-8 rounded-md shadow-md transition-colors text-[17px] flex items-center gap-2 border-[1.5px] border-white">
                        <i class="fa-solid fa-clipboard-check text-lg"></i>
                        <span>Konfirmasi Setoran</span>
                    </button>

                </div>

            </div> <!-- END VIEW 3 -->

        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL POP UP KONFIRMASI MULAI TIMBANG -->
    <!-- ============================================== -->
    <div id="modalKonfirmasiTimbang" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" onclick="tutupModalTimbang()"></div>
        <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-sm flex flex-col items-center pt-8 pb-6 px-6 z-10">
            <button type="button" onclick="tutupModalTimbang()" class="absolute top-0 right-0 bg-[#FF4545] hover:bg-red-600 text-white w-10 h-10 flex items-center justify-center rounded-bl-lg transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <h2 class="text-2xl font-extrabold text-black text-center mb-8">
                Mulai timbang sampah?
            </h2>
            <div class="flex items-center gap-4 w-full justify-center">
                <button type="button" onclick="mulaiTimbang()" class="bg-[#5CB655] hover:bg-green-600 text-white font-bold py-2 px-10 rounded shadow-sm transition-colors text-[15px]">
                    Ya
                </button>
                <button type="button" onclick="tutupModalTimbang()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-8 rounded shadow-sm transition-colors text-[15px]">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL SCAN QR PETUGAS (FASE 5) -->
    <!-- ============================================== -->
    <div id="modalQrPetugas" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="batalQr()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10 flex flex-col items-center">
            <h3 class="text-xl font-extrabold text-gray-900 mb-1">Pindai QR Nasabah</h3>
            <p class="text-xs text-gray-500 text-center mb-4">Minta nasabah memindai kode QR berbatas waktu ini menggunakan HP nasabah.</p>

            <!-- Tampilan QR SVG Representation -->
            <div class="bg-gray-50 border-2 border-dashed border-emerald-400 p-6 rounded-2xl flex flex-col items-center justify-center mb-4 w-64 h-64">
                <svg class="w-48 h-48" viewBox="0 0 100 100" fill="currentColor">
                    <rect x="5" y="5" width="25" height="25" fill="#103D34" />
                    <rect x="10" y="10" width="15" height="15" fill="white" />
                    <rect x="13" y="13" width="9" height="9" fill="#103D34" />

                    <rect x="70" y="5" width="25" height="25" fill="#103D34" />
                    <rect x="75" y="10" width="15" height="15" fill="white" />
                    <rect x="78" y="13" width="9" height="9" fill="#103D34" />

                    <rect x="5" y="70" width="25" height="25" fill="#103D34" />
                    <rect x="10" y="75" width="15" height="15" fill="white" />
                    <rect x="13" y="78" width="9" height="9" fill="#103D34" />

                    <!-- Random data matrix lines -->
                    <rect x="40" y="10" width="6" height="6" fill="#103D34" />
                    <rect x="50" y="15" width="6" height="6" fill="#103D34" />
                    <rect x="40" y="25" width="6" height="6" fill="#103D34" />
                    <rect x="10" y="45" width="6" height="6" fill="#103D34" />
                    <rect x="25" y="45" width="6" height="6" fill="#103D34" />
                    <rect x="45" y="45" width="10" height="10" fill="#103D34" />
                    <rect x="65" y="40" width="6" height="6" fill="#103D34" />
                    <rect x="80" y="45" width="6" height="6" fill="#103D34" />
                    <rect x="40" y="65" width="6" height="6" fill="#103D34" />
                    <rect x="55" y="75" width="6" height="6" fill="#103D34" />
                    <rect x="70" y="70" width="6" height="6" fill="#103D34" />
                    <rect x="85" y="80" width="6" height="6" fill="#103D34" />
                </svg>
            </div>

            <!-- Kode Token Teks & Countdown -->
            <div class="text-center mb-4">
                <span class="text-xs text-gray-400 block font-bold">KODE SESI:</span>
                <span id="qrTokenDisplay" class="font-mono text-xs font-bold bg-gray-100 px-3 py-1 rounded-md text-gray-700">MEMUAT...</span>
                <div class="mt-2 text-xs font-bold text-amber-600 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-clock"></i>
                    <span>Kedaluwarsa dalam: <span id="qrTimerDisplay">120</span>s</span>
                </div>
            </div>

            <!-- Tombol Aksi QR -->
            <div class="flex items-center gap-3 w-full justify-center">
                <button type="button" onclick="perbaruiQr()" class="px-4 py-2 bg-[#5C8A8A] text-white rounded-lg text-xs font-bold hover:bg-[#4a7070] transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Perbarui QR</span>
                </button>
                <button type="button" onclick="batalQr()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-xs font-bold hover:bg-gray-300 transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL SUKSES SETORAN & NOTA (FASE 5 Langkah 16-17) -->
    <!-- ============================================== -->
    <div id="modalSuksesSetoran" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10 flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-3xl mb-4">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-1 text-center">Setoran Berhasil!</h3>
            <p class="text-xs text-gray-500 text-center mb-6">Transaksi setoran sampah telah tercatat ke saldo nasabah dan nota digital telah diterbitkan.</p>

            <div class="w-full bg-gray-50 rounded-xl p-4 border border-gray-200 text-xs space-y-2 mb-6">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nomor Nota:</span>
                    <span id="suksesNomorNota" class="font-bold font-mono text-gray-800">NOTA-XXXX</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Nilai Setoran:</span>
                    <span id="suksesTotalNilai" class="font-bold text-emerald-700">Rp 0</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2">
                    <span class="text-gray-500">Saldo Nasabah Terbaru:</span>
                    <span id="suksesSaldoBaru" class="font-bold text-gray-900">Rp 0</span>
                </div>
            </div>

            <!-- Tombol OK kembali ke daftar jenis sampah -->
            <button type="button" onclick="okKembaliKeJenisSampah()" class="w-full py-3 bg-[#5CB655] hover:bg-green-600 text-white font-bold rounded-xl shadow-md transition-colors text-sm">
                OK (Lanjutkan Jenis Lain)
            </button>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIKA PENIMBANGAN (BEBAS SIMULASI) -->
    <script>
        // State Transaksi Penimbangan
        let selectedNasabah = null;
        let selectedSampah = null;
        let selectedKondisi = 'sudah_dipilah';
        let currentHargaPerKg = 0;
        let currentBerat = 0.00;

        // State Sesi QR
        let activeQrToken = null;
        let qrCountdownInterval = null;
        let qrPollInterval = null;
        let qrTimeRemaining = 120;

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(Math.round(angka));
        }

        // ==========================================
        // VIEW 1: PILIH NASABAH & PENCARIAN
        // ==========================================
        function filterNasabah() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const items = document.querySelectorAll(".nasabah-item");
            items.forEach(item => {
                const nama = item.getAttribute("data-nama").toLowerCase();
                const nik = item.getAttribute("data-nik").toLowerCase();
                item.style.display = (nama.includes(input) || nik.includes(input)) ? "flex" : "none";
            });
        }

        function pilihNasabahElement(el) {
            const status = el.getAttribute("data-status");
            const alertBox = document.getElementById("alertNasabahTidakAktif");

            if (status !== 'aktif') {
                alertBox.classList.remove("hidden");
                document.getElementById("pesanNasabahTidakAktif").innerText =
                    `Akun nasabah "${el.getAttribute('data-nama')}" tidak aktif. Transaksi penimbangan tidak dapat dilanjutkan.`;
                return;
            }

            alertBox.classList.add("hidden");

            selectedNasabah = {
                id: parseInt(el.getAttribute("data-id")),
                nama: el.getAttribute("data-nama"),
                nik: el.getAttribute("data-nik"),
                telp: el.getAttribute("data-telp"),
                alamat: el.getAttribute("data-alamat"),
                saldo: parseInt(el.getAttribute("data-saldo")),
                status: status
            };

            masukKePilihJenisSampah();
        }

        function masukKePilihJenisSampah() {
            if (!selectedNasabah) return;

            document.getElementById("detailNamaNasabah").innerText = selectedNasabah.nama;
            document.getElementById("detailTelpNasabah").innerText = selectedNasabah.telp || '-';
            document.getElementById("detailAlamatNasabah").innerText = selectedNasabah.alamat || '-';
            document.getElementById("detailSaldoNasabah").innerText = "Rp " + formatRupiah(selectedNasabah.saldo);

            document.getElementById("viewCariNasabah").classList.add("hidden");
            document.getElementById("viewCariNasabah").classList.remove("flex");

            document.getElementById("viewPilihJenis").classList.remove("hidden");
            document.getElementById("viewPilihJenis").classList.add("flex");
        }

        function kembaliKePencarian() {
            selectedNasabah = null;
            selectedSampah = null;
            document.getElementById("searchInput").value = "";
            filterNasabah();

            document.getElementById("viewPilihJenis").classList.add("hidden");
            document.getElementById("viewPilihJenis").classList.remove("flex");

            document.getElementById("viewCariNasabah").classList.remove("hidden");
            document.getElementById("viewCariNasabah").classList.add("flex");
        }

        // ==========================================
        // SESI QR CODE (FASE 5)
        // ==========================================
        async function bukaModalQrPetugas() {
            document.getElementById('modalQrPetugas').classList.remove('hidden');
            await buatQrBaru();
        }

        async function buatQrBaru() {
            clearInterval(qrCountdownInterval);
            clearInterval(qrPollInterval);

            try {
                const response = await fetch("{{ route('pengelola.penimbangan.qr.create') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                activeQrToken = data.token;
                qrTimeRemaining = data.expires_in || 120;

                document.getElementById('qrTokenDisplay').innerText = activeQrToken;
                document.getElementById('qrTimerDisplay').innerText = qrTimeRemaining;

                // Hitung mundur timer
                qrCountdownInterval = setInterval(() => {
                    qrTimeRemaining--;
                    document.getElementById('qrTimerDisplay').innerText = Math.max(0, qrTimeRemaining);
                    if (qrTimeRemaining <= 0) {
                        clearInterval(qrCountdownInterval);
                        clearInterval(qrPollInterval);
                        document.getElementById('qrTokenDisplay').innerText = "KEDALUWARSA (Klik Perbarui)";
                    }
                }, 1000);

                // Polling status session ke backend
                qrPollInterval = setInterval(checkQrStatus, 1500);

            } catch (err) {
                console.error(err);
                alert("Gagal menerbitkan sesi QR.");
            }
        }

        async function perbaruiQr() {
            clearInterval(qrCountdownInterval);
            clearInterval(qrPollInterval);

            try {
                const response = await fetch("{{ route('pengelola.penimbangan.qr.refresh') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                activeQrToken = data.token;
                qrTimeRemaining = data.expires_in || 120;

                document.getElementById('qrTokenDisplay').innerText = activeQrToken;
                document.getElementById('qrTimerDisplay').innerText = qrTimeRemaining;

                qrCountdownInterval = setInterval(() => {
                    qrTimeRemaining--;
                    document.getElementById('qrTimerDisplay').innerText = Math.max(0, qrTimeRemaining);
                    if (qrTimeRemaining <= 0) {
                        clearInterval(qrCountdownInterval);
                        clearInterval(qrPollInterval);
                        document.getElementById('qrTokenDisplay').innerText = "KEDALUWARSA (Klik Perbarui)";
                    }
                }, 1000);

                qrPollInterval = setInterval(checkQrStatus, 1500);
            } catch (err) {
                console.error(err);
            }
        }

        async function checkQrStatus() {
            if (!activeQrToken) return;

            try {
                const response = await fetch(`{{ route('pengelola.penimbangan.qr.status') }}?token=${activeQrToken}`);
                const data = await response.json();

                if (data.status === 'claimed' && data.nasabah) {
                    clearInterval(qrCountdownInterval);
                    clearInterval(qrPollInterval);
                    document.getElementById('modalQrPetugas').classList.add('hidden');

                    if (data.nasabah.status !== 'aktif') {
                        alert(`Nasabah ${data.nasabah.nama} tidak aktif. Penimbangan tidak dapat dilanjutkan.`);
                        return;
                    }

                    selectedNasabah = data.nasabah;
                    masukKePilihJenisSampah();
                } else if (data.status === 'expired') {
                    clearInterval(qrCountdownInterval);
                    clearInterval(qrPollInterval);
                    document.getElementById('qrTokenDisplay').innerText = "KEDALUWARSA (Klik Perbarui)";
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function batalQr() {
            clearInterval(qrCountdownInterval);
            clearInterval(qrPollInterval);

            if (activeQrToken) {
                try {
                    await fetch("{{ route('pengelola.penimbangan.qr.cancel') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ token: activeQrToken })
                    });
                } catch (e) {}
            }

            activeQrToken = null;
            document.getElementById('modalQrPetugas').classList.add('hidden');
        }

        // ==========================================
        // VIEW 2: PILIH JENIS SAMPAH
        // ==========================================
        function pilihJenisSampahCard(el) {
            document.querySelectorAll(".jenis-sampah-card").forEach(c => {
                c.classList.remove("ring-4", "ring-emerald-500", "bg-[#AEE68C]");
                c.classList.add("bg-[#CBE6E0]");
            });

            el.classList.add("ring-4", "ring-emerald-500", "bg-[#AEE68C]");
            el.classList.remove("bg-[#CBE6E0]");

            selectedSampah = {
                id: parseInt(el.getAttribute("data-id")),
                kode: el.getAttribute("data-kode"),
                nama: el.getAttribute("data-nama"),
                deskripsi: el.getAttribute("data-deskripsi"),
                hargaSudah: parseInt(el.getAttribute("data-harga-sudah")),
                hargaBelum: parseInt(el.getAttribute("data-harga-belum")),
                hargaCampuran: parseInt(el.getAttribute("data-harga-campuran"))
            };
        }

        function bukaModalTimbang() {
            if (!selectedSampah) {
                alert("Silakan pilih SATU jenis sampah terlebih dahulu!");
                return;
            }
            document.getElementById("modalKonfirmasiTimbang").classList.remove("hidden");
        }

        function tutupModalTimbang() {
            document.getElementById("modalKonfirmasiTimbang").classList.add("hidden");
        }

        function mulaiTimbang() {
            tutupModalTimbang();

            document.getElementById("viewPilihJenis").classList.add("hidden");
            document.getElementById("viewPilihJenis").classList.remove("flex");

            document.getElementById("viewProsesTimbang").classList.remove("hidden");
            document.getElementById("viewProsesTimbang").classList.add("flex");

            // Isi Header Data Timbang
            document.getElementById("timbangNamaNasabah").innerText = selectedNasabah.nama;
            document.getElementById("timbangTelpNasabah").innerText = selectedNasabah.telp || '-';
            document.getElementById("timbangAlamatNasabah").innerText = selectedNasabah.alamat || '-';
            document.getElementById("timbangSaldoNasabah").innerText = "Rp " + formatRupiah(selectedNasabah.saldo);

            document.getElementById("timbangKodeProduk").innerText = selectedSampah.kode;
            document.getElementById("timbangNamaProduk").innerText = selectedSampah.nama;

            // Reset Pilihan Kondisi & Berat
            document.querySelector("input[name='kondisiSampah'][value='sudah_dipilah']").checked = true;
            selectedKondisi = 'sudah_dipilah';
            hitungHargaSesuaiKondisi();

            document.getElementById("inputBeratKg").value = "";
            currentBerat = 0.00;
            renderKalkulasi();
        }

        function kembaliKePilihJenis() {
            document.getElementById("viewProsesTimbang").classList.add("hidden");
            document.getElementById("viewProsesTimbang").classList.remove("flex");

            document.getElementById("viewPilihJenis").classList.remove("hidden");
            document.getElementById("viewPilihJenis").classList.add("flex");
        }

        // ==========================================
        // VIEW 3: PROSES TIMBANG & KALKULASI
        // ==========================================
        function ubahKondisiSampah(kondisi) {
            selectedKondisi = kondisi;
            hitungHargaSesuaiKondisi();
            renderKalkulasi();
        }

        function hitungHargaSesuaiKondisi() {
            if (selectedKondisi === 'sudah_dipilah') {
                currentHargaPerKg = selectedSampah.hargaSudah;
            } else if (selectedKondisi === 'belum_dipilah') {
                currentHargaPerKg = selectedSampah.hargaBelum;
            } else {
                currentHargaPerKg = selectedSampah.hargaCampuran;
            }
        }

        function updateBeratManual(val) {
            let beratNum = parseFloat(val);
            if (isNaN(beratNum) || beratNum < 0) {
                beratNum = 0.00;
            }
            currentBerat = beratNum;
            renderKalkulasi();
        }

        function renderKalkulasi() {
            const beratStr = currentBerat.toFixed(2).replace('.', ',');
            document.getElementById("angkaBeratIoT").innerText = beratStr;
            document.getElementById("calcBerat").innerText = beratStr + " kg";
            document.getElementById("calcHarga").innerText = "Rp " + formatRupiah(currentHargaPerKg);

            const total = Math.round(currentBerat * currentHargaPerKg);
            document.getElementById("calcTotal").innerText = "Rp " + formatRupiah(total);
        }

        async function konfirmasiSetoran() {
            if (currentBerat <= 0) {
                alert("Berat sampah harus lebih besar dari 0 kilogram!");
                document.getElementById("inputBeratKg").focus();
                return;
            }

            const btn = document.getElementById("btnKonfirmasiSetoran");
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const response = await fetch("{{ route('pengelola.penimbangan.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nasabah_id: selectedNasabah.id,
                        jenis_sampah_id: selectedSampah.id,
                        kondisi: selectedKondisi,
                        berat: currentBerat
                    })
                });

                const res = await response.json();

                if (response.ok && res.success) {
                    // Update Saldo Nasabah Lokal
                    selectedNasabah.saldo = res.data.saldo_terbaru;

                    document.getElementById("suksesNomorNota").innerText = res.data.nomor_nota;
                    document.getElementById("suksesTotalNilai").innerText = "Rp " + formatRupiah(res.data.total_nilai);
                    document.getElementById("suksesSaldoBaru").innerText = "Rp " + formatRupiah(res.data.saldo_terbaru);

                    // Tampilkan modal sukses
                    document.getElementById("modalSuksesSetoran").classList.remove("hidden");
                } else {
                    alert(res.message || "Gagal menyimpan transaksi setoran.");
                }
            } catch (err) {
                console.error(err);
                alert("Terjadi kesalahan jaringan saat menyimpan setoran.");
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-clipboard-check text-lg"></i> Konfirmasi Setoran';
            }
        }

        function okKembaliKeJenisSampah() {
            document.getElementById("modalSuksesSetoran").classList.add("hidden");

            // Kembali ke View 2 (Pilih Jenis Sampah) untuk nasabah yang sama
            kembaliKePilihJenis();

            // Perbarui tampilan saldo di View 2
            document.getElementById("detailSaldoNasabah").innerText = "Rp " + formatRupiah(selectedNasabah.saldo);

            // Reset seleksi jenis sampah
            selectedSampah = null;
            document.querySelectorAll(".jenis-sampah-card").forEach(c => {
                c.classList.remove("ring-4", "ring-emerald-500", "bg-[#AEE68C]");
                c.classList.add("bg-[#CBE6E0]");
            });
        }
    </script>
</body>
</html>
