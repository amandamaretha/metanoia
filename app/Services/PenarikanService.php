<?php

namespace App\Services;

use App\Models\MutasiSaldo;
use App\Models\Nasabah;
use App\Models\Penarikan;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PenarikanService
{
    /**
     * Nasabah mengajukan penarikan saldo tabungan.
     *
     *
     * @throws InvalidArgumentException
     */
    public function ajukan(Nasabah $nasabah, int|string $nominalInput): Penarikan
    {
        // 1. Validasi nominal integer > 0
        if (! is_numeric($nominalInput) || (int) $nominalInput <= 0 || (int) $nominalInput != $nominalInput) {
            throw new InvalidArgumentException('Nominal penarikan harus berupa bilangan bulat lebih dari 0.');
        }

        $nominal = (int) $nominalInput;

        // 2. Periksa status akun
        if ($nasabah->status !== 'aktif') {
            throw new InvalidArgumentException('Akun nasabah tidak aktif. Pengajuan penarikan tidak dapat diproses.');
        }

        // 3. Periksa kecukupan saldo saat pengajuan
        if ($nominal > $nasabah->saldo) {
            throw new InvalidArgumentException('Saldo tabungan Anda tidak mencukupi untuk melakukan penarikan nominal ini.');
        }

        // 4. Periksa apakah sudah ada pengajuan berstatus 'menunggu'
        $adaMenunggu = Penarikan::where('nasabah_id', $nasabah->id)
            ->where('status', 'menunggu')
            ->exists();

        if ($adaMenunggu) {
            throw new InvalidArgumentException('Anda masih memiliki pengajuan penarikan yang menunggu persetujuan.');
        }

        return Penarikan::create([
            'nasabah_id' => $nasabah->id,
            'nominal' => $nominal,
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);
    }

    /**
     * Ketua atau Bendahara menyetujui pengajuan penarikan.
     * Menerapkan DB::transaction + lockForUpdate, cek ulang saldo, kurangi saldo, dan catat mutasi saldo.
     * Jika saldo tidak mencukupi saat disetujui, tolak persetujuan dengan status tetap 'menunggu'.
     * Keputusan hanya dapat diambil sekali.
     *
     * @return array{success: bool, message: string, penarikan: Penarikan, saldo_berubah: bool}
     *
     * @throws InvalidArgumentException
     */
    public function setujui(Penarikan $penarikan, int $petugasId, ?string $catatan = null): array
    {
        return DB::transaction(function () use ($penarikan, $petugasId, $catatan) {
            /** @var Penarikan $lockedPenarikan */
            $lockedPenarikan = Penarikan::where('id', $penarikan->id)->lockForUpdate()->firstOrFail();

            // Keputusan hanya boleh diambil satu kali
            if ($lockedPenarikan->status !== 'menunggu') {
                throw new InvalidArgumentException('Keputusan sudah pernah diambil untuk pengajuan ini.');
            }

            /** @var Nasabah $lockedNasabah */
            $lockedNasabah = Nasabah::where('id', $lockedPenarikan->nasabah_id)->lockForUpdate()->firstOrFail();

            // CEK ULANG SALDO NASABAH
            if ($lockedNasabah->saldo < $lockedPenarikan->nominal) {
                // Saldo tidak cukup saat disetujui -> tolak dengan pesan dan status TETAP "menunggu"
                return [
                    'success' => false,
                    'message' => 'Saldo nasabah saat ini tidak mencukupi untuk disetujui. Status pengajuan tetap menunggu.',
                    'penarikan' => $lockedPenarikan,
                    'saldo_berubah' => false,
                ];
            }

            // Kurangi saldo nasabah
            $lockedNasabah->saldo -= $lockedPenarikan->nominal;
            $lockedNasabah->save();

            // Catat mutasi saldo jenis penarikan
            MutasiSaldo::create([
                'nasabah_id' => $lockedNasabah->id,
                'jenis' => 'penarikan',
                'jumlah' => $lockedPenarikan->nominal,
                'saldo_sesudah' => $lockedNasabah->saldo,
                'referensi_tipe' => Penarikan::class,
                'referensi_id' => $lockedPenarikan->id,
            ]);

            // Update status penarikan
            $lockedPenarikan->update([
                'status' => 'disetujui',
                'catatan' => $catatan,
                'diputuskan_oleh' => $petugasId,
                'tanggal_keputusan' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Pengajuan Berhasil Disetujui!',
                'penarikan' => $lockedPenarikan->fresh(),
                'saldo_berubah' => true,
            ];
        });
    }

    /**
     * Ketua atau Bendahara menolak pengajuan penarikan.
     * Saldo nasabah tidak berubah, status diubah menjadi 'ditolak'.
     * Keputusan hanya boleh diambil sekali.
     *
     * @return array{success: bool, message: string, penarikan: Penarikan}
     *
     * @throws InvalidArgumentException
     */
    public function tolak(Penarikan $penarikan, int $petugasId, ?string $catatan = null): array
    {
        return DB::transaction(function () use ($penarikan, $petugasId, $catatan) {
            /** @var Penarikan $lockedPenarikan */
            $lockedPenarikan = Penarikan::where('id', $penarikan->id)->lockForUpdate()->firstOrFail();

            if ($lockedPenarikan->status !== 'menunggu') {
                throw new InvalidArgumentException('Keputusan sudah pernah diambil untuk pengajuan ini.');
            }

            $lockedPenarikan->update([
                'status' => 'ditolak',
                'catatan' => $catatan,
                'diputuskan_oleh' => $petugasId,
                'tanggal_keputusan' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Mohon Maaf Pengajuan ditolak!',
                'penarikan' => $lockedPenarikan->fresh(),
            ];
        });
    }
}
