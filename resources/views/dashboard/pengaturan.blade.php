<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaturan - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }
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
                        <img src="{{ Auth::guard('petugas')->user()?->foto ? (filter_var(Auth::guard('petugas')->user()->foto, FILTER_VALIDATE_URL) ? Auth::guard('petugas')->user()->foto : asset(Auth::guard('petugas')->user()->foto)) : 'https://i.pravatar.cc/150?img=12' }}" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 id="displayNamaSidebar" class="mt-4 text-lg font-bold">{{ Auth::guard('petugas')->user()->nama ?? 'Petugas' }}</h2>
                </div>

                <nav class="px-4 space-y-2">
                    @if(Auth::guard('petugas')->user()->jabatan === 'pengelola')
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
                    @elseif(Auth::guard('petugas')->user()->jabatan === 'bendahara')
                        <a href="{{ route('bendahara.transaksi') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                            <i class="fa-solid fa-money-bill-transfer w-6"></i>
                            <span>Transaksi Kas</span>
                        </a>
                        <a href="{{ route('bendahara.riwayat') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                            <i class="fa-solid fa-clock-rotate-left w-6"></i>
                            <span>Riwayat Kas</span>
                        </a>
                        <a href="{{ route('bendahara.laporan-keuangan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                            <i class="fa-solid fa-file-invoice-dollar w-6"></i>
                            <span>Laporan Kas</span>
                        </a>
                    @elseif(Auth::guard('petugas')->user()->jabatan === 'ketua')
                        <a href="{{ route('ketua.pengajuan-penarikan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg font-semibold transition-colors">
                            <i class="fa-solid fa-hand-holding-dollar w-6"></i>
                            <span>Pengajuan Penarikan</span>
                        </a>
                    @endif
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

            <!-- Flash Pesan Sukses / Error -->
            @if(session('success'))
                <div class="max-w-5xl mx-auto w-full mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="max-w-5xl mx-auto w-full mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span class="text-sm font-bold">Terjadi Kesalahan:</span>
                    </div>
                    <ul class="text-xs list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                            <h2 id="displayNamaUtama" class="text-3xl font-extrabold text-white mb-1">{{ Auth::guard('petugas')->user()->nama ?? 'Petugas' }}</h2>
                            <p class="text-gray-200 text-lg mb-4 capitalize">{{ Auth::guard('petugas')->user()->jabatan ?? 'Petugas' }}</p>

                            <div>
                                <button type="button" onclick="bukaProfil()" class="bg-[#6DC355] hover:bg-green-600 border border-[#5ea949] text-white text-sm font-bold py-1.5 px-4 rounded shadow-sm transition-colors flex items-center gap-2">
                                    <i class="fa-regular fa-user"></i> Lihat Profile
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col w-full">
                        @if(Auth::guard('petugas')->user()->jabatan === 'pengelola')
                            <div class="border-t border-gray-300 w-full"></div>
                            <a href="{{ route('pengelola.petugas.tambah') }}" class="py-5 px-2 hover:bg-gray-50 transition block">
                                <span class="text-xl font-medium text-black">Tambah Akun Petugas</span>
                            </a>
                            <div class="border-t border-gray-300 w-full"></div>
                            <a href="{{ route('pengelola.petugas.index') }}" class="py-5 px-2 hover:bg-gray-50 transition block">
                                <span class="text-xl font-medium text-black">Kelola Akun Petugas</span>
                            </a>
                        @endif

                        <div class="border-t border-gray-300 w-full"></div>
                        <a href="javascript:void(0)" onclick="bukaModalPassword()" class="py-5 px-2 hover:bg-gray-50 transition block">
                            <span class="text-xl font-medium text-black">Ubah Password</span>
                        </a>
                        <div class="border-t border-gray-300 w-full"></div>
                        <a href="javascript:void(0)" onclick="konfirmasiKeluar()" class="py-5 px-2 hover:bg-red-50 text-red-600 transition block">
                            <span class="text-xl font-medium text-red-600">Keluar Akun</span>
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
                    <button type="button" onclick="tutupProfil()" class="text-[36px] font-extrabold text-black hover:text-gray-600 transition flex items-center gap-4">
                        <i class="fa-solid fa-chevron-left text-[32px]"></i> Profile
                    </button>
                </div>

                <form id="formSimpanProfil" action="{{ route('pengaturan.profil.update') }}" method="POST">
                    @csrf
                    <div class="bg-white rounded-xl shadow-md border border-gray-300 p-4 w-full flex flex-col md:flex-row min-h-[580px]">

                        <!-- Sisi Kiri: Foto & Identitas -->
                        <div class="w-full md:w-[45%] bg-[#5C8A8A] rounded-lg flex flex-col items-center justify-center p-8">
                            <div class="w-56 h-56 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-[6px] border-white/20 mb-8 shadow-lg">
                                <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                            </div>

                            <div class="text-center text-white w-full px-4">
                                <h2 class="text-[28px] font-extrabold flex items-center justify-center gap-3 mb-1">
                                    <span id="displayNamaKiri">{{ Auth::guard('petugas')->user()->nama ?? 'Petugas' }}</span>
                                    <i class="fa-solid fa-pen-to-square text-xl cursor-pointer hover:text-gray-200 transition" onclick="document.getElementById('inputNamaLengkap').focus()"></i>
                                </h2>
                                <p class="text-xl font-light opacity-90 mb-0.5 capitalize">{{ Auth::guard('petugas')->user()->jabatan ?? 'Petugas' }}</p>
                                <p id="displayNoHpKiri" class="text-lg font-light opacity-90">{{ Auth::guard('petugas')->user()->no_telp ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Sisi Kanan: Form Data -->
                        <div class="w-full md:w-[55%] relative p-8 md:px-12 md:py-10 flex flex-col">

                            <div class="flex justify-end gap-3 w-full mb-12">
                                <button type="button" onclick="tutupProfil()" class="px-5 py-2.5 rounded-md border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 transition text-sm">
                                    Batal
                                </button>
                                <button type="submit" class="bg-[#8AA29E] hover:bg-[#6c8581] text-white font-bold py-2.5 px-6 rounded-md shadow-md transition-colors text-sm">
                                    Simpan Perubahan
                                </button>
                            </div>

                            <div class="flex-1 flex flex-col justify-center space-y-8 mt-4">

                                <div class="w-full">
                                    <label class="block text-gray-400 font-bold mb-2 text-[13px]">Nama Lengkap</label>
                                    <div class="relative">
                                        <input type="text" name="nama" id="inputNamaLengkap" value="{{ old('nama', Auth::guard('petugas')->user()->nama) }}" required
                                            class="w-full border-2 border-gray-200 rounded-md px-4 py-3 text-gray-700 font-semibold focus:outline-none focus:border-[#5C8A8A] shadow-sm pr-10">
                                        <i class="fa-solid fa-pen absolute right-3 top-4 text-gray-400 cursor-pointer" onclick="document.getElementById('inputNamaLengkap').focus()"></i>
                                    </div>
                                    @error('nama')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="w-full">
                                    <label class="block text-gray-400 font-bold mb-2 text-[13px]">No Telepon</label>
                                    <div class="flex items-center gap-6">
                                        <!-- Field No HP dibuat readonly, pengubahan diverifikasi lewat Modal OTP -->
                                        <div class="relative w-full md:w-[70%]">
                                            <input type="text" name="telepon" id="inputNoHp" value="{{ old('telepon', Auth::guard('petugas')->user()->no_telp) }}" readonly
                                                class="w-full border-2 border-gray-200 rounded-md px-4 py-3 text-gray-700 font-semibold focus:outline-none bg-gray-50 shadow-sm cursor-not-allowed pr-10">
                                            <i class="fa-solid fa-pen absolute right-3 top-4 text-gray-400 cursor-pointer" onclick="bukaModalTelp()"></i>
                                        </div>

                                        <!-- Tombol trigger modal -->
                                        <a href="javascript:void(0)" onclick="bukaModalTelp()" class="text-[#7E96FB] hover:text-blue-700 underline text-[12px] font-medium transition-colors whitespace-nowrap">
                                            Ubah No Telepon
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 1: UBAH NOMOR TELEPON -->
    <!-- ============================================== -->
    <div id="modalUbahTelp" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="relative bg-white rounded-[15px] modal-shadow w-full max-w-[420px] px-8 py-10 z-10 flex flex-col items-center">
            <h3 class="text-[20px] font-bold text-gray-700 mb-6">Ubah Nomor Telepon</h3>

            <div id="errorModalTelp" class="w-full mb-4 hidden bg-red-50 border border-red-200 text-red-700 text-xs p-3 rounded"></div>

            <div class="w-full mb-4">
                <label class="block text-gray-500 font-bold text-[13px] mb-1 text-left">No Telepon Lama</label>
                <input type="text" id="inputTelpLama" value="{{ Auth::guard('petugas')->user()->no_telp }}" placeholder="08xxxxxxxxxxx"
                    class="w-full border-2 border-gray-200 rounded-md px-4 py-2.5 text-gray-700 font-medium focus:outline-none focus:border-[#5C8A8A] shadow-sm">
            </div>

            <div class="w-full mb-6">
                <label class="block text-gray-500 font-bold text-[13px] mb-1 text-left">No Telepon Baru</label>
                <input type="text" id="inputTelpBaru" placeholder="08xxxxxxxxxxx"
                    class="w-full border-2 border-gray-200 rounded-md px-4 py-2.5 text-gray-700 font-medium focus:outline-none focus:border-[#5C8A8A] shadow-sm">
            </div>

            <div class="flex items-center gap-4 w-full justify-center">
                <button type="button" onclick="tutupModalTelp()" class="px-6 py-2.5 rounded-md border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="button" id="btnKirimOtp" onclick="mintaOtpTelpBaru()" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-2.5 px-6 rounded-md shadow-sm transition text-sm">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 2: VERIFIKASI NO TELEPON (KODE OTP 6 DIGIT) -->
    <!-- ============================================== -->
    <div id="modalVerifikasiTelp" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="relative bg-white rounded-[15px] modal-shadow w-full max-w-[420px] px-8 py-10 z-10 flex flex-col items-center text-center">
            <h3 class="text-[20px] font-bold text-gray-700 mb-2">Verifikasi No Telepon</h3>
            <p class="text-xs text-gray-500 mb-6">Masukkan 6 digit kode verifikasi yang telah dikirim ke nomor baru Anda.</p>

            <div id="errorModalVerif" class="w-full mb-4 hidden bg-red-50 border border-red-200 text-red-700 text-xs p-3 rounded"></div>

            <div class="w-full mb-6">
                <input type="text" id="inputKodeOtp" maxlength="6" placeholder="000000"
                    class="w-full border-2 border-gray-300 rounded-md px-4 py-3 text-center text-2xl tracking-[0.5em] font-bold text-gray-800 focus:outline-none focus:border-[#5C8A8A]">
            </div>

            <div class="flex items-center gap-4 w-full justify-center">
                <button type="button" onclick="tutupModalVerifikasi()" class="px-5 py-2.5 rounded-md border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="button" id="btnVerifOtp" onclick="prosesVerifikasiOtp()" class="bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold py-2.5 px-6 rounded-md shadow-sm transition text-sm">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 3: NOTIFIKASI VERIFIKASI BERHASIL -->
    <!-- ============================================== -->
    <div id="modalVerifikasiSukses" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="relative bg-white rounded-[15px] modal-shadow w-full max-w-[380px] p-8 z-10 flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-3xl mb-4">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Verifikasi Berhasil!</h3>
            <p class="text-xs text-gray-500 mb-6">Nomor telepon baru Anda telah terverifikasi. Tekan lanjut untuk memasang pada formulir profil.</p>
            <button type="button" onclick="lanjutSetelahVerifikasi()" class="w-full bg-[#6DC355] hover:bg-green-600 text-white font-bold py-2.5 px-4 rounded-md transition text-sm">
                Lanjut
            </button>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 4: UBAH PASSWORD -->
    <!-- ============================================== -->
    <div id="modalUbahPassword" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="relative bg-white rounded-[15px] modal-shadow w-full max-w-[440px] px-8 py-10 z-10">
            <h3 class="text-[20px] font-bold text-gray-700 mb-6 text-center">Ubah Password</h3>

            <form action="{{ route('pengaturan.password.update') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-500 font-bold text-xs mb-1">Password Lama</label>
                    <input type="password" name="password_lama" required placeholder="Masukkan password lama"
                        class="w-full border-2 border-gray-200 rounded-md px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-[#5C8A8A]">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-500 font-bold text-xs mb-1">Password Baru</label>
                    <input type="password" name="password_baru" minlength="8" required placeholder="Minimal 8 karakter"
                        class="w-full border-2 border-gray-200 rounded-md px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-[#5C8A8A]">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-500 font-bold text-xs mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" minlength="8" required placeholder="Ulangi password baru"
                        class="w-full border-2 border-gray-200 rounded-md px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-[#5C8A8A]">
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="tutupModalPassword()" class="px-5 py-2 rounded-md border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 rounded-md bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold text-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FORM LOGOUT TERSEMBUNYI -->
    <form id="formLogout" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <script>
        let nomorBaruTerverifikasi = '';

        function bukaProfil() {
            document.getElementById('pengaturanView').classList.add('hidden');
            document.getElementById('profilView').classList.remove('hidden');
        }

        function tutupProfil() {
            document.getElementById('profilView').classList.add('hidden');
            document.getElementById('pengaturanView').classList.remove('hidden');
        }

        function bukaModalTelp() {
            document.getElementById('errorModalTelp').classList.add('hidden');
            document.getElementById('inputTelpBaru').value = '';
            document.getElementById('modalUbahTelp').classList.remove('hidden');
        }

        function tutupModalTelp() {
            document.getElementById('modalUbahTelp').classList.add('hidden');
        }

        function mintaOtpTelpBaru() {
            let telpLama = document.getElementById('inputTelpLama').value.trim();
            let telpBaru = document.getElementById('inputTelpBaru').value.trim();
            let errBox = document.getElementById('errorModalTelp');
            errBox.classList.add('hidden');

            if (!telpLama || !telpBaru) {
                errBox.innerText = 'Nomor telepon lama dan nomor baru wajib diisi.';
                errBox.classList.remove('hidden');
                return;
            }

            let btn = document.getElementById('btnKirimOtp');
            btn.disabled = true;
            btn.innerText = 'Mengirim...';

            fetch('{{ route('pengaturan.profil.request-otp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    telepon_lama: telpLama,
                    telepon_baru: telpBaru
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(res => {
                btn.disabled = false;
                btn.innerText = 'Simpan';
                if (res.status === 200 && res.body.success) {
                    nomorBaruTerverifikasi = res.body.telepon_baru;
                    tutupModalTelp();
                    document.getElementById('inputKodeOtp').value = '';
                    document.getElementById('errorModalVerif').classList.add('hidden');
                    document.getElementById('modalVerifikasiTelp').classList.remove('hidden');
                } else {
                    errBox.innerText = res.body.message || 'Terjadi kesalahan saat memproses permintaan.';
                    errBox.classList.remove('hidden');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Simpan';
                errBox.innerText = 'Gagal menghubungi server.';
                errBox.classList.remove('hidden');
            });
        }

        function tutupModalVerifikasi() {
            document.getElementById('modalVerifikasiTelp').classList.add('hidden');
        }

        function prosesVerifikasiOtp() {
            let kode = document.getElementById('inputKodeOtp').value.trim();
            let errBox = document.getElementById('errorModalVerif');
            errBox.classList.add('hidden');

            if (kode.length !== 6) {
                errBox.innerText = 'Kode verifikasi harus 6 digit angka.';
                errBox.classList.remove('hidden');
                return;
            }

            let btn = document.getElementById('btnVerifOtp');
            btn.disabled = true;
            btn.innerText = 'Memverifikasi...';

            fetch('{{ route('pengaturan.profil.verify-otp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ kode: kode })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(res => {
                btn.disabled = false;
                btn.innerText = 'Simpan';
                if (res.status === 200 && res.body.success) {
                    nomorBaruTerverifikasi = res.body.telepon_baru || nomorBaruTerverifikasi;
                    tutupModalVerifikasi();
                    document.getElementById('modalVerifikasiSukses').classList.remove('hidden');
                } else {
                    errBox.innerText = res.body.message || 'Kode verifikasi salah.';
                    errBox.classList.remove('hidden');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Simpan';
                errBox.innerText = 'Gagal menghubungi server.';
                errBox.classList.remove('hidden');
            });
        }

        function lanjutSetelahVerifikasi() {
            document.getElementById('modalVerifikasiSukses').classList.add('hidden');
            if (nomorBaruTerverifikasi) {
                document.getElementById('inputNoHp').value = nomorBaruTerverifikasi;
            }
        }

        function bukaModalPassword() {
            document.getElementById('modalUbahPassword').classList.remove('hidden');
        }

        function tutupModalPassword() {
            document.getElementById('modalUbahPassword').classList.add('hidden');
        }

        function konfirmasiKeluar() {
            if (confirm('Apakah Anda yakin ingin keluar dari akun?')) {
                document.getElementById('formLogout').submit();
            }
        }
    </script>
</body>
</html>
