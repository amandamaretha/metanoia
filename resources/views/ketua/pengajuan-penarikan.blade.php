<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Penarikan - Metanoia</title>
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
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between flex-shrink-0">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <img src="https://i.pravatar.cc/150?img=60" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">{{ Auth::guard('petugas')->user()->nama ?? 'Ketua' }}</h2>
                    <span class="text-xs bg-white/20 px-3 py-1 rounded-full uppercase tracking-wider mt-1">Ketua</span>
                </div>

                <nav class="px-4 space-y-2">
                    <a href="{{ route('ketua.beranda') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-house w-6"></i>
                        <span>Beranda</span>
                    </a>
                    <a href="{{ route('ketua.pengajuan-penarikan') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors">
                        <i class="fa-solid fa-hand-holding-dollar w-6"></i>
                        <span>Pengajuan Penarikan</span>
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
            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-black">Daftar Pengajuan Penarikan</h1>
                    <p class="text-gray-600 text-sm mt-1">Kelola dan tinjau persetujuan permohonan penarikan saldo tabungan oleh nasabah.</p>
                </div>
            </div>

            <!-- NOTIFIKASI FLASH -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-xl shadow-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-green-600 text-xl"></i>
                    <p class="text-green-800 text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl shadow-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-amber-600 text-xl"></i>
                    <p class="text-amber-800 text-sm font-semibold">{{ session('warning') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-xmark text-red-600 text-xl"></i>
                    <p class="text-red-800 text-sm font-semibold">{{ session('error') }}</p>
                </div>
            @endif

            <!-- RINGKASAN KARTU (FASE 6: Ringkasan Pengajuan) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 uppercase">Total Pengajuan</span>
                        <div class="w-9 h-9 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center">
                            <i class="fa-solid fa-list-check text-sm"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900 mt-2">{{ $ringkasan['total'] }}</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-600 uppercase">Menunggu Persetujuan</span>
                        <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fa-solid fa-hourglass-half text-sm"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-amber-600 mt-2">{{ $ringkasan['menunggu'] }}</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-green-600 uppercase">Disetujui</span>
                        <div class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                            <i class="fa-solid fa-circle-check text-sm"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-green-600 mt-2">{{ $ringkasan['disetujui'] }}</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-red-600 uppercase">Ditolak</span>
                        <div class="w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                            <i class="fa-solid fa-circle-xmark text-sm"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-red-600 mt-2">{{ $ringkasan['ditolak'] }}</p>
                </div>
            </div>

            <!-- FILTER & PENCARIAN -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <form action="{{ route('ketua.pengajuan-penarikan') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto items-center flex-1">
                        <!-- Kolom Pencarian -->
                        <div class="relative w-full sm:w-80">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau NIK nasabah..."
                                class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:border-[#5C8A8A] font-medium">
                        </div>

                        <!-- Filter Status -->
                        <div class="w-full sm:w-56">
                            <select name="status" onchange="this.form.submit()"
                                class="w-full py-2.5 px-3 rounded-lg border border-gray-300 text-sm focus:outline-none focus:border-[#5C8A8A] font-medium text-gray-700">
                                <option value="" {{ empty($status) ? 'selected' : '' }}>Semua Status</option>
                                <option value="menunggu" {{ $status === 'menunggu' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                                <option value="disetujui" {{ $status === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="ditolak" {{ $status === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto justify-end">
                        <button type="submit" class="bg-[#5C8A8A] hover:bg-[#4a7070] text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-filter text-xs"></i>
                            <span>Terapkan</span>
                        </button>
                        @if ($search || $status)
                            <a href="{{ route('ketua.pengajuan-penarikan') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- TABEL DAFTAR PENGAJUAN -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase font-extrabold tracking-wider">
                                <th class="py-4 px-6">Tanggal</th>
                                <th class="py-4 px-6">Nasabah</th>
                                <th class="py-4 px-6">NIK</th>
                                <th class="py-4 px-6">Nominal Penarikan</th>
                                <th class="py-4 px-6">Saldo Saat Ini</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-800">
                            @forelse ($pengajuanList as $item)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="py-4 px-6 whitespace-nowrap text-gray-600 font-medium">
                                        {{ $item->tanggal_pengajuan->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $item->nasabah->nama }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->nasabah->no_telp }}</div>
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap font-mono text-xs text-gray-600">
                                        {{ $item->nasabah->masked_nik }}
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap font-bold text-emerald-700 text-base">
                                        Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap font-medium text-gray-800">
                                        Rp {{ number_format($item->nasabah->saldo, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap text-center">
                                        @if ($item->status === 'disetujui')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                <i class="fa-solid fa-circle-check mr-1.5"></i> Disetujui
                                            </span>
                                        @elseif ($item->status === 'ditolak')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                <i class="fa-solid fa-circle-xmark mr-1.5"></i> Ditolak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                <i class="fa-solid fa-hourglass-half mr-1.5"></i> Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap text-center">
                                        <a href="{{ route('ketua.pengajuan-penarikan.show', $item) }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#5C8A8A] hover:bg-[#4a7070] text-white rounded-lg text-xs font-bold shadow-sm transition-colors">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                            <span>Detail</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-500 font-medium">
                                        <i class="fa-solid fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                                        Belum ada data pengajuan penarikan yang sesuai kriteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($pengajuanList->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $pengajuanList->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
