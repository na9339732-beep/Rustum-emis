-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2026 at 01:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `testemis`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Leave') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `student_id`, `class_id`, `teacher_id`, `attendance_date`, `status`, `created_at`, `updated_at`) VALUES
(27, 21, 23, 29, '2026-04-30', 'Present', '2026-04-30 14:22:48', '2026-04-30 14:22:48');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(256) NOT NULL,
  `class_short` varchar(12) NOT NULL,
  `class_status` enum('active','inactive','deleted') DEFAULT 'active',
  `session_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `class_short`, `class_status`, `session_id`) VALUES
(23, '11th grade', 'xi', 'active', 16),
(24, '12th grade', 'xii', 'active', 16);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`) VALUES
(1, 'Computer Science'),
(3, 'English'),
(4, 'Maths'),
(2, 'STAT');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `exam_id` int(11) NOT NULL,
  `exam_title` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`exam_id`, `exam_title`, `class_id`, `start_date`, `end_date`, `description`, `created_at`) VALUES
(12, 'Mid term', 23, '2020-06-01', '2026-06-30', '', '2026-04-30 11:51:13'),
(13, 'mid term', 24, '2026-05-01', '2026-05-30', '', '2026-04-30 14:24:43');

-- --------------------------------------------------------

--
-- Table structure for table `exam_routine`
--

CREATE TABLE `exam_routine` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_routine`
--

INSERT INTO `exam_routine` (`id`, `exam_id`, `subject_id`, `exam_date`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(20, 12, 24, '2020-06-01', '09:00:00', '10:00:00', '2026-04-30 11:52:48', '2026-04-30 11:52:48'),
(21, 12, 24, '2020-06-03', '09:00:00', '11:00:00', '2026-04-30 13:06:59', '2026-04-30 13:06:59'),
(22, 12, 25, '2020-06-07', '09:00:00', '11:00:00', '2026-04-30 13:07:32', '2026-04-30 13:07:32'),
(23, 12, 24, '2026-06-07', '09:00:00', '11:00:00', '2026-04-30 14:05:59', '2026-04-30 14:05:59'),
(24, 12, 24, '2026-02-01', '09:00:00', '11:00:00', '2026-04-30 14:07:23', '2026-04-30 14:07:23'),
(25, 12, 24, '2026-06-07', '09:00:00', '11:00:00', '2026-04-30 14:09:57', '2026-04-30 14:09:57');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `user_type` enum('Student','Parents','Admin','Teacher') DEFAULT 'Parents',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ptm_bookings`
--

CREATE TABLE `ptm_bookings` (
  `booking_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `meeting_date` date NOT NULL,
  `booked_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
  `availability_id` int(11) DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ptm_bookings`
--

INSERT INTO `ptm_bookings` (`booking_id`, `child_id`, `teacher_id`, `meeting_date`, `booked_by`, `created_at`, `status`, `availability_id`, `remarks`) VALUES
(14, 21, 29, '2026-05-03', 109, '2026-04-30 18:52:26', 'Cancelled', NULL, NULL),
(15, 21, 29, '2026-05-06', 109, '2026-04-30 18:58:46', 'Confirmed', 14, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `marks` text NOT NULL,
  `grade` varchar(5) NOT NULL,
  `exam_term` varchar(50) DEFAULT 'Final',
  `session_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `session_name` varchar(20) NOT NULL,
  `starting_date` date NOT NULL,
  `status` enum('active','completed') DEFAULT 'active',
  `remarks` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `session_name`, `starting_date`, `status`, `remarks`) VALUES
(16, '2020 -2022', '2020-01-01', 'active', '');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_name` varchar(256) NOT NULL,
  `father_name` varchar(256) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('registered','admitted','banned','suspended') DEFAULT 'registered',
  `user_id` int(11) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `student_cnic` varchar(15) DEFAULT NULL,
  `father_cnic` varchar(15) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_name`, `father_name`, `email`, `phone`, `address`, `gender`, `dob`, `class_id`, `registration_date`, `status`, `user_id`, `city`, `state`, `photo`, `student_cnic`, `father_cnic`, `session_id`, `group_id`) VALUES
(21, 'Rustam Ali', 'Mansab dar', 'ra7527753@gmail.com', '03315609564', 'RANGE ROAD', 'Male', '2000-05-01', 23, '2026-04-30 13:40:02', 'admitted', 108, '0', '', NULL, '3740514536155', '3740514335177', 16, NULL),
(26, 'Hamza', 'Saeed', 'hamza@gmail.com', '03315609667', 'range road', 'Male', '2004-01-02', 24, '2026-04-30 17:24:06', 'admitted', 122, '0', '', NULL, '3740514536086', '3740514637160', 16, 11);

-- --------------------------------------------------------

--
-- Table structure for table `student_groups`
--

CREATE TABLE `student_groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_short` varchar(20) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_groups`
--

INSERT INTO `student_groups` (`group_id`, `group_name`, `group_short`, `status`, `created_at`) VALUES
(10, 'ICS', 'ICS', 'Active', '2026-04-30 14:55:13'),
(11, 'Pre Engineering', 'PE', 'Active', '2026-04-30 14:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `study_materials`
--

CREATE TABLE `study_materials` (
  `material_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `class_id`, `teacher_id`, `status`, `created_at`, `updated_at`) VALUES
(24, 'math', 23, NULL, 'active', '2026-04-30 11:52:48', '2026-04-30 11:52:48'),
(25, 'english', 23, 29, 'active', '2026-04-30 13:05:29', '2026-04-30 13:05:29');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `teacher_name` varchar(100) NOT NULL,
  `cnic` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `highest_qualification` varchar(100) NOT NULL,
  `bps` int(11) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `job_nature` enum('Permanent','Contract','Visiting') DEFAULT 'Contract',
  `joining` date NOT NULL,
  `job_status` enum('Active','Resigned','Retired','Suspended') DEFAULT 'Active',
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `teacher_name`, `cnic`, `email`, `photo`, `designation`, `highest_qualification`, `bps`, `subject`, `job_nature`, `joining`, `job_status`, `department_id`) VALUES
(29, 'Ahmed Khan', '3740514536155', 'ahmed@emis.com', 'teacher_69f2996979297.jpg', 'teacher', 'Master', 34, 'Math', 'Permanent', '2026-01-01', 'Active', 1),
(30, 'Ahmed2', '1310131446378', 'Ahmed@gmail.com', 'teacher_69f3954190b90.jpg', 'lecturer', 'ms', 17, 'computer', 'Permanent', '2026-04-30', 'Active', 1);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_availability`
--

CREATE TABLE `teacher_availability` (
  `availability_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('Available','Booked','Cancelled') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_availability`
--

INSERT INTO `teacher_availability` (`availability_id`, `teacher_id`, `meeting_date`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(14, 29, '2026-05-06', '09:00:00', '10:00:00', 'Booked', '2026-04-30 13:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_classes`
--

CREATE TABLE `teacher_classes` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `status` enum('Active','Deleted') DEFAULT 'Active',
  `subject` varchar(100) NOT NULL,
  `day` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_classes`
--

INSERT INTO `teacher_classes` (`id`, `class_id`, `teacher_id`, `session_id`, `status`, `subject`, `day`, `start_time`, `end_time`) VALUES
(72, 23, 29, 16, 'Active', 'english', 'Monday', '09:00:00', '10:00:00'),
(73, 23, 29, 16, 'Active', 'english', 'Tuesday', '09:00:00', '10:00:00'),
(74, 23, 29, 16, 'Active', 'english', 'Wednesday', '09:00:00', '10:00:00'),
(75, 23, 29, 16, 'Active', 'english', 'Thursday', '09:00:00', '10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `cnic` varchar(15) DEFAULT NULL,
  `password` varchar(256) NOT NULL,
  `role` enum('Admin','Teacher','Student','Parents') DEFAULT 'Student',
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `verification_token` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `cnic`, `password`, `role`, `creation_date`, `status`, `verification_token`, `email_verified`, `reset_token`, `reset_expires`) VALUES
(1, 'Hello Dello', 'hellodello@gmail.com', NULL, '$2y$10$EPymCQZQNksEB6GVZ.oXU.68IbLQkBJcoKP4DSXG8Kh776w5iiBZC', 'Admin', '2025-12-09 20:37:17', 'active', 'bdc0acc681c836a62d828b19fd546c3a', 1, NULL, NULL),
(107, 'ahmed@emis.com', 'ahmed@emis.com', '3740514536155', '$2y$10$K4eP/jiFhciC1igiJe9iL.tn7u5IO.vjdTDHQst50e.u1HPkIiGO2', 'Teacher', '2026-04-29 23:51:05', 'active', NULL, 1, NULL, NULL),
(108, 'Rustam Ali', 'ra7527753@gmail.com', '3740514536155', '$2y$10$MMivs0GIMPit6oo.ZXnFM.VWkEhAvLTeWZZdNuU51bfaf9uO/9gCK', 'Student', '2026-04-30 13:38:23', 'active', '99a74f1fca375f8fc78ae01cb6fe2861', 1, NULL, NULL),
(109, 'Mansabdar', 'ra7524733@gmail.com', '3740514335177', '$2y$10$TbcDoom5WlPQrGF1oWY3meHaR/9tsfsP3wpJm9LksNcCyPDu99OCa', 'Parents', '2026-04-30 13:50:32', 'active', '739744a882f50cebf019d2033a274cf5', 0, NULL, NULL),
(111, 'test', 'test@gmail.com', '', '$2y$10$a3xXNoWsB.9D5WWzYowRvOMUAqTUqPURxVmBrNgxlxbBHQrG5P1sG', 'Student', '2026-04-30 14:40:20', 'inactive', 'f7db9fe7fffa6c34bf85b2ef6d3b4ae9', 0, NULL, NULL),
(122, 'Hamza', 'hamza@gmail.com', '3740514536086', '$2y$10$bHSRv64amm2u9C5YM/KV5ee8d9sNLNfHgwNpwesbVUsiJtKT4R9UK', 'Student', '2026-04-30 17:22:17', 'active', '4f4f68286312713090b46737d38a0291', 1, NULL, NULL),
(124, 'Rustam', 'ra7517733@gmail.com', '', '$2y$10$Kshm80YIVih0vjqX4KdkA.fpt4qJjt3Qwd3oUX/IWBAmUQkJyQ5Kq', 'Student', '2026-04-30 17:42:50', 'active', '56468db60c31117487ea06b1492606cf', 0, NULL, NULL),
(125, 'Ahmed2', 'Ahmed@gmail.com', '1310131446378', '$2y$10$CZJFWtBZt3zL2wsV9mJ3XuaqJmVAp6ghNeFIW3VPCRj3DAlMXz3om', 'Teacher', '2026-04-30 17:45:37', 'active', NULL, 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `exam_routine`
--
ALTER TABLE `exam_routine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exam` (`exam_id`),
  ADD KEY `fk_subject` (`subject_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`group_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ptm_bookings`
--
ALTER TABLE `ptm_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_child` (`child_id`),
  ADD KEY `fk_teacher` (`teacher_id`),
  ADD KEY `fk_booked_by` (`booked_by`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_student` (`student_id`),
  ADD KEY `fk_session` (`session_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `fk_students_user` (`user_id`),
  ADD KEY `fk_students_group` (`group_id`);

--
-- Indexes for table `student_groups`
--
ALTER TABLE `student_groups`
  ADD PRIMARY KEY (`group_id`),
  ADD UNIQUE KEY `group_name` (`group_name`),
  ADD UNIQUE KEY `group_short` (`group_short`);

--
-- Indexes for table `study_materials`
--
ALTER TABLE `study_materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `fk_subject_class` (`class_id`),
  ADD KEY `fk_subject_teacher` (`teacher_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `cnic` (`cnic`),
  ADD KEY `fk_teacher_department` (`department_id`);

--
-- Indexes for table `teacher_availability`
--
ALTER TABLE `teacher_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `exam_routine`
--
ALTER TABLE `exam_routine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ptm_bookings`
--
ALTER TABLE `ptm_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `student_groups`
--
ALTER TABLE `student_groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `study_materials`
--
ALTER TABLE `study_materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `teacher_availability`
--
ALTER TABLE `teacher_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_routine`
--
ALTER TABLE `exam_routine`
  ADD CONSTRAINT `fk_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ptm_bookings`
--
ALTER TABLE `ptm_bookings`
  ADD CONSTRAINT `fk_booked_by` FOREIGN KEY (`booked_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_child` FOREIGN KEY (`child_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `fk_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_group` FOREIGN KEY (`group_id`) REFERENCES `student_groups` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `study_materials`
--
ALTER TABLE `study_materials`
  ADD CONSTRAINT `study_materials_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `study_materials_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subject_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_subject_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teacher_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `teacher_availability`
--
ALTER TABLE `teacher_availability`
  ADD CONSTRAINT `teacher_availability_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  ADD CONSTRAINT `teacher_classes_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teacher_classes_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teacher_classes_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
