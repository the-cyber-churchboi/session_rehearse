-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2023 at 11:41 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `building`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `hkid_passport` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verification_code` varchar(32) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_registered` tinyint(4) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `verification_code_1` varchar(64) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `hkid_passport`, `email`, `password`, `verification_code`, `is_verified`, `registration_date`, `is_registered`, `reset_token`, `verification_code_1`, `token_expiration`) VALUES
(30, 'B678922(4)', 'obadsam200@gmail.com', '$2y$10$YnXqouQrq9e.hXkVnBnmoOjTRDLTP0zeCHfcQ9k7YWl23UMxZwNvS', 'bc9ed429ae47cf4103c770688fa3975d', 1, '2023-08-05 13:34:11', 1, NULL, NULL, NULL),
(31, 'A012345(9)', 'myobad4929@gmail.com', '$2y$10$f8KsNQno99J8V9snvUk/5u./j5kr8exqm1yLj0jhvi79nd.h.cbWK', '2b18eaa5bbddb797057ad5d9215c3c2a', 1, '2023-08-08 16:51:18', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_registration`
--

CREATE TABLE `admin_registration` (
  `id` int(11) NOT NULL,
  `title` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `hkid_passport` varchar(50) NOT NULL,
  `profession` varchar(100) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `company_address` varchar(200) NOT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `building` varchar(100) DEFAULT NULL,
  `street_number` varchar(50) DEFAULT NULL,
  `street_name` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `years_experience` int(11) DEFAULT NULL,
  `professional_membership` varchar(100) DEFAULT NULL,
  `proof_membership` longblob DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email` varchar(50) DEFAULT NULL,
  `unique_identifier` varchar(255) NOT NULL,
  `full_name` varchar(255) GENERATED ALWAYS AS (concat(`first_name`,' ',`last_name`)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_registration`
--

INSERT INTO `admin_registration` (`id`, `title`, `first_name`, `last_name`, `hkid_passport`, `profession`, `company_name`, `company_address`, `floor`, `building`, `street_number`, `street_name`, `district`, `years_experience`, `professional_membership`, `proof_membership`, `created_at`, `updated_at`, `email`, `unique_identifier`) VALUES
(11, 'Mrs./Ms.', 'Obadare', 'Ayokunle', 'B678922(4)', 'Engineer', 'Horbad', 'aule, Ilupeju quarters, akure, ondo state', '10', 'mybuilding', '11', 'aule', '627jsjka', 45, 'dre', 0x433a5c78616d70705c6874646f63735c6275696c64696e675f776f726b2f75706c6f6164732f56454e4f4d204359533230332e706466, '2023-08-05 13:35:22', '2023-08-09 14:39:41', 'obadsam200@gmail.com', '1000011'),
(12, 'Mr', 'Obadare', 'Samuel', 'A012345(9)', 'Local Authority', 'Horbad', 'aule, Ilupeju quarters, akure, ondo state', '12', 'mybuilding', '12', 'arewe', '627jsjka', 5, 'aaad', 0x433a5c78616d70705c6874646f63735c6275696c64696e675f776f726b2f75706c6f6164732f6f626164206379733230332e706466, '2023-08-08 17:08:35', '2023-08-09 14:39:41', 'myobad4929@gmail.com', '1000012');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Obadare Ayokunle', 'ayostarobad@gmail.com', 'referral error', 'kdkdkkdkdkd', '2023-08-07 11:47:24'),
(2, 'Obadare Ayokunle', 'obadsam200@gmail.com', 'referral error', 'kkdkkddkdk', '2023-08-07 11:58:32');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `receiver_id` varchar(255) DEFAULT NULL,
  `sender_id` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `receiver_id`, `sender_id`, `message`, `timestamp`) VALUES
(1, '1000011', '110', 'hello', '2023-08-09 14:46:48'),
(2, '1000011', '110', 'hello', '2023-08-09 14:47:04'),
(3, '1000011', '110', 'hddddd', '2023-08-10 18:59:21'),
(4, '1000011', '110', 'hello', '2023-08-10 20:39:35'),
(5, '1000011', '110', 'hello', '2023-08-10 20:39:36'),
(6, '1000011', '110', 'hello', '2023-08-10 20:39:36'),
(7, '1000011', '110', 'hello', '2023-08-10 20:39:37'),
(8, '1000012', '110', 'hello', '2023-08-10 20:39:40'),
(9, '1000012', '110', 'hello', '2023-08-10 20:39:40'),
(10, '1000012', '110', 'hello', '2023-08-10 20:39:40'),
(11, '1000011', '110', 'hello', '2023-08-10 20:41:38'),
(12, '1000011', '110', 'how do u do', '2023-08-10 20:41:56'),
(13, '1000011', '110', 'hello', '2023-08-10 20:45:54'),
(14, '1000011', '110', 'how are you doing', '2023-08-10 20:46:05'),
(15, '1000011', '110', 'hello', '2023-08-10 20:54:20'),
(16, '1000011', '110', 'how are you', '2023-08-10 20:54:28'),
(17, '1000012', '110', 'fuck you', '2023-08-10 20:54:38'),
(18, '1000011', '110', 'you', '2023-08-10 20:57:50'),
(19, '1000011', '110', 'what\'s up', '2023-08-10 20:57:57'),
(20, '1000012', '110', 'djdjdjj', '2023-08-10 21:01:38'),
(21, '1000011', '110', 'hello', '2023-08-10 23:34:58'),
(22, '1000011', '110', 'how are you doing??', '2023-08-10 23:35:06'),
(23, '1000012', '110', 'hello', '2023-08-10 23:35:25'),
(24, '1000012', '110', 'how are you doing??', '2023-08-10 23:35:32'),
(25, '1000012', '110', 'good?', '2023-08-10 23:35:38'),
(26, '1000011', '1000011', 'hello', '2023-08-13 07:02:21'),
(27, '1000012', '1000011', 'hello', '2023-08-13 07:02:28'),
(28, '1000012', '1000011', 'hello', '2023-08-13 07:03:04'),
(29, '1000011', '1000011', 'hello', '2023-08-13 07:04:14'),
(30, '1000012', '1000011', 'hello', '2023-08-13 07:31:00'),
(31, '1000012', '1000011', 'hello', '2023-08-13 07:35:38'),
(32, '1000011', '110', 'hdjddkdd', '2023-08-31 17:09:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `title` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `room` varchar(10) NOT NULL,
  `flat` varchar(10) NOT NULL,
  `block` varchar(10) NOT NULL,
  `floor` varchar(10) NOT NULL,
  `building` varchar(100) NOT NULL,
  `street_number` varchar(10) NOT NULL,
  `street_name` varchar(100) NOT NULL,
  `district` varchar(50) NOT NULL,
  `district_options` varchar(50) NOT NULL,
  `tenancy_status` varchar(50) NOT NULL,
  `scale_of_renovation` varchar(100) NOT NULL,
  `apartment_type` varchar(100) NOT NULL,
  `apartment_amenities` text NOT NULL,
  `major_requirements` text NOT NULL,
  `apartment_defects` text NOT NULL,
  `address_proof` varchar(255) NOT NULL,
  `timestamps` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(50) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL,
  `unique_identifier` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `title`, `first_name`, `last_name`, `username`, `password`, `date_of_birth`, `phone_number`, `room`, `flat`, `block`, `floor`, `building`, `street_number`, `street_name`, `district`, `district_options`, `tenancy_status`, `scale_of_renovation`, `apartment_type`, `apartment_amenities`, `major_requirements`, `apartment_defects`, `address_proof`, `timestamps`, `email`, `reset_token`, `token_expiration`, `unique_identifier`) VALUES
(10, 'Mr', 'Obadare', 'Ayokunle', 'ayostar', '$2y$10$3OQh6SufuDNx6/LQVaYlWes7TH28PEHa9nK5PbQQc.27cad8JQv3.', '2023-08-23', '+852 2527 6143', 'no1', 'upper flat', '2', '10', 'mybuilding', '200', 'obad street', 'HONG KONG ISLAND', 'Tai Koo shing', 'Homeowner', '11 – 30 %', 'One Bedroom', 'N/A', 'Balcony lighting', 'Absence of fire detector and emergency alert in design', 'uploads/MEE 102 +++.pdf', '2023-08-04 13:14:36', 'ayostarobad@gmail.com', NULL, NULL, '110'),
(11, 'Mr', 'Joy', 'Testimony', 'obadsam', '$2y$10$hXiTm4/SLI.ATRRG1SDg3.l.iUT8dgEtl4WxB24BzCsJhkGSvNK9C', '2023-08-24', '+852 2527 6143', 'no1', 'upper flat', '2', '10', 'mybuilding', '200', 'obad street', 'OUTLYING ISLANDS', 'Cheung Chau', 'Tenant', '51 – 70 %', 'Studio', 'Fully furnished kitchen, Fully furnished washing room, Wall painting/interior decoration', 'Built-in kitchen cabinet, Built-in microwave, Ventilation fan with heater', 'Structural design is not flexible for future renovation (e.g., alteration in layout plan), Water seepage, delaminated tiles, discolored tiles, and efflorescence', 'uploads/MTS 102 LEXTURE REVIEWED2.pdf', '2023-08-05 13:25:09', 'oludelevictor80@gmail.com', NULL, NULL, '111');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_registration`
--
ALTER TABLE `admin_registration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `admin_registration`
--
ALTER TABLE `admin_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
