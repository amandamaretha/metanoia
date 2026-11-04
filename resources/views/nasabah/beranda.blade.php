<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Nasabah - Metanoia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-metanoia { background-color: #5C8A8A; }
        .text-metanoia { color: #5C8A8A; }
    </style>
</head>
<body class="bg-[#F4F6F6] min-h-screen font-sans flex flex-col">
    <!-- NAVBAR -->
    <header class="bg-metanoia text-white shadow-md">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-bold text-lg">
                    M
                </div>
                <div>
                    <h1 class="text-xl font-bold leading-tight">Bank Sampah Metanoia</h1>
                    <p class="text-xs text-white/80">Portal Nasabah</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium hidden sm:inline">{{ Auth::guard('nasabah')->user()->nama ?? 'Nasabah' }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-6 md:p-8">
        <!-- NOTIFIKASI FLASH -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-xl mt-0.5"></i>
                <div>
                    <p class="font-bold text-emerald-900 text-sm">Berhasil</p>
                    <p class="text-emerald-800 text-sm mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-600 text-xl mt-0.5"></i>
                <div>
                    <p class="font-bold text-red-900 text-sm">Perhatian</p>
                    <p class="text-red-800 text-sm mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl mt-0.5"></i>
                    <div>
                        <p class="font-bold text-red-900 text-sm">Gagal Memproses Permintaan:</p>
                        <ul class="list-disc list-inside text-red-800 text-sm mt-1 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- WELCOME CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <p class="text-sm text-gray-500 font-medium">Selamat Datang,</p>
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mt-1">
                    {{ Auth::guard('nasabah')->user()->nama ?? 'Nasabah Metanoia' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    <i class="fa-solid fa-phone text-xs mr-1 text-metanoia"></i> {{ Auth::guard('nasabah')->user()->no_telp ?? '-' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    <i class="fa-solid fa-id-card text-xs mr-1"></i> NIK: {{ Auth::guard('nasabah')->user()->masked_nik ?? '-' }}
                </p>
            </div>

            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-6 py-4 w-full md:w-auto text-left md:text-right">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Saldo Tabungan</span>
                <p class="text-2xl md:text-3xl font-black text-emerald-700 mt-1">
                    Rp {{ number_format(Auth::guard('nasabah')->user()->saldo ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- FORM PENGAJUAN PENARIKAN (FASE 6) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8 mb-8">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
                <div>
                    <h3 class="text-xl font-extrabold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar text-amber-500"></i>
                        <span>Pengajuan Penarikan Saldo</span>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Masukkan nominal saldo tabungan yang ingin Anda tarik. Pengajuan akan ditinjau dan diputuskan oleh Ketua.
                    </p>
                </div>
            </div>

            @if (!empty($pengajuanMenunggu))
                <!-- Peringatan jika sudah ada pengajuan yang sedang menunggu -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-4 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 text-lg">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-amber-900 text-base">Pengajuan Penarikan Sedang Menunggu Persetujuan</h4>
                        <p class="text-sm text-amber-800 mt-1">
                            Anda telah mengajukan penarikan sebesar <strong class="font-extrabold">Rp {{ number_format($pengajuanMenunggu->nominal, 0, ',', '.') }}</strong> pada tanggal {{ $pengajuanMenunggu->tanggal_pengajuan->format('d/m/Y H:i') }}.
                        </p>
                        <p class="text-xs text-amber-700 mt-2 font-medium">
                            * Pengajuan penarikan baru hanya dapat dilakukan setelah pengajuan ini disetujui atau ditolak oleh Ketua.
                        </p>
                    </div>
                </div>
            @elseif (Auth::guard('nasabah')->user()->status !== 'aktif')
                <!-- Peringatan jika akun tidak aktif -->
                <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-4 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 text-lg">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-red-900 text-base">Akun Tidak Aktif</h4>
                        <p class="text-sm text-red-800 mt-1">
                            Akun Anda saat ini berstatus tidak aktif. Pengajuan penarikan saldo tidak dapat dilakukan. Hubungi loket pengelola untuk informasi selengkapnya.
                        </p>
                    </div>
                </div>
            @else
                <!-- Form Input Nominal Penarikan -->
                <form action="{{ route('nasabah.pengajuan-penarikan') }}" method="POST" class="space-y-4 max-w-lg">
                    @csrf
                    <div>
                        <label for="nominal" class="block text-sm font-bold text-gray-700 mb-1">
                            Nominal Penarikan (Rp)
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 font-bold text-sm">
                                Rp
                            </div>
                            <input type="number" name="nominal" id="nominal" min="1" max="{{ Auth::guard('nasabah')->user()->saldo }}" step="1"
                                value="{{ old('nominal') }}" required placeholder="Contoh: 50000"
                                class="block w-full rounded-xl border-gray-300 pl-12 pr-4 py-3 text-gray-900 font-semibold focus:border-emerald-500 focus:ring-emerald-500 border text-base">
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">
                            Saldo maksimal yang dapat ditarik: <strong class="text-gray-800 font-bold">Rp {{ number_format(Auth::guard('nasabah')->user()->saldo, 0, ',', '.') }}</strong>
                        </p>
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl shadow-sm transition-colors text-sm flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Pengajuan Penarikan</span>
                    </button>
                </form>
            @endif
        </div>

        <!-- TOMBOL AKSI CEPAT & SCAN QR LOKET -->
        <div class="flex flex-wrap gap-4 mb-8">
            <a href="{{ route('nasabah.riwayat') }}" class="bg-[#5C8A8A] hover:bg-[#4a7070] text-white font-bold py-3.5 px-6 rounded-xl shadow-sm transition-colors flex items-center gap-2 text-sm sm:text-base">
                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                <span>Riwayat Setoran & Nota</span>
            </a>
            <button type="button" onclick="bukaModalScanQr()" class="bg-[#2D6A4F] hover:bg-[#1B4332] text-white font-bold py-3.5 px-6 rounded-xl shadow-sm transition-colors flex items-center gap-2 text-sm sm:text-base">
                <i class="fa-solid fa-qrcode text-lg"></i>
                <span>Pindai QR Loket Penimbangan</span>
            </button>
        </div>

        <!-- STATS & INFO -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-emerald-100 text-metanoia rounded-lg flex items-center justify-center text-xl">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Status Akun</p>
                        <p class="text-lg font-bold text-gray-800 capitalize">{{ Auth::guard('nasabah')->user()->status ?? 'Aktif' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                        <i class="fa-solid fa-recycle"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Setoran Sampah</p>
                        <p class="text-lg font-bold text-gray-800">Tersedia di Loket</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center text-xl">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Riwayat Penarikan</p>
                        <p class="text-lg font-bold text-gray-800">{{ !empty($riwayatPenarikan) ? $riwayatPenarikan->count() : 0 }} Tercatat</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIWAYAT PENGAJUAN PENARIKAN TERAKHIR -->
        @if (!empty($riwayatPenarikan) && $riwayatPenarikan->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                <h3 class="text-lg font-extrabold text-gray-900 mb-4">Pengajuan Penarikan Terakhir</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-xs uppercase">
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Nominal</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-800">
                            @foreach ($riwayatPenarikan as $p)
                                <tr>
                                    <td class="py-3 px-4 font-medium">{{ $p->tanggal_pengajuan->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4 font-bold text-emerald-700">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4">
                                        @if ($p->status === 'disetujui')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                Disetujui
                                            </span>
                                        @elseif ($p->status === 'ditolak')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                Ditolak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                Menunggu Persetujuan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-500">{{ $p->catatan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </main>

    <!-- MODAL PINDAI QR LOKET (NASABAH) -->
    <div id="modalScanQrNasabah" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-qrcode text-[#5C8A8A]"></i>
                    <span>Pindai QR Loket</span>
                </h3>
                <button type="button" onclick="tutupModalScanQr()" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <p class="text-xs text-gray-500 mb-4">
                Masukkan atau pindai kode token sesi QR dari layar petugas loket penimbangan untuk menghubungkan akun Anda.
            </p>

            <form id="formScanQr" onsubmit="submitScanQr(event)" class="space-y-4">
                @csrf
                <div>
                    <label for="qrTokenInput" class="block text-xs font-bold text-gray-700 mb-1">Kode Token QR</label>
                    <input type="text" id="qrTokenInput" required placeholder="Tempel / Masukkan token QR..."
                        class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div id="scanQrAlert" class="hidden text-xs p-3 rounded-lg"></div>

                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" onclick="tutupModalScanQr()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold hover:bg-gray-200">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitScanQr" class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Kirim Token</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500 mt-auto">
        &copy; {{ date('Y') }} Bank Sampah Metanoia. Hak cipta dilindungi undang-undang.
    </footer>

    <script>
        function bukaModalScanQr() {
            document.getElementById('modalScanQrNasabah').classList.remove('hidden');
            document.getElementById('qrTokenInput').focus();
        }

        function tutupModalScanQr() {
            document.getElementById('modalScanQrNasabah').classList.add('hidden');
            document.getElementById('scanQrAlert').classList.add('hidden');
            document.getElementById('qrTokenInput').value = '';
        }

        async function submitScanQr(e) {
            e.preventDefault();
            const token = document.getElementById('qrTokenInput').value.trim();
            const alertBox = document.getElementById('scanQrAlert');
            const submitBtn = document.getElementById('btnSubmitScanQr');

            if (!token) return;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
            alertBox.classList.add('hidden');

            try {
                const response = await fetch("{{ route('nasabah.scan-qr') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ token: token })
                });

                const data = await response.json();

                alertBox.classList.remove('hidden');
                if (response.ok && data.success) {
                    alertBox.className = 'text-xs p-3 rounded-lg bg-green-50 text-green-800 border border-green-200 font-medium';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-check text-green-600 mr-1"></i> ' + data.message;
                    setTimeout(() => {
                        tutupModalScanQr();
                        alert('QR Loket berhasil terhubung! Petugas sekarang dapat memproses penimbangan sampah Anda.');
                    }, 1000);
                } else {
                    alertBox.className = 'text-xs p-3 rounded-lg bg-red-50 text-red-800 border border-red-200 font-medium';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-xmark text-red-600 mr-1"></i> ' + (data.message || 'Gagal memindai token QR.');
                }
            } catch (err) {
                alertBox.classList.remove('hidden');
                alertBox.className = 'text-xs p-3 rounded-lg bg-red-50 text-red-800 border border-red-200 font-medium';
                alertBox.innerHTML = '<i class="fa-solid fa-circle-xmark text-red-600 mr-1"></i> Terjadi kesalahan jaringan.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Kirim Token';
            }
        }
    </script>
</body>
</html>
