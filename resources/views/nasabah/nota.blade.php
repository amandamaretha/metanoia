<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Setoran {{ $nota->nomor }} - Metanoia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-metanoia { background-color: #5C8A8A; }
        .text-metanoia { color: #5C8A8A; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-[#F4F6F6] min-h-screen font-sans flex flex-col">
    <!-- NAVBAR (Hidden on print) -->
    <header class="bg-metanoia text-white shadow-md no-print">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('nasabah.riwayat') }}" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-bold text-lg text-white hover:bg-white/30 transition">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold leading-tight">Nota Setoran</h1>
                    <p class="text-xs text-white/80">Nomor: {{ $nota->nomor }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button onclick="window.print()" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Nota</span>
                </button>
                <a href="{{ route('nasabah.riwayat') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Riwayat</span>
                </a>
            </div>
        </div>
    </header>

    <!-- KERTAS NOTA -->
    <main class="flex-1 max-w-2xl w-full mx-auto p-6 md:p-8 flex justify-center items-start">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8 w-full max-w-xl">
            <!-- Header Nota -->
            <div class="text-center pb-6 border-b-2 border-dashed border-gray-300">
                <h2 class="text-2xl font-black text-gray-900 tracking-wide uppercase">BANK SAMPAH METANOIA</h2>
                <p class="text-xs text-gray-500 mt-1">Struk & Bukti Penimbangan Setoran Sampah</p>
                <div class="mt-4 inline-block bg-gray-100 text-gray-800 text-xs font-mono font-bold px-3 py-1.5 rounded border border-gray-300">
                    No. Nota: {{ $nota->nomor }}
                </div>
            </div>

            <!-- Meta Data Transaksi -->
            <div class="py-6 border-b border-gray-200 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Waktu Penimbangan:</span>
                    <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($nota->tanggal)->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Nama Nasabah:</span>
                    <span class="font-bold text-gray-900">{{ $nota->setoran->nasabah->nama }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">NIK:</span>
                    <span class="font-mono text-gray-700">{{ $nota->setoran->nasabah->masked_nik }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Petugas Penerima:</span>
                    <span class="text-gray-800">{{ $nota->setoran->petugas->nama ?? '-' }}</span>
                </div>
            </div>

            <!-- Rincian Setoran -->
            <div class="py-6 border-b-2 border-dashed border-gray-300 space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-bold text-gray-900 text-base">{{ $nota->setoran->jenisSampah->nama }}</p>
                        <p class="text-xs text-gray-500">Kondisi: {{ str_replace('_', ' ', ucfirst($nota->setoran->kondisi)) }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-mono text-xs text-gray-500">{{ number_format($nota->setoran->berat, 2, ',', '.') }} kg &times; Rp {{ number_format($nota->setoran->harga_per_kg, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex justify-between text-sm pt-2">
                    <span class="text-gray-500">Subtotal:</span>
                    <span class="font-mono text-xs text-gray-700">Rp {{ number_format($nota->setoran->nilai_sebelum_pembulatan, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pembulatan:</span>
                    <span class="font-mono text-xs text-gray-700">{{ ($nota->setoran->pembulatan >= 0 ? '+' : '') }}Rp {{ number_format($nota->setoran->pembulatan, 0, ',', '.') }}</span>
                </div>

                <div class="pt-3 flex justify-between items-center text-lg">
                    <span class="font-extrabold text-gray-900">Total Nilai:</span>
                    <span class="font-black text-2xl text-emerald-700">Rp {{ number_format($nota->setoran->total_nilai, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Footer Nota -->
            <div class="pt-6 text-center space-y-1">
                <p class="text-xs font-semibold text-gray-700">Saldo telah otomatis ditambahkan ke buku tabungan nasabah.</p>
                <p class="text-[11px] text-gray-400">Terima kasih telah berkontribusi menjaga kelestarian lingkungan bersama Metanoia!</p>
            </div>

            <!-- Tombol Kembali no-print -->
            <div class="mt-8 pt-4 border-t border-gray-100 flex justify-center no-print">
                <a href="{{ route('nasabah.riwayat') }}" class="text-sm font-bold text-metanoia hover:underline">
                    &larr; Kembali ke Riwayat Transaksi
                </a>
            </div>
        </div>
    </main>
</body>
</html>
