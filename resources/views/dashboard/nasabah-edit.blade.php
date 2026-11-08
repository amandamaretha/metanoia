<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Nasabah - Metanoia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .bg-sidebar { background-color: #5C8A8A; }
        .text-sidebar-active { color: #5C8A8A; }
    </style>
</head>
<body class="bg-gray-200 h-screen font-sans relative">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-sidebar flex flex-col text-white justify-between relative z-10">
            <div>
                <div class="flex flex-col items-center mt-10 mb-8">
                    <div class="w-24 h-24 bg-blue-200 rounded-full flex items-center justify-center overflow-hidden border-4 border-white/20">
                        <img src="https://i.pravatar.cc/150?img=12" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <h2 class="mt-4 text-lg font-bold">Ammirudin suep</h2>
                </div>

                <nav class="px-4 space-y-2">
                    <!-- Menu Dashboard -->
                    <a href="{{ route('pengelola.dashboard') }}"
                       class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.dashboard') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-house w-6"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Menu Nasabah -->
                    <a href="{{ route('pengelola.nasabah') }}"
                       class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.nasabah*') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-users w-6"></i>
                        <span>Nasabah</span>
                    </a>

                    <!-- Menu Transaksi -->
                    <a href="{{ route('pengelola.transaksi') }}"
                       class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.transaksi') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-money-bill-transfer w-6"></i>
                        <span>Transaksi</span>
                    </a>

                    <a href="{{ route('pengelola.penimbangan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition-colors">
                        <i class="fa-solid fa-scale-balanced w-6"></i>
                        <span>Penimbangan</span>
                    </a>
                    <a href="{{ route('pengelola.detail-sampah') }}"
                        class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.detail-sampah') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-trash w-6"></i>
                        <span>Detail Sampah</span>
                    </a>
                    <a href="{{ route('pengelola.laporan') }}"
                       class="flex items-center px-4 py-3 rounded-lg transition-colors {{ Request::routeIs('pengelola.laporan') ? 'bg-gray-100 text-sidebar-active font-bold' : 'hover:bg-white/10 font-semibold' }}">
                        <i class="fa-solid fa-file-lines w-6"></i>
                        <span>Laporan</span>
                    </a>
                </nav>
            </div>

            <div class="p-4 mb-4">
                <a href="{{ route('pengelola.pengaturan') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition-colors">
                    <i class="fa-solid fa-gear w-6"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-8 overflow-y-auto relative z-0">
            <h1 class="text-3xl font-extrabold text-black mb-6">Edit Data Nasabah</h1>

            <!-- FORM CONTAINER -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-300 max-w-4xl mx-auto">

                <!-- Header Card & Avatar -->
                <div class="bg-gray-200 h-40 flex items-center justify-center rounded-t-lg relative">
                    <div class="w-32 h-32 bg-[#F6CA44] rounded-full border-4 border-gray-200 flex items-center justify-center overflow-hidden absolute -bottom-10 shadow-sm">
                        <img src="https://i.pravatar.cc/150?img=47" alt="Profile Nasabah" class="w-full h-full object-cover">
                    </div>
                </div>
                <div class="h-14"></div>

                <form action="#" method="POST">
                    <!-- Row: Nama Nasabah (Tanpa Pensil) -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium">Nama Nasabah</label>
                        <div class="w-2/3">
                            <input type="text" value="Hafidh Tedi Setiawan" class="border border-gray-300 rounded-md px-3 py-2 w-full max-w-md focus:outline-none focus:border-[#5C8A8A]">
                        </div>
                    </div>

                    <!-- Row: Saldo (DENGAN PENSIL) -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium">Saldo</label>
                        <div class="w-2/3 flex items-center gap-3">
                            <span class="text-gray-700 font-medium">Rp</span>
                            <input type="text" id="mainSaldoInput" value="300.000" readonly class="border border-gray-300 bg-gray-50 rounded-md px-3 py-2 w-full max-w-sm focus:outline-none cursor-not-allowed">
                            <i onclick="openEditModal('saldo', 'Saldo')" class="fa-solid fa-pen text-gray-500 cursor-pointer hover:text-blue-500 transition-colors p-2"></i>
                        </div>
                    </div>

                    <!-- Row: NIK (Tanpa Pensil) -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium">NIK</label>
                        <div class="w-2/3">
                            <input type="text" value="089648354570" class="border border-gray-300 rounded-md px-3 py-2 w-full max-w-md focus:outline-none focus:border-[#5C8A8A]">
                        </div>
                    </div>

                    <!-- Row: No Telepon (DENGAN PENSIL) -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium">No Telepon</label>
                        <div class="w-2/3 flex items-center gap-3">
                            <input type="text" id="mainTeleponInput" value="089648354570" readonly class="border border-gray-300 bg-gray-50 rounded-md px-3 py-2 w-full max-w-md focus:outline-none cursor-not-allowed">
                            <i onclick="openEditModal('telepon', 'No Telepon')" class="fa-solid fa-pen text-gray-500 cursor-pointer hover:text-blue-500 transition-colors p-2"></i>
                        </div>
                    </div>

                    <!-- Row: Alamat (Tanpa Pensil) -->
                    <div class="flex items-start border-t border-gray-200 px-8 py-4">
                        <label class="w-1/3 text-gray-700 font-medium pt-2">Alamat</label>
                        <div class="w-2/3">
                            <textarea rows="3" class="border border-gray-300 rounded-md px-3 py-2 w-full max-w-md focus:outline-none focus:border-[#5C8A8A]">Perum Graha Pratama&#13;&#10;Permai Blok C3</textarea>
                        </div>
                    </div>

                    <!-- Row: Password (DENGAN PENSIL) -->
                    <div class="flex items-center border-t border-gray-200 px-8 py-4 rounded-b-lg">
                        <label class="w-1/3 text-gray-700 font-medium">Password</label>
                        <div class="w-2/3 flex items-center gap-3">
                            <!-- Input tipe text atau password, sementara dibiarkan text sesuai desain -->
                            <input type="text" id="mainPasswordInput" value="Hafidh2345!" readonly class="border border-gray-300 bg-gray-50 rounded-md px-3 py-2 w-full max-w-md focus:outline-none cursor-not-allowed">
                            <i onclick="openEditModal('password', 'Password')" class="fa-solid fa-pen text-gray-500 cursor-pointer hover:text-blue-500 transition-colors p-2"></i>
                        </div>
                    </div>
                </form>
            </div>

            <div class="flex justify-center gap-4 mt-8 mb-12">
                <button type="button" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-3 px-12 rounded-lg shadow-sm transition-colors text-lg">
                    Simpan
                </button>
                <a href="{{ route('pengelola.nasabah') }}" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-3 px-12 rounded-lg shadow-sm transition-colors text-lg">
                    Batal
                </a>
            </div>

        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL EDIT DINAMIS (Menggabungkan Saldo, No.Telp, & Password) -->
    <!-- ============================================== -->
    <div id="dynamicEditModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60 transition-opacity" onclick="closeEditModal()"></div>

        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden transform transition-all">
            <div class="h-12 bg-[#5C8A8A]"></div>

            <div class="px-10 py-10">
                <!-- Teks Peringatan Dinamis -->
                <p class="text-gray-800 text-[17px] text-center mb-8">
                    Perhatikan Dengan Baik, Anda Sedang Mengubah <span id="modalWarningText" class="font-bold">Saldo</span> Nasabah
                </p>

                <!-- CONTAINER 1: UNTUK SALDO & NO TELEPON (Satu Input) -->
                <div id="singleInputContainer" class="flex flex-col items-center justify-center gap-4 mb-8 hidden">
                    <div class="flex items-center gap-3">
                        <label id="modalInputLabel" class="text-gray-800 font-medium whitespace-nowrap w-40 text-right">Saldo Nasabah : </label>
                        <span id="modalPrefixRp" class="hidden text-gray-800 font-medium">Rp</span>
                        <input type="text" id="modalGenericInput" class="border border-gray-300 rounded-md px-3 py-2 w-64 focus:outline-none focus:border-[#5C8A8A]">
                    </div>
                </div>

                <!-- CONTAINER 2: UNTUK PASSWORD (Dua Input) -->
                <div id="passwordInputContainer" class="flex flex-col items-center justify-center gap-4 mb-8 hidden">
                    <!-- Password Baru -->
                    <div class="flex items-center gap-3">
                        <label class="text-gray-800 font-medium whitespace-nowrap w-44 text-right">Password Baru :</label>
                        <input type="text" id="modalPasswordNew" class="border border-gray-300 rounded-md px-3 py-2 w-64 focus:outline-none focus:border-[#5C8A8A]">
                    </div>
                    <!-- Konfirmasi Password -->
                    <div class="flex items-center gap-3">
                        <label class="text-gray-800 font-medium whitespace-nowrap w-44 text-right">Konfirmasi Password :</label>
                        <input type="text" id="modalPasswordConfirm" class="border border-gray-300 rounded-md px-3 py-2 w-64 focus:outline-none focus:border-[#5C8A8A]">
                    </div>
                    <!-- Pesan Error jika Password tidak cocok -->
                    <p id="passwordError" class="text-red-500 text-sm hidden">Konfirmasi password tidak cocok!</p>
                </div>

                <!-- Tombol Action Modal -->
                <div class="flex justify-center gap-6">
                    <button type="button" onclick="saveToMainForm()" class="bg-[#6DC355] hover:bg-green-600 text-white font-bold py-3 px-10 rounded-lg shadow-md transition-colors text-lg">
                        Simpan
                    </button>
                    <button type="button" onclick="closeEditModal()" class="bg-[#7F7F7F] hover:bg-gray-600 text-white font-bold py-3 px-10 rounded-lg shadow-md transition-colors text-lg">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT JAVASCRIPT UNTUK MODAL DINAMIS -->
    <script>
        let activeFieldId = '';

        function openEditModal(fieldId, fieldName) {
            activeFieldId = fieldId;

            // Atur teks peringatan
            document.getElementById('modalWarningText').innerText = fieldName;

            // Referensi Elemen Container
            const singleContainer = document.getElementById('singleInputContainer');
            const passwordContainer = document.getElementById('passwordInputContainer');

            // Reset Error Message
            document.getElementById('passwordError').classList.add('hidden');

            if (fieldId === 'password') {
                // TAMPILKAN MODAL KHUSUS PASSWORD (2 INPUT)
                singleContainer.classList.add('hidden');
                passwordContainer.classList.remove('hidden');

                // Kosongkan form password setiap kali dibuka
                document.getElementById('modalPasswordNew').value = '';
                document.getElementById('modalPasswordConfirm').value = '';

            } else {
                // TAMPILKAN MODAL STANDAR (1 INPUT UNTUK SALDO/NO TELP)
                passwordContainer.classList.add('hidden');
                singleContainer.classList.remove('hidden');

                document.getElementById('modalInputLabel').innerText = fieldName + " :";

                if (fieldId === 'saldo') {
                    document.getElementById('modalPrefixRp').classList.remove('hidden');
                } else {
                    document.getElementById('modalPrefixRp').classList.add('hidden');
                }

                const inputId = 'main' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1) + 'Input';
                const currentValue = document.getElementById(inputId).value;
                document.getElementById('modalGenericInput').value = currentValue;
            }

            // Tampilkan Modal
            document.getElementById('dynamicEditModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('dynamicEditModal').classList.add('hidden');
        }

        function saveToMainForm() {
            const targetInputId = 'main' + activeFieldId.charAt(0).toUpperCase() + activeFieldId.slice(1) + 'Input';

            if (activeFieldId === 'password') {
                // Validasi khusus untuk Password
                const newPass = document.getElementById('modalPasswordNew').value;
                const confirmPass = document.getElementById('modalPasswordConfirm').value;

                if (newPass === '') {
                    alert('Password tidak boleh kosong!');
                    return;
                }

                if (newPass !== confirmPass) {
                    // Tampilkan pesan error
                    document.getElementById('passwordError').classList.remove('hidden');
                    return; // Hentikan proses simpan
                }

                // Jika lolos validasi, simpan ke form utama
                document.getElementById(targetInputId).value = newPass;

            } else {
                // Proses simpan untuk Saldo / No Telepon
                const newValue = document.getElementById('modalGenericInput').value;
                document.getElementById(targetInputId).value = newValue;
            }

            // Tutup Modal setelah selesai
            closeEditModal();
        }
    </script>
</body>
</html>
