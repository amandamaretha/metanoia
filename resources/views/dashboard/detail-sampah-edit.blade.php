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

            <div class="w-full max-w-4xl flex flex-col">
                <h1 class="text-[32px] font-extrabold text-black mb-6">Ubah Produk</h1>

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <p class="font-bold">Periksa kembali isian Anda.</p>
                    </div>
                @endif

                <!-- Pembungkus Kartu Putih Utama -->
                <div class="bg-white rounded-xl shadow-md p-10 w-full min-h-[500px] border border-gray-200 flex justify-center items-center">

                    <!-- Kartu Hijau Form Edit -->
                    <form action="{{ route('pengelola.detail-sampah.update', $jenisSampah->id) }}" method="POST" enctype="multipart/form-data" novalidate class="bg-[#C2DCD6] border-2 border-[#a7c5be] rounded-[20px] p-8 w-full max-w-3xl shadow-lg flex flex-col md:flex-row gap-8 relative pb-20">
                        @csrf
                        @method('PUT')

                        <!-- Sisi Kiri: Gambar Produk -->
                        <div class="flex flex-col items-center flex-shrink-0">
                            <div class="w-[220px] h-[220px] bg-white rounded-xl flex items-center justify-center overflow-hidden border border-gray-300 shadow-sm p-2 relative">
                                <img id="editImagePreview" src="{{ $jenisSampah->gambar ? asset('storage/' . $jenisSampah->gambar) : '' }}" alt="{{ $jenisSampah->nama }}" class="{{ $jenisSampah->gambar ? '' : 'hidden' }} w-full h-full object-cover rounded-lg">
                                <div id="editImagePlaceholder" class="{{ $jenisSampah->gambar ? 'hidden' : '' }} w-full h-full bg-black/90 rounded-lg flex flex-col items-center justify-center text-white">
                                    <i class="fa-solid fa-bucket text-6xl mb-2"></i>
                                    <span class="text-xs font-bold text-center">Gambar Produk</span>
                                </div>
                            </div>
                            <label class="mt-3 text-xs bg-[#5C8A8A] text-white px-3 py-1.5 rounded cursor-pointer hover:bg-[#4a7070] inline-block font-semibold shadow-sm transition-colors">
                                <i class="fa-solid fa-camera mr-1"></i> Ganti Gambar
                                <input type="file" name="gambar" id="editImageInput" class="hidden" accept="image/jpeg,image/png,image/webp,image/jpg">
                            </label>
                            @error('gambar') <p class="text-red-600 text-xs mt-1 text-center">{{ $message }}</p> @enderror
                        </div>

                        <!-- Sisi Kanan: Form Input -->
                        <div class="flex-1 flex flex-col space-y-3">

                            <!-- Nama Produk -->
                            <div class="flex items-center gap-4">
                                <label class="w-28 text-gray-700 font-bold text-[13px] leading-tight">Nama<br>Produk</label>
                                <div class="input-icon-wrapper flex-1">
                                    <input type="text" name="nama" id="editNama" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-800 font-medium text-sm shadow-sm" value="{{ old('nama', $jenisSampah->nama) }}">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>
                            @error('nama') <p class="text-red-600 text-xs pl-32">{{ $message }}</p> @enderror

                            <!-- Kode Produk -->
                            <div class="flex items-center gap-4">
                                <label class="w-28 text-gray-700 font-bold text-[13px] leading-tight">Kode<br>Produk</label>
                                <div class="input-icon-wrapper flex-1">
                                    <input type="text" name="kode" id="editKode" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-800 font-medium text-sm shadow-sm" value="{{ old('kode', $jenisSampah->kode) }}">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>
                            @error('kode') <p class="text-red-600 text-xs pl-32">{{ $message }}</p> @enderror

                            <!-- Kategori Produk -->
                            <div class="flex items-center gap-4">
                                <label class="w-28 text-gray-700 font-bold text-[13px] leading-tight">Kategori<br>Produk</label>
                                <div class="input-icon-wrapper flex-1">
                                    <input type="text" name="kategori" id="editKategori" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-800 font-medium text-sm shadow-sm" value="{{ old('kategori', $jenisSampah->kategori) }}">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>
                            @error('kategori') <p class="text-red-600 text-xs pl-32">{{ $message }}</p> @enderror

                            <!-- Deskripsi Produk -->
                            <div class="flex items-start gap-4">
                                <label class="w-28 text-gray-700 font-bold text-[13px] leading-tight pt-2">Deskripsi<br>Produk</label>
                                <div class="input-icon-wrapper flex-1">
                                    <textarea name="deskripsi" id="editDeskripsi" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-800 font-medium text-sm shadow-sm h-20 resize-none">{{ old('deskripsi', $jenisSampah->deskripsi) }}</textarea>
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>
                            @error('deskripsi') <p class="text-red-600 text-xs pl-32">{{ $message }}</p> @enderror

                            @php
                                $hargaSudah = $jenisSampah->getHarga('sudah_dipilah')?->harga_per_kg;
                                $hargaBelum = $jenisSampah->getHarga('belum_dipilah')?->harga_per_kg;
                                $hargaCampur = $jenisSampah->getHarga('campuran')?->harga_per_kg;
                            @endphp

                            <!-- Harga Sudah Dipilah -->
                            <div class="flex items-center gap-4">
                                <label class="w-28 text-gray-700 font-bold text-[13px] leading-tight">Sudah Dipilah<br><span class="text-[11px] font-normal text-gray-600">(Rp/kg)</span></label>
                                <div class="input-icon-wrapper flex-1">
                                    <input type="text" name="harga_sudah_dipilah" id="editHargaSudah" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-800 font-medium text-sm shadow-sm" value="{{ old('harga_sudah_dipilah', $hargaSudah ? number_format($hargaSudah, 0, ',', '.') : '') }}">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>
                            @error('harga_sudah_dipilah') <p class="text-red-600 text-xs pl-32">{{ $message }}</p> @enderror

                            <!-- Harga Belum Dipilah -->
                            <div class="flex items-center gap-4">
                                <label class="w-28 text-gray-700 font-bold text-[13px] leading-tight">Belum Dipilah<br><span class="text-[11px] font-normal text-gray-600">(Rp/kg)</span></label>
                                <div class="input-icon-wrapper flex-1">
                                    <input type="text" name="harga_belum_dipilah" id="editHargaBelum" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-800 font-medium text-sm shadow-sm" value="{{ old('harga_belum_dipilah', $hargaBelum ? number_format($hargaBelum, 0, ',', '.') : '') }}">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>
                            @error('harga_belum_dipilah') <p class="text-red-600 text-xs pl-32">{{ $message }}</p> @enderror

                            <!-- Harga Campuran -->
                            <div class="flex items-center gap-4">
                                <label class="w-28 text-gray-700 font-bold text-[13px] leading-tight">Campuran<br><span class="text-[11px] font-normal text-gray-600">(Rp/kg)</span></label>
                                <div class="input-icon-wrapper flex-1">
                                    <input type="text" name="harga_campuran" id="editHargaCampuran" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-800 font-medium text-sm shadow-sm" value="{{ old('harga_campuran', $hargaCampur ? number_format($hargaCampur, 0, ',', '.') : '') }}">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>
                            @error('harga_campuran') <p class="text-red-600 text-xs pl-32">{{ $message }}</p> @enderror

                        </div>

                        <!-- Grup Tombol Aksi di Bawah Kanan Kartu Hijau -->
                        <div class="absolute bottom-6 right-8 flex items-center gap-3">
                            <a href="{{ route('pengelola.detail-sampah') }}" class="bg-[#FF0A0A] hover:bg-red-700 text-white font-bold py-1.5 px-8 rounded shadow-sm text-sm border border-red-500 transition-colors inline-block text-center">
                                Batal
                            </a>
                            <button type="submit" class="bg-[#59A835] hover:bg-green-600 text-white font-bold py-1.5 px-6 rounded shadow-sm text-sm border border-green-500 transition-colors">
                                Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>

    <!-- JAVASCRIPT PRATINJAU GAMBAR -->
    <script>
        const imageInput = document.getElementById('editImageInput');
        const imagePreview = document.getElementById('editImagePreview');
        const imagePlaceholder = document.getElementById('editImagePlaceholder');

        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imagePreview.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                        if (imagePlaceholder) imagePlaceholder.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
</body>
</html>
