-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Hôte : sql213.infinityfree.com
-- Généré le :  ven. 06 fév. 2026 à 10:20
-- Version du serveur :  11.4.9-MariaDB
-- Version de PHP :  7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `if0_41085742_swapdb`
--

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

CREATE TABLE `category` (
  `CategoryId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `CategoryName` varchar(100) NOT NULL UNIQUE,
  `CategoryDescription` text DEFAULT NULL,
  `PostCount` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`CategoryId`, `CategoryName`, `CategoryDescription`, `PostCount`) VALUES
(1, 'Technology & Programming', 'Web development, software engineering, databases, and IT skills', 0),
(2, 'Design & Creative', 'Graphic design, UI/UX, illustration, and digital art', 0),
(3, 'Photography & Video', 'Photography, videography, editing, and visual media', 0),
(4, 'Writing & Content', 'Creative writing, copywriting, blogging, and content creation', 0),
(5, 'Music & Performance', 'Musical instruments, singing, music theory, and performance arts', 0),
(6, 'Cooking & Culinary', 'Cooking techniques, baking, cuisine specialties, and food preparation', 0),
(7, 'Health & Fitness', 'Exercise, nutrition, wellness, and physical training', 0),
(8, 'Languages', 'Foreign languages, translation, and communication skills', 0),
(9, 'Automotive & Mechanics', 'Car maintenance, repairs, and automotive knowledge', 0),
(10, 'Gardening & Nature', 'Gardening, landscaping, plant care, and horticulture', 0),
(11, 'Business & Finance', 'Entrepreneurship, investing, accounting, and business management', 0),
(12, 'Arts & Crafts', 'Painting, drawing, sculpture, and handmade crafts', 0),
(13, 'Home Improvement', 'DIY projects, construction, repairs, and home maintenance', 0),
(14, 'Cleaning & Organization', 'Home organization, cleaning techniques, and decluttering', 0),
(15, 'Science & Education', 'STEM subjects, tutoring, and educational support', 0),
(16, 'Fashion & Beauty', 'Sewing, styling, makeup, and personal appearance', 0);

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `credittransactions`
--

CREATE TABLE `credittransactions` (
  `TransactionId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `UserId` int(11) NOT NULL,
  `TransactionType` enum('earned','spent') NOT NULL,
  `Amount` int(11) NOT NULL,
  `BalanceAfter` int(11) NOT NULL,
  `RelatedEntityType` varchar(50),
  `RelatedEntityId` int(11),
  `Description` text,
  `CreatedAt` timestamp DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user_transactions` (`UserId`),
  CONSTRAINT `fk_credit_user` FOREIGN KEY (`UserId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `credittransactions`
--

INSERT INTO `credittransactions` (`TransactionId`, `UserId`, `TransactionType`, `Amount`, `BalanceAfter`, `RelatedEntityType`, `RelatedEntityId`, `Description`, `CreatedAt`) VALUES
(1, 1, 'earned', 50, 300, 'exchange', 1, 'Exchange completed with Fatima Debbache', '2025-12-16 22:11:46'),
(2, 2, 'spent', 45, 255, 'exchange', 1, 'JavaScript course with Youssef Benhadj', '2025-12-16 22:11:46'),
(3, 3, 'earned', 60, 240, 'exchange', 3, 'Photo session completed with Ahmed', '2025-12-16 22:11:46'),
(4, 4, 'spent', 50, 170, 'exchange', 4, 'Blog content writing with Leila', '2025-12-16 22:11:46'),
(5, 5, 'earned', 45, 235, 'exchange', 5, 'Guitar lessons given to Karim', '2025-12-16 22:11:46'),
(6, 6, 'spent', 55, 225, 'exchange', 6, 'Cooking coaching with Amina', '2025-12-16 22:11:46'),
(7, 7, 'earned', 100, 310, 'bonus', NULL, 'Bonus welcome new member', '2025-12-16 22:11:46'),
(8, 8, 'spent', 40, 220, 'exchange', 8, 'English tutoring with Samira', '2025-12-16 22:11:46'),
(9, 10, 'earned', 80, 310, 'exchange', 9, 'Mechanics diagnostics completed', '2025-12-16 22:11:46'),
(10, 26, 'earned', 50, 320, 'exchange', 10, 'Organic gardening consultation provided', '2025-12-16 22:11:46'),
(11, 30, 'spent', 50, 30, 'exchange', 76, 'aaaaaaaaaaa with salam', '2026-02-05 18:34:53'),
(12, 28, 'earned', 50, 50, 'exchange', 76, 'aaaaaaaaaaa from hello', '2026-02-05 18:34:53'),
(13, 28, 'spent', 20, 30, 'event', 23, 'Event: salam', '2026-02-05 19:20:55'),
(14, 30, 'earned', 20, 50, 'event', 23, 'Event: salam from salam', '2026-02-05 19:20:55'),
(15, 28, 'spent', 20, 10, 'event', 23, 'Event: salam', '2026-02-05 19:22:18'),
(16, 30, 'earned', 20, 70, 'event', 23, 'Event: salam from salam', '2026-02-05 19:22:18');

-- --------------------------------------------------------

--
-- Structure de la table `events`
--

CREATE TABLE `events` (
  `EventId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `OrganizerId` int(11) NOT NULL,
  `EventTitle` varchar(150) NOT NULL,
  `EventDescription` text NOT NULL,
  `EventLocation` varchar(150) DEFAULT NULL,
  `EventType` enum('online','in-person') NOT NULL,
  `EventStartDate` datetime NOT NULL,
  `EventEndDate` datetime NOT NULL,
  `MaxAttendees` int(11) NOT NULL,
  `CurrentAttendeesNumber` int(11) DEFAULT 0,
  `EventCost` decimal(10,2) DEFAULT 0,
  `EventStatus` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `CreatedAt` timestamp DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_organizer` (`OrganizerId`),
  CONSTRAINT `fk_event_organizer` FOREIGN KEY (`OrganizerId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `events`
--

INSERT INTO `events` (`EventId`, `OrganizerId`, `EventTitle`, `EventDescription`, `EventLocation`, `EventType`, `EventStartDate`, `EventEndDate`, `MaxAttendees`, `CurrentAttendeesNumber`, `EventCost`, `EventStatus`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, 'Web Development Workshop', 'Join us for an intensive hands-on workshop where we\'ll dive deep into modern web development practices. This workshop is designed for both beginners and intermediate developers who want to enhance their skills in HTML, CSS, and JavaScript.', 'Community Center, Room 304', 'in-person', '2026-02-13 02:31:46', '2026-02-13 08:31:46', 15, 8, 50, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(2, 2, 'Advanced React.js Bootcamp', 'Deep dive into React.js with advanced concepts including hooks, context API, Redux, and performance optimization. Perfect for developers who already know JavaScript basics.', 'Tech Hub Downtown', 'in-person', '2026-02-20 02:31:46', '2026-02-22 02:31:46', 20, 15, 75, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(3, 3, 'Node.js Backend Development', 'Learn to build scalable backend applications using Node.js and Express.js. We\'ll cover REST APIs, database integration, authentication, and deployment.', 'Online', 'online', '2026-02-16 02:31:46', '2026-02-18 02:31:46', 25, 18, 60, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(4, 3, 'Photography Basics Meetup', 'Learn the fundamentals of photography in this practical meetup. We\'ll explore camera settings, composition techniques, and lighting principles. Bring your camera or smartphone.', 'City Park', 'in-person', '2026-02-11 02:31:46', '2026-02-11 05:31:46', 20, 12, 25, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(5, 4, 'Advanced Photography Workshop', 'Master advanced photography techniques including macro, landscape, and portrait photography. Learn post-processing and portfolio building.', 'Nature Reserve', 'in-person', '2026-02-27 02:31:46', '2026-02-27 07:31:46', 12, 8, 55, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(6, 5, 'Design Thinking Session', 'This workshop explores the design thinking methodology for solving complex problems creatively. Perfect for UX/UI designers and anyone interested in user-centered design.', 'Online', 'online', '2026-02-15 02:31:46', '2026-02-15 05:31:46', 15, 15, 30, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(7, 6, 'UI/UX Design Masterclass', 'Learn professional UI/UX design principles, tools like Figma, prototyping, and user research methods. Create a complete design system from scratch.', 'Design Studio', 'in-person', '2026-02-21 02:31:46', '2026-02-23 02:31:46', 18, 10, 80, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(8, 7, 'Business & Marketing Workshop', 'Learn modern marketing strategies and social media best practices. This workshop covers content creation, audience engagement, and analytics.', 'Business Hub', 'in-person', '2026-02-14 02:31:46', '2026-02-14 06:31:46', 12, 6, 40, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(9, 8, 'Digital Marketing & SEO', 'Master SEO, SEM, content marketing, and analytics. Learn how to drive organic traffic and maximize ROI for your digital campaigns.', 'Online', 'online', '2026-02-18 02:31:46', '2026-02-20 02:31:46', 30, 22, 45, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(10, 9, 'Entrepreneurship Bootcamp', 'From idea to launch: Learn business planning, funding, pitching, and growth strategies. Perfect for aspiring entrepreneurs and startup founders.', 'Innovation Hub', 'in-person', '2026-02-26 02:31:46', '2026-02-28 02:31:46', 25, 18, 65, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(11, 10, 'Language Exchange Meetup', 'A casual meetup for language learners and teachers to practice different languages and exchange cultural knowledge in a relaxed environment.', 'Coffee Shop Downtown', 'in-person', '2026-02-12 02:31:46', '2026-02-12 04:31:46', 20, 10, 0, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(12, 1, 'Spanish Conversation Workshop', 'Improve your Spanish speaking skills through interactive conversations, games, and cultural activities. All levels welcome.', 'Online', 'online', '2026-02-17 02:31:46', '2026-02-17 04:31:46', 15, 9, 20, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(13, 2, 'French Language Intensive', 'Comprehensive French course covering grammar, vocabulary, listening, and speaking skills. Perfect for beginners to intermediate learners.', 'Language Center', 'in-person', '2026-02-19 02:31:46', '2026-02-24 02:31:46', 12, 8, 90, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(14, 3, 'Python for Data Science', 'Learn Python programming with a focus on data analysis, pandas, NumPy, matplotlib, and machine learning fundamentals.', 'Tech Hub', 'in-person', '2026-02-22 02:31:46', '2026-02-24 02:31:46', 20, 15, 70, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(15, 4, 'Machine Learning 101', 'Introduction to machine learning concepts, algorithms, and practical applications. We\'ll use scikit-learn and TensorFlow.', 'Online', 'online', '2026-02-25 02:31:46', '2026-02-27 02:31:46', 25, 20, 75, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(16, 5, 'Graphic Design Fundamentals', 'Learn graphic design principles, color theory, typography, and design software. Create professional-looking designs from day one.', 'Design Studio', 'in-person', '2026-02-16 02:31:46', '2026-02-16 06:31:46', 15, 9, 35, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(17, 6, 'Digital Illustration Workshop', 'Learn digital illustration techniques using Procreate, Photoshop, or Clip Studio Paint. Perfect for artists wanting to go digital.', 'Online', 'online', '2026-02-23 02:31:46', '2026-02-23 05:31:46', 12, 7, 40, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(18, 7, 'Skill Exchange Fair 2025', 'A large community event celebrating skill exchange. Meet various instructors and learners, explore different skill categories, and connect with people.', 'Main Square', 'in-person', '2026-02-28 02:31:46', '2026-02-28 10:31:46', 50, 34, 0, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(19, 8, 'Advanced Mathematics Workshop', 'Explore advanced mathematics topics including calculus, linear algebra, and discrete mathematics. Great for students and professionals.', 'University Campus', 'in-person', '2026-03-01 02:31:46', '2026-03-01 07:31:46', 18, 11, 45, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46'),
(20, 9, 'Physics & Science Exploration', 'Hands-on science workshop covering physics concepts, practical experiments, and real-world applications.', 'Science Center', 'in-person', '2026-03-02 02:31:46', '2026-03-02 06:31:46', 22, 14, 30, 'upcoming', '2026-02-06 02:31:46', '2026-02-06 02:31:46');

-- --------------------------------------------------------

--
-- Structure de la table `eventsattendees`
--

CREATE TABLE `eventsattendees` (
  `AttendanceId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `EventId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Status` enum('registered','confirmed','attended','cancelled') DEFAULT 'registered',
  `RegisteredAt` datetime DEFAULT current_timestamp(),
  `ConfirmedAt` datetime DEFAULT NULL,
  KEY `idx_event_attendees` (`EventId`),
  KEY `idx_user_events` (`UserId`),
  UNIQUE KEY `unique_event_user` (`EventId`,`UserId`),
  CONSTRAINT `fk_eventatt_event` FOREIGN KEY (`EventId`) REFERENCES `events`(`EventId`) ON DELETE CASCADE,
  CONSTRAINT `fk_eventatt_user` FOREIGN KEY (`UserId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `eventsattendees`
--

INSERT INTO `eventsattendees` (`AttendanceId`, `EventId`, `UserId`, `Status`, `RegisteredAt`, `ConfirmedAt`) VALUES
(1, 1, 2, 'registered', '2026-02-06 02:31:46', NULL),
(2, 1, 3, 'registered', '2026-02-06 02:31:46', NULL),
(3, 1, 4, 'registered', '2026-02-06 02:31:46', NULL),
(4, 1, 5, 'registered', '2026-02-06 02:31:46', NULL),
(5, 1, 6, 'registered', '2026-02-06 02:31:46', NULL),
(6, 1, 7, 'registered', '2026-02-06 02:31:46', NULL),
(7, 1, 8, 'registered', '2026-02-06 02:31:46', NULL),
(8, 1, 9, 'registered', '2026-02-06 02:31:46', NULL),
(9, 2, 1, 'registered', '2026-02-06 02:31:46', NULL),
(10, 2, 2, 'registered', '2026-02-06 02:31:46', NULL),
(11, 2, 3, 'registered', '2026-02-06 02:31:46', NULL),
(12, 2, 4, 'registered', '2026-02-06 02:31:46', NULL),
(13, 2, 5, 'registered', '2026-02-06 02:31:46', NULL),
(14, 2, 6, 'registered', '2026-02-06 02:31:46', NULL),
(15, 2, 7, 'registered', '2026-02-06 02:31:46', NULL),
(16, 2, 8, 'registered', '2026-02-06 02:31:46', NULL),
(17, 2, 9, 'registered', '2026-02-06 02:31:46', NULL),
(18, 2, 10, 'registered', '2026-02-06 02:31:46', NULL),
(19, 3, 1, 'registered', '2026-02-06 02:31:46', NULL),
(20, 3, 2, 'registered', '2026-02-06 02:31:46', NULL),
(21, 3, 3, 'registered', '2026-02-06 02:31:46', NULL),
(22, 3, 4, 'registered', '2026-02-06 02:31:46', NULL),
(23, 3, 5, 'registered', '2026-02-06 02:31:46', NULL),
(24, 3, 6, 'registered', '2026-02-06 02:31:46', NULL),
(25, 3, 7, 'registered', '2026-02-06 02:31:46', NULL),
(26, 3, 8, 'registered', '2026-02-06 02:31:46', NULL),
(27, 3, 9, 'registered', '2026-02-06 02:31:46', NULL),
(28, 3, 10, 'registered', '2026-02-06 02:31:46', NULL),
(29, 4, 1, 'registered', '2026-02-06 02:31:46', NULL),
(30, 4, 2, 'registered', '2026-02-06 02:31:46', NULL),
(31, 4, 3, 'registered', '2026-02-06 02:31:46', NULL),
(32, 4, 4, 'registered', '2026-02-06 02:31:46', NULL),
(33, 4, 5, 'registered', '2026-02-06 02:31:46', NULL),
(34, 4, 6, 'registered', '2026-02-06 02:31:46', NULL),
(35, 4, 7, 'registered', '2026-02-06 02:31:46', NULL),
(36, 4, 8, 'registered', '2026-02-06 02:31:46', NULL),
(37, 4, 9, 'registered', '2026-02-06 02:31:46', NULL),
(38, 4, 10, 'registered', '2026-02-06 02:31:46', NULL),
(39, 5, 3, 'registered', '2026-02-06 02:31:46', NULL),
(40, 5, 4, 'registered', '2026-02-06 02:31:46', NULL),
(41, 5, 5, 'registered', '2026-02-06 02:31:46', NULL),
(42, 5, 6, 'registered', '2026-02-06 02:31:46', NULL),
(43, 5, 7, 'registered', '2026-02-06 02:31:46', NULL),
(44, 5, 8, 'registered', '2026-02-06 02:31:46', NULL),
(45, 5, 9, 'registered', '2026-02-06 02:31:46', NULL),
(46, 5, 10, 'registered', '2026-02-06 02:31:46', NULL),
(47, 6, 1, 'registered', '2026-02-06 02:31:46', NULL),
(48, 6, 2, 'registered', '2026-02-06 02:31:46', NULL),
(49, 6, 3, 'registered', '2026-02-06 02:31:46', NULL),
(50, 6, 4, 'registered', '2026-02-06 02:31:46', NULL),
(51, 6, 5, 'registered', '2026-02-06 02:31:46', NULL),
(52, 6, 6, 'registered', '2026-02-06 02:31:46', NULL),
(53, 6, 7, 'registered', '2026-02-06 02:31:46', NULL),
(54, 6, 8, 'registered', '2026-02-06 02:31:46', NULL),
(55, 6, 9, 'registered', '2026-02-06 02:31:46', NULL),
(56, 6, 10, 'registered', '2026-02-06 02:31:46', NULL),
(57, 7, 1, 'registered', '2026-02-06 02:31:46', NULL),
(58, 7, 2, 'registered', '2026-02-06 02:31:46', NULL),
(59, 7, 3, 'registered', '2026-02-06 02:31:46', NULL),
(60, 7, 4, 'registered', '2026-02-06 02:31:46', NULL),
(61, 7, 5, 'registered', '2026-02-06 02:31:46', NULL),
(62, 7, 6, 'registered', '2026-02-06 02:31:46', NULL),
(63, 7, 7, 'registered', '2026-02-06 02:31:46', NULL),
(64, 7, 8, 'registered', '2026-02-06 02:31:46', NULL),
(65, 7, 9, 'registered', '2026-02-06 02:31:46', NULL),
(66, 7, 10, 'registered', '2026-02-06 02:31:46', NULL),
(67, 8, 2, 'registered', '2026-02-06 02:31:46', NULL),
(68, 8, 4, 'registered', '2026-02-06 02:31:46', NULL),
(69, 8, 6, 'registered', '2026-02-06 02:31:46', NULL),
(70, 8, 8, 'registered', '2026-02-06 02:31:46', NULL),
(71, 8, 9, 'registered', '2026-02-06 02:31:46', NULL),
(72, 8, 10, 'registered', '2026-02-06 02:31:46', NULL),
(73, 9, 1, 'registered', '2026-02-06 02:31:46', NULL),
(74, 9, 2, 'registered', '2026-02-06 02:31:46', NULL),
(75, 9, 3, 'registered', '2026-02-06 02:31:46', NULL),
(76, 9, 4, 'registered', '2026-02-06 02:31:46', NULL),
(77, 9, 5, 'registered', '2026-02-06 02:31:46', NULL),
(78, 9, 6, 'registered', '2026-02-06 02:31:46', NULL),
(79, 9, 7, 'registered', '2026-02-06 02:31:46', NULL),
(80, 9, 8, 'registered', '2026-02-06 02:31:46', NULL),
(81, 9, 9, 'registered', '2026-02-06 02:31:46', NULL),
(82, 9, 10, 'registered', '2026-02-06 02:31:46', NULL),
(83, 10, 1, 'registered', '2026-02-06 02:31:46', NULL),
(84, 10, 2, 'registered', '2026-02-06 02:31:46', NULL),
(85, 10, 3, 'registered', '2026-02-06 02:31:46', NULL),
(86, 10, 4, 'registered', '2026-02-06 02:31:46', NULL),
(87, 10, 5, 'registered', '2026-02-06 02:31:46', NULL),
(88, 10, 6, 'registered', '2026-02-06 02:31:46', NULL),
(89, 10, 7, 'registered', '2026-02-06 02:31:46', NULL),
(90, 10, 8, 'registered', '2026-02-06 02:31:46', NULL),
(91, 10, 9, 'registered', '2026-02-06 02:31:46', NULL),
(92, 10, 10, 'registered', '2026-02-06 02:31:46', NULL),
(93, 11, 1, 'registered', '2026-02-06 02:31:46', NULL),
(94, 11, 2, 'registered', '2026-02-06 02:31:46', NULL),
(95, 11, 3, 'registered', '2026-02-06 02:31:46', NULL),
(96, 11, 4, 'registered', '2026-02-06 02:31:46', NULL),
(97, 11, 5, 'registered', '2026-02-06 02:31:46', NULL),
(98, 11, 6, 'registered', '2026-02-06 02:31:46', NULL),
(99, 11, 7, 'registered', '2026-02-06 02:31:46', NULL),
(100, 11, 8, 'registered', '2026-02-06 02:31:46', NULL),
(101, 11, 9, 'registered', '2026-02-06 02:31:46', NULL),
(102, 12, 1, 'registered', '2026-02-06 02:31:46', NULL),
(103, 12, 2, 'registered', '2026-02-06 02:31:46', NULL),
(104, 12, 3, 'registered', '2026-02-06 02:31:46', NULL),
(105, 12, 4, 'registered', '2026-02-06 02:31:46', NULL),
(106, 12, 5, 'registered', '2026-02-06 02:31:46', NULL),
(107, 12, 6, 'registered', '2026-02-06 02:31:46', NULL),
(108, 12, 7, 'registered', '2026-02-06 02:31:46', NULL),
(109, 12, 8, 'registered', '2026-02-06 02:31:46', NULL),
(110, 12, 9, 'registered', '2026-02-06 02:31:46', NULL),
(111, 12, 10, 'registered', '2026-02-06 02:31:46', NULL),
(112, 13, 2, 'registered', '2026-02-06 02:31:46', NULL),
(113, 13, 3, 'registered', '2026-02-06 02:31:46', NULL),
(114, 13, 4, 'registered', '2026-02-06 02:31:46', NULL),
(115, 13, 5, 'registered', '2026-02-06 02:31:46', NULL),
(116, 13, 6, 'registered', '2026-02-06 02:31:46', NULL),
(117, 13, 7, 'registered', '2026-02-06 02:31:46', NULL),
(118, 13, 8, 'registered', '2026-02-06 02:31:46', NULL),
(119, 13, 9, 'registered', '2026-02-06 02:31:46', NULL),
(120, 14, 1, 'registered', '2026-02-06 02:31:46', NULL),
(121, 14, 2, 'registered', '2026-02-06 02:31:46', NULL),
(122, 14, 3, 'registered', '2026-02-06 02:31:46', NULL),
(123, 14, 4, 'registered', '2026-02-06 02:31:46', NULL),
(124, 14, 5, 'registered', '2026-02-06 02:31:46', NULL),
(125, 14, 6, 'registered', '2026-02-06 02:31:46', NULL),
(126, 14, 7, 'registered', '2026-02-06 02:31:46', NULL),
(127, 14, 8, 'registered', '2026-02-06 02:31:46', NULL),
(128, 14, 9, 'registered', '2026-02-06 02:31:46', NULL),
(129, 14, 10, 'registered', '2026-02-06 02:31:46', NULL),
(130, 15, 1, 'registered', '2026-02-06 02:31:46', NULL),
(131, 15, 2, 'registered', '2026-02-06 02:31:46', NULL),
(132, 15, 3, 'registered', '2026-02-06 02:31:46', NULL),
(133, 15, 4, 'registered', '2026-02-06 02:31:46', NULL),
(134, 15, 5, 'registered', '2026-02-06 02:31:46', NULL),
(135, 15, 6, 'registered', '2026-02-06 02:31:46', NULL),
(136, 15, 7, 'registered', '2026-02-06 02:31:46', NULL),
(137, 15, 8, 'registered', '2026-02-06 02:31:46', NULL),
(138, 15, 9, 'registered', '2026-02-06 02:31:46', NULL),
(139, 15, 10, 'registered', '2026-02-06 02:31:46', NULL),
(140, 16, 1, 'registered', '2026-02-06 02:31:46', NULL),
(141, 16, 2, 'registered', '2026-02-06 02:31:46', NULL),
(142, 16, 3, 'registered', '2026-02-06 02:31:46', NULL),
(143, 16, 4, 'registered', '2026-02-06 02:31:46', NULL),
(144, 16, 5, 'registered', '2026-02-06 02:31:46', NULL),
(145, 16, 6, 'registered', '2026-02-06 02:31:46', NULL),
(146, 16, 7, 'registered', '2026-02-06 02:31:46', NULL),
(147, 16, 8, 'registered', '2026-02-06 02:31:46', NULL),
(148, 16, 9, 'registered', '2026-02-06 02:31:46', NULL),
(149, 17, 1, 'registered', '2026-02-06 02:31:46', NULL),
(150, 17, 2, 'registered', '2026-02-06 02:31:46', NULL),
(151, 17, 3, 'registered', '2026-02-06 02:31:46', NULL),
(152, 17, 4, 'registered', '2026-02-06 02:31:46', NULL),
(153, 17, 5, 'registered', '2026-02-06 02:31:46', NULL),
(154, 17, 6, 'registered', '2026-02-06 02:31:46', NULL),
(155, 17, 7, 'registered', '2026-02-06 02:31:46', NULL),
(156, 18, 1, 'registered', '2026-02-06 02:31:46', NULL),
(157, 18, 2, 'registered', '2026-02-06 02:31:46', NULL),
(158, 18, 3, 'registered', '2026-02-06 02:31:46', NULL),
(159, 18, 4, 'registered', '2026-02-06 02:31:46', NULL),
(160, 18, 5, 'registered', '2026-02-06 02:31:46', NULL),
(161, 18, 6, 'registered', '2026-02-06 02:31:46', NULL),
(162, 18, 7, 'registered', '2026-02-06 02:31:46', NULL),
(163, 18, 8, 'registered', '2026-02-06 02:31:46', NULL),
(164, 18, 9, 'registered', '2026-02-06 02:31:46', NULL),
(165, 18, 10, 'registered', '2026-02-06 02:31:46', NULL),
(166, 19, 1, 'registered', '2026-02-06 02:31:46', NULL),
(167, 19, 2, 'registered', '2026-02-06 02:31:46', NULL),
(168, 19, 3, 'registered', '2026-02-06 02:31:46', NULL),
(169, 19, 4, 'registered', '2026-02-06 02:31:46', NULL),
(170, 19, 5, 'registered', '2026-02-06 02:31:46', NULL),
(171, 19, 6, 'registered', '2026-02-06 02:31:46', NULL),
(172, 19, 7, 'registered', '2026-02-06 02:31:46', NULL),
(173, 19, 8, 'registered', '2026-02-06 02:31:46', NULL),
(174, 19, 9, 'registered', '2026-02-06 02:31:46', NULL),
(175, 19, 10, 'registered', '2026-02-06 02:31:46', NULL),
(176, 20, 1, 'registered', '2026-02-06 02:31:46', NULL),
(177, 20, 2, 'registered', '2026-02-06 02:31:46', NULL),
(178, 20, 3, 'registered', '2026-02-06 02:31:46', NULL),
(179, 20, 4, 'registered', '2026-02-06 02:31:46', NULL),
(180, 20, 5, 'registered', '2026-02-06 02:31:46', NULL),
(181, 20, 6, 'registered', '2026-02-06 02:31:46', NULL),
(182, 20, 7, 'registered', '2026-02-06 02:31:46', NULL),
(183, 20, 8, 'registered', '2026-02-06 02:31:46', NULL),
(184, 20, 9, 'registered', '2026-02-06 02:31:46', NULL),
(185, 20, 10, 'registered', '2026-02-06 02:31:46', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `eventskills`
--

CREATE TABLE `eventskills` (
  `EventSkillId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `EventId` int(11) NOT NULL,
  `SkillId` int(11) NOT NULL,
  `IsRequired` enum('yes','no') DEFAULT 'no',
  KEY `idx_event_skills` (`EventId`),
  KEY `idx_skill` (`SkillId`),
  CONSTRAINT `fk_eventskill_event` FOREIGN KEY (`EventId`) REFERENCES `events`(`EventId`) ON DELETE CASCADE,
  CONSTRAINT `fk_eventskill_skill` FOREIGN KEY (`SkillId`) REFERENCES `skills`(`SkillId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `eventskills`
--

INSERT INTO `eventskills` (`EventSkillId`, `EventId`, `SkillId`, `IsRequired`) VALUES
(1, 1, 1, 'yes'),
(2, 1, 7, 'yes'),
(3, 1, 6, 'yes'),
(4, 1, 8, 'yes'),
(5, 2, 1, 'yes'),
(6, 2, 2, 'yes'),
(7, 3, 1, 'yes'),
(8, 3, 4, 'yes'),
(9, 4, 24, 'yes'),
(10, 5, 24, 'yes'),
(11, 5, 25, 'yes'),
(12, 6, 16, 'yes'),
(13, 6, 13, 'yes'),
(14, 7, 16, 'yes'),
(15, 7, 13, 'yes'),
(16, 8, 20, 'yes'),
(17, 8, 17, 'yes'),
(18, 9, 20, 'yes'),
(19, 9, 16, 'yes'),
(20, 10, 10, 'yes'),
(21, 10, 3, 'yes'),
(22, 11, 7, 'yes'),
(23, 11, 1, 'yes'),
(24, 12, 8, 'yes'),
(25, 12, 5, 'yes'),
(26, 13, 3, 'yes'),
(27, 13, 9, 'yes'),
(28, 14, 3, 'yes'),
(29, 14, 5, 'yes'),
(30, 15, 3, 'yes'),
(31, 15, 10, 'yes'),
(32, 16, 15, 'yes'),
(33, 16, 14, 'yes'),
(34, 17, 15, 'yes'),
(35, 17, 22, 'yes'),
(36, 18, 1, 'no'),
(37, 19, 9, 'yes'),
(38, 19, 11, 'yes'),
(39, 20, 12, 'yes'),
(40, 20, 10, 'yes');

-- --------------------------------------------------------

--
-- Structure de la table `exchanges`
--

CREATE TABLE `exchanges` (
  `ExchangeId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `PostId` int(11) NOT NULL,
  `OfferedByUserId` int(11) NOT NULL,
  `RequestedByUserId` int(11) NOT NULL,
  `Status` enum('pending','accepted','rejected','completed','cancelled') DEFAULT 'pending',
  `ProposedDate` datetime NOT NULL,
  `ConfirmedDate` datetime DEFAULT NULL,
  `CompletedDate` datetime DEFAULT NULL,
  `MeetingLink` varchar(255) DEFAULT NULL,
  `MeetingLocation` varchar(150) DEFAULT NULL,
  `CreditsCost` int(11) DEFAULT 0,
  `CreatedAt` timestamp DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_post` (`PostId`),
  KEY `idx_offered_by` (`OfferedByUserId`),
  KEY `idx_requested_by` (`RequestedByUserId`),
  CONSTRAINT `fk_exchange_post` FOREIGN KEY (`PostId`) REFERENCES `posts`(`PostId`) ON DELETE CASCADE,
  CONSTRAINT `fk_exchange_offered` FOREIGN KEY (`OfferedByUserId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE,
  CONSTRAINT `fk_exchange_requested` FOREIGN KEY (`RequestedByUserId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `exchanges`
--

INSERT INTO `exchanges` (`ExchangeId`, `PostId`, `OfferedByUserId`, `RequestedByUserId`, `Status`, `ProposedDate`, `ConfirmedDate`, `CompletedDate`, `MeetingLink`, `MeetingLocation`, `CreditsCost`, `CreatedAt`) VALUES
(1, 1, 1, 2, 'completed', '2025-12-20 10:00:00', '2025-12-21 14:30:00', '2025-12-23 18:00:00', NULL, 'Alger, Hydra', 0, '2025-12-16 22:11:46'),
(2, 2, 2, 3, 'accepted', '2025-12-21 09:00:00', '2025-12-22 11:20:00', NULL, NULL, 'En Ligne', 0, '2025-12-16 22:11:46'),
(3, 3, 3, 4, 'completed', '2025-12-22 15:00:00', '2025-12-23 16:45:00', '2025-12-28 18:30:00', NULL, 'Constantine, Belkaid', 0, '2025-12-16 22:11:46'),
(4, 4, 4, 5, 'pending', '2025-12-24 10:30:00', NULL, NULL, NULL, 'En Ligne', 0, '2025-12-16 22:11:46'),
(5, 5, 5, 6, 'accepted', '2025-12-25 14:00:00', '2025-12-26 09:15:00', NULL, NULL, 'Tlemcen', 0, '2025-12-16 22:11:46'),
(6, 6, 6, 7, 'completed', '2025-12-27 11:00:00', '2025-12-29 13:45:00', '2026-01-12 20:00:00', NULL, 'Blida', 0, '2025-12-16 22:11:46'),
(7, 7, 7, 8, 'accepted', '2026-01-02 16:30:00', '2026-01-03 10:00:00', NULL, NULL, 'Sétif', 0, '2025-12-16 22:11:46'),
(8, 8, 8, 9, 'pending', '2026-01-05 09:00:00', NULL, NULL, NULL, 'En Ligne', 0, '2025-12-16 22:11:46'),
(9, 9, 9, 10, 'completed', '2026-01-06 15:20:00', '2026-01-08 11:30:00', '2026-01-15 17:00:00', NULL, 'Tipaza', 0, '2025-12-16 22:11:46'),
(10, 10, 10, 1, 'accepted', '2026-01-10 10:45:00', '2026-01-11 14:00:00', NULL, NULL, 'Boumerdès', 0, '2025-12-16 22:11:46'),
(50, 19, 9, 26, 'pending', '2026-02-11 10:00:00', NULL, NULL, NULL, NULL, 0, '2026-02-05 02:02:41'),
(76, 34, 28, 30, 'accepted', '2026-02-13 17:28:00', '2026-02-13 17:28:00', NULL, NULL, NULL, 50, '2026-02-05 17:57:36');

-- --------------------------------------------------------

--
-- Structure de la table `postavailabledates`
--

CREATE TABLE `postavailabledates` (
  `PostAvailableDateId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `PostId` int(11) NOT NULL,
  `AvailableDate` datetime NOT NULL,
  KEY `idx_post_dates` (`PostId`),
  CONSTRAINT `fk_postdate_post` FOREIGN KEY (`PostId`) REFERENCES `posts`(`PostId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `postavailabledates`
--

INSERT INTO `postavailabledates` (`PostAvailableDateId`, `PostId`, `AvailableDate`) VALUES
(2, 1, '2026-02-10 14:00:00'),
(4, 1, '2026-02-11 14:00:00'),
(5, 1, '2026-02-12 10:00:00'),
(6, 1, '2026-02-12 14:00:00'),
(7, 1, '2026-02-13 10:00:00'),
(8, 1, '2026-02-13 14:00:00'),
(9, 1, '2026-02-14 10:00:00'),
(10, 1, '2026-02-14 14:00:00'),
(13, 1, '2026-02-18 10:00:00'),
(14, 1, '2026-02-18 14:00:00'),
(15, 1, '2026-02-19 10:00:00'),
(16, 1, '2026-02-19 14:00:00'),
(19, 12, '2026-02-21 10:00:00'),
(1, 17, '2026-02-10 10:00:00'),
(11, 18, '2026-02-17 10:00:00'),
(12, 18, '2026-02-17 14:00:00'),
(3, 19, '2026-02-11 10:00:00'),
(17, 19, '2026-02-20 10:00:00'),
(18, 19, '2026-02-20 14:00:00'),
(20, 19, '2026-02-21 14:00:00'),
(21, 24, '2026-02-13 19:51:00'),
(26, 34, '2026-02-13 17:28:00'),
(27, 34, '2026-02-13 19:28:00'),
(28, 35, '2026-02-06 19:53:00'),
(29, 36, '2026-02-14 20:10:00');

-- --------------------------------------------------------

--
-- Structure de la table `postlikes`
--

CREATE TABLE `postlikes` (
  `LikeId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  UNIQUE KEY `unique_post_like` (`PostId`,`UserId`),
  KEY `idx_user_likes` (`UserId`),
  CONSTRAINT `fk_postlike_post` FOREIGN KEY (`PostId`) REFERENCES `posts`(`PostId`) ON DELETE CASCADE,
  CONSTRAINT `fk_postlike_user` FOREIGN KEY (`UserId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `postlikes`
--

INSERT INTO `postlikes` (`LikeId`, `PostId`, `UserId`, `CreatedAt`) VALUES
(14, 6, 1, '2026-02-02 16:06:04'),
(17, 18, 1, '2026-02-02 16:24:13'),
(30, 10, 1, '2026-02-02 16:38:55'),
(47, 13, 1, '2026-02-02 17:13:47'),
(48, 17, 1, '2026-02-02 17:13:49'),
(49, 16, 1, '2026-02-02 17:13:51'),
(52, 8, 1, '2026-02-02 17:14:16'),
(54, 9, 1, '2026-02-02 17:19:17');

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE `posts` (
  `PostId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `UserId` int(11) NOT NULL,
  `Title` varchar(150) NOT NULL,
  `Description` text NOT NULL,
  `PostType` enum('in-person','online') NOT NULL DEFAULT 'in-person',
  `PostStatus` enum('active','disabled') DEFAULT 'active',
  `CategoryId` int(11) NOT NULL,
  `Duration` int(11) DEFAULT NULL,
  `MeetLocation` varchar(150),
  `Prerequisites` text,
  `Requirements` text,
  `PaymentMethod` enum('credit','exchange') DEFAULT 'credit',
  `RequiredCredits` int(11) DEFAULT 0,
  `LikeCount` int(11) DEFAULT 0,
  `AvailableDate` datetime,
  `CreatedAt` timestamp DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user` (`UserId`),
  KEY `idx_category` (`CategoryId`),
  KEY `idx_status` (`PostStatus`),
  CONSTRAINT `fk_post_user` FOREIGN KEY (`UserId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_category` FOREIGN KEY (`CategoryId`) REFERENCES `category`(`CategoryId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `posts`
--

INSERT INTO `posts` (`PostId`, `UserId`, `Title`, `Description`, `PostType`, `PostStatus`, `CategoryId`, `Duration`, `MeetLocation`, `Prerequisites`, `Requirements`, `PaymentMethod`, `RequiredCredits`, `LikeCount`, `CreatedAt`) VALUES
(1, 1, 'Private JavaScript Lessons', 'Private lessons in JavaScript for beginners to intermediate. Fast learning guaranteed.', 'in-person', 'active', 1, 120, 'Algiers, Hydra - Cultural Center', NULL, NULL, 'credit', 50, 8, '2025-12-16 21:51:24'),
(2, 2, 'Custom Graphic Design', 'Creation of custom designs for your brand. Logos, brochures, and marketing materials.', 'online', 'active', 2, 180, NULL, NULL, NULL, 'exchange', 22, 11, '2025-12-16 21:51:24'),
(3, 3, 'Professional Photo Session', 'Professional photography for portraits, products, and events. Retouching included.', 'in-person', 'active', 3, 90, 'Constantine, Belkaid - Photo Studio', NULL, NULL, 'credit', 60, 15, '2025-12-16 21:51:24'),
(4, 4, 'Blog Content Writing', 'Writing SEO-optimized articles for your blog. Engaging and relevant content.', 'online', 'active', 4, 240, NULL, NULL, NULL, 'exchange', 0, 6, '2025-12-16 21:51:24'),
(5, 5, 'Guitar Lessons All Levels', 'Teaching acoustic and electric guitar. Structured and progressive method.', 'in-person', 'active', 5, 60, 'Tlemcen - Music Studio', NULL, NULL, 'credit', 45, 10, '2025-12-16 21:51:24'),
(6, 6, 'Algerian Cooking Coaching', 'Traditional Algerian cuisine training. Learning authentic recipes.', 'in-person', 'active', 6, 150, 'Blida, Downtown - Kitchen', NULL, NULL, 'credit', 55, 9, '2025-12-16 21:51:24'),
(7, 7, 'Personal Fitness Coaching', 'Personal training adapted to your goals. With nutrition and regular follow-up.', 'in-person', 'active', 7, 120, 'Setif, Haouchias - ProFit Gym', NULL, NULL, 'exchange', 0, 14, '2025-12-16 21:51:24'),
(8, 8, 'Intensive English Tutoring', 'Intensive English conversation and grammar course. Rapid improvement guaranteed.', 'online', 'active', 8, 90, NULL, NULL, NULL, 'credit', 40, 7, '2025-12-16 21:51:24'),
(9, 9, 'Auto Mechanics Diagnostics', 'Complete vehicle diagnostics with detailed report. Repair quote included.', 'in-person', 'active', 9, 60, 'Tipaza, Chenoua - Garage', NULL, NULL, 'exchange', 0, 5, '2025-12-16 21:51:24'),
(10, 10, 'Organic Gardening Consultation', 'Personalized advice for ecological garden. Design and landscape arrangement.', 'in-person', 'active', 10, 180, 'Boumerdes, Baya - Green Center', NULL, NULL, 'credit', 50, 11, '2025-12-16 21:51:24'),
(11, 1, 'Private JavaScript Lessons', 'Private lessons in JavaScript for beginners to intermediate. Fast learning guaranteed.', 'in-person', 'active', 1, 120, 'Algiers, Hydra - Cultural Center', NULL, NULL, 'credit', 50, 7, '2025-12-16 22:11:46'),
(12, 2, 'Custom Graphic Design', 'Creation of custom designs for your brand. Logos, brochures, and marketing materials.', 'online', 'active', 2, 180, NULL, NULL, NULL, 'exchange', 0, 12, '2025-12-16 22:11:46'),
(13, 3, 'Professional Photo Session', 'Professional photography for portraits, products, and events. Retouching included.', 'in-person', 'active', 3, 90, 'Constantine, Belkaid - Photo Studio', NULL, NULL, 'credit', 60, 15, '2025-12-16 22:11:46'),
(14, 4, 'Blog Content Writing', 'Writing SEO-optimized articles for your blog. Engaging and relevant content.', 'online', 'active', 4, 240, NULL, NULL, NULL, 'exchange', 0, 6, '2025-12-16 22:11:46'),
(15, 5, 'Guitar Lessons All Levels', 'Teaching acoustic and electric guitar. Structured and progressive method.', 'in-person', 'active', 5, 60, 'Tlemcen - Music Studio', NULL, NULL, 'credit', 45, 10, '2025-12-16 22:11:46'),
(16, 6, 'Algerian Cooking Coaching', 'Traditional Algerian cuisine training. Learning authentic recipes.', 'in-person', 'active', 6, 150, 'Blida, Downtown - Kitchen', NULL, NULL, 'credit', 55, 9, '2025-12-16 22:11:46'),
(17, 7, 'Personal Fitness Coaching', 'Personal training adapted to your goals. With nutrition and regular follow-up.', 'in-person', 'active', 7, 120, 'Setif, Haouchias - ProFit Gym', NULL, NULL, 'exchange', 0, 15, '2025-12-16 22:11:46'),
(18, 8, 'Intensive English Tutoring', 'Intensive English conversation and grammar course. Rapid improvement guaranteed.', 'online', 'active', 8, 90, NULL, NULL, NULL, 'credit', 40, 7, '2025-12-16 22:11:46'),
(19, 9, 'Auto Mechanics Diagnostics', 'Complete vehicle diagnostics with detailed report. Repair quote included.', 'in-person', 'active', 9, 60, 'Tipaza, Chenoua - Garage', NULL, NULL, 'exchange', 0, 5, '2025-12-16 22:11:46'),
(20, 10, 'Organic Gardening Consultation', 'Personalized advice for ecological garden. Design and landscape arrangement.', 'in-person', 'active', 10, 180, 'Boumerdes, Baya - Green Center', NULL, NULL, 'credit', 50, 11, '2025-12-16 22:11:46'),
(24, 26, 'jtujjjjjjjjjjjjjjjjjjjjjjjjjjj', 'dddddddddddddddddddddddddddddddddddddd', 'online', 'active', 7, 30, 'San Francisco, CA', NULL, NULL, '', 50, 0, '2026-02-04 19:51:20'),
(34, 28, 'aaaaaaaaaaa', 'aaaaaaaalllllll\r\nllllllllllllllllllllllll', 'online', 'active', 10, 30, 'ksqndk', NULL, NULL, 'exchange', 50, 0, '2026-02-05 17:29:00'),
(35, 31, 'aaaaaaaaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaa\r\naaaaaaaaaaaaa', 'online', 'active', 10, 60, 'aaaaaaaaaaa', NULL, NULL, 'exchange', 50, 0, '2026-02-05 19:53:36'),
(36, 31, 'sssssssssss', 'fffffffffffffffffffffffffffff', 'online', 'active', 2, 60, 'bejaia', NULL, NULL, 'exchange', 100, 0, '2026-02-05 20:11:53');

-- --------------------------------------------------------

--
-- Structure de la table `postskills`
--

CREATE TABLE `postskills` (
  `PostSkillId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `PostId` int(11) NOT NULL,
  `SkillId` int(11) NOT NULL,
  `SkillType` enum('offered','requested') NOT NULL DEFAULT 'offered',
  KEY `idx_post_skills` (`PostId`),
  KEY `idx_skill` (`SkillId`),
  CONSTRAINT `fk_postskill_post` FOREIGN KEY (`PostId`) REFERENCES `posts`(`PostId`) ON DELETE CASCADE,
  CONSTRAINT `fk_postskill_skill` FOREIGN KEY (`SkillId`) REFERENCES `skills`(`SkillId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `postskills`
--

INSERT INTO `postskills` (`PostSkillId`, `PostId`, `SkillId`, `SkillType`) VALUES
(1, 1, 1, 'offered'),
(2, 1, 2, 'offered'),
(3, 1, 7, 'offered'),
(4, 2, 13, 'offered'),
(5, 2, 15, 'offered'),
(6, 2, 20, 'offered'),
(7, 3, 25, 'offered'),
(8, 3, 26, 'offered'),
(9, 3, 28, 'offered'),
(10, 4, 37, 'offered'),
(11, 4, 38, 'offered'),
(12, 4, 40, 'offered'),
(13, 5, 49, 'offered'),
(14, 5, 51, 'offered'),
(15, 5, 57, 'offered'),
(16, 6, 61, 'offered'),
(17, 6, 62, 'offered'),
(18, 6, 63, 'offered'),
(19, 7, 71, 'offered'),
(20, 7, 73, 'offered'),
(21, 8, 85, 'offered'),
(22, 8, 86, 'offered'),
(23, 9, 93, 'offered'),
(24, 9, 94, 'offered'),
(25, 10, 110, 'offered'),
(26, 10, 111, 'offered'),
(27, 24, 73, 'offered'),
(28, 24, 86, 'requested'),
(33, 11, 1, 'offered'),
(34, 11, 2, 'offered'),
(35, 11, 7, 'offered'),
(36, 12, 13, 'offered'),
(37, 12, 15, 'offered'),
(38, 12, 20, 'offered'),
(39, 13, 25, 'offered'),
(40, 13, 26, 'offered'),
(41, 13, 28, 'offered'),
(42, 14, 37, 'offered'),
(43, 14, 38, 'offered'),
(44, 14, 40, 'offered'),
(45, 15, 49, 'offered'),
(46, 15, 51, 'offered'),
(47, 16, 61, 'offered'),
(48, 16, 62, 'offered'),
(49, 16, 63, 'offered'),
(50, 17, 71, 'offered'),
(51, 17, 73, 'offered'),
(52, 18, 85, 'offered'),
(53, 18, 86, 'offered'),
(54, 19, 93, 'offered'),
(55, 19, 94, 'offered'),
(56, 20, 110, 'offered'),
(57, 20, 111, 'offered'),
(78, 1, 13, 'requested'),
(79, 1, 15, 'requested'),
(80, 2, 1, 'requested'),
(81, 2, 3, 'requested'),
(82, 3, 37, 'requested'),
(83, 3, 40, 'requested'),
(84, 4, 25, 'requested'),
(85, 4, 26, 'requested'),
(86, 5, 58, 'requested'),
(87, 5, 61, 'requested'),
(88, 6, 71, 'requested'),
(89, 6, 73, 'requested'),
(90, 7, 85, 'requested'),
(91, 7, 86, 'requested'),
(92, 8, 110, 'requested'),
(93, 8, 111, 'requested'),
(94, 9, 49, 'requested'),
(95, 9, 51, 'requested'),
(96, 10, 93, 'requested'),
(97, 10, 94, 'requested'),
(98, 11, 13, 'requested'),
(99, 11, 20, 'requested'),
(100, 12, 2, 'requested'),
(101, 12, 4, 'requested'),
(102, 13, 85, 'requested'),
(103, 13, 86, 'requested'),
(104, 14, 49, 'requested'),
(105, 14, 51, 'requested'),
(106, 15, 58, 'requested'),
(107, 15, 63, 'requested'),
(108, 16, 110, 'requested'),
(109, 16, 111, 'requested'),
(110, 17, 1, 'requested'),
(111, 17, 7, 'requested'),
(112, 18, 25, 'requested'),
(113, 18, 28, 'requested'),
(114, 19, 37, 'requested'),
(115, 19, 38, 'requested'),
(116, 20, 71, 'requested'),
(117, 20, 73, 'requested'),
(118, 24, 72, 'requested'),
(119, 24, 74, 'requested'),
(126, 34, 107, 'offered'),
(127, 34, 63, 'requested'),
(128, 35, 107, 'offered'),
(129, 35, 95, 'requested'),
(130, 36, 19, 'offered'),
(131, 36, 116, 'requested');

-- --------------------------------------------------------

--
-- Structure de la table `profile_reviews`
--

CREATE TABLE `profile_reviews` (
  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ReviewerID` int(11) NOT NULL,
  `ReviewDate` datetime NOT NULL,
  `ReviewText` text NOT NULL,
  `ReviewRate` int(11) NOT NULL CHECK (ReviewRate >= 1 AND ReviewRate <= 5),
  `userID` int(11) NOT NULL,
  KEY `idx_reviewer` (`ReviewerID`),
  KEY `idx_user` (`userID`),
  CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`ReviewerID`) REFERENCES `users`(`UserId`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_user` FOREIGN KEY (`userID`) REFERENCES `users`(`UserId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `profile_reviews`
--

INSERT INTO `profile_reviews` (`ID`, `ReviewerID`, `ReviewDate`, `ReviewText`, `ReviewRate`, `userID`) VALUES
(13, 3, '2026-01-17 21:37:43', 'NICE', 5, 2),
(14, 2, '2026-01-17 21:38:29', 'NOOOOOOO', 1, 3),
(17, 2, '2026-02-01 23:19:59', 'This is my first test review for this user!', 5, 4),
(18, 4, '2026-02-01 23:36:00', 'This is my first test review for this user!', 5, 2),
(19, 26, '2026-02-04 19:04:23', 'ibbvuybv', 1, 1),
(20, 26, '2026-02-04 20:10:17', 'disappointing', 2, 7),
(21, 26, '2026-02-04 20:11:57', 'salam', 2, 2);

-- --------------------------------------------------------

--
-- Structure de la table `rating`
--

CREATE TABLE `rating` (
  `ReviewId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ExchangeId` int(11) NOT NULL,
  `ReviewerId` int(11) NOT NULL,
  `ReviewedUserId` int(11) NOT NULL,
  `Rating` int(11) NOT NULL CHECK (Rating >= 1 AND Rating <= 5),
  `ReviewText` text,
  `CreatedAt` timestamp DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_exchange` (`ExchangeId`),
  KEY `idx_reviewer` (`ReviewerId`),
  KEY `idx_reviewed` (`ReviewedUserId`),
  CONSTRAINT `fk_rating_exchange` FOREIGN KEY (`ExchangeId`) REFERENCES `exchanges`(`ExchangeId`) ON DELETE CASCADE,
  CONSTRAINT `fk_rating_reviewer` FOREIGN KEY (`ReviewerId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE,
  CONSTRAINT `fk_rating_reviewed` FOREIGN KEY (`ReviewedUserId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `rating`
--

INSERT INTO `rating` (`ReviewId`, `ExchangeId`, `ReviewerId`, `ReviewedUserId`, `Rating`, `ReviewText`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, 2, 1, 5, 'Excellent teacher! Very pedagogical and patient. Highly recommended!', '2025-12-16 22:11:46', '2025-12-16 22:11:46'),
(2, 2, 3, 2, 5, 'Talented designer with excellent understanding of needs. Superb result!', '2025-12-16 22:11:46', '2025-12-16 22:11:46'),
(3, 3, 4, 3, 4, 'Very professional. Quality photographs. Some minor delays but final result very good.', '2025-12-16 22:11:46', '2025-12-16 22:11:46'),
(4, 4, 5, 4, 5, 'Well-structured content and SEO optimized. Really satisfied!', '2025-12-16 22:11:46', '2025-12-16 22:11:46'),
(5, 6, 7, 6, 5, 'Delicious lessons! Chef very welcoming and clear explanations. Will do again!', '2025-12-16 22:11:46', '2025-12-16 22:11:46'),
(6, 7, 8, 7, 4, 'Good coach. Motivating and effective. Programs well adapted.', '2025-12-16 22:11:46', '2025-12-16 22:11:46'),
(7, 9, 10, 9, 5, 'Complete and very professional diagnostics. Trustworthy mechanic!', '2025-12-16 22:11:46', '2025-12-16 22:11:46'),
(8, 10, 1, 10, 5, 'Excellent gardening advice. Very knowledgeable and inspiring!', '2025-12-16 22:11:46', '2025-12-16 22:11:46'),
(9, 5, 6, 5, 5, 'Amazing guitar teacher! Patience and excellent method. Rapid progress!', '2025-12-16 22:11:46', '2025-12-16 22:11:46'),
(10, 8, 9, 8, 4, 'Good English lessons. Good accent and clear explanations.', '2025-12-16 22:11:46', '2025-12-16 22:11:46');

-- --------------------------------------------------------

--
-- Structure de la table `skills`
--

CREATE TABLE `skills` (
  `SkillId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `SkillName` varchar(100) NOT NULL UNIQUE,
  `CategoryId` int(11) NOT NULL,
  `SkillDescription` text DEFAULT NULL,
  KEY `idx_category` (`CategoryId`),
  CONSTRAINT `fk_skill_category` FOREIGN KEY (`CategoryId`) REFERENCES `category`(`CategoryId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `skills`
--

INSERT INTO `skills` (`SkillId`, `SkillName`, `CategoryId`, `SkillDescription`) VALUES
(1, 'JavaScript', 1, 'Modern JavaScript programming and ES6+ features'),
(2, 'React', 1, 'React framework for building user interfaces'),
(3, 'Python', 1, 'Python programming for various applications'),
(4, 'Node.js', 1, 'Server-side JavaScript development'),
(5, 'SQL Database', 1, 'Relational database design and queries'),
(6, 'Git Version Control', 1, 'Source code management with Git'),
(7, 'HTML & CSS', 1, 'Web markup and stylesheet design'),
(8, 'PHP', 1, 'Server-side scripting language'),
(9, 'Java', 1, 'Object-oriented programming with Java'),
(10, 'Cloud Computing', 1, 'AWS, Azure and cloud services'),
(11, 'Cybersecurity', 1, 'Network security and ethical hacking'),
(12, 'Mobile App Development', 1, 'iOS and Android development'),
(13, 'Figma', 2, 'UI/UX design tool and prototyping'),
(14, 'Adobe Photoshop', 2, 'Professional photo editing software'),
(15, 'Adobe Illustrator', 2, 'Vector graphics and illustration'),
(16, 'UI/UX Design', 2, 'Interface design and user experience'),
(17, 'Logo Design', 2, 'Brand identity and logo creation'),
(18, 'Typography', 2, 'Font design and text layout'),
(19, 'Color Theory', 2, 'Color selection and harmony'),
(20, 'Brand Design', 2, 'Complete brand identity systems'),
(21, '3D Modeling', 2, 'Three-dimensional design and rendering'),
(22, 'Animation', 2, 'Animated graphics and character animation'),
(23, 'InDesign', 2, 'Document layout and publishing'),
(24, 'Portrait Photography', 3, 'Professional portrait techniques'),
(25, 'Lightroom', 3, 'Photo editing and color grading'),
(26, 'Video Editing', 3, 'Post-production and video assembly'),
(27, 'Adobe Premiere', 3, 'Professional video editing software'),
(28, 'Drone Photography', 3, 'Aerial photography and videography'),
(29, 'Wedding Photography', 3, 'Event and wedding documentation'),
(30, 'Product Photography', 3, 'Commercial product imagery'),
(31, 'Street Photography', 3, 'Urban candid photography'),
(32, 'Studio Lighting', 3, 'Professional lighting setup'),
(33, 'Final Cut Pro', 3, 'Apple video editing suite'),
(34, 'After Effects', 3, 'Motion graphics and visual effects'),
(35, 'Creative Writing', 4, 'Fiction writing and storytelling'),
(36, 'Copywriting', 4, 'Promotional and marketing writing'),
(37, 'Blogging', 4, 'Blog content creation and management'),
(38, 'Journalism', 4, 'Journalism and news writing'),
(39, 'Screenwriting', 4, 'Script and screenplay writing'),
(40, 'SEO Writing', 4, 'Search engine optimized content'),
(41, 'Editing & Proofreading', 4, 'Text correction and improvement'),
(42, 'Technical Writing', 4, 'Technical documentation and manuals'),
(43, 'Poetry', 4, 'Poetry composition and technique'),
(44, 'Social Media Content', 4, 'Social media content writing'),
(45, 'Ghostwriting', 4, 'Ghostwriting for authors'),
(46, 'Classical Guitar', 5, 'Classical guitar instruction'),
(47, 'Acoustic Guitar', 5, 'Acoustic guitar techniques'),
(48, 'Electric Guitar', 5, 'Advanced electric guitar techniques'),
(49, 'Singing', 5, 'Vocal instruction and technique'),
(50, 'Piano', 5, 'Piano lessons and music theory'),
(51, 'Music Theory', 5, 'Harmony, melody and composition'),
(52, 'Ukulele', 5, 'Ukulele learning and teaching'),
(53, 'Drums', 5, 'Drum techniques and rhythm'),
(54, 'Bass', 5, 'Bass instruction and accompaniment'),
(55, 'Music Composition', 5, 'Music creation and composition'),
(56, 'Stage Performance', 5, 'Performance and stage techniques'),
(57, 'Mediterranean Cuisine', 6, 'Mediterranean cooking preparation'),
(58, 'Baking & Pastry', 6, 'Baking techniques and pastry'),
(59, 'Algerian Cuisine', 6, 'Traditional Algerian dishes and recipes'),
(60, 'International Cuisine', 6, 'Asian, French, and Italian cooking'),
(61, 'Halal Cooking', 6, 'Certified halal food preparation'),
(62, 'Special Diet Cooking', 6, 'Vegan, gluten-free, vegetarian meals'),
(63, 'Cooking Techniques', 6, 'Roasting, braising, and culinary methods'),
(64, 'Sauce Preparation', 6, 'Homemade sauces and condiments'),
(65, 'Kitchen Preparation', 6, 'Kitchen setup and organization'),
(66, 'Catering & Events', 6, 'Professional catering and events'),
(67, 'Healthy Nutrition Cooking', 6, 'Healthy and balanced meal preparation'),
(68, 'Personal Training', 7, 'Customized training programs'),
(69, 'Yoga & Pilates', 7, 'Yoga and Pilates instruction'),
(70, 'Cardio Fitness', 7, 'Cardiovascular training'),
(71, 'Weight Training', 7, 'Muscle strengthening and bodybuilding'),
(72, 'Sports Nutrition', 7, 'Nutritional advice for athletes'),
(73, 'Stretching & Flexibility', 7, 'Flexibility and stretching techniques'),
(74, 'Fitness Dance', 7, 'Dance fitness classes'),
(75, 'Crossfit', 7, 'Intense functional training'),
(76, 'Health Coaching', 7, 'Overall health and wellness coaching'),
(77, 'Physical Rehabilitation', 7, 'Recovery and physical rehabilitation'),
(78, 'Meditation & Relaxation', 7, 'Relaxation and meditation techniques'),
(79, 'French', 8, 'French language instruction'),
(80, 'English', 8, 'English courses for all levels'),
(81, 'Classical Arabic', 8, 'Arabic grammar and literature'),
(82, 'Algerian Dialect', 8, 'Algerian Arabic (Darija)'),
(83, 'Spanish', 8, 'Spanish language and culture'),
(84, 'German', 8, 'German language instruction'),
(85, 'Italian', 8, 'Italian language and culture'),
(86, 'Mandarin', 8, 'Mandarin Chinese and culture'),
(87, 'TOEFL & IELTS', 8, 'English language test preparation'),
(88, 'Translation', 8, 'Professional translation services'),
(89, 'Language Conversation', 8, 'Conversation practice in foreign languages'),
(90, 'Auto Mechanics', 9, 'General automobile repair'),
(91, 'Engines & Transmissions', 9, 'Engine and transmission repair'),
(92, 'Braking Systems', 9, 'Brake systems and maintenance'),
(93, 'Automotive Electrical', 9, 'Vehicle electrical systems'),
(94, 'Air Conditioning', 9, 'AC repair and recharging'),
(95, 'Automotive Painting', 9, 'Car painting and bodywork'),
(96, 'Computer Diagnostics', 9, 'OBD diagnostics and electronics'),
(97, 'Suspension & Alignment', 9, 'Suspension and wheel alignment'),
(98, 'Preventive Maintenance', 9, 'Regular maintenance and upkeep'),
(99, 'Tires & Wheels', 9, 'Tire replacement and balancing'),
(100, 'Two-Wheeler Mechanics', 9, 'Motorcycle and scooter repair'),
(101, 'Ornamental Gardening', 10, 'Decorative garden creation'),
(102, 'Permaculture', 10, 'Permanent and sustainable cultivation'),
(103, 'Vegetable Gardening', 10, 'Vegetable gardening and crops'),
(104, 'Plant Care', 10, 'Plant maintenance and care'),
(105, 'Landscaping', 10, 'Professional landscape design'),
(106, 'Ecological Gardening', 10, 'Ecological gardening techniques'),
(107, 'Garden Composition', 10, 'Garden design and composition'),
(108, 'Indoor Plants', 10, 'Indoor plant cultivation'),
(109, 'Aromatic Herbs', 10, 'Herb and aromatic plant growing'),
(110, 'Composting', 10, 'Composting and fertilization technique'),
(111, 'Bonsai', 10, 'Bonsai art and miniature cultivation'),
(112, 'Entrepreneurship', 11, 'Business launch and management'),
(113, 'Financial Management', 11, 'Business finance management'),
(114, 'Accounting', 11, 'Bookkeeping and accounting'),
(115, 'Investing', 11, 'Investment advice and strategies'),
(116, 'Digital Marketing', 11, 'Online marketing strategies'),
(117, 'Project Management', 11, 'Project management techniques'),
(118, 'Leadership', 11, 'Leadership development'),
(119, 'Business Negotiation', 11, 'Negotiation techniques'),
(120, 'Business Plan', 11, 'Business plan creation'),
(121, 'Sales & Commerce', 11, 'Sales techniques'),
(122, 'Human Resources', 11, 'Human resources management'),
(123, 'Acrylic Painting', 12, 'Acrylic painting techniques'),
(124, 'Oil Painting', 12, 'Oil painting techniques'),
(125, 'Watercolor', 12, 'Watercolor painting'),
(126, 'Pencil Drawing', 12, 'Classical drawing techniques'),
(127, 'Sculpture', 12, 'Sculpture and modeling techniques'),
(128, 'Ceramics', 12, 'Pottery and ceramic work'),
(129, 'Weaving', 12, 'Traditional weaving techniques'),
(130, 'Embroidery', 12, 'Embroidery and thread work'),
(131, 'Calligraphy', 12, 'Calligraphy and artistic lettering'),
(132, 'Digital Art', 12, 'Digital artistic creation'),
(133, 'Engraving', 12, 'Engraving and printing techniques'),
(134, 'Masonry', 13, 'Masonry techniques'),
(135, 'Carpentry', 13, 'Woodworking and carpentry'),
(136, 'Home Electrical', 13, 'Home electrical installation'),
(137, 'Plumbing', 13, 'Plumbing installation and repair'),
(138, 'Interior Painting', 13, 'Interior painting and finishing'),
(139, 'Flooring', 13, 'Tile and flooring installation'),
(140, 'Wallpapering', 13, 'Wallpaper and tapestry installation'),
(141, 'Bathroom Renovation', 13, 'Bathroom renovation'),
(142, 'Kitchen Renovation', 13, 'Kitchen renovation'),
(143, 'Doors & Windows', 13, 'Door and window installation'),
(144, 'Heating & Cooling', 13, 'Heating and air conditioning installation'),
(145, 'Home Organization', 14, 'Home organization and storage'),
(146, 'Eco Cleaning', 14, 'Ecological and natural cleaning'),
(147, 'Decluttering', 14, 'Decluttering technique'),
(148, 'Wardrobe Organization', 14, 'Clothing organization'),
(149, 'Professional Cleaning', 14, 'Professional home cleaning'),
(150, 'Kitchen Organization', 14, 'Kitchen organization'),
(151, 'Home Office Organization', 14, 'Work space organization'),
(152, 'Storage Management', 14, 'Efficient storage systems'),
(153, 'Detail Cleaning', 14, 'Detailed and thorough cleaning'),
(154, 'Space Evaluation', 14, 'Space evaluation and optimization'),
(155, 'Lifestyle Advice', 14, 'Minimalist lifestyle advice'),
(156, 'Mathematics', 15, 'Mathematics instruction'),
(157, 'Chemistry', 15, 'Chemistry courses and laboratory'),
(158, 'Physics', 15, 'Physics instruction'),
(159, 'Biology', 15, 'Biology and science courses'),
(160, 'Academic Tutoring', 15, 'Homework help and catch-up'),
(161, 'Exam Preparation', 15, 'Baccalaureate and exam preparation'),
(162, 'Scientific English', 15, 'Technical and scientific English'),
(163, 'Computer Science Education', 15, 'Computer science instruction'),
(164, 'Natural Sciences', 15, 'Natural sciences instruction'),
(165, 'Study Methodology', 15, 'Effective study techniques'),
(166, 'Academic Orientation', 15, 'Academic guidance and orientation'),
(167, 'Sewing', 16, 'Sewing and garment construction'),
(168, 'Clothing Design', 16, 'Clothing design and creation'),
(169, 'Textile Alteration', 16, 'Clothing alteration and repair'),
(170, 'Professional Makeup', 16, 'Artistic and professional makeup'),
(171, 'Hairstyling', 16, 'Hair cutting and styling'),
(172, 'Skincare', 16, 'Facial and skin care'),
(173, 'Body Beauty', 16, 'Body care and beauty'),
(174, 'Personal Styling', 16, 'Personal style and image consultation'),
(175, 'Fashion & Trends', 16, 'Fashion advice and trends'),
(176, 'Fashion Accessories', 16, 'Accessory and jewelry creation'),
(177, 'Nail Care', 16, 'Manicure and pedicure services');

-- --------------------------------------------------------

--
-- Structure de la table `usernotifications`
--

CREATE TABLE `usernotifications` (
  `NotificationId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `SenderId` int(11) DEFAULT NULL,
  `ExchangeId` int(11) DEFAULT NULL,
  `EventId` int(11) DEFAULT NULL,
  `AttendeeId` int(11) DEFAULT NULL,
  `RecipientId` int(11) NOT NULL,
  `NotificationType` enum('like','comment','rating','booking','accepted','completing','being_refused','acceptedInEvent','RejectedFromEvent','earned','spent','eventJoinRequest') NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Message` longtext,
  `IsRead` enum('yes','no') DEFAULT 'no',
  `CreatedAt` timestamp DEFAULT CURRENT_TIMESTAMP,
  `NotificationSection` varchar(50),
  KEY `idx_recipient` (`RecipientId`),
  KEY `idx_sender` (`SenderId`),
  KEY `idx_isread` (`IsRead`),
  CONSTRAINT `fk_notif_sender` FOREIGN KEY (`SenderId`) REFERENCES `users`(`UserId`) ON DELETE SET NULL,
  CONSTRAINT `fk_notif_recipient` FOREIGN KEY (`RecipientId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `usernotifications`
--

INSERT INTO `usernotifications` (`NotificationId`, `SenderId`, `ExchangeId`, `EventId`, `AttendeeId`, `RecipientId`, `NotificationType`, `Title`, `Message`, `IsRead`, `CreatedAt`, `NotificationSection`) VALUES
(148, 1, NULL, NULL, NULL, 2, 'acceptedInEvent', 'Event Request Accepted', 'JohnDoe has accepted your request to join Web Development Workshop', 'no', '2026-02-06 02:31:45', 'events'),
(149, 3, NULL, NULL, NULL, 4, 'acceptedInEvent', 'Event Request Accepted', 'MikeSmith has accepted your request to join UI/UX Design Session', 'yes', '2026-02-06 02:31:45', 'events'),
(150, 2, NULL, NULL, NULL, 1, 'acceptedInEvent', 'Event Request Accepted', 'JaneDoe has accepted your request to join Python Basics', 'no', '2026-02-06 02:31:45', 'events'),
(151, 2, NULL, NULL, NULL, 5, 'RejectedFromEvent', 'Event Request Declined', 'JaneDoe has declined your request to join Python Basics', 'no', '2026-02-06 02:31:45', 'events'),
(152, 1, NULL, NULL, NULL, 3, 'RejectedFromEvent', 'Event Request Declined', 'JohnDoe has declined your request to join Web Development Workshop', 'yes', '2026-02-06 02:31:45', 'events'),
(153, 2, NULL, NULL, NULL, 1, 'booking', 'Event Join Request', 'JaneDoe has requested to join your event Web Development Workshop on January 28, 2026', 'no', '2026-02-06 02:31:45', 'Exchange'),
(154, 3, NULL, NULL, NULL, 1, 'booking', 'Event Join Request', 'MikeSmith has requested to join your event Web Development Workshop on January 27, 2026', 'no', '2026-02-06 02:31:45', 'Exchange'),
(155, 4, NULL, NULL, NULL, 2, 'booking', 'Event Join Request', 'SarahLee has requested to join your event Python Basics on January 26, 2026', 'no', '2026-02-06 02:31:45', 'Exchange'),
(156, 1, NULL, NULL, NULL, 3, 'booking', 'Event Join Request', 'JohnDoe has requested to join your event Data Science Meetup on January 25, 2026', 'yes', '2026-02-06 02:31:45', 'Exchange'),
(157, 3, NULL, NULL, NULL, 1, 'booking', 'New Exchange Request', 'MikeSmith wants to exchange skills with you for JavaScript Fundamentals', 'no', '2026-02-06 02:31:45', 'Exchange'),
(158, 4, NULL, NULL, NULL, 2, 'accepted', 'Exchange Accepted', 'SarahLee has accepted your skill exchange request', 'yes', '2026-02-06 02:31:45', 'Exchange'),
(159, 1, NULL, NULL, NULL, 4, 'completing', 'Exchange Completed', 'JohnDoe has marked the exchange as completed', 'no', '2026-02-06 02:31:45', 'Exchange'),
(160, 2, NULL, NULL, NULL, 3, 'being_refused', 'Exchange Declined', 'JaneDoe has declined your skill exchange request', 'no', '2026-02-06 02:31:45', 'Exchange'),
(161, 2, NULL, NULL, NULL, 1, 'rating', 'New Rating Received', 'JaneDoe gave you a 5-star rating!', 'no', '2026-02-06 02:31:45', 'Reviews'),
(162, 3, NULL, NULL, NULL, 2, 'rating', 'New Rating Received', 'MikeSmith gave you a 4-star rating', 'yes', '2026-02-06 02:31:45', 'Reviews'),
(163, 1, NULL, NULL, NULL, 3, 'comment', 'New Comment', 'JohnDoe commented on your post: Great tutorial!', 'no', '2026-02-06 02:31:45', 'Reviews'),
(164, 4, NULL, NULL, NULL, 1, 'like', 'New Like', 'SarahLee liked your post', 'yes', '2026-02-06 02:31:45', 'Reviews'),
(165, NULL, NULL, NULL, NULL, 1, 'earned', 'Credits Earned', 'You earned 50 credits for completing an exchange', 'no', '2026-02-06 02:31:45', 'credits'),
(166, NULL, NULL, NULL, NULL, 2, 'spent', 'Credits Spent', 'You spent 30 credits on Web Development Workshop', 'yes', '2026-02-06 02:31:45', 'credits'),
(167, NULL, NULL, NULL, NULL, 3, 'earned', 'Credits Earned', 'You earned 25 credits for receiving a 5-star rating', 'no', '2026-02-06 02:31:45', 'credits');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `UserId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `UserName` varchar(45) NOT NULL UNIQUE,
  `FullName` varchar(45) NOT NULL,
  `Email` varchar(45) NOT NULL UNIQUE,
  `Password` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `ProfilePicture` varchar(255) DEFAULT NULL,
  `ProfessionalTitle` varchar(100) DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `BirthDate` date DEFAULT NULL,
  `Gender` enum('M','F') DEFAULT NULL,
  `UserSince` datetime DEFAULT current_timestamp(),
  `IsBanned` enum('yes','no') DEFAULT 'no',
  `Rating` decimal(3,2) DEFAULT 0.00 CHECK (Rating >= 0.00 AND Rating <= 5.00),
  `RatingCount` int(11) DEFAULT 0 CHECK (RatingCount >= 0),
  `ExchangeCount` int(11) DEFAULT 0 CHECK (ExchangeCount >= 0),
  `CreditBalance` int(11) DEFAULT 100 CHECK (CreditBalance >= 0),
  `IsAdmin` enum('yes','no') DEFAULT 'no',
  KEY `idx_email` (`Email`),
  KEY `idx_username` (`UserName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`UserId`, `UserName`, `FullName`, `Email`, `Password`, `Description`, `ProfilePicture`, `ProfessionalTitle`, `Location`, `PhoneNumber`, `BirthDate`, `Gender`, `UserSince`, `IsBanned`, `Rating`, `RatingCount`, `ExchangeCount`, `CreditBalance`, `IsAdmin`) VALUES
(1, 'youssef_dev', 'Youssef Benhadj', 'youssef@outlook.dz', '1223456aaaa', 'Experienced web developer passionate about teaching and mentoring', NULL, 'Senior Developer', 'Algiers, Hydra', '+213 05 12 345 678', '1990-05-15', 'M', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 250, 'yes'),
(2, 'fatima_design', 'Fatima Debbache', 'fatima@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Creative art director with 8 years of experience in digital design', '../../assets/uploads/profile_pics/profile6966e9731bf619.87408790.png', 'UI/UX Designer', 'Oran, Downtown', '+213 06 23 456 789', '1988-08-22', 'F', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 300, 'no'),
(3, 'ahmed_photo', 'Ahmed Medjahed', 'ahmed.photo@outlook.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Professional photographer and certified trainer in visual media', NULL, 'Photographer', 'Constantine, Belkaid', '+213 07 34 567 890', '1985-03-10', 'M', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 180, 'no'),
(4, 'leila_writer', 'Leila Saidane', 'leila.writer@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Content writer and expert in copywriting and SEO optimization', NULL, 'Content Writer', 'Annaba, Sidi Salem', '+213 05 45 678 901', '1992-11-30', 'F', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 220, 'no'),
(5, 'karim_music', 'Karim Bouchikhi', 'karim.music@outlook.dz', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Music teacher specialized in guitar instruction for all levels', NULL, 'Music Instructor', 'Tlemcen', '+213 06 56 789 012', '1987-07-18', 'M', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 190, 'no'),
(6, 'amina_chef', 'Amina Hadj-Aissa', 'amina.chef@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Professional chef trained in culinary arts and traditional cooking', NULL, 'Chef', 'Blida, Downtown', '+213 07 67 890 123', '1991-04-25', 'F', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 280, 'no'),
(7, 'ali_fitness', 'Ali Bouchta', 'ali.fitness@outlook.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Certified personal trainer and nutritionist with 12 years experience', NULL, 'Fitness Trainer', 'Setif, Haouchias', '+213 05 78 901 234', '1989-09-12', 'M', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 210, 'no'),
(8, 'samira_language', 'Samira Kebaili', 'samira.lang@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Polyglot fluent in 6 languages with international teaching credentials', NULL, 'Language Teacher', 'Medea', '+213 06 89 012 345', '1993-06-08', 'F', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 260, 'no'),
(9, 'moussa_mechanic', 'Moussa Aidel', 'moussa.mechanic@outlook.dz', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Auto mechanic with 15 years experience in vehicle diagnostics', NULL, 'Mechanic', 'Tipaza, Chenoua', '+213 07 90 123 456', '1982-11-20', 'M', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 230, 'no'),
(10, 'zainab_garden', 'Zainab Benkhalifa', 'zainab.garden@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Master horticulturist and expert in sustainable landscaping design', NULL, 'Horticulturist', 'Boumerdes, Baya', '+213 05 01 234 567', '1986-02-14', 'F', '2025-12-16 21:51:24', 'no', '0.00', 0, 0, 270, 'no'),
(25, 'mouloud691', 'mouloud yacine', 'yacine.mouloud@ensia.edu.dz', '$2y$10$kir8yhI0vpeUHIQD.a1bWuv1xqAdt7XtJVDn06..ohGAvQ6x7ehby', 'This is your profile description. Tell people more about yourself!', NULL, 'Your Professional Title', 'bejaia', '+213783808305', '2006-12-06', 'M', '2026-02-02 16:02:01', 'no', '0.00', 0, 0, 0, 'no'),
(26, 'mouloud556', 'mouloud yacine', 'mouloudyacine06@gmail.com', '$2y$10$udgV8OkN/4exlz.qsBnjM.96DDEWCxX8//bwR70/3nD8YP1YQf3tK', 'salaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaam', '../../assets/uploads/profile_pics/profile6984bb18c50ea9.52262172.jpg', 'Your Professional Title', 'bejaia', '+213783808305', '2006-12-06', 'M', '2026-02-02 21:07:35', 'no', '0.00', 0, 0, 200, 'no'),
(27, 'mouloud458', 'mouloud yacine', 'moncefgh00@gmail.com', '$2y$10$x0oGs3q/rYTbhzZprpgNo.bUz08uavJ/GGSe84VbefxWdVfw.qqMe', 'This is your profile description. Tell people more about yourself!', NULL, 'Your Professional Title', 'bejaia', '+213783808305', '2002-12-06', 'M', '2026-02-04 18:37:27', 'no', '0.00', 0, 0, 100, 'no'),
(28, 'salam743', 'salam', 'ya@gmail.com', '$2y$10$zC9ZPH1zuUQiR1jY.5T5NeMr41nmgE.57kArTI6mHsrYFyehSP/.W', NULL, NULL, NULL, 'LA', '0663450912', '2002-12-02', 'M', '2026-02-05 17:13:17', 'no', '0.00', 0, 0, 10, 'no'),
(30, 'hello770', 'hello', 'sa@gmail.com', '$2y$10$iOPAJuKtcnrXUX36ZN.cP.n6qWxs0aoi/eo/D52wvdF.8zXlybqAO', NULL, NULL, NULL, 'NY', '0688620336', '2005-02-02', 'M', '2026-02-05 17:34:28', 'no', '0.00', 0, 0, 70, 'yes'),
(31, 'tsssss284', 'tsssss', 'alex.rivera@example.com', '$2y$10$YYZzwyV9X4ZWIuET54t/He6a6oxJPQFHHvaXXmBRbuXkFrvAC5Cem', NULL, NULL, NULL, 'Chicago', '0668123541', '2002-12-22', 'M', '2026-02-05 19:45:32', 'no', '0.00', 0, 0, 100, 'no');

-- --------------------------------------------------------

--
-- Structure de la table `userskills`
--

CREATE TABLE `userskills` (
  `UserSkillId` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `UserId` int(11) NOT NULL,
  `SkillId` int(11) NOT NULL,
  `SkillType` enum('teach','learn') NOT NULL,
  `ProficiencyLevel` int(11) NOT NULL DEFAULT 0 CHECK (ProficiencyLevel >= 0 AND ProficiencyLevel <= 100),
  KEY `idx_user_skills` (`UserId`),
  KEY `idx_skill` (`SkillId`),
  UNIQUE KEY unique_user_skill (`UserId`, `SkillId`, `SkillType`),
  CONSTRAINT `fk_userskill_user` FOREIGN KEY (`UserId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE,
  CONSTRAINT `fk_userskill_skill` FOREIGN KEY (`SkillId`) REFERENCES `skills`(`SkillId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `userskills`
--

INSERT INTO `userskills` (`UserSkillId`, `UserId`, `SkillId`, `SkillType`, `ProficiencyLevel`) VALUES
(1, 1, 1, 'teach', 25),
(2, 1, 2, 'teach', 25),
(3, 1, 7, 'teach', 25),
(4, 2, 13, 'teach', 25),
(5, 2, 14, 'teach', 25),
(6, 2, 20, 'teach', 25),
(7, 3, 25, 'teach', 25),
(8, 3, 26, 'teach', 25),
(9, 3, 28, 'teach', 25),
(10, 4, 37, 'teach', 25),
(11, 4, 38, 'teach', 25),
(12, 4, 40, 'teach', 25),
(13, 5, 49, 'teach', 25),
(14, 5, 51, 'teach', 25),
(15, 5, 57, 'teach', 25),
(16, 6, 61, 'teach', 25),
(17, 6, 62, 'teach', 25),
(18, 6, 63, 'teach', 25),
(19, 7, 71, 'teach', 25),
(20, 7, 73, 'teach', 25),
(21, 8, 85, 'teach', 25),
(22, 8, 86, 'teach', 25),
(23, 9, 93, 'teach', 25),
(24, 9, 94, 'teach', 25),
(25, 10, 110, 'teach', 25),
(26, 10, 111, 'teach', 25),
(35, 25, 13, 'teach', 0),
(36, 25, 156, 'teach', 0),
(38, 26, 158, 'learn', 25),
(40, 27, 9, 'learn', 25),
(42, 27, 168, 'teach', 0),
(67, 26, 73, 'teach', 25),
(68, 26, 118, 'teach', 25),
(69, 26, 146, 'teach', 25),
(70, 28, 119, 'teach', 0),
(71, 28, 53, 'learn', 0),
(72, 30, 171, 'teach', 0),
(73, 30, 50, 'learn', 0),
(74, 31, 114, 'teach', 0),
(75, 31, 84, 'learn', 0);

-- --------------------------------------------------------

--
-- Structure de la table `user_seeking_skills`
--

CREATE TABLE `user_seeking_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `UserId` int(11) NOT NULL,
  `SkillId` int(11) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `unique_user_seeking_skill` (`UserId`,`SkillId`),
  KEY `idx_skill` (`SkillId`),
  CONSTRAINT `fk_seeking_user` FOREIGN KEY (`UserId`) REFERENCES `users`(`UserId`) ON DELETE CASCADE,
  CONSTRAINT `fk_seeking_skill` FOREIGN KEY (`SkillId`) REFERENCES `skills`(`SkillId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_seeking_skills`
--

INSERT INTO `user_seeking_skills` (`id`, `UserId`, `SkillId`, `CreatedAt`) VALUES
(84, 2, 90, '2026-02-01 22:06:02'),
(85, 2, 131, '2026-02-01 22:06:02'),
(86, 1, 119, '2026-02-01 23:04:41'),
(87, 1, 121, '2026-02-01 23:04:41'),
(88, 1, 141, '2026-02-01 23:04:41'),
(89, 1, 142, '2026-02-01 23:04:41'),
(101, 26, 116, '2026-02-05 15:59:21');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contacts_email` (`email`),
  ADD KEY `idx_contacts_date` (`created_at`);

--
-- Index pour la table `eventsattendees`
--
ALTER TABLE `eventsattendees`
  ADD PRIMARY KEY (`AttendanceId`),
  ADD UNIQUE KEY `unique_event_user` (`EventId`,`UserId`),
  ADD KEY `idx_attendees_event` (`EventId`),
  ADD KEY `idx_attendees_user` (`UserId`);

--
-- Index pour la table `eventskills`
--
ALTER TABLE `eventskills`
  ADD PRIMARY KEY (`EventSkillId`),
  ADD UNIQUE KEY `unique_event_skill` (`EventId`,`SkillId`),
  ADD KEY `idx_eventskills_event` (`EventId`),
  ADD KEY `idx_eventskills_skill` (`SkillId`);

--
-- Index pour la table `postavailabledates`
--
ALTER TABLE `postavailabledates`
  ADD PRIMARY KEY (`PostAvailableDateId`),
  ADD KEY `idx_post_available_dates` (`PostId`,`AvailableDate`);

--
-- Index pour la table `postlikes`
--
ALTER TABLE `postlikes`
  ADD PRIMARY KEY (`LikeId`),
  ADD UNIQUE KEY `unique_post_like` (`PostId`,`UserId`),
  ADD KEY `idx_likes_post` (`PostId`),
  ADD KEY `idx_likes_user` (`UserId`);

--
-- Index pour la table `postskills`
--
ALTER TABLE `postskills`
  ADD PRIMARY KEY (`PostSkillId`),
  ADD UNIQUE KEY `unique_post_skill` (`PostId`,`SkillId`),
  ADD KEY `idx_postskills_post` (`PostId`),
  ADD KEY `idx_postskills_skill` (`SkillId`);

--
-- Index pour la table `profile_reviews`
--
ALTER TABLE `profile_reviews`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `unique_review_pair` (`ReviewerID`,`userID`);

--
-- Index pour la table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`SkillId`),
  ADD UNIQUE KEY `SkillName` (`SkillName`),
  ADD KEY `idx_skills_category` (`CategoryId`);

--
-- Index pour la table `user_seeking_skills`
--
ALTER TABLE `user_seeking_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_skill` (`UserId`,`SkillId`),
  ADD KEY `fk_seeking_skill` (`SkillId`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `category`
--
ALTER TABLE `category`
  MODIFY `CategoryId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `credittransactions`
--
ALTER TABLE `credittransactions`
  MODIFY `TransactionId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `events`
--
ALTER TABLE `events`
  MODIFY `EventId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `eventsattendees`
--
ALTER TABLE `eventsattendees`
  MODIFY `AttendanceId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT pour la table `eventskills`
--
ALTER TABLE `eventskills`
  MODIFY `EventSkillId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT pour la table `exchanges`
--
ALTER TABLE `exchanges`
  MODIFY `ExchangeId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `postavailabledates`
--
ALTER TABLE `postavailabledates`
  MODIFY `PostAvailableDateId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `postlikes`
--
ALTER TABLE `postlikes`
  MODIFY `LikeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT pour la table `posts`
--
ALTER TABLE `posts`
  MODIFY `PostId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `postskills`
--
ALTER TABLE `postskills`
  MODIFY `PostSkillId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- AUTO_INCREMENT pour la table `profile_reviews`
--
ALTER TABLE `profile_reviews`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `rating`
--
ALTER TABLE `rating`
  MODIFY `ReviewId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `skills`
--
ALTER TABLE `skills`
  MODIFY `SkillId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT pour la table `usernotifications`
--
ALTER TABLE `usernotifications`
  MODIFY `NotificationId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `UserId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `userskills`
--
ALTER TABLE `userskills`
  MODIFY `UserSkillId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_seeking_skills`
--
ALTER TABLE `user_seeking_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `eventsattendees`
--
ALTER TABLE `eventsattendees`
  ADD CONSTRAINT `eventsattendees_ibfk_1` FOREIGN KEY (`EventId`) REFERENCES `events` (`EventId`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventsattendees_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `users` (`UserId`) ON DELETE CASCADE;

--
-- Contraintes pour la table `eventskills`
--
ALTER TABLE `eventskills`
  ADD CONSTRAINT `eventskills_ibfk_1` FOREIGN KEY (`EventId`) REFERENCES `events` (`EventId`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventskills_ibfk_2` FOREIGN KEY (`SkillId`) REFERENCES `skills` (`SkillId`) ON DELETE CASCADE;

--
-- Contraintes pour la table `postavailabledates`
--
ALTER TABLE `postavailabledates`
  ADD CONSTRAINT `postavailabledates_ibfk_1` FOREIGN KEY (`PostId`) REFERENCES `posts` (`PostId`) ON DELETE CASCADE;

--
-- Contraintes pour la table `postlikes`
--
ALTER TABLE `postlikes`
  ADD CONSTRAINT `postlikes_ibfk_1` FOREIGN KEY (`PostId`) REFERENCES `posts` (`PostId`) ON DELETE CASCADE,
  ADD CONSTRAINT `postlikes_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `users` (`UserId`) ON DELETE CASCADE;

--
-- Contraintes pour la table `postskills`
--
ALTER TABLE `postskills`
  ADD CONSTRAINT `postskills_ibfk_1` FOREIGN KEY (`PostId`) REFERENCES `posts` (`PostId`) ON DELETE CASCADE,
  ADD CONSTRAINT `postskills_ibfk_2` FOREIGN KEY (`SkillId`) REFERENCES `skills` (`SkillId`) ON DELETE CASCADE;

--
-- Contraintes pour la table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`CategoryId`) REFERENCES `category` (`CategoryId`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_seeking_skills`
--
ALTER TABLE `user_seeking_skills`
  ADD CONSTRAINT `fk_seeking_skill` FOREIGN KEY (`SkillId`) REFERENCES `skills` (`SkillId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_seeking_user` FOREIGN KEY (`UserId`) REFERENCES `users` (`UserId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
