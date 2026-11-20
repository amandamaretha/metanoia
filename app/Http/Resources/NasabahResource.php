<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NasabahResource extends JsonResource
{
    /**
     * Disable wrapping for this resource.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Menentukan apakah NIK dan alamat disertakan dalam output.
     */
    protected bool $includeSensitive;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  bool  $includeSensitive
     */
    public function __construct($resource, bool $includeSensitive = false)
    {
        parent::__construct($resource);
        $this->includeSensitive = $includeSensitive;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'nama' => $this->nama,
            'no_telp' => $this->no_telp,
            'saldo' => (int) $this->saldo,
            'status' => $this->status,
        ];

        if ($this->includeSensitive) {
            $data['nik'] = $this->nik;
            $data['alamat'] = $this->alamat;
        }

        return $data;
    }
}
