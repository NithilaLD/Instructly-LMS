-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2026 at 04:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `lms_admin`
--

CREATE TABLE `lms_admin` (
  `a_id` int(20) NOT NULL,
  `a_name` varchar(200) NOT NULL,
  `a_uname` varchar(200) NOT NULL,
  `a_email` varchar(200) NOT NULL,
  `a_pwd` varchar(200) NOT NULL,
  `a_dpic` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_admin`
--

INSERT INTO `lms_admin` (`a_id`, `a_name`, `a_uname`, `a_email`, `a_pwd`, `a_dpic`) VALUES
(1, 'Dulan Nithila Liyanarachchi', 'Admin', 'nithila0411@gmail.com', '2df861c1a05c06a2c0c580bc15e1860c7f63c752', '1.png');

-- --------------------------------------------------------

--
-- Table structure for table `lms_answers`
--

CREATE TABLE `lms_answers` (
  `an_id` int(20) NOT NULL,
  `q_code` varchar(200) NOT NULL,
  `an_code` varchar(200) NOT NULL,
  `cc_id` varchar(200) NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `c_code` varchar(200) NOT NULL,
  `c_name` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `q_id` varchar(200) NOT NULL,
  `q_details` longblob NOT NULL,
  `ans_details` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_answers`
--

INSERT INTO `lms_answers` (`an_id`, `q_code`, `an_code`, `cc_id`, `c_id`, `c_code`, `c_name`, `i_id`, `q_id`, `q_details`, `ans_details`) VALUES
(14, '1G5R7-09415', '8YOL3-09573', '20', '18', 'Unit1', 'Statistics', '150', '20', 0x576861742069732035202b20353f3c62723e0d0a0d0a57686174206973203130202b2031303f, 0x312e2031300d0a322e203230);

-- --------------------------------------------------------

--
-- Table structure for table `lms_certs`
--

CREATE TABLE `lms_certs` (
  `cert_id` int(20) NOT NULL,
  `en_id` varchar(200) NOT NULL,
  `s_id` varchar(200) NOT NULL,
  `s_regno` varchar(200) NOT NULL,
  `s_name` varchar(200) NOT NULL,
  `s_unit_code` varchar(200) NOT NULL,
  `s_unit_name` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `i_name` varchar(200) NOT NULL,
  `en_date` varchar(200) NOT NULL,
  `date_generated` timestamp(4) NOT NULL DEFAULT current_timestamp(4) ON UPDATE current_timestamp(4)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_certs`
--

INSERT INTO `lms_certs` (`cert_id`, `en_id`, `s_id`, `s_regno`, `s_name`, `s_unit_code`, `s_unit_name`, `i_id`, `i_name`, `en_date`, `date_generated`) VALUES
(20, '35', '409', 'R9MJQ63894', 'Ranjith Piyanada Liyanarachchi', 'Unit1', 'Statistics', '150', 'Thanuja Geethanjalee Ilayperuma', '09 Oct 2025 5:36pm', '2026-05-03 09:45:19.9110');

-- --------------------------------------------------------

--
-- Table structure for table `lms_course`
--

CREATE TABLE `lms_course` (
  `c_id` int(20) NOT NULL,
  `cc_id` varchar(200) NOT NULL,
  `a_id` varchar(200) NOT NULL,
  `i_id` varchar(200) DEFAULT NULL,
  `c_code` varchar(200) NOT NULL,
  `c_name` varchar(200) NOT NULL,
  `c_category` varchar(200) NOT NULL,
  `c_desc` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_course`
--

INSERT INTO `lms_course` (`c_id`, `cc_id`, `a_id`, `i_id`, `c_code`, `c_name`, `c_category`, `c_desc`) VALUES
(18, '20', '1', NULL, 'Unit1', 'Statistics', 'AGWHE-93628', 0x54686973206973207468652031737420756e6974206f6620746865204d6174687320436f7572736520776869636820696e636c756465642074686520626173696373206f6620737461746973746963732e),
(20, '20', '1', NULL, 'Unit2', 'Theory', 'AGWHE-93628', 0x676667666767666766),
(21, '20', '1', NULL, 'Unit3', 'Theo', 'AGWHE-93628', 0x7364736464736473),
(22, '22', '1', NULL, 'sdsdsd', 'rfdfs', 'EO6D8-34769', 0x7364736464736473),
(23, '22', '1', NULL, 'fdfdfd', 'gdcfddf', 'EO6D8-34769', 0x66647366646664);

-- --------------------------------------------------------

--
-- Table structure for table `lms_course_categories`
--

CREATE TABLE `lms_course_categories` (
  `cc_id` int(20) NOT NULL,
  `cc_name` longtext NOT NULL,
  `cc_dept_head` varchar(200) NOT NULL,
  `cc_code` varchar(200) NOT NULL,
  `cc_desc` longblob NOT NULL,
  `cc_dpic` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_course_categories`
--

INSERT INTO `lms_course_categories` (`cc_id`, `cc_name`, `cc_dept_head`, `cc_code`, `cc_desc`, `cc_dpic`) VALUES
(20, 'Maths Course', 'Thanuja Geethanjalee Ilayperuma', 'AGWHE-93628', 0x54686973206973206d617468732072656c6174656420436f75727365, 'AGWHE-93628.png'),
(22, 'Science Course', 'Thanuja Geethanjalee Ilayperuma', 'EO6D8-34769', 0x6c3b6b6b6b2c, 'EO6D8-34769.png');

-- --------------------------------------------------------

--
-- Table structure for table `lms_enrollments`
--

CREATE TABLE `lms_enrollments` (
  `en_id` int(20) NOT NULL,
  `s_name` varchar(200) NOT NULL,
  `s_regno` varchar(200) NOT NULL,
  `s_unit_code` varchar(200) NOT NULL,
  `s_unit_name` varchar(200) NOT NULL,
  `i_name` varchar(200) NOT NULL,
  `cc_id` varchar(200) NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `s_id` varchar(200) NOT NULL,
  `s_course` varchar(200) NOT NULL,
  `en_date` timestamp(4) NOT NULL DEFAULT current_timestamp(4) ON UPDATE current_timestamp(4)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_enrollments`
--

INSERT INTO `lms_enrollments` (`en_id`, `s_name`, `s_regno`, `s_unit_code`, `s_unit_name`, `i_name`, `cc_id`, `c_id`, `i_id`, `s_id`, `s_course`, `en_date`) VALUES
(35, 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'Unit1', 'Statistics', 'Thanuja Geethanjalee Ilayperuma', '20', '18', '150', '409', 'AGWHE-93628', '2025-10-09 12:06:26.9330'),
(37, 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'Unit2', 'Theory', 'Thanuja Geethanjalee Ilayperuma', '20', '20', '150', '409', 'AGWHE-93628', '2026-05-05 09:31:04.8785'),
(38, 'hffgfg', 'STD0BQHU19640', 'Unit1', 'Statistics', 'Thanuja Geethanjalee Ilayperuma', '20', '18', '150', '432', 'AGWHE-93628', '2026-05-05 14:30:08.6859'),
(39, 'fffffff', 'V5KA075482', 'Unit1', 'Statistics', 'Thanuja Geethanjalee Ilayperuma', '20', '18', '150', '441', 'AGWHE-93628', '2026-05-05 16:46:56.2486');

-- --------------------------------------------------------

--
-- Table structure for table `lms_instructor`
--

CREATE TABLE `lms_instructor` (
  `i_id` int(20) NOT NULL,
  `i_number` varchar(200) NOT NULL,
  `i_name` varchar(200) NOT NULL,
  `i_email` varchar(200) NOT NULL,
  `i_phone` varchar(200) NOT NULL,
  `i_pwd` varchar(200) NOT NULL,
  `i_dpic` varchar(200) NOT NULL,
  `i_bio` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_instructor`
--

INSERT INTO `lms_instructor` (`i_id`, `i_number`, `i_name`, `i_email`, `i_phone`, `i_pwd`, `i_dpic`, `i_bio`) VALUES
(150, 'INSMA96L49326', 'Thanuja Geethanjalee Ilayperuma', 'thanuilayperuma@gmail.com', '0713909972', '6f2672b50da926c80354a29355c1e8e0689d5b80', 'INSMA96L49326.png', NULL),
(174, 'INSP0JQY78374', 'kdshkolds', 'c@g.com', '2222222', '5bda51196610a70b305de98845e8190183a64eb5', 'INSP0JQY78374.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lms_paid_study_materials`
--

CREATE TABLE `lms_paid_study_materials` (
  `psm_id` int(20) NOT NULL,
  `ls_id` int(20) NOT NULL,
  `c_code` varchar(200) NOT NULL,
  `sm_number` varchar(200) NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `cc_id` varchar(200) NOT NULL,
  `c_name` varchar(200) NOT NULL,
  `c_category` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `i_name` varchar(200) NOT NULL,
  `p_method` varchar(200) NOT NULL,
  `p_code` varchar(200) NOT NULL,
  `p_amt` varchar(200) NOT NULL,
  `p_date_paid` timestamp(4) NOT NULL DEFAULT current_timestamp(4) ON UPDATE current_timestamp(4),
  `s_id` varchar(200) NOT NULL,
  `s_name` varchar(200) NOT NULL,
  `s_regno` varchar(200) NOT NULL,
  `p_verification_status` varchar(50) NOT NULL DEFAULT 'pending',
  `p_rejection_reason` text DEFAULT NULL,
  `p_verified_date` datetime DEFAULT NULL,
  `verified_by_id` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_paid_study_materials`
--

INSERT INTO `lms_paid_study_materials` (`psm_id`, `ls_id`, `c_code`, `sm_number`, `c_id`, `cc_id`, `c_name`, `c_category`, `i_id`, `i_name`, `p_method`, `p_code`, `p_amt`, `p_date_paid`, `s_id`, `s_name`, `s_regno`, `p_verification_status`, `p_rejection_reason`, `p_verified_date`, `verified_by_id`) VALUES
(31, 67, 'Unit1', 'O7F1Q-97436', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '200300400500', '1000', '2026-05-03 13:27:59.2764', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'verified', NULL, '2026-05-03 18:57:59', 150),
(33, 68, 'Unit1', '2WP9K-62459', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '656664646', '1000', '2026-05-06 14:59:00.4960', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'rejected', 'Invalid Code', '2026-05-06 20:29:00', NULL),
(34, 69, 'Unit1', 'P5GZT-72950', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '656566565', '1000', '2026-05-06 14:58:36.0454', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'verified', NULL, '2026-05-06 20:28:36', NULL),
(35, 68, 'Unit1', '2WP9K-62459', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '56565656', '1000', '2026-05-06 15:03:07.2048', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'rejected', 'Invalid Code', '2026-05-06 20:33:07', 150),
(36, 68, 'Unit1', '2WP9K-62459', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '665697979479479', '1000', '2026-05-06 15:04:55.8542', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'rejected', 'Invalid Code', '2026-05-06 20:34:55', NULL),
(37, 68, 'Unit1', '2WP9K-62459', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '979646545443', '1000', '2026-05-06 15:06:48.7717', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'rejected', 'Invalid Code', '2026-05-06 20:36:48', 150),
(38, 68, 'Unit1', '2WP9K-62459', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '10000000', '1000', '2026-05-06 16:02:00.6826', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'verified', NULL, '2026-05-06 21:32:00', NULL),
(39, 70, 'Unit1', 'REX12-54930', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '54545454545', '1000', '2026-05-07 12:59:34.2899', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'verified', NULL, '2026-05-07 18:29:34', NULL),
(40, 71, 'Unit2', 'EZS6X-23748', '20', '20', 'Theory', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '979997979', '1000', '2026-05-07 13:24:20.7525', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'rejected', 'hkjhkds', '2026-05-07 18:54:20', NULL),
(41, 71, 'Unit2', 'EZS6X-23748', '20', '20', 'Theory', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'Credit/Debit Card', '5545475854', '1000', '2026-05-07 13:25:29.7904', '409', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', 'verified', NULL, '2026-05-07 18:55:29', 150);

-- --------------------------------------------------------

--
-- Table structure for table `lms_questions`
--

CREATE TABLE `lms_questions` (
  `q_id` int(20) NOT NULL,
  `q_code` varchar(200) NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `cc_id` varchar(200) NOT NULL,
  `c_code` varchar(200) NOT NULL,
  `c_name` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `q_details` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_questions`
--

INSERT INTO `lms_questions` (`q_id`, `q_code`, `c_id`, `cc_id`, `c_code`, `c_name`, `i_id`, `q_details`) VALUES
(20, '1G5R7-09415', '18', '20', 'Unit1', 'Statistics', '150', 0x576861742069732035202b20353f0d0a57686174206973203130202b2031303f);

-- --------------------------------------------------------

--
-- Table structure for table `lms_results`
--

CREATE TABLE `lms_results` (
  `rs_id` int(20) NOT NULL,
  `rs_code` varchar(200) NOT NULL,
  `s_name` varchar(200) NOT NULL,
  `s_regno` varchar(200) NOT NULL,
  `s_id` varchar(200) NOT NULL,
  `s_unit_code` varchar(200) NOT NULL,
  `s_unit_name` varchar(200) NOT NULL,
  `i_name` varchar(200) NOT NULL,
  `cc_id` varchar(200) NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `c_eos_marks` varchar(200) NOT NULL,
  `c_cat1_marks` varchar(200) NOT NULL,
  `c_cat2_marks` varchar(200) NOT NULL,
  `c_date_added` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_results`
--

INSERT INTO `lms_results` (`rs_id`, `rs_code`, `s_name`, `s_regno`, `s_id`, `s_unit_code`, `s_unit_name`, `i_name`, `cc_id`, `c_id`, `i_id`, `c_eos_marks`, `c_cat1_marks`, `c_cat2_marks`, `c_date_added`) VALUES
(31, 'ART0L46538', 'Ranjith Piyanada Liyanarachchi', 'R9MJQ63894', '409', 'Unit1', 'Statistics', 'Thanuja Geethanjalee Ilayperuma', '20', '18', '150', '70', '20', '20', '2026-05-03 09:44:23.921908');

-- --------------------------------------------------------

--
-- Table structure for table `lms_student`
--

CREATE TABLE `lms_student` (
  `s_id` int(20) NOT NULL,
  `s_regno` varchar(200) NOT NULL,
  `s_course` varchar(2000) NOT NULL,
  `s_name` varchar(200) NOT NULL,
  `s_email` varchar(200) NOT NULL,
  `s_pwd` varchar(200) NOT NULL,
  `s_phoneno` varchar(200) NOT NULL,
  `s_dpic` varchar(200) NOT NULL,
  `s_bio` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_student`
--

INSERT INTO `lms_student` (`s_id`, `s_regno`, `s_course`, `s_name`, `s_email`, `s_pwd`, `s_phoneno`, `s_dpic`, `s_bio`) VALUES
(409, 'R9MJQ63894', 'Maths Course', 'Ranjith Piyanada Liyanarachchi', 'rpliyanarachchi@gmail.com', '6c998756a312266aa0332eabbc218fb33a2ef163', '0723424237', 'R9MJQ63894.jpg', ''),
(433, 'V4OCI47069', 'Maths Course', 'dsdssd', 'dsds@fdz.dsds', '1fc63c1b06bd1743c3af081951d9f81585e811c6', '5165665', 'V4OCI47069.png', NULL),
(440, '618NH12890', 'jhjhsaj', 'sdssds', 'g@h.com', 'd3b7d19d050d0259e4428a8d3f7dd281e974730f', '0000000000', '618NH12890.png', NULL),
(441, 'V5KA075482', 'jsjjsh', 'fffffff', 'h@h.com', 'c97a8711103072946a51d7bab54c933635cb6d2d', '8888888888', 'V5KA075482.png', NULL),
(442, '9YTRB68972', 'jhjhsaj', 'sdssds', 'g@h.com', 'd3b7d19d050d0259e4428a8d3f7dd281e974730f', '0000000000', '9YTRB68972.png', NULL),
(443, 'QBU4Z70486', 'jsjjsh', 'fffffff', 'h@h.com', 'c97a8711103072946a51d7bab54c933635cb6d2d', '8888888888', 'QBU4Z70486.png', NULL),
(444, 'ZDK6O70645', 'Maths Course', 'ccxcxcxcx', 'cc@fff.fff', '8484d95e5f2198988bfa72f525c97895615fb197', 'cxcxcxcxcx', 'ZDK6O70645.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lms_study_material`
--

CREATE TABLE `lms_study_material` (
  `ls_id` int(20) NOT NULL,
  `c_code` varchar(200) NOT NULL,
  `sm_number` varchar(200) NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `cc_id` varchar(200) NOT NULL,
  `c_name` varchar(200) NOT NULL,
  `c_category` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `i_name` varchar(200) NOT NULL,
  `sm_materials` longtext NOT NULL,
  `sm_price` varchar(200) NOT NULL,
  `payment_status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_study_material`
--

INSERT INTO `lms_study_material` (`ls_id`, `c_code`, `sm_number`, `c_id`, `cc_id`, `c_name`, `c_category`, `i_id`, `i_name`, `sm_materials`, `sm_price`, `payment_status`) VALUES
(67, 'Unit1', 'O7F1Q-97436', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'sm_69f745f793a443.61672621.pdf', '1000', 'Paid'),
(68, 'Unit1', '2WP9K-62459', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'sm_69fa1f446dc922.85291550.pdf', '1000', 'Paid'),
(69, 'Unit1', 'P5GZT-72950', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'sm_69fb51eb604397.68412508.pdf', '1000', 'Paid'),
(70, 'Unit1', 'REX12-54930', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'sm_69fc8a617fa921.01342837.pdf', '1000', 'Paid'),
(71, 'Unit2', 'EZS6X-23748', '20', '20', 'Theory', 'AGWHE-93628', '150', 'Thanuja Geethanjalee Ilayperuma', 'sm_69fc8dd6a60757.60051916.pdf', '1000', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `lms_sys_setttings`
--

CREATE TABLE `lms_sys_setttings` (
  `sys_id` int(20) NOT NULL,
  `sys_name` longtext NOT NULL,
  `sys_logo` longtext NOT NULL,
  `sys_tagline` longblob NOT NULL,
  `sys_license` longblob NOT NULL,
  `sys_privacy_policy` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_sys_setttings`
--

INSERT INTO `lms_sys_setttings` (`sys_id`, `sys_name`, `sys_logo`, `sys_tagline`, `sys_license`, `sys_privacy_policy`) VALUES
(1, 'Instructly LMS', 'lms_logo.png', 0x496e7374616c6c696e6720496e6e6f766174696f6e204f6e205669727475616c204c6561726e696e67, 0x3c703e4d4954204c6963656e736520436f70797269676874202863292032303231204d617274446576656c6f7065727320496e633c2f703e3c703e5065726d697373696f6e20697320686572656279206772616e7465642c2066726565206f66206368617267652c20746f20616e7920706572736f6e206f627461696e696e67206120636f7079206f66207468697320736f66747761726520616e64206173736f63696174656420646f63756d656e746174696f6e2066696c65732028746865202671756f743b536f6674776172652671756f743b292c20746f206465616c20696e2074686520536f66747761726520776974686f7574207265737472696374696f6e2c20696e636c7564696e6720776974686f7574206c696d69746174696f6e207468652072696768747320746f207573652c20636f70792c206d6f646966792c206d657267652c207075626c6973682c20646973747269627574652c207375626c6963656e73652c20616e642f6f722073656c6c20636f70696573206f662074686520536f6674776172652c20616e6420746f207065726d697420706572736f6e7320746f2077686f6d2074686520536f667477617265206973206675726e697368656420746f20646f20736f2c207375626a65637420746f2074686520666f6c6c6f77696e6720636f6e646974696f6e733a205468652061626f766520636f70797269676874206e6f7469636520616e642074686973207065726d697373696f6e206e6f74696365207368616c6c20626520696e636c7564656420696e20616c6c20636f70696573206f72207375627374616e7469616c20706f7274696f6e73206f662074686520536f6674776172652e2054484520534f4654574152452049532050524f5649444544202671756f743b41532049532671756f743b2c20574954484f55542057415252414e5459204f4620414e59204b494e442c2045585052455353204f5220494d504c4945442c20494e434c5544494e4720425554204e4f54204c494d4954454420544f205448452057415252414e54494553204f46204d45524348414e544142494c4954592c204649544e45535320464f52204120504152544943554c415220505552504f534520414e44204e4f4e494e4652494e47454d454e542e20494e204e4f204556454e54205348414c4c2054484520415554484f5253204f5220434f5059524947485420484f4c44455253204245204c4941424c4520464f5220414e5920434c41494d2c2044414d41474553204f52204f54484552204c494142494c4954592c205748455448455220494e20414e20414354494f4e204f4620434f4e54524143542c20544f5254204f52204f54484552574953452c2041524953494e472046524f4d2c204f5554204f46204f5220494e20434f4e4e454354494f4e20574954482054484520534f465457415245204f522054484520555345204f52204f54484552204445414c494e475320494e2054484520534f4654574152452e3c2f703e, 0x3c703e57652075736520796f757220706572736f6e616c20696e666f726d6174696f6e206173207468697320507269766163792053746174656d656e74206465736372696265732e204e6f206d617474657220776865726520796f75206172652c20776865726520796f75206c6976652c206f72207768617420796f757220636974697a656e736869702069732c2077652070726f76696465207468652073616d652068696768207374616e64617264206f6620707269766163792070726f74656374696f6e20746f20616c6c206f75722075736572732061726f756e642074686520776f726c642c207265676172646c657373206f6620746865697220636f756e747279206f66206f726967696e206f72206c6f636174696f6e2e3c2f703e);

-- --------------------------------------------------------

--
-- Table structure for table `lms_units_assaigns`
--

CREATE TABLE `lms_units_assaigns` (
  `ua_id` int(20) NOT NULL,
  `c_code` varchar(200) NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `cc_id` varchar(200) NOT NULL,
  `c_name` varchar(200) NOT NULL,
  `c_category` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `i_number` varchar(200) NOT NULL,
  `i_name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_units_assaigns`
--

INSERT INTO `lms_units_assaigns` (`ua_id`, `c_code`, `c_id`, `cc_id`, `c_name`, `c_category`, `i_id`, `i_number`, `i_name`) VALUES
(29, 'Unit1', '18', '20', 'Statistics', 'AGWHE-93628', '150', 'MA96L 49326', 'Thanuja Geethanjalee Ilayperuma'),
(31, 'Unit2', '20', '20', 'Theory', 'AGWHE-93628', '150', 'MA96L 49326', 'Thanuja Geethanjalee Ilayperuma');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lms_admin`
--
ALTER TABLE `lms_admin`
  ADD PRIMARY KEY (`a_id`);

--
-- Indexes for table `lms_answers`
--
ALTER TABLE `lms_answers`
  ADD PRIMARY KEY (`an_id`);

--
-- Indexes for table `lms_certs`
--
ALTER TABLE `lms_certs`
  ADD PRIMARY KEY (`cert_id`);

--
-- Indexes for table `lms_course`
--
ALTER TABLE `lms_course`
  ADD PRIMARY KEY (`c_id`);

--
-- Indexes for table `lms_course_categories`
--
ALTER TABLE `lms_course_categories`
  ADD PRIMARY KEY (`cc_id`);

--
-- Indexes for table `lms_enrollments`
--
ALTER TABLE `lms_enrollments`
  ADD PRIMARY KEY (`en_id`);

--
-- Indexes for table `lms_instructor`
--
ALTER TABLE `lms_instructor`
  ADD PRIMARY KEY (`i_id`);

--
-- Indexes for table `lms_paid_study_materials`
--
ALTER TABLE `lms_paid_study_materials`
  ADD PRIMARY KEY (`psm_id`);

--
-- Indexes for table `lms_questions`
--
ALTER TABLE `lms_questions`
  ADD PRIMARY KEY (`q_id`);

--
-- Indexes for table `lms_results`
--
ALTER TABLE `lms_results`
  ADD PRIMARY KEY (`rs_id`);

--
-- Indexes for table `lms_student`
--
ALTER TABLE `lms_student`
  ADD PRIMARY KEY (`s_id`);

--
-- Indexes for table `lms_study_material`
--
ALTER TABLE `lms_study_material`
  ADD PRIMARY KEY (`ls_id`);

--
-- Indexes for table `lms_sys_setttings`
--
ALTER TABLE `lms_sys_setttings`
  ADD PRIMARY KEY (`sys_id`);

--
-- Indexes for table `lms_units_assaigns`
--
ALTER TABLE `lms_units_assaigns`
  ADD PRIMARY KEY (`ua_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lms_admin`
--
ALTER TABLE `lms_admin`
  MODIFY `a_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lms_answers`
--
ALTER TABLE `lms_answers`
  MODIFY `an_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `lms_certs`
--
ALTER TABLE `lms_certs`
  MODIFY `cert_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `lms_course`
--
ALTER TABLE `lms_course`
  MODIFY `c_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `lms_course_categories`
--
ALTER TABLE `lms_course_categories`
  MODIFY `cc_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `lms_enrollments`
--
ALTER TABLE `lms_enrollments`
  MODIFY `en_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `lms_instructor`
--
ALTER TABLE `lms_instructor`
  MODIFY `i_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `lms_paid_study_materials`
--
ALTER TABLE `lms_paid_study_materials`
  MODIFY `psm_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `lms_questions`
--
ALTER TABLE `lms_questions`
  MODIFY `q_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `lms_results`
--
ALTER TABLE `lms_results`
  MODIFY `rs_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `lms_student`
--
ALTER TABLE `lms_student`
  MODIFY `s_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=446;

--
-- AUTO_INCREMENT for table `lms_study_material`
--
ALTER TABLE `lms_study_material`
  MODIFY `ls_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `lms_sys_setttings`
--
ALTER TABLE `lms_sys_setttings`
  MODIFY `sys_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lms_units_assaigns`
--
ALTER TABLE `lms_units_assaigns`
  MODIFY `ua_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
