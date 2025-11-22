-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 11:48 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sig_minahasa`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2, 'operator', '$2y$10$S.g/R1W/4.d/5.d/6.d/7.d/8.d/9.d/0.d/1.d/2.d/3.d/4.d');

-- --------------------------------------------------------

--
-- Table structure for table `banjir`
--

CREATE TABLE `banjir` (
  `id` varchar(50) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `level` enum('Rendah','Sedang','Tinggi') DEFAULT 'Sedang',
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banjir`
--

INSERT INTO `banjir` (`id`, `nama`, `lat`, `lng`, `keterangan`, `level`, `tanggal`) VALUES
('banjir_691ff5798fd63', 'David P Kost', 1.26508600, 124.88797500, 'Penyuka Petrik Star', 'Tinggi', '2025-11-21'),
('bj1', 'Banjir Tondano Timur', 1.30500000, 124.92000000, 'Luapan Danau Tondano akibat hujan deras.', 'Tinggi', '2024-12-01'),
('bj2', 'Genangan Wawalintouan', 1.30200000, 124.91500000, 'Drainase tersumbat sampah.', 'Sedang', '2024-12-02'),
('bj3', 'Banjir Kiniar', 1.30800000, 124.91800000, 'Luapan sungai kecil.', 'Rendah', '2024-12-03');

-- --------------------------------------------------------

--
-- Table structure for table `longsor`
--

CREATE TABLE `longsor` (
  `id` varchar(50) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `level` enum('Rendah','Sedang','Tinggi') DEFAULT 'Sedang'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `longsor`
--

INSERT INTO `longsor` (`id`, `nama`, `lat`, `lng`, `keterangan`, `level`) VALUES
('ls1', 'Longsor Jalan Tondano-Tomohon', 1.29000000, 124.89000000, 'Tebing labil setinggi 5 meter.', 'Tinggi'),
('ls2', 'Longsor Bukit Kasih', 1.28500000, 124.89500000, 'Retakan tanah terdeteksi.', 'Sedang');

-- --------------------------------------------------------

--
-- Table structure for table `rs`
--

CREATE TABLE `rs` (
  `id` varchar(50) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rs`
--

INSERT INTO `rs` (`id`, `nama`, `lat`, `lng`, `keterangan`) VALUES
('rs1', 'RSUD Sam Ratulangi', 1.31500000, 124.90500000, 'Rumah Sakit Umum Daerah Tipe C'),
('rs2', 'Puskesmas Tondano', 1.31000000, 124.91000000, 'Puskesmas Kecamatan'),
('rs3', 'Klinik Tataaran', 1.29500000, 124.89800000, 'Klinik Pratama 24 Jam');

-- --------------------------------------------------------

--
-- Table structure for table `sekolah`
--

CREATE TABLE `sekolah` (
  `id` varchar(50) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sekolah`
--

INSERT INTO `sekolah` (`id`, `nama`, `lat`, `lng`, `keterangan`) VALUES
('sch1', 'SMA N 1 Tondano', 1.30950000, 124.91300000, 'Sekolah Menengah Atas (Kapasitas 500)'),
('sch2', 'SMP N 2 Tondano', 1.30400000, 124.91100000, 'Sekolah Menengah Pertama'),
('sch3', 'Universitas Negeri Manado', 1.27730000, 124.88310000, 'Kampus Pusat / Auditorium');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `banjir`
--
ALTER TABLE `banjir`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `longsor`
--
ALTER TABLE `longsor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rs`
--
ALTER TABLE `rs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sekolah`
--
ALTER TABLE `sekolah`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
