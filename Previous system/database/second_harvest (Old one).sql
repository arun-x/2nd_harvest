-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2026 at 10:36 AM
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
-- Database: `second_harvest`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `charities`
--

DROP TABLE IF EXISTS `charities`;
CREATE TABLE `charities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `org_name` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `operational_focus` varchar(150) DEFAULT NULL,
  `verification_doc_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `charities`
--

INSERT INTO `charities` (`id`, `user_id`, `org_name`, `address`, `operational_focus`, `verification_doc_path`) VALUES
(1, 2, 'Hope Kitchen', '45 Galle Road, Colombo 06', 'Daily meal programme for low-income families', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `disputes`
--

DROP TABLE IF EXISTS `disputes`;
CREATE TABLE `disputes` (
  `id` int(11) NOT NULL,
  `raised_by` int(11) NOT NULL,
  `against_user` int(11) DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `details` text NOT NULL,
  `status` enum('open','pending_info','resolved') NOT NULL DEFAULT 'open',
  `resolution` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

DROP TABLE IF EXISTS `listings`;
CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `outlet_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `category` enum('fruit','vegetable') NOT NULL,
  `quantity_kg` decimal(6,2) NOT NULL,
  `quantity_remaining_kg` decimal(6,2) NOT NULL,
  `reference_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date NOT NULL,
  `claim_deadline` datetime NOT NULL,
  `status` enum('available','reserved','collected','expired','removed') NOT NULL DEFAULT 'available',
  `posted_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `outlet_id`, `item_name`, `category`, `quantity_kg`, `quantity_remaining_kg`, `reference_price`, `expiry_date`, `claim_deadline`, `status`, `posted_by`, `created_at`) VALUES
(27, 5, 'Strawberry', 'fruit', 100.00, 100.00, 200.00, '2026-08-10', '2026-08-10 22:30:00', 'available', 15, '2026-08-10 16:01:53'),
(29, 5, 'Apple', 'fruit', 5.00, 0.00, 250.00, '2026-08-10', '2026-08-10 22:30:00', 'collected', 15, '2026-08-10 16:02:24'),
(30, 5, 'Mango', 'fruit', 10.00, 3.00, 300.00, '2026-08-10', '2026-08-10 22:30:00', 'available', 15, '2026-08-10 16:11:03');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 2, 'New listing available: Bananas at FreshMart Supermarket - Colombo 05', 'new_listing', 0, '2026-07-12 13:30:11'),
(2, 1, 'A consumer reserved 1 kg of \"Bananas\".', 'consumer_reservation', 0, '2026-07-26 11:10:07'),
(3, 1, 'A consumer reserved 1 kg of \"Bananas\".', 'consumer_reservation', 0, '2026-07-26 13:05:05'),
(4, 8, 'A consumer reserved 1 kg of \"Mixed Organic Greens\".', 'consumer_reservation', 0, '2026-07-26 22:38:01'),
(5, 8, 'A consumer reserved 6 kg of \"Ripe Avocado Crate\".', 'consumer_reservation', 0, '2026-07-26 22:39:43'),
(6, 1, 'A consumer reserved 1 kg of \"Carrots\".', 'consumer_reservation', 0, '2026-07-26 23:21:01'),
(7, 1, 'A consumer reserved 1 kg of \"Bananas\".', 'consumer_reservation', 0, '2026-07-27 00:12:55'),
(8, 1, 'A consumer reserved 1 kg of \"Carrots\".', 'consumer_reservation', 0, '2026-08-02 01:20:44'),
(9, 1, 'A consumer reserved 1 kg of \"Bananas\".', 'consumer_reservation', 0, '2026-08-03 22:45:55'),
(10, 1, 'A consumer reserved 1 kg of \"Carrots\".', 'consumer_reservation', 0, '2026-08-07 15:52:06'),
(11, 1, 'A consumer reserved 1 kg of \"Carrots\".', 'consumer_reservation', 0, '2026-08-07 16:04:00'),
(12, 1, 'A consumer reserved 1 kg of \"Bananas\".', 'consumer_reservation', 0, '2026-08-07 16:04:10'),
(13, 15, 'A consumer reserved 1 kg of \"Mangoas\".', 'consumer_reservation', 0, '2026-08-09 03:06:19'),
(14, 1, 'A consumer reserved 2 kg of \"Bananas\".', 'consumer_reservation', 0, '2026-08-09 03:13:17'),
(15, 15, 'A consumer reserved 1 kg of \"Momos\".', 'consumer_reservation', 0, '2026-08-09 03:18:54'),
(16, 15, 'A consumer reserved 1 kg of \"Mangoas\".', 'consumer_reservation', 0, '2026-08-09 03:53:10'),
(17, 15, 'A consumer reserved 3 kg of \"Momos\".', 'consumer_reservation', 0, '2026-08-09 03:54:43'),
(18, 15, 'A consumer reserved 1 kg of \"Banana\".', 'consumer_reservation', 0, '2026-08-10 10:10:29'),
(19, 15, 'A consumer reserved 10 kg of \"Carrot\".', 'consumer_reservation', 0, '2026-08-10 12:15:13'),
(20, 15, 'A consumer reserved 100 kg of \"Strawberry\".', 'consumer_reservation', 0, '2026-08-10 15:35:31'),
(21, 15, 'A consumer reserved 4 kg of \"Apple\".', 'consumer_reservation', 0, '2026-08-10 16:03:52'),
(22, 15, 'A consumer reserved 1 kg of \"Apple\".', 'consumer_reservation', 0, '2026-08-10 16:06:13'),
(23, 15, 'A consumer reserved 5 kg of \"Mango\".', 'consumer_reservation', 0, '2026-08-10 16:15:26'),
(24, 15, 'A consumer reserved 2 kg of \"Mango\".', 'consumer_reservation', 0, '2026-08-10 16:28:07'),
(25, 14, 'Reservation confirmed for 2 kg of \"Mango\". Pre-payment simulated: LKR 240.00.', 'reservation_confirmed', 1, '2026-08-10 16:28:07'),
(26, 14, 'Pickup completed at the outlet for \"Mango\" (2 kg). Thanks for rescuing food!', 'pickup_completed_by_outlet', 1, '2026-08-10 16:34:24'),
(27, 15, 'A consumer reserved 1 kg of \"Broccoli\".', 'consumer_reservation', 0, '2026-08-10 16:37:53'),
(28, 14, 'Reservation confirmed for 1 kg of \"Broccoli\". Pre-payment simulated: LKR 120.00.', 'reservation_confirmed', 1, '2026-08-10 16:37:53'),
(29, 19, 'A consumer reserved 3 kg of \"Strawberry\".', 'consumer_reservation', 0, '2026-09-19 14:04:57'),
(30, 20, 'Reservation confirmed for 3 kg of \"Strawberry\". Pre-payment simulated: LKR 240.00.', 'reservation_confirmed', 1, '2026-09-19 14:04:57'),
(31, 20, 'Pickup completed at the outlet for \"Strawberry\" (3 kg). Thanks for rescuing food!', 'pickup_completed_by_outlet', 1, '2026-09-19 14:05:27');

-- --------------------------------------------------------

--
-- Table structure for table `outlets`
--

DROP TABLE IF EXISTS `outlets`;
CREATE TABLE `outlets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `outlet_name` varchar(150) NOT NULL,
  `branch_location` varchar(255) NOT NULL,
  `region` varchar(100) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `license_doc_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `outlets`
--

INSERT INTO `outlets` (`id`, `user_id`, `outlet_name`, `branch_location`, `region`, `latitude`, `longitude`, `license_doc_path`) VALUES
(1, 1, 'FreshMart Supermarket - Colombo 05', '123 Havelock Road, Colombo 05', 'Colombo', NULL, NULL, NULL),
(2, 8, 'Green Valley Grocers', '124 Market St, Colombo 07', 'Western', NULL, NULL, NULL),
(3, 9, 'The Daily Knead', '45 Bakery Ln, Colombo 05', 'Western', NULL, NULL, NULL),
(4, 13, 'Keells', '12-Castle Lane', 'Kollupitiya', NULL, NULL, NULL),
(5, 15, 'Keells', '12-Castle Lane', 'Kollupitiya', NULL, NULL, NULL),
(6, 16, 'Keells', '12-Castle Lane', 'Kollupitiya', NULL, NULL, NULL),
(7, 17, 'Keells', '12-Castle Lane', 'Kollupitiya', NULL, NULL, NULL),
(8, 19, 'SuperCity', '12-Castle Lane', 'Wellawatte', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pickups`
--

DROP TABLE IF EXISTS `pickups`;
CREATE TABLE `pickups` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `collected_qty_kg` decimal(6,2) NOT NULL,
  `confirmed_by` int(11) NOT NULL,
  `confirmed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pickups`
--

INSERT INTO `pickups` (`id`, `reservation_id`, `collected_qty_kg`, `confirmed_by`, `confirmed_at`) VALUES
(20, 25, 4.00, 15, '2026-08-10 16:04:18'),
(21, 26, 1.00, 15, '2026-08-10 16:06:33'),
(22, 27, 5.00, 15, '2026-08-10 16:28:49'),
(23, 28, 2.00, 15, '2026-08-10 16:34:24');

-- --------------------------------------------------------

--
-- Table structure for table `pickup_slots`
--

DROP TABLE IF EXISTS `pickup_slots`;
CREATE TABLE `pickup_slots` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `slot_start` datetime NOT NULL,
  `slot_end` datetime NOT NULL,
  `capacity` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `booked_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pickup_slots`
--

INSERT INTO `pickup_slots` (`id`, `listing_id`, `slot_start`, `slot_end`, `capacity`, `booked_count`) VALUES
(237, 27, '2026-08-10 19:00:00', '2026-08-10 19:30:00', 1, 0),
(238, 27, '2026-08-10 19:30:00', '2026-08-10 20:00:00', 1, 0),
(239, 27, '2026-08-10 20:00:00', '2026-08-10 20:30:00', 1, 0),
(240, 27, '2026-08-10 20:30:00', '2026-08-10 21:00:00', 1, 0),
(241, 27, '2026-08-10 21:00:00', '2026-08-10 21:30:00', 1, 0),
(242, 27, '2026-08-10 21:30:00', '2026-08-10 22:00:00', 1, 0),
(243, 27, '2026-08-10 22:00:00', '2026-08-10 22:30:00', 1, 0),
(251, 29, '2026-08-10 19:00:00', '2026-08-10 19:30:00', 1, 0),
(252, 29, '2026-08-10 19:30:00', '2026-08-10 20:00:00', 1, 0),
(253, 29, '2026-08-10 20:00:00', '2026-08-10 20:30:00', 1, 0),
(254, 29, '2026-08-10 20:30:00', '2026-08-10 21:00:00', 1, 1),
(255, 29, '2026-08-10 21:00:00', '2026-08-10 21:30:00', 1, 1),
(256, 29, '2026-08-10 21:30:00', '2026-08-10 22:00:00', 1, 0),
(257, 29, '2026-08-10 22:00:00', '2026-08-10 22:30:00', 1, 0),
(258, 30, '2026-08-10 19:00:00', '2026-08-10 19:30:00', 1, 0),
(259, 30, '2026-08-10 19:30:00', '2026-08-10 20:00:00', 1, 0),
(260, 30, '2026-08-10 20:00:00', '2026-08-10 20:30:00', 1, 0),
(261, 30, '2026-08-10 20:30:00', '2026-08-10 21:00:00', 1, 1),
(262, 30, '2026-08-10 21:00:00', '2026-08-10 21:30:00', 1, 1),
(263, 30, '2026-08-10 21:30:00', '2026-08-10 22:00:00', 1, 0),
(264, 30, '2026-08-10 22:00:00', '2026-08-10 22:30:00', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reserved_qty_kg` decimal(6,2) NOT NULL,
  `reservation_type` enum('charity_priority','consumer_paid') NOT NULL,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `price_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pickup_slot_id` int(11) DEFAULT NULL,
  `status` enum('active','cancelled','no_show','completed') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `listing_id`, `user_id`, `reserved_qty_kg`, `reservation_type`, `discount_pct`, `price_paid`, `pickup_slot_id`, `status`, `created_at`) VALUES
(25, 29, 14, 4.00, 'consumer_paid', 60.00, 400.00, 254, 'completed', '2026-08-10 16:03:52'),
(26, 29, 14, 1.00, 'consumer_paid', 60.00, 100.00, 255, 'completed', '2026-08-10 16:06:13'),
(27, 30, 14, 5.00, 'consumer_paid', 60.00, 600.00, 261, 'completed', '2026-08-10 16:15:26'),
(28, 30, 14, 2.00, 'consumer_paid', 60.00, 240.00, 262, 'completed', '2026-08-10 16:28:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('employee','charity','consumer','admin') NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `status` enum('pending','approved','rejected','locked') NOT NULL DEFAULT 'pending',
  `failed_logins` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `email`, `password_hash`, `full_name`, `phone`, `status`, `failed_logins`, `created_at`, `updated_at`) VALUES
(1, 'employee', 'staff@freshmart.lk', '$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Nimal Perera', '0771234567', 'approved', 0, '2026-07-12 13:30:10', '2026-07-12 13:30:10'),
(2, 'charity', 'contact@hopekitchen.lk', '$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Hope Kitchen Rep', '0777654321', 'approved', 0, '2026-07-12 13:30:10', '2026-07-12 13:30:10'),
(3, 'consumer', 'consumer1@example.com', '$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Amaya Silva', '0712223344', 'approved', 0, '2026-07-12 13:30:10', '2026-07-12 13:30:10'),
(4, 'admin', 'admin@2ndharvest.lk', '$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Platform Admin', '0700000000', 'approved', 0, '2026-07-12 13:30:10', '2026-07-12 13:30:10'),
(5, 'employee', 'staff2@greengrocer.lk', '$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Kasun Fernando', '0719988776', 'pending', 0, '2026-07-12 13:30:10', '2026-07-12 13:30:10'),
(6, 'charity', 'info@foodforall.lk', '$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Food For All Rep', '0765554433', 'pending', 0, '2026-07-12 13:30:10', '2026-07-12 13:30:10'),
(7, 'consumer', 'jordan@demo.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jordan Miller', '+94 71 111 1111', 'approved', 0, '2026-07-25 21:54:18', '2026-08-02 00:59:24'),
(8, 'employee', 'staff@greenvalley.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Green Valley Staff', NULL, 'approved', 0, '2026-07-25 21:54:18', '2026-07-25 21:54:18'),
(9, 'employee', 'staff@dailyknead.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Daily Knead Staff', NULL, 'approved', 0, '2026-07-25 21:54:18', '2026-07-25 21:54:18'),
(10, 'consumer', 'arun@gmail.com', '$2y$10$B1ZJa93KmmVJfCeQQfc1UutBsg.iaPelM6QE6ioz1p5omfg6jhddC', 'Arun', NULL, 'approved', 0, '2026-08-02 01:04:54', '2026-08-02 01:04:54'),
(11, 'consumer', 'john@gmail.com', '$2y$10$pobygZp0GRy5o4PU8ZwYMezjS92q6IaQrFJ7BYB1ngxxIOds8stym', 'John', NULL, 'approved', 0, '2026-08-02 01:15:34', '2026-08-02 01:15:34'),
(13, 'employee', 'arun19@gmail.com', '$2y$10$CwlUeGKvJH7l0sFUvwEUS.uZpJvGsczWEDK998Um/CevA4DsRhlJa', 'Arun', '0710656706', 'approved', 0, '2026-08-08 14:43:11', '2026-08-08 14:43:11'),
(14, 'consumer', 'arun22@gmail.com', '$2y$10$MsBW1kmkQ3gXYVH/kLEIQ.n0KmwTNM1/WzjLHp5BnMd8P3U4Ke8Dy', 'Arun', NULL, 'approved', 0, '2026-08-09 02:29:55', '2026-08-10 11:40:53'),
(15, 'employee', 'arun33@gmail.com', '$2y$10$WsWFVHiELFhqWTqOg8QFzOBH6UWYz2DZUmcqo9LHg5RTS61Q1QVuW', 'Kumar', '5653251541', 'approved', 0, '2026-08-09 02:34:34', '2026-08-10 11:37:45'),
(16, 'employee', 'arun67@gmail.com', '$2y$10$P7l2P98/inzfDpwDndEamuksN5.NcGWnJqCZJAibEB4q721MoYM8S', 'Arun67', '0710656706', 'approved', 0, '2026-08-10 08:17:22', '2026-08-10 08:17:22'),
(17, 'employee', 'arun34@gmail.com', '$2y$10$Kbfr4GGCNZknsVgLbV8ZzOGqH0qSLMVQGM5WuprZWlygIveXmG/ce', 'Arun', '0710656706', 'approved', 0, '2026-09-19 10:31:34', '2026-09-19 10:31:34'),
(18, 'consumer', 'arun43@gmail.com', '$2y$10$lZUG.MDMYpWV/a1IgHzZzOmwSyrJkBGrgYYOFsFYVMTHNVnXlJkJa', 'arun', NULL, 'approved', 0, '2026-09-19 10:32:27', '2026-09-19 10:32:27'),
(19, 'employee', 'arun54@gmail.com', '$2y$10$RTfFjGfyU25R3hNOPkri..8v6OHxpM64DFjkMRhSQ9rQPxJdyDH5y', 'Anitha', '0710656706', 'approved', 0, '2026-09-19 14:02:40', '2026-09-19 14:02:40'),
(20, 'consumer', 'arun45@gmail.com', '$2y$10$Fh1nHD8/8cpC8wwY15ona.HS/20DETIf92pvWxJCt647xKO9WxRXO', 'Arun', NULL, 'approved', 0, '2026-09-19 14:03:46', '2026-09-19 14:03:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `charities`
--
ALTER TABLE `charities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `disputes`
--
ALTER TABLE `disputes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `raised_by` (`raised_by`),
  ADD KEY `against_user` (`against_user`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`),
  ADD KEY `idx_listings_status` (`status`),
  ADD KEY `idx_listings_outlet` (`outlet_id`),
  ADD KEY `idx_listings_deadline` (`claim_deadline`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`,`is_read`);

--
-- Indexes for table `outlets`
--
ALTER TABLE `outlets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_outlets_geo` (`latitude`,`longitude`);

--
-- Indexes for table `pickups`
--
ALTER TABLE `pickups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `confirmed_by` (`confirmed_by`);

--
-- Indexes for table `pickup_slots`
--
ALTER TABLE `pickup_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pickup_slot_id` (`pickup_slot_id`),
  ADD KEY `idx_reservations_listing` (`listing_id`),
  ADD KEY `idx_reservations_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `charities`
--
ALTER TABLE `charities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `outlets`
--
ALTER TABLE `outlets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pickups`
--
ALTER TABLE `pickups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `pickup_slots`
--
ALTER TABLE `pickup_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=272;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `charities`
--
ALTER TABLE `charities`
  ADD CONSTRAINT `charities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disputes`
--
ALTER TABLE `disputes`
  ADD CONSTRAINT `disputes_ibfk_1` FOREIGN KEY (`raised_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `disputes_ibfk_2` FOREIGN KEY (`against_user`) REFERENCES `users` (`id`);

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_ibfk_1` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `listings_ibfk_2` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `outlets`
--
ALTER TABLE `outlets`
  ADD CONSTRAINT `outlets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pickups`
--
ALTER TABLE `pickups`
  ADD CONSTRAINT `pickups_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pickups_ibfk_2` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `pickup_slots`
--
ALTER TABLE `pickup_slots`
  ADD CONSTRAINT `pickup_slots_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`pickup_slot_id`) REFERENCES `pickup_slots` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
