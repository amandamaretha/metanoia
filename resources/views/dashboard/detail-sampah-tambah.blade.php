<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; background-color: #F3F4F6; }

        /* Box-shadow khusus untuk pembungkus tombol seperti di desain */
        .btn-wrapper-shadow { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
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
                    <!-- Menu Detail Sampah Aktif -->
                    <a href="{{ route('pengelola.detail-sampah') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors shadow-sm">
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
                <a href="{{ route('pengelola.pengaturan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                    <i class="fa-solid fa-gear w-6"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT CONTAINER -->

        <main class="flex-1 overflow-y-auto relative z-0 flex flex-col items-center justify-center p-10 bg-[#EAEAEA]">


            <div class="w-full max-w-4xl flex flex-col">

                <h1 class="text-[32px] font-extrabold text-black mb-6">Tambah Produk</h1>
                <!-- Kartu Form -->
                <div class="bg-white rounded-xl shadow-md p-10 w-full min-h-[580px] border border-gray-200">

                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                            <p class="font-bold">Periksa kembali isian Anda.</p>
                        </div>
                    @endif

                    <form action="{{ route('pengelola.detail-sampah.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf

                        <div class="grid grid-cols-[180px_1fr] gap-y-6 items-start w-full pr-4">

                            <!-- Nama Produk -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Nama Produk</label>
                            <div>
                                <input type="text" name="nama" id="inputNama" value="{{ old('nama') }}" required class="w-full border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm">
                                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Kode Produk -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Kode Produk</label>
                            <div>
                                <input type="text" name="kode" id="inputKode" value="{{ old('kode') }}" required class="w-full border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm" placeholder="Contoh: P01">
                                @error('kode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Kategori Produk -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Kategori Produk</label>
                            <div>
                                <input type="text" name="kategori" id="inputKategori" value="{{ old('kategori') }}" required class="w-full border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm" placeholder="Contoh: Plastik, Kertas, Logam">
                                @error('kategori') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Deskripsi Produk -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Deskripsi Produk</label>
                            <div>
                                <textarea name="deskripsi" id="inputDeskripsi" class="w-full border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm h-28 resize-none">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Harga Sudah Dipilah -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Harga Sudah Dipilah (/kg)</label>
                            <div>
                                <input type="text" name="harga_sudah_dipilah" id="inputHargaSudahDipilah" value="{{ old('harga_sudah_dipilah') }}" required class="w-full border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm" placeholder="Contoh: 3.000">
                                @error('harga_sudah_dipilah') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Harga Belum Dipilah -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Harga Belum Dipilah (/kg)</label>
                            <div>
                                <input type="text" name="harga_belum_dipilah" id="inputHargaBelumDipilah" value="{{ old('harga_belum_dipilah') }}" required class="w-full border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm" placeholder="Contoh: 2.500">
                                @error('harga_belum_dipilah') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Harga Campuran -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Harga Campuran (/kg)</label>
                            <div>
                                <input type="text" name="harga_campuran" id="inputHargaCampuran" value="{{ old('harga_campuran') }}" required class="w-full border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm" placeholder="Contoh: 2.000">
                                @error('harga_campuran') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Area Upload Gambar (Garis Putus-putus) -->
                            <div class="col-span-2 flex flex-col items-center justify-center mt-4">
                                <div class="w-[85%] h-44 border-[3px] border-dashed border-[#5C8A8A] bg-[#eef4f4] rounded-lg flex flex-col items-center justify-center cursor-pointer hover:bg-[#e4ecec] transition-colors relative overflow-hidden">
                                    <!-- Input File tersembunyi -->
                                    <input type="file" name="gambar" id="inputFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/jpeg,image/png,image/webp,image/jpg">

                                    <img id="imagePreview" alt="Pratinjau Gambar" class="hidden absolute inset-0 w-full h-full object-contain bg-[#eef4f4] z-[5]">

                                    <div class="relative">
                                        <i class="fa-regular fa-image text-5xl text-[#5C8A8A]"></i>
                                        <i class="fa-solid fa-plus absolute -top-1 -right-4 text-xl text-[#5C8A8A]"></i>
                                    </div>
                                    <span class="text-[#5C8A8A] font-bold mt-3 text-sm">Tambahkan gambar disini (opsional, maks 2 MB)</span>
                                </div>
                                @error('gambar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Tombol Simpan & Batal -->
                            <div class="col-span-2 flex justify-center mt-6">
                                <div class="bg-white p-2 rounded-xl btn-wrapper-shadow border border-gray-100 flex items-center gap-3">
                                    <button type="submit" class="bg-[#5CA6D6] hover:bg-blue-500 text-white font-bold py-2 px-8 rounded text-[15px] transition-colors shadow-sm">
                                        Simpan
                                    </button>

                                    <a href="{{ route('pengelola.detail-sampah') }}" class="bg-[#F26464] hover:bg-red-500 text-white font-bold py-2 px-8 rounded text-[15px] transition-colors shadow-sm inline-block text-center">
                                        Batal
                                    </a>
                                </div>
                            </div>

                        </div>
                    </form>

                </div>
            </div>

        </main>
    </div>

    <!-- JAVASCRIPT PRATINJAU GAMBAR -->
    <script>
        let currentPreviewUrl = null;
        const inputFile = document.getElementById('inputFile');
        const imagePreview = document.getElementById('imagePreview');

        if (inputFile && imagePreview) {
            inputFile.addEventListener('change', function () {
                const file = this.files && this.files[0];

                if (currentPreviewUrl) {
                    URL.revokeObjectURL(currentPreviewUrl);
                    currentPreviewUrl = null;
                }

                if (file) {
                    currentPreviewUrl = URL.createObjectURL(file);
                    imagePreview.src = currentPreviewUrl;
                    imagePreview.classList.remove('hidden');
                } else {
                    imagePreview.src = '';
                    imagePreview.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
