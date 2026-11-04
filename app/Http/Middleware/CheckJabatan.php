<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckJabatan
{
    /**
     * Menangani permintaan masuk untuk memeriksa jabatan petugas.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$jabatan): Response
    {
        $petugas = Auth::guard('petugas')->user();

        if (! $petugas) {
            return redirect()->route('login.show', ['role' => 'petugas']);
        }

        if (empty($jabatan)) {
            return $next($request);
        }

        $allowed = [];
        foreach ($jabatan as $j) {
            foreach (explode(',', $j) as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '') {
                    $allowed[] = $trimmed;
                }
            }
        }

        if (in_array('semua', $allowed, true)) {
            $allowed = config('metanoia.jabatan', []);
        }

        if (! in_array($petugas->jabatan, $allowed, true)) {
            abort(403, 'Akses ditolak: jabatan tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
