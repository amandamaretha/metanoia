<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Nasabah - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }
    </style>
</head>
<body class="bg-gray-200 h-screen font-sans relative">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <img src="{{ Auth::guard('petugas')->user()?->foto ? (filter_var(Auth::guard('petugas')->user()->foto, FILTER_VALIDATE_URL) ? Auth::guard('petugas')->user()->foto : asset(Auth::guard('petugas')->user()->foto)) : 'https://i.pravatar.cc/150?img=12' }}" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">{{ Auth::guard('petugas')->user()->nama ?? 'Pengelola' }}</h2>
                </div>

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

                    <a href="{{ route('pengelola.penimbangan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition-colors">
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
            </div>

            <div class="p-4 mb-4">
                <a href="{{ route('pengelola.pengaturan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition-colors">
                    <i class="fa-solid fa-gear w-6"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-8 overflow-y-auto relative z-0">
            <h1 class="text-3xl font-extrabold text-black mb-6">Edit Data Nasabah</h1>

            <!-- FORM CONTAINER -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-300 max-w-4xl mx-auto">

                <!-- Header Card & Avatar -->
                <div class="bg-gray-200 h-40 flex items-center justify-center rounded-t-lg relative">
                    <div class="w-32 h-32 bg-[#F6CA44] rounded-full border-4 border-gray-200 flex items-center justify-center overflow-hidden absolute -bottom-10 shadow-sm">
                        <img src="https://i.pravatar.cc/150?img=47" alt="Profile Nasabah" class="w-full h-full object-cover">
                    </div>
                @if ($errors->any())
                    <div class="mb-6 mx-8 mt-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <p class="font-bold mb-1">Terjadi kesalahan validasi:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('pengelola.nasabah.update', $nasabah->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Row: Nama Nasabah -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium">Nama Nasabah</label>
                        <div class="w-2/3">
                            <input type="text" name="nama" value="{{ old('nama', $nasabah->nama) }}" required class="border border-gray-300 rounded-md px-3 py-2 w-full max-w-md focus:outline-none focus:border-[#5C8A8A]">
                            @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Row: NIK (Tampil sebagian, readonly) -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium">NIK</label>
                        <div class="w-2/3">
                            <input type="text" value="{{ $nasabah->masked_nik }}" readonly class="border border-gray-200 bg-gray-100 rounded-md px-3 py-2 w-full max-w-md text-gray-600 font-mono cursor-not-allowed">
                            <span class="text-xs text-gray-400 block mt-1">NIK terdaftar (hanya ditampilkan sebagian demi privasi)</span>
                        </div>
                    </div>

                    <!-- Row: No Telepon -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium">No Telepon</label>
                        <div class="w-2/3">
                            <input type="tel" name="no_telp" value="{{ old('no_telp', $nasabah->no_telp) }}" required class="border border-gray-300 rounded-md px-3 py-2 w-full max-w-md focus:outline-none focus:border-[#5C8A8A]">
                            @error('no_telp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Row: Alamat -->
                    <div class="flex items-start border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium pt-2">Alamat</label>
                        <div class="w-2/3">
                            <textarea name="alamat" rows="3" required class="border border-gray-300 rounded-md px-3 py-2 w-full max-w-md focus:outline-none focus:border-[#5C8A8A]">{{ old('alamat', $nasabah->alamat) }}</textarea>
                            @error('alamat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Row: Status Nasabah -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium">Status Akun</label>
                        <div class="w-2/3">
                            <select name="status" required class="border border-gray-300 rounded-md px-3 py-2 w-full max-w-md focus:outline-none focus:border-[#5C8A8A]">
                                <option value="aktif" {{ old('status', $nasabah->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="tidak_aktif" {{ old('status', $nasabah->status) === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Row: Saldo (READONLY, TANPA FITUR EDIT) -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4 rounded-b-lg">
                        <label class="w-1/3 text-gray-700 font-medium">Saldo Tabungan</label>
                        <div class="w-2/3 flex items-center gap-3">
                            <span class="text-gray-700 font-medium">Rp</span>
                            <input type="text" value="{{ number_format($nasabah->saldo, 0, ',', '.') }}" readonly class="border border-gray-200 bg-gray-100 rounded-md px-3 py-2 w-full max-w-sm focus:outline-none text-gray-600 font-bold cursor-not-allowed">
                            <span class="text-xs text-gray-400 italic">(Saldo tidak dapat diubah manual lewat formulir)</span>
                        </div>
                    </div>

                    <div class="flex justify-center gap-4 py-8 bg-gray-50 rounded-b-lg border-t border-gray-200">
                        <button type="submit" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-3 px-12 rounded-lg shadow-sm transition-colors text-lg">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('pengelola.nasabah') }}" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-3 px-12 rounded-lg shadow-sm transition-colors text-lg inline-block text-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>

        </main>
    </div>
</body>
</html>
