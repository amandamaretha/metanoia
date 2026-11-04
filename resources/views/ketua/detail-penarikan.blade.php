<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan Penarikan #{{ $penarikan->id }} - Metanoia</title>
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
            <!-- BREADCRUMB & HEADER -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <a href="{{ route('ketua.pengajuan-penarikan') }}" class="text-xs font-bold text-[#5C8A8A] hover:underline flex items-center gap-1.5 mb-2">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Kembali ke Daftar Pengajuan</span>
                    </a>
                    <h1 class="text-3xl font-extrabold text-black">Detail Pengajuan Penarikan</h1>
                </div>

                <div>
                    @if ($penarikan->status === 'disetujui')
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-extrabold bg-green-100 text-green-800 shadow-sm border border-green-200">
                            <i class="fa-solid fa-circle-check mr-2"></i> Pengajuan Disetujui
                        </span>
                    @elseif ($penarikan->status === 'ditolak')
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-extrabold bg-red-100 text-red-800 shadow-sm border border-red-200">
                            <i class="fa-solid fa-circle-xmark mr-2"></i> Pengajuan Ditolak
                        </span>
                    @else
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-extrabold bg-amber-100 text-amber-800 shadow-sm border border-amber-200">
                            <i class="fa-solid fa-hourglass-half mr-2"></i> Menunggu Persetujuan
                        </span>
                    @endif
                </div>
            </div>

            <!-- NOTIFIKASI FLASH -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-xl shadow-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-green-600 text-xl"></i>
                    <div>
                        <p class="text-green-800 text-sm font-bold">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl shadow-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-amber-600 text-xl"></i>
                    <div>
                        <p class="text-amber-800 text-sm font-bold">{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-xmark text-red-600 text-xl"></i>
                    <div>
                        <p class="text-red-800 text-sm font-bold">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- CARD DETAIL PENGAJUAN -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="bg-gray-50 px-8 py-5 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="font-extrabold text-gray-800 text-lg">Informasi Pemohon & Saldo</h2>
                    <span class="text-xs text-gray-500 font-medium">Diajukan pada: {{ $penarikan->tanggal_pengajuan->format('d F Y, H:i') }}</span>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- KOLOM 1: DATA NASABAH -->
                    <div class="space-y-4">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase">Nama Lengkap Nasabah</span>
                            <p class="text-lg font-extrabold text-gray-900 mt-0.5">{{ $penarikan->nasabah->nama }}</p>
                        </div>

                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase">NIK (Nomor Induk Kependudukan)</span>
                            <p class="text-base font-bold font-mono text-gray-800 mt-0.5">{{ $penarikan->nasabah->masked_nik }}</p>
                            <span class="text-[11px] text-gray-400">* Sebagian digit disamarkan demi privasi data nasabah</span>
                        </div>

                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase">Nomor Telepon</span>
                            <p class="text-sm font-semibold text-gray-700 mt-0.5">{{ $penarikan->nasabah->no_telp }}</p>
                        </div>

                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase">Alamat</span>
                            <p class="text-sm text-gray-700 mt-0.5">{{ $penarikan->nasabah->alamat }}</p>
                        </div>
                    </div>

                    <!-- KOLOM 2: DATA KEUANGAN -->
                    <div class="space-y-5 bg-gray-50/70 p-6 rounded-xl border border-gray-100">
                        <div class="border-b border-gray-200 pb-4">
                            <span class="text-xs font-bold text-gray-500 uppercase">Nominal yang Diajukan</span>
                            <p class="text-3xl font-black text-emerald-700 mt-1">
                                Rp {{ number_format($penarikan->nominal, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Saldo Nasabah Saat Ini</span>
                            <p class="text-2xl font-extrabold text-gray-900 mt-1">
                                Rp {{ number_format($penarikan->nasabah->saldo, 0, ',', '.') }}
                            </p>
                            @if ($penarikan->nasabah->saldo < $penarikan->nominal && $penarikan->status === 'menunggu')
                                <div class="mt-2 text-xs font-bold text-red-600 bg-red-50 p-2.5 rounded-lg border border-red-200 flex items-center gap-1.5">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>Peringatan: Saldo nasabah saat ini kurang dari nominal yang diajukan.</span>
                                </div>
                            @endif
                        </div>

                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Tanggal Pengajuan</span>
                            <p class="text-sm font-medium text-gray-700 mt-0.5">
                                {{ $penarikan->tanggal_pengajuan->translatedFormat('l, d F Y - H:i:s') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- JIKA SUDAH DIPUTUSKAN -->
                @if ($penarikan->status !== 'menunggu')
                    <div class="bg-gray-50 px-8 py-6 border-t border-gray-200">
                        <h3 class="font-extrabold text-gray-800 text-sm uppercase mb-3">Informasi Keputusan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                            <div>
                                <span class="text-xs text-gray-500 block">Diputuskan Oleh</span>
                                <span class="font-bold text-gray-900">{{ $penarikan->petugas->nama ?? 'Ketua' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 block">Waktu Keputusan</span>
                                <span class="font-medium text-gray-800">
                                    {{ $penarikan->tanggal_keputusan ? $penarikan->tanggal_keputusan->format('d/m/Y H:i:s') : '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 block">Catatan Ketua</span>
                                <span class="font-medium text-gray-800 italic">{{ $penarikan->catatan ?? '(Tidak ada catatan)' }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <a href="{{ route('ketua.pengajuan-penarikan') }}" class="px-6 py-2.5 bg-[#5C8A8A] text-white rounded-lg text-sm font-bold shadow-sm hover:bg-[#4a7070] transition-colors inline-flex items-center gap-2">
                                <i class="fa-solid fa-check"></i>
                                <span>Selesai</span>
                            </a>
                        </div>
                    </div>
                @else
                    <!-- FORM KEPUTUSAN (SETUJUI / TOLAK) HANYA SEKALI -->
                    <div class="bg-gray-50 px-8 py-6 border-t border-gray-200">
                        <h3 class="font-extrabold text-gray-800 text-base mb-2">Formulir Keputusan Ketua</h3>
                        <p class="text-xs text-gray-500 mb-4">
                            Isi catatan bila perlu, kemudian tentukan apakah permohonan penarikan ini disetujui atau ditolak. Keputusan hanya dapat diambil sekali.
                        </p>

                        <div class="mb-5 max-w-2xl">
                            <label for="catatan" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                Catatan Ketua (Opsional)
                            </label>
                            <textarea id="catatanInput" rows="3" placeholder="Tuliskan catatan atau alasan (bila ada)..."
                                class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-[#5C8A8A] focus:outline-none"></textarea>
                        </div>

                        <div class="flex items-center gap-4">
                            <!-- Form Setujui -->
                            <form id="formSetujui" action="{{ route('ketua.pengajuan-penarikan.setujui', $penarikan) }}" method="POST" onsubmit="syncCatatan('formSetujui')">
                                @csrf
                                <input type="hidden" name="catatan" class="catatanHidden">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition-colors text-sm flex items-center gap-2">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Setujui</span>
                                </button>
                            </form>

                            <!-- Form Tolak -->
                            <form id="formTolak" action="{{ route('ketua.pengajuan-penarikan.tolak', $penarikan) }}" method="POST" onsubmit="syncCatatan('formTolak')">
                                @csrf
                                <input type="hidden" name="catatan" class="catatanHidden">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition-colors text-sm flex items-center gap-2">
                                    <i class="fa-solid fa-xmark"></i>
                                    <span>Tolak</span>
                                </button>
                            </form>

                            <a href="{{ route('ketua.pengajuan-penarikan') }}" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-sm font-bold transition-colors">
                                Kembali
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <script>
        function syncCatatan(formId) {
            const val = document.getElementById('catatanInput').value;
            const form = document.getElementById(formId);
            form.querySelector('.catatanHidden').value = val;
        }
    </script>
</body>
</html>
