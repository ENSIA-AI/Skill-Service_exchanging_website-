-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 12:14 AM
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
-- Table structure for table `profile_reviews`
--

CREATE TABLE `profile_reviews` (
  `ID` int(11) NOT NULL,
  `ReviewerID` int(11) NOT NULL,
  `ReviewDate` datetime NOT NULL,
  `ReviewText` text NOT NULL,
  `ReviewRate` int(11) NOT NULL,
  `userID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile_reviews`
--

INSERT INTO `profile_reviews` (`ID`, `ReviewerID`, `ReviewDate`, `ReviewText`, `ReviewRate`, `userID`) VALUES
(13, 3, '2026-01-17 21:37:43', 'NICE', 5, 2),
(14, 2, '2026-01-17 21:38:29', 'NOOOOOOO', 1, 3),
(17, 2, '2026-02-01 23:19:59', 'This is my first test review for this user!', 5, 4),
(18, 4, '2026-02-01 23:36:00', 'This is my first test review for this user!', 5, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `profile_reviews`
--
ALTER TABLE `profile_reviews`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `unique_review_pair` (`ReviewerID`,`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `profile_reviews`
--
ALTER TABLE `profile_reviews`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
