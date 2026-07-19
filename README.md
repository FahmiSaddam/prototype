# Sistem Reservasi Futsal Center XYZ

Prototipe aplikasi web untuk reservasi lapangan futsal secara online. Dibangun dengan PHP native (tanpa framework) dan MySQL, menggunakan pola PDO untuk akses database.

## Fitur

### Pengunjung (Belum Login)
- Melihat landing page dengan info lapangan dan tarif
- Melihat jadwal ketersediaan lapangan hari ini
- Registrasi akun baru
- Login

### Pelanggan
- Dashboard ringkasan aktivitas reservasi
- Melihat jadwal ketersediaan lapangan per tanggal
- Membuat reservasi lapangan (pilih lapangan, tanggal, jam)
- Upload bukti pembayaran (transfer bank)
- Membatalkan reservasi yang belum dibayar
- Melihat riwayat reservasi
- Mengubah profil (nama, telepon, alamat)

### Admin
- Dashboard ringkasan sistem
- Verifikasi/tolak bukti pembayaran pelanggan
- Kelola data reservasi (termasuk menyelesaikan reservasi)
- Kelola jadwal & pemblokiran slot (misal untuk maintenance)
- Kelola data lapangan (aktif/nonaktif)
- Kelola data pelanggan (aktif/nonaktif)
- Laporan reservasi
- Laporan pendapatan

### Pemilik
- Dashboard bisnis
- Statistik penggunaan lapangan
- Laporan pendapatan

## Teknologi

- **Backend**: PHP native (PDO untuk akses MySQL)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML, CSS (`styles.css`), tanpa JavaScript framework
- **Autentikasi**: Session PHP (`$_SESSION`), password di-hash dengan `password_hash`

## Struktur Folder

```
prototype/
├── admin/                  # Halaman khusus role admin
│   ├── dashboard.php
│   ├── jadwal.php
│   ├── lapangan.php
│   ├── laporan_pendapatan.php
│   ├── laporan_reservasi.php
│   ├── pelanggan.php
│   ├── reservasi.php
│   └── verifikasi.php
├── pelanggan/               # Halaman khusus role pelanggan
│   ├── dashboard.php
│   ├── jadwal.php
│   ├── profil.php
│   └── reservasi.php
├── pemilik/                 # Halaman khusus role pemilik
│   ├── dashboard.php
│   ├── laporan.php
│   └── statistik.php
├── includes/
│   └── layout.php           # Layout/komponen bersama (header, navbar, dsb.)
├── config.php                # Koneksi DB, helper function, autentikasi, session
├── proses.php                 # Handler semua aksi POST (login, register, reservasi, dll.)
├── index.php                  # Landing page
├── login.php                  # Halaman login
├── register.php               # Halaman registrasi
├── styles.css                 # Stylesheet utama
└── futsal_xyz.sql             # Skema database + data dummy
```

## Peran Pengguna (Role)

| Role      | Deskripsi                                      |
|-----------|-------------------------------------------------|
| pelanggan | Pengguna umum yang melakukan reservasi lapangan |
| admin     | Mengelola operasional sehari-hari (verifikasi pembayaran, jadwal, dsb.) |
| pemilik   | Melihat laporan dan statistik bisnis            |

## Instalasi & Menjalankan

### Prasyarat
- PHP 7.4+ (disarankan PHP 8.x) dengan ekstensi `pdo_mysql`
- MySQL / MariaDB
- Web server (Apache/Nginx) atau bisa menggunakan built-in server PHP

### Langkah-langkah

1. **Clone / salin proyek** ke direktori web server, misalnya `htdocs` (XAMPP) atau `www` (Laragon).

2. **Buat database** dengan mengimpor file `futsal_xyz.sql`:
   ```bash
   mysql -u root -p < futsal_xyz.sql
   ```
   File ini akan otomatis membuat database `futsal_xyz`, seluruh tabel, dan data dummy.

3. **Sesuaikan konfigurasi database** jika diperlukan pada `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'futsal_xyz');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Jalankan aplikasi**:
   - Dengan XAMPP/Laragon: akses `http://localhost/<folder-proyek>/prototype/`
   - Atau menggunakan built-in server PHP:
     ```bash
     php -S localhost:8000
     ```
     lalu buka `http://localhost:8000/`

## Akun Demo

Data dummy berikut tersedia setelah import `futsal_xyz.sql`:

| Role      | Email                 | Password    |
|-----------|------------------------|-------------|
| Pelanggan | budi@email.com          | password    |
| Admin     | admin@futsal.com        | admin123    |
| Pemilik   | pemilik@futsal.com      | pemilik123  |

> Terdapat 12 akun pelanggan dummy lainnya (misal `sari@email.com`, `andi@email.com`, dst.) dengan password yang sama: `password`.

## Struktur Database

| Tabel          | Deskripsi                                              |
|----------------|----------------------------------------------------------|
| `users`        | Data pengguna (pelanggan, admin, pemilik)                |
| `lapangan`     | Data lapangan futsal (nama, tipe, tarif weekday/weekend)  |
| `reservasi`    | Data transaksi reservasi lapangan                         |
| `pembayaran`   | Data pembayaran & bukti transfer per reservasi            |
| `slot_blocked` | Slot waktu yang diblokir admin (misal untuk maintenance)  |

### Status Reservasi
- `pending_payment` — Reservasi dibuat, menunggu pembayaran
- `waiting_verification` — Bukti pembayaran diunggah, menunggu verifikasi admin
- `confirmed` — Pembayaran diverifikasi, reservasi dikonfirmasi
- `completed` — Reservasi selesai (sudah bermain)
- `cancelled` — Reservasi dibatalkan

## Alur Reservasi

1. Pelanggan login dan memilih jadwal (tanggal, lapangan, jam) yang tersedia.
2. Sistem membuat reservasi dengan status `pending_payment` dan menghitung total biaya berdasarkan tarif weekday/weekend.
3. Pelanggan mengunggah bukti transfer pembayaran → status berubah menjadi `waiting_verification`.
4. Admin memverifikasi pembayaran:
   - Jika **diterima** → status menjadi `confirmed`.
   - Jika **ditolak** → status kembali ke `pending_payment`.
5. Setelah jadwal bermain selesai, admin menandai reservasi sebagai `completed`.

## Catatan

Proyek ini merupakan **prototipe** untuk keperluan tugas mata kuliah Analisis dan Desain Sistem Informasi, sehingga beberapa aspek (seperti upload file bukti pembayaran yang sesungguhnya, validasi keamanan lanjutan, dan payment gateway) belum diimplementasikan secara penuh dan disederhanakan untuk kebutuhan demo.
