<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun Petugas - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }
    </style>
</head>
<body class="bg-[#EAEAEA] h-screen font-sans relative">

    <div class="flex h-screen overflow-hidden">
        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10 pb-6">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
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
                    <a href="{{ route('pengelola.detail-sampah') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
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
                <a href="{{ route('pengelola.pengaturan') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors">
                    <i class="fa-solid fa-gear w-6"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-10 overflow-y-auto relative z-0 flex flex-col">
            <div class="max-w-5xl mx-auto w-full space-y-6">

                <!-- HEADER -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('pengelola.pengaturan') }}" class="text-gray-600 hover:text-black transition">
                            <i class="fa-solid fa-arrow-left text-2xl"></i>
                        </a>
                        <h1 class="text-3xl font-extrabold text-black">Kelola Akun Petugas</h1>
                    </div>
                    <a href="{{ route('pengelola.petugas.tambah') }}" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-2.5 px-6 rounded-lg text-sm shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Tambah Petugas
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span class="font-bold">Peringatan:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- PENCARIAN -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between">
                    <form action="{{ route('pengelola.petugas.index') }}" method="GET" class="flex items-center gap-3 w-full max-w-md">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama petugas..."
                                class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm">
                        </div>
                        <button type="submit" class="bg-[#5C8A8A] hover:bg-[#4a7272] text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                            Cari
                        </button>
                        @if($search)
                            <a href="{{ route('pengelola.petugas.index') }}" class="text-xs text-gray-500 hover:text-gray-700 underline">Reset</a>
                        @endif
                    </form>
                </div>

                <!-- TABEL AKUN PETUGAS -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-700 font-bold">
                                    <th class="py-3.5 px-4 w-12 text-center">No</th>
                                    <th class="py-3.5 px-4">Nama Petugas</th>
                                    <th class="py-3.5 px-4">Nomor Telepon</th>
                                    <th class="py-3.5 px-4">Jabatan</th>
                                    <th class="py-3.5 px-4 text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($petugasList as $index => $p)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 text-center text-gray-500">{{ $petugasList->firstItem() + $index }}</td>
                                    <td class="py-3.5 px-4 font-semibold text-gray-800">
                                        {{ $p->nama }}
                                        @if($p->id === Auth::guard('petugas')->id())
                                            <span class="ml-2 text-[10px] bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded-full">Akun Anda</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600">{{ $p->no_telp }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="text-xs font-bold px-2.5 py-1 rounded-full capitalize
                                            {{ $p->jabatan === 'pengelola' ? 'bg-purple-100 text-purple-700' : ($p->jabatan === 'bendahara' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                            {{ $p->jabatan === 'ketua' ? 'Ketua Bank Sampah' : $p->jabatan }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('pengelola.petugas.edit', $p) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <button type="button" onclick="konfirmasiHapus('{{ $p->id }}', '{{ addslashes($p->nama) }}')" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        Tidak ada akun petugas yang ditemukan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($petugasList->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $petugasList->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </main>
    </div>

    <!-- FORM DELETE TERSEMBUNYI -->
    <form id="formHapusPetugas" action="" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <!-- MODAL KONFIRMASI HAPUS AKUN -->
    <div id="modalHapusPetugas" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-center">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2" id="judulKonfirmasiHapus">
                Apakah anda yakin ingin menghapus akun ini?
            </h3>
            <p class="text-xs text-gray-500 mb-6">Akun akan dinonaktifkan (soft delete) dan tidak dapat login ke sistem.</p>
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

    <script>
        let deleteUrl = '';

        function konfirmasiHapus(id, nama) {
            deleteUrl = '{{ url('/pengelola/pengaturan/kelola-akun') }}/' + id;
            document.getElementById('judulKonfirmasiHapus').innerText = "Apakah anda yakin ingin menghapus akun '" + nama + "'?";
            document.getElementById('modalHapusPetugas').classList.remove('hidden');
        }

        function tutupModalHapus() {
            document.getElementById('modalHapusPetugas').classList.add('hidden');
            deleteUrl = '';
        }

        function eksekusiHapus() {
            if (deleteUrl) {
                let form = document.getElementById('formHapusPetugas');
                form.action = deleteUrl;
                form.submit();
            }
        }
    </script>
</body>
</html>
