-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 25 Sep 2025 pada 10.23
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
-- Database: `rental_motor`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bagi_hasils`
--

CREATE TABLE `bagi_hasils` (
  `ID_Bagi_Hasil` bigint(20) UNSIGNED NOT NULL,
  `penyewaan_id` bigint(20) UNSIGNED NOT NULL,
  `bagi_hasil_pemilik` decimal(10,2) NOT NULL,
  `bagi_hasil_admin` decimal(10,2) NOT NULL,
  `settled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `bagi_hasils`
--

INSERT INTO `bagi_hasils` (`ID_Bagi_Hasil`, `penyewaan_id`, `bagi_hasil_pemilik`, `bagi_hasil_admin`, `settled_at`, `created_at`, `updated_at`) VALUES
(1, 1, 14400.00, 33600.00, '2025-09-24 17:17:37', '2025-09-24 17:17:37', '2025-09-24 17:17:37'),
(2, 2, 600.00, 1400.00, '2025-09-24 18:36:06', '2025-09-24 18:36:06', '2025-09-24 18:36:06'),
(3, 4, 1800.00, 4200.00, '2025-09-24 20:12:58', '2025-09-24 20:12:58', '2025-09-24 20:12:58'),
(4, 3, 16800.00, 39200.00, '2025-09-25 00:46:12', '2025-09-25 00:46:12', '2025-09-25 00:46:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0', 'i:2;', 1758770909),
('laravel-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0:timer', 'i:1758770909;', 1758770909);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_09_24_003715_create_motors_table', 1),
(5, '2025_09_24_003741_create_tarif_rentals_table', 1),
(6, '2025_09_24_003754_create_penyewaans_table', 1),
(7, '2025_09_24_003804_create_transaksis_table', 1),
(8, '2025_09_24_003814_create_bagi_hasils_table', 1),
(9, '2025_09_24_135508_create_pembayarans_table', 1),
(10, '2025_09_25_005808_add_return_fields_to_penyewaans_table', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `motors`
--

CREATE TABLE `motors` (
  `ID_Motor` bigint(20) UNSIGNED NOT NULL,
  `owner_id` bigint(20) UNSIGNED NOT NULL,
  `merk` varchar(255) NOT NULL,
  `tipe_cc` varchar(255) NOT NULL,
  `no_plat` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `dokumen_kepemilikan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `motors`
--

INSERT INTO `motors` (`ID_Motor`, `owner_id`, `merk`, `tipe_cc`, `no_plat`, `status`, `photo`, `dokumen_kepemilikan`, `created_at`, `updated_at`) VALUES
(1, 2, 'Honda bot', '125cc', 'B 1234 ABC', 'disewa', NULL, NULL, '2025-09-24 17:14:08', '2025-09-24 20:13:50'),
(2, 2, 'Yamaha NMAX', '155cc', 'B 2345 BCD', 'tersedia', NULL, NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(3, 2, 'Suzuki Satria', '150cc', 'B 3456 CDE', 'tersedia', NULL, NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(4, 2, 'Honda Vario', '125cc', 'B 4567 DEF', 'tersedia', NULL, NULL, '2025-09-24 17:14:08', '2025-09-25 00:49:51'),
(5, 2, 'Yamaha Aerox', '155cc', 'B 5678 EFG', 'tersedia', NULL, NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(7, 2, 'Honda Scoopy', '110cc', 'B 7890 GHI', 'tersedia', NULL, NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(8, 2, 'Yamaha Mio', '125cc', 'B 8901 HIJ', 'tersedia', NULL, NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(9, 2, 'Honda PCX', '160cc', 'B 9012 IJK', 'tersedia', NULL, NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(10, 2, 'Vespa Sprint', '150cc', 'B 0123 JKL', 'tersedia', NULL, NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(12, 2, 'Mio', '125cc', 'Z 2030 PD', 'tersedia', 'motors/mio-20250925.png', 'documents/mio-document-20250925.png', '2025-09-24 20:27:35', '2025-09-25 00:50:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayarans`
--

CREATE TABLE `pembayarans` (
  `ID_Pembayaran` bigint(20) UNSIGNED NOT NULL,
  `penyewaan_id` bigint(20) UNSIGNED NOT NULL,
  `metode_pembayaran` varchar(255) NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `uang_bayar` decimal(15,2) NOT NULL,
  `uang_kembalian` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `kode_pembayaran` varchar(255) NOT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal_bayar` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pembayarans`
--

INSERT INTO `pembayarans` (`ID_Pembayaran`, `penyewaan_id`, `metode_pembayaran`, `jumlah_bayar`, `uang_bayar`, `uang_kembalian`, `status`, `kode_pembayaran`, `catatan`, `tanggal_bayar`, `created_at`, `updated_at`) VALUES
(1, 1, 'bri_va', 48000.00, 50000.00, 2000.00, 'paid', 'PAY202509252935', 'holahola', '2025-09-24 17:17:37', '2025-09-24 17:17:37', '2025-09-24 17:17:37'),
(2, 2, 'qris_static', 2000.00, 2000.00, 0.00, 'paid', 'PAY202509254612', 'ddfdfdff', '2025-09-24 18:36:06', '2025-09-24 18:36:06', '2025-09-24 18:36:06'),
(3, 4, 'bca_va', 6000.00, 10000.00, 4000.00, 'paid', 'PAY202509258750', 'tes aa', '2025-09-24 20:12:58', '2025-09-24 20:12:58', '2025-09-24 20:12:58'),
(4, 3, 'bca_va', 56000.00, 56000.00, 0.00, 'paid', 'PAY202509251498', 'dfdfd', '2025-09-25 00:46:12', '2025-09-25 00:46:12', '2025-09-25 00:46:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penyewaans`
--

CREATE TABLE `penyewaans` (
  `ID_Penyewaan` bigint(20) UNSIGNED NOT NULL,
  `penyewa_id` bigint(20) UNSIGNED NOT NULL,
  `motor_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `tipe_durasi` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `catatan_pengembalian` text DEFAULT NULL,
  `tanggal_pengembalian` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `penyewaans`
--

INSERT INTO `penyewaans` (`ID_Penyewaan`, `penyewa_id`, `motor_id`, `tanggal_mulai`, `tanggal_selesai`, `tipe_durasi`, `status`, `harga`, `catatan_pengembalian`, `tanggal_pengembalian`, `created_at`, `updated_at`) VALUES
(1, 3, 4, '2025-09-25', '2026-09-25', 'monthly', 'selesai', 48000.00, NULL, NULL, '2025-09-24 17:17:13', '2025-09-24 18:32:03'),
(2, 3, 4, '2025-09-25', '2025-09-26', 'daily', 'selesai', 2000.00, NULL, NULL, '2025-09-24 18:35:55', '2025-09-24 19:04:40'),
(3, 3, 4, '2025-09-25', '2026-11-25', 'monthly', 'selesai', 56000.00, NULL, NULL, '2025-09-24 19:27:59', '2025-09-25 00:49:51'),
(4, 3, 1, '2025-09-27', '2025-10-18', 'weekly', 'dibayar', 6000.00, NULL, NULL, '2025-09-24 20:11:40', '2025-09-24 20:12:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('8EQHCua6CbxckUy72ACkVJOxWc5Q1ds7zRzB3VYu', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQk9oWmFsenlpdEVzTmJwMlJnaVg0TElQUzh0aUtnM25hd1lvcVNXTSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL3VzZXJzIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fX0=', 1758788430),
('9loz8yuHe9YudMTXnmrML8dO1U2cWmoo6l7cSIGE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiTVVBa01ZbDR2ZU5FaXR2QmhyeUJRT1VydldGVjdLUlBaQnZENTdlbCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758785701),
('dIEaCAAJG9N4ivsxyO7piUQ4RACUZNrF4WOgPQVF', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoicWVZeXA3bU5mNjdONGVETWpNc3BSSm5HZEFqR2ZlZUY5OXpRbEd6ZyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL293bmVyL21vdG9ycy9jcmVhdGUiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo0OiJtYXJ5IjthOjE6e3M6NToidG9hc3QiO2E6MDp7fX19', 1758788571),
('Pa4x2brBX0EAb3fBdCTp72i8dIzzS9U9CWgNT3kl', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiOGRLM2EyOVhEMFZ2eEtjM1R6UGdBZm5tWUNyWjBrRmlZMlR6SmFMZCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Jvb2tpbmdzIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ib29raW5ncyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7czo0OiJtYXJ5IjthOjE6e3M6NToidG9hc3QiO2E6MDp7fX19', 1758788451),
('V26pbMUZnvv5FkjXT7D996yoBkdoEEPx5CFzf4AB', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiazY0MnBDTG9MVWx0SUVUV2FhcE9YWDV4OWxyUjJZYzlvdm9oYThVZSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL293bmVyL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1758786191),
('ZiI7l7zgWFy4oajA2DXL8j1YUgGvGWuMBjthKPWr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQ3lMQ2o0M3F6WTdjMTFzSU1nbFRZdDNqdWpDU0tGd1FKOFRtS3hNRyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758785702);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tarif_rentals`
--

CREATE TABLE `tarif_rentals` (
  `ID_Tarif` bigint(20) UNSIGNED NOT NULL,
  `motor_id` bigint(20) UNSIGNED NOT NULL,
  `tarif_harian` int(11) NOT NULL,
  `tarif_mingguan` int(11) NOT NULL,
  `tarif_bulanan` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tarif_rentals`
--

INSERT INTO `tarif_rentals` (`ID_Tarif`, `motor_id`, `tarif_harian`, `tarif_mingguan`, `tarif_bulanan`, `created_at`, `updated_at`) VALUES
(1, 1, 1000, 2000, 3000, '2025-09-24 17:16:33', '2025-09-24 17:16:33'),
(2, 4, 2000, 3000, 4000, '2025-09-24 17:16:49', '2025-09-24 17:16:49'),
(4, 9, 1000, 9000, 90000, '2025-09-24 20:19:43', '2025-09-24 20:19:43'),
(5, 12, 10000, 20000, 30000, '2025-09-24 20:27:35', '2025-09-24 20:27:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksis`
--

CREATE TABLE `transaksis` (
  `ID_Transaksi` bigint(20) UNSIGNED NOT NULL,
  `penyewaan_id` bigint(20) UNSIGNED NOT NULL,
  `metode_pembayaran` varchar(255) NOT NULL,
  `jumlah_bayar` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `transaksis`
--

INSERT INTO `transaksis` (`ID_Transaksi`, `penyewaan_id`, `metode_pembayaran`, `jumlah_bayar`, `status`, `tanggal`, `created_at`, `updated_at`) VALUES
(1, 1, 'bri_va', '48000.00', 'completed', '2025-09-25', '2025-09-24 17:17:37', '2025-09-24 17:17:37'),
(2, 2, 'qris_static', '2000.00', 'completed', '2025-09-25', '2025-09-24 18:36:06', '2025-09-24 18:36:06'),
(3, 4, 'bca_va', '6000.00', 'completed', '2025-09-25', '2025-09-24 20:12:58', '2025-09-24 20:12:58'),
(4, 3, 'bca_va', '56000.00', 'completed', '2025-09-25', '2025-09-25 00:46:12', '2025-09-25 00:46:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `ID_User` bigint(20) UNSIGNED NOT NULL,
  `kode_user` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `role` enum('admin','pemilik','penyewa') NOT NULL DEFAULT 'penyewa',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`ID_User`, `kode_user`, `nama`, `email`, `email_verified_at`, `password`, `no_telp`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'ADM001', 'Admin User', 'admin@a.com', NULL, '$2y$12$dOEKWSmA30CxSjcC8k54zO3J9Hz60.C7zEbRV6E6PwB7OkKTkXSjm', '081234567890', 'admin', NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(2, 'OWN002', 'Pemilik Motor', 'pemilik@a.com', NULL, '$2y$12$zinwU5SwDFIUpBkuZHU0AeoanCSDwIMoFiqORYIWjbdppouBlgNSq', '089876543210', 'pemilik', NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(3, 'REN001', 'Penyewa Motor', 'penyewa@a.com', NULL, '$2y$12$wC2CouD8JIid//uOOcEHlOlvSU1hYfnVVeCg.BGGQWoqBD7bj31Uq', '085555555555', 'penyewa', NULL, '2025-09-24 17:14:08', '2025-09-24 17:14:08'),
(4, 'RND001', 'bebas', 'bebas@mail.com', NULL, '$2y$12$wvd3SFRmP36Y841vqK0vTeRL6MUiTQKkVIcFuzTCPBP.STP4GnwfC', NULL, 'penyewa', NULL, '2025-09-24 20:33:10', '2025-09-24 20:33:10');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bagi_hasils`
--
ALTER TABLE `bagi_hasils`
  ADD PRIMARY KEY (`ID_Bagi_Hasil`),
  ADD UNIQUE KEY `bagi_hasils_penyewaan_id_unique` (`penyewaan_id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `motors`
--
ALTER TABLE `motors`
  ADD PRIMARY KEY (`ID_Motor`),
  ADD UNIQUE KEY `motors_no_plat_unique` (`no_plat`),
  ADD KEY `motors_owner_id_foreign` (`owner_id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `pembayarans`
--
ALTER TABLE `pembayarans`
  ADD PRIMARY KEY (`ID_Pembayaran`),
  ADD UNIQUE KEY `pembayarans_kode_pembayaran_unique` (`kode_pembayaran`),
  ADD KEY `pembayarans_penyewaan_id_status_index` (`penyewaan_id`,`status`),
  ADD KEY `pembayarans_kode_pembayaran_index` (`kode_pembayaran`);

--
-- Indeks untuk tabel `penyewaans`
--
ALTER TABLE `penyewaans`
  ADD PRIMARY KEY (`ID_Penyewaan`),
  ADD KEY `penyewaans_penyewa_id_foreign` (`penyewa_id`),
  ADD KEY `penyewaans_motor_id_foreign` (`motor_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `tarif_rentals`
--
ALTER TABLE `tarif_rentals`
  ADD PRIMARY KEY (`ID_Tarif`),
  ADD KEY `tarif_rentals_motor_id_foreign` (`motor_id`);

--
-- Indeks untuk tabel `transaksis`
--
ALTER TABLE `transaksis`
  ADD PRIMARY KEY (`ID_Transaksi`),
  ADD UNIQUE KEY `transaksis_penyewaan_id_unique` (`penyewaan_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID_User`),
  ADD UNIQUE KEY `users_kode_user_unique` (`kode_user`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bagi_hasils`
--
ALTER TABLE `bagi_hasils`
  MODIFY `ID_Bagi_Hasil` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `motors`
--
ALTER TABLE `motors`
  MODIFY `ID_Motor` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `pembayarans`
--
ALTER TABLE `pembayarans`
  MODIFY `ID_Pembayaran` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `penyewaans`
--
ALTER TABLE `penyewaans`
  MODIFY `ID_Penyewaan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `tarif_rentals`
--
ALTER TABLE `tarif_rentals`
  MODIFY `ID_Tarif` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `transaksis`
--
ALTER TABLE `transaksis`
  MODIFY `ID_Transaksi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `ID_User` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bagi_hasils`
--
ALTER TABLE `bagi_hasils`
  ADD CONSTRAINT `bagi_hasils_penyewaan_id_foreign` FOREIGN KEY (`penyewaan_id`) REFERENCES `penyewaans` (`ID_Penyewaan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `motors`
--
ALTER TABLE `motors`
  ADD CONSTRAINT `motors_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`ID_User`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembayarans`
--
ALTER TABLE `pembayarans`
  ADD CONSTRAINT `pembayarans_penyewaan_id_foreign` FOREIGN KEY (`penyewaan_id`) REFERENCES `penyewaans` (`ID_Penyewaan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penyewaans`
--
ALTER TABLE `penyewaans`
  ADD CONSTRAINT `penyewaans_motor_id_foreign` FOREIGN KEY (`motor_id`) REFERENCES `motors` (`ID_Motor`) ON DELETE CASCADE,
  ADD CONSTRAINT `penyewaans_penyewa_id_foreign` FOREIGN KEY (`penyewa_id`) REFERENCES `users` (`ID_User`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tarif_rentals`
--
ALTER TABLE `tarif_rentals`
  ADD CONSTRAINT `tarif_rentals_motor_id_foreign` FOREIGN KEY (`motor_id`) REFERENCES `motors` (`ID_Motor`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksis`
--
ALTER TABLE `transaksis`
  ADD CONSTRAINT `transaksis_penyewaan_id_foreign` FOREIGN KEY (`penyewaan_id`) REFERENCES `penyewaans` (`ID_Penyewaan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
