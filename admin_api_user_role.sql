-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 17, 2026 at 03:10 AM
-- Server version: 8.0.30
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_api_user_role`
--

-- --------------------------------------------------------

--
-- Table structure for table `abouts`
--

CREATE TABLE `abouts` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mission_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mission_description` text COLLATE utf8mb4_unicode_ci,
  `mission_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vision_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vision_description` text COLLATE utf8mb4_unicode_ci,
  `vision_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `jwt_token` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-0d4VekmMie6mehYN', 'a:1:{s:11:\"valid_until\";i:1772252806;}', 1773462406),
('laravel-cache-0IpwSbCUJ0t6BJhd', 'a:1:{s:11:\"valid_until\";i:1772255123;}', 1773464783),
('laravel-cache-1Mz7HjY4LJZK8E0H', 'a:1:{s:11:\"valid_until\";i:1772687187;}', 1773895467),
('laravel-cache-aJThIt8JyPw9Sdxo', 'a:1:{s:11:\"valid_until\";i:1772254277;}', 1773462497),
('laravel-cache-axKhgALrT7Wue3kI', 'a:1:{s:11:\"valid_until\";i:1772255109;}', 1773464109),
('laravel-cache-DCoUyn7waHAOE3tC', 'a:1:{s:11:\"valid_until\";i:1773462418;}', 1774669978),
('laravel-cache-GSf6cJuIOMZfLI6e', 'a:1:{s:11:\"valid_until\";i:1772252736;}', 1773461436),
('laravel-cache-j7Lbnix9Zk6pUZHD', 'a:1:{s:11:\"valid_until\";i:1772254401;}', 1773463941),
('laravel-cache-VzlXl7f5saEdFmwi', 'a:1:{s:11:\"valid_until\";i:1772337232;}', 1773544612);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Habib', 'habib', 'Hi Habib', 'uploads/category/1772270620_cat-2.jpg', 0, '2026-02-28 03:23:40', '2026-02-28 03:23:40'),
(5, 'Furni', 'furni', 'Hello furni', 'uploads/category/1772853220_carousel-3.jpg', 1, '2026-03-06 21:13:40', '2026-03-06 21:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint UNSIGNED NOT NULL,
  `faq_category_id` bigint UNSIGNED NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order_number` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faq_categories`
--

CREATE TABLE `faq_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_number` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hires`
--

CREATE TABLE `hires` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order_number` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(85, '0001_01_01_000000_create_users_table', 1),
(86, '0001_01_01_000001_create_cache_table', 1),
(87, '0001_01_01_000002_create_jobs_table', 1),
(88, '2025_12_07_093941_create_permission_tables', 1),
(89, '2025_12_25_060625_create_admins_table', 1),
(90, '2025_12_25_060818_create_settings_table', 1),
(91, '2025_12_29_082355_create_sliders_table', 1),
(92, '2026_01_01_024633_create_hires_table', 1),
(93, '2026_01_01_031410_create_faq_categories_table', 1),
(94, '2026_01_01_040023_create_faqs_table', 1),
(95, '2026_01_01_055200_create_abouts_table', 1),
(96, '2026_01_04_025945_create_categories_table', 1),
(97, '2026_01_04_085026_create_subcategories_table', 1),
(98, '2026_01_28_090638_add_image_and_role_to_users_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `group_name`, `created_at`, `updated_at`) VALUES
(1, 'admin.dashboard.index', 'admin', 'dashboard', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(2, 'admin.dashboard.create', 'admin', 'dashboard', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(3, 'admin.dashboard.store', 'admin', 'dashboard', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(4, 'admin.dashboard.update', 'admin', 'dashboard', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(5, 'admin.index', 'admin', 'admin', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(6, 'admin.register', 'admin', 'admin', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(7, 'admin.store', 'admin', 'admin', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(8, 'admin.edit', 'admin', 'admin', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(9, 'admin.update', 'admin', 'admin', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(10, 'admin.destroy', 'admin', 'admin', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(11, 'admin.logout', 'admin', 'admin', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(12, 'admin.password', 'admin', 'admin', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(13, 'admin.passwordchange', 'admin', 'admin', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(14, 'admin.role.index', 'admin', 'role', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(15, 'admin.role.store', 'admin', 'role', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(16, 'admin.role.edit', 'admin', 'role', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(17, 'admin.role.update', 'admin', 'role', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(18, 'admin.role.delete', 'admin', 'role', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(19, 'admin.permission.index', 'admin', 'permission', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(20, 'admin.permission.store', 'admin', 'permission', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(21, 'admin.permission.edit', 'admin', 'permission', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(22, 'admin.permission.update', 'admin', 'permission', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(23, 'admin.permission.destroy', 'admin', 'permission', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(24, 'admin.category.index', 'admin', 'category', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(25, 'admin.category.store', 'admin', 'category', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(26, 'admin.category.edit', 'admin', 'category', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(27, 'admin.category.update', 'admin', 'category', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(28, 'admin.category.destroy', 'admin', 'category', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(29, 'admin.subcategory.index', 'admin', 'subcategory', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(30, 'admin.subcategory.store', 'admin', 'subcategory', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(31, 'admin.subcategory.edit', 'admin', 'subcategory', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(32, 'admin.subcategory.update', 'admin', 'subcategory', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(33, 'admin.subcategory.destroy', 'admin', 'subcategory', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(34, 'admin.brand.index', 'admin', 'brand', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(35, 'admin.brand.store', 'admin', 'brand', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(36, 'admin.brand.edit', 'admin', 'brand', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(37, 'admin.brand.update', 'admin', 'brand', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(38, 'admin.brand.destroy', 'admin', 'brand', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(39, 'admin.setting.index', 'admin', 'setting', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(40, 'admin.setting.update', 'admin', 'setting', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(41, 'admin.slider.index', 'admin', 'slider', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(42, 'admin.slider.store', 'admin', 'slider', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(43, 'admin.slider.edit', 'admin', 'slider', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(44, 'admin.slider.update', 'admin', 'slider', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(45, 'admin.slider.destroy', 'admin', 'slider', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(46, 'admin.faq-categories.index', 'admin', 'faq-categories', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(47, 'admin.faq-categories.store', 'admin', 'faq-categories', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(48, 'admin.faq-categories.edit', 'admin', 'faq-categories', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(49, 'admin.faq-categories.update', 'admin', 'faq-categories', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(50, 'admin.faq-categories.destroy', 'admin', 'faq-categories', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(51, 'admin.faq.index', 'admin', 'faq', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(52, 'admin.faq.store', 'admin', 'faq', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(53, 'admin.faq.edit', 'admin', 'faq', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(54, 'admin.faq.update', 'admin', 'faq', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(55, 'admin.faq.destroy', 'admin', 'faq', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(56, 'admin.hire.index', 'admin', 'hire', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(57, 'admin.hire.store', 'admin', 'hire', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(58, 'admin.hire.edit', 'admin', 'hire', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(59, 'admin.hire.update', 'admin', 'hire', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(60, 'admin.hire.destroy', 'admin', 'hire', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(61, 'admin.about.index', 'admin', 'about', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(62, 'admin.about.update', 'admin', 'about', '2026-01-28 06:10:30', '2026-01-28 06:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'api', '2026-01-28 06:10:30', '2026-01-28 06:10:30'),
(2, 'Admin', 'api', '2026-01-28 06:10:30', '2026-01-28 06:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('aKgfM2rBsb6RTOQSywLv7w7eOqhApI3bd7LreGGL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiekdlbUpadjBqeTJhUk5RQWJ4eGtXYzRNUWRWUGpsOVhWUzAzTzZFSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772872452),
('PvmBSxzhxMo5zoRElMNRQ5PtCdUJPWUeZ3p95ICT', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUlZTm5LTG5NVlpvWDMwbjVPcEVua2hSWWFTc1JoWVVKSzVpUUcyaCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772271853),
('r7XQLEBkc9AnJTkjYBKVjuQpLIyAo9VVle1RgYeq', NULL, '127.0.0.1', 'PostmanRuntime/7.51.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMjN1cUkzYmw4N0RXTVBFYTlBM3dFZWNyVHNRMXZkVkJoRGptbWZwMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772270499),
('XsN1r6rL5MZ4jO7VeACyruusYgdsVO3HamR7u5mu', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNlJxVmxrYkJSTDBaYzR5bk1McjJxR2ZVQm9SbEVKZ1hhSUJQYVV2bCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9tb3N0bHktb2Rkcy1hcG5pYy1yZWFsbS50cnljbG91ZGZsYXJlLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1773657166),
('Yw1ncaFUfYYoA1FwrScgy6SXY8BOWkK6VG1c8Ckr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQm1HY0J1ZVVTTHAwVDJZN0RjdzVxeUZycFhEV0VORmtDV3dudHZzZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9tb3N0bHktb2Rkcy1hcG5pYy1yZWFsbS50cnljbG91ZGZsYXJlLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1773657410);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `site_name` longtext COLLATE utf8mb4_unicode_ci,
  `site_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_logo` varchar(155) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_meta_description` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_keywords` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_mode` enum('local','live') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright_text` varchar(124) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_url` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube_url` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_url` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_url` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `map_link` text COLLATE utf8mb4_unicode_ci,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_number` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_no` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `support_email` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pusher_app_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pusher_app_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pusher_app_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pusher_app_cluster` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_client_id` text COLLATE utf8mb4_unicode_ci,
  `google_client_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_redirect_url` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order_number` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '0 = User, 1 = Admin',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '0 = Inactive, 1 = Active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jwt_token` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `image`, `type`, `phone`, `role`, `status`, `remember_token`, `jwt_token`, `google_id`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super@gmail.com', NULL, '$2y$12$BfezH6LhiYShYXUvIr.5WOk.zLZzgVJtY0MrTCH/fXs6RbP4rxXhm', NULL, 1, NULL, NULL, '1', NULL, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNzczNDYxMDg3LCJleHAiOjE3NzM0NjQ2ODcsIm5iZiI6MTc3MzQ2MTA4NywianRpIjoiZWhzRzd4ZnBjRXJ0SHFhVyIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.iLT7OK-9JWGbWdakLLUqtzjWKsnVUFVB0uKGgbWqNgw', NULL, '2026-01-28 06:10:30', '2026-03-13 22:04:47'),
(2, 'Admin', 'admin@gmail.com', NULL, '$2y$12$Dy4yeJ9cQiNnHqR2mUiz2.HYEieklaVW9NlRg2dhNEIsgxUp3T/Y.', NULL, 1, NULL, NULL, '1', NULL, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNzczNDYyNDE4LCJleHAiOjE3NzM0NjYwMTgsIm5iZiI6MTc3MzQ2MjQxOCwianRpIjoib1dHNVJsVVJXQlVhaEJQciIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.umSQRExNSzeQMBQt2IBP2CBFyK88f1HGDiUVz7FXyFI', NULL, '2026-01-28 06:10:30', '2026-03-13 22:26:58'),
(6, 'admin', 'supddedrsssdd@gmail.com', NULL, '$2y$12$/VXWhwmWA6nN1SESajoKm.zzB5dN85RpXMlVsHvQ6wd96RfnIMiNW', NULL, 0, '01985625114', NULL, NULL, NULL, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2FkbWluL3JlZ2lzdGVyIiwiaWF0IjoxNzY5NjU3MDU5LCJleHAiOjE3Njk2NjA2NTksIm5iZiI6MTc2OTY1NzA1OSwianRpIjoiSm9Lb3lYWWJraHVLelltTiIsInN1YiI6IjYiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.RNSLvH4xpbaWA3DkFY-hWM1J_hVSdX9YaJ4AYTaew2M', NULL, '2026-01-28 21:24:19', '2026-01-28 21:24:19'),
(7, 'admin', 'supddedrddsssdd@gmail.com', NULL, '$2y$12$itRqcR4rdcZAOBOmaV84mue3NE8fKcZZFZcWu7TKFIJvwH7jC0/9G', NULL, 0, '01985625114', NULL, NULL, NULL, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2FkbWluL3JlZ2lzdGVyIiwiaWF0IjoxNzY5NjU3MzgyLCJleHAiOjE3Njk2NjA5ODIsIm5iZiI6MTc2OTY1NzM4MiwianRpIjoibTlQMVl3U1ZEbGhSNVRCOCIsInN1YiI6IjciLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.F8d142fFBo1ftoyY144YZJBnfM2cvKJn9QJ5KJeYlE4', NULL, '2026-01-28 21:29:42', '2026-01-28 21:29:42'),
(8, 'admin', 'supddedrddssffsdd@gmail.com', NULL, '$2y$12$tSu9T7hKcMVtjhjFGYJLduNI8KWCJDGoAangG4ZQS9.n8hqi6RWR2', NULL, 0, '01985625114', NULL, NULL, NULL, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2FkbWluL3JlZ2lzdGVyIiwiaWF0IjoxNzY5NjU3NTI1LCJleHAiOjE3Njk2NjExMjUsIm5iZiI6MTc2OTY1NzUyNSwianRpIjoiRG5hQkkyOHRmSlk5RHZBaiIsInN1YiI6IjgiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.wO2gDzcnkvpSCKpzr2VDJ3Yn6w_rlhsr8WpaHqEboEk', NULL, '2026-01-28 21:32:05', '2026-01-28 21:32:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abouts`
--
ALTER TABLE `abouts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faqs_faq_category_id_foreign` (`faq_category_id`);

--
-- Indexes for table `faq_categories`
--
ALTER TABLE `faq_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faq_categories_slug_unique` (`slug`);

--
-- Indexes for table `hires`
--
ALTER TABLE `hires`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subcategories_slug_unique` (`slug`),
  ADD KEY `subcategories_category_id_foreign` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abouts`
--
ALTER TABLE `abouts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faq_categories`
--
ALTER TABLE `faq_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hires`
--
ALTER TABLE `hires`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `faqs`
--
ALTER TABLE `faqs`
  ADD CONSTRAINT `faqs_faq_category_id_foreign` FOREIGN KEY (`faq_category_id`) REFERENCES `faq_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
