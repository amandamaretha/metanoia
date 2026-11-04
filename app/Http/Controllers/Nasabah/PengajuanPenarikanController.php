<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Services\PenarikanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class PengajuanPenarikanController extends Controller
{
    public function __construct(
        protected PenarikanService $penarikanService
    ) {}

    /**
     * Ajukan penarikan nominal tabungan oleh nasabah.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nominal' => ['required', 'integer', 'min:1'],
        ], [
            'nominal.required' => 'Nominal penarikan wajib diisi.',
            'nominal.integer' => 'Nominal penarikan harus berupa bilangan bulat.',
            'nominal.min' => 'Nominal penarikan minimal Rp 1.',
        ]);

        /** @var Nasabah $nasabah */
        $nasabah = Auth::guard('nasabah')->user();

        try {
            $penarikan = $this->penarikanService->ajukan($nasabah, (int) $request->input('nominal'));

            return back()->with('success', 'Pengajuan penarikan sebesar Rp '.number_format($penarikan->nominal, 0, ',', '.').' berhasil dikirim dan menunggu persetujuan Ketua.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['nominal' => $e->getMessage()])->withInput();
        }
    }
}
