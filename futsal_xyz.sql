-- ============================================================
-- Sistem Reservasi Futsal Center XYZ
-- Jalankan file ini untuk membuat database + data dummy
-- ============================================================

DROP DATABASE IF EXISTS futsal_xyz;
CREATE DATABASE futsal_xyz DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;
USE futsal_xyz;

-- ============================================================
-- TABEL USERS
-- ============================================================
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nama          VARCHAR(100)   NOT NULL,
    email         VARCHAR(100)   NOT NULL UNIQUE,
    password      VARCHAR(255)   NOT NULL,
    telepon       VARCHAR(20)    NOT NULL,
    alamat        TEXT           NULL,
    role          ENUM('pelanggan','admin','pemilik') NOT NULL DEFAULT 'pelanggan',
    status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role  (role)
) ENGINE=InnoDB;

-- ============================================================
-- TABEL LAPANGAN
-- ============================================================
CREATE TABLE lapangan (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nama            VARCHAR(50)    NOT NULL,
    deskripsi       VARCHAR(255)   NOT NULL,
    tipe            ENUM('indoor','outdoor') NOT NULL DEFAULT 'indoor',
    tarif_weekday   INT            NOT NULL,
    tarif_weekend   INT            NOT NULL,
    status          ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL RESERVASI
-- ============================================================
CREATE TABLE reservasi (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    kode            VARCHAR(30)    NOT NULL UNIQUE,
    pelanggan_id    INT            NOT NULL,
    lapangan_id     INT            NOT NULL,
    tanggal         DATE           NOT NULL,
    jam_mulai       TIME           NOT NULL,
    jam_selesai     TIME           NOT NULL,
    durasi          INT            NOT NULL DEFAULT 1,
    total_biaya     INT            NOT NULL,
    catatan         TEXT           NULL,
    status          ENUM('pending_payment','waiting_verification','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending_payment',
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (lapangan_id)  REFERENCES lapangan(id) ON DELETE RESTRICT,
    INDEX idx_kode (kode),
    INDEX idx_tanggal (tanggal),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- TABEL PEMBAYARAN
-- ============================================================
CREATE TABLE pembayaran (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    reservasi_id        INT            NOT NULL,
    jumlah              INT            NOT NULL,
    metode              VARCHAR(50)    NOT NULL DEFAULT 'Transfer Bank',
    bukti_pembayaran    VARCHAR(255)   NULL,
    bank_pengirim       VARCHAR(50)    NULL,
    nama_pengirim       VARCHAR(100)   NULL,
    status              ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    tanggal_pembayaran  DATETIME       NULL,
    admin_verifikasi_id INT            NULL,
    waktu_verifikasi    DATETIME       NULL,
    created_at          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reservasi_id)        REFERENCES reservasi(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_verifikasi_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_reservasi (reservasi_id)
) ENGINE=InnoDB;

-- ============================================================
-- TABEL SLOT BLOCKED
-- ============================================================
CREATE TABLE slot_blocked (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    lapangan_id INT            NOT NULL,
    tanggal     DATE           NOT NULL,
    jam_mulai   TIME           NOT NULL,
    jam_selesai TIME           NOT NULL,
    alasan      VARCHAR(255)   NULL DEFAULT 'Maintenance',
    created_by  INT            NULL,
    created_at  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lapangan_id) REFERENCES lapangan(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_lapangan_tanggal (lapangan_id, tanggal)
) ENGINE=InnoDB;

-- ============================================================
-- DUMMY DATA: USERS
-- Password semua: password123 (pelanggan), admin123 (admin), pemilik123 (pemilik)
-- Hash bcrypt yang sama = password "password" (untuk kompatibilitas login demo)
-- ============================================================
INSERT INTO users (id, nama, email, password, telepon, alamat, role, status) VALUES
(1,  'Budi Santoso',      'budi@email.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0812-1111-2222', 'Jl. Merdeka No. 10, Jakarta',      'pelanggan', 'active'),
(2,  'Sari Dewi',         'sari@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0812-3333-4444', 'Jl. Sudirman No. 25, Jakarta',     'pelanggan', 'active'),
(3,  'Andi Pratama',      'andi@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0812-5555-6666', 'Jl. Gatot Subroto No. 8, Jakarta', 'pelanggan', 'active'),
(4,  'Rina Wati',         'rina@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0812-7777-8888', 'Jl. Thamrin No. 15, Jakarta',      'pelanggan', 'active'),
(5,  'Dewa Kusuma',       'dewa@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0813-2222-3333', 'Jl. Kuningan No. 12, Jakarta',     'pelanggan', 'active'),
(6,  'Maya Putri',        'maya@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0813-4444-5555', 'Jl. Rasuna Said No. 7, Jakarta',   'pelanggan', 'active'),
(7,  'Rudi Hermawan',     'rudi@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0815-6666-7777', 'Jl. Kemang No. 30, Jakarta',       'pelanggan', 'active'),
(8,  'Lestari Wulandari', 'lestari@email.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0816-8888-9999', 'Jl. Senopati No. 5, Jakarta',      'pelanggan', 'active'),
(9,  'Hendra Wijaya',     'hendra@email.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0817-1010-2020', 'Jl. Ampera No. 18, Jakarta',       'pelanggan', 'active'),
(10, 'Dian Sastro',       'dian@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0818-3030-4040', 'Jl. Pondok Indah No. 22, Jakarta', 'pelanggan', 'active'),
(11, 'Fajar Nugroho',     'fajar@email.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0819-5050-6060', 'Jl. Blok M No. 9, Jakarta',        'pelanggan', 'active'),
(12, 'Putri Rahayu',      'putri@email.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0821-7070-8080', 'Jl. Fatmawati No. 14, Jakarta',    'pelanggan', 'inactive'),
(13, 'Admin Utama',       'admin@futsal.com',    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', '0812-0000-1111', NULL, 'admin',   'active'),
(14, 'Admin Cadangan',    'admin2@futsal.com',   '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', '0812-0000-2222', NULL, 'admin',   'active'),
(15, 'Pak Andi',          'pemilik@futsal.com',  '$2y$10$HGxBHPEMIAXQF5H5J5M4/.jCfMBjAE6/jIOnJ5ApHIFGGjG5KAXiO', '0812-9999-0000', NULL, 'pemilik', 'active');

-- ============================================================
-- DUMMY DATA: LAPANGAN
-- ============================================================
INSERT INTO lapangan (id, nama, deskripsi, tipe, tarif_weekday, tarif_weekend, status) VALUES
(1, 'Lapangan A', 'Indoor - Rumput sintetis premium, pencahayaan LED, ventilasi baik',  'indoor',  100000, 120000, 'aktif'),
(2, 'Lapangan B', 'Indoor - Rumput sintetis standar, pencahayaan LED, area tribun',     'indoor',  100000, 120000, 'aktif'),
(3, 'Lapangan C', 'Outdoor - Rumput sintetis, area terbuka, cocok untuk sore hari',     'outdoor',  80000, 100000, 'aktif');

-- ============================================================
-- DUMMY DATA: RESERVASI
-- ============================================================
INSERT INTO reservasi (id, kode, pelanggan_id, lapangan_id, tanggal, jam_mulai, jam_selesai, durasi, total_biaya, catatan, status, created_at) VALUES
(1,  'RSV-20260701-001', 1,  1, '2026-07-01', '14:00', '16:00', 2, 200000, '',                         'completed',             '2026-07-01 10:00:00'),
(2,  'RSV-20260702-001', 2,  2, '2026-07-02', '16:00', '18:00', 2, 200000, '',                         'completed',             '2026-07-02 09:30:00'),
(3,  'RSV-20260703-001', 3,  3, '2026-07-03', '08:00', '10:00', 2, 160000, '',                         'completed',             '2026-07-03 07:00:00'),
(4,  'RSV-20260704-001', 4,  1, '2026-07-04', '18:00', '20:00', 2, 240000, 'Weekend rate',             'completed',             '2026-07-04 12:00:00'),
(5,  'RSV-20260705-001', 5,  2, '2026-07-05', '10:00', '12:00', 2, 240000, '',                         'completed',             '2026-07-05 08:00:00'),
(6,  'RSV-20260706-001', 6,  1, '2026-07-06', '14:00', '16:00', 2, 200000, '',                         'completed',             '2026-07-06 11:00:00'),
(7,  'RSV-20260707-001', 7,  3, '2026-07-07', '16:00', '18:00', 2, 160000, '',                         'completed',             '2026-07-07 14:00:00'),
(8,  'RSV-20260708-001', 8,  2, '2026-07-08', '08:00', '10:00', 2, 200000, '',                         'completed',             '2026-07-08 06:30:00'),
(9,  'RSV-20260709-001', 9,  1, '2026-07-09', '20:00', '22:00', 2, 200000, 'Latihan malam',            'completed',             '2026-07-09 15:00:00'),
(10, 'RSV-20260710-001', 10, 3, '2026-07-10', '10:00', '12:00', 2, 160000, '',                         'completed',             '2026-07-10 09:00:00'),
(11, 'RSV-20260714-001', 1,  1, '2026-07-14', '14:00', '16:00', 2, 200000, '',                         'confirmed',             '2026-07-14 10:00:00'),
(12, 'RSV-20260715-001', 2,  2, '2026-07-15', '16:00', '18:00', 2, 200000, '',                         'confirmed',             '2026-07-15 11:30:00'),
(13, 'RSV-20260716-001', 5,  1, '2026-07-16', '10:00', '12:00', 2, 200000, 'Main bareng teman kantor', 'confirmed',             '2026-07-16 08:00:00'),
(14, 'RSV-20260718-001', 1,  1, '2026-07-18', '14:00', '16:00', 2, 240000, '',                         'confirmed',             '2026-07-18 10:00:00'),
(15, 'RSV-20260718-002', 2,  2, '2026-07-18', '16:00', '18:00', 2, 240000, '',                         'waiting_verification',  '2026-07-18 12:00:00'),
(16, 'RSV-20260718-003', 3,  1, '2026-07-18', '18:00', '20:00', 2, 240000, '',                         'pending_payment',       '2026-07-18 13:00:00'),
(17, 'RSV-20260718-004', 4,  3, '2026-07-18', '08:00', '10:00', 2, 200000, '',                         'completed',             '2026-07-18 07:00:00'),
(18, 'RSV-20260719-001', 6,  1, '2026-07-19', '10:00', '12:00', 2, 240000, 'Booking untuk tim futsal',  'confirmed',             '2026-07-18 09:00:00'),
(19, 'RSV-20260719-002', 7,  2, '2026-07-19', '14:00', '16:00', 2, 240000, '',                         'pending_payment',       '2026-07-18 14:00:00'),
(20, 'RSV-20260719-003', 9,  3, '2026-07-19', '08:00', '10:00', 2, 200000, '',                         'waiting_verification',  '2026-07-18 11:00:00'),
(21, 'RSV-20260720-001', 10, 1, '2026-07-20', '18:00', '20:00', 2, 200000, '',                         'pending_payment',       '2026-07-18 15:00:00'),
(22, 'RSV-20260721-001', 11, 2, '2026-07-21', '20:00', '22:00', 2, 200000, 'Futsal malam Selasa',      'confirmed',             '2026-07-18 10:00:00'),
(23, 'RSV-20260712-001', 3,  1, '2026-07-12', '10:00', '12:00', 2, 240000, '',                         'cancelled',             '2026-07-12 08:00:00'),
(24, 'RSV-20260713-001', 8,  3, '2026-07-13', '14:00', '16:00', 2, 200000, 'Batal karena hujan',       'cancelled',             '2026-07-13 12:00:00'),
(25, 'RSV-20260601-001', 1,  1, '2026-06-01', '14:00', '16:00', 2, 200000, '',                         'completed',             '2026-06-01 10:00:00'),
(26, 'RSV-20260603-001', 2,  2, '2026-06-03', '16:00', '18:00', 2, 200000, '',                         'completed',             '2026-06-03 11:00:00'),
(27, 'RSV-20260605-001', 4,  3, '2026-06-05', '08:00', '10:00', 2, 160000, '',                         'completed',             '2026-06-05 07:00:00'),
(28, 'RSV-20260608-001', 5,  1, '2026-06-08', '18:00', '20:00', 2, 200000, '',                         'completed',             '2026-06-08 14:00:00'),
(29, 'RSV-20260610-001', 6,  2, '2026-06-10', '10:00', '12:00', 2, 200000, '',                         'completed',             '2026-06-10 08:00:00'),
(30, 'RSV-20260612-001', 7,  1, '2026-06-12', '14:00', '16:00', 2, 200000, '',                         'completed',             '2026-06-12 10:00:00'),
(31, 'RSV-20260615-001', 9,  3, '2026-06-15', '16:00', '18:00', 2, 160000, '',                         'completed',             '2026-06-15 13:00:00'),
(32, 'RSV-20260618-001', 10, 2, '2026-06-18', '08:00', '10:00', 2, 200000, '',                         'completed',             '2026-06-18 06:00:00'),
(33, 'RSV-20260620-001', 1,  1, '2026-06-20', '20:00', '22:00', 2, 200000, '',                         'completed',             '2026-06-20 15:00:00'),
(34, 'RSV-20260622-001', 3,  2, '2026-06-22', '14:00', '16:00', 2, 200000, '',                         'cancelled',             '2026-06-22 10:00:00'),
(35, 'RSV-20260625-001', 6,  3, '2026-06-25', '10:00', '12:00', 2, 160000, '',                         'completed',             '2026-06-25 08:00:00'),
(36, 'RSV-20260628-001', 8,  1, '2026-06-28', '16:00', '18:00', 2, 200000, '',                         'completed',             '2026-06-28 12:00:00'),
(37, 'RSV-20260502-001', 1,  1, '2026-05-02', '14:00', '16:00', 2, 200000, '',                         'completed',             '2026-05-02 10:00:00'),
(38, 'RSV-20260505-001', 2,  2, '2026-05-05', '16:00', '18:00', 2, 200000, '',                         'completed',             '2026-05-05 11:00:00'),
(39, 'RSV-20260508-001', 4,  3, '2026-05-08', '08:00', '10:00', 2, 160000, '',                         'completed',             '2026-05-08 07:00:00'),
(40, 'RSV-20260510-001', 5,  1, '2026-05-10', '18:00', '20:00', 2, 200000, '',                         'completed',             '2026-05-10 14:00:00'),
(41, 'RSV-20260515-001', 7,  2, '2026-05-15', '10:00', '12:00', 2, 200000, '',                         'completed',             '2026-05-15 08:00:00'),
(42, 'RSV-20260518-001', 9,  1, '2026-05-18', '14:00', '16:00', 2, 200000, '',                         'completed',             '2026-05-18 10:00:00'),
(43, 'RSV-20260522-001', 10, 3, '2026-05-22', '16:00', '18:00', 2, 160000, '',                         'completed',             '2026-05-22 13:00:00'),
(44, 'RSV-20260525-001', 11, 2, '2026-05-25', '08:00', '10:00', 2, 200000, '',                         'completed',             '2026-05-25 06:00:00');

-- ============================================================
-- DUMMY DATA: PEMBAYARAN
-- ============================================================
INSERT INTO pembayaran (id, reservasi_id, jumlah, metode, bukti_pembayaran, bank_pengirim, nama_pengirim, status, tanggal_pembayaran, admin_verifikasi_id, waktu_verifikasi) VALUES
(1,  1,  200000, 'Transfer Bank', 'bukti_budi_01.jpg',     'BCA',     'Budi Santoso',     'verified', '2026-07-01 10:30:00', 13, '2026-07-01 11:00:00'),
(2,  2,  200000, 'Transfer Bank', 'bukti_sari_01.jpg',     'Mandiri', 'Sari Dewi',        'verified', '2026-07-02 10:00:00', 13, '2026-07-02 10:30:00'),
(3,  3,  160000, 'Transfer Bank', 'bukti_andi_01.jpg',     'BRI',     'Andi Pratama',     'verified', '2026-07-03 07:15:00', 13, '2026-07-03 07:30:00'),
(4,  4,  240000, 'Transfer Bank', 'bukti_rina_01.jpg',     'BCA',     'Rina Wati',        'verified', '2026-07-04 12:30:00', 13, '2026-07-04 13:00:00'),
(5,  5,  240000, 'Transfer Bank', 'bukti_dewa_01.jpg',     'Mandiri', 'Dewa Kusuma',      'verified', '2026-07-05 08:30:00', 14, '2026-07-05 09:00:00'),
(6,  6,  200000, 'Transfer Bank', 'bukti_maya_01.jpg',     'BNI',     'Maya Putri',       'verified', '2026-07-06 11:30:00', 13, '2026-07-06 12:00:00'),
(7,  7,  160000, 'Transfer Bank', 'bukti_rudi_01.jpg',     'BCA',     'Rudi Hermawan',    'verified', '2026-07-07 14:30:00', 14, '2026-07-07 15:00:00'),
(8,  8,  200000, 'Transfer Bank', 'bukti_lestari_01.jpg',  'BRI',     'Lestari Wulandari','verified', '2026-07-08 07:00:00', 13, '2026-07-08 07:30:00'),
(9,  9,  200000, 'Transfer Bank', 'bukti_hendra_01.jpg',   'BCA',     'Hendra Wijaya',    'verified', '2026-07-09 15:30:00', 13, '2026-07-09 16:00:00'),
(10, 10, 160000, 'Transfer Bank', 'bukti_dian_01.jpg',     'Mandiri', 'Dian Sastro',      'verified', '2026-07-10 09:30:00', 14, '2026-07-10 10:00:00'),
(11, 11, 200000, 'Transfer Bank', 'bukti_budi_02.jpg',     'BCA',     'Budi Santoso',     'verified', '2026-07-14 10:30:00', 13, '2026-07-14 11:00:00'),
(12, 12, 200000, 'Transfer Bank', 'bukti_sari_02.jpg',     'Mandiri', 'Sari Dewi',        'verified', '2026-07-15 12:00:00', 13, '2026-07-15 12:30:00'),
(13, 13, 200000, 'Transfer Bank', 'bukti_dewa_02.jpg',     'BCA',     'Dewa Kusuma',      'verified', '2026-07-16 08:30:00', 13, '2026-07-16 09:00:00'),
(14, 14, 240000, 'Transfer Bank', 'bukti_budi_03.jpg',     'BCA',     'Budi Santoso',     'verified', '2026-07-18 10:30:00', 13, '2026-07-18 11:00:00'),
(15, 15, 240000, 'Transfer Bank', 'bukti_sari_03.jpg',     'Mandiri', 'Sari Dewi',        'pending',  '2026-07-18 12:30:00', NULL, NULL),
(16, 17, 200000, 'Transfer Bank', 'bukti_rina_03.jpg',     'BRI',     'Rina Wati',        'verified', '2026-07-18 07:15:00', 13, '2026-07-18 07:30:00'),
(17, 18, 240000, 'Transfer Bank', 'bukti_maya_02.jpg',     'BCA',     'Maya Putri',       'verified', '2026-07-18 09:30:00', 13, '2026-07-18 10:00:00'),
(18, 20, 200000, 'Transfer Bank', 'bukti_hendra_02.jpg',   'BNI',     'Hendra Wijaya',    'pending',  '2026-07-18 11:30:00', NULL, NULL),
(19, 22, 200000, 'Transfer Bank', 'bukti_fajar_01.jpg',    'BCA',     'Fajar Nugroho',    'verified', '2026-07-18 10:30:00', 13, '2026-07-18 11:00:00'),
(20, 25, 200000, 'Transfer Bank', 'bukti_budi_jun01.jpg',  'BCA',     'Budi Santoso',     'verified', '2026-06-01 10:30:00', 13, '2026-06-01 11:00:00'),
(21, 26, 200000, 'Transfer Bank', 'bukti_sari_jun01.jpg',  'Mandiri', 'Sari Dewi',        'verified', '2026-06-03 11:30:00', 13, '2026-06-03 12:00:00'),
(22, 27, 160000, 'Transfer Bank', 'bukti_rina_jun01.jpg',  'BRI',     'Rina Wati',        'verified', '2026-06-05 07:30:00', 13, '2026-06-05 08:00:00'),
(23, 28, 200000, 'Transfer Bank', 'bukti_dewa_jun01.jpg',  'BCA',     'Dewa Kusuma',      'verified', '2026-06-08 14:30:00', 14, '2026-06-08 15:00:00'),
(24, 29, 200000, 'Transfer Bank', 'bukti_maya_jun01.jpg',  'Mandiri', 'Maya Putri',       'verified', '2026-06-10 08:30:00', 13, '2026-06-10 09:00:00'),
(25, 30, 200000, 'Transfer Bank', 'bukti_rudi_jun01.jpg',  'BCA',     'Rudi Hermawan',    'verified', '2026-06-12 10:30:00', 13, '2026-06-12 11:00:00'),
(26, 31, 160000, 'Transfer Bank', 'bukti_hendra_jun01.jpg','BRI',      'Hendra Wijaya',    'verified', '2026-06-15 13:30:00', 13, '2026-06-15 14:00:00'),
(27, 32, 200000, 'Transfer Bank', 'bukti_dian_jun01.jpg',  'BCA',     'Dian Sastro',      'verified', '2026-06-18 06:30:00', 13, '2026-06-18 07:00:00'),
(28, 33, 200000, 'Transfer Bank', 'bukti_budi_jun02.jpg',  'Mandiri', 'Budi Santoso',     'verified', '2026-06-20 15:30:00', 13, '2026-06-20 16:00:00'),
(29, 35, 160000, 'Transfer Bank', 'bukti_maya_jun02.jpg',  'BCA',     'Maya Putri',       'verified', '2026-06-25 08:30:00', 14, '2026-06-25 09:00:00'),
(30, 36, 200000, 'Transfer Bank', 'bukti_lestari_jun01.jpg','BRI',     'Lestari Wulandari','verified', '2026-06-28 12:30:00', 13, '2026-06-28 13:00:00'),
(31, 37, 200000, 'Transfer Bank', 'bukti_budi_mei01.jpg',  'BCA',     'Budi Santoso',     'verified', '2026-05-02 10:30:00', 13, '2026-05-02 11:00:00'),
(32, 38, 200000, 'Transfer Bank', 'bukti_sari_mei01.jpg',  'Mandiri', 'Sari Dewi',        'verified', '2026-05-05 11:30:00', 13, '2026-05-05 12:00:00'),
(33, 39, 160000, 'Transfer Bank', 'bukti_rina_mei01.jpg',  'BRI',     'Rina Wati',        'verified', '2026-05-08 07:30:00', 13, '2026-05-08 08:00:00'),
(34, 40, 200000, 'Transfer Bank', 'bukti_dewa_mei01.jpg',  'BCA',     'Dewa Kusuma',      'verified', '2026-05-10 14:30:00', 13, '2026-05-10 15:00:00'),
(35, 41, 200000, 'Transfer Bank', 'bukti_rudi_mei01.jpg',  'Mandiri', 'Rudi Hermawan',    'verified', '2026-05-15 08:30:00', 14, '2026-05-15 09:00:00'),
(36, 42, 200000, 'Transfer Bank', 'bukti_hendra_mei01.jpg','BCA',      'Hendra Wijaya',    'verified', '2026-05-18 10:30:00', 13, '2026-05-18 11:00:00'),
(37, 43, 160000, 'Transfer Bank', 'bukti_dian_mei01.jpg',  'BRI',     'Dian Sastro',      'verified', '2026-05-22 13:30:00', 13, '2026-05-22 14:00:00'),
(38, 44, 200000, 'Transfer Bank', 'bukti_fajar_mei01.jpg', 'BCA',     'Fajar Nugroho',    'verified', '2026-05-25 06:30:00', 13, '2026-05-25 07:00:00');

-- ============================================================
-- DUMMY DATA: SLOT BLOCKED
-- ============================================================
INSERT INTO slot_blocked (id, lapangan_id, tanggal, jam_mulai, jam_selesai, alasan, created_by, created_at) VALUES
(1, 1, '2026-07-18', '08:00', '10:00', 'Maintenance rutin lapangan A', 13, '2026-07-17 16:00:00'),
(2, 1, '2026-07-19', '08:00', '09:00', 'Pembersihan lapangan',         13, '2026-07-18 10:00:00'),
(3, 2, '2026-07-20', '08:00', '10:00', 'Perbaikan pencahayaan LED',    13, '2026-07-18 09:00:00'),
(4, 3, '2026-07-21', '08:00', '10:00', 'Servis rumput sintetis',       14, '2026-07-18 11:00:00');

-- Reset AUTO_INCREMENT
ALTER TABLE users      AUTO_INCREMENT = 16;
ALTER TABLE lapangan   AUTO_INCREMENT = 4;
ALTER TABLE reservasi  AUTO_INCREMENT = 45;
ALTER TABLE pembayaran AUTO_INCREMENT = 39;
ALTER TABLE slot_blocked AUTO_INCREMENT = 5;
