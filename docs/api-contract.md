# Kontrak API Mobile Nasabah: Metanoia

Versi 1.0 (21 November 2026). Backend: satu project Laravel (Sanctum). Klien: aplikasi mobile Nasabah.
Perubahan yang memengaruhi klien (nama field, status kode, path) diberitahukan ke tim mobile lewat PR sebelum di-merge.

## Status endpoint

| Endpoint | Status |
|---|---|
| `POST /api/auth/register` | READY |
| `POST /api/auth/login` | READY |
| `POST /api/auth/logout` | READY |
| `GET /api/auth/me` | READY |
| Lupa password (3 endpoint) | DITUNDA: belum ada pengirim OTP. Bentuk di bawah bersifat DRAFT |
| Riwayat setoran, nota, pengajuan penarikan, jenis sampah/informasi, edit profil, ubah password | PLANNED: belum ada di backend, bentuk data bisa berubah |

READY berarti kode sudah jadi dan lolos tes backend. Itu tidak berarti ada server bersama yang bisa diakses.

## Konvensi umum

- Base URL: `http://<host>/api` (nilai host berbeda per lingkungan; produksi wajib HTTPS).
- Gunakan path ber-prefix `/api/auth/...`. Path tanpa `/auth` ada di backend tetapi bukan bagian dari kontrak.
- Header di setiap permintaan: `Accept: application/json` dan `Content-Type: application/json`.
- Autentikasi: `Authorization: Bearer <token>` (token didapat dari login).
- Masa berlaku token: **30 hari sejak login** (tidak diperpanjang otomatis). Setelah itu atau setelah logout, token ditolak dengan **401**, lalu arahkan pengguna ke layar login.
- Logout hanya mencabut token yang sedang dipakai. Perangkat lain tetap login.

Format error validasi (422):

```json
{
  "message": "NIK sudah terdaftar.",
  "errors": { "nik": ["NIK sudah terdaftar."] }
}
```

Token tidak valid atau kedaluwarsa (401), body JSON dari Laravel (umumnya `{"message": "Unauthenticated."}`).

Pembatasan percobaan (berbeda per endpoint, perhatikan):

| Endpoint | Batas | Respons saat terlampaui |
|---|---|---|
| Register | 5 permintaan/menit per IP | **429** + header `Retry-After`, body bawaan Laravel |
| Login | 5 percobaan gagal/menit per nomor + IP | **422**, pesan di `errors.no_telp` (bukan 429) |

## 1. POST `/api/auth/register`

Mendaftarkan nasabah baru. **Tidak login otomatis.** Kirim semua data dalam satu permintaan.

Request:

```json
{
  "nama": "string, wajib, maks 255",
  "nik": "string, wajib, tepat 16 digit angka, unik",
  "no_telp": "string, wajib, unik. Format 08xx atau 62xx diterima, disimpan sebagai 62xx",
  "alamat": "string, wajib",
  "password": "string, wajib, minimal 8 karakter",
  "password_confirmation": "harus sama dengan password"
}
```

Respons sukses, 201:

```json
{ "message": "Pendaftaran nasabah berhasil." }
```

Respons gagal: 422 (validasi, contoh pesan: "NIK sudah terdaftar.", "Nomor telepon sudah terdaftar.", "Konfirmasi password tidak cocok.", "NIK harus tepat 16 digit angka."), 429 (terlalu banyak permintaan).
Status awal akun: `aktif`.

## 2. POST `/api/auth/login`

Request:

```json
{ "no_telp": "08123456789", "password": "string" }
```

`no_telp` diterima dalam format 08xx atau 62xx.

Respons sukses, 200:

```json
{
  "token": "1|abc...",
  "token_type": "Bearer",
  "nasabah": {
    "id": 1,
    "nama": "string",
    "no_telp": "62812xxxxxxx",
    "saldo": 0,
    "status": "aktif"
  }
}
```

- Respons login **tidak** memuat `nik` dan `alamat`.
- `saldo` bilangan bulat (Rupiah). `status` bernilai `aktif` atau `tidak_aktif`.
- Nasabah `tidak_aktif` **tetap bisa login**. Transaksi diblokir di sisi backend (fitur transaksi belum tersedia di API).
- `no_telp` dikembalikan dalam format `62xxxx`, bukan `08xxxx`. Sesuaikan tampilan jika perlu.

Respons gagal: 422. Pesan tidak membedakan nomor tidak terdaftar dan password salah: `errors.no_telp: ["Nomor telepon atau password salah."]`. Saat melewati batas percobaan, 422 juga dengan pesan tentang terlalu banyak percobaan di `errors.no_telp`.

## 3. POST `/api/auth/logout`

Header: `Authorization: Bearer <token>`. Tanpa body.

Respons sukses, 200:

```json
{ "message": "Berhasil logout." }
```

Respons gagal: 401.

## 4. GET `/api/auth/me`

Header: `Authorization: Bearer <token>`.

Respons sukses, 200 (objek datar, tanpa pembungkus `data`):

```json
{
  "id": 1,
  "nama": "string",
  "no_telp": "62812xxxxxxx",
  "saldo": 0,
  "status": "aktif",
  "nik": "3201xxxxxxxxxxxx",
  "alamat": "string"
}
```

`nik` dan `alamat` hanya ada di endpoint ini dan hanya untuk pemilik akun. Jangan menyimpan atau mencatat (log) data ini di sisi klien tanpa kebutuhan.

Respons gagal: 401.

## Lupa password: DITUNDA (DRAFT, belum ada di backend)

Fitur ini menunggu keputusan pengirim OTP. Bentuk berikut bisa berubah.

- `POST /api/auth/lupa-password/kirim-otp`: body `{ "no_telp" }` → `200 { "message" }`
- `POST /api/auth/lupa-password/verifikasi-otp`: body `{ "no_telp", "kode" }` → `200 { "message", "valid": true }`, kode salah/kedaluwarsa → `422`
- `POST /api/auth/lupa-password/reset`: body `{ "no_telp", "kode", "password", "password_confirmation" }` → `200 { "message" }`

Alur layar yang direncanakan: Kirim OTP → Verifikasi OTP → Sandi Baru. Layar sandi baru perlu membawa `no_telp` dan `kode` dari layar OTP. Kode OTP berlaku 5 menit sejak dikirim, jeda kirim ulang 60 detik, dan salah 5 kali menghanguskan kode.

## Catatan integrasi

- Registrasi: urutan layar `Register → Lengkapi Profil`, lalu kirim semua data sekali, baru tampilkan layar berhasil.
- Layar Saya mengambil data dari `/me`. Edit profil dan ubah password belum tersedia di backend.
- Simpan token di penyimpanan aman perangkat. Saat menerima 401 di endpoint mana pun, hapus token dan arahkan ke login.
