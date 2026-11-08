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
                        <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
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

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-lg bg-red-100 border border-red-300 text-red-700 text-sm font-semibold">
                        Periksa kembali isian Anda.
                        <ul class="mt-2 list-disc list-inside font-normal">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Kartu Form -->
                <div class="bg-white rounded-xl shadow-md p-10 w-full min-h-[580px] border border-gray-200">

                    <form action="{{ route('pengelola.detail-sampah.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        <div class="grid grid-cols-[160px_1fr] gap-y-6 items-start w-full pr-4">

                            <!-- Nama Produk -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Nama Produk</label>
                            <input type="text" name="nama" id="inputNama" required value="{{ old('nama') }}" class="w-full border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm">

                            <!-- Deskripsi Produk -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Deskripsi Produk</label>
                            <textarea name="deskripsi" id="inputDeskripsi" class="w-full border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm h-32 resize-none">{{ old('deskripsi') }}</textarea>

                            <!-- Kategori & Kode Produk -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Kategori Produk</label>
                            <div class="flex items-center justify-between gap-6">
                                <select name="kategori" id="inputKategori" required class="w-1/2 border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm">
                                    <option value="">Pilih Kategori</option>
                                    <option value="Plastik" {{ old('kategori') == 'Plastik' ? 'selected' : '' }}>Plastik</option>
                                    <option value="Kertas" {{ old('kategori') == 'Kertas' ? 'selected' : '' }}>Kertas</option>
                                    <option value="Logam" {{ old('kategori') == 'Logam' ? 'selected' : '' }}>Logam</option>
                                    <option value="Kaca" {{ old('kategori') == 'Kaca' ? 'selected' : '' }}>Kaca</option>
                                    <option value="Lainnya" {{ old('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>

                                <label class="text-gray-500 font-bold text-[15px] whitespace-nowrap">Kode Produk</label>
                                <input type="text" name="kode" id="inputKode" required value="{{ old('kode') }}" class="w-1/2 border-2 border-gray-300 rounded-md px-4 py-2.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm">
                            </div>

                            <!-- Harga Produk 3 Kondisi -->
                            <label class="text-gray-500 font-bold text-[15px] pt-3">Harga / Kg</label>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="text-xs text-gray-500 font-semibold block mb-1">Sudah Dipilah</label>
                                    <input type="text" name="harga_sudah_dipilah" id="inputHargaSudahDipilah" required value="{{ old('harga_sudah_dipilah') }}" placeholder="Contoh: 3.000" class="w-full border-2 border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 font-semibold block mb-1">Belum Dipilah</label>
                                    <input type="text" name="harga_belum_dipilah" id="inputHargaBelumDipilah" required value="{{ old('harga_belum_dipilah') }}" placeholder="Contoh: 2.500" class="w-full border-2 border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 font-semibold block mb-1">Campuran</label>
                                    <input type="text" name="harga_campuran" id="inputHargaCampuran" required value="{{ old('harga_campuran') }}" placeholder="Contoh: 2.000" class="w-full border-2 border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium shadow-sm">
                                </div>
                            </div>

                            <!-- Area Upload Gambar (Garis Putus-putus) -->
                            <div class="col-span-2 flex justify-center mt-6">
                                <div class="w-[85%] h-48 border-[3px] border-dashed border-[#5C8A8A] bg-[#eef4f4] rounded-lg flex flex-col items-center justify-center cursor-pointer hover:bg-[#e4ecec] transition-colors relative">
                                    <!-- Input File tersembunyi -->
                                    <input type="file" name="gambar" id="inputFile" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this)">

                                    <div class="relative">
                                        <i class="fa-regular fa-image text-6xl text-[#5C8A8A]"></i>
                                        <i class="fa-solid fa-plus absolute -top-1 -right-4 text-2xl text-[#5C8A8A]"></i>
                                    </div>
                                    <span id="labelUpload" class="text-[#5C8A8A] font-bold mt-4">Tambahkan gambar disini</span>
                                </div>
                            </div>

                            <!-- Tombol Simpan & Batal -->
                            <div class="col-span-2 flex justify-center mt-6">
                                <!-- Pembungkus Putih Berbayang -->
                                <div class="bg-white p-2 rounded-xl btn-wrapper-shadow border border-gray-100 flex items-center gap-3">
                                    <button type="submit" class="bg-[#5CA6D6] hover:bg-blue-500 text-white font-bold py-2 px-8 rounded text-[15px] transition-colors shadow-sm">
                                        Simpan
                                    </button>

                                    <a href="{{ route('pengelola.detail-sampah') }}" class="bg-[#F26464] hover:bg-red-500 text-white font-bold py-2 px-8 rounded text-[15px] transition-colors shadow-sm text-center inline-block">
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

    <script>
        function previewFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('labelUpload').innerText = input.files[0].name;
            }
        }
    </script>
</body>
</html>
