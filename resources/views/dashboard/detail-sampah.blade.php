<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Sampah - Metanoia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-[#EAEAEA] h-screen font-sans relative">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10 pb-6 shadow-xl flex-shrink-0">
            <!-- (Isi sidebar sama seperti sebelumnya, lewati untuk keringkasan atau tetap copy paste) -->
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <img src="{{ Auth::guard('petugas')->user()?->foto ? (filter_var(Auth::guard('petugas')->user()->foto, FILTER_VALIDATE_URL) ? Auth::guard('petugas')->user()->foto : asset(Auth::guard('petugas')->user()->foto)) : 'https://i.pravatar.cc/150?img=12' }}" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">{{ Auth::guard('petugas')->user()->nama ?? 'Pengelola' }}</h2>
                </div>
                <nav class="px-4 space-y-2">
                    <a href="{{ route('pengelola.dashboard') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors"><i class="fa-solid fa-house w-6"></i><span>Dashboard</span></a>
                    <a href="{{ route('pengelola.nasabah') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors"><i class="fa-solid fa-users w-6"></i><span>Nasabah</span></a>
                    <a href="{{ route('pengelola.transaksi') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors"><i class="fa-solid fa-money-bill-transfer w-6"></i><span>Transaksi</span></a>
                    <a href="{{ route('pengelola.penimbangan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors"><i class="fa-solid fa-scale-balanced w-6"></i><span>Penimbangan</span></a>
                    <!-- Aktif -->
                    <a href="{{ route('pengelola.detail-sampah') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors"><i class="fa-solid fa-trash w-6"></i><span>Detail Sampah</span></a>
                    <a href="{{ route('pengelola.laporan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors"><i class="fa-solid fa-file-lines w-6"></i><span>Laporan</span></a>
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

            <div class="bg-[#F3C546] px-10 pt-10 pb-8 rounded-xl shadow-sm w-full flex-shrink-0">
                <h1 class="text-4xl font-extrabold text-black mb-6">Detail Sampah</h1>

                @if (session('success'))
                    <div class="mb-4 bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <div class="flex gap-4 items-center">
                    <a href="{{ route('pengelola.detail-sampah.tambah') }}" class="bg-[#5C8A8A] hover:bg-[#4a7070] text-white font-bold py-2.5 px-6 rounded-md shadow-md transition-colors text-[15px] flex items-center gap-2 border-[1.5px] border-white">
                        <i class="fa-solid fa-circle-plus"></i> Tambah Produk
                    </a>
                    @if ($modeUbah ?? false)
                        <a href="{{ route('pengelola.detail-sampah') }}" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-md shadow-md transition-colors text-[15px] flex items-center gap-2 border-[1.5px] border-white">
                            <i class="fa-solid fa-xmark"></i> Batal Ubah
                        </a>
                        <span class="text-sm font-semibold text-black bg-white/70 px-3 py-1.5 rounded-md border border-black/10">
                            Mode Ubah Aktif: Klik salah satu produk di bawah untuk mengubah datanya
                        </span>
                    @else
                        <a href="{{ route('pengelola.detail-sampah', ['mode' => 'ubah']) }}" class="bg-[#FF0000] hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-md shadow-md transition-colors text-[15px] flex items-center gap-2 border-[1.5px] border-white">
                            <i class="fa-solid fa-pen-to-square"></i> Ubah Produk
                        </a>
                    @endif
                </div>
            </div>

            <!-- AREA GRID PRODUK DARI SERVER -->
            <div class="px-10 py-8 flex-1 overflow-y-auto">
                <div id="gridProdukContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @forelse ($jenisSampah as $item)
                        @php
                            $targetUrl = ($modeUbah ?? false)
                                ? route('pengelola.detail-sampah.ubah', $item->id)
                                : 'javascript:void(0)';
                        @endphp
                        <a href="{{ ($modeUbah ?? false) ? route('pengelola.detail-sampah.ubah', $item->id) : route('pengelola.detail-sampah.ubah', $item->id) }}"
                           class="bg-[#CBE6E0] border-[1.5px] border-[#103D34] rounded-xl p-2.5 flex items-stretch gap-3 hover:shadow-md cursor-pointer transition-shadow h-28 relative {{ ($modeUbah ?? false) ? 'ring-2 ring-red-500 hover:border-red-600' : '' }}">
                            @if ($modeUbah ?? false)
                                <span class="absolute top-1 right-2 text-xs bg-red-600 text-white px-2 py-0.5 rounded-full font-bold">
                                    <i class="fa-solid fa-pen text-[10px]"></i> Ubah
                                </span>
                            @endif
                            <div class="w-[90px] h-full bg-white rounded-lg flex-shrink-0 overflow-hidden border border-gray-300 flex items-center justify-center">
                                @if ($item->gambar)
                                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-[10px]">Gambar</div>
                                @endif
                            </div>
                            <div class="flex flex-col flex-1 justify-center items-center px-1 text-center">
                                <span class="font-extrabold text-[#103D34] text-xl leading-tight">{{ $item->kode }}</span>
                                <span class="font-bold text-[#103D34] text-[12px] leading-tight mb-1">{{ $item->nama }}</span>
                                <span class="text-[#103D34] text-[9px] leading-tight overflow-hidden text-ellipsis line-clamp-3">{{ $item->deskripsi }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-500">
                            <i class="fa-solid fa-box-open text-4xl mb-3 block text-gray-400"></i>
                            <p class="font-semibold">Belum ada data produk sampah.</p>
                            <p class="text-sm mt-1">Silakan klik tombol "Tambah Produk" untuk menambahkan jenis sampah baru.</p>
                        </div>
                    @endforelse
                </div>
            </div>
</body>
</html>
