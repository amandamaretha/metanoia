<?php

namespace App\Services;

use App\Models\HargaSampah;
use App\Models\MutasiSaldo;
use App\Models\Nasabah;
use App\Models\Nota;
use App\Models\Setoran;
use App\Support\TransactionCalculator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SetoranService
{
    /**
     * SetoranService dalam satu DB::transaction dengan lockForUpdate pada nasabah:
     * - Cek status nasabah (harus aktif)
     * - Validasi berat (> 0, maksimal 2 desimal tanpa float)
     * - Ambil harga aktif hari ini (urut tanggal_mulai desc, id desc)
     * - Hitung total nilai menggunakan TransactionCalculator dengan pembulatan kelipatan
     * - Tolak jika nilai terbulatkan 0 ("Nilai setoran terlalu kecil.")
     * - Simpan setoran dengan nilai_sebelum_pembulatan, pembulatan, dan total_nilai
     * - Tambah saldo nasabah
     * - Tulis mutasi_saldo (jenis setoran, saldo_sesudah)
     * - Buat nota dengan nomor unik yang aman dari balapan (retry loop khusus UniqueConstraintViolationException)
     * Invarian: saldo nasabah = saldo_sesudah mutasi terakhir.
     *
     * @return array{setoran: Setoran, nota: Nota, mutasi: MutasiSaldo, nasabah: Nasabah}
     *
     * @throws InvalidArgumentException
     */
    public function processSetoran(
        int $nasabahId,
        int $petugasId,
        int $jenisSampahId,
        string $kondisi,
        float|int|string $beratInput,
        ?WeighingSource $weighingSource = null
    ): array {
        if (! in_array($kondisi, ['sudah_dipilah', 'belum_dipilah', 'campuran'], true)) {
            throw new InvalidArgumentException("Kondisi sampah '{$kondisi}' tidak valid.");
        }

        $source = $weighingSource ?? new ManualWeighingSource;
        $beratRaw = $source->getWeight(['berat' => $beratInput]);

        // Normalisasi dan validasi berat tanpa operasi float (maksimal 2 desimal)
        $beratStr = TransactionCalculator::normalizeWeight($beratRaw);

        if (bccomp($beratStr, '0', 4) <= 0) {
            throw new InvalidArgumentException('Berat sampah harus lebih besar dari 0 kilogram.');
        }

        $today = now()->toDateString();
        $hargaAktif = HargaSampah::where('jenis_sampah_id', $jenisSampahId)
            ->where('kondisi', $kondisi)
            ->where('tanggal_mulai', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_berakhir')
                    ->orWhere('tanggal_berakhir', '>=', $today);
            })
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();

        if (! $hargaAktif) {
            throw new InvalidArgumentException('Harga sampah aktif tidak ditemukan untuk jenis dan kondisi ini pada hari ini.');
        }

        $hargaPerKg = $hargaAktif->harga_per_kg;

        // Hitung nilai terbulatkan dan rincian pembulatan
        $calc = TransactionCalculator::calculateDetails($beratStr, $hargaPerKg);
        $nilaiSebelumPembulatan = $calc['nilai_sebelum_pembulatan'];
        $pembulatan = $calc['pembulatan'];
        $totalNilai = $calc['total_nilai'];

        if ($totalNilai <= 0) {
            throw new InvalidArgumentException('Nilai setoran terlalu kecil.');
        }

        return DB::transaction(function () use (
            $nasabahId,
            $petugasId,
            $jenisSampahId,
            $kondisi,
            $beratStr,
            $hargaPerKg,
            $nilaiSebelumPembulatan,
            $pembulatan,
            $totalNilai
        ) {
            /** @var Nasabah $nasabah */
            $nasabah = Nasabah::where('id', $nasabahId)->lockForUpdate()->firstOrFail();

            if ($nasabah->status !== 'aktif') {
                throw new InvalidArgumentException('Akun nasabah tidak aktif. Transaksi setoran tidak dapat dilanjutkan.');
            }

            $setoran = Setoran::create([
                'nasabah_id' => $nasabah->id,
                'petugas_id' => $petugasId,
                'jenis_sampah_id' => $jenisSampahId,
                'kondisi' => $kondisi,
                'berat' => $beratStr,
                'harga_per_kg' => $hargaPerKg,
                'nilai_sebelum_pembulatan' => $nilaiSebelumPembulatan,
                'pembulatan' => $pembulatan,
                'total_nilai' => $totalNilai,
                'tanggal' => now(),
            ]);

            // Tambah saldo nasabah memakai total_nilai terbulatkan
            $nasabah->saldo += $totalNilai;
            $nasabah->save();

            // Tulis mutasi saldo
            $mutasi = MutasiSaldo::create([
                'nasabah_id' => $nasabah->id,
                'jenis' => 'setoran',
                'jumlah' => $totalNilai,
                'saldo_sesudah' => $nasabah->saldo,
                'referensi_tipe' => Setoran::class,
                'referensi_id' => $setoran->id,
            ]);

            // Buat nota dengan nomor unik aman balapan (retry loop khusus UniqueConstraintViolationException)
            $nota = $this->createUniqueNota($setoran->id);

            return [
                'setoran' => $setoran,
                'nota' => $nota,
                'mutasi' => $mutasi,
                'nasabah' => $nasabah,
            ];
        });
    }

    /**
     * Buat nota baru dengan nomor unik, hanya melakukan retry jika terjadi tabrakan nomor unik (UniqueConstraintViolationException).
     */
    protected function createUniqueNota(int $setoranId, int $maxAttempts = 5): Nota
    {
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $nomor = 'NOTA-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

                return Nota::create([
                    'setoran_id' => $setoranId,
                    'nomor' => $nomor,
                    'tanggal' => now(),
                ]);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Gagal menghasilkan nomor nota unik setelah beberapa percobaan.');
    }
}
