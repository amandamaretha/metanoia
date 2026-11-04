<?php

namespace App\Services;

class ManualWeighingSource implements WeighingSource
{
    /**
     * Dapatkan berat dari konteks input petugas.
     *
     * @param  array<string, mixed>  $context
     */
    public function getWeight(array $context = []): float
    {
        $berat = $context['berat'] ?? 0;

        if (is_string($berat)) {
            $berat = str_replace(',', '.', trim($berat));
        }

        return (float) $berat;
    }
}
