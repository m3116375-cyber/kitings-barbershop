# Panduan Setup — Kiting's Barbershop (Laravel 12)

> **Status verifikasi:** kode sudah dicek sintaksnya (PHP 8.3) dan dicocokkan antara route, controller, dan view,
> tetapi **belum dijalankan penuh** (di lingkungan pembuatan tidak ada Composer/internet ke Packagist).
> Jadi saat pertama kali dijalankan di komputer Anda mungkin ada error kecil — itu normal, kirim pesan error-nya
> ke saya dan kita perbaiki bersama.

## 1. Fitur

| Peran | Yang bisa dilakukan |
|---|---|
| **Pelanggan** | Registrasi/login · lihat layanan & produk · booking (pilih layanan, **tanggal, slot waktu**, produk tambahan seperti cat rambut/serum) · bayar **online (Midtrans)** atau **COD** · lihat status & riwayat · batalkan pesanan (selama belum dibayar/dilayani) |
| **Admin** | Dashboard · kelola & tangani pesanan masuk (konfirmasi → mulai layani → selesai, jadwal ulang, batalkan, konfirmasi COD lunas) · CRUD layanan · CRUD produk + foto + stok · atur jam operasional, durasi slot, **kapasitas per slot**, tutup slot tertentu |
| **Owner** | Dashboard (total booking, pendapatan, layanan & produk terlaris, grafik pendapatan per minggu, keterisian slot) · laporan dengan filter periode/layanan/status bayar/metode · ekspor Excel (CSV) · cetak/simpan PDF |

**Aturan bisnis penting (sudah diimplementasikan):**
- Slot punya kapasitas. Saat dibooking, baris slot **dikunci** (`lockForUpdate`) sehingga dua pelanggan tidak bisa mengambil kursi terakhir bersamaan → mencegah *double booking*.
- Slot penuh / ditutup / sudah lewat tidak bisa dipilih. Booking hanya sampai `BOOKING_MAX_DAYS_AHEAD` hari ke depan (default 30).
- Stok produk berkurang saat booking dan **dikembalikan** bila pesanan dibatalkan; kapasitas slot juga dikembalikan.
- Harga layanan/produk **disalin** ke pesanan, jadi perubahan harga di kemudian hari tidak merusak laporan lama.
- Status pesanan: `menunggu → dikonfirmasi → sedang_dilayani → selesai` (atau `dibatalkan`).
- Status pembayaran: `belum_dibayar`, `lunas`, `gagal`, `kedaluwarsa`, `dibatalkan`.
- Pendapatan di laporan = pembayaran berstatus **lunas** (online otomatis dari Midtrans, COD setelah admin konfirmasi).

## 2. Kebutuhan

- **PHP 8.2+** (ekstensi: mbstring, openssl, pdo_mysql, fileinfo, curl) — sudah ada di XAMPP/Laragon terbaru
- **Composer** (https://getcomposer.org)
- **MySQL/MariaDB** (XAMPP atau Laragon)
- Koneksi internet (Tailwind CSS, Chart.js, dan Midtrans dimuat dari CDN; **tidak perlu** Node/npm)

## 3. Instalasi

```bash
# 1) Masuk ke folder proyek
cd kitings-barbershop

# 2) Pasang dependensi PHP
composer install

# 3) Salin konfigurasi & buat APP_KEY
copy .env.example .env          # Windows  (Mac/Linux: cp .env.example .env)
php artisan key:generate

# 4) Buat database kosong bernama: kitings_barbershop
#    (lewat phpMyAdmin: New → nama "kitings_barbershop" → collation utf8mb4_unicode_ci)
#    Sesuaikan DB_USERNAME / DB_PASSWORD di file .env bila perlu

# 5) Buat tabel + isi data awal & data contoh
php artisan migrate --seed

# 6) Tautkan folder upload foto produk
php artisan storage:link

# 7) Jalankan
php artisan serve
```

Buka **http://localhost:8000**.

> **Alternatif lewat phpMyAdmin:** impor `database/sql/kitings_barbershop.sql` (skema + data awal) sebagai ganti langkah 4–5.
> Pilih salah satu cara, jangan dicampur. File SQL tidak berisi data contoh riwayat booking.

### Akun contoh (password semuanya: `password`)

| Peran | E-mail |
|---|---|
| Admin | `admin@kitings.test` |
| Owner | `owner@kitings.test` |
| Pelanggan | `pelanggan@kitings.test` |

⚠ **Ganti password ini sebelum dipakai sungguhan.**
Data contoh (riwayat 45 hari + beberapa pesanan aktif) bisa dimatikan: tulis `SEED_DEMO_DATA=false` di `.env`, lalu `php artisan migrate:fresh --seed`.

## 4. Skenario uji cepat

1. Login **pelanggan** → *Booking* → pilih layanan + tanggal + slot + (opsional) produk → *Lanjut ke Pembayaran*.
2. Pilih **COD** → pesanan tampil di *Pesanan Saya* dengan status *Menunggu*.
3. Login **admin** → *Kelola Pesanan* → buka pesanan → *Konfirmasi* → *Mulai Layani* → *Tandai Selesai* → *Konfirmasi COD (Lunas)*.
4. Login **owner** → *Dashboard* / *Laporan* → pendapatan bertambah, coba *Ekspor Excel (CSV)*.
5. Coba booking slot yang sama sampai kapasitas penuh → slot berubah jadi "Penuh" dan tidak bisa dipilih.

## 5. Menghubungkan Midtrans (Payment Gateway) — langkah yang kita kerjakan bersama

Aplikasi sudah memuat seluruh kodenya (membuat Snap token, popup pembayaran, webhook, validasi signature,
sinkron status). Yang perlu **Anda** lakukan hanya mengisi kunci akun Midtrans.

### 5.1 Buat akun Sandbox (gratis, untuk uji coba)
1. Daftar di **https://dashboard.sandbox.midtrans.com/register** dan login.
2. Buka **Settings → Access Keys**. Salin **Client Key** (`SB-Mid-client-...`) dan **Server Key** (`SB-Mid-server-...`).
3. Isi di file `.env`:
   ```env
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
   MIDTRANS_IS_PRODUCTION=false
   ```
4. Jalankan `php artisan config:clear` lalu restart `php artisan serve`.
5. Login pelanggan → booking → pilih **Bayar Online** → popup Midtrans muncul. Gunakan data uji dari
   dokumentasi Midtrans (*Testing Payment / Sandbox Simulator*), misalnya kartu uji `4811 1111 1111 1114`
   (CVV `123`, tanggal kedaluwarsa bebas di masa depan, OTP `112`) — cek dokumentasi bila angkanya berubah.

> **Jangan pernah** membagikan Server Key atau meng-commit file `.env` ke GitHub.

### 5.2 Notifikasi otomatis (webhook)
Setelah pelanggan membayar, Midtrans mengirim notifikasi ke `https://DOMAIN-ANDA/midtrans/notification`
untuk mengubah status pembayaran menjadi **lunas** secara otomatis.

- Atur di dashboard Midtrans: **Settings → Configuration → Payment Notification URL**.
- Di **localhost** Midtrans tidak bisa menjangkau komputer Anda. Dua solusi:
  1. **Tombol "Cek Status Pembayaran"** (sudah ada di halaman detail pesanan) dan pengecekan otomatis setelah popup ditutup —
     aplikasi menanyakan status langsung ke Midtrans. Cukup untuk demo/tugas.
  2. Pakai **ngrok** (`ngrok http 8000`), lalu isi Notification URL dengan
     `https://xxxx.ngrok-free.app/midtrans/notification`.
- Endpoint webhook memverifikasi `signature_key` (SHA-512) dan nominal, jadi notifikasi palsu ditolak.

### 5.3 Pindah ke production (nanti, bila barbershop benar-benar memakai)
1. Lengkapi verifikasi bisnis di dashboard Midtrans (**production**), ambil key production.
2. Di `.env` server: `MIDTRANS_IS_PRODUCTION=true` + key production.
3. Wajib memakai **HTTPS** dan domain sungguhan; isi Notification URL production.
4. Biaya transaksi Midtrans (MDR) ditanggung pemilik usaha — sesuaikan harga bila perlu.

## 6. Struktur penting

```
app/Services/BookingManager.php    ← semua aturan booking (kapasitas, stok, batal, jadwal ulang, status)
app/Services/MidtransService.php   ← Snap token, cek status, verifikasi signature
app/Services/SlotGenerator.php     ← membuat slot dari jam operasional
app/Services/ReportService.php     ← query dashboard & laporan owner
app/Http/Controllers/{Admin,Owner,Customer}/  ← controller per peran
app/Http/Middleware/RoleMiddleware.php        ← pembatas akses per role
database/migrations/               ← 9 tabel (users, services, products, operating_hours, slots, bookings, booking_services, booking_products, payments)
database/seeders/                  ← akun, data master, dan data contoh
database/sql/kitings_barbershop.sql← alternatif impor lewat phpMyAdmin
resources/views/                   ← tampilan Blade (Tailwind via CDN)
routes/web.php                     ← seluruh rute
tests/Feature/                     ← uji otomatis (php artisan test)
```

## 7. Catatan perbedaan dengan dokumen BAB I & BAB II (perlu disamakan)

Proyek ini dibangun dengan **Laravel + MySQL**, sedangkan BAB I/II sebelumnya menulis **React + Supabase (PostgreSQL)**.
Bagian yang perlu diperbarui di dokumen: teknologi pada Project Charter (Technology Used), metode pengembangan
(tahap Mengodekan Sistem), kebutuhan non-fungsional (RLS Supabase → RBAC lewat middleware), dan sequence diagram
(Supabase Auth → Laravel Auth). Kalau mau, kita perbarui dokumennya bersama.

Penyesuaian kecil terhadap ERD BAB II:
- `operating_hours` ditambah `is_open` dan `capacity` (kapasitas default per slot).
- `slots` ditambah `is_closed` (admin dapat menutup slot).
- `payments` ditambah `snap_token`, `snap_created_at`, `payload`; status pembayaran ditambah `dibatalkan`.
- `bookings` ditambah `code` (kode pesanan, mis. BK-00012) dan `notes`.
- Nama kolom memakai bahasa Inggris (sama seperti ERD): `name`, `price`, dst.

## 8. Belum ada (bisa kita kembangkan berikutnya)
- Notifikasi e-mail/WhatsApp (saat ini admin melihat pesanan baru di dashboard).
- Reset password lewat e-mail.
- Refund otomatis (saat ini refund dilakukan manual di dashboard Midtrans; sistem memberi peringatan saat membatalkan pesanan lunas).
- Ekspor PDF native (saat ini memakai *Cetak → Simpan sebagai PDF* dari browser) dan Excel `.xlsx` (saat ini CSV yang dibuka di Excel).
- Tailwind dimuat dari CDN agar tanpa `npm`; untuk production sebaiknya di-build dengan Vite.

## 9. Pemecahan masalah

| Masalah | Solusi |
|---|---|
| `could not find driver` | Aktifkan `extension=pdo_mysql` di `php.ini` (XAMPP: sudah aktif). |
| `Access denied for user 'root'` / `Unknown database` | Cek `DB_*` di `.env` dan pastikan database `kitings_barbershop` sudah dibuat. |
| Halaman error **419 Page Expired** | Muat ulang halaman (token CSRF kedaluwarsa). |
| Foto produk tidak tampil | Jalankan `php artisan storage:link`. |
| Popup Midtrans tidak muncul / "belum dikonfigurasi" | Isi `MIDTRANS_*` di `.env`, lalu `php artisan config:clear`. |
| Error 401 saat membuat transaksi | Server Key salah atau tertukar dengan key production/sandbox. |
| Setelah mengubah `.env` tidak berpengaruh | `php artisan config:clear`. |
| Ingin mengulang database dari awal | `php artisan migrate:fresh --seed` (**menghapus semua data**). |

Uji otomatis: `php artisan test` (memakai SQLite in-memory; pastikan ekstensi `pdo_sqlite` aktif).

## 10. Sebelum dipakai sungguhan (checklist keamanan)
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` diisi domain HTTPS.
- [ ] Ganti semua password akun contoh; hapus akun/data contoh.
- [ ] Jangan commit `.env`; simpan Server Key hanya di server.
- [ ] Jalankan `php artisan config:cache && php artisan route:cache`.
- [ ] Backup database berkala.
