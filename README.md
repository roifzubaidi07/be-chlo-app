# Internal Procurement API (Laravel 12)

API-only backend untuk **Sistem Procurement Internal**: pengajuan permintaan barang/jasa, approval, pengadaan ke vendor, dan pelacakan status. Autentikasi memakai **Laravel Sanctum** (Bearer token).

## Stack

- PHP 8.2+, Laravel 12
- Database: **PostgreSQL 14+** (default pengembangan lokal: **localhost**)
- Token: Sanctum (`personal_access_tokens`)

## Persiapan cepat

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### PostgreSQL di localhost (default)

1. Pastikan PostgreSQL berjalan di mesin Anda (biasanya `127.0.0.1:5432`).
2. Buat database kosong (nama mengikuti `.env`):

   ```sql
   CREATE DATABASE be_chlo_app;
   ```

3. Isi `.env` — nilai default dari `.env.example`:

   | Variabel | Default lokal |
   |----------|----------------|
   | `DB_CONNECTION` | `pgsql` |
   | `DB_HOST` | `127.0.0.1` |
   | `DB_PORT` | `5432` |
   | `DB_DATABASE` | `be_chlo_app` |
   | `DB_USERNAME` | `postgres` |
   | `DB_PASSWORD` | `postgres` |

   Sesuaikan `DB_USERNAME` / `DB_PASSWORD` dengan instalasi PostgreSQL Anda.

4. Migrasi + seed:

   ```bash
   php artisan migrate:fresh --seed
   ```

**Catatan:** Tes otomatis PHPUnit tetap memakai SQLite in-memory (`phpunit.xml`), tidak bergantung pada PostgreSQL.

### Alternatif: SQLite

Jika ingin memakai file SQLite, ubah `.env` menjadi `DB_CONNECTION=sqlite` dan set `DB_DATABASE` ke path file (mis. `database/database.sqlite`), lalu buat file database tersebut dan jalankan migrasi.

## Menjalankan server

```bash
php artisan serve
```

Basis URL API: **`http://localhost:8000/api`** (host/port mengikuti perintah di atas).

## Akun uji (setelah seed)

| Email | Peran | Password |
|-------|--------|----------|
| `requester@test.local` | requester | `password` |
| `approver@test.local` | approver | `password` |
| `purchasing@test.local` | purchasing | `password` |
| `warehouse@test.local` | warehouse | `password` |

## Endpoint ringkas

Semua path di bawah mengasumsikan prefix **`/api`**. Header untuk route terproteksi:

```http
Authorization: Bearer <token_sanctum>
Accept: application/json
```

| Method | Path | Auth | Keterangan |
|--------|------|------|------------|
| POST | `/login` | Tidak | Email + password → token |
| POST | `/logout` | Ya | Menghapus token saat ini |
| GET | `/user` | Ya | User yang sedang login |
| GET | `/requests` | Ya | Daftar permintaan; query opsional `?status=SUBMITTED` (dll.) |
| POST | `/requests` | Ya | Buat permintaan + baris item |
| GET | `/requests/{id}` | Ya | Detail permintaan |
| POST | `/requests/{id}/approve` | Ya | Peran **approver**; status harus `SUBMITTED` |
| POST | `/requests/{id}/reject` | Ya | Peran **approver** |
| POST | `/requests/{id}/procure` | Ya | Peran **purchasing**; body `vendor_id` |

### Contoh body

**Login**

```json
{
  "email": "requester@test.local",
  "password": "password"
}
```

**Buat permintaan (`POST /requests`)**

```json
{
  "submit": true,
  "items": [
    {
      "item_id": 1,
      "qty": 2,
      "code": "L1",
      "discount": 0,
      "tax": 0
    }
  ]
}
```

- `submit: true` → status awal **SUBMITTED**; tanpa / `false` → **DRAFT**.

**Procure (`POST /requests/{id}/procure`)**

```json
{
  "vendor_id": 1,
  "po_number": null
}
```

`po_number` opsional; jika kosong akan digenerate.

### Status permintaan

Alur umum: `DRAFT` → `SUBMITTED` → `APPROVED` / `REJECTED` → `IN_PROCUREMENT` → `COMPLETED` (endpoint selesai dapat ditambahkan terpisah).

### HTTP status yang umum

- `200` — sukses
- `201` — permintaan dibuat
- `401` — tidak terautentikasi
- `403` — tidak punya peran / tidak diizinkan
- `404` — tidak ditemukan
- `409` — konflik (mis. approval ganda oleh user yang sama)
- `422` — validasi gagal atau transisi status tidak valid

## Postman (file terpisah)

Koleksi Postman ada di **akar repositori** (bukan di dalam README):

- [`Internal-Procurement-API.postman_collection.json`](Internal-Procurement-API.postman_collection.json)

Di Postman: **Import** → pilih file tersebut.

Variabel koleksi:

- `base_url` — default `http://localhost:8000/api` (sesuaikan jika server tidak di port 8000)
- `token` — diisi otomatis setelah request **Login** berhasil (tab Tests)
- `request_id` — bisa terisi otomatis setelah **Create request**

Urutan uji alur tipikal:

1. **Login** (requester / approver / purchasing sesuai kebutuhan)
2. **Create request** (requester)
3. **Approve** atau **Reject** (approver)
4. **Procure** (purchasing, setelah `APPROVED`)

## Perintah Artisan berguna

```bash
php artisan route:list --path=api
php artisan migrate
php artisan db:seed
php artisan optimize:clear
```

## Struktur domain (ringkas)

Tabel utama: `departments`, `users`, `items`, `vendors`, `requests`, `request_items`, `approvals`, `stocks`, `status_histories`, `procurement_orders`.

Model `ProcurementRequest` memetakan ke tabel **`requests`** (nama `Request` bentrok dengan HTTP Request).

## Lisensi

Proyek Laravel dasar berlisensi MIT; konten aplikasi procurement mengikuti kebijakan repositori Anda.
