-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2025 at 02:42 PM
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
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Accounting & Finance'),
(2, 'Administrative & Office Support'),
(3, 'Advertising & Marketing'),
(4, 'Architecture & Engineering'),
(5, 'Arts, Design & Entertainment'),
(6, 'Customer Service'),
(7, 'Education & Training'),
(8, 'Healthcare & Medical'),
(9, 'Hospitality & Tourism'),
(10, 'Human Resources & Recruitment'),
(11, 'Information Technology (IT)'),
(12, 'Legal & Compliance'),
(13, 'Manufacturing & Production'),
(14, 'Project Management'),
(15, 'Retail & Sales'),
(16, 'Science & Research'),
(17, 'Skilled Trades & Construction'),
(18, 'Supply Chain & Logistics'),
(19, 'Telecommunications'),
(20, 'Writing & Editing'),
(21, 'Business & Strategy'),
(22, 'Consulting'),
(23, 'Government & Public Administration'),
(24, 'Insurance'),
(25, 'Media & Communications'),
(26, 'Nonprofit & Social Services'),
(27, 'Real Estate'),
(28, 'Security & Law Enforcement'),
(29, 'Sports, Fitness & Recreation'),
(30, 'Transportation & Automotive'),
(31, 'General Labor & Construction Workers'),
(32, 'Public Relations & Communications'),
(33, 'E-commerce & Digital Marketing'),
(34, 'Environmental & Sustainability'),
(35, 'Energy & Utilities'),
(36, 'Aerospace & Aviation'),
(37, 'Technology & Software Development'),
(38, 'Retail & E-commerce'),
(39, 'Creative & Media Production'),
(40, 'Biotechnology & Pharmaceuticals'),
(41, 'Hospitality Management & Culinary Arts'),
(42, 'Finance & Investment'),
(43, 'Data Science & Analytics'),
(44, 'Real Estate & Property Management'),
(45, 'Public Health & Safety'),
(46, 'Telemedicine & Digital Health'),
(47, 'Artificial Intelligence & Robotics'),
(48, 'Education Support & Counseling'),
(49, 'Event Planning & Management'),
(50, 'Cybersecurity'),
(51, 'Gaming & Interactive Media');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
