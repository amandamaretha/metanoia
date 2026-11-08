<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Produk - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; background-color: #F3F4F6; }

        /* Custom input untuk icon pensil di kanan */
        .input-icon-wrapper { position: relative; width: 100%; }
        .input-icon-wrapper i {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            color: #9CA3AF; cursor: pointer; pointer-events: none;
        }
        .input-icon-wrapper textarea + i { top: 20px; }
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
                    <a href="{{ route('pengelola.penimbangan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-scale-balanced w-6"></i><span>Penimbangan</span>
                    </a>
                    <!-- Menu Detail Sampah Aktif -->
                    <a href="{{ route('pengelola.detail-sampah') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors shadow-sm">
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
        <main class="flex-1 overflow-y-auto relative z-0 flex flex-col items-center justify-center p-10 bg-[#EAEAEA]">

            @php
                $sampah = $jenisSampah ?? $sampah;
            @endphp

            <div class="w-full max-w-4xl flex flex-col">
                <h1 class="text-[32px] font-extrabold text-black mb-6">Ubah Produk</h1>

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

                <!-- Pembungkus Kartu Putih Utama -->
                <div class="bg-white rounded-xl shadow-md p-10 w-full min-h-[500px] border border-gray-200 flex justify-center items-center">

                    <form action="{{ route('pengelola.detail-sampah.update', $sampah->id) }}" method="POST" enctype="multipart/form-data" novalidate class="w-full max-w-3xl">
                        @csrf
                        @method('PUT')

                        <!-- Kartu Hijau Form Edit -->
                        <div class="bg-[#C2DCD6] border-2 border-[#a7c5be] rounded-[20px] p-8 w-full shadow-lg flex flex-col md:flex-row gap-8 relative pb-24">

                            <!-- Sisi Kiri: Gambar Produk -->
                            <div class="w-[220px] flex flex-col gap-2 flex-shrink-0 mx-auto">
                                <div class="w-[220px] h-[220px] bg-white rounded-xl flex items-center justify-center overflow-hidden border border-gray-300 shadow-sm p-2 relative">
                                    @if ($sampah->gambar)
                                        <img src="{{ asset('storage/' . $sampah->gambar) }}" alt="{{ $sampah->nama }}" class="w-full h-full object-cover rounded-lg">
                                    @else
                                        <div class="w-full h-full bg-black/90 rounded-lg flex flex-col items-center justify-center text-white">
                                            <i class="fa-solid fa-bucket text-6xl mb-2"></i>
                                            <span class="text-xs font-bold text-center">Gambar Produk</span>
                                        </div>
                                    @endif
                                    <!-- Input File tersembunyi untuk upload gambar baru -->
                                    <input type="file" name="gambar" id="editFile" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" title="Klik untuk ubah gambar" onchange="previewEditFileName(this)">
                                </div>
                                <span id="labelEditUpload" class="text-[11px] text-center text-gray-600">Klik gambar untuk ubah</span>
                            </div>

                            <!-- Sisi Kanan: Form Input -->
                            <div class="flex-1 flex flex-col space-y-4">

                                <!-- Nama Produk -->
                                <div class="flex items-center gap-4">
                                    <label class="w-24 text-gray-500 font-bold text-[13px] leading-tight">Nama<br>Produk</label>
                                    <div class="input-icon-wrapper">
                                        <input type="text" name="nama" id="editNama" required value="{{ old('nama', $sampah->nama) }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium text-sm shadow-sm">
                                        <i class="fa-solid fa-pen"></i>
                                    </div>
                                </div>

                                <!-- Deskripsi Produk -->
                                <div class="flex items-start gap-4">
                                    <label class="w-24 text-gray-500 font-bold text-[13px] leading-tight pt-2">Deskripsi<br>Produk</label>
                                    <div class="input-icon-wrapper">
                                        <textarea name="deskripsi" id="editDeskripsi" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium text-sm shadow-sm h-20 resize-none">{{ old('deskripsi', $sampah->deskripsi) }}</textarea>
                                        <i class="fa-solid fa-pen"></i>
                                    </div>
                                </div>

                                <!-- Kategori & Kode Produk -->
                                <div class="flex items-center gap-4">
                                    <label class="w-24 text-gray-500 font-bold text-[13px] leading-tight">Kategori</label>
                                    <div class="input-icon-wrapper w-36">
                                        <input type="text" name="kategori" id="editKategori" required value="{{ old('kategori', $sampah->kategori) }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium text-sm shadow-sm">
                                        <i class="fa-solid fa-pen"></i>
                                    </div>

                                    <label class="ml-2 w-16 text-gray-500 font-bold text-[13px] leading-tight text-center">Kode<br>Produk</label>
                                    <div class="input-icon-wrapper w-28">
                                        <input type="text" name="kode" id="editKode" required value="{{ old('kode', $sampah->kode) }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium text-sm shadow-sm">
                                        <i class="fa-solid fa-pen"></i>
                                    </div>
                                </div>

                                <!-- Harga 3 Kondisi -->
                                <div class="flex items-center gap-4">
                                    <label class="w-24 text-gray-500 font-bold text-[13px] leading-tight">Harga / Kg</label>
                                    <div class="grid grid-cols-3 gap-2 flex-1">
                                        <div>
                                            <span class="text-[11px] text-gray-600 block mb-0.5">Dipilah</span>
                                            <input type="text" name="harga_sudah_dipilah" required value="{{ old('harga_sudah_dipilah', $sampah->getHarga('sudah_dipilah')?->harga_per_kg ? number_format($sampah->getHarga('sudah_dipilah')->harga_per_kg, 0, ',', '.') : '') }}" placeholder="3.000" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium text-xs shadow-sm">
                                        </div>
                                        <div>
                                            <span class="text-[11px] text-gray-600 block mb-0.5">Belum</span>
                                            <input type="text" name="harga_belum_dipilah" required value="{{ old('harga_belum_dipilah', $sampah->getHarga('belum_dipilah')?->harga_per_kg ? number_format($sampah->getHarga('belum_dipilah')->harga_per_kg, 0, ',', '.') : '') }}" placeholder="2.500" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium text-xs shadow-sm">
                                        </div>
                                        <div>
                                            <span class="text-[11px] text-gray-600 block mb-0.5">Campuran</span>
                                            <input type="text" name="harga_campuran" required value="{{ old('harga_campuran', $sampah->getHarga('campuran')?->harga_per_kg ? number_format($sampah->getHarga('campuran')->harga_per_kg, 0, ',', '.') : '') }}" placeholder="2.000" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:border-[#5C8A8A] text-gray-700 font-medium text-xs shadow-sm">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Grup Tombol Aksi di Bawah Kanan Kartu Hijau -->
                            <div class="absolute bottom-6 right-8 flex items-center gap-3">
                                <a href="{{ route('pengelola.detail-sampah') }}" class="bg-[#FF0A0A] hover:bg-red-700 text-white font-bold py-1.5 px-8 rounded shadow-sm text-sm border border-red-500 transition-colors">
                                    Batal
                                </a>
                                <button type="submit" class="bg-[#59A835] hover:bg-green-600 text-white font-bold py-1.5 px-6 rounded shadow-sm text-sm border border-green-500 transition-colors">
                                    Simpan Perubahan
                                </button>
                            </div>

                        </div>
                    </form>

                </div>
            </div>

        </main>
    </div>

    <script>
        function previewEditFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('labelEditUpload').innerText = input.files[0].name;
            }
        }
    </script>
</body>
</html>
