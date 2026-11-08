<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }

        /* Custom styling untuk modal agar persis seperti desain */
        .modal-shadow { box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.15); }
    </style>
</head>
<body class="bg-[#EAEAEA] h-screen font-sans relative">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10 pb-6">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 id="displayNamaSidebar" class="mt-4 text-lg font-bold">Ammirudin suep</h2>
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
                    <a href="{{ route('pengelola.penimbangan') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.penimbangan') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
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

            <!-- ============================================== -->
            <!-- VIEW 1: HALAMAN PENGATURAN UTAMA -->
            <!-- ============================================== -->
            <div id="pengaturanView">
                <h1 class="text-[36px] font-extrabold text-black mb-6 mt-2">Pengaturan</h1>

                <div class="bg-white rounded-xl shadow-md border border-gray-300 p-8 w-full max-w-5xl mx-auto min-h-[600px]">
                    <div class="bg-[#5C8A8A] rounded-xl p-8 flex items-center gap-6 shadow-sm mb-8">
                        <div class="w-28 h-28 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/30 shadow-md flex-shrink-0">
                            <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                        </div>

                        <div class="flex flex-col justify-center">
                            <h2 id="displayNamaUtama" class="text-3xl font-extrabold text-white mb-1">Ammirudin suep</h2>
                            <p class="text-gray-200 text-lg mb-4">Pengelola</p>

                            <div>
                                <button type="button" onclick="bukaProfil()" class="bg-[#6DC355] hover:bg-green-600 border border-[#5ea949] text-white text-sm font-bold py-1.5 px-4 rounded shadow-sm transition-colors flex items-center gap-2">
                                    <i class="fa-regular fa-user"></i> Lihat Profile
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col w-full">
                        <div class="border-t border-gray-300 w-full"></div>
                        <a href="#" class="py-5 px-2 hover:bg-gray-50 transition block">
                            <span class="text-xl font-medium text-black">Tambah Akun Pengelola</span>
                        </a>
                        <div class="border-t border-gray-300 w-full"></div>
                        <a href="#" class="py-5 px-2 hover:bg-gray-50 transition block">
                            <span class="text-xl font-medium text-black">Kelola Akun Petugas</span>
                        </a>
                        <div class="border-t border-gray-300 w-full"></div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- VIEW 2: HALAMAN PROFILE DETAIL -->
            <!-- ============================================== -->
            <div id="profilView" class="hidden w-full max-w-5xl mx-auto mt-2">

                <div class="flex items-center mb-6">
                    <button onclick="tutupProfil()" class="text-[36px] font-extrabold text-black hover:text-gray-600 transition flex items-center gap-4">
                        <i class="fa-solid fa-chevron-left text-[32px]"></i> Profile
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-md border border-gray-300 p-4 w-full flex flex-col md:flex-row min-h-[580px]">

                    <!-- Sisi Kiri: Foto & Identitas -->
                    <div class="w-full md:w-[45%] bg-[#5C8A8A] rounded-lg flex flex-col items-center justify-center p-8">
                        <div class="w-56 h-56 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-[6px] border-white/20 mb-8 shadow-lg">
                            <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                        </div>

                        <div class="text-center text-white w-full px-4">
                            <h2 class="text-[28px] font-extrabold flex items-center justify-center gap-3 mb-1">
                                <span id="displayNamaKiri">Ammirudin suep</span>
                                <i class="fa-solid fa-pen-to-square text-xl cursor-pointer hover:text-gray-200 transition"></i>
                            </h2>
                            <p class="text-xl font-light opacity-90 mb-0.5">Pengelola</p>
                            <p id="displayNoHpKiri" class="text-lg font-light opacity-90">082314754841</p>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Form Data -->
                    <div class="w-full md:w-[55%] relative p-8 md:px-12 md:py-10 flex flex-col">

                        <div class="flex justify-end w-full mb-12">
                            <button type="button" onclick="simpanPerubahanProfil()" class="bg-[#8AA29E] hover:bg-[#6c8581] text-white font-bold py-2.5 px-6 rounded-md shadow-md transition-colors text-sm">
                                Simpan Perubahann
                            </button>
                        </div>

                        <div class="flex-1 flex flex-col justify-center space-y-8 mt-10">

                            <div class="flex flex-col md:flex-row gap-6 w-full">
                                <div class="w-full md:w-1/2">
                                    <label class="block text-gray-400 font-bold mb-2 text-[13px]">Nama Depan</label>
                                    <input type="text" id="inputNamaDepan" value="Ammirudin"
                                        class="w-full border-2 border-gray-200 rounded-md px-4 py-3 text-gray-400 font-semibold focus:outline-none focus:border-[#5C8A8A] shadow-sm">
                                </div>
                                <div class="w-full md:w-1/2">
                                    <label class="block text-gray-400 font-bold mb-2 text-[13px]">Nama Belakang</label>
                                    <input type="text" id="inputNamaBelakang" value="suep"
                                        class="w-full border-2 border-gray-200 rounded-md px-4 py-3 text-gray-400 font-semibold focus:outline-none focus:border-[#5C8A8A] shadow-sm">
                                </div>
                            </div>

                            <div class="w-full">
                                <label class="block text-gray-400 font-bold mb-2 text-[13px]">No Telepon</label>
                                <div class="flex items-center gap-6">
                                    <!-- Field No HP dibuat readonly agar pengubahan hanya melalui Modal -->
                                    <input type="text" id="inputNoHp" value="082314754841" readonly
                                        class="w-full md:w-[65%] border-2 border-gray-200 rounded-md px-4 py-3 text-gray-400 font-semibold focus:outline-none bg-white shadow-sm cursor-not-allowed">

                                    <!-- Tombol trigger modal -->
                                    <a href="javascript:void(0)" onclick="bukaModalTelp()" class="text-[#7E96FB] hover:text-blue-700 underline text-[12px] font-medium transition-colors whitespace-nowrap">
                                        Ubah No Telepon
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL POP UP - UBAH NOMOR TELEPON -->
    <!-- ============================================== -->
    <div id="modalUbahTelp" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <!-- Latar transparan -->
        <div class="absolute inset-0 bg-white/40 transition-opacity" onclick="tutupModalTelp()"></div>

        <!-- Box Modal -->
        <div class="relative bg-white rounded-[15px] modal-shadow w-full max-w-[420px] px-10 py-12 z-10 flex flex-col items-center">

            <h3 class="text-[22px] font-bold text-gray-500 mb-8">Ubah Nomor Telepon</h3>

            <div class="w-full mb-5">
                <label class="block text-gray-400 font-bold text-[13px] mb-2 text-left">No Telepon Lama</label>
                <input type="text" id="inputTelpLama" placeholder="08xxxxxxxxxxx" class="w-full border-2 border-gray-200 rounded-md px-4 py-3 text-gray-400 font-medium focus:outline-none focus:border-[#5C8A8A] shadow-sm">
            </div>

            <div class="w-full mb-8">
                <label class="block text-gray-400 font-bold text-[13px] mb-2 text-left">No Telepon Baru</label>
                <input type="text" id="inputTelpBaru" placeholder="08xxxxxxxxxxx" class="w-full border-2 border-gray-200 rounded-md px-4 py-3 text-gray-400 font-medium focus:outline-none focus:border-[#5C8A8A] shadow-sm">
            </div>

            <button type="button" onclick="simpanTelpBaru()" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-2.5 px-10 rounded-md shadow-sm transition-colors text-sm w-32">
                Simpan
            </button>

        </div>
    </div>

    <!-- JAVASCRIPT LOGIKA -->
    <script>
        // Logika Perpindahan Halaman
        function bukaProfil() {
            document.getElementById('pengaturanView').classList.add('hidden');
            document.getElementById('profilView').classList.remove('hidden');
        }

        function tutupProfil() {
            document.getElementById('profilView').classList.add('hidden');
            document.getElementById('pengaturanView').classList.remove('hidden');
        }

        // =====================================
        // Logika Modal Ubah Telepon
        // =====================================
        function bukaModalTelp() {
            // Mengambil nomor HP yang saat ini ada di form utama untuk dijadikan "No Telepon Lama"
            let noHpSaatIni = document.getElementById('inputNoHp').value;
            document.getElementById('inputTelpLama').value = noHpSaatIni;
            document.getElementById('inputTelpBaru').value = '';

            document.getElementById('modalUbahTelp').classList.remove('hidden');
        }

        function tutupModalTelp() {
            document.getElementById('modalUbahTelp').classList.add('hidden');
        }

        function simpanTelpBaru() {
            let telpBaru = document.getElementById('inputTelpBaru').value.trim();

            if (telpBaru === '') {
                alert('Nomor telepon baru tidak boleh kosong!');
                return;
            }

            // Masukkan nomor telepon baru ke input form utama
            document.getElementById('inputNoHp').value = telpBaru;

            tutupModalTelp();
        }

        // =====================================
        // Logika Simpan Seluruh Perubahan Profil
        // =====================================
        function simpanPerubahanProfil() {
            let namaDepan = document.getElementById('inputNamaDepan').value.trim();
            let namaBelakang = document.getElementById('inputNamaBelakang').value.trim();
            let noHp = document.getElementById('inputNoHp').value.trim();

            if(namaDepan === '' || noHp === '') {
                alert('Nama depan dan No Telepon tidak boleh kosong!');
                return;
            }

            let namaLengkap = namaDepan;
            if(namaBelakang !== '') {
                namaLengkap += ' ' + namaBelakang;
            }

            // Memperbarui 3 tampilan sekaligus
            document.getElementById('displayNamaKiri').innerText = namaLengkap;
            document.getElementById('displayNoHpKiri').innerText = noHp;
            document.getElementById('displayNamaUtama').innerText = namaLengkap;
            document.getElementById('displayNamaSidebar').innerText = namaLengkap;

            alert('Perubahan profil berhasil disimpan!');
            tutupProfil();
        }
    </script>
</body>
</html>
