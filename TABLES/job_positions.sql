-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2025 at 02:38 PM
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
-- Database: `job_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `job_positions`
--

CREATE TABLE `job_positions` (
  `id` int(11) NOT NULL,
  `position_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_positions`
--

INSERT INTO `job_positions` (`id`, `position_name`, `category_id`) VALUES
(1, 'Office Clerk', 2),
(2, 'HR Officer', 10),
(3, 'Accounting Staff', 1),
(4, 'IT Support', 11),
(5, 'Cashier', 15),
(6, 'Counter Checker', 15),
(7, 'Sales Associate', 15),
(8, 'Merchandiser', 15),
(9, 'Customer Service Representative', 6),
(10, 'Greeter', 6),
(11, 'Inventory Counter', 18),
(12, 'Stock Clerk', 18),
(13, 'Warehouse Associate', 18),
(14, 'Receiving Clerk', 18),
(15, 'Security Guard', 28),
(16, 'Loss Prevention Officer', 28),
(17, 'Janitor / Housekeeping Staff', 31),
(18, 'Maintenance Technician', 31),
(19, 'Store Supervisor', 15),
(20, 'Department Manager', 15),
(21, 'Floor Manager', 15),
(22, 'Operations Manager', 15),
(23, 'Branch Manager', 15),
(24, 'Financial Analyst', 1),
(25, 'Bookkeeper', 1),
(26, 'Payroll Specialist', 1),
(27, 'Tax Accountant', 1),
(28, 'Administrative Assistant', 2),
(29, 'Executive Assistant', 2),
(30, 'Data Entry Clerk', 2),
(31, 'Receptionist', 2),
(32, 'Marketing Coordinator', 3),
(33, 'SEO Specialist', 3),
(34, 'Social Media Manager', 3),
(35, 'Content Creator', 3),
(36, 'Civil Engineer', 4),
(37, 'Architect', 4),
(38, 'Structural Engineer', 4),
(39, 'CAD Drafter', 4),
(40, 'Graphic Designer', 5),
(41, 'Video Editor', 5),
(42, 'Animator', 5),
(43, 'Photographer', 5),
(44, 'Call Center Agent', 6),
(45, 'Technical Support Specialist', 6),
(46, 'Client Relations Manager', 6),
(47, 'Teacher', 7),
(48, 'Tutor', 7),
(49, 'Corporate Trainer', 7),
(50, 'Instructional Designer', 7),
(51, 'Registered Nurse', 8),
(52, 'Medical Assistant', 8),
(53, 'Pharmacist', 8),
(54, 'Dental Hygienist', 8),
(55, 'Hotel Receptionist', 9),
(56, 'Chef', 9),
(57, 'Tour Guide', 9),
(58, 'Bartender', 9),
(59, 'Recruitment Specialist', 10),
(60, 'HR Generalist', 10),
(61, 'HR Manager', 10),
(62, 'Employee Relations Specialist', 10),
(63, 'Software Developer', 11),
(64, 'IT Support Specialist', 11),
(65, 'Cybersecurity Analyst', 11),
(66, 'Network Administrator', 11),
(67, 'Paralegal', 12),
(68, 'Legal Assistant', 12),
(69, 'Compliance Officer', 12),
(70, 'Corporate Lawyer', 12),
(71, 'Production Supervisor', 13),
(72, 'Quality Control Inspector', 13),
(73, 'Machine Operator', 13),
(74, 'Assembly Line Worker', 13),
(75, 'Project Coordinator', 14),
(76, 'Scrum Master', 14),
(77, 'Agile Coach', 14),
(78, 'Program Manager', 14),
(79, 'Retail Store Manager', 15),
(80, 'Sales Representative', 15),
(81, 'Visual Merchandiser', 15),
(82, 'E-commerce Specialist', 15),
(83, 'Lab Technician', 16),
(84, 'Research Scientist', 16),
(85, 'Clinical Research Associate', 16),
(86, 'Biostatistician', 16),
(87, 'Electrician', 17),
(88, 'Plumber', 17),
(89, 'Welder', 17),
(90, 'Carpenter', 17),
(91, 'Logistics Coordinator', 18),
(92, 'Warehouse Manager', 18),
(93, 'Supply Chain Analyst', 18),
(94, 'Fleet Manager', 18),
(95, 'Network Engineer', 19),
(96, 'Telecom Technician', 19),
(97, 'VoIP Specialist', 19),
(98, 'Wireless Engineer', 19),
(99, 'Copywriter', 20),
(100, 'Editor', 20),
(101, 'Technical Writer', 20),
(102, 'Proofreader', 20),
(103, 'Business Analyst', 21),
(104, 'Management Consultant', 21),
(105, 'Strategy Manager', 21),
(106, 'Operations Consultant', 21),
(107, 'IT Consultant', 22),
(108, 'Financial Consultant', 22),
(109, 'HR Consultant', 22),
(110, 'Marketing Consultant', 22),
(111, 'Policy Analyst', 23),
(112, 'Government Affairs Specialist', 23),
(113, 'Urban Planner', 23),
(114, 'Public Relations Officer', 23),
(115, 'Insurance Agent', 24),
(116, 'Claims Adjuster', 24),
(117, 'Underwriter', 24),
(118, 'Risk Analyst', 24),
(119, 'Public Relations Specialist', 25),
(120, 'Broadcast Journalist', 25),
(121, 'Media Planner', 25),
(122, 'Video Producer', 25),
(123, 'Social Worker', 26),
(124, 'Grant Writer', 26),
(125, 'Community Outreach Coordinator', 26),
(126, 'Volunteer Coordinator', 26),
(127, 'Real Estate Agent', 27),
(128, 'Property Manager', 27),
(129, 'Real Estate Appraiser', 27),
(130, 'Leasing Consultant', 27),
(131, 'Police Officer', 28),
(132, 'Corrections Officer', 28),
(133, 'Private Investigator', 28),
(134, 'Security Consultant', 28),
(135, 'Personal Trainer', 29),
(136, 'Sports Coach', 29),
(137, 'Recreation Coordinator', 29),
(138, 'Athletic Trainer', 29),
(139, 'Truck Driver', 30),
(140, 'Auto Mechanic', 30),
(141, 'Fleet Manager', 30),
(142, 'Delivery Driver', 30),
(143, 'Construction Worker', 31),
(144, 'General Laborer', 31),
(145, 'Heavy Equipment Operator', 31),
(146, 'Demolition Worker', 31),
(147, 'Full Stack Developer', 37),
(148, 'Mobile App Developer', 37),
(149, 'Web Developer', 37),
(150, 'E-commerce Manager', 38),
(151, 'Digital Merchandiser', 38),
(152, 'Film Director', 39),
(153, 'Production Assistant', 39),
(154, 'Biotech Researcher', 40),
(155, 'Clinical Trial Coordinator', 40),
(156, 'Hotel Manager', 41),
(157, 'Sous Chef', 41),
(158, 'Investment Banker', 42),
(159, 'Data Scientist', 43),
(160, 'Business Intelligence Analyst', 43),
(161, 'Real Estate Broker', 44),
(162, 'Health Inspector', 45),
(163, 'Telehealth Coordinator', 46),
(164, 'AI Researcher', 47),
(165, 'Event Manager', 48),
(166, 'Penetration Tester', 49),
(167, 'Game Designer', 50),
(168, 'Visual Artist', 5),
(169, 'Painter', 5),
(170, 'Illustrator', 5),
(171, 'Sculptor', 5),
(172, 'Concept Artist', 5),
(173, 'UI/UX Designer', 5),
(174, 'Motion Graphics Designer', 5),
(175, 'Art Director', 5),
(179, 'Video Producer', 5),
(180, 'Cinematographer', 5),
(181, 'Sound Designer', 5),
(182, 'Music Composer', 5),
(183, 'Fashion Designer', 5),
(184, 'Fashion Illustrator', 5),
(185, 'Comic Book Artist', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_positions`
--
ALTER TABLE `job_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `job_positions`
--
ALTER TABLE `job_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `job_positions`
--
ALTER TABLE `job_positions`
  ADD CONSTRAINT `job_positions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
