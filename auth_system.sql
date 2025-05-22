-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2025 at 12:57 PM
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
-- Database: `auth_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `sha256_hash` varchar(64) DEFAULT NULL,
  `hmac_signature` text DEFAULT NULL,
  `digital_signature` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`document_id`, `title`, `description`, `file_path`, `file_type`, `file_size`, `sha256_hash`, `hmac_signature`, `digital_signature`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(51, 'Document from Admin', 'Hii', 'uploads/documents/682eff497ac38.pdf', 'application/pdf', 551485, 'acea905a19ac4fb39dc14233df1550f89c30f8b6c82fb46ff45e2084f6673d88', '87ead0a9ca1b27f6935885e96c65ecf52db2d9d2277a0e0259a428fa9c3088cd', 'GtHk7lCFB84oo7ioorByAAZlevV8rubsrwdO82BzpuHpx2btHs6Xf0qVcbfdkXXxz5VCn3AUJlD9h73lfP3DQVMuSQ9N+IzVhLfIduKWmIEjtOXBKVrXNbOXbu3arSXIJ5ImkILSB4q8ylDYAw1516qVquKej9vXEj4m8ownVzCCO7oTUM+XVrX00zI3owmeFBWz4pT5IB41JxgDhcttni2cy04D3lLgaMvFpAJXuUXYwuQ2bsCQfrkp8PYVw6aCHyzKsyFPFIu7TFXDdnhIaeIsHmBBLFpV4A+OWGDn/IY4OsRWmftnSIhPvdJ+w9SedUxhcMOsYGs/VESr0S70Yg==', 1, '2025-05-22 10:41:13', '2025-05-22 10:41:13');

-- --------------------------------------------------------

--
-- Table structure for table `document_shares`
--

CREATE TABLE `document_shares` (
  `share_id` int(11) NOT NULL,
  `document_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `permission_level` enum('read','write','admin') DEFAULT 'read',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_shares`
--

INSERT INTO `document_shares` (`share_id`, `document_id`, `user_id`, `permission_level`, `created_at`) VALUES
(43, 51, 34, 'read', '2025-05-22 10:41:13'),
(46, 51, 33, 'read', '2025-05-22 10:41:13');

-- --------------------------------------------------------

--
-- Table structure for table `document_signatures`
--

CREATE TABLE `document_signatures` (
  `signature_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `signature_data` text NOT NULL,
  `signed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `version_id` int(11) NOT NULL,
  `document_id` int(11) DEFAULT NULL,
  `version_number` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `changes_description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `encrypted_files`
--

CREATE TABLE `encrypted_files` (
  `file_id` int(11) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `encrypted_data` longblob NOT NULL,
  `encrypted_key` text NOT NULL,
  `file_hash` varchar(64) NOT NULL,
  `digital_signature` text NOT NULL,
  `iv` varchar(32) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files_with_signatures`
--

CREATE TABLE `files_with_signatures` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `content_type` varchar(50) DEFAULT NULL,
  `sha256_hash` varchar(64) DEFAULT NULL,
  `hmac_signature` text DEFAULT NULL,
  `digital_signature` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `auth_method` enum('manual','github','google','okta') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `ip_address`, `login_time`, `auth_method`) VALUES
(1, 1, '::1', '2025-05-18 13:38:27', 'manual'),
(4, 1, '::1', '2025-05-18 15:24:14', 'manual'),
(6, 1, '0.0.0.0', '2025-05-18 15:50:59', 'manual'),
(8, 1, '0.0.0.0', '2025-05-18 15:54:55', 'manual'),
(10, 1, '0.0.0.0', '2025-05-18 15:59:36', 'manual'),
(11, 1, '0.0.0.0', '2025-05-18 16:06:04', 'manual'),
(12, 1, '0.0.0.0', '2025-05-18 16:15:35', 'manual'),
(20, 36, '::1', '2025-05-22 10:44:39', 'github'),
(21, 36, '::1', '2025-05-22 10:51:12', 'github');

-- --------------------------------------------------------

--
-- Table structure for table `mfa_secrets`
--

CREATE TABLE `mfa_secrets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `secret_key` varchar(32) NOT NULL,
  `backup_codes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_name`, `description`, `created_at`) VALUES
(1, 'manage_users', 'Can create, edit, and delete users', '2025-05-18 11:15:41'),
(2, 'manage_roles', 'Can create, edit, and delete roles', '2025-05-18 11:15:41'),
(3, 'view_logs', 'Can view system logs', '2025-05-18 11:15:41'),
(4, 'manage_documents', 'Can create, edit, and delete any document', '2025-05-18 11:15:41'),
(5, 'upload_documents', 'Upload own documents', '2025-05-18 11:15:41'),
(6, 'download_documents', 'Can download any document', '2025-05-18 11:15:41'),
(7, 'sign_documents', 'Sign documents', '2025-05-18 11:15:41'),
(8, 'view_profile', 'View own profile', '2025-05-18 11:15:41'),
(9, 'edit_profile', 'Edit own profile', '2025-05-18 11:15:41'),
(10, 'view_users', 'Can view user list and details', '2025-05-18 11:15:41'),
(11, 'assign_roles', 'Can assign roles to users', '2025-05-18 11:15:41'),
(12, 'view_documents', 'Can view all documents', '2025-05-18 11:15:41'),
(13, 'delete_documents', 'Can delete any document', '2025-05-18 11:15:41'),
(14, 'view_roles', 'Can view role list and details', '2025-05-18 11:15:41'),
(15, 'assign_permissions', 'Can assign permissions to roles', '2025-05-18 11:15:41'),
(16, 'manage_system', 'Can manage system settings', '2025-05-18 11:15:41'),
(17, 'view_dashboard', 'Can view admin dashboard', '2025-05-18 11:15:41');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `created_at`) VALUES
(1, 'admin', '2025-05-18 11:15:41'),
(2, 'user', '2025-05-18 11:15:41');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 12);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 24 hour)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`, `created_at`, `expires_at`) VALUES
('4njgrmbcn1k2fb2556sorqlobm', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', NULL, '2025-05-21 21:24:51', '2025-05-21 21:24:51', '2025-05-22 21:24:51'),
('efhean8j62v7bkbjb2jcj7hefj', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', NULL, '2025-05-22 07:00:23', '2025-05-22 07:00:23', '2025-05-23 07:00:23'),
('isjtk3577jpkj6ssftgfhd7qfn', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', NULL, '2025-05-22 09:35:36', '2025-05-22 09:35:36', '2025-05-23 09:35:36'),
('j3og5vnekl1048m28uqpmckthb', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', NULL, '2025-05-22 09:56:26', '2025-05-22 09:56:26', '2025-05-23 09:56:26'),
('lpfbtiatr1tu2vf5hk9np3g9l1', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', NULL, '2025-05-21 17:52:59', '2025-05-21 17:43:55', '2025-05-22 17:43:55');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'admin_login', 'Admin user logged in', '::1', '2025-05-18 11:28:31'),
(2, NULL, 'upload', 'Uploaded document: demoooooo', '::1', '2025-05-18 14:57:00'),
(3, NULL, 'download', 'Downloaded document: demoooooo', '::1', '2025-05-18 14:57:03'),
(4, NULL, 'sign', 'Signed document ID: 1', '::1', '2025-05-18 14:57:19'),
(5, NULL, 'download', 'Downloaded document: demoooooo', '::1', '2025-05-18 15:09:20'),
(6, NULL, 'sign', 'Signed document ID: 1', '::1', '2025-05-18 15:10:16'),
(7, NULL, 'download', 'Downloaded document: demoooooo', '::1', '2025-05-18 15:13:03'),
(8, NULL, 'download', 'Downloaded document: demoooooo', '::1', '2025-05-18 15:33:05'),
(9, NULL, 'upload', 'Uploaded document: dddddddd', '::1', '2025-05-18 15:34:35'),
(10, NULL, 'download', 'Downloaded document: dddddddd', '::1', '2025-05-18 15:34:42'),
(11, NULL, 'sign', 'Signed document ID: 2', '::1', '2025-05-18 15:34:44'),
(12, NULL, 'download', 'Downloaded document: dddddddd', '::1', '2025-05-18 15:34:48'),
(13, NULL, 'upload', 'Uploaded document: dddddd', '::1', '2025-05-18 15:43:48'),
(14, NULL, 'download', 'Downloaded document: dddddd', '::1', '2025-05-18 15:43:55'),
(15, NULL, 'download', 'Downloaded document: dddddd', '::1', '2025-05-18 15:43:59'),
(16, NULL, 'sign', 'Signed document ID: 3', '::1', '2025-05-18 15:46:15'),
(17, NULL, 'upload', 'Uploaded document: nbbbbbbbb', '::1', '2025-05-18 15:46:28'),
(18, NULL, 'download', 'Downloaded document: nbbbbbbbb', '::1', '2025-05-18 15:46:33'),
(19, NULL, 'sign', 'Signed document ID: 4', '::1', '2025-05-18 15:46:36'),
(20, NULL, 'upload', 'Uploaded document: bvvvv', '::1', '2025-05-18 15:52:51'),
(21, NULL, 'download', 'Downloaded document: bvvvv', '::1', '2025-05-18 15:52:53'),
(22, NULL, 'sign', 'Signed document ID: 5', '::1', '2025-05-18 15:52:57'),
(23, NULL, 'upload', 'Uploaded and signed document: vvvvvv', '::1', '2025-05-18 16:08:47'),
(24, NULL, 'upload', 'Uploaded and signed document: kkkkkkkkkkkkkkko', '::1', '2025-05-18 16:09:15'),
(25, NULL, 'download', 'Downloaded and verified document: kkkkkkkkkkkkkkko', '::1', '2025-05-18 16:10:34'),
(26, NULL, 'download', 'Downloaded and verified document: kkkkkkkkkkkkkkko', '::1', '2025-05-18 16:10:34'),
(27, NULL, 'download', 'Downloaded and verified document: kkkkkkkkkkkkkkko', '::1', '2025-05-18 16:10:43'),
(28, NULL, 'download', 'Downloaded and verified document: kkkkkkkkkkkkkkko', '::1', '2025-05-18 16:10:43'),
(29, NULL, 'download', 'Downloaded and verified document: kkkkkkkkkkkkkkko', '::1', '2025-05-18 16:10:46'),
(30, NULL, 'download', 'Downloaded and verified document: kkkkkkkkkkkkkkko', '::1', '2025-05-18 16:10:46'),
(31, NULL, 'upload', 'Uploaded and signed document: mhjhjhjhjh', '::1', '2025-05-18 16:12:32'),
(32, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:12:35'),
(33, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:12:35'),
(34, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:14:21'),
(35, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:14:21'),
(36, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:14:22'),
(37, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:14:22'),
(38, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:14:26'),
(39, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:14:26'),
(40, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:14:27'),
(41, NULL, 'download', 'Downloaded and verified document: mhjhjhjhjh', '::1', '2025-05-18 16:14:27'),
(42, NULL, 'sign', 'Signed document ID: 8', '::1', '2025-05-18 16:17:03'),
(43, NULL, 'upload', 'Uploaded and signed document: vvcccccccv', '::1', '2025-05-18 16:22:13'),
(44, NULL, 'sign', 'Signed document ID: 9', '::1', '2025-05-18 16:22:25'),
(45, 1, 'delete_document', 'Deleted document: dddddddd (ID: 2)', '::1', '2025-05-18 16:28:09'),
(46, 1, 'delete_document', 'Deleted document: demoooooo (ID: 1)', '::1', '2025-05-18 16:28:14'),
(49, 1, 'upload', 'Uploaded and signed document: iam the admin ', '::1', '2025-05-21 12:28:08'),
(50, 1, 'download', 'Downloaded and verified document: شس', '::1', '2025-05-21 12:28:25'),
(51, 1, 'upload', 'Uploaded and signed document: as', '::1', '2025-05-21 12:33:08'),
(52, 1, 'upload', 'Uploaded and signed document: my name is adham', '::1', '2025-05-21 13:22:46'),
(53, 1, 'upload', 'Uploaded and signed document: serammm', '::1', '2025-05-21 13:23:31'),
(54, 1, 'edit_user', 'Updated user ID: 17', '::1', '2025-05-21 13:24:28'),
(55, 1, 'download', 'Downloaded and verified document: شس', '::1', '2025-05-21 13:33:35'),
(56, 1, 'upload', 'Uploaded and signed document: sd', '::1', '2025-05-21 13:34:03'),
(57, 1, 'edit_user', 'Updated user ID: 17', '::1', '2025-05-21 13:34:38'),
(67, 1, 'download', 'Downloaded and verified document: xc', '::1', '2025-05-21 13:42:46'),
(68, 1, 'upload', 'Uploaded and signed document: omat admin', '::1', '2025-05-21 13:46:22'),
(69, 1, 'delete_document', 'Document deleted: omat admin', '::1', '2025-05-21 13:48:40'),
(70, 1, 'delete_document', 'Deleted document: omat admin (ID: 18)', '::1', '2025-05-21 13:48:40'),
(71, 1, 'delete_document', 'Document deleted: xc', '::1', '2025-05-21 13:48:43'),
(72, 1, 'delete_document', 'Deleted document: xc (ID: 17)', '::1', '2025-05-21 13:48:43'),
(73, 1, 'upload', 'Uploaded and signed document: kareem', '::1', '2025-05-21 13:49:08'),
(74, 1, 'download', 'Downloaded and verified document: kareemahmed', '::1', '2025-05-21 13:50:42'),
(75, 1, 'download', 'Downloaded and verified document: kareemahmed', '::1', '2025-05-21 13:50:56'),
(76, 1, 'upload', 'Uploaded and signed document: ibrahim', '::1', '2025-05-21 13:51:27'),
(77, 1, 'download', 'Downloaded and verified document: ibrahim', '::1', '2025-05-21 13:51:43'),
(81, 1, 'upload', 'Uploaded and signed document: as', '::1', '2025-05-21 13:59:39'),
(82, 1, 'delete_document', 'Document deleted: as', '::1', '2025-05-21 14:01:16'),
(83, 1, 'delete_document', 'Deleted document: as (ID: 21)', '::1', '2025-05-21 14:01:16'),
(84, 1, 'delete_document', 'Document deleted: ibrahim', '::1', '2025-05-21 14:01:21'),
(85, 1, 'delete_document', 'Deleted document: ibrahim (ID: 20)', '::1', '2025-05-21 14:01:21'),
(86, 1, 'upload', 'Uploaded and signed document: sd', '::1', '2025-05-21 14:19:40'),
(96, 1, 'download', 'Downloaded and verified document: iam the gon', '::1', '2025-05-21 17:41:45'),
(97, 1, 'download', 'Downloaded and verified document: iam the gon', '::1', '2025-05-21 17:42:04'),
(98, 1, 'upload', 'Uploaded and signed document: as', '::1', '2025-05-21 17:43:06'),
(99, 1, 'upload', 'Uploaded and signed document: sss', '::1', '2025-05-21 17:45:26'),
(100, 1, 'download', 'Downloaded and verified document: iam the gon', '::1', '2025-05-21 17:46:16'),
(106, 1, 'download', 'Downloaded and verified document: iam the gon', '::1', '2025-05-21 18:44:01'),
(107, 1, 'upload', 'Uploaded and signed document: manager', '::1', '2025-05-21 18:44:21'),
(108, 1, 'upload', 'Uploaded and signed document: dasa', '::1', '2025-05-21 18:45:25'),
(109, 1, 'download', 'Downloaded and verified document: dasa', '::1', '2025-05-21 18:46:14'),
(110, 1, 'delete_document', 'Document deleted: dasa', '::1', '2025-05-21 18:48:40'),
(111, 1, 'delete_document', 'Deleted document: dasa (ID: 29)', '::1', '2025-05-21 18:48:40'),
(112, 1, 'delete_document', 'Document deleted: manager', '::1', '2025-05-21 18:48:46'),
(113, 1, 'delete_document', 'Deleted document: manager (ID: 28)', '::1', '2025-05-21 18:48:46'),
(114, 1, 'upload', 'Uploaded and signed document: serassss', '::1', '2025-05-21 18:52:01'),
(115, 1, 'download', 'Downloaded and verified document: serassss', '::1', '2025-05-21 18:52:52'),
(116, 1, 'download', 'Downloaded and verified document: serassss', '::1', '2025-05-21 18:53:06'),
(117, 1, 'upload', 'Uploaded and signed document: sdasasas', '::1', '2025-05-21 18:53:22'),
(118, 1, 'upload', 'Uploaded and signed document: num5', '::1', '2025-05-21 19:08:38'),
(119, 1, 'download', 'Downloaded and verified document: num5', '::1', '2025-05-21 19:08:53'),
(120, 1, 'upload', 'Uploaded and signed document: das', '::1', '2025-05-21 19:18:51'),
(121, 1, 'upload', 'Uploaded and signed document: asa', '::1', '2025-05-21 19:19:33'),
(122, 1, 'download', 'Downloaded and verified document: asa', '::1', '2025-05-21 19:19:49'),
(123, 1, 'download', 'Downloaded and verified document: asa', '::1', '2025-05-21 19:20:59'),
(124, 1, 'upload', 'Uploaded and signed document: mares', '::1', '2025-05-21 19:21:38'),
(125, 1, 'download', 'Downloaded and verified document: mares', '::1', '2025-05-21 19:21:47'),
(126, 1, 'download', 'Downloaded and verified document: mares', '::1', '2025-05-21 19:25:32'),
(127, 1, 'delete_document', 'Document deleted: mares', '::1', '2025-05-21 19:25:39'),
(128, 1, 'delete_document', 'Deleted document: mares (ID: 35)', '::1', '2025-05-21 19:25:39'),
(129, 1, 'delete_document', 'Document deleted: asa', '::1', '2025-05-21 19:25:43'),
(130, 1, 'delete_document', 'Deleted document: asa (ID: 34)', '::1', '2025-05-21 19:25:43'),
(131, 1, 'delete_document', 'Document deleted: das', '::1', '2025-05-21 19:25:47'),
(132, 1, 'delete_document', 'Deleted document: das (ID: 33)', '::1', '2025-05-21 19:25:47'),
(133, 1, 'upload', 'Uploaded and signed document: ssssssssa', '::1', '2025-05-21 19:25:58'),
(134, 1, 'download', 'Downloaded and verified document: ssssssssa', '::1', '2025-05-21 19:26:02'),
(135, 1, 'download', 'Downloaded and verified document: num5', '::1', '2025-05-21 19:26:37'),
(136, 1, 'upload', 'Uploaded and signed document: adhama', '::1', '2025-05-21 19:36:59'),
(137, 1, 'download', 'Downloaded and verified document: adhama', '::1', '2025-05-21 19:37:08'),
(138, 1, 'upload', 'Uploaded and signed document: hello', '::1', '2025-05-21 19:39:51'),
(139, 1, 'download', 'Downloaded and verified document: hello', '::1', '2025-05-21 19:40:09'),
(140, 1, 'delete_document', 'Document deleted: hello', '::1', '2025-05-21 19:49:55'),
(141, 1, 'delete_document', 'Deleted document: hello (ID: 38)', '::1', '2025-05-21 19:49:55'),
(142, 1, 'upload', 'Uploaded and signed document: mohmmaed', '::1', '2025-05-21 19:50:30'),
(143, 1, 'download', 'Downloaded and verified document: mohmmaed', '::1', '2025-05-21 19:51:16'),
(144, 1, 'upload', 'Uploaded and signed document: hamadda', '::1', '2025-05-21 19:55:31'),
(145, 1, 'download', 'Downloaded and verified document: hamadda', '::1', '2025-05-21 19:56:13'),
(155, 1, 'upload', 'Uploaded and signed document: ssssss', '::1', '2025-05-21 20:07:58'),
(156, 1, 'download', 'Downloaded and verified document: ssssss', '::1', '2025-05-21 20:08:01'),
(171, 1, 'delete', 'Deleted document: ssssss', NULL, '2025-05-21 22:13:51'),
(172, 1, 'download', 'Downloaded document ID: 46', NULL, '2025-05-21 22:14:02'),
(173, 1, 'download', 'Downloaded document ID: 47', NULL, '2025-05-21 22:14:04'),
(174, 1, 'delete_user', 'Deleted user ID: 14', '::1', '2025-05-21 22:28:24'),
(175, 1, 'delete_user', 'Deleted user ID: 15', '::1', '2025-05-21 22:28:31'),
(176, 1, 'edit_user', 'Updated user ID: 5', '::1', '2025-05-21 22:29:00'),
(177, 1, 'download', 'Downloaded document ID: 47', NULL, '2025-05-21 22:29:29'),
(178, 1, 'download', 'Downloaded document ID: 46', NULL, '2025-05-21 22:29:33'),
(179, 1, 'download', 'Downloaded document ID: 47', NULL, '2025-05-21 22:29:49'),
(180, 1, 'download', 'Downloaded document ID: 46', NULL, '2025-05-21 22:29:53'),
(181, 1, 'delete', 'Deleted document ID: 46', '::1', '2025-05-21 22:36:14'),
(182, 1, 'edit_user', 'Updated user ID: 27', '::1', '2025-05-21 22:39:41'),
(183, 1, 'edit_user', 'Updated user ID: 26', '::1', '2025-05-21 22:39:48'),
(190, 1, 'upload', 'Uploaded and signed document: hamo', NULL, '2025-05-22 08:01:41'),
(191, 1, 'edit_user', 'Updated user ID: 29', '::1', '2025-05-22 10:25:12'),
(192, 1, 'delete_user', 'Deleted user ID: 32', '::1', '2025-05-22 10:25:41'),
(193, 1, 'delete', 'Deleted document: hamo', '::1', '2025-05-22 10:33:34'),
(194, 1, 'edit_user', 'Updated user ID: 28', '::1', '2025-05-22 10:36:34'),
(195, 1, 'delete_user', 'Deleted user ID: 29', '::1', '2025-05-22 10:38:21'),
(196, 1, 'delete_user', 'Deleted user ID: 16', '::1', '2025-05-22 10:38:28'),
(197, 1, 'delete_user', 'Deleted user ID: 5', '::1', '2025-05-22 10:38:34'),
(198, 1, 'delete_user', 'Deleted user ID: 17', '::1', '2025-05-22 10:38:42'),
(199, 1, 'delete_user', 'Deleted user ID: 10', '::1', '2025-05-22 10:38:47'),
(200, 1, 'delete_user', 'Deleted user ID: 20', '::1', '2025-05-22 10:38:55'),
(201, 1, 'delete_user', 'Deleted user ID: 18', '::1', '2025-05-22 10:39:00'),
(202, 1, 'delete_user', 'Deleted user ID: 21', '::1', '2025-05-22 10:39:05'),
(203, 1, 'delete_user', 'Deleted user ID: 22', '::1', '2025-05-22 10:39:10'),
(204, 1, 'delete_user', 'Deleted user ID: 24', '::1', '2025-05-22 10:39:15'),
(205, 1, 'delete_user', 'Deleted user ID: 25', '::1', '2025-05-22 10:39:20'),
(206, 1, 'delete_user', 'Deleted user ID: 28', '::1', '2025-05-22 10:39:26'),
(207, 1, 'delete_user', 'Deleted user ID: 23', '::1', '2025-05-22 10:39:29'),
(208, 1, 'edit_user', 'Updated user ID: 33', '::1', '2025-05-22 10:40:13'),
(209, 1, 'upload', 'Uploaded and signed document: Document from Admin', NULL, '2025-05-22 10:41:13'),
(210, 1, 'download', 'Downloaded document ID: 51', NULL, '2025-05-22 10:41:19'),
(211, 33, 'upload', 'Uploaded and signed document: Document From Omar', NULL, '2025-05-22 10:42:18'),
(212, 33, 'download', 'Downloaded document ID: 52', NULL, '2025-05-22 10:42:36'),
(213, 33, 'download', 'Downloaded document ID: 51', NULL, '2025-05-22 10:42:38'),
(214, 33, 'delete', 'Deleted document: Document From Omar', NULL, '2025-05-22 10:42:41'),
(215, 33, 'upload', 'Uploaded and signed document: Document From Omar', NULL, '2025-05-22 10:43:07'),
(216, 37, 'upload', 'Uploaded and signed document: fffff', NULL, '2025-05-22 10:54:38'),
(217, 37, 'delete', 'Deleted document: fffff', NULL, '2025-05-22 10:54:43'),
(218, 36, 'upload', 'Uploaded and signed document: wrr', NULL, '2025-05-22 10:55:03'),
(219, 36, 'delete', 'Deleted document: wrr', NULL, '2025-05-22 10:55:18'),
(220, 35, 'upload', 'Uploaded and signed document: edfeeer', NULL, '2025-05-22 10:55:47'),
(221, 35, 'delete', 'Deleted document: edfeeer', NULL, '2025-05-22 10:56:04'),
(222, 1, 'delete', 'Deleted document: Document From Omar', '::1', '2025-05-22 10:56:39'),
(223, 1, 'edit_user', 'Updated user ID: 38', '::1', '2025-05-22 10:57:04'),
(224, 1, 'delete_user', 'Deleted user ID: 38', '::1', '2025-05-22 10:57:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `github_id` varchar(255) DEFAULT NULL,
  `okta_id` varchar(255) DEFAULT NULL,
  `auth_method` enum('manual','github','google','okta') NOT NULL DEFAULT 'manual',
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `mfa_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `google_id`, `github_id`, `okta_id`, `auth_method`, `profile_picture`, `is_active`, `is_verified`, `verification_token`, `status`, `mfa_enabled`, `created_at`, `updated_at`, `role_id`) VALUES
(1, 'System Admin', 'admin@system.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 'manual', NULL, 1, 1, NULL, 'active', 0, '2025-05-18 11:15:41', '2025-05-18 11:15:41', 1),
(33, 'Omar', 'omar@gmail.com', '$2y$10$wyUyeF2yWb0XAM1/wY3NX.OHTHksLAgAT5lFBAJ4w32E.FSf.KvFi', NULL, NULL, NULL, 'manual', NULL, 1, 0, NULL, 'active', 0, '2025-05-22 10:39:50', '2025-05-22 10:40:13', 2),
(34, 'Omar Admin', 'omar.admin@gmail.com', '$2y$10$hwXqL1HzDtCz7RIUQrA4neC6.7/sxY0ARm15E57CZ5vn0F7gS3ITe', NULL, NULL, NULL, 'manual', NULL, 1, 0, NULL, 'active', 0, '2025-05-22 10:40:46', '2025-05-22 10:40:46', 1),
(35, 'omarmagdyyy14_85ed', 'omarmagdyyy14@gmail.com', '', '105127838779589286448', NULL, NULL, 'google', 'https://lh3.googleusercontent.com/a/ACg8ocInhh17awnDz4hWj5NymUlOKGYTbplsCsGsIHF9aPwlIbRczA=s96-c', 1, 0, NULL, 'active', 0, '2025-05-22 10:43:28', '2025-05-22 10:55:59', NULL),
(36, 'Omar3443', 'omar.magdy3443728@gmail.com', '', NULL, '139278558', NULL, 'github', NULL, 1, 0, NULL, 'active', 0, '2025-05-22 10:44:39', '2025-05-22 10:51:11', 2),
(37, 'Omar Magdy', 'omarr.elkhazendar@gmail.com', '', NULL, NULL, 'google-oauth2|116046072616793089377', 'okta', NULL, 1, 0, NULL, 'active', 0, '2025-05-22 10:45:00', '2025-05-22 10:54:24', 2);

-- --------------------------------------------------------

--
-- Table structure for table `user_keys`
--

CREATE TABLE `user_keys` (
  `key_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `public_key` text NOT NULL,
  `private_key_encrypted` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_keys`
--

INSERT INTO `user_keys` (`key_id`, `user_id`, `public_key`, `private_key_encrypted`, `created_at`, `updated_at`) VALUES
(3, 1, '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0O3x12yQn6q+Eu2qbMJc\nqwT9LvCJBG7mgTxtsBLU04snH1XGDTi40OyZMUF5x1TGAeQLtdOxpZnUKOAFXeIr\n152yaAibAsQCJ9IUZ1T+1BcpEMcG0E83ndRzi+4stUSkfovYqOeOTbSnoxJ82Ndm\nfInQiseG/+Pky0PVD31bBpHGJ6K6Ow+oKZo/hW4BoJ3z6/YZbuFmhYKJSQzPXCxE\nuweUCb8S4OPbwMlgatCPfA+cpQd2eLzs7X8IIFcNM/6tS4RNfEMb8KbAiQmbzZON\nQrF9jxr3OpHBWm2ee7bu1FoLVOXcBw/AI4NtNcz1tMHQOezmW/gbK0A513PHwJqO\nHQIDAQAB\n-----END PUBLIC KEY-----\n', 'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2QUlCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktZd2dnU2lBZ0VBQW9JQkFRRFE3ZkhYYkpDZnFyNFMKN2Fwc3dseXJCUDB1OElrRWJ1YUJQRzJ3RXRUVGl5Y2ZWY1lOT0xqUTdKa3hRWG5IVk1ZQjVBdTEwN0dsbWRRbwo0QVZkNGl2WG5iSm9DSnNDeEFJbjBoUm5WUDdVRnlrUXh3YlFUemVkMUhPTDdpeTFSS1IraTlpbzU0NU50S2VqCkVuelkxMlo4aWRDS3g0Yi80K1RMUTlVUGZWc0drY1lub3JvN0Q2Z3BtaitGYmdHZ25mUHI5aGx1NFdhRmdvbEoKRE05Y0xFUzdCNVFKdnhMZzQ5dkF5V0JxMEk5OEQ1eWxCM1o0dk96dGZ3Z2dWdzB6L3ExTGhFMThReHZ3cHNDSgpDWnZOazQxQ3NYMlBHdmM2a2NGYWJaNTd0dTdVV2d0VTVkd0hEOEFqZzIwMXpQVzB3ZEE1N09aYitCc3JRRG5YCmM4ZkFtbzRkQWdNQkFBRUNnZ0VBRUxjY3ZHYTE2bVJMYVRIRkN3Y0F5WGp3ZFo4dVl5R3BST2tzQUdSOVRRQnEKekoyODV1VmxHZWYzK0tJYnAxZ3ZzRXFKcWs0cHZnMUExVlZZOEpJdC9rWGlWbUxyM2V5SEhKNzQzV3lHSjd0RApvSkUveVE0eGJhbm41YWZVUVZ0Z3lyUVBEVEpiWnZtZldOTmJsRENsaFN5MGQ4bnZVYzB1aXBtZWMrc01TNG5jCmRBdXBFN1QraGI1YS9pM1FvQVdnQ3BmelFqdTlsREg4ay9SekV0K2RnV2o1RjNsZUdVUkpaZmxtV3dyaGhnUHkKYUlJU05xTWtCNUptN0VvNTdSSVFyY2d3MWhxOTB4L25UOU5XN3VkS3A2RDVzRDc3RDd4Tm5pOWVtYjdUSUNTZQpid05aY0p3NDRxc3p5bnNjMHpIa21sSnZ1S3FLVTZLbDJJYVVhMEgzeVFLQmdRRCtNYVY4RlNjbDh2aFl3R1lFCjAyQzRmb2pZY0FqOWhOOU1UbDF6VXFGdWVTdTl3eVREbm9sZHhxbXk3NmNHWWs0UFVUWkJabXhRVnJXdkxrTnQKZnA5dzVoQ1hYWEJLL3EybTFkeWovektXUjYxZVN4ampZQTN0WkF4RDJHdTkyYlRXNit6dDFGUStWeW5KbGh4RwpzNU9TcGx6RVNqTUhvM2xaekJwNjBxcFRkUUtCZ1FEU2FmZDV1SDBZYVRKRHRLd1BhWEVJTXg1SmhNVi84T2FVCkdQZVlIK3RTQnM1c0h3NklmK1B3RGkwV25adlV0ZTk1czd2N2I2QS9zSTZja2hSOS9pVkg4MmRtM1JDbjlxTUoKckpueHVsTkVQOTFnWC9DVlUzajFuWXRkcncxSnRkb0VYMURSTXNMYStEb2Y4bWRsSVV2dmtZanQ2RTRONVE3QwpHemh4dHU1RENRS0JnRVZBdzJRbDR5K01uYXFZYUhNQ0g4VEQvSVp6SjQvaVptL0VuZU13YlBqTEhHYXJ4dFZnCjJPM3FsUXRDRFpCSXNobXNONHJqMjdpYmx3NHVIWUswVDc0VGdBdXRFazNzU3VVOE9NdVpXRy9uQVUzQ1NmcVEKamRyU09pRTEwa3k4Qm0vVGdRNWVuY0VLUUVGQ20xdUx5elhXQzBvVEtRbjR1TFFGVG1XT0JZZU5Bb0dBY1lMZgpFZGc0RENJOERwQjZQZm81RXg1WW1YcTUvUkpkb2NseVVLdnZqQm5GNUVoQTA5eUNmKzkxM1h4N0k1NWVxWDRQCnpFM2sybGNLS0djYURQbzg3SHJmN25zVjBEYmFyNDVnb2lwTlgwVkF6UXdVd3NuOE9DeWNrWmF0Q1hYRVBwV1oKQ0xKa3NqSTFVU1M1S0xKMHY3SDRkN2ZaMjk4VlBodm5NRmxxWWRrQ2dZQjNzOTBnbysvekxtdWNaNUFmb21JagppU2RBUWg0ZGNFVW1VVklYd2dHdUY2YVF5UWRFYkI1Z0RvbVhtTzZNRXpNV0FmMWszeGpPUnNrbE9XVVAxRlFKClVEanZvcC9BRGF2MGNZczNrdFZvbkVicmMzVlVtK0p3eEFZMFBBUVNOdjZIbnpCSGlqTElrdTl3ZlVLTGdjcmoKdndab2p5OEQ4U0k4SGhaNXlLTXZIUT09Ci0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K', '2025-05-21 12:28:08', '2025-05-21 12:28:08'),
(10, 33, '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1o1JsCUrEPPcrNqtyJst\nJUPlXhmxe+hIDoi0h1vUbhObBCDbLvH7aPqrJyQ4ekufIXcN5nnFVmXcKAV4mqeU\nh7vl8p5knsetWpMBru2Gbf4wAMiFzG77emhTxVZXqy1JrHD2lz3TA1bn4OEGgDjP\niznrBNeXyvv/hGp6BJrXphpaBfWAnuU/RewLevtzabTZ+CQPqgQLKnLphFGRg5uK\nuzZ48qDYywKC8gwcmbpjwEaTyNP64BI9sRxYeWskfF8orztCFFf0ELNCFKXMAOFa\nEYGcfTap8JK/8g3KNkiUnr+B+K8T+XWTbSsgt64nPw+g6jdBy5lOuOJ14jABuPFS\njQIDAQAB\n-----END PUBLIC KEY-----\n', 'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2Z0lCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktnd2dnU2tBZ0VBQW9JQkFRRFdqVW13SlNzUTg5eXMKMnEzSW15MGxRK1ZlR2JGNzZFZ09pTFNIVzlSdUU1c0VJTnN1OGZ0bytxc25KRGg2UzU4aGR3M21lY1ZXWmR3bwpCWGlhcDVTSHUrWHlubVNleDYxYWt3R3U3WVp0L2pBQXlJWE1idnQ2YUZQRlZsZXJMVW1zY1BhWFBkTURWdWZnCjRRYUFPTStMT2VzRTE1ZksrLytFYW5vRW10ZW1HbG9GOVlDZTVUOUY3QXQ2KzNOcHRObjRKQStxQkFzcWN1bUUKVVpHRG00cTdObmp5b05qTEFvTHlEQnladW1QQVJwUEkwL3JnRWoyeEhGaDVheVI4WHlpdk8wSVVWL1FRczBJVQpwY3dBNFZvUmdaeDlOcW53a3IveURjbzJTSlNldjRINHJ4UDVkWk50S3lDM3JpYy9ENkRxTjBITG1VNjQ0blhpCk1BRzQ4VktOQWdNQkFBRUNnZ0VBRFVKdm1LZC9aUjJSR0l5dU8wemR5aVVMVHBpN1o0czRNcysvaW5NQ0txaGoKQnVzSjZMbVRQazVwbC9KczNDQUN6bVJuYzBRNHppbGU1UWxMaGhGUHhHQzRLaG1nWWRDNDFiVnFuL2MxZk9ROQo3cnAxekdmYWVscnF2SElrMm1NaTM3bWVmOFBXSCtsMXpLZlRpVWFaaXdMeUU1dElXZ2JmYUgxVkc2WS9LQmh4CldvbDgvWGZlYlQwQk5xSGdBbXFueWV2aTgrRzZKQkpzWlZEQTFkd2FKckVwSXJrK3dWdHZVU3I1TmNrK2JRMkEKbGdEZjBMcDJycThEWnVHZFNWTXRUNmRma1pFVzllV1dPZzZMOVdYU2doeG5XSCtIRkFvT2Q1NlhFaGlYUEgwWQo0UFdBeXFqcXF1TVpmS2h1bkNWQ20xQnVCTWRGRDZSaVdoZEhQTEEyT1FLQmdRRHFtenFrTWJtQTBIcERpTGlhCmZWcmtsc1VLTW54K1hYb3pSVVVHd21Ocm9hWlh0OGZCa2Q3Umt0a1R4aVhtNFJDZWozeTlZcGwyZEtkRnZETmEKd0w3S21tcXhFRU5GazFDQ2FBaG1IV2ZKeGhOUnpTUlovQ0FSSk9seHZQdW9xSHVCUFlWYUgyRTBmSkRTT3BPLwpzT2xWTFhhU3RFOG5EVk9Pb2Zkd290TTNHUUtCZ1FEcUhlV3llZXB4YTNKS0xHL0hJTTFMNDRCT3FJVEFiOVZkCnNnYnNSa0dGem9pYWQxd0Y4aG1GZ0J4K1UyYTJsNElVcXlsVkgyZFd6Sm9iUTBiYUgzWnI5emtBRXBla2hSZ0kKRFVWbUJMd3NRbllOa3VUbzZMc0ZpbGh1WE9rcFpLWkw2VHRZT0U1MzB1VVdWR2RUTnI1eXd5WmtsdTZFTlpKcApZTE5hNzlGcGxRS0JnUUM4Z29hWU1LeFI2VEtVMldNY0grWE5ENmk4RE4wajNKQzRhY3lSSmRrbFB2cWVPVG4xCk8vVENpVENNUndkbGdTc05mVlpLQnRRdzdvTUo4Yk9DRktZNXZlSkU3RUJodzRGSmg3Wlg5d0RTaktveGJKanQKQlQzdkNLM2JpbkxjanFUT3NGUmFtaVJOUEZwUjBtZXQrOFZCZ0FwcXp4OWF6Y1o2TVlMVnFyLzA0UUtCZ1FDaApqWS83NFBWTzN2NDNBUU1CUGpsc3JNalZmcVJjeHM5Tm9KUDJaMlMvbVEzYXlaTE81NG1FL2lxcllaSDNYZ1Y5CmpiOHY1a1BCT2Q3K0FTYmJUZXJDQmc5blpXMFBNbWlxNWIyRjhLNkpQRE9LbWxzci9hMVEzVzY0ZVpUTDQ1QjkKd2F3WjdJRDVYcWFIQ2lkaTNOdi9CWHR3M2xZcEZXNjdHT2VyVU9JSklRS0JnQzB1UGxmYmdQUVZUejBJSmduaQpBdzFBNHZBbmJjTWJySXh3TTFaZk43WVJQRGhQempTSHFJVFExYXFQK3YvRVEvT1hPMzhJSXdmN1JJMVI5b29ECkxkN3ljUXVGRmVrOGxrbzBVZDBwMFBxTTNoUmlCeTBoT3MvSmowWnFBOEdrMlNzZWMwVllzVnMxK2Q4Zk9NSkMKdDZHZ1d1MXNsaWhEWXZIbEJsd2pkc3RNCi0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K', '2025-05-22 10:42:18', '2025-05-22 10:42:18'),
(11, 36, '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAm4Oa92mXhd//o1R+2NkU\nsODJSkKqrZc368+1aIXQOYG+gk4dq7n2qbSn1aOmvZ7CoOkbd8mi8yFCtxXr70Yk\nXVwEVbS48bceoZOIK269SVinL7NlA6dZDTag6n+a0ym41kFvbpLOIdPIed8dyRry\n1fhcvU4Zg1xwUHom4VrckSZ3CYspbMTVhJhwUS1pRsPiiBvxdze7UzxFaoiGFBtC\nb2fnOPPyt1lU1AiQ6Ewmy3bk1XXLzvbCEzS0seit+YMikgjXv5Hddj5l4O9UDFli\nMQ+nzQXlaTsfcALNoafwHq7vGFe3GnB4+BARu6qAi+AlDeTejl8v365IhmD0DEv0\njwIDAQAB\n-----END PUBLIC KEY-----\n', 'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2UUlCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktjd2dnU2pBZ0VBQW9JQkFRQ2JnNXIzYVplRjMvK2oKVkg3WTJSU3c0TWxLUXFxdGx6ZnJ6N1ZvaGRBNWdiNkNUaDJydWZhcHRLZlZvNmE5bnNLZzZSdDN5YUx6SVVLMwpGZXZ2UmlSZFhBUlZ0TGp4dHg2aGs0Z3JicjFKV0tjdnMyVURwMWtOTnFEcWY1clRLYmpXUVc5dWtzNGgwOGg1CjN4M0pHdkxWK0Z5OVRobURYSEJRZWliaFd0eVJKbmNKaXlsc3hOV0VtSEJSTFdsR3crS0lHL0YzTjd0VFBFVnEKaUlZVUcwSnZaK2M0OC9LM1dWVFVDSkRvVENiTGR1VFZkY3ZPOXNJVE5MU3g2SzM1Z3lLU0NOZS9rZDEyUG1YZwo3MVFNV1dJeEQ2Zk5CZVZwT3g5d0FzMmhwL0FlcnU4WVY3Y2FjSGo0RUJHN3FvQ0w0Q1VONU42T1h5L2Zya2lHCllQUU1TL1NQQWdNQkFBRUNnZ0VBQmd1R0ZSTDJNRjZHTGQrbGRLRnZrL2FRN0lHTjUrU0dhSFN3L1FpaWtQR3gKcVpnR3dEdWcwblR5eHpPdTBoVWZSS2o3Qjl0L0tZdEQ4Q0Q2aEZrS0pkVGpXRCtIcHVHeTM1My8vNHcwOEdweApTVUhrYmRGdC9Uei80VTJuZzRqT0pnbnlIbTYzYzRvcmMwMVFkNVpFRlR3cndySUwvTmExaVBOT21wanhGUFZGCnlDMUVwT3ZMSi9ZUlZSUzNTUjlsaDdReHRmbENLTWdrU3A3VWJzWkg0V0c2cVRWUjlLQWdTb3RzY2VFb2poWXcKSEozaGVnOWZZdmt3RUdEc09mUHVxVmhubTBOMjVqZjdoLzdBS3VHaFFiaUFKdkliL2ovZGJ6aG4vb1hydDJIeQoxNWcwcGlaQXkvb05mbTFuQWdTdk5vZnprOThvK29ESVpkWXhXVVVTcVFLQmdRRFIwdTloeGYvd0tHNmVkK2ZQCnhxMVdYOS9hY01vdEFZQkJIaEVkNVczcnNQemp1OWVLS1MvYWxZbzA1bzI0UFM1NkVxbjBjQVM1SlV6N2tDaHcKcmZUR2x3M0Fic0dEVDJnODlMQk9hdklGL0pVZXRIeU1uZVBzSU1NYzdTWUg3ajMzUHVFWkxnN0VJY205S05FdgpVbHlSWHR4SEIxR29YaU9WNzM5QUFHZzJGd0tCZ1FDOXZQV2k5QTErSVdkdzlmVUUvY2lhQThqSkUrV1VKTWpzCko1TDhmdjk1Y20raHhJL09OUVRCclFNQ0ZtSmprK0xORDNpdEJSODE5dkVkZys2QlBVVTlwNUVnNVVnZTVPQlYKcExmL0ZkZXg0MmcxY1ZnUDgrY2loT0JuVGNPbmNWZDRuN29GaXplT1UzTjBJVFJSYndHRXlobHB4WldiMXNnbwpXam4zT215NFNRS0JnR24xRWl1V3VFdEhyNnpZc2poTGhTY1VIVkFMR3gybEgyWkN6N1FBTXdVTmlIZGNXVmtNCnVYeXhmV3gvYk51NEVhdFZsM3Uzd2JyWmFrbXpINmpmUDdlMWVoMU9FT3pGZ2NjZWJaWDhEYUlXVGh1R1cwc0wKUkpqdVpubEtES1YrbXM0cmM3S3Fmb3h1MzVobGIxVnRTdytpRHF6VHRsbkd5RU9rQU9lS0RSWWJBb0dCQUo1VgpxNTVhajAvc1RFZ2wyRnhiOUNVaWQxSGxlcllQcEdQOStsa2ZHYzZkUXE3Nk93OVhpeXFjV0dCTGtidUxVNzdQCjVHSnVYY2RJMVpsTjJhQ2NJc1g2cWMrTHhvMlJiZXZLVTRsWkR2QngxeXFSOVcwS21wMWh5V29ycU5SNGJ3aEkKTXBJaHhURE9UeEJnRFNyUHViSzRmRkNhQStsU2FTSFRlWC9vajRXUkFvR0FiK0Y3ZlovYUFYRFNrZ3pGeEdPNQpmQzRhK1QwQ1B1ZlE2YWcwbnRLajExYkhBbTZYVDZ6bEFQcmk0TVZycjQyN25UdzBUMzVEazM3ODZ2eDN6MVlMCk1uZDJRM2Z1RHdDZldkdlF4TDNMMmVhak9uMmdTL2JLRUM0MWJnU21BdU15NjFoN1Vtdkc5QTZOa2xHNit3cjkKb0ZXd0JqUzFUZ3k1REkzQjFEdDBsam89Ci0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K', '2025-05-22 10:52:36', '2025-05-22 10:52:36'),
(12, 37, '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA+6JGmy5HdnxWkALjYbH1\n8m+T2qjD51XtSiLDlNEll8t/0kkeMvVXe4/yfd3wd+yJUVugjDdaIZe6SaTVj1Fm\nX/sBD/WUOPfAu/bMKwD2LZ6i2d+z/CNBa7EibW6hVP4qQ6/nhxSPaK5KOcj5TrVq\nQvVUNDam13Og3yQ2QTfc6ZmoprfEZziWnq0yD/Mk4AGhjRnty+my+iF0dBsTYheM\nyyRUZb/b9Ro/L6TW/ogbrAHmpX+75xpadgi8y9je2WxT2P5TCNyP5lQHwtgFXyFy\nduj3TCCq2wcR/2aoZbCfwpKWUF1W68fqiAMl99IaFHEo4j413yDVuan3tBKHqI0C\nOwIDAQAB\n-----END PUBLIC KEY-----\n', 'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2Z0lCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktnd2dnU2tBZ0VBQW9JQkFRRDdva2FiTGtkMmZGYVEKQXVOaHNmWHliNVBhcU1QblZlMUtJc09VMFNXWHkzL1NTUjR5OVZkN2ovSjkzZkIzN0lsUlc2Q01OMW9obDdwSgpwTldQVVdaZit3RVA5WlE0OThDNzlzd3JBUFl0bnFMWjM3UDhJMEZyc1NKdGJxRlUvaXBEcitlSEZJOW9ya281CnlQbE90V3BDOVZRME5xYlhjNkRmSkRaQk45enBtYWltdDhSbk9KYWVyVElQOHlUZ0FhR05HZTNMNmJMNklYUjAKR3hOaUY0ekxKRlJsdjl2MUdqOHZwTmIraUJ1c0FlYWxmN3ZuR2xwMkNMekwyTjdaYkZQWS9sTUkzSS9tVkFmQwoyQVZmSVhKMjZQZE1JS3JiQnhIL1pxaGxzSi9Da3BaUVhWYnJ4K3FJQXlYMzBob1VjU2ppUGpYZklOVzVxZmUwCkVvZW9qUUk3QWdNQkFBRUNnZ0VBQzlYaG9Vbm5kWlM2azVKM05IQ2s0MDdKTXdjdUU3THdjc1M0MDBhVkVKTXoKUTJaS0E5YVRVMDM5ZDFzWmhPSjh3UFZnMDBlRk4wQkdORkdLOCs4OXMvVC80MHJIT25pNHVYNk5zdFljUGlBUQpnOVBvU01zMXVBV0FXNHBSRDBqWEh4RG1nRzJBbzBsVFYyMXpBSGM2OU1SS1BoeDE4M3RPVFVDTVJyd0VhWDVIClZxdXRSZ1RkdWhpNzRqWDlUTGtnNExjRHp6L3o0alpIK0Q1MHNpS0pmTGRvRVBvb29xUkZ1bjExNFdmTDYvamgKZTRQcE5mR0hVcytCaVVnOVc0MXVPR1BRSkFoamFkYlFTVnJTSTFXWElTaTZlNkZ6Y0hYNHc1eGxTU0o5YWNsagpXSnd2Y1lQU0lvdXFVWUp3SlhIMkcvWitLR0xxRzl6eU9lSGZJMnZBTFFLQmdRRC9Id2FMVTd2TVpyQklEdlZmCklqY3hOT0Evc1RrbXpnMEIySEZJM2kxREVlRHl1T3pjZ2FnbDg0dmUwVU42bTNQcHE2RCtVRWMzYkZTc041dXgKWEV2Vm1IQy92aUg2TXhhRlROeXA4bCtkMnp2YnF3L203MksvblZpZlZPcmFLYWZ0ODAxbjh0OGtoN2xkcjA2dwprNUxBVzRxVDNzRlJQMXZRUEpDWGgwakJQd0tCZ1FEOGdDek9JamVXMnc4UDRRY2RWMFd0Qm5qV1VkcXZIMUZ1CnMrRGJuYTdGM0pGc3NGWEttMGFNeHJ1aDhPcjkyYUpCaXNJOFlGTU5hcmdsbTdERlhLd1RvYTAyOUNXNUdoUUUKRmt6SnFQMldiTVBhQ0NLb0d6SHhxa0RKaDdmUXlUbjFlYUR1OUtiY2VpTnN2NHNmYnRhT1BLZVVvL2w5Mmk1cgovelBKWjVQRUJRS0JnUURsc1l4VjB1ekpha2NhaVlVM3d2bUlMd0FidURjcGplaStHWjVkS2RQOGg5Tk5GVndFCmFDUDVEMTZHSFVpdzBkYzVzaEhBQm8wb0JEdnoxaGt5UlZQdG1sTWcvMUZlRDdNZUR3YTVhQVBZZnpOVVlwNXQKb0dmNjl2SjhlWnI5RXZZajBhT0dqRytGVjBGNGJNYmZTZTZkaTQybjlxMTh0aHUyejJDclhOMFBSd0tCZ0ZmaQoyVFdLYldUaHJIVnZjc1dBcWV6V2t0cXVOUHE5WU4reVZuSzZpS212NHlJOG9pL2FLZzAzNHZrejNPY3NpREJkCjlzdURENFZjL3VtQUxtQVRxSVZRd2ZTZHVEK2NWeGt4RmRzZktkemcwSzZrSkVQMDYwWmRaeDErVXJtbVFMa3oKYzVPWXJqZVJmNkVMYm9ZclV4ekErZWRmeDZoSmQ5ejVQL0k3VGhCdEFvR0JBSkdZMXFtckZPeGQ3WlZpL0ZjcQp5THRROTZ3N0tRdFFpWkQyUldaQWxVQVRFSitCcWlRdFlFTUtOSzVDcFRBZ2NwNit3NVlCZCtvUTJrQ0luOEpPCkVoRFZCZGYweFRyRExoYmhEYU1UUlJQdU1UYjBNV29JR0FXamk2Tzd4VUg2THVKMjV6UkJnSGVEQVBFdWV1bFYKWCtkSmFYS2NHMTFmL3dXUFN4Szl5Y0ZuCi0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K', '2025-05-22 10:54:38', '2025-05-22 10:54:38'),
(13, 35, '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA/QffgCMVcy0ymqp3efx6\n6bjUygsOL2L9mpRDM1ciHc32avN9tCURQMlz+lWxtNYDXsI2kgmISnLPRf09/SHx\nK+iJZnErjVfD4gidIs6oA9dBHMs84doFraRNT7VjCZlj58yNN8URfkNEcQ0FQzr8\neAWf+P/S3zecgE6gCrHEWcwMBdJ4uTgnxYBct/+AzLCPDVoEcuBDTUe3Xk6NDnUS\nBxCTiXLQL9KS2DdwQJAEAUiwh0HTLKzX6exnMBM6iuGzlnjU03eA0LVXk+Qs8mtd\n+wTJHAmhn8QagCmTBSTrzwYXO0nwePQ+IAKx5K2e9M64j+k/Y8twVSB5BgwcDqfL\n3wIDAQAB\n-----END PUBLIC KEY-----\n', 'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2Z0lCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktnd2dnU2tBZ0VBQW9JQkFRRDlCOStBSXhWekxUS2EKcW5kNS9IcnB1TlRLQ3c0dll2MmFsRU16VnlJZHpmWnE4MzIwSlJGQXlYUDZWYkcwMWdOZXdqYVNDWWhLY3M5RgovVDM5SWZFcjZJbG1jU3VOVjhQaUNKMGl6cWdEMTBFY3l6emgyZ1d0cEUxUHRXTUptV1BuekkwM3hSRitRMFJ4CkRRVkRPdng0QlovNC85TGZONXlBVHFBS3NjUlp6QXdGMG5pNU9DZkZnRnkzLzRETXNJOE5XZ1J5NEVOTlI3ZGUKVG8wT2RSSUhFSk9KY3RBdjBwTFlOM0JBa0FRQlNMQ0hRZE1zck5mcDdHY3dFenFLNGJPV2VOVFRkNERRdFZlVAo1Q3p5YTEzN0JNa2NDYUdmeEJxQUtaTUZKT3ZQQmhjN1NmQjQ5RDRnQXJIa3JaNzB6cmlQNlQ5ankzQlZJSGtHCkRCd09wOHZmQWdNQkFBRUNnZ0VBRGdxUU5JcUtxdnNvdFZZZEovL1BtTGkzYjlJS0NSUHptNXVWQytLMUpzSkMKWlp6NlNBQVp5Y3o5c1FVR3hlbzZhTmxhL2VMWmlJZ1pydUNaOGFDRlhOSnBEWWZkWkhLUEtwcFM5NWEybjFtSwptM1p5TDBNZTBMOHU5K3ZLZ1k5MlZ3VU9GNFZqUit6VzVFSldVR3pISmR1RHBySit6R2Q3T1dud1d0ckZ2MjlyCjNCU28rOVpwT3RHMHFTZm1nMVVEcVVqeG5PSEMyY05EMEV6L2dYU1dyNU43WU9RSHpEUzgzRGwrNzF6RXVNREIKOTF1V0dtWk1TOEZjbGp2Q0pLQUJMcktXTFN5dGNDQVNscS9BTDFiMU4rODRIUjBYc1hwVXpIWm4xOU1DT0pZeQpNNDBGTXRVM2V6Q0Y5dkVtUXRobkFSQlRXeGhMMGd5OWpWUUhMY09YUVFLQmdRRCtvLzNybFpQeHhpQzBsOTMzCmtpKzJoRGVrUkZrb1JCRFhxSDgrTFdHYnFkQUxTRktFZmEyU1VxVlJjVks0eGFqOWlWbkFuWnhBNTY2Z2hjZkgKVDViTkJ2bHFUa2tTbHNNMU9xQ3ZYN1RHVFZRZTNOSlFzME9wUUwwTC9uZ0tnRlVVRXNuYmtmbW5OMzJuVDVaNgo1MERPWHZ4eXNvRzA5RmVFOEkyOXNIb3Q1UUtCZ1FEK1lhNWFNbkFvbnZ2b0FqQnRFZEoxVVorR1p3M1RZZnZxCllrdmkrckVoSnBhWW5ZVi9tWnhQb1dTQ2JHNDVZeHVmUmlqbUdieHY1VHhtZ3IwUVVZM1hyQzFyTm5yVktyRDEKS1FIb29kMlVDaTZUK3RLOWU5aHBqN252eDlxd0Q5RUcraWNxcGtZY2tUdmRrY1lpVHI4ZGNoSCtPNCsvMUtOZApQbjIycDAyV2N3S0JnUUNRRDQwM08rOXdLQ3dXbjVpWFdLQWZ2UnVSbG8xdVIxdlQ5bUxPZFhRMFVkVCtuUkw2CkcvUVh4U1A4T0lXWlBWSkc1ZTdlRnd6d2QyS0hORWg3RFI3K3JZUnJ6UkJ3TU5VTmh6YWpJUjB2MTAzUkRCTDgKVHY3bmdWV3l0R3VMWFdGRXN3QjZkVkp0ZE9wa3ZwVWV3VGdieVFjL2lKZnpIUTFxaTRGVklNTnV0UUtCZ0FVZwpvT3dPZ2pZL1ppSjZFTkhSL3lVQVBTL3ZXZGI5N1o3KzFqckFCTnYvTkIvbFpQQjZmeTBYdXJmcVNacDhoZTBDCjlBWVhWTFlJcjl2OUhLUjhrRmhkanhqUEkxQ3lxSmg0ZUNKaFNOOFlDWEpRSnZsTXlzTHQ4N2lDbGNUZ3ZMemcKUS9QWElDaXBRTVNwa0kzV2VvRzBiK3BvOXUvTjhIRUx0bXI5R2xKREFvR0JBTnZHYlhyZUhDbW5KZ1VmREVrcApWUGV2U1hFaWM1UkFjN0VuNE91SnFzeHFVWG55VWtjaVY0TWpPOElBQzNZaHRodFpOWWlCSGwveTRQTHpLYXlWCmVBY21ua1lydjJPc2pYMWxaVVdxRUl1cHBYTWl5emFrNGVleThqR2Z4SXBLU0ZnNG4xY0luV014TkhyZk9sWEwKcFpCejQxOE5hSGNsNjRIamc0Qm1rNVRCCi0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K', '2025-05-22 10:55:47', '2025-05-22 10:55:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `document_shares`
--
ALTER TABLE `document_shares`
  ADD PRIMARY KEY (`share_id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `document_signatures`
--
ALTER TABLE `document_signatures`
  ADD PRIMARY KEY (`signature_id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`version_id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `encrypted_files`
--
ALTER TABLE `encrypted_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `files_with_signatures`
--
ALTER TABLE `files_with_signatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_logs_user` (`user_id`),
  ADD KEY `idx_login_logs_time` (`login_time`);

--
-- Indexes for table `mfa_secrets`
--
ALTER TABLE `mfa_secrets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_password_reset_token` (`token`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_google_id` (`google_id`),
  ADD KEY `idx_github_id` (`github_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_verification_token` (`verification_token`);

--
-- Indexes for table `user_keys`
--
ALTER TABLE `user_keys`
  ADD PRIMARY KEY (`key_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `document_shares`
--
ALTER TABLE `document_shares`
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `document_signatures`
--
ALTER TABLE `document_signatures`
  MODIFY `signature_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `version_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `encrypted_files`
--
ALTER TABLE `encrypted_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files_with_signatures`
--
ALTER TABLE `files_with_signatures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `mfa_secrets`
--
ALTER TABLE `mfa_secrets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `user_keys`
--
ALTER TABLE `user_keys`
  MODIFY `key_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `document_shares`
--
ALTER TABLE `document_shares`
  ADD CONSTRAINT `document_shares_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_shares_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_signatures`
--
ALTER TABLE `document_signatures`
  ADD CONSTRAINT `document_signatures_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`),
  ADD CONSTRAINT `document_signatures_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `document_versions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_versions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `encrypted_files`
--
ALTER TABLE `encrypted_files`
  ADD CONSTRAINT `encrypted_files_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `login_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mfa_secrets`
--
ALTER TABLE `mfa_secrets`
  ADD CONSTRAINT `mfa_secrets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_keys`
--
ALTER TABLE `user_keys`
  ADD CONSTRAINT `user_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
