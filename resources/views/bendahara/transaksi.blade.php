<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Transaksi - Metanoia</title>
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
                    <a href="{{ route('bendahara.transaksi') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors">
                        <i class="fa-solid fa-money-bill-transfer w-6"></i>
                        <span>Tambah Transaksi</span>
                    </a>
                    <a href="{{ route('bendahara.riwayat') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-clock-rotate-left w-6"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                    <a href="{{ route('bendahara.laporan-keuangan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
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
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-3xl font-extrabold text-black">Tambah Transaksi</h1>
                    <div class="bg-white px-5 py-3 rounded-xl shadow-sm border border-gray-200 text-right">
                        <span class="text-xs text-gray-500 font-semibold block uppercase">Saldo Kas Saat Ini</span>
                        <span class="text-xl font-bold text-gray-800">Rp {{ number_format($saldoKas, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Formulir Tambah Transaksi -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <form id="formTambahTransaksi" action="{{ route('bendahara.transaksi.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Nominal Transaksi -->
                        <div class="mb-5">
                            <label for="nominal" class="block text-sm font-bold text-gray-700 mb-2">Nominal (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="nominal" id="nominal" value="{{ old('nominal') }}" placeholder="Contoh: 150000" min="1" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                            @error('nominal')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Transaksi -->
                        <div class="mb-5">
                            <label for="tanggal" class="block text-sm font-bold text-gray-700 mb-2">Tanggal Transaksi <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                            @error('tanggal')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenis Transaksi -->
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Transaksi <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-6 mt-1">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="jenis" value="pemasukan" {{ old('jenis') === 'pemasukan' ? 'checked' : '' }} required
                                        class="w-4 h-4 text-[#5C8A8A] focus:ring-[#5C8A8A] border-gray-300">
                                    <span class="text-sm font-medium text-gray-700"><i class="fa-solid fa-arrow-down text-green-600 mr-1"></i> Pemasukan</span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="jenis" value="pengeluaran" {{ old('jenis') === 'pengeluaran' ? 'checked' : '' }} required
                                        class="w-4 h-4 text-[#5C8A8A] focus:ring-[#5C8A8A] border-gray-300">
                                    <span class="text-sm font-medium text-gray-700"><i class="fa-solid fa-arrow-up text-red-600 mr-1"></i> Pengeluaran</span>
                                </label>
                            </div>
                            @error('jenis')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Keterangan Transaksi -->
                        <div class="mb-5">
                            <label for="keterangan" class="block text-sm font-bold text-gray-700 mb-2">Keterangan <span class="text-red-500">*</span></label>
                            <textarea name="keterangan" id="keterangan" rows="3" placeholder="Tuliskan keterangan detail transaksi..." required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Lampiran Bukti (Opsional) -->
                        <div class="mb-8">
                            <label for="lampiran" class="block text-sm font-bold text-gray-700 mb-2">Lampiran Bukti (Opsional: JPG, PNG, PDF maks 5 MB)</label>
                            <input type="file" name="lampiran" id="lampiran" accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#5C8A8A]/10 file:text-[#5C8A8A] hover:file:bg-[#5C8A8A]/20">
                            @error('lampiran')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tombol Aksi: Simpan & Reset -->
                        <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6">
                            <button type="button" onclick="resetForm()" class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                                Reset
                            </button>
                            <button type="button" onclick="validasiDanSimpan()" class="px-8 py-2.5 rounded-lg bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold text-sm shadow-sm transition">
                                Simpan Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL PERINGATAN: TIDAK DAPAT MENYIMPAN (DATA WAJIB KURANG) -->
    <div id="modalKurangData" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-center">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Tidak Dapat Menyimpan</h3>
            <p id="pesanKurangData" class="text-sm text-gray-600 mb-6">Mohon lengkapi seluruh data wajib: nominal, tanggal, jenis, dan keterangan transaksi.</p>
            <button type="button" onclick="tutupModalKurangData()" class="w-full bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold py-2.5 px-4 rounded-lg text-sm transition">
                Ya
            </button>
        </div>
    </div>

    <!-- MODAL NOTIFIKASI SUKSES: TRANSAKSI BERHASIL DISIMPAN -->
    @if(session('success'))
    <div id="modalSukses" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-center">
            <div class="w-14 h-14 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Transaksi berhasil disimpan</h3>
            <p class="text-sm text-gray-600 mb-6">{{ session('success') }}</p>
            <button type="button" onclick="tutupModalSukses()" class="w-full bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold py-2.5 px-4 rounded-lg text-sm transition">
                Ya
            </button>
        </div>
    </div>
    @endif

    <script>
        function resetForm() {
            document.getElementById('formTambahTransaksi').reset();
            document.getElementById('nominal').value = '';
            document.getElementById('keterangan').value = '';
            document.getElementById('lampiran').value = '';
        }

        function validasiDanSimpan() {
            let nominal = document.getElementById('nominal').value.trim();
            let tanggal = document.getElementById('tanggal').value.trim();
            let keterangan = document.getElementById('keterangan').value.trim();
            let jenisPemasukan = document.querySelector('input[name="jenis"][value="pemasukan"]').checked;
            let jenisPengeluaran = document.querySelector('input[name="jenis"][value="pengeluaran"]').checked;

            if (!nominal || !tanggal || !keterangan || (!jenisPemasukan && !jenisPengeluaran)) {
                document.getElementById('modalKurangData').classList.remove('hidden');
                return;
            }

            document.getElementById('formTambahTransaksi').submit();
        }

        function tutupModalKurangData() {
            document.getElementById('modalKurangData').classList.add('hidden');
        }

        function tutupModalSukses() {
            let modal = document.getElementById('modalSukses');
            if (modal) modal.remove();
        }
    </script>
</body>
</html>
