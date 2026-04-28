-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 06:48 PM
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
-- Database: `course_registration_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `is_super_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `full_name`, `department_id`, `is_super_admin`, `created_at`) VALUES
(1, 'admin', '$2y$10$kEVdRBO5C7YEUgOxuFjOzu6JBXv0tSvSSjVdwqf.9M1HrbVJX/cam', 'Super Admin', NULL, 1, '2026-04-24 21:28:48'),
(3, 'CMP', '$2y$10$daJfWYUd43SW.nGOGYS1welXeWqqeZ6lXclWoKM8qBKZ7QEBZ0IRC', 'Tahir', 1, 0, '2026-04-25 06:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `credit_unit` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `credit_unit`, `semester_id`, `session_id`, `department_id`, `level`) VALUES
(61, 'GST111', 'Communication in English', 2, 1, 3, 4, 100),
(62, 'GST111', 'Communication in English', 2, 1, 3, 1, 100),
(63, 'GST111', 'Communication in English', 2, 1, 3, 2, 100),
(64, 'GST111', 'Communication in English', 2, 1, 3, 3, 100),
(65, 'GST111', 'Communication in English', 2, 1, 2, 4, 200),
(66, 'GST111', 'Communication in English', 2, 1, 2, 1, 200),
(67, 'GST111', 'Communication in English', 2, 1, 2, 2, 200),
(68, 'GST111', 'Communication in English', 2, 1, 2, 3, 200),
(69, 'GST111', 'Communication in English', 2, 1, 3, 4, 200),
(70, 'GST111', 'Communication in English', 2, 1, 3, 1, 200),
(71, 'GST111', 'Communication in English', 2, 1, 3, 2, 200),
(72, 'GST111', 'Communication in English', 2, 1, 3, 3, 200),
(73, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 3, 4, 200),
(74, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 3, 1, 200),
(75, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 3, 2, 200),
(76, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 3, 3, 200),
(77, 'GST111', 'Communication in English', 2, 1, 1, 4, 300),
(78, 'GST111', 'Communication in English', 2, 1, 1, 1, 300),
(79, 'GST111', 'Communication in English', 2, 1, 1, 2, 300),
(80, 'GST111', 'Communication in English', 2, 1, 1, 3, 300),
(81, 'GST111', 'Communication in English', 2, 1, 2, 4, 300),
(82, 'GST111', 'Communication in English', 2, 1, 2, 1, 300),
(83, 'GST111', 'Communication in English', 2, 1, 2, 2, 300),
(84, 'GST111', 'Communication in English', 2, 1, 2, 3, 300),
(85, 'GST111', 'Communication in English', 2, 1, 3, 4, 300),
(86, 'GST111', 'Communication in English', 2, 1, 3, 1, 300),
(87, 'GST111', 'Communication in English', 2, 1, 3, 2, 300),
(88, 'GST111', 'Communication in English', 2, 1, 3, 3, 300),
(89, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 2, 4, 300),
(90, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 2, 1, 300),
(91, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 2, 2, 300),
(92, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 2, 3, 300),
(93, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 3, 4, 300),
(94, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 3, 1, 300),
(95, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 3, 2, 300),
(96, 'GST212', 'Philosophy, Logic and Human Existence', 2, 1, 3, 3, 300);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `matric_prefix` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `matric_prefix`) VALUES
(1, 'Computer Science', 'CMP'),
(2, 'Microbiology', 'MCB'),
(3, 'Physics', 'PHY'),
(4, 'Chemistry', 'CHM');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`) VALUES
(1, 'First Semester'),
(2, 'Second Semester'),
(3, 'Third Semester');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `name`) VALUES
(1, '2023/2024'),
(2, '2024/2025'),
(3, '2025/2026');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `matric_number` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `department_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone_number` varchar(20) DEFAULT NULL,
  `level` varchar(20) DEFAULT NULL,
  `study_mode` varchar(20) DEFAULT 'Full-Time',
  `faculty` varchar(100) DEFAULT 'Faculty of Science',
  `fingerprint_template` text DEFAULT NULL,
  `face_encoding` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `matric_number`, `full_name`, `department_id`, `email`, `password`, `profile_image`, `reset_token`, `reset_expires`, `created_at`, `phone_number`, `level`, `study_mode`, `faculty`, `fingerprint_template`, `face_encoding`) VALUES
(2, 'FT23MCB0001', 'Jane Smith', 2, 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, '2026-04-14 17:50:10', NULL, NULL, 'Full-Time', 'Faculty of Science', NULL, NULL),
(3, 'FT23CMP0390', 'Legend', 2, 'anddi@gmail.com', '$2y$10$JO0Js7Ba2OBCoT2qIMiDnu6KPMhbuHLTpe3wBjRJn1SRgGRlebe5a', 'IMG_1776342148_FT23CMP0390.jpg', NULL, NULL, '2026-04-14 17:51:18', NULL, NULL, 'Full-Time', 'Faculty of Science', NULL, NULL),
(5, 'FT23CMP0392', 'Legend', 3, 'legend2@gmail.com', '$2y$10$AXSUJDiv4qIp/bZsj50QEuyt7zGGQQjst6YJanR1ep7BFRr4lH466', 'IMG_1776342193_FT23CMP0392.jpg', NULL, NULL, '2026-04-15 18:07:39', NULL, NULL, 'Full-Time', 'Faculty of Science', NULL, NULL),
(6, 'FT23CMP0393', 'Legend', 4, 'legend3@gmail.com', '$2y$10$nZChSDNJUhpkWWtw16wWGuK9zkyQh4cOqv/ofDXo3AqxckU4AwjfC', NULL, NULL, NULL, '2026-04-15 18:10:30', NULL, NULL, 'Full-Time', 'Faculty of Science', NULL, NULL),
(7, 'FT23CMP0399', 'Legend', 2, 'leee@gmail.com', '$2y$10$nbW0aFidHM.G84D5MT/TfOvFHQjS/y5XEWOdq6d5tV4ysb3ZviHk2', NULL, NULL, NULL, '2026-04-18 08:03:32', NULL, NULL, 'Full-Time', 'Faculty of Science', NULL, NULL),
(8, 'FT23CMP001', 'Legend', 1, 'legend@gmail.com', '$2y$10$qB4glkhJPycYKihUXORBru5HaGqWl.mBod7IO1CJ6vemksoYq6RHC', 'IMG_1777056100_FT23CMP001.jpg', NULL, NULL, '2026-04-24 18:40:11', NULL, NULL, 'Full-Time', 'Faculty of Science', NULL, NULL),
(9, 'FT23CMP0380', 'Abdulwasiu Isah', 1, 'abdulwasiuisah34@gmail.com', '$2y$10$x8tulKlskcKpASxSlJBOF.7Lkw0hTeOjUVknCHP6Lgx0YgPS9KEXK', NULL, NULL, NULL, '2026-04-24 20:36:33', '08116878163', '300', 'Full-Time', 'Faculty of Science', NULL, NULL),
(10, 'FT25CMP0001', 'Abdulwasiu Lgend', 1, 'abdulwasiuisah@gmail.com', '$2y$10$j1aOBLpVEx0ICDo8KHbQ7eZy6kNh1fQtBhsYise5YtJvCCC9nw.DK', NULL, NULL, NULL, '2026-04-25 07:02:58', '08116878165', '100', 'Full-Time', 'Faculty of Science', NULL, NULL),
(13, 'FT23CMP0400', 'Abdul Wasiu', 1, 'abdulwas@gmail.com', '$2y$10$Y0qLLYBFUxb0w9nmCnOSXuzMm8GJ/sMt1Od5lW5R9WipEj3cn5twe', NULL, NULL, NULL, '2026-04-26 14:20:28', '08116878156', '300', 'Full-Time', 'Faculty of Science', '', 'face_1777213225438_FT23CMP0400');

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `matric_prefix` (`matric_prefix`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matric_number` (`matric_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`),
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`),
  ADD CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
