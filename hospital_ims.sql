-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 25, 2026 at 01:18 PM
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
-- Database: `hospital_ims`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT 'Guest',
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'admin', 'SYSTEM_INIT', 'System', 'Database initialized and default administrative account seeded.', '127.0.0.1', '2026-05-19 07:38:26'),
(2, 1, 'admin', 'LOGIN', 'Auth', 'User logged in.', '127.0.0.1', '2026-05-19 07:43:50'),
(3, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-19 07:44:20'),
(4, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-19 08:23:31'),
(5, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-19 08:23:39'),
(6, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-19 09:23:19'),
(7, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-19 09:23:42'),
(9, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-19 18:59:18'),
(10, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-19 19:10:23'),
(11, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-19 19:10:44'),
(12, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-19 19:11:00'),
(13, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-19 19:12:22'),
(15, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-19 19:16:42'),
(16, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-19 19:16:47'),
(18, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-20 03:18:37'),
(19, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-20 03:18:41'),
(20, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-20 03:30:27'),
(21, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-20 03:30:52'),
(22, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-20 03:45:37'),
(23, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-20 03:45:41'),
(24, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-20 03:45:44'),
(25, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-20 05:23:59'),
(26, 1, 'admin', 'UPDATE_USER', 'Users', 'Updated user account: juandelacruz. Changes: Username (\'staff_juan\' -> \'juandelacruz\'), Name (\'Juan Dela Cruz (Pharmacy Staff)\' -> \'Juan Dela Cruz\')', '127.0.0.1', '2026-05-20 05:59:19'),
(27, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-20 06:08:31'),
(28, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-20 06:08:47'),
(29, 1, 'admin', 'UPDATE_USER', 'Users', 'Updated user account: juandelacruz. Changes: Password (Changed)', '127.0.0.1', '2026-05-20 06:09:06'),
(30, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-20 06:09:17'),
(31, 2, 'juandelacruz', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-20 06:09:25'),
(32, 2, 'juandelacruz', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-20 06:09:43'),
(33, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-20 06:09:48'),
(34, 1, 'admin', 'EXPORT_CSV', 'Audit Trail', 'Exported audit trail history logs to CSV.', '127.0.0.1', '2026-05-20 06:12:33'),
(35, 1, 'admin', 'PRINT_HISTORY', 'Audit Trail', 'Printed audit trail history logs.', '127.0.0.1', '2026-05-20 06:12:37'),
(36, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-21 07:05:08'),
(37, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-21 07:18:31'),
(38, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-21 07:19:50'),
(39, 1, 'admin', 'PRINT_HISTORY', 'Audit Trail', 'Printed audit trail history logs.', '127.0.0.1', '2026-05-21 07:33:01'),
(40, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-21 08:33:52'),
(41, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-21 08:36:50'),
(42, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 06:47:27'),
(43, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 06:48:44'),
(44, 2, 'juandelacruz', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 06:48:51'),
(45, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 06:49:47'),
(46, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 06:50:04'),
(47, 2, 'juandelacruz', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 06:50:19'),
(48, 2, 'juandelacruz', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 07:04:27'),
(49, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 07:04:31'),
(50, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 07:04:34'),
(51, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 07:43:05'),
(52, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 07:43:10'),
(53, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:02:32'),
(54, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:02:37'),
(55, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:10:02'),
(56, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:10:12'),
(57, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:12:52'),
(58, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:12:55'),
(59, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:15:25'),
(60, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:15:27'),
(61, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:21:30'),
(62, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:21:34'),
(63, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:31:26'),
(64, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:31:29'),
(65, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:35:28'),
(66, 1, 'admin', 'UPDATE_USER', 'Users', 'Updated user account: juandelacruz. Changes: Department (\'PHARMA\' -> \'SUPPLIES\')', '127.0.0.1', '2026-05-22 08:39:30'),
(67, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:42:04'),
(68, 2, 'juandelacruz', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:42:14'),
(69, 2, 'juandelacruz', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:42:21'),
(70, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:42:25'),
(71, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:55:35'),
(72, 2, 'juandelacruz', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:55:43'),
(73, 2, 'juandelacruz', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:55:49'),
(74, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:55:53'),
(75, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-22 08:58:21'),
(76, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-22 08:58:40'),
(77, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-25 00:27:47'),
(78, 1, 'admin', 'LOGOUT', 'Auth', 'User logged out.', '127.0.0.1', '2026-05-25 00:27:54'),
(79, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-25 01:08:10'),
(80, 1, 'admin', 'LOGIN', 'Auth', 'User successfully logged in.', '127.0.0.1', '2026-05-25 05:51:24'),
(81, 1, 'admin', 'UPDATE_PROFILE', 'Auth', 'Updated own profile details. Changed name.', '127.0.0.1', '2026-05-25 07:53:11'),
(82, 1, 'admin', 'PRINT_HISTORY', 'Audit Trail', 'Printed audit trail history logs.', '127.0.0.1', '2026-05-25 08:09:10'),
(83, 1, 'admin', 'EXPORT_CSV', 'Audit Trail', 'Exported audit trail history logs to CSV.', '127.0.0.1', '2026-05-25 08:09:18');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `code`, `name`, `description`, `created_at`) VALUES
(1, 'LAB', 'Laboratory Services', 'Clinical laboratory and diagnostic testing services.', '2026-05-19 07:38:26'),
(2, 'PHARMA', 'Pharmacy Department', 'Medication dispensing and pharmaceutical care services.', '2026-05-19 07:38:26'),
(3, 'SUPPLIES', 'Central Supplies', 'General hospital medical supplies and distribution.', '2026-05-19 07:38:26'),
(4, 'OR/DR COMPLEX', 'OR/DR Complex', 'Operating Room and Delivery Room specialized services.', '2026-05-19 07:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `department` enum('LAB','PHARMA','SUPPLIES','OR/DR COMPLEX') NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) NOT NULL DEFAULT 'pcs',
  `min_stock` int(11) NOT NULL DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `item_code`, `name`, `description`, `department`, `quantity`, `unit`, `min_stock`, `created_at`, `updated_at`) VALUES
(1, 'LAB-001', 'Blood Collection Tube (Red Top)', 'Silicone-coated tubes for serum determinations.', 'LAB', 150, 'pcs', 20, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(2, 'LAB-002', 'Microscope Glass Slides', 'Pre-cleaned glass slides for lab microscopy.', 'LAB', 80, 'box', 10, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(3, 'LAB-003', 'Rapid Antigen Test Kits', 'Rapid diagnostic test kits for infectious disease screening.', 'LAB', 4, 'pcs', 15, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(4, 'PHA-001', 'Paracetamol 500mg Tablets', 'Analgesic and antipyretic tablets.', 'PHARMA', 1200, 'pcs', 100, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(5, 'PHA-002', 'Amoxicillin 250mg Capsules', 'Broad-spectrum antibiotic medication.', 'PHARMA', 500, 'pcs', 50, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(6, 'PHA-003', 'Ibuprofen 400mg Tablets', 'Nonsteroidal anti-inflammatory drug.', 'PHARMA', 3, 'pcs', 20, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(7, 'SUP-001', 'Surgical Gloves (Size 7.5)', 'Sterile latex powder-free surgical gloves.', 'SUPPLIES', 300, 'pairs', 50, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(8, 'SUP-002', 'N95 Respirator Masks', 'Particulate respirator mask for medical protection.', 'SUPPLIES', 120, 'pcs', 30, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(9, 'SUP-003', 'Adhesive Bandages (Assorted)', 'Elastic strip bandages for wound care.', 'SUPPLIES', 2, 'box', 5, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(10, 'ORD-001', 'Suture Silk 3-0', 'Non-absorbable sterile surgical suture.', 'OR/DR COMPLEX', 90, 'box', 15, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(11, 'ORD-002', 'Scalpel Blade No. 10', 'High-grade carbon steel surgical scalpel blades.', 'OR/DR COMPLEX', 150, 'pcs', 20, '2026-05-19 07:38:26', '2026-05-19 07:38:26'),
(12, 'ORD-003', 'Sterile Drape Pack', 'Complete disposable sterile surgical drape kit.', 'OR/DR COMPLEX', 1, 'pcs', 10, '2026-05-19 07:38:26', '2026-05-19 07:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-05-25-000001', 'App\\Database\\Migrations\\AddFirstLastNameToUsers', 'default', 'App', 1779671722, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `role` enum('admin','staff') DEFAULT 'staff',
  `department_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `last_name`, `first_name`, `role`, `department_id`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$3Xhfb.GCbHUtlu0haHSTTOR7X75o9CkiE.EvsWqRs6BNXJNmQPGxe', 'Administrator', 'Hospital', 'admin', NULL, 1, '2026-05-19 07:38:26'),
(2, 'juandelacruz', '$2y$10$uxsb.CCGK.KUvBdDmdo7V.Jm/Azjgxg8OqL1D1a6CqAdQB5FdcNna', 'Dela Cruz', 'Juan', 'staff', 3, 1, '2026-05-19 07:38:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_code` (`item_code`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_users_department` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
