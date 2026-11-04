<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Services\QrSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanQrController extends Controller
{
    public function __construct(
        protected QrSessionService $qrSessionService
    ) {}

    /**
     * Endpoint pemindaian QR oleh nasabah yang sedang login.
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        /** @var Nasabah $nasabah */
        $nasabah = Auth::guard('nasabah')->user();

        $result = $this->qrSessionService->claimSession($request->input('token'), $nasabah);

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
