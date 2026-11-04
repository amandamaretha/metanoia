<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Metanoia</title>
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
                    <a href="{{ route('bendahara.transaksi') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-money-bill-transfer w-6"></i>
                        <span>Tambah Transaksi</span>
                    </a>
                    <a href="{{ route('bendahara.riwayat') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors">
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
            <div class="max-w-6xl mx-auto space-y-6">
                <!-- HEADER & SALDO -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h1 class="text-3xl font-extrabold text-black">Riwayat Transaksi Keuangan</h1>
                    <div class="bg-white px-5 py-3 rounded-xl shadow-sm border border-gray-200 text-right">
                        <span class="text-xs text-gray-500 font-semibold block uppercase">Saldo Kas Saat Ini</span>
                        <span class="text-xl font-bold text-gray-800">Rp {{ number_format($saldoKas, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- PENCARIAN & DOWNLOAD DATA -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
                    <form action="{{ route('bendahara.riwayat') }}" method="GET" class="flex items-center gap-3 w-full md:max-w-md">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Cari keterangan, jenis, nominal..."
                                class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                        </div>
                        <button type="submit" class="bg-[#5C8A8A] hover:bg-[#4a7272] text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                            Cari
                        </button>
                        @if($search)
                            <a href="{{ route('bendahara.riwayat') }}" class="text-xs text-gray-500 hover:text-gray-700 underline">Reset</a>
                        @endif
                    </form>

                    <div>
                        <a href="{{ route('bendahara.transaksi.download', ['search' => $search]) }}" class="inline-flex items-center gap-2 bg-[#6DC355] hover:bg-green-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition text-sm">
                            <i class="fa-solid fa-file-csv"></i> Download Data
                        </a>
                    </div>
                </div>

                <!-- TABEL RIWAYAT TRANSAKSI -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-700 font-bold">
                                    <th class="py-3.5 px-4 w-12 text-center">No</th>
                                    <th class="py-3.5 px-4">Tanggal</th>
                                    <th class="py-3.5 px-4">Jenis</th>
                                    <th class="py-3.5 px-4">Keterangan</th>
                                    <th class="py-3.5 px-4 text-right">Nominal (Rp)</th>
                                    <th class="py-3.5 px-4 text-center">Lampiran</th>
                                    <th class="py-3.5 px-4 text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($riwayatTransaksi as $index => $t)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="py-3 px-4 text-center text-gray-500">{{ $riwayatTransaksi->firstItem() + $index }}</td>
                                    <td class="py-3 px-4 text-gray-700">{{ $t->tanggal->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4">
                                        @if($t->jenis === 'pemasukan')
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-green-700 bg-green-100 px-2.5 py-1 rounded-full">
                                                <i class="fa-solid fa-arrow-down text-[10px]"></i> Pemasukan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-red-700 bg-red-100 px-2.5 py-1 rounded-full">
                                                <i class="fa-solid fa-arrow-up text-[10px]"></i> Pengeluaran
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-800">{{ $t->keterangan }}</td>
                                    <td class="py-3 px-4 text-right font-semibold text-gray-900">
                                        {{ number_format($t->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($t->lampiran)
                                            <a href="{{ route('bendahara.transaksi.lampiran', $t) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs font-semibold inline-flex items-center gap-1 underline">
                                                <i class="fa-solid fa-paperclip"></i> Lihat
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('bendahara.transaksi.edit', $t) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <button type="button" onclick="konfirmasiHapus('{{ $t->id }}', '{{ addslashes($t->keterangan) }}')" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500">
                                        Belum ada data transaksi yang tercatat.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($riwayatTransaksi->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $riwayatTransaksi->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- FORM DELETE TERSEMBUNYI -->
    <form id="formHapusTransaksi" action="" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div id="modalHapus" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-center">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Apakah anda yakin ingin menghapus transaksi ini?</h3>
            <p id="deskripsiHapus" class="text-xs text-gray-500 mb-6"></p>
            <div class="flex items-center justify-center gap-4">
                <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition w-28">
                    Tidak
                </button>
                <button type="button" onclick="eksekusiHapus()" class="px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow-sm transition w-28">
                    Ya
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL SUKSES (FLASH MESSAGE) -->
    @if(session('success'))
    <div id="modalSukses" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-center">
            <div class="w-14 h-14 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ session('success') }}</h3>
            <button type="button" onclick="tutupModalSukses()" class="w-full bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold py-2.5 px-4 rounded-lg text-sm transition mt-4">
                Ya
            </button>
        </div>
    </div>
    @endif

    <script>
        let deleteUrl = '';

        function konfirmasiHapus(id, keterangan) {
            deleteUrl = '{{ url('/bendahara/transaksi') }}/' + id;
            document.getElementById('deskripsiHapus').innerText = 'Keterangan: ' + keterangan;
            document.getElementById('modalHapus').classList.remove('hidden');
        }

        function tutupModalHapus() {
            document.getElementById('modalHapus').classList.add('hidden');
            deleteUrl = '';
        }

        function eksekusiHapus() {
            if (deleteUrl) {
                let form = document.getElementById('formHapusTransaksi');
                form.action = deleteUrl;
                form.submit();
            }
        }

        function tutupModalSukses() {
            let modal = document.getElementById('modalSukses');
            if (modal) modal.remove();
        }
    </script>
</body>
</html>
