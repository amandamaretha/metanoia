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
                    <h2 class="mt-4 text-lg font-bold">Ammirudin suep</h2>
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
                    <a href="{{ route('pengelola.detail-sampah.ubah') }}" class="bg-[#FF0000] hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-md shadow-md transition-colors text-[15px] flex items-center gap-2 border-[1.5px] border-white">
                        <i class="fa-solid fa-pen-to-square"></i> Ubah Produk
                    </a>
                </div>
            </div>

            <!-- AREA GRID (Dikosongkan untuk diisi oleh JS) -->
            <div class="px-10 py-8 flex-1 overflow-y-auto">
                <div id="gridProdukContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <!-- Data akan dirender otomatis oleh JavaScript -->
                </div>
            </div>

        </main>
    </div>

    <!-- JAVASCRIPT LOGIKA MENGAMBIL DATA PRODUK -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let container = document.getElementById("gridProdukContainer");

            // Mengambil data dari localStorage (Database sementara browser)
            let dataProduk = JSON.parse(localStorage.getItem('db_jenis_sampah'));

            // Jika belum ada data sama sekali, berikan dummy data agar tampilan awal tidak kosong
            if (!dataProduk || dataProduk.length === 0) {
                dataProduk = [
                    { kode: 'POS', nama: 'Gelas / Cup Bening Bersih', deskripsi: 'Gelas Plastik kemasan warna bening yang sudah dipotong bibir gelasnya' },
                    { kode: 'P06', nama: 'Botol Putih', deskripsi: 'Botol plastik bahan PET berwarna putih pekat, bebas dari tutup botol & label' },
                    { kode: 'P07', nama: 'Ember warna campur', deskripsi: 'Wadah/kemasan plastik berbahan PP contoh jerigen, HDPE, LDPE, & ember, botol oli, botol sampo' }
                ];
                // Simpan dummy ini ke local storage
                localStorage.setItem('db_jenis_sampah', JSON.stringify(dataProduk));
            }

            // Render HTML berdasarkan data yang ada
            let htmlContent = "";
            dataProduk.forEach(item => {
                htmlContent += `
                    <div class="bg-[#CBE6E0] border-[1.5px] border-[#103D34] rounded-xl p-2.5 flex items-stretch gap-3 hover:shadow-md cursor-pointer transition-shadow h-28">
                        <div class="w-[90px] h-full bg-white rounded-lg flex-shrink-0 overflow-hidden border border-gray-300 flex items-center justify-center">
                            <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-[10px]">Gambar</div>
                        </div>
                        <div class="flex flex-col flex-1 justify-center items-center px-1 text-center">
                            <span class="font-extrabold text-[#103D34] text-xl leading-tight">${item.kode}</span>
                            <span class="font-bold text-[#103D34] text-[12px] leading-tight mb-1">${item.nama}</span>
                            <span class="text-[#103D34] text-[9px] leading-tight overflow-hidden text-ellipsis line-clamp-3">${item.deskripsi}</span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = htmlContent;
        });
    </script>
</body>
</html>
