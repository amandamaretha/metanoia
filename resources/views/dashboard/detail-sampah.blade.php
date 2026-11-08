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
                        <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
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
        </aside>

        <!-- MAIN CONTENT CONTAINER -->
        <main class="flex-1 overflow-hidden relative z-0 flex flex-col bg-[#EAEAEA]">

            <div class="bg-[#F3C546] px-10 pt-10 pb-8 rounded-xl shadow-sm w-full flex-shrink-0">
                <h1 class="text-4xl font-extrabold text-black mb-6">Detail Sampah</h1>
                <div class="flex gap-4">
                    <a href="{{ route('pengelola.detail-sampah.tambah') }}" class="bg-[#5C8A8A] hover:bg-[#4a7070] text-white font-bold py-2.5 px-6 rounded-md shadow-md transition-colors text-[15px] flex items-center gap-2 border-[1.5px] border-white">
                        <i class="fa-solid fa-circle-plus"></i> Tambah Produk
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="px-10 pt-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="px-10 pt-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- AREA GRID -->
            <div class="px-10 py-8 flex-1 overflow-y-auto">
                <div id="gridProdukContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @forelse ($jenisSampah as $sampah)
                        <div class="bg-[#CBE6E0] border-[1.5px] border-[#103D34] rounded-xl p-2.5 flex items-stretch gap-3 hover:shadow-md cursor-pointer transition-shadow h-28">
                            <div class="w-[90px] h-full bg-white rounded-lg flex-shrink-0 overflow-hidden border border-gray-300 flex items-center justify-center">
                                @if ($sampah->gambar)
                                    <img src="{{ asset('storage/' . $sampah->gambar) }}" alt="{{ $sampah->nama }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-[10px]">Gambar</div>
                                @endif
                            </div>
                            <div class="flex flex-col flex-1 justify-center items-center px-1 text-center">
                                <span class="font-extrabold text-[#103D34] text-xl leading-tight">{{ $sampah->kode }}</span>
                                <span class="font-bold text-[#103D34] text-[12px] leading-tight mb-1">{{ $sampah->nama }}</span>
                                <span class="text-[#103D34] text-[9px] leading-tight overflow-hidden text-ellipsis line-clamp-1 mb-1">{{ $sampah->deskripsi }}</span>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <a href="{{ route('pengelola.detail-sampah.ubah', $sampah->id) }}" class="bg-[#FF0000] hover:bg-red-700 text-white font-bold py-1 px-2.5 rounded shadow-sm transition-colors text-[10px] flex items-center gap-1 border-[1.5px] border-white">
                                        <i class="fa-solid fa-pen-to-square"></i> Ubah
                                    </a>
                                    <button type="button" onclick="hapusSampah('{{ route('pengelola.detail-sampah.destroy', $sampah->id) }}', '{{ addslashes($sampah->nama) }}')" class="bg-[#7F7F7F] hover:bg-gray-700 text-white font-bold py-1 px-2.5 rounded shadow-sm transition-colors text-[10px] flex items-center gap-1 border-[1.5px] border-white">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-gray-500 font-medium">
                            Belum ada data jenis sampah.
                        </div>
                    @endforelse
                </div>
            </div>

        </main>
    </div>

    <form id="deleteSampahForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function hapusSampah(actionUrl, nama) {
            if (confirm('Apakah anda yakin ingin menghapus produk "' + nama + '"?')) {
                const form = document.getElementById('deleteSampahForm');
                form.action = actionUrl;
                form.submit();
            }
        }
    </script>
</body>
</html>
