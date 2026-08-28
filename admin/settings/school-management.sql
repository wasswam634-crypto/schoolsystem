-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2026 at 11:53 AM
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
-- Database: `school-management`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_groups`
--

CREATE TABLE `academic_groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_type` enum('Class','Course','Department') DEFAULT 'Class',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_groups`
--

INSERT INTO `academic_groups` (`group_id`, `group_name`, `group_type`, `status`, `created_at`) VALUES
(1, 's6', 'Class', 'Active', '2026-07-25 20:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `academic_periods`
--

CREATE TABLE `academic_periods` (
  `period_id` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `period_name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Active','Closed') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_periods`
--

INSERT INTO `academic_periods` (`period_id`, `academic_year`, `period_name`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, '2026', 'Term 1', '2026-02-01', '2026-05-01', 'Closed', '2026-07-25 09:58:52'),
(2, '2026', 'semester 2', '2026-10-10', '2026-12-10', 'Closed', '2026-07-27 10:36:17'),
(3, '2026', 'semester 1', '2026-01-01', '2026-04-30', 'Closed', '2026-07-27 13:55:25'),
(4, '2026', 'semester 2', '2026-09-03', '2026-12-03', 'Active', '2026-07-27 14:23:11');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('active','hidden') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `status`, `created_at`) VALUES
(1, 'neeeeeeeews', 'tooooooooooooodaaaaaaaaaay', 'active', '2026-07-19 04:32:46'),
(3, 'neeeeeeeeews', 'cccccccccccc', 'active', '2026-07-20 09:16:00');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Sick','Excused') NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `student_id`, `attendance_date`, `status`, `recorded_by`, `created_at`) VALUES
(1, 5, '2026-07-23', 'Present', 1, '2026-07-23 08:04:15'),
(2, 6, '2026-07-23', 'Present', 1, '2026-07-23 08:04:15'),
(3, 3, '2026-07-23', 'Present', 1, '2026-07-23 08:04:15'),
(4, 7, '2026-07-23', 'Present', 1, '2026-07-23 08:04:15'),
(5, 4, '2026-07-23', 'Present', 1, '2026-07-23 08:04:15');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `created_at`) VALUES
(1, 1, 'Recorded fee payment for student ID 5', '2026-07-24 07:50:52'),
(2, 1, 'Recorded fee payment for student ID 9', '2026-07-24 08:53:28'),
(3, 1, 'Recorded fee payment for student ID 5', '2026-07-24 09:36:48'),
(4, 1, 'Recorded fee payment for student ID 5', '2026-07-25 07:10:57'),
(5, 1, 'Updated fee payment for student ID 9', '2026-07-25 08:06:49'),
(6, 1, 'Updated fee payment for student ID 5', '2026-07-25 09:25:02'),
(7, 1, 'Created academic group s6', '2026-07-25 20:24:17'),
(8, 1, 'Opened academic period 2026 semester 2', '2026-07-27 10:36:17'),
(9, 1, 'Created fee item tuition', '2026-07-27 10:38:15');

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `faculty_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`faculty_id`, `name`, `description`, `created_at`) VALUES
(2, 'Martin', '\r\nttttttttt', '2026-07-19 05:15:32');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `fee_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) DEFAULT 0.00,
  `term` varchar(20) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`fee_id`, `student_id`, `amount_due`, `amount_paid`, `balance`, `term`, `academic_year`, `payment_date`, `created_at`) VALUES
(1, 5, 100000.00, 100000.00, 0.00, 'Term 1', '2026', '2026-07-25', '2026-07-24 07:50:52'),
(2, 9, 1000000.00, 1000000.00, 0.00, 'Term 1', '2026', '2026-07-25', '2026-07-24 08:53:28'),
(3, 5, 10000000.00, 4000.00, 9996000.00, 'Term 1', '2026', '2026-01-01', '2026-07-24 09:36:48'),
(4, 5, 9996000.00, 9996000.00, 0.00, 'Term 1', '2026', '2026-09-07', '2026-07-25 07:10:57');

-- --------------------------------------------------------

--
-- Table structure for table `fee_items`
--

CREATE TABLE `fee_items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_items`
--

INSERT INTO `fee_items` (`item_id`, `item_name`, `description`, `status`, `created_at`) VALUES
(1, 'tuition', '', 'Active', '2026-07-27 10:38:15');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `payment_id` int(11) NOT NULL,
  `student_fee_id` int(11) NOT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_structure`
--

CREATE TABLE `fee_structure` (
  `structure_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `gallery_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`gallery_id`, `title`, `image`, `uploaded_at`) VALUES
(1, 'tttttttttttttttt', '1784433511_ui2.jpg', '2026-07-19 03:58:31');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `mark_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `period_id` int(11) NOT NULL,
  `marks` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`mark_id`, `student_id`, `subject_id`, `period_id`, `marks`) VALUES
(25, 5, 1, 2, 44),
(26, 5, 2, 2, 89),
(27, 5, 3, 2, 90),
(28, 9, 1, 2, 77),
(29, 9, 2, 2, 90),
(30, 9, 3, 2, 90),
(34, 5, 1, 3, 89),
(35, 5, 2, 3, 89),
(36, 5, 3, 3, 89),
(37, 6, 1, 3, 78),
(38, 6, 2, 3, 90),
(39, 6, 3, 3, 78),
(40, 7, 1, 3, 90),
(41, 7, 2, 3, 90),
(42, 7, 3, 3, 77),
(43, 4, 1, 3, 67),
(44, 4, 2, 3, 90),
(45, 4, 3, 3, 78),
(46, 9, 1, 3, 89),
(47, 9, 2, 3, 89),
(48, 9, 3, 3, 90),
(49, 5, 1, 4, 90),
(50, 5, 2, 4, 90),
(51, 5, 3, 4, 90);

-- --------------------------------------------------------

--
-- Table structure for table `period_closing_settings`
--

CREATE TABLE `period_closing_settings` (
  `id` int(11) NOT NULL,
  `old_period_id` int(11) DEFAULT NULL,
  `new_period_id` int(11) DEFAULT NULL,
  `carry_balance` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `marks` int(11) NOT NULL,
  `term` varchar(20) DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE `school_settings` (
  `id` int(11) NOT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `motto` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `theme` varchar(50) DEFAULT 'modern',
  `primary_color` varchar(20) DEFAULT '#0d6efd',
  `secondary_color` varchar(20) DEFAULT '#198754',
  `accent_color` varchar(20) DEFAULT '#ffc107',
  `font_family` varchar(100) DEFAULT 'Arial'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_settings`
--

INSERT INTO `school_settings` (`id`, `school_name`, `motto`, `address`, `phone`, `email`, `logo`, `theme`, `primary_color`, `secondary_color`, `accent_color`, `font_family`) VALUES
(1, 'LIRA UNI', 'A CENTER FOR EXCELLENCEwsws', '123345', '0750503396', 'wasswam@gmail.com', 'logo_6a632a24bd1bb3.28320251.png', 'classic', '#0B3D91', '#FFD700', '#000000', 'Arial');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `reg_no` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `class` varchar(20) DEFAULT NULL,
  `stream` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `nationality` varchar(50) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `parent_contact` varchar(20) DEFAULT NULL,
  `parent_name` varchar(100) DEFAULT NULL,
  `parent_email` varchar(100) DEFAULT NULL,
  `parent_address` text DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `medical_condition` text DEFAULT NULL,
  `previous_school` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `reg_no`, `full_name`, `gender`, `class`, `stream`, `dob`, `admission_date`, `status`, `nationality`, `religion`, `parent_contact`, `parent_name`, `parent_email`, `parent_address`, `occupation`, `blood_group`, `allergies`, `medical_condition`, `previous_school`, `notes`, `photo`, `user_id`, `group_id`) VALUES
(3, '234897/mn', 'wasswa martin', 'm', '23', 'd', '0000-00-00', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL),
(4, '25/u/2956/Lcs', 'wasswa Martinz', 'Male', 's6', 'b4', NULL, NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, '234444', 'kato mark', 'Female', 's6', 'm1', NULL, NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, '25/u/2956/ics2', 'mathuas muwonge', 'Male', 's6', 'm1', '2002-10-31', '2027-11-21', 'Active', 'Ugandan', 'catholic', '070000000', 'anthony', 'wasswam634@gmail.com', 'wwwwwwwwwwwwwwwwwwwww', 'eeeeeeeeeeee', 'a', 'no', 'fine', 'wmmm p/s', 'not yet', '1784751870_images (1).jpeg', NULL, NULL),
(7, '2344445', 'Wasswa Martin skelly', 'Female', 's6', 'b4', '2010-10-10', '2020-11-11', 'Active', 'kenyan', 'muslim', '0711111111', 'Wasswa Martin', 'wasswam634@gmail.com', 'eeeeeeeee', 'cook', 'ab', 'noo', 'fine', 'wmmm p/s', 'waaaaaaaaaaa', '1784752209_images.jpeg', NULL, NULL),
(9, '25/u/23444', 'kenferd', 'Male', 'cs', 'b4', '2026-04-01', '2025-01-01', 'Active', 'Ugandan', 'catholic', '070000000', 'Wasswa Martin', 'wasswam634@gmail.com', 'kampala', 'cook', 'o', 'noo', 'fine', 'wmmm p/s', 'forms', '1784883078_images (2).jpeg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_fees`
--

CREATE TABLE `student_fees` (
  `student_fee_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `previous_balance` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) DEFAULT 0.00,
  `status` enum('Pending','Partial','Cleared') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `group_id`, `subject_code`, `subject_name`) VALUES
(1, NULL, '1ct', 'computer studies'),
(2, NULL, 'math01', 'math'),
(3, NULL, 'networking01', 'networking');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject_speciality` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `full_name`, `phone`, `email`, `subject_speciality`, `user_id`) VALUES
(2, 'kakante edison', '0750503396', 'wasswam634@gmail.com', NULL, 2),
(3, 'Wasswa Martin', '0750503396', 'wasswam634@gmail.com', 'physics', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `timetable_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `term` varchar(50) DEFAULT NULL,
  `period_name` varchar(50) DEFAULT NULL,
  `class` varchar(20) DEFAULT NULL,
  `day` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `lesson_type` varchar(50) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `period_time` varchar(50) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `teacher` varchar(100) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timetables`
--

INSERT INTO `timetables` (`timetable_id`, `period_id`, `academic_year`, `term`, `period_name`, `class`, `day`, `start_time`, `end_time`, `lesson_type`, `subject_id`, `teacher_id`, `period_time`, `subject`, `teacher`, `room`) VALUES
(1, 4, '2026', NULL, 'Period 1', 's6', 'Monday', '06:55:00', '09:30:00', 'Normal Lesson', 1, 2, NULL, NULL, NULL, '2'),
(2, 4, '2026', NULL, 'Period 1', 's6', 'Monday', '10:30:00', '01:40:00', 'Normal Lesson', 1, 2, NULL, NULL, NULL, '2');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `failed_attempts`, `locked_until`) VALUES
(1, 'admin', 'admin123', 'admin', 0, NULL),
(2, 'teacher1', 'teacher123', 'teacher', 0, NULL),
(3, 'student1', 'student123', 'student', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vacancies`
--

CREATE TABLE `vacancies` (
  `vacancy_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `deadline` date NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vacancies`
--

INSERT INTO `vacancies` (`vacancy_id`, `title`, `department`, `description`, `deadline`, `status`, `created_at`) VALUES
(1, 'cook', 'kitchen', 'wwwwwwwwwwwwwwww', '2320-12-12', 'open', '2026-07-19 05:12:01'),
(2, 'vaaaaaaaaaaaaaaaaaaaa', 'vaaaaaaaaaaaaaaaaaaaa', 'vaaaaaaaaaaaaaaaaaaaavaaaaaaaaaaaaaaaaaaaavaaaaaaaaaaaaaaaaaaaa', '2222-02-22', 'open', '2026-07-19 11:43:52');

-- --------------------------------------------------------
9
--
-- Table structure for table `website_sections`
--

CREATE TABLE `website_sections` (
  `section_id` int(11) NOT NULL,
  `page_name` varchar(50) NOT NULL,
  `section_key` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_sections`
--

INSERT INTO `website_sections` (`section_id`, `page_name`, `section_key`, `title`, `content`, `image`, `created_at`) VALUES
(1, 'academics', 'intro', 'Academicssssssssssssssssssss', 'Academicssssssssssssssssssss', NULL, '2026-07-15 11:23:34'),
(2, 'academics', 'nursery', 'Nursery Section', '', NULL, '2026-07-15 11:23:34'),
(3, 'academics', 'primary', 'Primary Section', '', NULL, '2026-07-15 11:23:34'),
(4, 'academics', 'olevel', 'O-Level Section', '', NULL, '2026-07-15 11:23:34'),
(5, 'academics', 'alevel', 'A-Level Section', '', NULL, '2026-07-15 11:23:34'),
(6, 'academics', 'cocurricular', 'Co-Curricular Activities', '', NULL, '2026-07-15 11:23:34'),
(7, 'academics', 'intro', 'Academicssssssssssssssssssss', 'Academicssssssssssssssssssss', NULL, '2026-07-15 11:24:48'),
(8, 'academics', 'nursery', 'Nursery Section', '', NULL, '2026-07-15 11:24:48'),
(9, 'academics', 'primary', 'Primary Section', '', NULL, '2026-07-15 11:24:48'),
(10, 'academics', 'olevel', 'O-Level Section', '', NULL, '2026-07-15 11:24:48'),
(11, 'academics', 'alevel', 'A-Level Section', '', NULL, '2026-07-15 11:24:48'),
(12, 'academics', 'cocurricular', 'Co-Curricular Activities', '', NULL, '2026-07-15 11:24:48'),
(13, 'admissions', 'intro', 'Admissions', 'Admission RequirementsAdmission RequirementsAdmission RequirementsAdmission RequirementsAdmission Requirements', NULL, '2026-07-15 11:25:58'),
(14, 'admissions', 'requirements', 'Admission Requirements', 'Admission RequirementsAdmission RequirementsAdmission RequirementsAdmission RequirementsAdmission Requirements', NULL, '2026-07-15 11:25:58'),
(15, 'admissions', 'documents', 'Required Documents', 'Required DocumentsRequired DocumentsRequired DocumentsRequired Documents', NULL, '2026-07-15 11:25:58'),
(16, 'admissions', 'procedure', 'Application Procedure', 'Application ProcedureApplication ProcedureApplication ProcedureApplication Procedure', NULL, '2026-07-15 11:25:58'),
(17, 'admissions', 'fees', 'Fees Information', 'Fees InformationFees InformationFees InformationFees InformationFees Information', NULL, '2026-07-15 11:25:58'),
(18, 'admissions', 'contact', 'Admissions Contact', 'Admissions ContactAdmissions ContactAdmissions ContactAdmissions Contact', NULL, '2026-07-15 11:25:58'),
(19, 'admissions', 'requirements', 'Admission Requirements', 'Admission RequirementsAdmission RequirementsAdmission RequirementsAdmission RequirementsAdmission Requirements', NULL, '2026-07-18 17:28:40'),
(20, 'admissions', 'procedure', 'Application Procedure', 'Application ProcedureApplication ProcedureApplication ProcedureApplication Procedure', NULL, '2026-07-18 17:28:40'),
(21, 'admissions', 'fees', 'Fees Information', 'Fees InformationFees InformationFees InformationFees InformationFees Information', NULL, '2026-07-18 17:28:40'),
(22, 'admissions', 'application', 'How to Apply', 'Enter application instructions here.', NULL, '2026-07-18 17:28:40'),
(23, 'home', 'hero', 'YUOR NUMBER ONE CHOICEKENMFRED', 'A ROAD TO EXCELLENCE RRTGY', '1784885495_images (2).jpeg', '2026-07-18 18:37:38'),
(24, 'about', 'intro', 'About Our WM SCHOOLS    NOW', 'Welcome to our school. We are committed to providing quality education. and children excellence in cocurricular activities rrrrrrrrrrrrrrrrrrr', NULL, '2026-07-18 21:38:11'),
(25, 'about', 'mission', 'mmmission', 'thats the missionbbbbbbbbbbbbbbbbb', NULL, '2026-07-18 21:38:11'),
(26, 'about', 'vision', 'vissionz', 'vvvvvvvvvvvvvvvvvvvvoooooooosssssssiiiiiioooooooonnnn', NULL, '2026-07-18 21:38:11'),
(27, 'about', 'values', 'vvvvvvv', 'valueeeeeeeeeeeeeeeees', NULL, '2026-07-18 21:38:11'),
(28, 'about', 'history', 'hiiiiiiiiisssssssstttttttooooory', 'fffffffffffffffffffffffff', NULL, '2026-07-18 21:38:11'),
(29, 'about', 'requirements', 'OUR REQS', 'WWWWWWWWWWWWWAAAAAAAAAAAASSSSSSSSSSWWWWWWWAAAAAAAA', NULL, '2026-07-19 03:07:07'),
(30, 'about', 'documents', 'DOCUMENTS', 'THIIIIIIIIIS IIIIIIIISSSSSSSS OUR       CONTENT', NULL, '2026-07-19 03:07:07'),
(31, 'about', 'procedure', 'PPPPPPPPPRRRRRRRRRROOOOOO', 'THIIIIIIIIIS IIIIIIIISSSSSSSS OUR       CONTENT', NULL, '2026-07-19 03:07:07'),
(32, 'about', 'fees', 'FEES STRUCTURE', 'THIIIIIIIIIS IIIIIIIISSSSSSSS OUR       CONTENT            WE CHARGE 1MILLION', NULL, '2026-07-19 03:07:07'),
(33, 'about', 'contact', 'CONTACT US VIA ', '0700000000. 075555555555', NULL, '2026-07-19 03:07:07'),
(34, 'academics', 'departments', 'Academicssssssssssssssssssss', 'Academicssssssssssssssssssss', NULL, '2026-07-19 03:50:29'),
(35, 'academics', 'programs', 'Academicssssssssssssssssssss', 'Academicssssssssssssssssssss', NULL, '2026-07-19 03:50:29'),
(36, 'academics', 'learning', 'Academicssssssssssssssssssss', 'Academicssssssssssssssssssss', NULL, '2026-07-19 03:50:29'),
(37, 'academics', 'research', 'Academicssssssssssssssssssss', 'Academicssssssssssssssssssss', NULL, '2026-07-19 03:50:29'),
(38, 'university_home', 'hero', 'Welcome to Our University', 'Quality education, research and innovation for a better future.', NULL, '2026-07-19 07:09:23'),
(39, 'university_home', 'faculties', 'Faculties', 'Our university has different faculties offering various programmes.', NULL, '2026-07-19 07:09:23'),
(40, 'university_home', 'departments', 'Departments', 'Explore our academic departments and areas of specialization.', NULL, '2026-07-19 07:09:23'),
(41, 'university_home', 'programmes', 'Academic Programmes', 'We offer certificate, diploma, undergraduate and postgraduate programmes.', NULL, '2026-07-19 07:09:23'),
(42, 'university_home', 'research', 'Research and Innovation', 'Our university promotes research and innovation.', NULL, '2026-07-19 07:09:23'),
(43, 'university_home', 'campus_life', 'Campus Life', 'Experience a vibrant learning environment.', NULL, '2026-07-19 07:09:23'),
(44, 'university_home', 'admissions', 'Admissions', 'Apply and become part of our university community.', NULL, '2026-07-19 07:09:23'),
(45, 'university_home', 'call_to_action', 'Join Our University', 'Start your academic journey with us.', NULL, '2026-07-19 07:09:23'),
(46, 'university_home', 'hero', 'Welcome to Our University', 'Quality education, research and innovation.', NULL, '2026-07-19 07:15:58'),
(47, 'university_home', 'faculties', 'Our Faculties', 'Explore our faculties and schools.', NULL, '2026-07-19 07:15:58'),
(48, 'university_home', 'departments', 'Academic Departments', 'Discover our departments.', NULL, '2026-07-19 07:15:58'),
(49, 'university_home', 'programmes', 'Academic Programmes', 'Certificate, diploma, undergraduate and postgraduate programmes.', NULL, '2026-07-19 07:15:58'),
(50, 'university_home', 'research', 'Research and Innovation', 'Advancing knowledge through research.', NULL, '2026-07-19 07:15:58'),
(51, 'university_home', 'campus_life', 'Campus Life', 'Experience learning beyond the classroom.', NULL, '2026-07-19 07:15:58'),
(52, 'university_home', 'admissions', 'Admissions', 'Apply and join our university.', NULL, '2026-07-19 07:15:58'),
(53, 'university_home', 'call_to_action', 'Join Our University', 'Begin your academic journey with us.', NULL, '2026-07-19 07:15:58'),
(54, 'home', 'principal_message', 'TTDYUCDI', 'FGCGDHCJDP', '1784885495_images (1).jpeg', '2026-07-20 09:07:59'),
(55, 'home', 'why_choose', 'bbbbbbbbbbbbb', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '', '2026-07-20 09:07:59'),
(56, 'home', 'academics', 'bbbbbbbbbbbbb', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '', '2026-07-20 09:07:59'),
(57, 'home', 'cta', 'bbbbbbbbbbbbb2', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb2222', '', '2026-07-20 09:07:59'),
(58, 'home', 'principal_message', 'TTDYUCDI', 'FGCGDHCJDP', '1784885495_images (1).jpeg', '2026-07-20 09:12:38'),
(59, 'home', 'why_choose', 'bbbbbbbbbbbbb', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '', '2026-07-20 09:12:38'),
(60, 'home', 'academics', 'bbbbbbbbbbbbb', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '', '2026-07-20 09:12:38'),
(61, 'home', 'cta', 'bbbbbbbbbbbbb2', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb2222', '', '2026-07-20 09:12:38');

-- --------------------------------------------------------

--
-- Table structure for table `website_sliders`
--

CREATE TABLE `website_sliders` (
  `slider_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `website_social_links`
--

CREATE TABLE `website_social_links` (
  `social_id` int(11) NOT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_social_links`
--

INSERT INTO `website_social_links` (`social_id`, `platform`, `url`, `icon`) VALUES
(1, 'google', 'wwwww,ggg.c0m', '');

-- --------------------------------------------------------

--
-- Table structure for table `website_staff`
--

CREATE TABLE `website_staff` (
  `staff_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_staff`
--

INSERT INTO `website_staff` (`staff_id`, `name`, `position`, `bio`, `photo`, `created_at`) VALUES
(6, 'Martin', 'vaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbb\r\n', '', '2026-07-19 12:34:04'),
(7, 'Martin', 'vaaaaaaaaaaaaaaaaaaaa', 'nnnnnnnnnnn\r\n', '1784464463_WIN_20260703_16_45_53_Pro.jpg', '2026-07-19 12:34:23'),
(8, 'NMJY', 'vaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbb\r\n', '1784465993_WIN_20260703_16_45_53_Pro.jpg', '2026-07-19 12:59:53');

-- --------------------------------------------------------

--
-- Table structure for table `website_statistics`
--

CREATE TABLE `website_statistics` (
  `stat_id` int(11) NOT NULL,
  `stat_title` varchar(100) DEFAULT NULL,
  `stat_value` varchar(100) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_statistics`
--

INSERT INTO `website_statistics` (`stat_id`, `stat_title`, `stat_value`, `icon`) VALUES
(1, 'students', '30000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `website_testimonials`
--

CREATE TABLE `website_testimonials` (
  `testimonial_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_testimonials`
--

INSERT INTO `website_testimonials` (`testimonial_id`, `name`, `role`, `message`, `photo`) VALUES
(1, 'Wasswa Martin', 'alumini', 'yaaaaaaaaaaaaaa', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `website_vacancies`
--

CREATE TABLE `website_vacancies` (
  `vacancy_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_groups`
--
ALTER TABLE `academic_groups`
  ADD PRIMARY KEY (`group_id`);

--
-- Indexes for table `academic_periods`
--
ALTER TABLE `academic_periods`
  ADD PRIMARY KEY (`period_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`faculty_id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `fee_items`
--
ALTER TABLE `fee_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `student_fee_id` (`student_fee_id`);

--
-- Indexes for table `fee_structure`
--
ALTER TABLE `fee_structure`
  ADD PRIMARY KEY (`structure_id`),
  ADD KEY `period_id` (`period_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`gallery_id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`mark_id`),
  ADD UNIQUE KEY `unique_student_subject_period` (`student_id`,`subject_id`,`period_id`);

--
-- Indexes for table `period_closing_settings`
--
ALTER TABLE `period_closing_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `reg_no` (`reg_no`);

--
-- Indexes for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD PRIMARY KEY (`student_fee_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `period_id` (`period_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`timetable_id`),
  ADD KEY `fk_timetables_period_2026` (`period_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vacancies`
--
ALTER TABLE `vacancies`
  ADD PRIMARY KEY (`vacancy_id`);

--
-- Indexes for table `website_sections`
--
ALTER TABLE `website_sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `website_sliders`
--
ALTER TABLE `website_sliders`
  ADD PRIMARY KEY (`slider_id`);

--
-- Indexes for table `website_social_links`
--
ALTER TABLE `website_social_links`
  ADD PRIMARY KEY (`social_id`);

--
-- Indexes for table `website_staff`
--
ALTER TABLE `website_staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `website_statistics`
--
ALTER TABLE `website_statistics`
  ADD PRIMARY KEY (`stat_id`);

--
-- Indexes for table `website_testimonials`
--
ALTER TABLE `website_testimonials`
  ADD PRIMARY KEY (`testimonial_id`);

--
-- Indexes for table `website_vacancies`
--
ALTER TABLE `website_vacancies`
  ADD PRIMARY KEY (`vacancy_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_groups`
--
ALTER TABLE `academic_groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `academic_periods`
--
ALTER TABLE `academic_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fee_items`
--
ALTER TABLE `fee_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_structure`
--
ALTER TABLE `fee_structure`
  MODIFY `structure_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `gallery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `mark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `period_closing_settings`
--
ALTER TABLE `period_closing_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_settings`
--
ALTER TABLE `school_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_fees`
--
ALTER TABLE `student_fees`
  MODIFY `student_fee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `timetable_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vacancies`
--
ALTER TABLE `vacancies`
  MODIFY `vacancy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `website_sections`
--
ALTER TABLE `website_sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `website_sliders`
--
ALTER TABLE `website_sliders`
  MODIFY `slider_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `website_social_links`
--
ALTER TABLE `website_social_links`
  MODIFY `social_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `website_staff`
--
ALTER TABLE `website_staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `website_statistics`
--
ALTER TABLE `website_statistics`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `website_testimonials`
--
ALTER TABLE `website_testimonials`
  MODIFY `testimonial_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `website_vacancies`
--
ALTER TABLE `website_vacancies`
  MODIFY `vacancy_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`student_fee_id`) REFERENCES `student_fees` (`student_fee_id`);

--
-- Constraints for table `fee_structure`
--
ALTER TABLE `fee_structure`
  ADD CONSTRAINT `fee_structure_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `academic_periods` (`period_id`),
  ADD CONSTRAINT `fee_structure_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `fee_items` (`item_id`);

--
-- Constraints for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD CONSTRAINT `student_fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_fees_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `academic_periods` (`period_id`);

--
-- Constraints for table `timetables`
--
ALTER TABLE `timetables`
  ADD CONSTRAINT `fk_timetable_period` FOREIGN KEY (`period_id`) REFERENCES `academic_periods` (`period_id`),
  ADD CONSTRAINT `fk_timetables_period_2026` FOREIGN KEY (`period_id`) REFERENCES `academic_periods` (`period_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
