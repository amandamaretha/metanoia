<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Setoran & Nota - Metanoia</title>
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
                <a href="{{ route('nasabah.beranda') }}" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-bold text-lg text-white hover:bg-white/30 transition">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold leading-tight">Riwayat Setoran & Nota</h1>
                    <p class="text-xs text-white/80">Portal Nasabah Metanoia</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium hidden sm:inline">{{ $nasabah->nama }}</span>
                <a href="{{ route('nasabah.beranda') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-house"></i>
                    <span>Beranda</span>
                </a>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-6 md:p-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-800">Daftar Setoran Sampah</h2>
                <p class="text-sm text-gray-500">Seluruh riwayat penimbangan sampah dan nota yang Anda terima.</p>
            </div>
            <a href="{{ route('nasabah.beranda') }}" class="text-sm font-semibold text-metanoia hover:underline flex items-center gap-1">
                <i class="fa-solid fa-chevron-left text-xs"></i> Kembali ke Beranda
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 font-bold border-b border-gray-200">
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4">Tanggal</th>
                            <th class="py-3.5 px-4">Jenis Sampah</th>
                            <th class="py-3.5 px-4 text-center">Kondisi</th>
                            <th class="py-3.5 px-4 text-right">Berat (kg)</th>
                            <th class="py-3.5 px-4 text-right">Harga/kg</th>
                            <th class="py-3.5 px-4 text-right">Total Nilai</th>
                            <th class="py-3.5 px-4 text-center">Nota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($setoranList as $index => $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3.5 px-4 text-center font-medium text-gray-600">{{ $index + 1 }}</td>
                                <td class="py-3.5 px-4 text-gray-700 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-gray-800">
                                    {{ $item->jenisSampah->nama ?? '-' }}
                                    <span class="block text-xs text-gray-400 font-normal">Kode: {{ $item->jenisSampah->kode ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if($item->kondisi === 'sudah_dipilah') bg-emerald-100 text-emerald-800
                                        @elseif($item->kondisi === 'belum_dipilah') bg-amber-100 text-amber-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ str_replace('_', ' ', ucfirst($item->kondisi)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-medium text-gray-700">
                                    {{ number_format($item->berat, 2, ',', '.') }} kg
                                </td>
                                <td class="py-3.5 px-4 text-right text-gray-600">
                                    Rp {{ number_format($item->harga_per_kg, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-emerald-700">
                                    Rp {{ number_format($item->total_nilai, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($item->nota)
                                        <a href="{{ route('nasabah.nota', $item->nota->id) }}" class="inline-flex items-center gap-1.5 bg-[#5C8A8A] hover:bg-[#4a7070] text-white px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm transition">
                                            <i class="fa-solid fa-receipt"></i>
                                            <span>Lihat Nota</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Tidak ada nota</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-gray-500">
                                    <i class="fa-solid fa-receipt text-4xl mb-3 block text-gray-300"></i>
                                    <p class="font-semibold">Belum ada riwayat setoran sampah.</p>
                                    <p class="text-xs text-gray-400 mt-1">Lakukan penimbangan sampah di loket Bank Sampah Metanoia untuk mencatat setoran.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500 mt-auto">
        &copy; {{ date('Y') }} Bank Sampah Metanoia.
    </footer>
</body>
</html>
