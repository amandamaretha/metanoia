<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Akun Petugas - Metanoia</title>
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
            <div class="max-w-3xl mx-auto w-full">
                <div class="flex items-center gap-4 mb-6">
                    <a href="{{ route('pengelola.petugas.index') }}" class="text-gray-600 hover:text-black transition">
                        <i class="fa-solid fa-arrow-left text-2xl"></i>
                    </a>
                    <h1 class="text-3xl font-extrabold text-black">Detail Akun Petugas</h1>
                </div>

                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8">
                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                            <span class="font-bold block mb-1">Periksa kembali data Anda:</span>
                            <ul class="list-disc list-inside text-xs">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="formEditPetugas" action="{{ route('pengelola.petugas.update', $petugas) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- NAMA PETUGAS: KETIK LANGSUNG DENGAN IKON PENSIL -->
                        <div class="mb-6">
                            <label for="nama" class="block text-sm font-bold text-gray-700 mb-1">Nama Petugas <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" name="nama" id="nama" value="{{ old('nama', $petugas->nama) }}" required
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#5C8A8A] text-sm pr-10">
                                <i class="fa-solid fa-pen absolute right-3 top-3 text-gray-400 cursor-pointer" onclick="document.getElementById('nama').focus()"></i>
                            </div>
                        </div>

                        <!-- NOMOR TELEPON: MODAL PERINGATAN DENGAN IKON PENSIL -->
                        <div class="mb-6">
                            <label for="no_telp" class="block text-sm font-bold text-gray-700 mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-3">
                                <div class="relative flex-1">
                                    <input type="text" name="no_telp" id="no_telp" value="{{ old('no_telp', $petugas->no_telp) }}" readonly required
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 text-sm cursor-not-allowed pr-10">
                                    <i class="fa-solid fa-pen absolute right-3 top-3 text-[#5C8A8A] cursor-pointer" onclick="bukaModalEditTelp()"></i>
                                </div>
                                <button type="button" onclick="bukaModalEditTelp()" class="text-xs text-[#5C8A8A] font-bold underline hover:text-[#4a7272]">
                                    Ubah No Telp
                                </button>
                            </div>
                        </div>

                        <!-- JABATAN: MODAL PILIHAN JABATAN DENGAN IKON PENSIL -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-3">
                                <div class="relative flex-1">
                                    <input type="text" id="displayJabatan" value="{{ ucfirst($petugas->jabatan) }}" readonly
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 text-sm capitalize cursor-not-allowed pr-10">
                                    <input type="hidden" name="jabatan" id="inputJabatan" value="{{ old('jabatan', $petugas->jabatan) }}">
                                    <i class="fa-solid fa-pen absolute right-3 top-3 text-[#5C8A8A] cursor-pointer" onclick="bukaModalJabatan()"></i>
                                </div>
                                <button type="button" onclick="bukaModalJabatan()" class="text-xs text-[#5C8A8A] font-bold underline hover:text-[#4a7272]">
                                    Ubah Jabatan
                                </button>
                            </div>
                        </div>

                        <!-- PASSWORD: MODAL PASSWORD DENGAN IKON PENSIL -->
                        <div class="mb-8">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                            <div class="flex items-center gap-3">
                                <div class="relative flex-1">
                                    <input type="password" value="********" readonly
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 text-sm cursor-not-allowed pr-10 tracking-widest">
                                    <input type="hidden" name="password" id="inputPassword" value="">
                                    <input type="hidden" name="konfirmasi_password" id="inputKonfirmasiPassword" value="">
                                    <i class="fa-solid fa-pen absolute right-3 top-3 text-[#5C8A8A] cursor-pointer" onclick="bukaModalEditPass()"></i>
                                </div>
                                <button type="button" onclick="bukaModalEditPass()" class="text-xs text-[#5C8A8A] font-bold underline hover:text-[#4a7272]">
                                    Ubah Password
                                </button>
                            </div>
                            <span id="labelPasswordStatus" class="text-xs text-gray-400 block mt-1">Kosongkan bila tidak ingin mengubah password akun ini.</span>
                        </div>

                        <!-- TOMBOL AKSI: SIMPAN & BATAL -->
                        <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6">
                            <a href="{{ route('pengelola.petugas.index') }}" class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <button type="submit" class="px-8 py-2.5 rounded-lg bg-[#5C8A8A] hover:bg-[#4a7272] text-white font-bold text-sm shadow-sm transition">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL 1: PERINGATAN UBAH NO TELP -->
    <div id="modalEditTelp" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-center">
            <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800 mb-2">
                Perhatikan dengan baik, Anda sedang mengubah No. Telp Petugas
            </h3>
            <p class="text-xs text-gray-500 mb-4">Pastikan nomor telepon baru aktif dan dapat dihubungi.</p>

            <div class="mb-6 text-left">
                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Telepon Baru</label>
                <input type="text" id="tempNoTelp" placeholder="08xxxxxxxxxxx" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5C8A8A]">
            </div>

            <div class="flex items-center justify-center gap-4">
                <button type="button" onclick="tutupModalEditTelp()" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 w-28">
                    Batal
                </button>
                <button type="button" onclick="simpanTempNoTelp()" class="px-5 py-2 rounded-lg bg-[#5C8A8A] text-white text-sm font-bold hover:bg-[#4a7272] w-28">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: PILIHAN JABATAN -->
    <div id="modalJabatan" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center">
            <h3 class="text-base font-bold text-gray-800 mb-4">Pilih Jabatan Petugas</h3>

            <div class="space-y-2 mb-6 text-left text-sm">
                <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="tempJabatanRadio" value="pengelola" class="w-4 h-4 text-[#5C8A8A]">
                    <span class="font-semibold text-gray-700">Pengelola</span>
                </label>
                <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="tempJabatanRadio" value="bendahara" class="w-4 h-4 text-[#5C8A8A]">
                    <span class="font-semibold text-gray-700">Bendahara</span>
                </label>
                <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="tempJabatanRadio" value="ketua" class="w-4 h-4 text-[#5C8A8A]">
                    <span class="font-semibold text-gray-700">Ketua Bank Sampah</span>
                </label>
            </div>

            <div class="flex items-center justify-center gap-4">
                <button type="button" onclick="tutupModalJabatan()" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 w-28">
                    Batal
                </button>
                <button type="button" onclick="simpanTempJabatan()" class="px-5 py-2 rounded-lg bg-[#5C8A8A] text-white text-sm font-bold hover:bg-[#4a7272] w-28">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: UBAH PASSWORD PETUGAS -->
    <div id="modalEditPass" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">
            <h3 class="text-base font-bold text-gray-800 mb-4 text-center">Ubah Password Petugas</h3>

            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 mb-1">Password Baru</label>
                <input type="password" id="tempPassword" placeholder="Minimal 8 karakter" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5C8A8A]">
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input type="password" id="tempKonfirmasiPassword" placeholder="Ketik ulang password baru" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5C8A8A]">
            </div>

            <div class="flex items-center justify-center gap-4">
                <button type="button" onclick="tutupModalEditPass()" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 w-28">
                    Batal
                </button>
                <button type="button" onclick="simpanTempPassword()" class="px-5 py-2 rounded-lg bg-[#5C8A8A] text-white text-sm font-bold hover:bg-[#4a7272] w-28">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <script>
        function bukaModalEditTelp() {
            document.getElementById('tempNoTelp').value = document.getElementById('no_telp').value;
            document.getElementById('modalEditTelp').classList.remove('hidden');
        }

        function tutupModalEditTelp() {
            document.getElementById('modalEditTelp').classList.add('hidden');
        }

        function simpanTempNoTelp() {
            let val = document.getElementById('tempNoTelp').value.trim();
            if (!val) {
                alert('Nomor telepon tidak boleh kosong.');
                return;
            }
            document.getElementById('no_telp').value = val;
            tutupModalEditTelp();
        }

        function bukaModalJabatan() {
            let current = document.getElementById('inputJabatan').value;
            let radios = document.getElementsByName('tempJabatanRadio');
            for (let r of radios) {
                if (r.value === current) r.checked = true;
            }
            document.getElementById('modalJabatan').classList.remove('hidden');
        }

        function tutupModalJabatan() {
            document.getElementById('modalJabatan').classList.add('hidden');
        }

        function simpanTempJabatan() {
            let selected = document.querySelector('input[name="tempJabatanRadio"]:checked');
            if (selected) {
                document.getElementById('inputJabatan').value = selected.value;
                let label = selected.value === 'ketua' ? 'Ketua Bank Sampah' : selected.value;
                document.getElementById('displayJabatan').value = label.charAt(0).toUpperCase() + label.slice(1);
            }
            tutupModalJabatan();
        }

        function bukaModalEditPass() {
            document.getElementById('tempPassword').value = '';
            document.getElementById('tempKonfirmasiPassword').value = '';
            document.getElementById('modalEditPass').classList.remove('hidden');
        }

        function tutupModalEditPass() {
            document.getElementById('modalEditPass').classList.add('hidden');
        }

        function simpanTempPassword() {
            let p1 = document.getElementById('tempPassword').value.trim();
            let p2 = document.getElementById('tempKonfirmasiPassword').value.trim();

            if (!p1 || !p2) {
                alert('Password dan konfirmasi password tidak boleh kosong.');
                return;
            }

            if (p1 !== p2) {
                alert('Password dan konfirmasi password tidak sama.');
                return;
            }

            document.getElementById('inputPassword').value = p1;
            document.getElementById('inputKonfirmasiPassword').value = p2;
            document.getElementById('labelPasswordStatus').innerText = 'Password baru telah disiapkan untuk disimpan.';
            document.getElementById('labelPasswordStatus').classList.remove('text-gray-400');
            document.getElementById('labelPasswordStatus').classList.add('text-green-600', 'font-semibold');
            tutupModalEditPass();
        }
    </script>
</body>
</html>
