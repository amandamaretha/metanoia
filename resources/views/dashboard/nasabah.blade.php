<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nasabah - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }
        .bg-table-header { background-color: #A3C2BB; }
    </style>
</head>
<body class="bg-gray-200 h-screen font-sans relative">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10">
            <div>
                <!-- Profile Section -->
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">{{ Auth::guard('petugas')->user()->nama ?? 'Pengelola' }}</h2>
                </div>

                <!-- Navigation Menu -->
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

                    <a href="{{ route('pengelola.penimbangan') }}"
                       class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.penimbangan') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
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
        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-extrabold text-black mb-8">Data Nasabah</h1>

            <!-- PENCARIAN NASABAH -->
            <form method="GET" action="{{ route('pengelola.nasabah') }}" class="flex items-center gap-3 mb-6">
                <div class="relative w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="cari" value="{{ $cari ?? '' }}" placeholder="Cari nama nasabah disini"
                        class="w-full pl-9 pr-4 py-2 rounded-md bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm">
                </div>
                <button type="submit" class="bg-[#5FA8DC] hover:bg-blue-600 text-white px-8 py-2 rounded-md text-sm font-semibold transition">
                    Cari
                </button>
            </form>

            <!-- TABEL DATA NASABAH -->
            <div class="bg-gray-100 border border-gray-300 rounded-lg p-4 shadow-sm overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-table-header text-black font-bold">
                            <th class="py-3 px-4 border border-gray-300 text-center w-12">No</th>
                            <th class="py-3 px-4 border border-gray-300">Nama Nasabah</th>
                            <th class="py-3 px-4 border border-gray-300">No. Telp</th>
                            <th class="py-3 px-4 border border-gray-300">Alamat</th>
                            <th class="py-3 px-4 border border-gray-300">Saldo</th>
                            <th class="py-3 px-4 border border-gray-300 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        @forelse ($nasabahList as $item)
                        <tr class="hover:bg-gray-200/50 transition">
                            <td class="py-3 px-4 border border-gray-300 text-center font-medium">{{ method_exists($nasabahList, 'firstItem') && $nasabahList->firstItem() ? ($nasabahList->firstItem() + $loop->index) : $loop->iteration }}</td>
                            <td class="py-3 px-4 border border-gray-300 font-medium">{{ $item->nama ?? $item['nama'] }}</td>
                            <td class="py-3 px-4 border border-gray-300">{{ $item->no_telp ?? $item['telp'] }}</td>
                            <td class="py-3 px-4 border border-gray-300">{{ $item->alamat ?? $item['alamat'] }}</td>
                            <td class="py-3 px-4 border border-gray-300 font-semibold">{{ is_numeric($item->saldo ?? null) ? 'Rp ' . number_format($item->saldo, 0, ',', '.') : ($item['saldo'] ?? '0') }}</td>
                            <td class="py-3 px-4 border border-gray-300 text-center">
                                <div class="flex justify-center space-x-2">
                                    <!-- Tombol Edit diarahkan ke halaman Edit -->
                                    <a href="{{ route('pengelola.nasabah.edit', $item->id ?? $item['id']) }}" class="bg-[#5FA8DC] hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-semibold inline-block">Edit</a>
                                    <!-- Tombol Hapus memicu fungsi openDeleteModal() di JavaScript -->
                                    <button type="button" onclick="openDeleteModal('{{ $item->id ?? $item['id'] }}', '{{ addslashes($item->nama ?? $item['nama']) }}')" class="bg-[#F86363] hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 px-4 text-center text-gray-500 font-medium">Tidak ada data nasabah.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if (method_exists($nasabahList, 'hasPages') && $nasabahList->hasPages())
                    <div class="mt-4 px-2">
                        {{ $nasabahList->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL KONFIRMASI HAPUS (Hidden by default)     -->
    <!-- ============================================== -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <!-- Backdrop transparan gelap -->
        <div class="absolute inset-0 bg-black/40 transition-opacity" onclick="closeDeleteModal()"></div>

        <!-- Konten Modal -->
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden transform transition-all">
            <!-- Header Modal warna hijau (mengikuti gambar referensi) -->
            <div class="h-12 bg-[#5C8A8A]"></div>

            <div class="px-8 pb-10 pt-6 text-center">
                <!-- Icon Tanda Seru Merah -->
                <div class="flex justify-center mb-6">
                    <div class="w-28 h-28 border-[6px] border-[#F86363] rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-exclamation text-7xl text-[#F86363]"></i>
                    </div>
                </div>

                <!-- Judul -->
                <h2 class="text-[32px] font-bold text-[#F86363] mb-4">Konfirmasi Hapus</h2>

                <!-- Teks Dinamis -->
                <p class="text-gray-600 text-xl mb-10">Apakah anda yakin ingin menghapus akun "<span id="modalNasabahName"></span>"?</p>

                <!-- Tombol -->
                <div class="flex justify-center gap-6">
                    <!-- Tombol Ya (Warna Biru) -->
                    <form id="deleteNasabahForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-[#5FA8DC] hover:bg-blue-600 text-white font-bold py-3 w-40 rounded-lg shadow-md transition-colors text-xl">
                            Ya
                        </button>
                    </form>
                    <!-- Tombol Tidak (Warna Abu-abu) untuk menutup modal -->
                    <button type="button" onclick="closeDeleteModal()" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-3 w-40 rounded-lg shadow-md transition-colors text-xl">
                        Tidak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script JavaScript untuk memunculkan/menyembunyikan Modal -->
    <script>
        function openDeleteModal(id, namaNasabah) {
            if (namaNasabah !== undefined) {
                document.getElementById('modalNasabahName').innerText = namaNasabah;
                document.getElementById('deleteNasabahForm').action = '/pengelola/nasabah/' + id;
            } else {
                document.getElementById('modalNasabahName').innerText = id;
            }
            // Tampilkan modal
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            // Sembunyikan modal
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</body>
</html>
