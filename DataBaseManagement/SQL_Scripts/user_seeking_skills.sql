-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 12:13 AM
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
-- Database: `skill_service_exchange_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_seeking_skills`
--

CREATE TABLE `user_seeking_skills` (
  `id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `SkillId` int(11) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_seeking_skills`
--

INSERT INTO `user_seeking_skills` (`id`, `UserId`, `SkillId`, `CreatedAt`) VALUES
(84, 2, 90, '2026-02-01 22:06:02'),
(85, 2, 131, '2026-02-01 22:06:02'),
(86, 1, 119, '2026-02-01 23:04:41'),
(87, 1, 121, '2026-02-01 23:04:41'),
(88, 1, 141, '2026-02-01 23:04:41'),
(89, 1, 142, '2026-02-01 23:04:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user_seeking_skills`
--
ALTER TABLE `user_seeking_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_skill` (`UserId`,`SkillId`),
  ADD KEY `fk_seeking_skill` (`SkillId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_seeking_skills`
--
ALTER TABLE `user_seeking_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_seeking_skills`
--
ALTER TABLE `user_seeking_skills`
  ADD CONSTRAINT `fk_seeking_skill` FOREIGN KEY (`SkillId`) REFERENCES `skills` (`SkillId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_seeking_user` FOREIGN KEY (`UserId`) REFERENCES `users` (`UserId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
