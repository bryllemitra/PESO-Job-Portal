-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2025 at 11:06 AM
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
-- Table structure for table `skill_list`
--

CREATE TABLE `skill_list` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `skill_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skill_list`
--

INSERT INTO `skill_list` (`id`, `category_id`, `skill_name`) VALUES
(1, 1, 'Financial Analysis'),
(2, 1, 'Taxation'),
(3, 1, 'Budgeting'),
(4, 1, 'Accounting Software'),
(5, 1, 'Auditing'),
(6, 1, 'Risk Management'),
(7, 1, 'Payroll Processing'),
(8, 1, 'Investment Analysis'),
(9, 1, 'Financial Reporting'),
(10, 1, 'Financial Modelling'),
(11, 2, 'Data Entry'),
(12, 2, 'Office Management'),
(13, 2, 'Calendar Management'),
(14, 2, 'Event Planning'),
(15, 2, 'Travel Coordination'),
(16, 2, 'Email Management'),
(17, 2, 'Receptionist Duties'),
(18, 2, 'Document Management'),
(19, 2, 'Customer Service'),
(20, 2, 'Multitasking'),
(21, 3, 'SEO (Search Engine Optimization)'),
(22, 3, 'Google Ads'),
(23, 3, 'Content Marketing'),
(24, 3, 'Social Media Marketing'),
(25, 3, 'Market Research'),
(26, 3, 'Brand Strategy'),
(27, 3, 'Copywriting'),
(28, 3, 'Email Marketing'),
(29, 3, 'PPC Advertising'),
(30, 3, 'Affiliate Marketing'),
(31, 4, 'CAD (Computer-Aided Design)'),
(32, 4, 'Structural Engineering'),
(33, 4, 'Construction Project Management'),
(34, 4, 'Building Codes'),
(35, 4, 'Blueprint Reading'),
(36, 4, 'Civil Engineering'),
(37, 4, '3D Modeling'),
(38, 4, 'Sustainability & Green Design'),
(39, 4, 'Material Science'),
(40, 4, 'Electrical Engineering'),
(41, 5, 'Graphic Design'),
(42, 5, 'Video Editing'),
(43, 5, 'Animation'),
(44, 5, 'Illustration'),
(45, 5, 'Photography'),
(46, 5, 'UI/UX Design'),
(47, 5, 'Creative Writing'),
(48, 5, 'Music Production'),
(49, 5, 'Fashion Design'),
(50, 5, 'Art Direction'),
(51, 6, 'Customer Support'),
(52, 6, 'Conflict Resolution'),
(53, 6, 'CRM Software'),
(54, 6, 'Call Center Management'),
(55, 6, 'Product Knowledge'),
(56, 6, 'Active Listening'),
(57, 6, 'Multitasking'),
(58, 6, 'Complaint Handling'),
(59, 6, 'Customer Retention'),
(60, 6, 'Sales Support'),
(61, 7, 'Curriculum Development'),
(62, 7, 'E-Learning'),
(63, 7, 'Tutoring'),
(64, 7, 'Classroom Management'),
(65, 7, 'Training Program Design'),
(66, 7, 'Public Speaking'),
(67, 7, 'Instructional Design'),
(68, 7, 'Assessment & Evaluation'),
(69, 7, 'Online Teaching'),
(70, 7, 'Learning Management Systems'),
(71, 8, 'Patient Care'),
(72, 8, 'Medical Coding & Billing'),
(73, 8, 'Clinical Research'),
(74, 8, 'Pharmaceuticals'),
(75, 8, 'Healthcare Administration'),
(76, 8, 'Surgical Assistance'),
(77, 8, 'Nursing'),
(78, 8, 'Lab Testing'),
(79, 8, 'Medical Records Management'),
(80, 8, 'Emergency Medical Services'),
(81, 9, 'Customer Service'),
(82, 9, 'Event Planning'),
(83, 9, 'Travel Management'),
(84, 9, 'Hotel Management'),
(85, 9, 'Tour Planning'),
(86, 9, 'Concierge Service'),
(87, 9, 'Food & Beverage Management'),
(88, 9, 'Reservation Systems'),
(89, 9, 'Tour Guide Services'),
(90, 9, 'Hospitality Sales'),
(91, 10, 'Recruitment'),
(92, 10, 'Employee Relations'),
(93, 10, 'Payroll Management'),
(94, 10, 'Talent Acquisition'),
(95, 10, 'Training & Development'),
(96, 10, 'HR Policies'),
(97, 10, 'HR Software'),
(98, 10, 'Performance Management'),
(99, 10, 'Employee Benefits'),
(100, 10, 'Conflict Resolution'),
(101, 11, 'Software Development'),
(102, 11, 'Web Development'),
(103, 11, 'Networking'),
(104, 11, 'Cloud Computing'),
(105, 11, 'Cybersecurity'),
(106, 11, 'Database Management'),
(107, 11, 'Machine Learning'),
(108, 11, 'Data Science'),
(109, 11, 'DevOps'),
(110, 11, 'Mobile App Development'),
(111, 12, 'Legal Research'),
(112, 12, 'Contract Law'),
(113, 12, 'Litigation'),
(114, 12, 'Compliance Auditing'),
(115, 12, 'Corporate Law'),
(116, 12, 'Intellectual Property Law'),
(117, 12, 'Regulatory Compliance'),
(118, 12, 'Risk Management'),
(119, 12, 'Family Law'),
(120, 12, 'Dispute Resolution'),
(121, 13, 'Lean Manufacturing'),
(122, 13, 'Production Planning'),
(123, 13, 'Quality Control'),
(124, 13, 'CAD/CAM Software'),
(125, 13, 'Supply Chain Management'),
(126, 13, 'Inventory Management'),
(127, 13, 'Process Optimization'),
(128, 13, 'Forklift Operation'),
(129, 13, 'Assembly Line Management'),
(130, 13, 'Safety Management'),
(131, 14, 'Project Planning'),
(132, 14, 'Risk Management'),
(133, 14, 'Agile Methodology'),
(134, 14, 'Project Scheduling'),
(135, 14, 'Team Management'),
(136, 14, 'Stakeholder Management'),
(137, 14, 'Budgeting & Forecasting'),
(138, 14, 'Project Monitoring'),
(139, 14, 'Resource Allocation'),
(140, 14, 'Change Management'),
(141, 15, 'Sales Strategies'),
(142, 15, 'Customer Relationship Management (CRM)'),
(143, 15, 'Product Knowledge'),
(144, 15, 'Sales Reporting'),
(145, 15, 'Negotiation Skills'),
(146, 15, 'Retail Management'),
(147, 15, 'Inventory Management'),
(148, 15, 'Point of Sale (POS) Systems'),
(149, 15, 'Sales Training'),
(150, 15, 'Visual Merchandising'),
(151, 16, 'Data Analysis'),
(152, 16, 'Research Methodology'),
(153, 16, 'Laboratory Techniques'),
(154, 16, 'Scientific Writing'),
(155, 16, 'Statistics'),
(156, 16, 'Experimental Design'),
(157, 16, 'Quantitative Analysis'),
(158, 16, 'Research Design'),
(159, 16, 'Clinical Trials'),
(160, 16, 'Biostatistics'),
(161, 17, 'Carpentry'),
(162, 17, 'Electrical Wiring'),
(163, 17, 'Plumbing'),
(164, 17, 'HVAC Systems'),
(165, 17, 'Welding'),
(166, 17, 'Masonry'),
(167, 17, 'Construction Management'),
(168, 17, 'Blueprint Reading'),
(169, 17, 'Forklift Operation'),
(170, 17, 'Concrete Work'),
(171, 18, 'Logistics Management'),
(172, 18, 'Inventory Control'),
(173, 18, 'Supply Chain Optimization'),
(174, 18, 'Distribution Management'),
(175, 18, 'Warehouse Management'),
(176, 18, 'Procurement'),
(177, 18, 'Demand Planning'),
(178, 18, 'Transportation Management'),
(179, 18, 'Supplier Relationship Management'),
(180, 18, 'Fleet Management'),
(181, 19, 'Telecommunications Systems'),
(182, 19, 'VoIP Systems'),
(183, 19, 'Network Management'),
(184, 19, 'Fiber Optic Technology'),
(185, 19, 'Wireless Communication'),
(186, 19, 'Telecommunication Design'),
(187, 19, 'Signal Processing'),
(188, 19, 'Broadband Technology'),
(189, 19, 'Radio Frequency'),
(190, 19, 'Telecom Infrastructure'),
(191, 20, 'Copywriting'),
(192, 20, 'Editing'),
(193, 20, 'Content Creation'),
(194, 20, 'Technical Writing'),
(195, 20, 'Proofreading'),
(196, 20, 'Creative Writing'),
(197, 20, 'Blogging'),
(198, 20, 'SEO Writing'),
(199, 20, 'Journalism'),
(200, 20, 'Script Writing'),
(201, 21, 'Business Analysis'),
(202, 21, 'Strategic Planning'),
(203, 21, 'Market Research'),
(204, 21, 'Business Development'),
(205, 21, 'Competitive Analysis'),
(206, 21, 'Project Management'),
(207, 21, 'Financial Planning'),
(208, 21, 'Leadership'),
(209, 21, 'Change Management'),
(210, 21, 'Consulting'),
(211, 22, 'Business Strategy'),
(212, 22, 'Management Consulting'),
(213, 22, 'Market Research'),
(214, 22, 'Leadership'),
(215, 22, 'Financial Modelling'),
(216, 22, 'Client Relationship Management'),
(217, 22, 'Stakeholder Engagement'),
(218, 22, 'Business Process Improvement'),
(219, 22, 'Operational Efficiency'),
(220, 22, 'Data Analytics'),
(221, 23, 'Public Policy'),
(222, 23, 'Regulatory Compliance'),
(223, 23, 'Community Engagement'),
(224, 23, 'Public Speaking'),
(225, 23, 'Grant Writing'),
(226, 23, 'Political Analysis'),
(227, 23, 'Nonprofit Administration'),
(228, 23, 'Government Relations'),
(229, 23, 'Budgeting'),
(230, 23, 'Public Relations'),
(231, 24, 'Risk Management'),
(232, 24, 'Claims Processing'),
(233, 24, 'Underwriting'),
(234, 24, 'Insurance Sales'),
(235, 24, 'Life Insurance'),
(236, 24, 'Property Insurance'),
(237, 24, 'Insurance Policy Analysis'),
(238, 24, 'Regulatory Compliance'),
(239, 24, 'Actuarial Analysis'),
(240, 24, 'Customer Support'),
(241, 25, 'Public Relations'),
(242, 25, 'Social Media Marketing'),
(243, 25, 'Broadcasting'),
(244, 25, 'Journalism'),
(245, 25, 'Media Buying'),
(246, 25, 'Content Creation'),
(247, 25, 'Copywriting'),
(248, 25, 'Press Releases'),
(249, 25, 'Podcasting'),
(250, 25, 'Event Planning'),
(251, 26, 'Nonprofit Fundraising'),
(252, 26, 'Grant Writing'),
(253, 26, 'Community Outreach'),
(254, 26, 'Volunteer Management'),
(255, 26, 'Social Work'),
(256, 26, 'Program Development'),
(257, 26, 'Social Media Advocacy'),
(258, 26, 'Public Relations'),
(259, 26, 'Advocacy Campaigns'),
(260, 26, 'Nonprofit Management'),
(261, 27, 'Property Management'),
(262, 27, 'Real Estate Investment'),
(263, 27, 'Market Analysis'),
(264, 27, 'Sales Negotiation'),
(265, 27, 'Real Estate Appraisal'),
(266, 27, 'Tenant Relations'),
(267, 27, 'Real Estate Marketing'),
(268, 27, 'Property Development'),
(269, 27, 'Leasing'),
(270, 27, 'Mortgage Brokerage'),
(271, 28, 'Security Management'),
(272, 28, 'Law Enforcement'),
(273, 28, 'Surveillance Systems'),
(274, 28, 'Criminal Law'),
(275, 28, 'Risk Assessment'),
(276, 28, 'Public Safety'),
(277, 28, 'Emergency Response'),
(278, 28, 'Security Operations'),
(279, 28, 'Cybersecurity'),
(280, 28, 'Fire Safety'),
(281, 29, 'Personal Training'),
(282, 29, 'Sports Coaching'),
(283, 29, 'Fitness Instruction'),
(284, 29, 'Athletic Training'),
(285, 29, 'Sports Nutrition'),
(286, 29, 'Recreation Planning'),
(287, 29, 'Physical Therapy'),
(288, 29, 'Exercise Science'),
(289, 29, 'Team Leadership'),
(290, 29, 'Wellness Coaching'),
(291, 30, 'Vehicle Maintenance'),
(292, 30, 'Logistics Management'),
(293, 30, 'Forklift Operation'),
(294, 30, 'Fleet Management'),
(295, 30, 'Driving'),
(296, 30, 'Automotive Repair'),
(297, 30, 'Route Planning'),
(298, 30, 'Supply Chain Management'),
(299, 30, 'Transportation Safety'),
(300, 30, 'Dispatching'),
(301, 31, 'Construction'),
(302, 31, 'Heavy Equipment Operation'),
(303, 31, 'Blueprint Reading'),
(304, 31, 'Masonry'),
(305, 31, 'Plumbing'),
(306, 31, 'Welding'),
(307, 31, 'Electrical Installation'),
(308, 31, 'Concrete Work'),
(309, 31, 'Carpentry'),
(310, 31, 'Site Management'),
(311, 32, 'Media Relations'),
(312, 32, 'Press Releases'),
(313, 32, 'Crisis Communication'),
(314, 32, 'Brand Management'),
(315, 32, 'Internal Communication'),
(316, 32, 'Speechwriting'),
(317, 32, 'Event Planning'),
(318, 32, 'Marketing Communications'),
(319, 32, 'Public Speaking'),
(320, 32, 'Social Media Strategy'),
(321, 33, 'E-commerce Management'),
(322, 33, 'Social Media Advertising'),
(323, 33, 'SEO'),
(324, 33, 'PPC Campaigns'),
(325, 33, 'Product Listing Optimization'),
(326, 33, 'Content Marketing'),
(327, 33, 'Email Campaigns'),
(328, 33, 'Web Analytics'),
(329, 33, 'Affiliate Marketing'),
(330, 33, 'Conversion Rate Optimization'),
(331, 34, 'Environmental Policy'),
(332, 34, 'Sustainability Reporting'),
(333, 34, 'Climate Change Mitigation'),
(334, 34, 'Renewable Energy'),
(335, 34, 'Energy Efficiency'),
(336, 34, 'Carbon Footprint Reduction'),
(337, 34, 'Waste Management'),
(338, 34, 'Environmental Auditing'),
(339, 34, 'Sustainability Consulting'),
(340, 34, 'Environmental Law'),
(341, 35, 'Energy Management'),
(342, 35, 'Renewable Energy'),
(343, 35, 'Electrical Systems'),
(344, 35, 'Utilities Management'),
(345, 35, 'Hydroelectric Systems'),
(346, 35, 'Smart Grid Technology'),
(347, 35, 'Energy Efficiency'),
(348, 35, 'Oil & Gas Operations'),
(349, 35, 'Environmental Compliance'),
(350, 35, 'Water Treatment'),
(351, 36, 'Aircraft Maintenance'),
(352, 36, 'Aviation Safety'),
(353, 36, 'Flight Operations'),
(354, 36, 'Aerospace Engineering'),
(355, 36, 'Air Traffic Control'),
(356, 36, 'Navigation Systems'),
(357, 36, 'Aerospace Design'),
(358, 36, 'Aircraft Systems'),
(359, 36, 'Flight Simulation'),
(360, 36, 'Avionics');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `skill_list`
--
ALTER TABLE `skill_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `skill_list`
--
ALTER TABLE `skill_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=361;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `skill_list`
--
ALTER TABLE `skill_list`
  ADD CONSTRAINT `skill_list_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
