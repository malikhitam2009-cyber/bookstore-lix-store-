-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 08:03 AM
-- Server version: 10.4.32-MariaDB-log
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lix_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `stock` int(11) DEFAULT 60
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `genre`, `price`, `description`, `image`, `stock`) VALUES
(1, 'Sherlock Holmes', 'mystery', 75000, 'Kumpulan kisah petualangan detektif terkenal Sherlock Holmes dalam memecahkan misteri-misteri tersulit di Inggris.', 'SHERLOCK HOLMES.jpg', 60),
(2, 'Arsene Lupin', 'mystery', 68000, 'Kisah pencuri ulung nan dermawan Arsene Lupin yang selalu berhasil mengelabui kepolisian.', 'Lupin.jpg', 60),
(3, 'Keigo Higashino', 'mystery', 85000, 'Novel fiksi kriminal misteri penuh plot twist mendalam karya novelis ternama Jepang.', 'Detektif.jpg', 60),
(4, 'Madilog', 'history', 95000, 'Karya legendaris Tan Malaka yang membahas Materialisme, Dialektika, dan Logika sebagai dasar pemikiran modern.', 'Madilog.jpg', 59),
(5, '30 Bohong Besar Soeharto', 'history', 65000, 'Buku kritis yang mengulas seputar fakta dan investigasi sejarah politik Orde Baru.', '30 Bohong besar.jpg', 59),
(6, 'Sejarah Singkat Dunia', 'history', 88000, 'Rangkuman perjalanan peradaban manusia dari zaman prasejarah hingga era modern.', 'Sejarah dunia singkat.jpg', 60),
(7, 'Pulang - Tere Liye', 'fiction', 79000, 'Novel aksi petualangan emosional karya Tere Liye tentang arti pulang dan perjalanan hidup.', 'Pulang.jpg', 60),
(8, 'Moby Dick', 'fiction', 70000, 'Kisah klasik petualangan Kapten Ahab berburu paus putih raksasa Moby Dick.', 'Moby Dick.jpg', 60),
(9, 'Megalodon', 'fiction', 72000, 'Fiksi ilmiah seru tentang ancaman predator hiu raksasa purba Megalodon di laut dalam.', 'Megalodon.jpg', 59),
(10, 'The Lover Next Door', 'fiction', 64000, 'Novel romantis hangat tentang kisah dua tetangga yang saling menyimpan perasaan.', 'The love next door.jpg', 60),
(11, 'Traditional Wing Chun', 'comic', 80000, 'Panduan seni bela diri ilmu Wing Chun tradisional lengkap dengan teknik dasar hingga mahir.', 'Wingchun.jpg', 55),
(12, 'Gamera', 'comic', 50000, 'Komik monster raksasa Gamera pelindung bumi melawan kaiju jahat.', 'Gamera.jpg', 55),
(13, 'Jack the ripper', 'mystery', 99000, 'Buku ini membahas salah satu kasus kejahatan nyata paling terkenal dan misterius dalam sejarah: pembunuhan berantai oleh Jack the Ripper di distrik Whitechapel, London, pada musim gugur tahun 1888.', 'Jack.jpg', 60);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `book_id`, `quantity`) VALUES
(1, 3, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Novel'),
(2, 'Manga'),
(3, 'Teknologi'),
(4, 'Pendidikan'),
(5, 'Resep masakan'),
(6, 'komik');

-- --------------------------------------------------------

--
-- Table structure for table `community_posts`
--

CREATE TABLE `community_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_log`
--

CREATE TABLE `email_log` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) DEFAULT NULL,
  `tujuan` varchar(150) NOT NULL,
  `subjek` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_log`
--

INSERT INTO `email_log` (`id`, `pesanan_id`, `tujuan`, `subjek`, `status`, `keterangan`, `created_at`) VALUES
(1, 21, 'alvianbdwj@gmail.com', 'Pesanan TL101126-001 sedang dikemas', 'gagal', 'Login SMTP gagal: 535-5.7.8 Username and Password not accepted. For more information, go to\r\n535 5.7.8  https://support.google.com/mail/?p=BadCredentials 98e67ed59e1d1-3b167928684sm2050914a91.10 - gsmtp', '2026-11-10 10:58:32'),
(2, 21, 'alvianbdwj@gmail.com', 'Pesanan TL101126-001 sedang dalam perjalanan', 'gagal', 'Login SMTP gagal: 535-5.7.8 Username and Password not accepted. For more information, go to\r\n535 5.7.8  https://support.google.com/mail/?p=BadCredentials 98e67ed59e1d1-3b1678d1f42sm2096924a91.7 - gsmtp', '2026-11-10 10:59:46'),
(3, 22, 'alvianbdwj@gmail.com', 'Pesanan TL101126-002 sedang dikemas', 'gagal', 'Login SMTP gagal: 535-5.7.8 Username and Password not accepted. For more information, go to\r\n535 5.7.8  https://support.google.com/mail/?p=BadCredentials d2e1a72fcca58-8baaac46b20sm365850b3a.30 - gsmtp', '2026-11-10 11:10:00'),
(4, 22, 'alvianbdwj@gmail.com', 'Pesanan TL101126-002 sedang dalam perjalanan', 'gagal', 'Login SMTP gagal: 535-5.7.8 Username and Password not accepted. For more information, go to\r\n535 5.7.8  https://support.google.com/mail/?p=BadCredentials d2e1a72fcca58-8baa7de3eecsm369091b3a.1 - gsmtp', '2026-11-10 11:10:48'),
(5, 23, 'alvianbdwj@gmail.com', 'Pesanan TL101126-003 sedang dikemas', 'gagal', 'Login SMTP gagal: 535-5.7.8 Username and Password not accepted. For more information, go to\r\n535 5.7.8  https://support.google.com/mail/?p=BadCredentials 98e67ed59e1d1-3b16792aabfsm2135560a91.12 - gsmtp', '2026-11-10 11:24:37'),
(6, 23, 'alvianbdwj@gmail.com', 'Pesanan TL101126-003 sedang dalam perjalanan', 'gagal', 'Login SMTP gagal: 535-5.7.8 Username and Password not accepted. For more information, go to\r\n535 5.7.8  https://support.google.com/mail/?p=BadCredentials 98e67ed59e1d1-3b16903a7fasm986187a91.3 - gsmtp', '2026-11-10 11:31:42'),
(7, 24, 'alvianbdwj@gmail.com', 'Pesanan TL101126-004 sedang dikemas', 'gagal', 'Login SMTP gagal: 535-5.7.8 Username and Password not accepted. For more information, go to\r\n535 5.7.8  https://support.google.com/mail/?p=BadCredentials 41be03b00d2f7-cdd8d7cf07dsm418395a12.28 - gsmtp', '2026-11-10 13:41:22'),
(8, 24, 'alvianbdwj@gmail.com', 'Pesanan TL101126-004 sedang dalam perjalanan', 'gagal', 'Login SMTP gagal: 535-5.7.8 Username and Password not accepted. For more information, go to\r\n535 5.7.8  https://support.google.com/mail/?p=BadCredentials d9443c01a7336-2edb6a412bbsm5504505ad.2 - gsmtp', '2026-11-10 13:45:41');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id_genre` int(11) NOT NULL,
  `nama_genre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id_genre`, `nama_genre`) VALUES
(1, 'Mystery & Detective'),
(2, 'Comic'),
(3, 'Fiction'),
(4, 'History');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
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
-- Dumping data for table `orders`
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
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('Pending','Verified','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `nama_pembeli` varchar(255) NOT NULL,
  `judul_buku` varchar(255) NOT NULL,
  `total` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `approval_time` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `metode_bayar` varchar(50) DEFAULT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `nominal_transfer` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(30) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kode_transaksi` varchar(30) DEFAULT NULL,
  `dikirim_time` datetime DEFAULT NULL,
  `selesai_time` datetime DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL,
  `ppn` int(11) DEFAULT NULL,
  `ongkir` int(11) DEFAULT NULL,
  `no_resi_retur` varchar(50) DEFAULT NULL,
  `kurir_retur` varchar(50) DEFAULT NULL,
  `alasan_retur` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `nama_pembeli`, `judul_buku`, `total`, `status`, `approval_time`, `user_id`, `metode_bayar`, `bukti_transfer`, `token`, `created_at`, `nominal_transfer`, `email`, `no_telepon`, `alamat`, `kode_transaksi`, `dikirim_time`, `selesai_time`, `subtotal`, `ppn`, `ongkir`, `no_resi_retur`, `kurir_retur`, `alasan_retur`) VALUES
(1, 'urip', 'Megalodon', 72000, 'Dikirim / Selesai', '2026-09-11 19:54:09', NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'aurel', 'Jack the ripper', 99000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-002', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'malik', 'Traditional Wing Chun', 80000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-003', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'pran', 'Traditional Wing Chun', 80000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-004', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Shalma', 'The Lover Next Door', 64000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-005', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'malik', 'The Lover Next Door', 64000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-006', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'malik', 'Gamera', 50000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-007', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'malik', 'Jack the ripper', 99000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-008', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'arkan', 'Arsene Lupin', 68000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-009', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'gilang', 'Traditional Wing Chun', 80000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-010', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'aurel', 'Moby Dick', 70000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'malik', 'Sherlock Holmes', 75000, 'Approved', NULL, NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-012', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'nadya', 'Sherlock Holmes', 75000, 'Dikirim / Selesai', '2026-09-11 19:54:13', NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-013', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'nadya', 'Arsene Lupin', 68000, 'Dikirim / Selesai', '2026-09-11 19:54:12', NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-014', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'Aufa Athallah Nur Muhammad Ilyasa Lubis', '30 Bohong Besar Soeharto (1x)', 65000, 'Dikirim / Selesai', '2026-11-03 09:49:30', NULL, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-015', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'User', 'Gamera (1x)', 50000, 'Dikirim / Selesai', '2026-11-03 09:49:13', 2, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-016', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'ipak', 'Gamera (1x)', 50000, 'Dikirim / Selesai', '2026-11-03 09:53:50', 2, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-017', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'malik', 'Traditional Wing Chun (4x)', 320000, 'Dikirim / Selesai', '2026-11-03 10:05:09', 2, NULL, NULL, NULL, '2026-11-06 16:22:49', NULL, NULL, NULL, NULL, 'TL061126-018', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'Athallah malik Prayitno', 'Gamera (1x)', 50000, 'Dikirim / Selesai', '2026-11-06 16:29:05', 3, 'BCA Transfer', 'bukti_20261106_102818_e2af94d8.png', '823937d54a2931b62e48e7dad8118ae4', '2026-11-06 16:26:09', NULL, NULL, NULL, NULL, 'TL061126-019', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'athallah malik prayitno', 'Gamera (1x)', 50000, 'Dikirim / Selesai', '2026-11-09 22:17:37', 1, 'DANA', 'bukti_20261109_161710_95e74369.png', 'd5ce9d2d5f0bc26b5636a508012d8478', '2026-11-09 21:59:00', 50000, 'athallahmalikprayitno@gmail.com', '081389635045', 'JL. Cipinqng pulo maja, Rt 006, Rw 010, Cipinang besar utara', 'TL091126-020', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'alvian budiman wijaya', 'Gamera (1x)', 50000, 'Dalam Perjalanan', '2026-11-10 10:58:08', 4, 'DANA', 'bukti_20261110_105736_377abd03.png', 'ad3bed8f5defac5ea318aea285b4258e', '2026-11-10 10:53:05', 50000, 'alvianbdwj@gmail.com', '087653412804', 'JLN. pesanggrahan Timur Rt 003 Rw 001', 'TL101126-001', '2026-11-10 10:59:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'alvian budiman wijaya', 'Madilog (1x)', 120450, 'Selesai', '2026-11-10 11:09:57', 4, 'DANA', 'bukti_20261110_110938_48144f6f.png', '195ddf3b20e13ec9eb1cdecbbe83002c', '2026-11-10 11:05:09', 120450, 'alvianbdwj@gmail.com', '087653412804', 'JLN. pesanggrahan Timur Rt 003 Rw 001', 'TL101126-002', '2026-11-10 11:10:44', '2026-11-10 11:12:44', 95000, 10450, 15000, NULL, NULL, NULL),
(23, 'alvian budiman wijaya', 'Megalodon (1x)', 94920, 'Selesai', '2026-11-10 11:24:33', 4, 'DANA', 'bukti_20261110_112414_20d8384c.png', '18528d56edb36da219a9af4ca87a7279', '2026-11-10 11:23:47', 94920, 'alvianbdwj@gmail.com', '087653412804', 'JLN. pesanggrahan Timur Rt 003 Rw 001', 'TL101126-003', '2026-11-10 11:31:39', '2026-11-10 11:32:49', 72000, 7920, 15000, NULL, NULL, NULL),
(24, 'alvian budiman wijaya', 'Traditional Wing Chun (1x)', 103800, 'Selesai', '2026-11-10 13:41:17', 4, 'DANA', 'bukti_20261110_133958_0b20dac3.png', '1b4517b47eb92c4774f5f098f3f82d40', '2026-11-10 13:37:49', 103800, 'alvianbdwj@gmail.com', '087653412804', 'JLN. pesanggrahan Timur Rt 003 Rw 001', 'TL101126-004', '2026-11-10 13:45:37', '2026-11-10 13:47:55', 80000, 8800, 15000, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `publishers`
--

CREATE TABLE `publishers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `website_name` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `no_telp`, `alamat`) VALUES
(1, 'admin Lix', 'admin@gmail.com', 'Malik_2009', 'admin', NULL, NULL),
(2, 'User', 'user@gmail.com', 'Malik_2009', 'user', NULL, NULL),
(3, 'Athallah malik Prayitno ', 'athallahmalikprayitno@gmail.com', 'Malik_2009', 'user', '085285851973', 'JL. Cipinqng pulo maja, Rt 006, Rw 010, Cipinang besar utara'),
(4, 'alvian budiman wijaya', 'alvianbdwj@gmail.com', 'Alvian123', 'user', '087653412804', 'JLN. pesanggrahan Timur Rt 003 Rw 001 ');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `theme` enum('light','dark') DEFAULT 'light',
  `language` varchar(20) DEFAULT 'id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `email_log`
--
ALTER TABLE `email_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id_genre`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kode_transaksi` (`kode_transaksi`);

--
-- Indexes for table `publishers`
--
ALTER TABLE `publishers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `community_posts`
--
ALTER TABLE `community_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_log`
--
ALTER TABLE `email_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id_genre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `publishers`
--
ALTER TABLE `publishers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
