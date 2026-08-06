-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Agu 2026 pada 02.17
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_bookstore`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `books`
--

INSERT INTO `books` (`id`, `title`, `category`, `price`, `description`, `image`) VALUES
(1, 'Sherlock Holmes', 'mystery', 75000, 'Kumpulan kisah petualangan detektif terkenal Sherlock Holmes dalam memecahkan misteri-misteri tersulit di Inggris.', 'SHERLOCK HOLMES.jpg'),
(2, 'Arsene Lupin', 'mystery', 68000, 'Kisah pencuri ulung nan dermawan Arsene Lupin yang selalu berhasil mengelabui kepolisian.', 'lupin.jpg'),
(3, 'Keigo Higashino', 'mystery', 85000, 'Novel fiksi kriminal misteri penuh plot twist mendalam karya novelis ternama Jepang.', 'Detektif.jpg'),
(4, 'Madilog', 'history', 95000, 'Karya legendaris Tan Malaka yang membahas Materialisme, Dialektika, dan Logika sebagai dasar pemikiran modern.', 'madilog.jpg'),
(5, '30 Bohong Besar Soeharto', 'history', 65000, 'Buku kritis yang mengulas seputar fakta dan investigasi sejarah politik Orde Baru.', '30 Bohong besar.jpg'),
(6, 'Sejarah Singkat Dunia', 'history', 88000, 'Rangkuman perjalanan peradaban manusia dari zaman prasejarah hingga era modern.', 'Sejarah dunia singkat.jpg'),
(7, 'Pulang - Tere Liye', 'fiction', 79000, 'Novel aksi petualangan emosional karya Tere Liye tentang arti pulang dan perjalanan hidup.', 'pulang.jpg'),
(8, 'Moby Dick', 'fiction', 70000, 'Kisah klasik petualangan Kapten Ahab berburu paus putih raksasa Moby Dick.', 'Moby dick.jpg'),
(9, 'Megalodon', 'fiction', 72000, 'Fiksi ilmiah seru tentang ancaman predator hiu raksasa purba Megalodon di laut dalam.', 'megalodon.jpg'),
(10, 'The Lover Next Door', 'fiction', 64000, 'Novel romantis hangat tentang kisah dua tetangga yang saling menyimpan perasaan.', 'The love next door.jpg'),
(11, 'Traditional Wing Chun', 'comic', 80000, 'Panduan seni bela diri ilmu Wing Chun tradisional lengkap dengan teknik dasar hingga mahir.', 'Wingchun.jpg'),
(12, 'Gamera', 'comic', 50000, 'Komik monster raksasa Gamera pelindung bumi melawan kaiju jahat.', 'gamera.jpg'),
(13, 'Jack the ripper', 'mystery', 99000, 'Buku ini membahas salah satu kasus kejahatan nyata paling terkenal dan misterius dalam sejarah: pembunuhan berantai oleh Jack the Ripper di distrik Whitechapel, London, pada musim gugur tahun 1888.', 'Jack.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `total_price` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `book_title`, `total_price`, `status`, `order_date`) VALUES
(1, 'Athallah', 'Sherlock Holmes', 75000, 'Approved', '2026-07-29 04:47:51'),
(2, 'Budi', 'Madilog', 95000, 'Approved', '2026-07-29 04:47:51'),
(3, 'Rani', 'Madilog', 95000, 'Approved', '2026-07-29 06:29:23'),
(4, 'Anto', 'Madilog', 95000, 'Approved', '2026-07-29 11:11:03'),
(5, 'urip', '30 Bohong Besar Soeharto', 65000, 'Approved', '2026-07-29 12:53:23'),
(6, 'Bagas Homis', 'Jack the ripper', 99000, 'Approved', '2026-07-29 13:00:43'),
(7, 'malik', 'Megalodon', 72000, 'Approved', '2026-07-30 00:56:35'),
(8, 'Anto', 'Pulang - Tere Liye', 79000, 'Approved', '2026-07-30 01:16:42'),
(9, ' Pak agus', 'Gamera', 50000, 'Approved', '2026-07-30 01:50:46'),
(10, 'yuli', 'Gamera', 50000, 'Approved', '2026-07-30 02:00:14'),
(11, 'yuli', 'Gamera', 50000, 'Approved', '2026-07-30 02:00:27'),
(12, 'yuli', 'Gamera', 50000, 'Approved', '2026-07-30 02:00:39'),
(13, 'yuli', 'Gamera', 50000, 'Approved', '2026-07-30 02:03:02'),
(14, 'yuli', 'Megalodon', 72000, 'Approved', '2026-07-30 02:03:14'),
(15, 'Anto', 'Traditional Wing Chun', 80000, 'Approved', '2026-07-30 02:05:42'),
(16, 'malik', 'Jack the ripper', 99000, 'Approved', '2026-07-30 02:09:11'),
(17, 'urip', 'Madilog', 95000, 'Approved', '2026-07-30 02:10:08'),
(18, 'yuli', 'Madilog', 95000, 'Approved', '2026-07-30 02:16:00'),
(19, 'yuli', 'Madilog', 95000, 'Approved', '2026-07-30 03:14:03'),
(20, 'yuli', 'Moby Dick', 70000, 'Approved', '2026-07-30 03:14:49'),
(21, 'yuli', 'Moby Dick', 70000, 'Approved', '2026-07-30 03:14:56'),
(22, 'Bagas Homis', 'Traditional Wing Chun', 80000, 'Approved', '2026-07-30 03:15:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `nama_pembeli` varchar(255) NOT NULL,
  `judul_buku` varchar(255) NOT NULL,
  `total` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `nama_pembeli`, `judul_buku`, `total`, `status`) VALUES
(1, 'urip', 'Megalodon', 72000, 'Approved'),
(2, 'aurel', 'Jack the ripper', 99000, 'Approved'),
(3, 'malik', 'Traditional Wing Chun', 80000, 'Approved'),
(4, 'pran', 'Traditional Wing Chun', 80000, 'Approved'),
(5, 'Shalma', 'The Lover Next Door', 64000, 'Approved'),
(6, 'malik', 'The Lover Next Door', 64000, 'Approved'),
(7, 'malik', 'Gamera', 50000, 'Approved'),
(8, 'malik', 'Jack the ripper', 99000, 'Pending');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`) VALUES
(1, 'admin Lix', 'admin@gmail.com', 'Malik_2009', 'admin'),
(2, 'User', 'user@gmail.com', 'Malik_2009', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
