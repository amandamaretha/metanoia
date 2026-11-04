<?php

namespace App\Console\Commands;

use App\Exceptions\InvalidPhoneException;
use App\Models\Petugas;
use App\Services\OtpService;
use Illuminate\Console\Command;

class BuatPetugasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'petugas:buat
                            {--nama= : Nama lengkap petugas}
                            {--telepon= : Nomor telepon petugas}
                            {--jabatan= : Jabatan petugas (pengelola/bendahara/ketua)}
                            {--password= : Kata sandi petugas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat akun petugas baru secara interaktif';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Pembuatan Akun Petugas Metanoia ===');

        // 1. Nama Petugas
        $nama = $this->option('nama') ?: $this->ask('Nama lengkap');
        while (empty(trim((string) $nama))) {
            $this->error('Nama tidak boleh kosong.');
            $nama = $this->ask('Nama lengkap');
        }
        $nama = trim((string) $nama);

        // 2. Nomor Telepon
        $rawTelepon = $this->option('telepon') ?: $this->ask('Nomor telepon');
        while (true) {
            $rawTelepon = trim((string) $rawTelepon);
            if ($rawTelepon === '') {
                $this->error('Nomor telepon tidak boleh kosong.');
                $rawTelepon = $this->ask('Nomor telepon');

                continue;
            }

            try {
                $normalizedTelepon = OtpService::normalizePhoneNumber($rawTelepon);
            } catch (InvalidPhoneException) {
                $this->error('Format nomor telepon tidak valid. Pastikan nomor diawali 08 atau 62.');
                $rawTelepon = $this->ask('Nomor telepon');

                continue;
            }

            if (Petugas::where('no_telp', $normalizedTelepon)->exists()) {
                $this->error("Nomor telepon {$normalizedTelepon} sudah terdaftar untuk petugas lain.");
                $rawTelepon = $this->ask('Nomor telepon');

                continue;
            }

            break;
        }

        // 3. Jabatan
        $allowedJabatan = config('metanoia.jabatan', ['pengelola', 'bendahara', 'ketua']);
        $jabatan = $this->option('jabatan');

        if (! $jabatan || ! in_array($jabatan, $allowedJabatan, true)) {
            $jabatan = $this->choice('Pilih jabatan petugas', $allowedJabatan, 0);
        }

        // 4. Password (lewat prompt tersembunyi / secret)
        $password = $this->option('password') ?: $this->secret('Password');
        while (empty($password) || strlen($password) < 6) {
            $this->error('Password minimal 6 karakter.');
            $password = $this->secret('Password');
        }

        // 5. Simpan ke database tanpa menulis password ke berkas apa pun
        $petugas = new Petugas([
            'nama' => $nama,
            'no_telp' => $normalizedTelepon,
            'password' => $password,
        ]);

        $petugas->forceFill([
            'jabatan' => $jabatan,
        ])->save();

        $this->info("Petugas [{$petugas->nama}] dengan jabatan [{$petugas->jabatan}] dan nomor [{$petugas->no_telp}] berhasil dibuat.");

        return Command::SUCCESS;
    }
}
