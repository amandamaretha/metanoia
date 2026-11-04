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

            <!-- NOTIFIKASI FLASH -->
            @if (session('success'))
                <div class="w-full max-w-5xl mx-auto mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-xl shadow-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-green-600 text-xl"></i>
                    <p class="text-green-800 text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="w-full max-w-5xl mx-auto mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-xmark text-red-600 text-xl mt-0.5"></i>
                        <div>
                            <p class="font-bold text-red-900 text-sm">Terjadi Kesalahan Validasi:</p>
                            <ul class="list-disc list-inside text-red-800 text-xs mt-1 space-y-1">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

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

                    <!-- KARTU PERSIAPAN PENJUALAN & PROGRES DARI DATA SERVER -->
                    <div class="bg-[#FDF3DE] border-2 border-[#F3C78B] rounded-xl p-6 flex flex-col md:flex-row gap-6 items-stretch mb-6">
                        <div class="w-full md:w-5/12 flex flex-col justify-center">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fa-solid fa-truck text-lg text-black"></i>
                                <h3 class="font-bold text-black text-lg">Persiapan Penjualan ke BSI</h3>
                            </div>
                            <div class="mb-2">
                                <span class="text-3xl font-extrabold text-black">{{ number_format($stokSaatIni, 1, ',', '.') }} Kg</span>
                                <span class="text-3xl font-bold text-gray-400"> / {{ number_format($batasMinimumKg, 0, ',', '.') }} Kg</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-5 mb-2 border border-gray-300 overflow-hidden">
                                <div class="bg-[#81C784] h-full rounded-full transition-all duration-500" style="width: {{ $progresPersen }}%"></div>
                            </div>
                            @if ($kurangKg > 0)
                                <p class="text-[11px] text-gray-600 font-medium">Masih membutuhkan {{ number_format($kurangKg, 1, ',', '.') }} Kg untuk mencapai batas minimum pengangkutan</p>
                            @else
                                <p class="text-[11px] text-green-700 font-bold">Batas minimum pengangkutan telah tercapai (Siap Diangkut)</p>
                            @endif
                        </div>

                        <!-- KEPUTUSAN DEFAULT: Rincian stok ditampilkan sebagai total (rincian per kategori ditunda karena penjualan tidak mencatat kategori) -->
                        <div class="w-full md:w-7/12 bg-white border border-[#F3C78B] rounded-xl p-4 flex flex-col justify-center">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-bold text-black text-sm">Rincian Stok Saat Ini</h4>
                                <h4 class="font-extrabold text-[#5C8A8A] text-base">Total: {{ number_format($stokSaatIni, 2, ',', '.') }} Kg</h4>
                            </div>
                            <p class="text-xs text-gray-500 mb-3 leading-relaxed">
                                Stok merupakan akumulasi setoran sampah nasabah dikurangi total penjualan ke BSI.
                            </p>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-xs text-gray-600 flex items-center justify-between">
                                <span>Status Stok Gudang:</span>
                                <span class="font-bold text-gray-800">{{ number_format($stokSaatIni, 2, ',', '.') }} Kg Tersedia</span>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU RIWAYAT PENJUALAN -->
                    <div class="border-2 border-gray-200 rounded-xl p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-black">Riwayat Penjualan ke BSI</h2>
                                <p class="text-gray-500 text-xs">Daftar pengangkutan dan detail sampah yang dijual ke Bank Sampah Induk (BSI)</p>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                                <form action="{{ route('pengelola.laporan.penjualan') }}" method="GET" class="relative w-full sm:w-64">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                                    </span>
                                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor transaksi..."
                                        class="w-full pl-9 pr-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:border-[#5C8A8A] text-xs font-medium">
                                </form>
                                <button type="button" onclick="openModalCatat()" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md shadow-sm transition-colors text-xs flex items-center gap-2 whitespace-nowrap">
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
                                    @forelse ($riwayatPenjualan as $item)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 penjualan-row"
                                            data-id="{{ $item->id }}"
                                            data-nomor="{{ $item->nomor }}"
                                            data-tanggal="{{ $item->tanggal_pengangkutan->toDateString() }}"
                                            data-berat="{{ $item->total_berat }}"
                                            data-nilai="{{ $item->total_nilai }}">
                                            <td class="py-3 px-2 font-medium">{{ $loop->iteration + ($riwayatPenjualan->currentPage() - 1) * $riwayatPenjualan->perPage() }}</td>
                                            <td class="py-3 px-4 font-mono font-bold text-gray-900 no-transaksi">{{ $item->nomor }}</td>
                                            <td class="py-3 px-4 td-tanggal">{{ $item->tanggal_pengangkutan->format('d/m/Y') }}</td>
                                            <td class="py-3 px-4 font-mono td-berat">{{ number_format($item->total_berat, 2, ',', '.') }}</td>
                                            <td class="py-3 px-4 font-mono font-bold text-emerald-700 td-nilai">{{ number_format($item->total_nilai, 0, ',', '.') }}</td>
                                            <td class="py-3 px-4 flex justify-center gap-2">
                                                <button type="button" onclick="openDetail({{ $item->id }}, '{{ $item->nomor }}', '{{ $item->tanggal_pengangkutan->toDateString() }}', '{{ $item->total_berat }}', '{{ $item->total_nilai }}')"
                                                    class="bg-[#5FA8DC] hover:bg-blue-600 text-white text-xs font-bold py-1.5 px-4 rounded shadow-sm transition-colors">
                                                    Detail
                                                </button>
                                                <button type="button" onclick="openHapus({{ $item->id }}, '{{ $item->nomor }}')"
                                                    class="bg-[#F86363] hover:bg-red-600 text-white text-xs font-bold py-1.5 px-4 rounded shadow-sm transition-colors">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-8 text-center text-gray-400 font-medium">
                                                Belum ada data riwayat penjualan ke BSI.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($riwayatPenjualan->hasPages())
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                {{ $riwayatPenjualan->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div> <!-- END MAIN VIEW -->

            <!-- ============================================== -->
            <!-- VIEW 2: HALAMAN FORM DETAIL PENJUALAN (Hidden by default) -->
            <!-- ============================================== -->
            <div id="detailView" class="hidden max-w-[850px] w-full mx-auto mt-4">
                <h1 class="text-[34px] font-extrabold text-black mb-6">Detail Penjualan</h1>

                <div class="bg-white rounded-xl shadow-md border border-gray-300 w-full mb-10 overflow-hidden">
                    <form id="formUpdatePenjualan" method="POST" action="" class="space-y-0">
                        @csrf
                        @method('PUT')

                        <!-- No Transaksi (Tidak bisa diubah) -->
                        <div class="flex items-center px-10 py-6">
                            <label class="w-1/3 text-gray-700 font-bold">No Transaksi</label>
                            <div class="w-2/3">
                                <input type="text" id="detailNoTransaksi" readonly
                                    class="border border-gray-200 bg-gray-100 text-gray-600 font-mono font-bold rounded-md px-4 py-2.5 w-full max-w-sm focus:outline-none cursor-not-allowed">
                                <span class="text-[11px] text-gray-400 block mt-1">* Nomor transaksi otomatis dan tidak dapat diubah</span>
                            </div>
                        </div>

                        <!-- Tanggal Pengangkutan -->
                        <div class="flex items-center border-t border-gray-200 px-10 py-6">
                            <label for="detailTanggal" class="w-1/3 text-gray-700 font-bold">Tanggal Pengangkutan</label>
                            <div class="w-2/3">
                                <input type="date" name="tanggal_pengangkutan" id="detailTanggal" required
                                    class="border border-gray-300 rounded-md px-4 py-2.5 w-full max-w-sm focus:outline-none focus:border-[#5C8A8A] font-medium text-gray-800">
                            </div>
                        </div>

                        <!-- Berat -->
                        <div class="flex items-center border-t border-gray-200 px-10 py-6">
                            <label for="detailBerat" class="w-1/3 text-gray-700 font-bold">Berat</label>
                            <div class="w-2/3 flex items-center gap-3">
                                <input type="number" step="0.01" min="0.01" name="total_berat" id="detailBerat" required
                                    class="border border-gray-300 rounded-md px-4 py-2.5 w-36 focus:outline-none focus:border-[#5C8A8A] font-bold text-gray-800">
                                <span class="text-gray-700 font-bold">Kg</span>
                            </div>
                        </div>

                        <!-- Total Nilai -->
                        <div class="flex items-center border-t border-b border-gray-200 px-10 py-6">
                            <label for="detailNilai" class="w-1/3 text-gray-700 font-bold">Total Nilai</label>
                            <div class="w-2/3 flex items-center gap-3">
                                <span class="text-gray-700 font-bold">Rp</span>
                                <input type="number" step="1" min="0" name="total_nilai" id="detailNilai" required
                                    class="border border-gray-300 rounded-md px-4 py-2.5 w-full max-w-[290px] focus:outline-none focus:border-[#5C8A8A] font-bold text-gray-800">
                            </div>
                        </div>

                        <!-- Tombol Action -->
                        <div class="flex justify-center gap-6 py-8">
                            <button type="submit" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-3 px-12 rounded-lg shadow-sm transition-colors text-lg">
                                Simpan
                            </button>
                            <button type="button" onclick="batalDetail()" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-3 px-12 rounded-lg shadow-sm transition-colors text-lg">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div> <!-- END DETAIL VIEW -->

        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL POP UP - CATAT PENJUALAN MANUAL -->
    <!-- ============================================== -->
    <div id="modalCatat" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60 transition-opacity" onclick="closeModalCatat()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden z-10">
            <div class="h-12 bg-[#5C8A8A]"></div>
            <div class="px-12 py-10">
                <h2 class="text-3xl font-extrabold text-center text-black mb-10">Catat Penjualan Manual</h2>
                <form action="{{ route('pengelola.penjualan.store') }}" method="POST" class="space-y-0">
                    @csrf
                    <div class="flex items-center border-t border-gray-200 py-6">
                        <label for="inputTanggal" class="w-1/2 text-gray-700 font-bold">Tanggal Pengangkutan</label>
                        <div class="w-1/2 flex h-11">
                            <div class="bg-gray-200 border border-gray-300 border-r-0 rounded-l-md px-4 flex items-center justify-center">
                                <i class="fa-regular fa-calendar text-gray-600 text-lg"></i>
                            </div>
                            <input type="date" name="tanggal_pengangkutan" id="inputTanggal" required value="{{ date('Y-m-d') }}"
                                class="w-full border border-gray-300 rounded-r-md px-3 text-gray-700 focus:outline-none focus:border-[#5C8A8A] font-medium">
                        </div>
                    </div>
                    <div class="flex items-center border-t border-gray-200 py-6">
                        <label for="inputBerat" class="w-1/2 text-gray-700 font-bold">Total Berat</label>
                        <div class="w-1/2 flex items-center gap-3">
                            <input type="number" step="0.01" min="0.01" name="total_berat" id="inputBerat" required placeholder="0.00"
                                class="w-36 border border-gray-300 rounded-md px-3 py-2 h-11 focus:outline-none focus:border-[#5C8A8A] font-bold text-gray-800">
                            <span class="text-gray-700 font-bold">Kg</span>
                        </div>
                    </div>
                    <div class="flex items-center border-t border-b border-gray-200 py-6">
                        <label for="inputNilai" class="w-1/2 text-gray-700 font-bold">Total Nilai</label>
                        <div class="w-1/2 flex items-center gap-3">
                            <span class="text-gray-700 font-bold">Rp</span>
                            <input type="number" step="1" min="0" name="total_nilai" id="inputNilai" required placeholder="0"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 h-11 focus:outline-none focus:border-[#5C8A8A] font-bold text-gray-800">
                        </div>
                    </div>
                    <div class="flex justify-center gap-6 mt-10">
                        <button type="submit" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-3 px-10 rounded-lg shadow-md transition-colors text-lg">
                            Simpan
                        </button>
                        <button type="button" onclick="closeModalCatat()" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-3 px-10 rounded-lg shadow-md transition-colors text-lg">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL POP UP - KONFIRMASI HAPUS PENJUALAN -->
    <!-- ============================================== -->
    <div id="modalHapus" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60 transition-opacity" onclick="closeHapus()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden z-10 pt-10 pb-8 px-8 text-center">

            <!-- Ikon Lingkaran Merah Tanda Seru -->
            <div class="w-24 h-24 rounded-full border-4 border-[#FF6B6B] flex items-center justify-center mx-auto mb-6">
                <span class="text-6xl text-[#FF6B6B] font-bold">!</span>
            </div>

            <h2 class="text-[28px] font-bold text-[#FF6B6B] mb-2">Konfirmasi Hapus</h2>
            <p class="text-gray-700 text-[15px] mb-8">
                Apakah anda yakin ingin menghapus transaksi "<span id="hapusNoTransaksi" class="font-bold"></span>"?
            </p>

            <form id="formHapusPenjualan" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex justify-center gap-4">
                    <button type="submit" class="bg-[#5FA8DC] hover:bg-blue-600 text-white font-bold py-2.5 px-12 rounded-lg shadow-sm transition-colors text-lg">
                        Ya
                    </button>
                    <button type="button" onclick="closeHapus()" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-2.5 px-12 rounded-lg shadow-sm transition-colors text-lg">
                        Tidak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT CLIENT INTERAKSI -->
    <script>
        function openModalCatat() {
            document.getElementById('modalCatat').classList.remove('hidden');
        }

        function closeModalCatat() {
            document.getElementById('modalCatat').classList.add('hidden');
        }

        function openDetail(id, nomor, tanggal, berat, nilai) {
            document.getElementById('detailNoTransaksi').value = nomor;
            document.getElementById('detailTanggal').value = tanggal;
            document.getElementById('detailBerat').value = berat;
            document.getElementById('detailNilai').value = Math.round(nilai);

            // Set action URL form update
            const form = document.getElementById('formUpdatePenjualan');
            form.action = `/pengelola/penjualan/${id}`;

            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailView').classList.remove('hidden');
        }

        function batalDetail() {
            document.getElementById('detailView').classList.add('hidden');
            document.getElementById('mainView').classList.remove('hidden');
        }

        function openHapus(id, nomor) {
            document.getElementById('hapusNoTransaksi').innerText = nomor;
            const form = document.getElementById('formHapusPenjualan');
            form.action = `/pengelola/penjualan/${id}`;
            document.getElementById('modalHapus').classList.remove('hidden');
        }

        function closeHapus() {
            document.getElementById('modalHapus').classList.add('hidden');
        }
    </script>
</body>
</html>
