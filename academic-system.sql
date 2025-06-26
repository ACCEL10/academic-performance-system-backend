-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2025 at 02:15 PM
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
-- Database: `academic-system`
--

-- --------------------------------------------------------

--
-- Table structure for table `advisees`
--

CREATE TABLE `advisees` (
  `advisor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `components`
--

CREATE TABLE `components` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `max_mark` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `components`
--

INSERT INTO `components` (`id`, `course_id`, `name`, `weight`, `max_mark`) VALUES
(1, 1, 'Quiz 1', 5.00, 5.00),
(2, 1, 'Quiz 2', 5.00, 5.00),
(3, 1, 'Quiz 3', 5.00, 5.00),
(4, 1, 'Test', 15.00, 15.00),
(5, 1, 'Assignment 1', 10.00, 10.00),
(6, 1, 'Assignment 2', 10.00, 10.00),
(7, 1, 'Project', 20.00, 20.00),
(8, 2, 'Quiz 1', 10.00, 10.00),
(9, 2, 'Quiz 2', 10.00, 10.00),
(10, 2, 'Assignment', 20.00, 20.00),
(11, 2, 'Midterm Test', 10.00, 10.00),
(12, 2, 'Project', 20.00, 20.00),
(13, 3, 'Quiz', 10.00, 10.00),
(14, 3, 'Lab Report', 20.00, 20.00),
(15, 3, 'Assignment', 10.00, 10.00),
(16, 3, 'Project', 30.00, 30.00),
(17, 4, 'Quiz', 10.00, 10.00),
(18, 4, 'Assignment 1', 15.00, 15.00),
(19, 4, 'Assignment 2', 15.00, 15.00),
(20, 4, 'Group Project', 30.00, 30.00),
(21, 5, 'Mini Quiz', 10.00, 10.00),
(22, 5, 'Case Study', 20.00, 20.00),
(23, 5, 'Assignment', 20.00, 20.00),
(24, 5, 'Presentation', 20.00, 20.00),
(25, 6, 'HTML Assignment', 10.00, 10.00),
(26, 6, 'CSS Quiz', 10.00, 10.00),
(27, 6, 'JS Test', 20.00, 20.00),
(28, 6, 'Web App Project', 30.00, 30.00);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `lecturer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `title`, `lecturer_id`) VALUES
(1, 'CSCI101', 'Intro to Computer Science', 1),
(2, 'CSCI102', 'Data Structures', 1),
(3, 'CSCI201', 'Database Systems', 2),
(4, 'CSCI202', 'Operating Systems', 2),
(5, 'CSCI203', 'Networks', 1),
(6, 'CSCI204', 'Web Programming', 2);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`) VALUES
(1, 5, 1),
(2, 5, 2),
(3, 5, 3),
(4, 6, 1),
(5, 6, 4),
(6, 6, 5),
(7, 7, 2),
(8, 7, 3),
(9, 7, 6),
(10, 8, 1),
(11, 8, 5),
(12, 8, 6),
(13, 9, 1),
(14, 9, 2),
(15, 9, 4),
(16, 10, 3),
(17, 10, 4),
(18, 10, 6),
(19, 11, 2),
(20, 11, 3),
(21, 11, 5),
(22, 12, 1),
(23, 12, 5),
(24, 12, 6),
(25, 13, 2),
(26, 13, 4),
(27, 13, 6),
(28, 14, 3),
(29, 14, 4),
(30, 14, 5);

-- --------------------------------------------------------

--
-- Table structure for table `final_exams`
--

CREATE TABLE `final_exams` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `mark` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `final_exams`
--

INSERT INTO `final_exams` (`id`, `student_id`, `course_id`, `mark`) VALUES
(1, 5, 1, 56.00),
(2, 5, 2, 50.00),
(3, 5, 3, 55.00),
(4, 6, 1, 47.00),
(5, 6, 4, 52.00),
(6, 6, 5, 82.00),
(7, 7, 2, 66.00),
(8, 7, 3, 79.00),
(9, 7, 6, 96.00),
(10, 8, 1, 87.00),
(11, 8, 5, 57.00),
(12, 8, 6, 48.00),
(13, 9, 1, 87.00),
(14, 9, 2, 74.00),
(15, 9, 4, 95.00),
(16, 10, 3, 58.00),
(17, 10, 4, 88.00),
(18, 10, 6, 67.00),
(19, 11, 2, 85.00),
(20, 11, 3, 51.00),
(21, 11, 5, 73.00),
(22, 12, 1, 70.00),
(23, 12, 5, 69.00),
(24, 12, 6, 90.00),
(25, 13, 2, 79.00),
(26, 13, 4, 85.00),
(27, 13, 6, 62.00),
(28, 14, 3, 46.00),
(29, 14, 4, 65.00),
(30, 14, 5, 81.00);

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` int(11) NOT NULL,
  `component_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `mark_obtained` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`id`, `component_id`, `student_id`, `mark_obtained`) VALUES
(1, 1, 5, 2.00),
(2, 2, 5, 3.00),
(3, 3, 5, 0.00),
(4, 4, 5, 0.00),
(5, 5, 5, 7.00),
(6, 6, 5, 3.00),
(7, 7, 5, 20.00),
(8, 8, 5, 6.00),
(9, 9, 5, 6.00),
(10, 10, 5, 19.00),
(11, 11, 5, 0.00),
(12, 12, 5, 5.00),
(13, 13, 5, 9.00),
(14, 14, 5, 9.00),
(15, 15, 5, 0.00),
(16, 16, 5, 17.00),
(17, 1, 6, 3.00),
(18, 2, 6, 1.00),
(19, 3, 6, 1.00),
(20, 4, 6, 1.00),
(21, 5, 6, 6.00),
(22, 6, 6, 2.00),
(23, 7, 6, 6.00),
(24, 17, 6, 8.00),
(25, 18, 6, 8.00),
(26, 19, 6, 2.00),
(27, 20, 6, 12.00),
(28, 21, 6, 7.00),
(29, 22, 6, 4.00),
(30, 23, 6, 15.00),
(31, 24, 6, 18.00),
(32, 8, 7, 9.00),
(33, 9, 7, 10.00),
(34, 10, 7, 3.00),
(35, 11, 7, 4.00),
(36, 12, 7, 9.00),
(37, 13, 7, 4.00),
(38, 14, 7, 5.00),
(39, 15, 7, 10.00),
(40, 16, 7, 15.00),
(41, 25, 7, 1.00),
(42, 26, 7, 7.00),
(43, 27, 7, 18.00),
(44, 28, 7, 14.00),
(45, 1, 8, 5.00),
(46, 2, 8, 2.00),
(47, 3, 8, 0.00),
(48, 4, 8, 12.00),
(49, 5, 8, 2.00),
(50, 6, 8, 6.00),
(51, 7, 8, 2.00),
(52, 21, 8, 4.00),
(53, 22, 8, 1.00),
(54, 23, 8, 2.00),
(55, 24, 8, 19.00),
(56, 25, 8, 8.00),
(57, 26, 8, 4.00),
(58, 27, 8, 1.00),
(59, 28, 8, 8.00),
(60, 1, 9, 5.00),
(61, 2, 9, 5.00),
(62, 3, 9, 2.00),
(63, 4, 9, 8.00),
(64, 5, 9, 0.00),
(65, 6, 9, 7.00),
(66, 7, 9, 6.00),
(67, 8, 9, 9.00),
(68, 9, 9, 9.00),
(69, 10, 9, 16.00),
(70, 11, 9, 3.00),
(71, 12, 9, 13.00),
(72, 17, 9, 3.00),
(73, 18, 9, 5.00),
(74, 19, 9, 6.00),
(75, 20, 9, 21.00),
(76, 13, 10, 8.00),
(77, 14, 10, 2.00),
(78, 15, 10, 1.00),
(79, 16, 10, 6.00),
(80, 17, 10, 7.00),
(81, 18, 10, 11.00),
(82, 19, 10, 0.00),
(83, 20, 10, 14.00),
(84, 25, 10, 8.00),
(85, 26, 10, 7.00),
(86, 27, 10, 8.00),
(87, 28, 10, 21.00),
(88, 8, 11, 0.00),
(89, 9, 11, 7.00),
(90, 10, 11, 5.00),
(91, 11, 11, 6.00),
(92, 12, 11, 3.00),
(93, 13, 11, 3.00),
(94, 14, 11, 20.00),
(95, 15, 11, 3.00),
(96, 16, 11, 15.00),
(97, 21, 11, 6.00),
(98, 22, 11, 4.00),
(99, 23, 11, 6.00),
(100, 24, 11, 18.00),
(101, 1, 12, 3.00),
(102, 2, 12, 4.00),
(103, 3, 12, 2.00),
(104, 4, 12, 10.00),
(105, 5, 12, 2.00),
(106, 6, 12, 2.00),
(107, 7, 12, 19.00),
(108, 21, 12, 5.00),
(109, 22, 12, 15.00),
(110, 23, 12, 7.00),
(111, 24, 12, 12.00),
(112, 25, 12, 2.00),
(113, 26, 12, 2.00),
(114, 27, 12, 1.00),
(115, 28, 12, 10.00),
(116, 8, 13, 5.00),
(117, 9, 13, 6.00),
(118, 10, 13, 17.00),
(119, 11, 13, 10.00),
(120, 12, 13, 15.00),
(121, 17, 13, 7.00),
(122, 18, 13, 9.00),
(123, 19, 13, 5.00),
(124, 20, 13, 20.00),
(125, 25, 13, 10.00),
(126, 26, 13, 10.00),
(127, 27, 13, 10.00),
(128, 28, 13, 14.00),
(129, 13, 14, 2.00),
(130, 14, 14, 14.00),
(131, 15, 14, 8.00),
(132, 16, 14, 26.00),
(133, 17, 14, 8.00),
(134, 18, 14, 1.00),
(135, 19, 14, 15.00),
(136, 20, 14, 7.00),
(137, 21, 14, 2.00),
(138, 22, 14, 20.00),
(139, 23, 14, 18.00),
(140, 24, 14, 17.00);

-- --------------------------------------------------------

--
-- Table structure for table `remark_requests`
--

CREATE TABLE `remark_requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `component_id` int(11) DEFAULT NULL,
  `justification` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('lecturer','student','advisor','admin') NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `matric_no` varchar(20) DEFAULT NULL,
  `pin` varchar(10) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `name`, `email`, `matric_no`, `pin`, `password_hash`, `created_at`) VALUES
(1, 'lecturer', 'Dr. Alice Tan', 'alice.tan@example.com', NULL, '0', 'alice123', '2025-06-25 08:35:05'),
(2, 'lecturer', 'Dr. Bob Lee', 'bob.lee@example.com', NULL, '0', 'bob123', '2025-06-25 08:35:05'),
(3, 'advisor', 'Dr. Nora Yunus', 'nora.yunus@example.com', NULL, '0', 'nora123', '2025-06-25 08:35:05'),
(4, 'advisor', 'Dr. Hafiz Rahman', 'hafiz.rahman@example.com', NULL, '0', 'hafiz123', '2025-06-25 08:35:05'),
(5, 'student', 'John Doe', 'john@example.com', 'A21CS001', '123456', 'studentpass', '2025-06-25 08:35:05'),
(6, 'student', 'Jane Smith', 'jane@example.com', 'A21CS002', '234567', 'studentpass', '2025-06-25 08:35:05'),
(7, 'student', 'Ali Ahmad', 'ali@example.com', 'A21CS003', '345678', 'studentpass', '2025-06-25 08:35:05'),
(8, 'student', 'Eizam Rosli', 'eizam@example.com', 'B23CS054', '456789', 'studentpass', '2025-06-25 08:35:05'),
(9, 'student', 'Nur Amirah', 'amirah@example.com', 'B23CS090', '987654', 'studentpass', '2025-06-25 08:35:05'),
(10, 'student', 'Nur Aisyah', 'aisyah@example.com', 'A21CS004', '567890', 'studentpass', '2025-06-25 08:35:05'),
(11, 'student', 'Daniel Lim', 'daniel.lim@example.com', 'B23CS101', '112233', 'studentpass', '2025-06-25 08:35:05'),
(12, 'student', 'Siti Hajar', 'siti.hajar@example.com', 'B23CS102', '221144', 'studentpass', '2025-06-25 08:35:05'),
(13, 'student', 'Kumar Raj', 'kumar.raj@example.com', 'B23CS103', '332255', 'studentpass', '2025-06-25 08:35:05'),
(14, 'student', 'Wong Mei Yee', 'mei.yee@example.com', 'B23CS104', '443366', 'studentpass', '2025-06-25 08:35:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advisees`
--
ALTER TABLE `advisees`
  ADD PRIMARY KEY (`advisor_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `components`
--
ALTER TABLE `components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `final_exams`
--
ALTER TABLE `final_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `component_id` (`component_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `remark_requests`
--
ALTER TABLE `remark_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `component_id` (`component_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matric_no` (`matric_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `components`
--
ALTER TABLE `components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `final_exams`
--
ALTER TABLE `final_exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `remark_requests`
--
ALTER TABLE `remark_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advisees`
--
ALTER TABLE `advisees`
  ADD CONSTRAINT `advisees_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `advisees_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `components`
--
ALTER TABLE `components`
  ADD CONSTRAINT `components_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `final_exams`
--
ALTER TABLE `final_exams`
  ADD CONSTRAINT `final_exams_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `final_exams_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`component_id`) REFERENCES `components` (`id`),
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `remark_requests`
--
ALTER TABLE `remark_requests`
  ADD CONSTRAINT `remark_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `remark_requests_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `remark_requests_ibfk_3` FOREIGN KEY (`component_id`) REFERENCES `components` (`id`);

-- --------------------------------------------------------

--
-- Table structure for table `advisor_notes`
--

CREATE TABLE IF NOT EXISTS `advisor_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `advisor_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `note` TEXT NOT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`advisor_id`) REFERENCES `users` (`id`),
    FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
