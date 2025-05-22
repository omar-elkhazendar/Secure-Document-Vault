-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2025 at 02:02 AM
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
(3, 'dddddd', 'dddddd', 'uploads/documents/682a003471c01.sql', 'application/octet-stream', 44527, NULL, NULL, NULL, NULL, '2025-05-18 15:43:48', '2025-05-18 15:43:48'),
(4, 'nbbbbbbbb', 'bbbbbbbbb', 'uploads/documents/682a00d434369.docx', 'application/vnd.openxmlformats-officedocument.word', 3681640, NULL, NULL, NULL, NULL, '2025-05-18 15:46:28', '2025-05-18 15:46:28'),
(5, 'bvvvv', 'vvvvvvvvx', 'uploads/documents/682a02537e775.pdf', 'application/pdf', 121869, NULL, NULL, NULL, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(6, 'vvvvvv', 'vvd', 'uploads/documents/682a060fa7aa7.pdf', 'application/pdf', 543989, 'b1080603a4c20dc1025fa4f17017c6e0f61f718fadf3590d9b8e067fc675061a', NULL, 'NHrs61U8vXIjKOLiY+IXaapjRZYgY3lqOfM02nRlqwqUgsWahERVJ5ejAwDdso/COR3ERKmzKmuJhRonyc45WBBL9kSIycsoTAsnarkMbaNAnc1juGIbf1j6iw7oSLmIhgN+wPDmy1+zFLj9AoZgmi0z64T3wSJlRaufYOZqaL1lbSOR1pf4oxNofwUZuJuqAXKbA8i3YLtJZE50wrUPM/I9hU1wZPqii0iSGHiO5MXvQXWu23kT/Ufm3E1RlKqR/8Qq6spfT1W7DkW9pI3sxzZtBhQwH9hkcOZIJVbKBB1jKcXI6mTZWQ7xiKirc8+8tJoZvfo35/Nxxzv2RphA5w==', NULL, '2025-05-18 16:08:47', '2025-05-18 16:08:47'),
(7, 'kkkkkkkkkkkkkkko', 'kkkkkkkkkkkkiyyy', 'uploads/documents/682a062b5f714.pdf', 'application/pdf', 3575549, 'ca901fe9df505a4880588a3052e1f0b24a64a6554ffcb0f99ccdf6db96bdfb47', NULL, 'Bvg+68zmW6i+fX2ptVah80UddDweTF6JNIya4Wlj9M3LtKcKNHUMKtJGU8QrNwejylv7khEG93aRhXuNeLQFSOTx/1nZnh9ElTipxrGjSh7Yu3nr+Yh9FuYkKCSK7CIIqREW8Cr+KjWViWrAs+gCA+SWq9zOsMpWpC8b+IA7ey6zwPxYS9Tx0PI33rLV4qrqMRbRQy6pxc/CISBLk8Br7c2qws7uvpEiLjq0XEIoqBI3pPbz6jjpTqvOAQ2J+Xc8H3yPBfGJWAgTr4uOJeryL5XuBTXE9SWUYTxEeir/3tY+qMvT6EnIEKvziVxXtUzsi1UTFZjBBi0dic8Eq/woqQ==', NULL, '2025-05-18 16:09:15', '2025-05-18 16:09:15'),
(8, 'mhjhjhjhjh', 'njhjhhjh', 'uploads/documents/682a06f0ba793.pdf', 'application/pdf', 1846519, '475dd4485728c51cc8aa60960a0f28986d7e03691b52948e0fdc8d43222ddaf6', NULL, 'HVewmM7YMhQWPBTfFxi7WR/M15iIkay6bei7HlHYY3sl55wfPaO0U53spLtJcPfzZpyVRoxHCL0/QHDG03KkwkwgD4IKzis97pVN2pY6YLyKqAWva+fZGNIilLUPY54F/ZqSgOZKkk48A25Gj45+bwnzvNxs1ZSSVhz6pOjUHzjqVUDOLXHCDDtluwd6H9+g9/BFnP3phIn5vYjHSsc3Sf25zd1hVOsfraO9j7VyW+2CRhCixuB9N9YOczAWa43bAkDaEfF8vz8PQYZaCbBwl1lOcc/KhBDmSmGtmCT8FZOF1dgyw71EgonkzASEHBT0Qxyv3EcsxBbuuTvaR2EyHA==', NULL, '2025-05-18 16:12:32', '2025-05-18 16:12:32'),
(9, 'vvcccccccv', 'mmmmmmmk', 'uploads/documents/682a093531037.pdf', 'application/pdf', 1846519, 'e8296a042cf20f557242e40ac3a7a02a536f0213ad20c1a0a91c2ddece079142', '8aa89b8fca61e0fedb388ff69eba411a898c7a3c91145d0c4a00b9456680cb04', 'FM4QCCfhnhxN3FOfCGem81SXlOlhf1MLxJZvL1QgUpDJwJDlKSWOCuw1nVLslwGnwa3k2r8XRnTuD7b1nfYxTfTQNTqKN7TnXWb0R33MNgvmTwHt/7gZ0bEWSDLnKE66NC92rEjrIUvtrWh+7Rka5beqi4LfTuJFuswqPtPbef12RaNUmwTnST+gnb5Ib29yC5npCsbv4GkLK1VLiDd/DkYnFWO75QmgP9yGye2dmyS+bc72tNw/LM/YBl/ybV73n4coZc/jDK6t/H9iNoOvtNAy7cEaZu/5AfXnA4odgnI1T7hLZe+aEbwyV/Wdip0dfHAcv4FQ65nUXKaWQqRQ9w==', NULL, '2025-05-18 16:22:13', '2025-05-18 16:22:13');

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
(5, 5, '::1', '2025-05-18 15:46:26', ''),
(6, 1, '0.0.0.0', '2025-05-18 15:50:59', 'manual'),
(7, 5, '0.0.0.0', '2025-05-18 15:51:32', ''),
(8, 1, '0.0.0.0', '2025-05-18 15:54:55', 'manual'),
(10, 1, '0.0.0.0', '2025-05-18 15:59:36', 'manual'),
(11, 1, '0.0.0.0', '2025-05-18 16:06:04', 'manual'),
(12, 1, '0.0.0.0', '2025-05-18 16:15:35', 'manual');

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
('bfvdd0oftb89nnvgsurfja84dt', 15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', NULL, '2025-05-20 22:50:48', '2025-05-20 22:50:48', '2025-05-21 22:50:48');

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
(46, 1, 'delete_document', 'Deleted document: demoooooo (ID: 1)', '::1', '2025-05-18 16:28:14');

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
(5, 'adham308', 'adham308@gmail.com', '$2y$10$jpmhgqjFuu3STs1jYcNcF.By.97oGXao.jyfRYDB9LIvIpCPGMXia', NULL, NULL, NULL, '', NULL, 1, 0, NULL, 'active', 0, '2025-05-18 15:41:05', '2025-05-18 16:51:04', 1),
(10, '', 'kareem.helmii@gmail.com', '$2y$10$Tyx.OE55oDk/RSlYPqDxsO.2o9p5nwlkqUGd1WMpNwHmec89Ukt/O', NULL, NULL, NULL, 'google', NULL, 1, 0, NULL, 'active', 0, '2025-05-18 16:08:39', '2025-05-18 17:15:43', 2),
(14, 'Zakarya Aaa', 'aaazakarya@gmail.com', '', NULL, NULL, 'google-oauth2|104678846324967081658', 'okta', NULL, 1, 0, NULL, 'active', 0, '2025-05-20 23:43:16', '2025-05-20 23:48:03', 2),
(15, 'Zakarya Aa', 'kingserum1999@gmail.com', '', NULL, NULL, 'google-oauth2|115689929249662701329', 'okta', NULL, 1, 0, NULL, 'active', 0, '2025-05-20 23:50:48', '2025-05-20 23:50:48', 2);

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
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `document_shares`
--
ALTER TABLE `document_shares`
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `version_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `mfa_secrets`
--
ALTER TABLE `mfa_secrets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_keys`
--
ALTER TABLE `user_keys`
  MODIFY `key_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
