-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2025 at 01:20 PM
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
-- Table structure for table `job_preferences`
--

CREATE TABLE `job_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `work_type` enum('remote','onsite','hybrid') NOT NULL DEFAULT 'onsite',
  `job_location` enum('local','overseas') NOT NULL,
  `employment_type` enum('fulltime','parttime','self-employed','freelance','contract','internship','apprenticeship','seasonal','home-based','domestic','temporary','volunteer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_preferences`
--

INSERT INTO `job_preferences` (`id`, `user_id`, `work_type`, `job_location`, `employment_type`) VALUES
(2, 67, 'onsite', 'local', 'fulltime');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_preferences`
--
ALTER TABLE `job_preferences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `job_preferences`
--
ALTER TABLE `job_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `job_preferences`
--
ALTER TABLE `job_preferences`
  ADD CONSTRAINT `job_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
