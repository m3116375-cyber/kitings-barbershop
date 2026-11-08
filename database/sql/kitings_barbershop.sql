-- =====================================================================
-- Kiting's Barbershop - Skema database + data awal (MySQL / MariaDB)
-- Untuk yang lebih suka impor lewat phpMyAdmin.
--
-- CARA PAKAI (pilih SALAH SATU, jangan dicampur):
--   A) Disarankan : php artisan migrate:fresh --seed        (tanpa file ini)
--   B) phpMyAdmin : impor file ini, lalu cukup jalankan aplikasi.
--      Tabel `migrations` sudah terisi sehingga `php artisan migrate`
--      tidak akan membuat ulang tabel yang sama.
--
-- Akun contoh (password: password) : admin@kitings.test, owner@kitings.test, pelanggan@kitings.test
-- =====================================================================
CREATE DATABASE IF NOT EXISTS `kitings_barbershop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kitings_barbershop`;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `payments`, `booking_products`, `booking_services`, `bookings`, `slots`, `operating_hours`, `products`, `services`, `sessions`, `password_reset_tokens`, `users`, `migrations`;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'pelanggan',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` int unsigned NOT NULL,
  `duration_minutes` smallint unsigned NOT NULL DEFAULT 30,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` int unsigned NOT NULL,
  `stock` int unsigned NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `operating_hours` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `day_of_week` tinyint unsigned NOT NULL COMMENT '0=Minggu ... 6=Sabtu',
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `open_time` time NOT NULL DEFAULT '09:00:00',
  `close_time` time NOT NULL DEFAULT '21:00:00',
  `slot_duration` smallint unsigned NOT NULL DEFAULT 60,
  `capacity` smallint unsigned NOT NULL DEFAULT 3,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operating_hours_day_of_week_unique` (`day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `slots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `capacity` smallint unsigned NOT NULL,
  `booked_count` smallint unsigned NOT NULL DEFAULT 0,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slots_date_start_time_unique` (`date`,`start_time`),
  KEY `slots_date_index` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `slot_id` bigint unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'menunggu' COMMENT 'menunggu|dikonfirmasi|sedang_dilayani|selesai|dibatalkan',
  `total` int unsigned NOT NULL DEFAULT 0,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_code_unique` (`code`),
  KEY `bookings_status_index` (`status`),
  CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `slots` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `booking_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `service_id` bigint unsigned NOT NULL,
  `price` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `booking_services_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `booking_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `qty` smallint unsigned NOT NULL DEFAULT 1,
  `price` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `booking_products_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `method` varchar(20) DEFAULT NULL COMMENT 'midtrans|cod',
  `status` varchar(20) NOT NULL DEFAULT 'belum_dibayar' COMMENT 'belum_dibayar|lunas|gagal|kedaluwarsa|dibatalkan',
  `amount` int unsigned NOT NULL,
  `midtrans_order_id` varchar(64) DEFAULT NULL,
  `snap_token` text,
  `snap_created_at` timestamp NULL DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_booking_id_unique` (`booking_id`),
  KEY `payments_status_index` (`status`),
  KEY `payments_midtrans_order_id_index` (`midtrans_order_id`),
  CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------- catatan migrasi Laravel ----------------
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000000_create_users_table', 1),
('2026_10_01_000001_create_services_table', 1),
('2026_10_01_000002_create_products_table', 1),
('2026_10_01_000003_create_operating_hours_table', 1),
('2026_10_01_000004_create_slots_table', 1),
('2026_10_01_000005_create_bookings_table', 1),
('2026_10_01_000006_create_payments_table', 1);

-- ---------------- data awal ----------------
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `created_at`, `updated_at`) VALUES
('Admin Kiting''s', 'admin@kitings.test', '081200000001', '$2y$10$yZZrcMPoK8zNE2mbZtNBh.avpaguT80vtAim5jK8AtMaxBkB/eGoG', 'admin', NOW(), NOW()),
('Muhammad Ilham Nurdin Harahap', 'owner@kitings.test', '081200000002', '$2y$10$yZZrcMPoK8zNE2mbZtNBh.avpaguT80vtAim5jK8AtMaxBkB/eGoG', 'owner', NOW(), NOW()),
('Andi Pratama', 'pelanggan@kitings.test', '081200000003', '$2y$10$yZZrcMPoK8zNE2mbZtNBh.avpaguT80vtAim5jK8AtMaxBkB/eGoG', 'pelanggan', NOW(), NOW());

INSERT INTO `operating_hours` (`day_of_week`, `is_open`, `open_time`, `close_time`, `slot_duration`, `capacity`, `created_at`, `updated_at`) VALUES
(0, 1, '09:00:00', '21:00:00', 60, 3, NOW(), NOW()),
(1, 1, '09:00:00', '21:00:00', 60, 3, NOW(), NOW()),
(2, 1, '09:00:00', '21:00:00', 60, 3, NOW(), NOW()),
(3, 1, '09:00:00', '21:00:00', 60, 3, NOW(), NOW()),
(4, 1, '09:00:00', '21:00:00', 60, 3, NOW(), NOW()),
(5, 1, '09:00:00', '21:00:00', 60, 3, NOW(), NOW()),
(6, 1, '09:00:00', '21:00:00', 60, 3, NOW(), NOW());

INSERT INTO `services` (`name`, `description`, `price`, `duration_minutes`, `is_active`, `created_at`, `updated_at`) VALUES
('Potong Rambut', 'Potong rambut sesuai model pilihan, termasuk keramas singkat.', 40000, 30, 1, NOW(), NOW()),
('Cukur & Perawatan', 'Potong rambut, cukur jenggot/kumis, dan masker wajah.', 55000, 45, 1, NOW(), NOW()),
('Pewarnaan Rambut', 'Pewarnaan rambut (belum termasuk produk cat).', 150000, 90, 1, NOW(), NOW()),
('Creambath', 'Perawatan rambut dan kulit kepala dengan pijat relaksasi.', 60000, 40, 1, NOW(), NOW());

INSERT INTO `products` (`name`, `description`, `price`, `stock`, `is_active`, `created_at`, `updated_at`) VALUES
('Cat Rambut', 'Cat rambut semi-permanen, tersedia berbagai warna.', 65000, 12, 1, NOW(), NOW()),
('Serum Perawatan', 'Serum penguat dan pelembap rambut.', 85000, 8, 1, NOW(), NOW()),
('Pomade', 'Pomade water-based, daya tahan hingga 8 jam.', 50000, 20, 1, NOW(), NOW()),
('Hair Tonic', 'Tonik penyegar kulit kepala.', 45000, 10, 1, NOW(), NOW());
