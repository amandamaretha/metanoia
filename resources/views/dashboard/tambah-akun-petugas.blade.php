<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun Pengelola - Metanoia</title>
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
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10 pb-6">
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
                    <a href="{{ route('pengelola.laporan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-file-lines w-6"></i>
                        <span>Laporan</span>
                    </a>
                </nav>
            </div>

            <div class="px-4 mt-auto">
                <a href="{{ route('pengelola.pengaturan') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors">
                    <i class="fa-solid fa-gear w-6"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-10 overflow-y-auto relative z-0 flex flex-col">
            <div class="max-w-3xl mx-auto w-full">
                <div class="flex items-center gap-4 mb-6">
                    <a href="{{ route('pengelola.pengaturan') }}" class="text-gray-600 hover:text-black transition">
                        <i class="fa-solid fa-arrow-left text-2xl"></i>
                    </a>
                    <!-- Catatan TEMUAN: Nama Menu di Pengaturan adalah 'Tambah Akun Petugas', tetapi judul formulir sesuai prosedur adalah 'Tambah Akun Pengelola' -->
                    <h1 class="text-3xl font-extrabold text-black">Tambah Akun Pengelola</h1>
                </div>

                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8">
                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                            <span class="font-bold block mb-1">Periksa kembali data Anda:</span>
                            <ul class="list-disc list-inside text-xs">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="formTambahPetugas" action="{{ route('pengelola.petugas.store') }}" method="POST">
                        @csrf

                        <!-- Nama Lengkap -->
                        <div class="mb-5">
                            <label for="nama" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required placeholder="Masukkan nama lengkap petugas"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                            @error('nama')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nomor Telepon -->
                        <div class="mb-5">
                            <label for="no_telp" class="block text-sm font-bold text-gray-700 mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                            <input type="text" name="no_telp" id="no_telp" value="{{ old('no_telp') }}" required placeholder="Contoh: 081234567890"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                            @error('no_telp')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jabatan -->
                        <div class="mb-5">
                            <label for="jabatan" class="block text-sm font-bold text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                            <select name="jabatan" id="jabatan" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                                <option value="">-- Pilih Jabatan --</option>
                                <option value="pengelola" {{ old('jabatan') === 'pengelola' ? 'selected' : '' }}>Pengelola</option>
                                <option value="bendahara" {{ old('jabatan') === 'bendahara' ? 'selected' : '' }}>Bendahara</option>
                                <option value="ketua" {{ old('jabatan') === 'ketua' ? 'selected' : '' }}>Ketua Bank Sampah</option>
                            </select>
                            @error('jabatan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-5">
                            <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" id="password" required minlength="8" placeholder="Minimal 8 karakter"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="mb-8">
                            <label for="konfirmasi_password" class="block text-sm font-bold text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                            <input type="password" name="konfirmasi_password" id="konfirmasi_password" required minlength="8" placeholder="Ketik ulang password"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                            @error('konfirmasi_password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6">
                            <a href="{{ route('pengelola.pengaturan') }}" class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <button type="button" onclick="bukaModalKonfirmasi()" class="px-8 py-2.5 rounded-lg bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold text-sm shadow-sm transition">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL KONFIRMASI TAMBAH PETUGAS -->
    <div id="modalKonfirmasiTambah" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-center">
            <div class="w-14 h-14 bg-blue-100 text-[#5C8A8A] rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Apakah Anda yakin ingin menambahkan petugas?</h3>
            <p class="text-xs text-gray-500 mb-6">Pastikan nomor telepon dan jabatan telah sesuai sebelum menyimpan akun.</p>
            <div class="flex items-center justify-center gap-4">
                <button type="button" onclick="tutupModalKonfirmasi()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition w-28">
                    Tidak
                </button>
                <button type="button" onclick="kirimFormPetugas()" class="px-5 py-2.5 rounded-lg bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold text-sm shadow-sm transition w-28">
                    Ya
                </button>
            </div>
        </div>
    </div>

    <script>
        function bukaModalKonfirmasi() {
            let nama = document.getElementById('nama').value.trim();
            let telp = document.getElementById('no_telp').value.trim();
            let jabatan = document.getElementById('jabatan').value.trim();
            let pass = document.getElementById('password').value.trim();
            let konf = document.getElementById('konfirmasi_password').value.trim();

            if (!nama || !telp || !jabatan || !pass || !konf) {
                alert('Mohon lengkapi seluruh kolom formulir terlebih dahulu.');
                return;
            }

            if (pass !== konf) {
                alert('Password dan konfirmasi password tidak sama.');
                return;
            }

            document.getElementById('modalKonfirmasiTambah').classList.remove('hidden');
        }

        function tutupModalKonfirmasi() {
            document.getElementById('modalKonfirmasiTambah').classList.add('hidden');
        }

        function kirimFormPetugas() {
            document.getElementById('formTambahPetugas').submit();
        }
    </script>
</body>
</html>
