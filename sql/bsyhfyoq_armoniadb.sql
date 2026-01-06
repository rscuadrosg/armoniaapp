-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 05, 2026 at 10:39 PM
-- Server version: 11.4.9-MariaDB-cll-lve
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bsyhfyoq_armoniadb`
--

-- --------------------------------------------------------

--
-- Table structure for table `band_roles`
--

CREATE TABLE `band_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `band_roles`
--

INSERT INTO `band_roles` (`id`, `role_name`, `sort_order`) VALUES
(1, 'Voz Principal', 1),
(2, 'Piano / Teclados', 2),
(3, 'Bajo', 3),
(4, 'Batería', 4),
(5, 'Guitarra Eléctrica', 5),
(6, 'Guitarra 2', 6);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_date` datetime NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_title`, `event_date`, `description`) VALUES
(12, '', '2025-12-24 00:00:00', 'Servicio de Navidad'),
(13, '', '2025-12-31 00:00:00', 'año nuevo');

-- --------------------------------------------------------

--
-- Table structure for table `event_assignments`
--

CREATE TABLE `event_assignments` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `instrument` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `event_assignments`
--

INSERT INTO `event_assignments` (`id`, `event_id`, `member_id`, `instrument`) VALUES
(5, 12, 1, 'Voz Principal'),
(10, 13, 1, 'Voz Principal');

-- --------------------------------------------------------

--
-- Table structure for table `event_confirmations`
--

CREATE TABLE `event_confirmations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `status` enum('pendiente','confirmado','rechazado') DEFAULT 'pendiente',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `event_confirmations`
--

INSERT INTO `event_confirmations` (`id`, `event_id`, `member_id`, `status`, `updated_at`) VALUES
(3, 12, 1, 'confirmado', '2025-12-21 06:52:47'),
(4, 13, 1, 'confirmado', '2025-12-28 04:44:17');

-- --------------------------------------------------------

--
-- Table structure for table `event_songs`
--

CREATE TABLE `event_songs` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `song_id` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `event_songs`
--

INSERT INTO `event_songs` (`id`, `event_id`, `song_id`, `position`) VALUES
(10, 12, 1, NULL),
(11, 12, 3, NULL),
(13, 13, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `member_type` enum('Internal','External') DEFAULT 'Internal',
  `is_available` tinyint(1) DEFAULT 1,
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` enum('admin','musico','director') DEFAULT 'musico'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `full_name`, `email`, `password`, `member_type`, `is_available`, `profile_photo`, `role`) VALUES
(1, 'Richard Cuadros', NULL, NULL, 'Internal', 1, 'profile_1_1766295708.jpg', 'musico'),
(2, 'Leidy Martinez', NULL, NULL, 'Internal', 1, NULL, 'musico'),
(3, 'Diego Paez', NULL, NULL, 'Internal', 1, NULL, 'musico'),
(4, 'Juan David Martinez', NULL, NULL, 'External', 1, NULL, 'musico'),
(5, 'Andres Benavides', NULL, NULL, 'External', 1, NULL, 'musico');

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) DEFAULT NULL,
  `musical_key` varchar(10) DEFAULT NULL,
  `youtube_link` varchar(255) DEFAULT NULL,
  `bpm` int(11) DEFAULT NULL,
  `has_multitrack` tinyint(1) DEFAULT 0,
  `has_lyrics` varchar(255) DEFAULT NULL,
  `priority` enum('High','Medium','Low') DEFAULT 'Medium',
  `midi_path` varchar(255) DEFAULT NULL,
  `propresenter_path` varchar(255) DEFAULT NULL,
  `is_requested` tinyint(1) DEFAULT 0,
  `requested_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`id`, `title`, `artist`, `musical_key`, `youtube_link`, `bpm`, `has_multitrack`, `has_lyrics`, `priority`, `midi_path`, `propresenter_path`, `is_requested`, `requested_by`) VALUES
(1, 'A cada instante', 'Marcos Witt', 'F', '', 120, 1, 'https://docs.google.com/spreadsheets/d/183n4DQdscpqu1302T1oray2y-exavaQx6K0v_DIOl0Q/edit?usp=drive_link', 'Low', '', 'https://docs.google.com/spreadsheets/d/183n4DQdscpqu1302T1oray2y-exavaQx6K0v_DIOl0Q/edit?usp=drive_link', 0, NULL),
(2, 'A ti el alfa y la omega', 'Marcos Witt', 'F', '', 110, 0, '', 'Medium', NULL, NULL, 0, NULL),
(3, 'Abres Caminos', 'Rojo', 'C', '', 129, 1, '', 'High', NULL, NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `band_roles`
--
ALTER TABLE `band_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_assignments`
--
ALTER TABLE `event_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_instrument_per_event` (`event_id`,`instrument`),
  ADD UNIQUE KEY `unique_assignment` (`event_id`,`instrument`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `event_confirmations`
--
ALTER TABLE `event_confirmations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conf` (`event_id`,`member_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `event_songs`
--
ALTER TABLE `event_songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `fk_requested_by` (`requested_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `band_roles`
--
ALTER TABLE `band_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `event_assignments`
--
ALTER TABLE `event_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `event_confirmations`
--
ALTER TABLE `event_confirmations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `event_songs`
--
ALTER TABLE `event_songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_assignments`
--
ALTER TABLE `event_assignments`
  ADD CONSTRAINT `event_assignments_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_assignments_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_confirmations`
--
ALTER TABLE `event_confirmations`
  ADD CONSTRAINT `event_confirmations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_confirmations_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_songs`
--
ALTER TABLE `event_songs`
  ADD CONSTRAINT `event_songs_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `event_songs_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`);

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `fk_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `members` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
