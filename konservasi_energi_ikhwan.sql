-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Nov 2026 pada 07.36
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `konservasi_energi_ikhwan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `checklist`
--

CREATE TABLE `checklist` (
  `id_checklist` int(11) NOT NULL,
  `nis` int(10) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_checklist`
--

CREATE TABLE `detail_checklist` (
  `id_detail` int(11) NOT NULL,
  `id_checklist` int(11) NOT NULL,
  `id_perangkat` int(11) NOT NULL,
  `tahapan` enum('SEBELUM','SELAMA','SESUDAH','5R') NOT NULL,
  `kondisi` varchar(100) DEFAULT NULL,
  `status` enum('Ya','Tidak') NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `perangkat`
--

CREATE TABLE `perangkat` (
  `id_perangkat` int(11) NOT NULL,
  `nama_perangkat` varchar(100) NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `perangkat`
--

INSERT INTO `perangkat` (`id_perangkat`, `nama_perangkat`, `jenis`, `keterangan`) VALUES
(1, 'Lampu', 'Penerangan', 'Lampu ruangan'),
(2, 'AC', 'Pendingin', 'Pendingin ruangan'),
(3, 'Kipas Angin', 'Pendingin', NULL),
(4, 'Komputer / PC', 'Elektronik', NULL),
(5, 'Proyektor', 'Elektronik', NULL),
(6, 'Dispenser', 'Elektronik', NULL),
(7, 'Stop Kontak', 'Kelistrikan', 'Colokan listrik');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `nis` int(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`nis`, `nama`, `kelas`) VALUES
(1001, 'Andi Pratama', 'X-1'),
(1002, 'Budi Santoso', 'X-1'),
(1003, 'Citra Lestari', 'X-2'),
(1004, 'Dewi Anggraini', 'X-2'),
(1005, 'Eko Prasetyo', 'XI-1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `level` enum('Admin','Guru','Siswa') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama`, `level`) VALUES
(1, 'ikhwan', '$2y$10$5zu98TLk1yuVBVZKjoRMGuo27aeNiu2PqfH/UJ2TXK.l3qqizNHfe', 'ikhwan', 'Admin'),
(4, 'guru', '$2y$10$h8UTs/PTo8ozfzB7vgKjqOg6HiArFJp.7sZQbPutSY8eitJw98wwO', 'Pak Ahmad', 'Guru'),
(5, 'guru2', '$2y$10$ErpCJpeKIEuG98xFownwJufOsXSJzHB9K2NyU5H8amYoM1Bs43mva', 'Bu Rina', 'Guru'),
(6, 'siswa', '$2y$10$UUDqk5vTQrpmbBun.bp9.O0jqWP50t28hBlpOud55.3M5/KBdCzSq', 'Andi Pratama', 'Siswa');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `checklist`
--
ALTER TABLE `checklist`
  ADD PRIMARY KEY (`id_checklist`),
  ADD KEY `fk_checklist_siswa` (`nis`);

--
-- Indeks untuk tabel `detail_checklist`
--
ALTER TABLE `detail_checklist`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_detail_checklist` (`id_checklist`),
  ADD KEY `fk_detail_perangkat` (`id_perangkat`);

--
-- Indeks untuk tabel `perangkat`
--
ALTER TABLE `perangkat`
  ADD PRIMARY KEY (`id_perangkat`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`nis`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `checklist`
--
ALTER TABLE `checklist`
  MODIFY `id_checklist` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `detail_checklist`
--
ALTER TABLE `detail_checklist`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `perangkat`
--
ALTER TABLE `perangkat`
  MODIFY `id_perangkat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `checklist`
--
ALTER TABLE `checklist`
  ADD CONSTRAINT `fk_checklist_siswa` FOREIGN KEY (`nis`) REFERENCES `siswa` (`nis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_checklist`
--
ALTER TABLE `detail_checklist`
  ADD CONSTRAINT `fk_detail_checklist` FOREIGN KEY (`id_checklist`) REFERENCES `checklist` (`id_checklist`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_perangkat` FOREIGN KEY (`id_perangkat`) REFERENCES `perangkat` (`id_perangkat`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
