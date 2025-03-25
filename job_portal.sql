-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2025 at 04:51 PM
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
-- Table structure for table `about`
--

CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `hero_text` text DEFAULT 'Empowering the community through employment opportunities.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about`
--

INSERT INTO `about` (`id`, `cover_photo`, `mission`, `vision`, `hero_text`) VALUES
(1, 'ppp.jpg', 'Our mission is to provide accessible, user-friendly, and effective job matching services that support job seekers in finding suitable employment while helping employers connect with a skilled and diverse talent pool. We are committed to delivering quality resources, career guidance, and continuous support to promote sustainable employment and personal growth for all members of the community.', 'To empower individuals and strengthen communities by connecting job seekers with meaningful employment opportunities, fostering a fair, inclusive, and dynamic workforce that drives economic growth and prosperity for all.', 'Welcome to PESO Job Portal, your trusted partner in connecting talent with opportunity. We are dedicated to empowering job seekers and employers alike by providing a dynamic and user-friendly platform that simplifies the hiring process. Our mission is to bridge the gap between skilled professionals and organizations seeking top talent, fostering growth and success for individuals and businesses across Zamboanga City.');

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `award_name` varchar(255) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `award_date` date NOT NULL,
  `proof_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `user_id`, `award_name`, `organization`, `award_date`, `proof_file`) VALUES
(5, 67, 'ICPep Region IX Quiz bowl Champion', ' Institute of Computer Engineers of the Philippines', '2025-03-20', '../uploads/achievements/vengwapo_1742439839.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_file` varchar(255) NOT NULL,
  `link_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`id`, `title`, `description`, `image_file`, `link_url`, `created_at`) VALUES
(33, ' Lorem Ipsum', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'ad_67da82a2190d49.95136902.JPG', 'https://www.facebook.com/venchansalido/', '2025-03-19 08:38:58'),
(34, '🎉 コスプレイベントのお知らせ！ 🎉', '皆さん、準備はできていますか？待ちに待った コスプレイベント がついに開催されます！\r\n\r\n📅 日時: 2025年4月20日（日）\r\n🕘 時間: 午前10時 〜 午後6時\r\n📍 場所: 東京ビッグサイト (イベントホールA)\r\n🎟️ 入場料: 前売り券 2,000円 / 当日券 2,500円\r\n\r\nお気に入りのキャラクターになりきって、楽しいひとときを過ごしましょう！撮影ブースやコンテスト、スペシャルゲストのトークショーもお楽しみに！\r\n\r\nさらに、限定グッズやフードエリアも充実！お友達と一緒に、最高の思い出を作りませんか？\r\n\r\n📸 コスプレコンテスト参加者募集中！\r\nエントリーは公式ウェブサイトから！あなたの素敵なコスプレ姿をみんなに披露しましょう！\r\n\r\n✨ みんなで盛り上がる特別な一日を一緒に楽しみましょう！\r\n詳細は公式サイトをご覧ください。', 'ad_67da84953fef19.33772224.jpg', 'https://www.google.com/search?q=otaku&sca_esv=bb7e2807df5d4671&udm=2&biw=1912&bih=1000&ei=b4TaZ5OuLP_j2roPstDO8Qg&ved=0ahUKEwjTocf64JWMAxX_sVYBHTKoM44Q4dUDCBQ&uact=5&oq=otaku&gs_lp=EgNpbWciBW90YWt1MggQABiABBixAzIIEAAYgAQYsQMyBRAAGIAEMggQABiABBixAzIFEAAYgA', '2025-03-19 08:47:17'),
(35, '📢 𝙋𝙐𝘽𝙇𝙄𝘾 𝘼𝙉𝙉𝙊𝙐𝙉𝘾𝙀𝙈𝙀𝙉𝙏 📢', 'Dear valued stakeholders, job seekers, employers, and partners,\r\nAs our 𝙤𝙛𝙛𝙞𝙘𝙞𝙖𝙡 𝙋𝙀𝙎𝙊 𝙕𝙖𝙢𝙗𝙤𝙖𝙣𝙜𝙖 𝙁𝙖𝙘𝙚𝙗𝙤𝙤𝙠 𝙥𝙖𝙜𝙚 𝙞𝙨 𝙘𝙪𝙧𝙧𝙚𝙣𝙩𝙡𝙮 𝙨𝙪𝙨𝙥𝙚𝙣𝙙𝙚𝙙, we will be using this temporary account to continue providing you with timely updates, job opportunities, and important announcements.\r\nWe kindly ask for your support—𝙥𝙡𝙚𝙖𝙨𝙚 𝙛𝙤𝙡𝙡𝙤𝙬 𝙩𝙝𝙞𝙨 𝙖𝙘𝙘𝙤𝙪𝙣𝙩 𝙖𝙣𝙙 𝙨𝙝𝙖𝙧𝙚 𝙩𝙝𝙞𝙨 𝙥𝙤𝙨𝙩 so we can reach more people. Your patience and understanding are greatly appreciated as we work on resolving this issue.\r\n𝙋𝙪𝙗𝙡𝙞𝙘 𝙀𝙢𝙥𝙡𝙤𝙮𝙢𝙚𝙣𝙩 𝙎𝙚𝙧𝙫𝙞𝙘𝙚 𝙊𝙛𝙛𝙞𝙘𝙚 (𝙋𝙀𝙎𝙊) – 𝙕𝙖𝙢𝙗𝙤𝙖𝙣𝙜𝙖 𝘾𝙞𝙩𝙮', 'ad_67da9336560744.27471085.jpg', 'https://www.facebook.com/profile.php?id=61573999820314', '2025-03-19 09:49:42');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `thumbnail` varchar(255) DEFAULT NULL,
  `url_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`, `thumbnail`, `url_link`) VALUES
(1, 'New Job Openings', 'We are excited to announce new job openings in various industries, ranging from tech and healthcare to finance and customer service. These positions offer competitive salaries, benefits, and opportunities for growth in dynamic, supportive work environments. Whether you&amp;#039;re looking to take the next step in your career or explore a new field, we have something for everyone. Stay tuned for updates on available roles, and don&amp;#039;t miss out—apply today and take the first step towards your future!', '2025-02-12 13:29:41', NULL, ''),
(2, 'Upcoming Career Fair', 'Join us for our annual career fair happening next month, where you’ll have the chance to meet top employers, network with industry professionals, and explore exciting career opportunities. Whether you\'re a recent graduate or an experienced professional looking for a new challenge, this event is designed to help you make valuable connections and take the next step in your career. Register now to secure your spot and get ready to unlock new possibilities for your future!', '2025-02-12 13:29:41', NULL, NULL),
(3, 'PESO Office Hours Update', 'Our office hours have been updated to better serve you. We encourage you to check the updated schedule on our website for the most accurate information regarding availability. Whether you\'re planning a visit or need assistance during specific hours, the new schedule ensures that we are here when you need us. Be sure to take a look and plan accordingly!', '2025-02-12 13:29:41', NULL, NULL),
(28, '📢 𝙋𝙐𝘽𝙇𝙄𝘾 𝘼𝙉𝙉𝙊𝙐𝙉𝘾𝙀𝙈𝙀𝙉𝙏 📢', 'Dear valued stakeholders, job seekers, employers, and partners,\r\nAs our 𝙤𝙛𝙛𝙞𝙘𝙞𝙖𝙡 𝙋𝙀𝙎𝙊 𝙕𝙖𝙢𝙗𝙤𝙖𝙣𝙜𝙖 𝙁𝙖𝙘𝙚𝙗𝙤𝙤𝙠 𝙥𝙖𝙜𝙚 𝙞𝙨 𝙘𝙪𝙧𝙧𝙚𝙣𝙩𝙡𝙮 𝙨𝙪𝙨𝙥𝙚𝙣𝙙𝙚𝙙, we will be using this temporary account to continue providing you with timely updates, job opportunities, and important announcements.\r\nWe kindly ask for your support—𝙥𝙡𝙚𝙖𝙨𝙚 𝙛𝙤𝙡𝙡𝙤𝙬 𝙩𝙝𝙞𝙨 𝙖𝙘𝙘𝙤𝙪𝙣𝙩 𝙖𝙣𝙙 𝙨𝙝𝙖𝙧𝙚 𝙩𝙝𝙞𝙨 𝙥𝙤𝙨𝙩 so we can reach more people. Your patience and understanding are greatly appreciated as we work on resolving this issue.\r\n𝙋𝙪𝙗𝙡𝙞𝙘 𝙀𝙢𝙥𝙡𝙤𝙮𝙢𝙚𝙣𝙩 𝙎𝙚𝙧𝙫𝙞𝙘𝙚 𝙊𝙛𝙛𝙞𝙘𝙚 (𝙋𝙀𝙎𝙊) – 𝙕𝙖𝙢𝙗𝙤𝙖𝙣𝙜𝙖 𝘾𝙞𝙩𝙮', '2025-03-03 10:03:11', '1742052994-ad67c57e7f6d4732.92310027.jpg', 'https://www.facebook.com/profile.php?id=61573999820314');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resume_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','accepted','rejected','canceled') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `status_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dismissed` tinyint(1) DEFAULT 0,
  `remark` text DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `user_viewed` tinyint(1) DEFAULT 0,
  `action_taken_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `job_id`, `applied_at`, `resume_file`, `status`, `is_read`, `status_updated_at`, `dismissed`, `remark`, `canceled_at`, `user_viewed`, `action_taken_by`) VALUES
(566, 67, 87, '2025-03-20 01:22:33', '../uploads/resumes/vengwapo.pdf', 'accepted', 0, '2025-03-21 15:31:30', 0, 'Your application has been accepted. Kindly await our call for next steps.', NULL, 1, 21),
(567, 67, 63, '2025-03-20 01:22:51', '../uploads/resumes/vengwapo.pdf', 'pending', 0, '2025-03-20 01:22:51', 0, NULL, NULL, 0, NULL),
(568, 67, 23, '2025-03-20 01:23:06', '../uploads/resumes/vengwapo.pdf', 'canceled', 0, '2025-03-21 15:32:37', 0, NULL, '2025-03-21 23:32:37', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `application_positions`
--

CREATE TABLE `application_positions` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application_positions`
--

INSERT INTO `application_positions` (`id`, `application_id`, `position_id`) VALUES
(291, 566, 28),
(292, 567, 9),
(293, 567, 11);

-- --------------------------------------------------------

--
-- Table structure for table `barangay`
--

CREATE TABLE `barangay` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay`
--

INSERT INTO `barangay` (`id`, `name`) VALUES
(1, 'Arena Blanco'),
(2, 'Ayala'),
(3, 'Baliwasan'),
(4, 'Baluno'),
(5, 'Barangay Zone I'),
(6, 'Barangay Zone II'),
(7, 'Barangay Zone III'),
(8, 'Barangay Zone IV'),
(9, 'Boalan'),
(10, 'Bolong'),
(11, 'Buenavista'),
(12, 'Bunguiao'),
(13, 'Busay'),
(14, 'Cabaluay'),
(15, 'Cabatangan'),
(16, 'Cacao'),
(17, 'Calabasa'),
(18, 'Calarian'),
(19, 'Camino Nuevo'),
(20, 'Campo Islam'),
(21, 'Canelar'),
(22, 'Capisan'),
(23, 'Cawit'),
(24, 'Culianan'),
(25, 'Curuan'),
(26, 'Dita'),
(27, 'Divisoria'),
(28, 'Dulian (Upper Bunguiao)'),
(29, 'Dulian (Upper Pasonanca)'),
(30, 'Guisao'),
(31, 'Guiwan'),
(32, 'Kasanyangan'),
(33, 'La Paz'),
(34, 'Labuan'),
(35, 'Lamisahan'),
(36, 'Landang Gua'),
(37, 'Landang Laum'),
(38, 'Lanzones'),
(39, 'Lapakan'),
(40, 'Latuan'),
(41, 'Licomo'),
(42, 'Limaong'),
(43, 'Limpapa'),
(44, 'Lubigan'),
(45, 'Lumayang'),
(46, 'Lumbangan'),
(47, 'Lunzuran'),
(48, 'Maasin'),
(49, 'Malagutay'),
(50, 'Mampang'),
(51, 'Manalipa'),
(52, 'Mangusu'),
(53, 'Manicahan'),
(54, 'Mariki'),
(55, 'Mercedes'),
(56, 'Muti'),
(57, 'Pamucutan'),
(58, 'Pangapuyan'),
(59, 'Panubigan'),
(60, 'Pasilmanta'),
(61, 'Pasobolong'),
(62, 'Pasonanca'),
(63, 'Patalon'),
(64, 'Putik'),
(65, 'Quiniput'),
(66, 'Recodo'),
(67, 'Rio Hondo'),
(68, 'Salaan'),
(69, 'Sangali'),
(70, 'San Jose Cawa-Cawa'),
(71, 'San Jose Gusu'),
(72, 'San Roque'),
(73, 'Santa Barbara'),
(74, 'Santa Catalina'),
(75, 'Santa Maria'),
(76, 'Santo Niño'),
(77, 'Sibulao'),
(78, 'Sinubung'),
(79, 'Sinunuc'),
(80, 'Tagasilay'),
(81, 'Taguiti'),
(82, 'Talabaan'),
(83, 'Talisayan'),
(84, 'Talon-Talon'),
(85, 'Taluksangay'),
(86, 'Tetuan'),
(87, 'Tictapul'),
(88, 'Tigbalabag'),
(89, 'Tigtabon'),
(90, 'Tolosa'),
(91, 'Tugbungan'),
(92, 'Tulungatung'),
(93, 'Tumaga'),
(94, 'Tumalutap'),
(95, 'Tumitus'),
(96, 'Victoria'),
(97, 'Vitali'),
(98, 'Zambowood');

-- --------------------------------------------------------

--
-- Table structure for table `browse`
--

CREATE TABLE `browse` (
  `id` int(11) NOT NULL,
  `cover_photo` varchar(255) NOT NULL,
  `hero_title` varchar(255) NOT NULL DEFAULT 'Discover Your Next Opportunity',
  `hero_subtitle` text NOT NULL DEFAULT 'Search for jobs that fit your expertise and apply with confidence.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `browse`
--

INSERT INTO `browse` (`id`, `cover_photo`, `hero_title`, `hero_subtitle`) VALUES
(48, '../uploads/browse_cover/67d67520a7bd5_67d5805c25153_67bf2a7df265b_476482549_2677225615810993_560686330115328693_n.png', 'Discover Your Next Opportunity', 'Search for jobs that fit your expertise and apply with confidence.');

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

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `certificate_name` varchar(255) NOT NULL,
  `issuing_organization` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `certificate_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `user_id`, `certificate_name`, `issuing_organization`, `issue_date`, `certificate_file`) VALUES
(4, 67, 'ICPep Region IX Quiz bowl Champion', 'Institute of Computer Engineers of the Philippines', '2025-03-20', '../uploads/certificates/vengwapo_1742444606.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `status` enum('active','deleted') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `is_read`, `status`) VALUES
(52, 'Borat Sagadiyev', 'borat@gmail.com', 'Halo I&#039;m under the water', 'please halp me!', '2025-03-01 08:11:57', 1, 'active'),
(53, 'Venard Jhon Salido', 'venard@gmail.com', 'tes', 'test', '2025-03-07 14:22:39', 1, 'deleted');

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `education_level` enum('primary','secondary','college','graduate','vocational') NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `institution` varchar(255) NOT NULL,
  `status` enum('Completed','Not Completed') NOT NULL,
  `completion_year` int(11) DEFAULT NULL,
  `expected_completion_date` date DEFAULT NULL,
  `course_highlights` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`id`, `user_id`, `education_level`, `course`, `institution`, `status`, `completion_year`, `expected_completion_date`, `course_highlights`) VALUES
(15, 67, 'primary', '', 'Zamboanga West Central School', 'Completed', 2007, '0000-00-00', ''),
(17, 67, 'secondary', '', 'Zamboanga City High School (Main)', 'Completed', 2011, '0000-00-00', ''),
(23, 67, 'college', 'BS in Information Technology', 'Western Mindanao State University', 'Not Completed', 0, '2026-05-12', 'Summa cumlaude'),
(27, 67, 'vocational', 'Massage Therapy', 'TESDA (Technical Education and Skills Development Authority)', 'Completed', 2011, '0000-00-00', '');

-- --------------------------------------------------------

--
-- Table structure for table `employers`
--

CREATE TABLE `employers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_description` text DEFAULT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employers`
--

INSERT INTO `employers` (`id`, `user_id`, `company_name`, `company_description`, `company_website`, `location`) VALUES
(2, 21, 'KCC Mall de Zamboanga', 'KCC Mall de Zamboanga is a prominent shopping destination located in Zamboanga City, Philippines. Owned by Koronadal Commercial Corporation (KCC), it stands as the largest mall in the Zamboanga Peninsula and the third in the Philippines under the KCC Malls branches.', 'https://www.facebook.com/search/top?q=kcc%20mall%20de%20zamboanga', 'Governor Camins Avenue in Barangay Camino Nuevo');

-- --------------------------------------------------------

--
-- Table structure for table `employer_requests`
--

CREATE TABLE `employer_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_message` text NOT NULL,
  `proof_file` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `remark` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `company_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_requests`
--

INSERT INTO `employer_requests` (`id`, `user_id`, `request_message`, `proof_file`, `status`, `remark`, `created_at`, `company_name`) VALUES
(54, 67, 'This is a test', '', 'approved', NULL, '2025-03-21 15:59:49', 'TechTrek');

-- --------------------------------------------------------

--
-- Table structure for table `employer_request_proofs`
--

CREATE TABLE `employer_request_proofs` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_request_proofs`
--

INSERT INTO `employer_request_proofs` (`id`, `request_id`, `file_path`, `created_at`) VALUES
(75, 54, '../uploads/company_proofs/465786029_9219920348037823_7671980852851727402_n.jpg', '2025-03-21 15:59:49'),
(76, 54, '../uploads/company_proofs/VENARD CV.pdf', '2025-03-21 15:59:49');

-- --------------------------------------------------------

--
-- Table structure for table `homepage`
--

CREATE TABLE `homepage` (
  `id` int(11) NOT NULL,
  `cover_photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homepage`
--

INSERT INTO `homepage` (`id`, `cover_photo`) VALUES
(42, '67d67471f3b18_67d5a63640254_67d581ce765d4_67c5805206786_Picsart_25-03-03_18-10-28-980.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `specific_location` varchar(255) DEFAULT NULL,
  `responsibilities` text NOT NULL,
  `requirements` text NOT NULL,
  `preferred_qualifications` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `employer_id` int(11) NOT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `description`, `thumbnail`, `photo`, `created_at`, `category_id`, `location`, `specific_location`, `responsibilities`, `requirements`, `preferred_qualifications`, `status`, `employer_id`, `remarks`) VALUES
(21, 'Graphic Designer for Hire', 'As a Graphic Designer, you will be responsible for creating visually engaging designs that align with our brand and marketing objectives. You will work closely with cross-functional teams, including marketing, product, and content, to develop graphics for various platforms, including web, social media, print, and more.', 'uploads/admin_job_thumbnail/graphics.jpg', 'uploads/admin_job_photo/graphx.jpg', '2025-02-19 05:44:12', 11, 'Baliwasan', NULL, 'Create and design digital and print materials, such as brochures, websites, social media graphics, banners, email templates, and advertisements.\r\nDevelop concepts and designs based on project requirements and brand guidelines.\r\nCollaborate with marketing, content, and product teams to ensure design consistency across all channels.\r\nEdit and enhance images, videos, and other visual assets for use in marketing campaigns.\r\nStay up to date with industry trends and software to maintain high-quality, innovative designs.\r\nAssist in the creation of presentations, reports, and other internal materials.\r\nEnsure all designs are delivered on time and meet the quality standards set by the company.\r\nMaintain a well-organized design library and asset management system.', 'Bachelor&#039;s degree in Graphic Design, Visual Arts, or related field (or equivalent practical experience).\r\nProven experience as a Graphic Designer or in a similar role.\r\nProficiency in design software, including Adobe Creative Suite (Photoshop, Illustrator, InDesign, etc.) and other relevant tools.\r\nStrong understanding of design principles, typography, color theory, and layout.\r\nAbility to work efficiently on multiple projects and meet deadlines.\r\nStrong attention to detail and a keen eye for aesthetics.\r\nExcellent communication and collaboration skills.\r\nExperience with video editing software (e.g., Premiere Pro, After Effects) is a plus.\r\nKnowledge of web design principles and familiarity with HTML/CSS is an advantage.\r\nA strong portfolio showcasing your design skills and previous work.', 'Experience with UX/UI design or web development.\r\nFamiliarity with motion graphics and animation.\r\nExperience in brand identity and logo design.', 'approved', 0, NULL),
(23, 'We are looking for an Animator', 'We are seeking a creative and skilled Animator to join our team. The ideal candidate will have a strong passion for storytelling and a deep understanding of animation principles. You will be responsible for creating visually engaging animations, bringing characters and scenes to life, and ensuring that animations align with the project’s vision. The Animator will work closely with the creative team to develop high-quality animations for various media, including film, television, video games, and digital content.', 'uploads/admin_job_thumbnail/Screenshot 2025-01-19 031039.png', 'uploads/admin_job_photo/ggg.jpg', '2025-02-19 05:56:22', 5, 'Putik', 'Marcos Drive,', 'Design and create 2D/3D animations based on storyboards, scripts, and creative briefs.\r\nCollaborate with directors, designers, and other animators to ensure consistency in style and visual direction.\r\nCreate character and environmental animations, ensuring fluid movement and accurate timing.\r\nParticipate in brainstorming sessions and contribute ideas to the creative process.\r\nEdit and refine animations based on feedback, ensuring the final product aligns with project goals.\r\nMaintain consistent communication with the team to meet deadlines and project milestones.\r\nIntegrate special effects, sound, and visual elements into animations.\r\nTroubleshoot animation-related technical issues, and suggest improvements or adjustments as needed.\r\nStay updated on the latest animation trends, techniques, and software.', 'Proven experience as an Animator, with a strong portfolio demonstrating skills in 2D/3D animation, motion graphics, or visual effects.\r\nProficiency in industry-standard animation software (e.g., Adobe Animate, Toon Boom, Maya, Blender, Cinema 4D).\r\nUnderstanding of animation principles (timing, weight, and fluidity of movement).\r\nAbility to adapt to various animation styles and techniques.\r\nStrong understanding of character design, facial expressions, and body mechanics.\r\nKnowledge of storyboarding, composition, and visual storytelling.\r\nAbility to meet deadlines and work effectively under pressure.\r\nStrong attention to detail and ability to incorporate feedback into revisions.\r\nExcellent communication skills and ability to collaborate with a creative team.\r\nBasic knowledge of 3D modeling and rigging (for 3D animators)', 'Bachelor’s degree in Animation, Fine Arts, Computer Graphics, or a related field.\r\nExperience working in the animation industry, including film, television, or gaming.\r\nExperience with motion capture or other advanced animation techniques.\r\nFamiliarity with character rigging and 3D rendering processes.\r\nUnderstanding of VR/AR or interactive media animation principles.\r\nProficiency in additional software such as Adobe After Effects, ZBrush, or Houdini.\r\nAbility to work in both traditional and digital animation environments.\r\nStrong understanding of the production pipeline and ability to work in a team-oriented environment.\r\nPassion for animation and storytelling, with a strong desire to innovate and experiment.', 'approved', 0, NULL),
(24, 'Tourist Guide for Hire', 'We are looking for an enthusiastic, knowledgeable, and personable Tourist Guide to join our team. The ideal candidate will be passionate about sharing the history, culture, and beauty of our region with travelers. You will lead groups on guided tours, providing engaging and informative commentary while ensuring a safe and enjoyable experience for all guests. Your goal is to offer an immersive and memorable experience for tourists by showcasing key attractions and providing insightful information about local landmarks and traditions.', 'uploads/admin_job_thumbnail/admin_67db8cc26ca53.jpg', 'uploads/admin_job_photo/admin_67db8cc26cddf.jpg', '2025-02-19 05:57:33', 9, 'Bolong', NULL, 'Lead guided tours for individuals or groups, providing information about the history, culture, and significance of local landmarks and attractions.\r\nShare interesting and engaging facts about the region’s heritage, traditions, and natural features.\r\nEnsure the safety and well-being of all participants during the tour, including managing group dynamics and addressing any concerns.\r\nTailor tours based on the interests and needs of the group, ensuring an enjoyable experience for people of all ages and backgrounds.\r\nMaintain a friendly and approachable demeanor while answering questions and engaging with tourists.\r\nAssist with tour bookings, providing detailed information to potential customers about available tours and schedules.\r\nCoordinate logistics for the tour, including transportation, timing, and ensuring all necessary resources are available.\r\nAdhere to all safety protocols and ensure that all guests are following guidelines throughout the tour.\r\nContinuously stay informed about local events, news, and changes to landmarks or attractions to provide up-to-date information.\r\nManage any issues or concerns that arise during the tour, providing solutions or escalating as', 'Proven experience as a Tourist Guide, or in a similar customer-facing role (hospitality, travel, or tourism).\r\nStrong knowledge of local attractions, landmarks, history, culture, and natural resources.\r\nExcellent communication skills, with the ability to engage and entertain a diverse group of people.\r\nStrong interpersonal skills and the ability to build rapport with tourists and colleagues.\r\nFluent in English (additional language skills are a plus).\r\nAbility to work in various weather conditions and during irregular hours, including weekends and holidays.\r\nA friendly, approachable, and positive attitude, with a passion for sharing knowledge.\r\nStrong organizational skills to manage group logistics and keep tours running smoothly.\r\nAbility to handle minor issues or challenges calmly and professionally during the tour.\r\nFirst aid certification (preferred but not required).', 'Certification in tourism or a related field (e.g., Certified Tourist Guide, Tourism Management).\r\nKnowledge of multiple languages to accommodate tourists from different regions.\r\nFamiliarity with a variety of tour-related technology (audio systems, mobile apps, etc.).\r\nPrevious experience in a customer service role, particularly in the travel or hospitality industry.\r\nPassion for local culture, history, or nature conservation.\r\nAbility to create customized, themed tours for specific types of tourists (e.g., history buffs, families, adventure seekers).', 'approved', 0, NULL),
(25, 'School Bus Driver For Hire', 'We are seeking a reliable and dedicated School Bus Driver to ensure the safe and timely transportation of students to and from school. As a School Bus Driver, you will be responsible for operating the school bus, maintaining a positive environment for students, and following all safety regulations. If you have a passion for working with children and a commitment to ensuring their safety, we encourage you to apply', 'uploads/admin_job_thumbnail/driver.jpg', 'uploads/admin_job_photo/bbbbbb.jpg', '2025-02-19 06:09:42', 30, 'Cabaluay', NULL, 'Safely drive the school bus according to established routes and schedules.\r\nEnsure the safety of all students during loading, unloading, and the duration of the ride.\r\nAdhere to all traffic laws, school policies, and safety procedures.\r\nAssist students in boarding and exiting the bus as needed.\r\nMaintain regular communication with the school and parents regarding any schedule changes or concerns.\r\nPerform routine inspections of the bus to ensure it is in good working order.\r\nReport any maintenance or mechanical issues promptly.\r\nEnsure the bus is clean and presentable at all times.\r\nFollow protocols for emergencies, including evacuation drills and first aid as required.', 'Valid Commercial Driver’s License (CDL) with School Bus Endorsement.\r\nClean driving record with no major traffic violations.\r\nAbility to pass a background check and drug screening.\r\nAbility to work with children and maintain control of the bus environment.\r\nGood communication and interpersonal skills.\r\nPhysical ability to assist students in case of emergency or to maintain the safety of the bus.\r\nAvailability to work early mornings and afternoons, depending on school schedules.', 'Previous experience as a school bus driver or in a similar transportation role.\r\nFirst Aid and CPR certification.\r\nKnowledge of local roads and traffic patterns.\r\nFamiliarity with school district transportation rules and guidelines.\r\nBilingual skills (if applicable).', 'approved', 0, NULL),
(59, 'GYM INSTRUCTOR FOR HIRE', 'We are seeking a passionate and certified Gym Instructor to join our fitness team. The ideal candidate will have a strong understanding of fitness principles, excellent communication skills, and a commitment to motivating others to achieve their fitness goals. As a Gym Instructor, you will be responsible for leading individual and group workout sessions, providing guidance on proper form and technique, and ensuring a safe and welcoming environment for all gym members.', 'uploads/admin_job_thumbnail/GYM.jpg', 'uploads/admin_job_photo/YEAH BUDDY!.jpg', '2025-02-24 10:21:38', 29, 'Guiwan', 'Beside AMA College, inside the fruitstand,', 'Conduct individual and group fitness classes, including strength training, cardio, and flexibility workouts.\r\nAssess clients&#039; fitness levels and create personalized training plans.\r\nMonitor clients&#039; progress, adjusting training programs as needed.\r\nEnsure the safety and proper use of gym equipment.\r\nProvide advice and support to gym members on proper workout techniques and nutrition.\r\nMaintain a clean and organized gym environment.\r\nStay up to date with industry trends and continuously improve your fitness knowledge.', 'Certification in personal training or group fitness (e.g., NASM, ACE, or equivalent).\r\nHigh school diploma or equivalent; a degree in kinesiology, exercise science, or a related field is preferred.\r\nProven experience as a gym instructor or personal trainer.\r\nExcellent communication and interpersonal skills.\r\nAbility to motivate and inspire clients to reach their fitness goals.\r\nBasic knowledge of nutrition and wellness principles.\r\nAbility to work flexible hours, including evenings and weekends.', 'Experience in teaching specialized fitness classes (e.g., yoga, Pilates, HIIT).\r\nCPR and First Aid certification.\r\nAdditional certifications in advanced training techniques or fitness programs.\r\nExperience working with diverse populations, including elderly or rehabilitation clients.', 'approved', 0, NULL),
(63, 'BUDGET WISE HIRING!', '𝙋𝙧𝙚𝙨𝙚𝙣𝙩𝙞𝙣𝙜 𝙩𝙝𝙚 𝙀𝙢𝙥𝙡𝙤𝙮𝙚𝙧𝙨 &amp;amp;amp;amp;amp; 𝙅𝙤𝙗 𝙊𝙥𝙥𝙤𝙧𝙩𝙪𝙣𝙞𝙩𝙞𝙚𝙨 𝙖𝙩 𝙩𝙝𝙚 88𝙩𝙝 𝘿𝙞𝙖 𝙙𝙚 𝙕𝙖𝙢𝙗𝙤𝙖𝙣𝙜𝙖 𝙅𝙤𝙗 𝙁𝙖𝙞𝙧! \r\n\r\nPESO Zamboanga is excited to introduce the 𝙡𝙤𝙘𝙖𝙡 𝙖𝙣𝙙 𝙤𝙫𝙚𝙧𝙨𝙚𝙖𝙨 𝙚𝙢𝙥𝙡𝙤𝙮𝙚𝙧𝙨 participating in this year’s Dia de Zamboanga Job Fair on February 26, 2025, at KCC Mall de Zamboanga – East Wing! Get ready to explore 4,421 𝙡𝙤𝙘𝙖𝙡 𝙖𝙣𝙙 7,972 𝙤𝙫𝙚𝙧𝙨𝙚𝙖𝙨 𝙤𝙛 𝙟𝙤𝙗 𝙫𝙖𝙘𝙖𝙣𝙘𝙞𝙚𝙨 in various industries.', 'uploads/admin_job_thumbnail/admin_67db8bf4c1406.jpg', 'uploads/admin_job_photo/admin_67db8bf4c1842.jpg', '2025-02-25 05:05:16', 0, 'Calarian', NULL, 'Cashier:\r\n\r\n-Handle cash transactions with customers using cash registers.\r\n\r\n-Scan goods and ensure pricing is accurate.\r\n\r\n-Issue receipts, refunds, or change.\r\n\r\n-Count money in cash drawers at the beginning and end of shifts to ensure that amounts are correct.\r\n\r\nCustomer Service Representative:\r\n\r\n-Greet customers and provide assistance.\r\n\r\n-Handle customer complaints, provide appropriate solutions, and follow up to ensure resolution.\r\n\r\n-Maintain a positive and professional demeanor to enhance customer experience.\r\n\r\n-Assist with product inquiries and information.\r\n\r\nInventory Counter:\r\n\r\n-Monitor and maintain inventory levels.\r\n\r\n-Conduct regular inventory audits and stock checks.\r\n\r\n-Enter inventory data into the system.\r\n\r\n-Assist in restocking shelves and organizing the warehouse.\r\n\r\nMerchandiser:\r\n\r\n-Ensure merchandise is visually appealing and correctly displayed.\r\n\r\n-Arrange products and create attractive displays.\r\n\r\n-Monitor stock levels and coordinate with the inventory team.\r\n\r\nImplement promotional campaigns and signage.\r\n\r\nSales Associate:\r\n\r\n-Assist customers with product selection and inquiries.\r\n\r\nProcess sales transactions and handle customer payments.\r\n\r\nMaintain a clean and organized sales floor.\r\n\r\nAchieve sales targets and contribute to overall store profitability.', 'General Requirements:\r\n\r\n-High school diploma or equivalent.\r\n\r\n-Strong communication and interpersonal skills.\r\n\r\n-Customer-focused with a positive attitude.\r\n\r\n-Basic math skills for handling transactions.\r\n\r\n-Ability to work flexible hours, including weekends and holidays.\r\n\r\nSpecific Requirements:\r\n\r\nCashier: Previous cashier experience is a plus.\r\n\r\nCustomer Service Representative: Experience in customer service or retail preferred.\r\n\r\nInventory Counter: Attention to detail and accuracy in counting.\r\n\r\nMerchandiser: Creativity and a good eye for design.\r\n\r\nSales Associate: Sales experience is an advantage.', 'Previous experience in the retail industry.\r\n\r\nProficiency in using retail software and POS systems.\r\n\r\nAbility to work effectively in a team environment.\r\n\r\nStrong problem-solving skills.\r\n\r\nMultilingual skills are a plus.', 'approved', 0, NULL),
(87, 'KCC Mall de ZAMBOANGA', 'We are seeking a highly organized and proactive Administrative Assistant and Office Support professional to join our team. The ideal candidate will provide comprehensive administrative support to ensure the smooth operation of daily office activities. This role requires someone who can manage multiple responsibilities with efficiency, communicate effectively with internal and external stakeholders, and contribute to maintaining an organized and welcoming work environment.', 'uploads/employer_job_thumbnail/venard_67d6e4f6b2f97.png', 'uploads/employer_job_photo/venard_67d6e4f6b3228.jpeg', '2025-03-02 15:05:30', 0, 'Canelar', 'Camins', 'Administrative Support:\r\nManage calendars, schedule appointments, and coordinate meetings for executives and staff.\r\nHandle incoming and outgoing correspondence, including emails, phone calls, and mail.\r\nPrepare and edit documents, reports, presentations, and spreadsheets as required.\r\nMaintain filing systems (both digital and physical) to ensure easy access to information.\r\nOffice Management:\r\nOversee general office operations, including ordering supplies, managing inventory, and ensuring equipment is functioning properly.\r\nGreet visitors professionally and direct them appropriately.\r\nCoordinate travel arrangements, including booking flights, accommodations, and transportation.\r\nCommunication:\r\nAct as the first point of contact for clients, vendors, and employees.\r\nDraft and distribute memos, letters, and other communications on behalf of management.\r\nRespond promptly to inquiries and resolve issues as they arise.\r\nProject Coordination:\r\nAssist with special projects by conducting research, gathering data, and preparing su', 'High school diploma or equivalent; associate degree in business administration or related field is a plus.\r\nMinimum of 1-2 years of experience in administrative roles or office support positions.\r\nProficiency in Microsoft Office Suite (Word, Excel, PowerPoint, Outlook).\r\nExcellent verbal and written communication skills.\r\nStrong organizational and time-management abilities.\r\nAbility to multitask and prioritize tasks in a fast-paced environment.\r\nAttention to detail and problem-solving skills.\r\nProfessional demeanor and ability to maintain confidentiality.', 'Bachelor’s degree in Business Administration, Communications, or a related field.\r\nPrior experience using CRM software or project management tools (e.g., Asana, Trello, Salesforce).\r\nFamiliarity with bookkeeping or accounting principles is a plus.\r\nExperience working in a corporate or professional services environment.\r\nSoft Skills:\r\nExceptional interpersonal skills and the ability to build strong relationships.\r\nAdaptability and willingness to take initiative.\r\nPositive attitude and a team-player mindset.\r\nTechnical Skills:\r\nAdvanced proficiency in Excel (pivot tables, formulas, etc.).\r\nKnowledge of video conferencing platforms like Zoom or Microsoft Teams.\r\nBasic understanding of IT troubleshooting for common office technology issues.', 'approved', 21, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `job_categories`
--

CREATE TABLE `job_categories` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_categories`
--

INSERT INTO `job_categories` (`id`, `job_id`, `category_id`) VALUES
(141, 59, 29),
(142, 25, 30),
(144, 23, 5),
(145, 23, 11),
(146, 21, 5),
(166, 87, 2),
(175, 63, 3),
(176, 63, 6),
(177, 24, 9);

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
(122, 'Video Producer (Media)', 25),
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
(179, 'Video Producer (Creative)', 5),
(180, 'Cinematographer', 5),
(181, 'Sound Designer', 5),
(182, 'Music Composer', 5),
(183, 'Fashion Designer', 5),
(184, 'Fashion Illustrator', 5),
(185, 'Comic Book Artist', 5);

-- --------------------------------------------------------

--
-- Table structure for table `job_positions_jobs`
--

CREATE TABLE `job_positions_jobs` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_positions_jobs`
--

INSERT INTO `job_positions_jobs` (`id`, `job_id`, `position_id`) VALUES
(156, 59, 77),
(157, 59, 138),
(158, 25, 73),
(159, 25, 139),
(161, 23, 42),
(162, 21, 40),
(182, 87, 28),
(200, 63, 5),
(201, 63, 6),
(202, 63, 9),
(203, 63, 11),
(204, 63, 8),
(205, 63, 7),
(206, 24, 57);

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

-- --------------------------------------------------------

--
-- Table structure for table `job_preferences_positions`
--

CREATE TABLE `job_preferences_positions` (
  `id` int(11) NOT NULL,
  `job_preference_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_preferences_positions`
--

INSERT INTO `job_preferences_positions` (`id`, `job_preference_id`, `position_id`) VALUES
(8, 2, 77);

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `language_name` varchar(100) NOT NULL,
  `fluency` enum('Basic','Conversational','Fluent','Native') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `user_id`, `language_name`, `fluency`) VALUES
(3, 67, 'English', 'Conversational'),
(4, 67, 'Filipino', 'Fluent');

-- --------------------------------------------------------

--
-- Table structure for table `languages_list`
--

CREATE TABLE `languages_list` (
  `id` int(11) NOT NULL,
  `language_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages_list`
--

INSERT INTO `languages_list` (`id`, `language_name`) VALUES
(1, 'Afrikaans'),
(2, 'Albanian'),
(3, 'Amharic'),
(4, 'Arabic'),
(5, 'Armenian'),
(6, 'Bengali'),
(7, 'Bosnian'),
(8, 'Bulgarian'),
(9, 'Catalan'),
(10, 'Chinese'),
(11, 'Croatian'),
(12, 'Czech'),
(13, 'Danish'),
(14, 'Dutch'),
(15, 'English'),
(16, 'Estonian'),
(17, 'Filipino'),
(18, 'Finnish'),
(19, 'French'),
(20, 'Georgian'),
(21, 'German'),
(22, 'Greek'),
(23, 'Gujarati'),
(24, 'Haitian Creole'),
(25, 'Hebrew'),
(26, 'Hindi'),
(27, 'Hungarian'),
(28, 'Icelandic'),
(29, 'Igbo'),
(30, 'Indonesian'),
(31, 'Irish'),
(32, 'Italian'),
(33, 'Japanese'),
(34, 'Javanese'),
(35, 'Kannada'),
(36, 'Kazakh'),
(37, 'Khmer'),
(38, 'Korean'),
(39, 'Kurdish'),
(40, 'Latvian'),
(41, 'Lithuanian'),
(42, 'Macedonian'),
(43, 'Malay'),
(44, 'Mandarin'),
(45, 'Marathi'),
(46, 'Mongolian'),
(47, 'Nepali'),
(48, 'Norwegian'),
(49, 'Pashto'),
(50, 'Persian'),
(51, 'Polish'),
(52, 'Portuguese'),
(53, 'Punjabi'),
(54, 'Romanian'),
(55, 'Russian'),
(56, 'Serbian'),
(57, 'Sindhi'),
(58, 'Sinhala'),
(59, 'Slovak'),
(60, 'Slovenian'),
(61, 'Somali'),
(62, 'Spanish'),
(63, 'Swahili'),
(64, 'Swedish'),
(65, 'Tagalog'),
(66, 'Tamil'),
(67, 'Telugu'),
(68, 'Thai'),
(69, 'Turkish'),
(70, 'Ukrainian'),
(71, 'Urdu'),
(72, 'Uzbek'),
(73, 'Vietnamese'),
(74, 'Yoruba'),
(75, 'Zulu'),
(76, 'Burmese'),
(77, 'Maltese'),
(78, 'Armenian'),
(79, 'Tigrinya'),
(80, 'Hmong'),
(81, 'Twi'),
(82, 'Chichewa'),
(83, 'Hausa'),
(84, 'Yiddish'),
(85, 'Xhosa'),
(86, 'Quechua'),
(87, 'Maori'),
(88, 'Wolof'),
(89, 'Fijian'),
(90, 'Tibetan'),
(91, 'Malayalam'),
(92, 'Bengali'),
(93, 'Nepali'),
(94, 'Samoan');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `application_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_role` enum('admin','user','employer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `recipient_id`, `sender_id`, `job_id`, `application_id`, `message`, `is_read`, `created_at`, `user_role`) VALUES
(360, 21, 67, 87, 566, 'A new application has been submitted for your job: 87', 1, '2025-03-20 01:22:33', 'employer'),
(361, 4, 67, 87, 566, 'A new application has been submitted for the job: 87', 0, '2025-03-20 01:22:34', 'admin'),
(362, 4, 67, 63, 567, 'A new application has been submitted for the job: 63', 0, '2025-03-20 01:22:51', 'admin'),
(363, 4, 67, 23, 568, 'A new application has been submitted for the job: 23', 1, '2025-03-20 01:23:06', 'admin'),
(364, 67, 21, 87, NULL, 'Venard Jhon Salido has been accepted for the job: KCC Mall de ZAMBOANGA.', 1, '2025-03-21 15:30:10', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `references`
--

CREATE TABLE `references` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `workplace` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `references`
--

INSERT INTO `references` (`id`, `user_id`, `name`, `position`, `workplace`, `contact_number`, `created_at`) VALUES
(5, 67, 'Adelaida S. Imbing ', 'Administrative Officer IV', 'Land Transportation Office (Zamboanga City)', '09123456789', '2025-03-23 07:47:12');

-- --------------------------------------------------------

--
-- Table structure for table `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_jobs`
--

INSERT INTO `saved_jobs` (`id`, `user_id`, `job_id`, `saved_at`) VALUES
(23, 21, 15, '2025-02-16 12:58:42'),
(26, 21, 8, '2025-02-16 16:03:08'),
(33, 21, 22, '2025-02-19 07:06:26'),
(51, 4, 51, '2025-02-24 00:59:58'),
(52, 4, 26, '2025-02-24 01:00:00'),
(110, 69, 87, '2025-03-11 14:44:59');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `proficiency` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `user_id`, `skill_id`, `proficiency`) VALUES
(17, 67, 306, 'Beginner'),
(18, 67, 312, 'Beginner'),
(19, 67, 313, 'Beginner'),
(20, 67, 308, 'Beginner');

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
(1, 1, 'Financial Reporting'),
(2, 1, 'Bookkeeping'),
(3, 1, 'Taxation'),
(4, 1, 'Auditing'),
(5, 1, 'Budgeting'),
(6, 1, 'Accounting Software'),
(7, 2, 'Data Entry'),
(8, 2, 'Office Management'),
(9, 2, 'Customer Support'),
(10, 2, 'Scheduling'),
(11, 2, 'Administrative Assistance'),
(12, 2, 'Time Management'),
(13, 3, 'Social Media Marketing'),
(14, 3, 'Brand Management'),
(15, 3, 'Content Creation'),
(17, 3, 'PPC Advertising'),
(18, 3, 'Market Research'),
(19, 4, 'Structural Design'),
(20, 4, 'Electrical Engineering'),
(21, 4, 'Mechanical Engineering'),
(22, 4, 'CAD Software'),
(23, 4, 'Construction Project Management'),
(24, 4, 'Civil Engineering'),
(25, 5, 'Graphic Design'),
(26, 5, 'Photography'),
(27, 5, 'Video Editing'),
(28, 5, 'Animation (Art & Design)'),
(29, 5, 'Art Direction'),
(30, 5, 'UI/UX Design'),
(31, 6, 'Customer Relationship Management'),
(32, 6, 'Call Center Management'),
(33, 6, 'Problem Solving'),
(34, 6, 'Conflict Resolution'),
(35, 6, 'Empathy'),
(36, 6, 'Sales Support'),
(37, 7, 'Curriculum Development'),
(38, 7, 'Tutoring'),
(39, 7, 'Educational Technology'),
(40, 7, 'Lesson Planning'),
(41, 7, 'Classroom Management'),
(42, 7, 'Online Learning'),
(43, 8, 'Patient Care'),
(44, 8, 'Medical Research'),
(45, 8, 'Clinical Skills'),
(46, 8, 'Nursing'),
(47, 8, 'Medical Software'),
(48, 8, 'Pharmaceuticals'),
(49, 9, 'Hospitality Management'),
(50, 9, 'Event Planning'),
(51, 9, 'Guest Relations'),
(52, 9, 'Travel Management'),
(53, 9, 'Food & Beverage Service'),
(54, 9, 'Tourism Marketing'),
(55, 10, 'Recruitment & Talent Acquisition'),
(56, 10, 'Employee Training'),
(57, 10, 'HR Management'),
(58, 10, 'Compensation & Benefits'),
(59, 10, 'Payroll Management'),
(60, 11, 'Network Administration'),
(61, 11, 'Software Development (IT)'),
(62, 11, 'Database Management'),
(63, 11, 'Cloud Computing'),
(64, 11, 'IT Support'),
(66, 12, 'Contract Negotiation'),
(67, 12, 'Compliance Management'),
(68, 12, 'Intellectual Property'),
(69, 12, 'Legal Research'),
(70, 12, 'Litigation Support'),
(71, 12, 'Corporate Law'),
(72, 13, 'Manufacturing Process Management'),
(73, 13, 'Production Management'),
(74, 13, 'Quality Assurance'),
(75, 13, 'Industrial Engineering'),
(76, 13, 'Supply Chain Management'),
(77, 13, 'Lean Manufacturing'),
(78, 14, 'Project Planning'),
(79, 14, 'Risk Management'),
(80, 14, 'Budget Management'),
(81, 14, 'Agile Methodology'),
(82, 14, 'Project Scheduling'),
(83, 14, 'Stakeholder Management'),
(84, 15, 'Retail Management'),
(85, 15, 'Sales Forecasting'),
(86, 15, 'Customer Service'),
(87, 15, 'Inventory Management'),
(88, 15, 'Merchandising'),
(89, 15, 'Store Operations'),
(90, 16, 'Research Methodology'),
(91, 16, 'Data Analysis (Science & Research)'),
(92, 16, 'Scientific Writing'),
(93, 16, 'Laboratory Skills'),
(94, 16, 'Quantitative Research'),
(95, 16, 'Experimental Design'),
(96, 17, 'Construction Management'),
(97, 17, 'Electrical Installation'),
(98, 17, 'Blueprint Reading'),
(99, 17, 'Heavy Machinery Operation'),
(100, 17, 'Carpentry'),
(101, 17, 'Plumbing'),
(102, 18, 'Logistics Management'),
(103, 18, 'Supply Chain Optimization'),
(104, 18, 'Inventory Control'),
(105, 18, 'Warehouse Management'),
(106, 18, 'Shipping & Receiving'),
(107, 18, 'Freight Management'),
(108, 19, 'Network Infrastructure'),
(109, 19, 'Telecom Equipment'),
(110, 19, 'Telecommunications Systems'),
(111, 19, 'Signal Processing'),
(112, 19, 'Voice-over-IP'),
(113, 19, 'Radio Frequency Engineering'),
(114, 20, 'Content Writing'),
(115, 20, 'Copywriting'),
(116, 20, 'Technical Writing'),
(117, 20, 'Editing'),
(118, 20, 'Proofreading'),
(119, 20, 'Creative Writing'),
(120, 21, 'Business Strategy'),
(121, 21, 'Market Analysis'),
(122, 21, 'Operations Management'),
(123, 21, 'Financial Planning'),
(124, 21, 'Mergers & Acquisitions'),
(125, 21, 'Strategic Planning'),
(126, 22, 'Business Consulting'),
(127, 22, 'Process Improvement'),
(128, 22, 'Management Consulting'),
(129, 22, 'Financial Consulting'),
(131, 22, 'Change Management'),
(132, 23, 'Public Administration'),
(133, 23, 'Government Policy'),
(134, 23, 'Political Science'),
(135, 23, 'Public Policy Analysis'),
(136, 23, 'Crisis Management'),
(137, 23, 'Regulatory Compliance'),
(138, 24, 'Risk Assessment'),
(139, 24, 'Claims Management'),
(140, 24, 'Underwriting'),
(141, 24, 'Insurance Fraud Detection'),
(142, 24, 'Customer Relations'),
(143, 24, 'Loss Prevention'),
(144, 25, 'Journalism'),
(145, 25, 'Public Relations Strategy'),
(146, 25, 'Social Media'),
(147, 25, 'Broadcasting'),
(148, 25, 'Advertising'),
(149, 25, 'Media Buying'),
(150, 26, 'Social Work'),
(151, 26, 'Mental Health Counseling'),
(152, 26, 'Community Outreach'),
(153, 26, 'Nonprofit Fundraising'),
(154, 26, 'Grant Writing'),
(155, 26, 'Volunteer Coordination'),
(156, 27, 'Real Estate Sales'),
(157, 27, 'Property Management'),
(158, 27, 'Real Estate Investment'),
(159, 27, 'Real Estate Appraisal'),
(160, 27, 'Mortgage Lending'),
(161, 27, 'Lease Negotiation'),
(162, 28, 'Criminal Justice'),
(163, 28, 'Security Management'),
(164, 28, 'Law Enforcement'),
(165, 28, 'Investigation Skills'),
(166, 28, 'Crisis Intervention'),
(167, 28, 'Conflict Resolution'),
(168, 29, 'Sports Coaching'),
(169, 29, 'Fitness Training'),
(170, 29, 'Athletic Training'),
(171, 29, 'Team Management'),
(172, 29, 'Sports Psychology'),
(173, 29, 'Sports Marketing'),
(174, 30, 'Vehicle Maintenance'),
(175, 30, 'Fleet Management'),
(176, 30, 'Automotive Repair'),
(177, 30, 'Vehicle Diagnostics'),
(178, 30, 'Auto Sales'),
(179, 30, 'Transportation Logistics'),
(180, 31, 'Construction Labor'),
(185, 31, 'Electrical Work'),
(186, 32, 'Public Relations & Media Relations'),
(187, 32, 'Crisis Communication'),
(189, 32, 'Event Coordination'),
(190, 32, 'Press Releases'),
(191, 33, 'E-commerce Marketing'),
(192, 33, 'Digital Advertising'),
(193, 33, 'Online Store Management'),
(195, 33, 'Affiliate Marketing'),
(196, 33, 'Conversion Rate Optimization'),
(197, 34, 'Sustainability Reporting'),
(198, 34, 'Environmental Impact Assessment'),
(199, 34, 'Energy Efficiency'),
(200, 34, 'Environmental Law'),
(201, 34, 'Waste Management'),
(202, 34, 'Renewable Energy'),
(203, 35, 'Energy Management'),
(204, 35, 'Energy Auditing'),
(205, 35, 'Oil & Gas Operations'),
(206, 35, 'Utility Management'),
(208, 35, 'Energy Trading'),
(209, 36, 'Aerospace Engineering'),
(210, 36, 'Flight Operations'),
(211, 36, 'Aircraft Maintenance'),
(212, 36, 'Aviation Safety'),
(213, 36, 'Air Traffic Control'),
(214, 36, 'Aerodynamics'),
(215, 37, 'Software Development (Technology)'),
(216, 37, 'Systems Analysis'),
(217, 37, 'Database Administration'),
(218, 37, 'UI/UX Development'),
(219, 37, 'Machine Learning (Software)'),
(220, 37, 'Software Testing'),
(221, 38, 'E-commerce Management'),
(222, 38, 'Product Listing Optimization'),
(225, 38, 'Order Fulfillment'),
(227, 39, 'Film Production'),
(228, 39, 'Video Editing (Media)'),
(229, 39, 'Audio Engineering'),
(231, 39, 'Animation (Media Production)'),
(232, 39, 'Special Effects'),
(233, 40, 'Pharmaceutical Research'),
(234, 40, 'Biotech Innovation'),
(235, 40, 'Clinical Trials'),
(236, 40, 'Drug Development'),
(237, 40, 'Medical Device Design'),
(238, 40, 'Genomics'),
(239, 41, 'Culinary Arts'),
(240, 41, 'Restaurant Management'),
(244, 41, 'Menu Planning'),
(245, 42, 'Investment Management'),
(247, 42, 'Financial Analysis'),
(249, 42, 'Portfolio Management'),
(250, 42, 'Trading'),
(251, 43, 'Data Analysis (Analytics)'),
(252, 43, 'Data Visualization'),
(253, 43, 'Big Data'),
(255, 43, 'Statistical Analysis'),
(256, 43, 'Machine Learning (Analytics)'),
(260, 44, 'Commercial Real Estate'),
(261, 44, 'Real Estate Appraisal'),
(263, 45, 'Public Health'),
(264, 45, 'Epidemiology'),
(265, 45, 'Health Policy'),
(266, 45, 'Healthcare Administration'),
(267, 45, 'Health Communication'),
(268, 45, 'Healthcare Research'),
(269, 46, 'Telemedicine'),
(270, 46, 'Telehealth'),
(271, 46, 'Virtual Care'),
(272, 46, 'Medical Consultation'),
(273, 46, 'Telehealth Technology'),
(274, 46, 'Healthcare IT'),
(275, 47, 'AI Development'),
(276, 47, 'Robotics Engineering'),
(277, 47, 'Machine Learning (AI)'),
(278, 47, 'Natural Language Processing'),
(279, 47, 'Automation'),
(280, 47, 'Deep Learning'),
(281, 48, 'Counseling'),
(282, 48, 'Academic Advising'),
(283, 48, 'Tutoring'),
(284, 48, 'Special Education'),
(285, 48, 'School Psychology'),
(286, 48, 'Therapeutic Services'),
(287, 49, 'Event Planning'),
(288, 49, 'Event Coordination'),
(289, 49, 'Wedding Planning'),
(290, 49, 'Conference Management'),
(291, 49, 'Catering Management'),
(292, 49, 'Public Speaking'),
(293, 50, 'Network Security'),
(294, 50, 'Incident Response'),
(295, 50, 'Penetration Testing'),
(296, 50, 'Firewall Management'),
(297, 50, 'Ethical Hacking'),
(298, 50, 'Cyber Risk Management'),
(299, 51, 'Game Design'),
(300, 51, 'Interactive Media'),
(301, 51, 'Game Development'),
(302, 51, 'Game Testing'),
(303, 51, 'Game Art'),
(304, 51, 'Game Programming'),
(305, 11, 'Web Development (Frontend)'),
(306, 11, 'Web Development (Backend)'),
(307, 11, 'Full Stack Development'),
(308, 11, 'HTML5 & CSS3'),
(309, 11, 'JavaScript (Web Development)'),
(310, 11, 'React.js'),
(311, 11, 'Node.js'),
(312, 11, 'PHP Development'),
(313, 11, 'Django (Python)'),
(314, 11, 'Ruby on Rails'),
(315, 11, 'API Integration'),
(316, 11, 'Mobile App Development'),
(317, 11, 'Mobile App Testing'),
(318, 37, 'Mobile Development (Android)'),
(319, 37, 'Mobile Development (iOS)'),
(320, 37, 'Flutter Development'),
(321, 37, 'React Native Development'),
(322, 37, 'Xamarin Development'),
(323, 37, 'UI/UX for Mobile Apps'),
(324, 37, 'App Development (Hybrid)'),
(325, 37, 'Swift Development'),
(326, 37, 'Kotlin Development'),
(327, 11, 'Cloud Computing (AWS)'),
(328, 11, 'Cloud Computing (Azure)'),
(329, 11, 'Cloud Infrastructure'),
(330, 11, 'Cloud Storage Solutions'),
(331, 11, 'Serverless Computing'),
(332, 11, 'DevOps'),
(333, 11, 'Docker'),
(334, 11, 'Kubernetes'),
(335, 37, 'Web Application Development'),
(336, 37, 'Web Development (PHP Frameworks)'),
(337, 37, 'JavaScript Frameworks (Angular, Vue.js)'),
(338, 11, 'CSS Preprocessing (SASS, LESS)'),
(339, 11, 'Web Performance Optimization'),
(340, 11, 'Web Security (HTTPS, SSL)'),
(341, 37, 'Database Development (SQL)'),
(342, 37, 'NoSQL Database (MongoDB, Firebase)'),
(343, 37, 'Database Design'),
(344, 37, 'Version Control (Git)'),
(345, 37, 'Agile Web Development'),
(346, 37, 'Website Optimization (SEO)'),
(347, 11, 'SEO (Search Engine Optimization)'),
(348, 11, 'Web Analytics (Google Analytics)'),
(349, 37, 'Progressive Web Apps (PWA)'),
(350, 37, 'Web Accessibility (WCAG)'),
(351, 37, 'Content Management Systems (WordPress, Drupal)'),
(352, 37, 'eCommerce Development (Shopify, WooCommerce)'),
(353, 37, 'Payment Gateway Integration'),
(354, 37, 'Content Delivery Networks (CDN)'),
(355, 11, 'Business Intelligence (BI)'),
(356, 11, 'Data Warehousing'),
(357, 11, 'ETL Development'),
(358, 43, 'Data Engineering'),
(359, 43, 'Data Wrangling'),
(360, 43, 'Data Mining'),
(361, 43, 'Data Modeling'),
(362, 43, 'Big Data Technologies (Hadoop, Spark)'),
(363, 43, 'Predictive Analytics'),
(364, 43, 'Business Analytics'),
(365, 43, 'Data Integration (ETL Tools)'),
(366, 43, 'Data Quality Management'),
(367, 43, 'Data Governance'),
(368, 43, 'Cloud Data Storage'),
(369, 37, 'Game Development (Unity)'),
(370, 37, 'Game Development (Unreal Engine)'),
(371, 51, 'Game Programming (C++)'),
(372, 51, 'Game Programming (C#)'),
(373, 51, 'Augmented Reality Development (AR)'),
(374, 51, 'Virtual Reality Development (VR)'),
(375, 51, 'Interactive Media Design'),
(376, 51, '3D Modeling for Games'),
(377, 51, 'Game Animation'),
(378, 37, 'Machine Learning Engineering'),
(379, 37, 'Deep Learning Development'),
(380, 37, 'Natural Language Processing (NLP)'),
(381, 11, 'Automation Testing'),
(382, 11, 'Unit Testing'),
(383, 11, 'Test-Driven Development (TDD)'),
(384, 11, 'Continuous Integration / Continuous Deployment (CI/CD)'),
(385, 11, 'Agile Software Development'),
(386, 11, 'Software Architecture Design'),
(387, 37, 'System Design & Architecture'),
(389, 37, 'API Development & Design'),
(390, 37, 'Blockchain Development'),
(391, 37, 'Blockchain Smart Contracts'),
(392, 11, 'Cybersecurity (Network Security)'),
(393, 11, 'Penetration Testing (Ethical Hacking)'),
(394, 50, 'Cybersecurity Risk Assessment'),
(395, 50, 'Security Operations Center (SOC)'),
(396, 50, 'Identity and Access Management (IAM)'),
(397, 50, 'Incident Response and Forensics'),
(398, 50, 'Cryptography'),
(399, 50, 'Firewalls & VPN Configuration'),
(400, 50, 'Data Encryption'),
(401, 50, 'Security Audits'),
(402, 50, 'Malware Analysis'),
(403, 50, 'Cloud Security (AWS, Azure, GCP)'),
(404, 37, 'Robotic Process Automation (RPA)'),
(405, 37, 'Artificial Intelligence (AI) Development'),
(406, 37, 'AI Algorithms'),
(407, 47, 'Robotics Engineering (AI)'),
(408, 47, 'AI for Automation'),
(409, 47, 'Reinforcement Learning'),
(410, 47, 'Machine Vision'),
(411, 47, 'Robotic Simulation'),
(412, 37, 'IoT Development (Internet of Things)'),
(413, 37, 'Embedded Software Development'),
(414, 37, 'Industrial IoT (IIoT)'),
(415, 37, 'IoT Architecture Design'),
(416, 37, 'Edge Computing for IoT'),
(417, 47, 'AI Chatbots'),
(418, 47, 'AI-powered Data Analysis'),
(419, 47, 'AI for Healthcare'),
(420, 47, 'Machine Learning for Finance'),
(421, 47, 'Speech Recognition Systems'),
(422, 47, 'Autonomous Vehicles Development'),
(423, 43, 'Quantitative Analysis'),
(424, 43, 'Statistical Modeling'),
(425, 43, 'Market Research Analytics'),
(426, 43, 'Sentiment Analysis'),
(428, 43, 'Customer Analytics'),
(429, 43, 'Predictive Modeling'),
(430, 43, 'Risk Analytics'),
(431, 43, 'A/B Testing'),
(432, 43, 'Business Forecasting'),
(433, 11, 'E-commerce Development (Magento)'),
(434, 11, 'SEO for E-commerce'),
(435, 33, 'Digital Marketing (Content Marketing)'),
(436, 33, 'Pay-Per-Click Advertising (PPC)'),
(437, 33, 'Social Media Ads'),
(438, 33, 'Email Marketing'),
(439, 33, 'Online Reputation Management'),
(440, 33, 'Affiliate Marketing (E-commerce)'),
(441, 33, 'Influencer Marketing'),
(442, 33, 'Web Analytics (Google Tag Manager)'),
(443, 33, 'Brand Strategy'),
(444, 33, 'Conversion Rate Optimization (CRO)'),
(445, 33, 'SEO Copywriting'),
(446, 33, 'Content Strategy'),
(447, 37, 'App Store Optimization (ASO)'),
(448, 37, 'Android Development (Kotlin)'),
(449, 37, 'iOS Development (Swift)'),
(450, 37, 'Mobile App UI/UX Design'),
(451, 37, 'Mobile App Testing (QA)'),
(452, 37, 'Cross-Platform App Development'),
(453, 37, 'UX/UI Research for Mobile Apps'),
(454, 37, 'Mobile Game Development'),
(455, 37, 'Mobile App Performance Optimization'),
(456, 37, 'User Interface (UI) Design'),
(457, 37, 'User Experience (UX) Design'),
(458, 17, 'Forklift Operation'),
(459, 17, 'Crane Operation'),
(460, 17, 'Construction Equipment Operation'),
(462, 17, 'Site Preparation'),
(463, 17, 'Concrete Mixing'),
(464, 17, 'Steel Reinforcement'),
(465, 17, 'Demolition'),
(466, 17, 'Blueprint Reading (Construction)'),
(467, 17, 'Masonry'),
(469, 17, 'Roofing'),
(471, 17, 'Electrical Wiring (Construction)'),
(472, 17, 'HVAC Installation'),
(473, 17, 'Welding'),
(474, 17, 'Pipe Fitting'),
(475, 17, 'Scaffolding'),
(476, 17, 'Ironworking'),
(477, 17, 'Paving'),
(478, 17, 'Drywall Installation'),
(480, 17, 'Asphalt Paving'),
(481, 30, 'Truck Driving (Class A CDL)'),
(482, 30, 'Forklift Driving'),
(483, 30, 'Delivery Truck Driving'),
(484, 30, 'Passenger Bus Driving'),
(485, 30, 'Heavy Equipment Transport'),
(486, 30, 'Vehicle Operation (Automotive)'),
(487, 30, 'Driver Safety Training'),
(488, 30, 'Long-Haul Trucking'),
(489, 30, 'Route Planning'),
(490, 30, 'Vehicle Maintenance (Driver)'),
(491, 30, 'Defensive Driving'),
(492, 30, 'Hazardous Materials Transportation (HAZMAT)'),
(493, 30, 'GPS Navigation'),
(494, 30, 'Road Safety Regulations'),
(495, 30, 'Cargo Handling'),
(496, 30, 'Cargo Securing'),
(497, 30, 'Driving Log Management'),
(498, 30, 'Fleet Management (Driver Focused)'),
(499, 13, 'Machine Operation (Manufacturing)'),
(500, 13, 'CNC Machine Operation'),
(501, 13, 'Metal Fabrication'),
(502, 13, 'Lathe Operation'),
(503, 13, 'Milling Machine Operation'),
(504, 13, '3D Printing (Manufacturing)'),
(505, 13, 'Robotic Arm Operation'),
(506, 13, 'Assembly Line Operation'),
(507, 13, 'Quality Control (Manufacturing)'),
(508, 13, 'Product Inspection'),
(509, 13, 'Welding Techniques (MIG, TIG, Stick)'),
(510, 13, 'Laser Cutting'),
(511, 13, 'Plasma Cutting'),
(512, 13, 'Blueprint Reading (Manufacturing)'),
(513, 13, 'Industrial Equipment Maintenance'),
(514, 13, 'Machinery Calibration'),
(515, 17, 'Welding (TIG)'),
(516, 17, 'Welding (MIG)'),
(517, 17, 'Welding (Stick)'),
(518, 17, 'Welding (Arc)'),
(519, 17, 'Welding (Gas Tungsten Arc)'),
(520, 17, 'Welding Inspection'),
(521, 17, 'Welding Safety Protocols'),
(522, 17, 'Welding Machine Setup'),
(523, 17, 'Metalworking'),
(524, 17, 'Soldering'),
(525, 17, 'Sheet Metal Fabrication'),
(527, 17, 'Welding Blueprint Interpretation'),
(528, 17, 'Pipe Welding'),
(529, 17, 'Structural Welding'),
(530, 17, 'Welding Certification (AWS)'),
(531, 17, 'Welding Quality Control'),
(532, 17, 'Welding Safety Standards'),
(533, 30, 'Driving Safety Protocols'),
(534, 30, 'Defensive Driving Techniques'),
(535, 30, 'Roadside Assistance Skills'),
(536, 30, 'Vehicle Inspection and Diagnostics'),
(537, 30, 'Passenger Safety Regulations'),
(538, 30, 'Driving License Management'),
(539, 30, 'Driving Record Maintenance'),
(540, 30, 'Transport Regulations (DOT)'),
(541, 30, 'Navigation Software (GPS, Apps)'),
(542, 17, 'Electrician (Industrial)'),
(543, 17, 'Electrical Panel Installation'),
(544, 17, 'Circuit Design (Industrial)'),
(545, 17, 'Electrical Wiring (Industrial)'),
(546, 17, 'Electrical Testing and Troubleshooting'),
(547, 17, 'Electric Motor Repair'),
(548, 17, 'Solar Panel Installation'),
(549, 17, 'HVAC System Design & Installation'),
(550, 17, 'Refrigeration System Installation'),
(551, 17, 'Wiring Diagrams'),
(552, 17, 'Electrical Maintenance'),
(553, 17, 'Automation Systems Wiring'),
(554, 17, 'PLC Programming (Industrial Control Systems)'),
(555, 17, 'Hydraulic Systems Maintenance'),
(556, 17, 'Pneumatic Systems Maintenance'),
(557, 17, 'Locksmithing'),
(558, 17, 'Drywall Finishing'),
(559, 17, 'Tile Installation'),
(560, 17, 'Painting and Coating Techniques'),
(561, 17, 'Asbestos Removal (Certified)'),
(562, 17, 'Hazardous Waste Handling'),
(563, 17, 'Construction Site Safety'),
(564, 17, 'Forklift Certification'),
(565, 17, 'Lifting Equipment Operation'),
(566, 17, 'Excavator Operation'),
(567, 17, 'Dump Truck Operation'),
(568, 17, 'Construction Laborer Skills'),
(569, 17, 'Sewing Machine Operation'),
(570, 17, 'Cabinet Making'),
(571, 17, 'Pallet Jack Operation'),
(572, 17, 'Window Installation'),
(573, 17, 'Insulation Installation'),
(574, 17, 'Roof Installation'),
(575, 17, 'Waterproofing'),
(576, 17, 'Site Surveying'),
(577, 17, 'Geotechnical Analysis'),
(578, 17, 'Tile Cutting'),
(579, 17, 'Landscape Construction'),
(580, 17, 'Fence Installation'),
(581, 17, 'Paving and Road Construction'),
(582, 17, 'Excavation & Grading'),
(583, 17, 'Construction Estimating'),
(584, 17, 'Plastering'),
(585, 17, 'Flooring Installation (Hardwood, Laminate)'),
(586, 17, 'Bricklaying'),
(587, 17, 'Stone Masonry'),
(588, 17, 'Gutter Installation'),
(589, 17, 'Concrete Pouring'),
(590, 17, 'Road Paving & Asphalt'),
(591, 17, 'Paving Stone Installation'),
(592, 17, 'Demolition (Residential & Commercial)'),
(593, 17, 'Construction Clean-Up'),
(594, 17, 'Heavy Equipment Maintenance'),
(595, 17, 'Trenching'),
(596, 17, 'Concrete Forming'),
(597, 17, 'Backhoe Operation'),
(598, 17, 'Tractor Operation'),
(599, 17, 'Paving Equipment Operation'),
(600, 5, 'Illustration (Traditional)'),
(601, 5, 'Illustration (Digital)'),
(602, 5, 'Concept Art'),
(603, 5, 'Character Design'),
(604, 5, 'Sketching (Pencil/Ink)'),
(605, 5, 'Storyboarding'),
(606, 5, 'Comic Art'),
(607, 5, '3D Sketching (Conceptual)'),
(608, 5, 'Fashion Illustration'),
(609, 5, 'Architectural Drawing (2D)'),
(610, 5, 'Architectural Drawing (3D)'),
(611, 5, 'Perspective Drawing'),
(612, 5, 'Technical Drawing (AutoCAD)'),
(613, 5, 'Industrial Design Drawing'),
(614, 5, 'Product Design Sketching'),
(615, 5, 'Logo Design'),
(616, 5, 'Infographics Design'),
(617, 5, 'Vector Illustration (Adobe Illustrator)'),
(618, 5, 'Graphic Design (Adobe Photoshop)'),
(619, 5, 'Typography (Hand Lettering)'),
(620, 5, 'Printmaking'),
(621, 5, 'Cartooning'),
(622, 5, 'Urban Sketching'),
(623, 5, 'Medical Illustration'),
(624, 5, 'Storyboard Art'),
(625, 5, 'Architectural Rendering'),
(626, 5, 'Landscape Drawing'),
(627, 5, 'Interior Design Sketching'),
(628, 5, '3D Modeling (SketchUp)'),
(629, 5, 'CAD (Computer-Aided Design)'),
(630, 5, 'Product Conceptual Drawing'),
(631, 5, 'Drafting (Technical)'),
(632, 5, 'Wireframe Design (UI/UX)'),
(633, 5, 'UI/UX Sketching'),
(634, 5, 'Digital Art (Procreate, Photoshop, etc.)'),
(635, 5, 'Fine Art Drawing (Charcoal, Pencil, Pastels)'),
(636, 5, 'Watercolor Illustration'),
(637, 5, 'Oil Painting (Sketching)'),
(638, 5, 'Sculpture Sketching'),
(639, 5, 'Animation Drawing (Character & Scene Design)'),
(640, 5, 'Caricature Drawing'),
(641, 5, 'Art Therapy Drawing'),
(642, 5, 'Architectural Drafting'),
(643, 5, 'Cartography (Map Drawing)'),
(644, 5, 'Portrait Drawing'),
(645, 5, 'Product Sketching for Manufacturing'),
(646, 5, 'Textile Design Drawing'),
(647, 5, 'Furniture Design Sketching');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user','employer') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `ext_name` varchar(10) DEFAULT NULL,
  `gender` enum('Male','Female','Non-Binary','LGBTQ+','Other') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `place_of_birth` varchar(100) DEFAULT NULL,
  `civil_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `uploaded_file` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `linkedin_profile` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `resume_file` varchar(255) DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `verification_token` varchar(32) DEFAULT NULL,
  `is_verified` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `created_at`, `username`, `first_name`, `middle_name`, `last_name`, `ext_name`, `gender`, `birth_date`, `age`, `phone_number`, `place_of_birth`, `civil_status`, `zip_code`, `street_address`, `barangay`, `city`, `uploaded_file`, `caption`, `linkedin_profile`, `portfolio_url`, `resume_file`, `cover_photo`, `verification_token`, `is_verified`) VALUES
(4, 'admin@gmail.com', '$2y$10$SN/q1gAReCmS2Xp.db2AQOVa87BhCpCDSrarPVa3FsadL2d9H1yhy', 'admin', '2025-02-09 04:34:27', 'admin', 'Super ', NULL, 'Admin', NULL, 'Other', '1956-02-17', 69, '09058316452', '', 'Single', '7000', 'Gov. Alvarez Street', 'Barangay Zone II', 'Zamboanga City', '../uploads/profile_admin/anonymous-8291223_960_720.webp', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', '', '', NULL, '../uploads/cover_admin/67d6768ee144f_67d58bdcc3c36_anonymouse.png', NULL, 1),
(21, 'venard@gmail.com', '$2y$10$6eeUccpKCoQi8PU1kuVTfuT9wgQEzsECkbISiMGZwQZT0XpHaJlsi', 'employer', '2025-02-12 07:38:43', 'venard', 'Venard Jhon', 'C.', 'Salido', NULL, 'Male', '1994-05-12', 30, '09351363586', 'ZC', 'Single', '7000', 'Kaputatan, Little Baguio', '64', 'Zamboanga City', '../uploads/profile_employer/465786029_9219920348037823_7671980852851727402_n.jpg', 'hehehehehe', 'https://www.linkedin.com/in/venard-jhon-cabahug-salido-08041434b/', 'https://venardjhoncsalido.netlify.app/', '../uploads/company_docu/21_1742131644.pdf', '../uploads/cover_employer/venard.jpg', NULL, 1),
(57, 'borat@gmail.com', '$2y$10$UCa0zO2ktIaQXhcCgbteG.4TiB/YpvKgSDECvTo4fEHDzWzpRNag6', 'user', '2025-02-19 13:40:41', 'borat', 'Borat', '', 'Sagdiyev', NULL, 'Male', '1980-05-12', 44, '09265605771', 'Zamboanga City', 'Widowed', '7000', 'Gov. Alvarez Street', '19', 'Zamboanga City', '../uploads/profile_user/borat.jpg', 'Very nice! how much?', '', '', NULL, '../uploads/cover_user/borat.jpg', NULL, 1),
(61, 'juan@gmail.com', '$2y$10$KJzMMaHZCI0fkA7LdkfzVuJKkgCKPphStkQAUSyb6JA9HoAsTsoSa', 'user', '2025-03-03 10:56:31', 'Juan', 'Juan', 'Tamad', 'Pendeho', '', 'Male', '1995-03-03', 30, '09351363586', 'Zamboanga City', '', '7000', 'Gov. Alvarez Street!', '', 'Zamboanga City!', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(67, 'sl201101795@wmsu.edu.ph', '$2y$10$xCM5uw39UdJOzH9bE2TBF.rceNM6FbEvdtWXbTaz0hoEf0eVzn88y', 'user', '2025-03-09 14:34:31', 'vengwapo', 'Venard Jhon', 'Cabahug', 'Salido', NULL, 'Male', '1999-05-12', 30, '09351363586', NULL, 'Single', '7000', 'Kaputatan, Little Baguio', '64', 'Zamboanga City', '../uploads/profile_user/vengwapo.jpg', 'Just a little boy', 'https://www.linkedin.com/in/venard-jhon-cabahug-salido-08041434b/', 'https://venardjhoncsalido.netlify.app/', '../uploads/resumes/vengwapo.pdf', '../uploads/cover_user/vengwapo.jpg', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `work_experience`
--

CREATE TABLE `work_experience` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `job_description` text NOT NULL,
  `employment_type` enum('fulltime','parttime','self-employed','freelance','contract','internship','apprenticeship','seasonal','home-based','domestic','temporary','volunteer') NOT NULL,
  `job_location` enum('local','overseas') NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `work_type` enum('remote','onsite','hybrid') NOT NULL DEFAULT 'onsite',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `currently_working` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_experience`
--

INSERT INTO `work_experience` (`id`, `user_id`, `job_title`, `company_name`, `job_description`, `employment_type`, `job_location`, `country`, `work_type`, `start_date`, `end_date`, `currently_working`) VALUES
(43, 67, 'Web Developer', 'TechTrek', 'Designed, developed, and maintained responsive websites and web applications.\r\nCollaborated with designers and cross-functional teams to implement user-friendly and visually appealing web solutions.', 'fulltime', 'local', NULL, 'onsite', '2025-03-18', '2025-03-21', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `application_positions`
--
ALTER TABLE `application_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_position` (`position_id`),
  ADD KEY `application_positions_ibfk_1` (`application_id`);

--
-- Indexes for table `barangay`
--
ALTER TABLE `barangay`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `browse`
--
ALTER TABLE `browse`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `employers`
--
ALTER TABLE `employers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `employer_requests`
--
ALTER TABLE `employer_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `employer_request_proofs`
--
ALTER TABLE `employer_request_proofs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `homepage`
--
ALTER TABLE `homepage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_categories`
--
ALTER TABLE `job_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_category_id` (`category_id`);

--
-- Indexes for table `job_positions`
--
ALTER TABLE `job_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `job_positions_jobs`
--
ALTER TABLE `job_positions_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_position_id` (`position_id`);

--
-- Indexes for table `job_preferences`
--
ALTER TABLE `job_preferences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `job_preferences_positions`
--
ALTER TABLE `job_preferences_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_preference_id` (`job_preference_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `languages_list`
--
ALTER TABLE `languages_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `references`
--
ALTER TABLE `references`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_job` (`user_id`,`job_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `skill_list`
--
ALTER TABLE `skill_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `work_experience`
--
ALTER TABLE `work_experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=569;

--
-- AUTO_INCREMENT for table `application_positions`
--
ALTER TABLE `application_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=295;

--
-- AUTO_INCREMENT for table `barangay`
--
ALTER TABLE `barangay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `browse`
--
ALTER TABLE `browse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `employers`
--
ALTER TABLE `employers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employer_requests`
--
ALTER TABLE `employer_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `employer_request_proofs`
--
ALTER TABLE `employer_request_proofs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `homepage`
--
ALTER TABLE `homepage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `job_categories`
--
ALTER TABLE `job_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

--
-- AUTO_INCREMENT for table `job_positions`
--
ALTER TABLE `job_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `job_positions_jobs`
--
ALTER TABLE `job_positions_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT for table `job_preferences`
--
ALTER TABLE `job_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `job_preferences_positions`
--
ALTER TABLE `job_preferences_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `languages_list`
--
ALTER TABLE `languages_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=365;

--
-- AUTO_INCREMENT for table `references`
--
ALTER TABLE `references`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `skill_list`
--
ALTER TABLE `skill_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=648;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `work_experience`
--
ALTER TABLE `work_experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_positions`
--
ALTER TABLE `application_positions`
  ADD CONSTRAINT `application_positions_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_positions_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `job_positions` (`id`),
  ADD CONSTRAINT `fk_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  ADD CONSTRAINT `fk_position` FOREIGN KEY (`position_id`) REFERENCES `job_positions` (`id`);

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `education_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employers`
--
ALTER TABLE `employers`
  ADD CONSTRAINT `employers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `employer_requests`
--
ALTER TABLE `employer_requests`
  ADD CONSTRAINT `employer_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_request_proofs`
--
ALTER TABLE `employer_request_proofs`
  ADD CONSTRAINT `employer_request_proofs_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `employer_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_categories`
--
ALTER TABLE `job_categories`
  ADD CONSTRAINT `fk_job_categories_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_categories_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_positions`
--
ALTER TABLE `job_positions`
  ADD CONSTRAINT `job_positions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_positions_jobs`
--
ALTER TABLE `job_positions_jobs`
  ADD CONSTRAINT `fk_job_positions_jobs_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_positions_jobs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_positions_jobs_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `job_positions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_preferences`
--
ALTER TABLE `job_preferences`
  ADD CONSTRAINT `job_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_preferences_positions`
--
ALTER TABLE `job_preferences_positions`
  ADD CONSTRAINT `job_preferences_positions_ibfk_1` FOREIGN KEY (`job_preference_id`) REFERENCES `job_preferences` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_preferences_positions_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `job_positions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `languages`
--
ALTER TABLE `languages`
  ADD CONSTRAINT `languages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_4` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `references`
--
ALTER TABLE `references`
  ADD CONSTRAINT `references_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skill_list` (`id`);

--
-- Constraints for table `skill_list`
--
ALTER TABLE `skill_list`
  ADD CONSTRAINT `skill_list_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_experience`
--
ALTER TABLE `work_experience`
  ADD CONSTRAINT `work_experience_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
