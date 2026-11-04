<?php

namespace App\Services;

use App\Models\HargaSampah;
use App\Models\MutasiSaldo;
use App\Models\Nasabah;
use App\Models\Nota;
use App\Models\Setoran;
use App\Support\TransactionCalculator;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SetoranService
{
    /**
     * SetoranService dalam satu DB::transaction dengan lockForUpdate pada nasabah:
     * - Cek status nasabah (harus aktif)
     * - Validasi berat (> 0)
     * - Ambil harga aktif hari ini
     * - Hitung total nilai menggunakan TransactionCalculator
     * - Simpan setoran dengan harga snapshot
     * - Tambah saldo nasabah
     * - Tulis mutasi_saldo (jenis setoran, saldo_sesudah)
     * - Buat nota dengan nomor unik yang aman dari balapan (retry loop)
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
        $berat = $source->getWeight(['berat' => $beratInput]);

        if ($berat <= 0) {
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
            ->latest('tanggal_mulai')
            ->first();

        if (! $hargaAktif) {
            throw new InvalidArgumentException('Harga sampah aktif tidak ditemukan untuk jenis dan kondisi ini pada hari ini.');
        }

        $hargaPerKg = $hargaAktif->harga_per_kg;
        $totalNilai = TransactionCalculator::calculate($berat, $hargaPerKg);

        return DB::transaction(function () use (
            $nasabahId,
            $petugasId,
            $jenisSampahId,
            $kondisi,
            $berat,
            $hargaPerKg,
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
                'berat' => $berat,
                'harga_per_kg' => $hargaPerKg,
                'total_nilai' => $totalNilai,
                'tanggal' => now(),
            ]);

            // Tambah saldo nasabah
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

            // Buat nota dengan nomor unik aman balapan (retry loop)
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
     * Buat nota baru dengan nomor unik, menerapkan retry jika terjadi race condition (tabrakan nomor).
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
            } catch (UniqueConstraintViolationException|QueryException $e) {
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Gagal menghasilkan nomor nota unik setelah beberapa percobaan.');
    }
}
