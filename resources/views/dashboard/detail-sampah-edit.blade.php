<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Produk - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; background-color: #F3F4F6; }

        /* Custom input untuk icon pensil di kanan */
        .input-icon-wrapper { position: relative; width: 100%; }
        .input-icon-wrapper i {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            color: #9CA3AF; cursor: pointer; pointer-events: none;
        }
        .input-icon-wrapper textarea + i { top: 20px; }
    </style>
</head>
<body class="bg-[#EAEAEA] h-screen font-sans relative">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10 pb-6 shadow-xl flex-shrink-0">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20 shadow-md">
                        <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">Ammirudin suep</h2>
                </div>

                <nav class="px-4 space-y-2">
                    <a href="{{ route('pengelola.dashboard') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-house w-6"></i><span>Dashboard</span>
                    </a>
                    <a href="{{ route('pengelola.nasabah') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-users w-6"></i><span>Nasabah</span>
                    </a>
                    <a href="{{ route('pengelola.transaksi') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-money-bill-transfer w-6"></i><span>Transaksi</span>
                    </a>
                    <a href="{{ route('pengelola.penimbangan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-scale-balanced w-6"></i><span>Penimbangan</span>
                    </a>
                    <!-- Menu Detail Sampah Aktif -->
                    <a href="{{ route('pengelola.detail-sampah') }}" class="flex items-center px-4 py-3 bg-gray-100 text-sidebar-active rounded-lg font-bold transition-colors shadow-sm">
                        <i class="fa-solid fa-trash w-6"></i><span>Detail Sampah</span>
                    </a>
                    <a href="{{ route('pengelola.laporan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                        <i class="fa-solid fa-file-lines w-6"></i><span>Laporan</span>
                    </a>
                </nav>
            </div>

            <div class="px-4 mt-auto">
                <a href="{{ route('pengelola.pengaturan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                    <i class="fa-solid fa-gear w-6"></i><span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT CONTAINER -->
        <main class="flex-1 overflow-y-auto relative z-0 flex flex-col items-center justify-center p-10 bg-[#EAEAEA]">

            <div class="w-full max-w-4xl flex flex-col">
                <h1 class="text-[32px] font-extrabold text-black mb-6">Ubah Produk</h1>

                <!-- Pembungkus Kartu Putih Utama -->
                <div class="bg-white rounded-xl shadow-md p-10 w-full min-h-[500px] border border-gray-200 flex justify-center items-center">

                    <!-- Kartu Hijau Form Edit -->
                    <div class="bg-[#C2DCD6] border-2 border-[#a7c5be] rounded-[20px] p-8 w-full max-w-3xl shadow-lg flex gap-8 relative pb-20">

                        <!-- Sisi Kiri: Gambar Produk -->
                        <div class="w-[220px] h-[220px] bg-white rounded-xl flex-shrink-0 flex items-center justify-center overflow-hidden border border-gray-300 shadow-sm p-2">
                            <!-- Placeholder Gambar Ember Hitam (Atau ikon jika tidak ada gambar) -->
                            <div class="w-full h-full bg-black/90 rounded-lg flex flex-col items-center justify-center text-white">
                                <i class="fa-solid fa-bucket text-6xl mb-2"></i>
                                <span class="text-xs font-bold text-center">Gambar Produk</span>
                            </div>
                        </div>

                        <!-- Sisi Kanan: Form Input -->
                        <div class="flex-1 flex flex-col space-y-4">

                            <!-- Nama Produk -->
                            <div class="flex items-center gap-4">
                                <label class="w-24 text-gray-500 font-bold text-[13px] leading-tight">Nama<br>Produk</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" id="editNama" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-400 font-medium text-sm shadow-sm" value="Ember Hitam">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>

                            <!-- Deskripsi Produk -->
                            <div class="flex items-start gap-4">
                                <label class="w-24 text-gray-500 font-bold text-[13px] leading-tight pt-2">Deskripsi<br>Produk</label>
                                <div class="input-icon-wrapper">
                                    <textarea id="editDeskripsi" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-400 font-medium text-sm shadow-sm h-24 resize-none">Jenis botol/wadah plastik berbahan PP, HDPE yang berwarna hitam / abu-abu.</textarea>
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>

                            <!-- Harga & Kode Produk -->
                            <div class="flex items-center gap-4 mt-2">
                                <label class="w-24 text-gray-500 font-bold text-[13px] leading-tight">Harga<br>Produk</label>
                                <div class="input-icon-wrapper w-32">
                                    <input type="text" id="editHarga" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-400 font-medium text-sm shadow-sm" value="2.400">
                                    <i class="fa-solid fa-pen"></i>
                                </div>

                                <label class="ml-2 w-16 text-gray-500 font-bold text-[13px] leading-tight text-center">Kode<br>Produk</label>
                                <div class="input-icon-wrapper w-28">
                                    <!-- ID Lama disimpan tersembunyi agar kode acuan pencarian di JS tidak hilang -->
                                    <input type="hidden" id="kodeLama" value="P0B">
                                    <input type="text" id="editKode" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#5C8A8A] text-gray-400 font-medium text-sm shadow-sm" value="P0B">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                            </div>

                        </div>

                        <!-- Grup Tombol Aksi di Bawah Kanan Kartu Hijau -->
                        <div class="absolute bottom-6 right-8 flex items-center gap-3">
                            <button type="button" onclick="batal()" class="bg-[#FF0A0A] hover:bg-red-700 text-white font-bold py-1.5 px-8 rounded shadow-sm text-sm border border-red-500 transition-colors">
                                Batal
                            </button>
                            <button type="button" onclick="simpanPerubahan()" class="bg-[#59A835] hover:bg-green-600 text-white font-bold py-1.5 px-6 rounded shadow-sm text-sm border border-green-500 transition-colors">
                                Simpan Perubahann
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL POP UP SUKSES UBAH PRODUK -->
    <!-- ============================================== -->
    <div id="modalSukses" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <!-- Latar Gelap Transparan -->
        <div class="absolute inset-0 bg-black/20 backdrop-blur-[1px] transition-opacity"></div>

        <!-- Box Modal -->
        <div class="relative bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.2)] w-full max-w-md flex flex-col items-center pb-8 z-10 overflow-hidden">

            <!-- Header Modal Biru Kehijauan -->
            <div class="bg-[#5C8A8A] w-full h-12 flex-shrink-0"></div>

            <!-- Lingkaran Checklist Hijau -->
            <div class="mt-8 mb-6">
                <div class="w-20 h-20 bg-[#65C622] rounded-full flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-check text-white text-4xl"></i>
                </div>
            </div>

            <h2 class="text-2xl font-extrabold text-black text-center leading-tight mb-8">
                Produk Berhasil<br>di ubah!
            </h2>

            <!-- Tombol Selesai Modal -->
            <button onclick="tutupModalSelesai()" class="bg-[#65C622] hover:bg-[#57a81d] text-white font-bold py-1.5 px-10 rounded text-sm shadow-sm transition-colors">
                Selesai
            </button>

        </div>
    </div>

    <!-- JAVASCRIPT LOGIKA EDIT & MODAL -->
    <script>
        // Logika saat halaman dimuat
        document.addEventListener("DOMContentLoaded", function() {
            // Simulasi: Mengambil data dari localStorage untuk mengisi form.
            // Secara default kita akan mencari 'P0B' (Ember Hitam) sebagai contoh desain mockup
            let db = JSON.parse(localStorage.getItem('db_jenis_sampah')) || [];
            let produkTarget = db.find(p => p.kode === 'P0B');

            if (produkTarget) {
                // Jika ditemukan di database sementara, load isinya
                document.getElementById('editNama').value = produkTarget.nama;
                document.getElementById('editDeskripsi').value = produkTarget.deskripsi;
                document.getElementById('editKode').value = produkTarget.kode;
                document.getElementById('editHarga').value = produkTarget.harga || '2.400';
            }
        });

        // 1. Simpan Perubahan ke LocalStorage
        function simpanPerubahan() {
            let namaBaru = document.getElementById('editNama').value.trim();
            let deskripsiBaru = document.getElementById('editDeskripsi').value.trim();
            let kodeBaru = document.getElementById('editKode').value.trim();
            let hargaBaru = document.getElementById('editHarga').value.trim();
            let kodeAcuan = document.getElementById('kodeLama').value;

            if(namaBaru === '' || kodeBaru === '') {
                alert("Nama Produk dan Kode Produk tidak boleh kosong!");
                return;
            }

            // Ambil database sementara
            let db = JSON.parse(localStorage.getItem('db_jenis_sampah')) || [];

            // Cari indeks produk yang ingin diubah (Acuan P0B atau kode sebelumnya)
            let index = db.findIndex(p => p.kode === kodeAcuan);

            if (index !== -1) {
                // Update datanya
                db[index].nama = namaBaru;
                db[index].deskripsi = deskripsiBaru;
                db[index].kode = kodeBaru;
                db[index].harga = hargaBaru;

                // Update acuan jika kode ikut diubah user
                document.getElementById('kodeLama').value = kodeBaru;
            } else {
                // Jika tidak ada di DB, tambahkan sebagai data baru
                db.push({ kode: kodeBaru, nama: namaBaru, deskripsi: deskripsiBaru, harga: hargaBaru });
            }

            // Simpan kembali
            localStorage.setItem('db_jenis_sampah', JSON.stringify(db));

            // Munculkan Modal Notifikasi Berhasil
            document.getElementById('modalSukses').classList.remove('hidden');
        }

        // 2. Tutup Modal & Kembali ke Grid
        function tutupModalSelesai() {
            document.getElementById('modalSukses').classList.add('hidden');
            // Kembali ke halaman detail sampah (grid)
            window.location.href = "{{ route('pengelola.detail-sampah') }}";
        }

        // 3. Tombol Batal
        function batal() {
            window.location.href = "{{ route('pengelola.detail-sampah') }}";
        }
    </script>
</body>
</html>
