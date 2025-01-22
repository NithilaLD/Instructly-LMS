-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2024 at 12:44 PM
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
-- Database: `lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `lms-course_work`
--

CREATE TABLE `lms-course_work` (
  `cw_id` int(20) NOT NULL,
  `cw_code` varchar(200) NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `cw_c_name` varchar(200) NOT NULL,
  `cw_c_code` varchar(200) NOT NULL,
  `cw_c_category` varchar(200) NOT NULL,
  `cw_details` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(1, 'Pabashi Fernando', 'Admin', 'mphfernando@gmail.com', '2ef79ea0ca4a7d56873c9be6b03c25575131c421', 'IMG-20231215-WA0013.jpg');

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
(11, '74C6P-47256', 'ZDW3C-75689', '17', '16', '1', 'MYU', '148', '16', 0x3c703e57686174277320596f7572204167653c2f703e, 0x3c703e32323c2f703e);

-- --------------------------------------------------------

--
-- Table structure for table `lms_billings`
--

CREATE TABLE `lms_billings` (
  `b_id` int(20) NOT NULL,
  `b_amt` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(16, '34', '406', 'Q74B2 63271', 'Dulan Nithila Liyanarachchi', '1', 'MYU', '148', 'Thanuja Geethanjalee Ilayperuma', '28 Apr 2024 2:16pm', '2024-04-28 09:05:45.6029'),
(17, '34', '406', 'Q74B2 63271', 'Dulan Nithila Liyanarachchi', '1', 'MYU', '148', 'Thanuja Geethanjalee Ilayperuma', '28 Apr 2024 2:16pm', '2024-05-06 10:30:59.4002');

-- --------------------------------------------------------

--
-- Table structure for table `lms_course`
--

CREATE TABLE `lms_course` (
  `c_id` int(20) NOT NULL,
  `cc_id` varchar(200) NOT NULL,
  `a_id` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `c_code` varchar(200) NOT NULL,
  `c_name` varchar(200) NOT NULL,
  `c_category` varchar(200) NOT NULL,
  `c_desc` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_course`
--

INSERT INTO `lms_course` (`c_id`, `cc_id`, `a_id`, `i_id`, `c_code`, `c_name`, `c_category`, `c_desc`) VALUES
(16, '17', '', '', '1', 'MYU', 'ZOISB-06514', 0x3c703e4d5920554e49543c2f703e);

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
(19, 'karate', 'Shihan Fernando', 'YEFNT-08514', 0x3c703e4b617261746520436f757273653c2f703e, 'IMG-20240504-WA0043.jpg');

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
(34, 'Dulan Nithila Liyanarachchi', 'Q74B2 63271', '1', 'MYU', 'Thanuja Geethanjalee Ilayperuma', '17', '16', '148', '406', 'ZOISB-06514', '2024-04-28 08:46:30.2931');

-- --------------------------------------------------------

--
-- Table structure for table `lms_forum`
--

CREATE TABLE `lms_forum` (
  `f_id` int(20) NOT NULL,
  `a_id` varchar(200) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `f_topic` longtext NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `s_unit_code` varchar(200) NOT NULL,
  `s_unit_name` varchar(200) NOT NULL,
  `f_no` varchar(200) NOT NULL,
  `f_date_posted` timestamp(4) NOT NULL DEFAULT current_timestamp(4) ON UPDATE current_timestamp(4)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lms_forum_discussions`
--

CREATE TABLE `lms_forum_discussions` (
  `fd_id` int(20) NOT NULL,
  `i_id` varchar(200) NOT NULL,
  `s_id` varchar(200) NOT NULL,
  `s_name` varchar(200) NOT NULL,
  `f_topic` longtext NOT NULL,
  `c_id` varchar(200) NOT NULL,
  `s_unit_code` varchar(200) NOT NULL,
  `s_unit_name` varchar(200) NOT NULL,
  `f_no` varchar(200) NOT NULL,
  `f_id` varchar(200) NOT NULL,
  `f_ans` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `i_bio` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_instructor`
--

INSERT INTO `lms_instructor` (`i_id`, `i_number`, `i_name`, `i_email`, `i_phone`, `i_pwd`, `i_dpic`, `i_bio`) VALUES
(148, '7XRYD98672', 'Shihan Fernando', 'shihanfernando.karate@yahoo.com', '0772731152', '268af3ef8906095ea006c48de68ea7e7decb3f95', 'WhatsApp Image 2024-05-05 at 19.42.16_ed59da52.jpg', 'Music Lover');

-- --------------------------------------------------------

--
-- Table structure for table `lms_messanges`
--

CREATE TABLE `lms_messanges` (
  `id` int(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `subject` longtext NOT NULL,
  `msg` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `s_regno` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_paid_study_materials`
--

INSERT INTO `lms_paid_study_materials` (`psm_id`, `ls_id`, `c_code`, `sm_number`, `c_id`, `cc_id`, `c_name`, `c_category`, `i_id`, `i_name`, `p_method`, `p_code`, `p_amt`, `p_date_paid`, `s_id`, `s_name`, `s_regno`) VALUES
(18, 22, '1', 'JP1EV-37812', '16', '17', 'MYU', 'ZOISB-06514', '148', 'Thanuja Geethanjalee Ilayperuma', 'Mpesa', 'FOVCY6JQH1', '1000', '2024-04-28 08:58:33.1530', '', '', ''),
(20, 22, '1', 'JP1EV-37812', '16', '17', 'MYU', 'ZOISB-06514', '148', 'Thanuja Geethanjalee Ilayperuma', 'Bank Deposit', 'XT72VZA4W6', '1000', '2024-04-28 09:11:35.6806', '406', 'Dulan Nithila Liyanarachchi', 'Q74B2 63271'),
(21, 22, '1', 'JP1EV-37812', '16', '17', 'MYU', 'ZOISB-06514', '148', 'Thanuja Geethanjalee Ilayperuma', 'Bank Deposit', 'MD6N780RSK', '1000', '2024-04-28 09:16:35.0751', '406', 'Dulan Nithila Liyanarachchi', 'Q74B2 63271'),
(22, 22, '1', 'JP1EV-37812', '16', '17', 'MYU', 'ZOISB-06514', '148', 'Thanuja Geethanjalee Ilayperuma', 'Bank Deposit', 'ABC1000FED', '1000', '2024-04-28 09:26:53.8056', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `lms_pwdresets`
--

CREATE TABLE `lms_pwdresets` (
  `id` int(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `token` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(16, '74C6P-47256', '16', '17', '1', 'MYU', '148', 0x3c703e57686174262333393b7320596f7572204167653c2f703e);

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
(22, 'LVPKS76314', 'Dulan Nithila Liyanarachchi', 'Q74B2 63271', '406', '1', 'MYU', 'Thanuja Geethanjalee Ilayperuma', '17', '16', '148', '50', '20', '20', '2024-04-28 09:01:42.148813');

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
  `s_dob` varchar(200) NOT NULL,
  `s_gender` varchar(200) NOT NULL,
  `s_dpic` varchar(200) NOT NULL,
  `s_acc_stats` varchar(200) NOT NULL,
  `s_bio` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_student`
--

INSERT INTO `lms_student` (`s_id`, `s_regno`, `s_course`, `s_name`, `s_email`, `s_pwd`, `s_phoneno`, `s_dob`, `s_gender`, `s_dpic`, `s_acc_stats`, `s_bio`) VALUES
(406, 'Q74B2 63271', 'mycourse', 'Dulan Nithila Liyanarachchi', 'nithila0411@gmail.com', 'a36ff0733076f7f8c978f85e659355bfa5caa90e', '0711372902', '11 Apr 2002', 'Male', '1710821766355.jpeg', 'Active', 'Music And Maths Lover');

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
  `payment_status` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lms_study_material`
--

INSERT INTO `lms_study_material` (`ls_id`, `c_code`, `sm_number`, `c_id`, `cc_id`, `c_name`, `c_category`, `i_id`, `i_name`, `sm_materials`, `sm_price`, `payment_status`) VALUES
(22, '1', 'JP1EV-37812', '16', '17', 'MYU', 'ZOISB-06514', '148', 'Thanuja Geethanjalee Ilayperuma', '1.pdf', '1000', '');

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
(1, 'Shihan’s Dojo', 'lms_logo.png', 0x496e7374696c6c696e6720496e6e6f766174696f6e204f6e205669727475616c204c6561726e696e67, 0x3c703e4d4954204c6963656e736520436f70797269676874202863292032303231204d617274446576656c6f7065727320496e633c2f703e3c703e5065726d697373696f6e20697320686572656279206772616e7465642c2066726565206f66206368617267652c20746f20616e7920706572736f6e206f627461696e696e67206120636f7079206f66207468697320736f66747761726520616e64206173736f63696174656420646f63756d656e746174696f6e2066696c65732028746865202671756f743b536f6674776172652671756f743b292c20746f206465616c20696e2074686520536f66747761726520776974686f7574207265737472696374696f6e2c20696e636c7564696e6720776974686f7574206c696d69746174696f6e207468652072696768747320746f207573652c20636f70792c206d6f646966792c206d657267652c207075626c6973682c20646973747269627574652c207375626c6963656e73652c20616e642f6f722073656c6c20636f70696573206f662074686520536f6674776172652c20616e6420746f207065726d697420706572736f6e7320746f2077686f6d2074686520536f667477617265206973206675726e697368656420746f20646f20736f2c207375626a65637420746f2074686520666f6c6c6f77696e6720636f6e646974696f6e733a205468652061626f766520636f70797269676874206e6f7469636520616e642074686973207065726d697373696f6e206e6f74696365207368616c6c20626520696e636c7564656420696e20616c6c20636f70696573206f72207375627374616e7469616c20706f7274696f6e73206f662074686520536f6674776172652e2054484520534f4654574152452049532050524f5649444544202671756f743b41532049532671756f743b2c20574954484f55542057415252414e5459204f4620414e59204b494e442c2045585052455353204f5220494d504c4945442c20494e434c5544494e4720425554204e4f54204c494d4954454420544f205448452057415252414e54494553204f46204d45524348414e544142494c4954592c204649544e45535320464f52204120504152544943554c415220505552504f534520414e44204e4f4e494e4652494e47454d454e542e20494e204e4f204556454e54205348414c4c2054484520415554484f5253204f5220434f5059524947485420484f4c44455253204245204c4941424c4520464f5220414e5920434c41494d2c2044414d41474553204f52204f54484552204c494142494c4954592c205748455448455220494e20414e20414354494f4e204f4620434f4e54524143542c20544f5254204f52204f54484552574953452c2041524953494e472046524f4d2c204f5554204f46204f5220494e20434f4e4e454354494f4e20574954482054484520534f465457415245204f522054484520555345204f52204f54484552204445414c494e475320494e2054484520534f4654574152452e3c2f703e, 0x3c703e57652075736520796f757220706572736f6e616c20696e666f726d6174696f6e206173207468697320507269766163792053746174656d656e74206465736372696265732e204e6f206d617474657220776865726520796f75206172652c20776865726520796f75206c6976652c206f72207768617420796f757220636974697a656e736869702069732c2077652070726f76696465207468652073616d652068696768207374616e64617264206f6620707269766163792070726f74656374696f6e20746f20616c6c206f75722075736572732061726f756e642074686520776f726c642c207265676172646c657373206f6620746865697220636f756e747279206f66206f726967696e206f72206c6f636174696f6e2e3c2f703e);

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
(28, '1', '16', '17', 'MYU', 'ZOISB-06514', '148', '7XRYD98672', 'Thanuja Geethanjalee Ilayperuma');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lms-course_work`
--
ALTER TABLE `lms-course_work`
  ADD PRIMARY KEY (`cw_id`);

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
-- Indexes for table `lms_billings`
--
ALTER TABLE `lms_billings`
  ADD PRIMARY KEY (`b_id`);

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
-- Indexes for table `lms_forum`
--
ALTER TABLE `lms_forum`
  ADD PRIMARY KEY (`f_id`);

--
-- Indexes for table `lms_forum_discussions`
--
ALTER TABLE `lms_forum_discussions`
  ADD PRIMARY KEY (`fd_id`);

--
-- Indexes for table `lms_instructor`
--
ALTER TABLE `lms_instructor`
  ADD PRIMARY KEY (`i_id`);

--
-- Indexes for table `lms_messanges`
--
ALTER TABLE `lms_messanges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lms_paid_study_materials`
--
ALTER TABLE `lms_paid_study_materials`
  ADD PRIMARY KEY (`psm_id`);

--
-- Indexes for table `lms_pwdresets`
--
ALTER TABLE `lms_pwdresets`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `lms-course_work`
--
ALTER TABLE `lms-course_work`
  MODIFY `cw_id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lms_admin`
--
ALTER TABLE `lms_admin`
  MODIFY `a_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lms_answers`
--
ALTER TABLE `lms_answers`
  MODIFY `an_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `lms_billings`
--
ALTER TABLE `lms_billings`
  MODIFY `b_id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lms_certs`
--
ALTER TABLE `lms_certs`
  MODIFY `cert_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `lms_course`
--
ALTER TABLE `lms_course`
  MODIFY `c_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `lms_course_categories`
--
ALTER TABLE `lms_course_categories`
  MODIFY `cc_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `lms_enrollments`
--
ALTER TABLE `lms_enrollments`
  MODIFY `en_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `lms_forum`
--
ALTER TABLE `lms_forum`
  MODIFY `f_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lms_forum_discussions`
--
ALTER TABLE `lms_forum_discussions`
  MODIFY `fd_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `lms_instructor`
--
ALTER TABLE `lms_instructor`
  MODIFY `i_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `lms_messanges`
--
ALTER TABLE `lms_messanges`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lms_paid_study_materials`
--
ALTER TABLE `lms_paid_study_materials`
  MODIFY `psm_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `lms_pwdresets`
--
ALTER TABLE `lms_pwdresets`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lms_questions`
--
ALTER TABLE `lms_questions`
  MODIFY `q_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `lms_results`
--
ALTER TABLE `lms_results`
  MODIFY `rs_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `lms_student`
--
ALTER TABLE `lms_student`
  MODIFY `s_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=409;

--
-- AUTO_INCREMENT for table `lms_study_material`
--
ALTER TABLE `lms_study_material`
  MODIFY `ls_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `lms_sys_setttings`
--
ALTER TABLE `lms_sys_setttings`
  MODIFY `sys_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lms_units_assaigns`
--
ALTER TABLE `lms_units_assaigns`
  MODIFY `ua_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
