-- ============================================================
-- SQL TAMBAHAN - Konservasi Energi Danish
-- Jalankan SETELAH konservasi_energi_ikhwan.sql (phpMyAdmin -> pilih database -> Import / tab SQL)
-- Aman dijalankan ulang: tidak membuat data ganda.
-- ============================================================
USE `konservasi_energi_ikhwan`;

-- ------------------------------------------------------------
-- 1) AKUN PER ROLE: Admin, Guru, Siswa
--    Password (segera ganti lewat menu Pengguna):
--      admin  -> admin123
--      guru1  -> guru123
--      siswa  -> siswa123   (username siswa = NIS)
-- ------------------------------------------------------------

-- Contoh data siswa (ganti dengan data siswa asli). Username akun Siswa harus sama dengan NIS.
INSERT IGNORE INTO `siswa` (`nis`, `nama`, `kelas`) VALUES
(1001, 'Siswa Contoh 1', 'X-1'),
(1002, 'Siswa Contoh 2', 'X-1');

INSERT INTO `users` (`username`, `password`, `nama`, `level`) VALUES
('admin', '$2y$10$x6JfAb7eIT2XSJDrMnpSe.s638UQI6.ELfk.mZKGWt36pA7WsW.Oi', 'Administrator', 'Admin'),
('guru1', '$2y$10$ErpCJpeKIEuG98xFownwJufOsXSJzHB9K2NyU5H8amYoM1Bs43mva', 'Guru Contoh',   'Guru'),
('1001',  '$2y$10$csRfl5DzuWxxpNp/BWlc8.bm6QUUmXqKW2lQVgMBFLTC7bOrUuKG.', 'Siswa Contoh 1', 'Siswa'),
('1002',  '$2y$10$csRfl5DzuWxxpNp/BWlc8.bm6QUUmXqKW2lQVgMBFLTC7bOrUuKG.', 'Siswa Contoh 2', 'Siswa')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`), `level` = VALUES(`level`);

-- Pastikan akun lama 'danish' tidak berlevel kosong
UPDATE `users` SET `level` = 'Admin' WHERE `username` = 'danish' AND `level` = '';

-- ------------------------------------------------------------
-- 2) DETAIL PERANGKAT: tambah kolom lokasi, jumlah, daya (watt), kondisi awal
-- ------------------------------------------------------------
ALTER TABLE `perangkat`
  ADD COLUMN IF NOT EXISTS `lokasi`       varchar(100) DEFAULT NULL AFTER `jenis`,
  ADD COLUMN IF NOT EXISTS `jumlah`       int(11) NOT NULL DEFAULT 1 AFTER `lokasi`,
  ADD COLUMN IF NOT EXISTS `daya_watt`    int(11) DEFAULT NULL AFTER `jumlah`,
  ADD COLUMN IF NOT EXISTS `kondisi_awal` enum('Baik','Rusak Ringan','Rusak Berat') NOT NULL DEFAULT 'Baik' AFTER `daya_watt`;

-- Isi detail untuk 7 perangkat awal (angka daya hanya perkiraan umum, sesuaikan dengan kondisi sekolah)
UPDATE `perangkat` SET `lokasi` = 'Ruang kelas',        `jumlah` = 6, `daya_watt` = 18,   `keterangan` = 'Lampu LED ruangan'                  WHERE `nama_perangkat` = 'Lampu';
UPDATE `perangkat` SET `lokasi` = 'Ruang kelas',        `jumlah` = 2, `daya_watt` = 800,  `keterangan` = 'AC split 1 PK'                   WHERE `nama_perangkat` = 'AC';
UPDATE `perangkat` SET `lokasi` = 'Ruang kelas',        `jumlah` = 2, `daya_watt` = 50,   `keterangan` = 'Kipas angin dinding'             WHERE `nama_perangkat` = 'Kipas Angin';
UPDATE `perangkat` SET `lokasi` = 'Laboratorium',       `jumlah` = 20,`daya_watt` = 150,  `keterangan` = 'Komputer lab / PC'               WHERE `nama_perangkat` = 'Komputer / PC';
UPDATE `perangkat` SET `lokasi` = 'Ruang kelas',        `jumlah` = 1, `daya_watt` = 250,  `keterangan` = 'Proyektor LCD'                   WHERE `nama_perangkat` = 'Proyektor';
UPDATE `perangkat` SET `lokasi` = 'Kantin / Ruang guru',`jumlah` = 1, `daya_watt` = 350,  `keterangan` = 'Dispenser air panas/dingin'      WHERE `nama_perangkat` = 'Dispenser';
UPDATE `perangkat` SET `lokasi` = 'Seluruh ruangan',    `jumlah` = 10,`daya_watt` = NULL, `keterangan` = 'Colokan listrik / terminal'       WHERE `nama_perangkat` = 'Stop Kontak';

-- Perangkat tambahan (dilewati jika namanya sudah ada)
INSERT INTO `perangkat` (`nama_perangkat`, `jenis`, `lokasi`, `jumlah`, `daya_watt`, `kondisi_awal`, `keterangan`)
SELECT t.* FROM (
  SELECT 'Printer'            AS nama_perangkat, 'Elektronik'  AS jenis, 'Ruang guru'  AS lokasi, 2 AS jumlah, 40  AS daya_watt, 'Baik' AS kondisi_awal, 'Printer ruang guru'      AS keterangan
  UNION ALL SELECT 'Sound System',        'Elektronik',  'Aula',         1, 200, 'Baik', 'Pengeras suara aula'
  UNION ALL SELECT 'TV / Monitor',        'Elektronik',  'Ruang guru',   2, 80,  'Baik', 'TV atau monitor informasi'
  UNION ALL SELECT 'Lampu Teras/Taman',   'Penerangan',  'Luar gedung',  4, 20,  'Baik', 'Lampu penerangan luar'
  UNION ALL SELECT 'Pompa Air',           'Kelistrikan', 'Toilet/Taman', 1, 250, 'Baik', 'Pompa air sekolah'
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `perangkat` p WHERE p.`nama_perangkat` = t.`nama_perangkat`);
