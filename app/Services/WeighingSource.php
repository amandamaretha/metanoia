<?php

namespace App\Services;

interface WeighingSource
{
    /**
     * Dapatkan berat sampah dalam kilogram.
     *
     * @param  array<string, mixed>  $context
     */
    public function getWeight(array $context = []): float;
}
