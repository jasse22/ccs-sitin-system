-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 25, 2026 at 08:48 AM
-- Server version: 8.0.46
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ccs_sitin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-05-18 03:30:22');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int NOT NULL,
  `admin_name` varchar(100) NOT NULL DEFAULT 'CCS Admin',
  `content` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `admin_name`, `content`, `created_at`) VALUES
(1, 'CCS Admin', 'Welcome to the CCS Sit-in Monitoring System! Please follow all laboratory rules and regulations.', '2026-01-15 00:00:00'),
(2, 'CCS Admin', 'Important Announcement: We are excited to launch our new Sit-in Monitoring System! Students can now reserve laboratory slots online.', '2026-02-11 02:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `computers`
--

CREATE TABLE `computers` (
  `id` int NOT NULL,
  `lab_room` varchar(20) NOT NULL,
  `pc_number` int NOT NULL,
  `status` varchar(20) DEFAULT 'available',
  `current_student_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computers`
--

INSERT INTO `computers` (`id`, `lab_room`, `pc_number`, `status`, `current_student_id`, `created_at`) VALUES
(1, '524', 1, 'available', NULL, '2026-05-25 03:43:48'),
(2, '524', 2, 'available', NULL, '2026-05-25 03:43:48'),
(3, '524', 3, 'available', NULL, '2026-05-25 03:43:48'),
(4, '524', 4, 'available', NULL, '2026-05-25 03:43:48'),
(5, '524', 5, 'available', NULL, '2026-05-25 03:43:48'),
(6, '524', 6, 'available', NULL, '2026-05-25 03:43:48'),
(7, '524', 7, 'available', NULL, '2026-05-25 03:43:48'),
(8, '524', 8, 'available', NULL, '2026-05-25 03:43:48'),
(9, '524', 9, 'available', NULL, '2026-05-25 03:43:48'),
(10, '524', 10, 'available', NULL, '2026-05-25 03:43:48'),
(11, '526', 1, 'available', NULL, '2026-05-25 03:43:48'),
(12, '526', 2, 'available', NULL, '2026-05-25 03:43:48'),
(13, '526', 3, 'available', NULL, '2026-05-25 03:43:48'),
(14, '526', 4, 'available', NULL, '2026-05-25 03:43:48'),
(15, '526', 5, 'available', NULL, '2026-05-25 03:43:48'),
(16, '526', 6, 'available', NULL, '2026-05-25 03:43:48'),
(17, '526', 7, 'available', NULL, '2026-05-25 03:43:48'),
(18, '526', 8, 'available', NULL, '2026-05-25 03:43:48'),
(19, '526', 9, 'available', NULL, '2026-05-25 03:43:48'),
(20, '526', 10, 'available', NULL, '2026-05-25 03:43:48'),
(21, '528', 1, 'available', NULL, '2026-05-25 03:43:48'),
(22, '528', 2, 'available', NULL, '2026-05-25 03:43:48'),
(23, '528', 3, 'available', NULL, '2026-05-25 03:43:48'),
(24, '528', 4, 'available', NULL, '2026-05-25 03:43:48'),
(25, '528', 5, 'available', NULL, '2026-05-25 03:43:48'),
(26, '528', 6, 'available', NULL, '2026-05-25 03:43:48'),
(27, '528', 7, 'available', NULL, '2026-05-25 03:43:48'),
(28, '528', 8, 'available', NULL, '2026-05-25 03:43:48'),
(29, '528', 9, 'available', NULL, '2026-05-25 03:43:48'),
(30, '528', 10, 'available', NULL, '2026-05-25 03:43:48');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `id_number` varchar(20) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `laboratory` varchar(50) NOT NULL,
  `time_in` time DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(50) NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'reservations_enabled', '1', '2026-05-18 14:40:27');

-- --------------------------------------------------------

--
-- Table structure for table `sit_in_history`
--

CREATE TABLE `sit_in_history` (
  `id` int NOT NULL,
  `student_id` int DEFAULT NULL,
  `id_number` varchar(20) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `sit_purpose` varchar(255) NOT NULL,
  `laboratory` varchar(50) NOT NULL,
  `login_time` time DEFAULT NULL,
  `logout_time` time DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `feedback` text,
  `pc_number` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sit_in_history`
--

INSERT INTO `sit_in_history` (`id`, `student_id`, `id_number`, `fullname`, `sit_purpose`, `laboratory`, `login_time`, `logout_time`, `date`, `created_at`, `feedback`, `pc_number`) VALUES
(1, 1, '2024-00001', 'Juan Santos Dela Cruz', 'C Programming', '524', '08:00:00', '10:00:00', '2026-05-18', '2026-05-18 03:30:22', NULL, NULL),
(2, 2, '2024-00002', 'Maria Garcia Reyes', 'Web Development', '526', '09:00:00', '11:00:00', '2026-05-18', '2026-05-18 03:30:22', NULL, NULL),
(3, 3, '2024-00003', 'Jose Ramos Santos', 'Database Systems', '524', '13:00:00', '15:00:00', '2026-05-18', '2026-05-18 03:30:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `software`
--

CREATE TABLE `software` (
  `id` int NOT NULL,
  `software_name` varchar(100) NOT NULL,
  `version` varchar(50) DEFAULT NULL,
  `lab_room` varchar(20) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `software`
--

INSERT INTO `software` (`id`, `software_name`, `version`, `lab_room`, `description`, `created_at`) VALUES
(1, 'Visual Studio Code', '1.89.0', '524', 'Code editing. Redefined.', '2026-05-24 20:59:52'),
(2, 'Python', '3.11.4', '524', 'Programming language', '2026-05-24 20:59:52'),
(3, 'MySQL Workbench', '8.0.35', '526', 'Database management', '2026-05-24 20:59:52'),
(4, 'Eclipse IDE', '2024-03', '526', 'Java development environment', '2026-05-24 20:59:52'),
(5, 'Android Studio', '2023.3', '528', 'Android app development', '2026-05-24 20:59:52'),
(6, 'Git', '2.45.0', '524', 'Version control system', '2026-05-24 20:59:52'),
(7, 'Node.js', '20.11.0', '526', 'JavaScript runtime', '2026-05-24 20:59:52'),
(8, 'Postman', '11.0.0', '528', 'API development tool', '2026-05-24 20:59:52'),
(9, 'Docker Desktop', '4.27.0', '528', 'Container platform', '2026-05-24 20:59:52'),
(10, 'XAMPP', '8.2.12', '524', 'Apache + PHP + MySQL', '2026-05-24 20:59:52'),
(11, 'Visual Studio Code', '1.89.0', '524', 'Code editing. Redefined.', '2026-05-24 21:07:55'),
(12, 'Python', '3.11.4', '524', 'Programming language', '2026-05-24 21:07:55'),
(13, 'MySQL Workbench', '8.0.35', '526', 'Database management', '2026-05-24 21:07:55'),
(14, 'Eclipse IDE', '2024-03', '526', 'Java development environment', '2026-05-24 21:07:55'),
(15, 'Android Studio', '2023.3', '528', 'Android app development', '2026-05-24 21:07:55'),
(16, 'Git', '2.45.0', '524', 'Version control system', '2026-05-24 21:07:55'),
(17, 'Node.js', '20.11.0', '526', 'JavaScript runtime', '2026-05-24 21:07:55'),
(18, 'Postman', '11.0.0', '528', 'API development tool', '2026-05-24 21:07:55'),
(19, 'Docker Desktop', '4.27.0', '528', 'Container platform', '2026-05-24 21:07:55'),
(20, 'XAMPP', '8.2.12', '524', 'Apache + PHP + MySQL', '2026-05-24 21:07:55'),
(21, 'Visual Studio Code', '1.89.0', '524', 'Code editing. Redefined.', '2026-05-24 21:27:42'),
(22, 'Python', '3.11.4', '524', 'Programming language', '2026-05-24 21:27:42'),
(23, 'MySQL Workbench', '8.0.35', '526', 'Database management', '2026-05-24 21:27:42'),
(24, 'Eclipse IDE', '2024-03', '526', 'Java development environment', '2026-05-24 21:27:42'),
(25, 'Android Studio', '2023.3', '528', 'Android app development', '2026-05-24 21:27:42'),
(26, 'Git', '2.45.0', '524', 'Version control system', '2026-05-24 21:27:42'),
(27, 'Node.js', '20.11.0', '526', 'JavaScript runtime', '2026-05-24 21:27:42'),
(28, 'Postman', '11.0.0', '528', 'API development tool', '2026-05-24 21:27:42'),
(29, 'Docker Desktop', '4.27.0', '528', 'Container platform', '2026-05-24 21:27:42'),
(30, 'XAMPP', '8.2.12', '524', 'Apache + PHP + MySQL', '2026-05-24 21:27:42'),
(31, 'Visual Studio Code', '1.89.0', '524', 'Code editing. Redefined.', '2026-05-24 21:28:48'),
(32, 'Python', '3.11.4', '524', 'Programming language', '2026-05-24 21:28:48'),
(33, 'MySQL Workbench', '8.0.35', '526', 'Database management', '2026-05-24 21:28:48'),
(34, 'Eclipse IDE', '2024-03', '526', 'Java development environment', '2026-05-24 21:28:48'),
(35, 'Android Studio', '2023.3', '528', 'Android app development', '2026-05-24 21:28:48'),
(36, 'Git', '2.45.0', '524', 'Version control system', '2026-05-24 21:28:48'),
(37, 'Node.js', '20.11.0', '526', 'JavaScript runtime', '2026-05-24 21:28:48'),
(38, 'Postman', '11.0.0', '528', 'API development tool', '2026-05-24 21:28:48'),
(39, 'Docker Desktop', '4.27.0', '528', 'Container platform', '2026-05-24 21:28:48'),
(40, 'XAMPP', '8.2.12', '524', 'Apache + PHP + MySQL', '2026-05-24 21:28:48');

-- --------------------------------------------------------

--
-- Table structure for table `software_uploads`
--

CREATE TABLE `software_uploads` (
  `id` int NOT NULL,
  `software_name` varchar(100) NOT NULL,
  `version` varchar(50) DEFAULT NULL,
  `lab_room` varchar(20) NOT NULL,
  `description` text,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` varchar(50) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int NOT NULL,
  `id_number` varchar(20) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT '',
  `course` varchar(20) NOT NULL,
  `year_level` tinyint NOT NULL DEFAULT '1',
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT '',
  `session` int NOT NULL DEFAULT '30',
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `id_number`, `lastname`, `firstname`, `middlename`, `course`, `year_level`, `email`, `password`, `address`, `session`, `profile_photo`, `created_at`) VALUES
(1, '2024-00001', 'Dela Cruz', 'Juan', 'Santos', 'BSIT', 1, 'juan@uc.edu.ph', '$2y$10$TKh8H1.PyfcAawKIhFCDn.LwPPMRAEnw8N2vFhzGQKmI8qOuE7Cme', 'Cebu City', 30, NULL, '2026-05-18 03:30:22'),
(2, '2024-00002', 'Reyes', 'Maria', 'Garcia', 'BSCS', 2, 'maria@uc.edu.ph', '$2y$10$TKh8H1.PyfcAawKIhFCDn.LwPPMRAEnw8N2vFhzGQKmI8qOuE7Cme', 'Mandaue City', 30, NULL, '2026-05-18 03:30:22'),
(3, '2024-00003', 'Santos', 'Jose', 'Ramos', 'BSIT', 3, 'jose@uc.edu.ph', '$2y$10$TKh8H1.PyfcAawKIhFCDn.LwPPMRAEnw8N2vFhzGQKmI8qOuE7Cme', 'Lapu-Lapu', 30, NULL, '2026-05-18 03:30:22'),
(4, '2024-00004', 'Flores', 'Ana', 'Torres', 'BSCS', 1, 'ana@uc.edu.ph', '$2y$10$TKh8H1.PyfcAawKIhFCDn.LwPPMRAEnw8N2vFhzGQKmI8qOuE7Cme', 'Talisay City', 30, NULL, '2026-05-18 03:30:22'),
(5, '23784630', 'Sarmiento', 'Kathleen', 'Daclan', 'BSIT', 3, 'daclankath.23@gmail.com', '$2y$10$TKh8H1.PyfcAawKIhFCDn.LwPPMRAEnw8N2vFhzGQKmI8qOuE7Cme', 'Carcar', 30, NULL, '2026-05-18 03:30:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `computers`
--
ALTER TABLE `computers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_room` (`lab_room`,`pc_number`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `software`
--
ALTER TABLE `software`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `software_uploads`
--
ALTER TABLE `software_uploads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `computers`
--
ALTER TABLE `computers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `software`
--
ALTER TABLE `software`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `software_uploads`
--
ALTER TABLE `software_uploads`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  ADD CONSTRAINT `sit_in_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
