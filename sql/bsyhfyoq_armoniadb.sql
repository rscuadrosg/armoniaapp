-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 11, 2026 at 11:11 PM
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
(1, 'Voz Principal', 6),
(2, 'Piano / Teclados', 5),
(3, 'Bajo/Bass', 2),
(4, 'Batería/Drums', 1),
(6, 'Guitarra 2', 4),
(7, 'Coro 1', 7),
(8, 'Coro 2', 8),
(9, 'Guitarra 1', 3),
(10, 'Coro 3', 9);

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
(26, '', '2026-01-11 00:00:00', 'Servicio Dominical'),
(35, '', '2026-02-01 08:00:00', 'Servicio General Dom-01-feb-2026'),
(36, '', '2026-02-04 19:00:00', 'Servicio General Mie-04-feb-2026'),
(37, '', '2026-02-08 08:00:00', 'Servicio General Dom-08-feb-2026'),
(38, '', '2026-02-11 19:00:00', 'Servicio General Mie-11-feb-2026'),
(39, '', '2026-02-15 08:00:00', 'Servicio General Dom-15-feb-2026'),
(40, '', '2026-02-18 19:00:00', 'Servicio General Mie-18-feb-2026'),
(41, '', '2026-02-22 08:00:00', 'Servicio General Dom-22-feb-2026'),
(42, '', '2026-02-25 19:00:00', 'Servicio General Mie-25-feb-2026');

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
(11, 26, 2, 'Coro 1'),
(12, 26, 1, 'Voz Principal'),
(13, 26, 4, 'Batería/Drums'),
(14, 26, 3, 'Bajo/Bass'),
(15, 26, 6, 'Guitarra 1'),
(16, 26, 7, 'Guitarra 2'),
(17, 26, 8, 'Coro 2'),
(18, 35, 6, 'Guitarra 1'),
(19, 35, 7, 'Guitarra 2'),
(20, 36, 9, 'Guitarra 1'),
(21, 36, 7, 'Guitarra 2'),
(22, 37, 10, 'Guitarra 1'),
(23, 37, 6, 'Guitarra 2'),
(24, 38, 9, 'Guitarra 1'),
(25, 38, 7, 'Guitarra 2'),
(26, 39, 6, 'Guitarra 1'),
(27, 39, 9, 'Guitarra 2'),
(28, 40, 7, 'Guitarra 1'),
(29, 40, 9, 'Guitarra 2'),
(30, 41, 6, 'Guitarra 1'),
(31, 41, 7, 'Guitarra 2'),
(32, 42, 9, 'Guitarra 1'),
(33, 42, 7, 'Guitarra 2');

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
(5, 26, 1, 'confirmado', '2026-01-06 05:23:32'),
(6, 26, 2, 'rechazado', '2026-01-06 05:23:48'),
(7, 26, 7, 'confirmado', '2026-01-11 14:25:42'),
(8, 26, 6, 'confirmado', '2026-01-12 03:49:15');

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
(65, 35, 49, 1),
(66, 35, 69, 2),
(67, 35, 25, 3),
(68, 35, 152, 4),
(69, 35, 139, 5),
(70, 35, 83, 6),
(71, 36, 15, 1),
(72, 36, 40, 2),
(73, 36, 30, 3),
(74, 36, 70, 4),
(75, 36, 177, 5),
(76, 36, 39, 6),
(77, 37, 22, 1),
(78, 37, 64, 2),
(79, 37, 32, 3),
(80, 37, 93, 4),
(81, 37, 106, 5),
(82, 37, 91, 6),
(83, 38, 56, 1),
(84, 38, 7, 2),
(85, 38, 61, 3),
(86, 38, 188, 4),
(87, 38, 41, 5),
(88, 38, 113, 6),
(89, 39, 169, 1),
(90, 39, 16, 2),
(91, 39, 128, 3),
(92, 39, 48, 4),
(93, 39, 12, 5),
(94, 39, 178, 6),
(95, 40, 162, 1),
(96, 40, 180, 2),
(97, 40, 121, 3),
(98, 40, 58, 4),
(99, 40, 8, 5),
(100, 40, 114, 6),
(101, 41, 145, 1),
(102, 41, 52, 2),
(103, 41, 112, 3),
(104, 41, 84, 4),
(105, 41, 100, 5),
(106, 41, 20, 6),
(107, 42, 102, 1),
(108, 42, 173, 2),
(109, 42, 73, 3),
(110, 42, 147, 4),
(111, 42, 176, 5),
(112, 42, 88, 6),
(113, 26, 121, 2),
(114, 26, 175, 1),
(115, 26, 192, 3),
(116, 26, 110, 5),
(117, 26, 143, 4),
(118, 26, 156, 6);

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
  `role` enum('admin','musico','director','lider') DEFAULT 'musico',
  `leader_instrument` text DEFAULT NULL,
  `playable_instruments` text DEFAULT NULL,
  `available_days` varchar(50) DEFAULT '0,1,2,3,4,5,6',
  `max_services_per_month` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `full_name`, `email`, `password`, `member_type`, `is_available`, `profile_photo`, `role`, `leader_instrument`, `playable_instruments`, `available_days`, `max_services_per_month`) VALUES
(1, 'Richard Cuadros', 'richards.cuadros@outlook.com', '$2y$10$vfV3nUkUjo/VJ4VFY/24zOKbP4SqowKxbhzwqALmW3MjBR8MZCtz.', 'Internal', 1, 'profile_1_1766295708.jpg', 'admin', NULL, 'Batería/Drums', '0,1,2,3,4,5,6', 10),
(2, 'Leidy Martinez', 'lmartinez@test.com', '$2y$10$V25MziE.Mdfe4DgDk67N/.aEnotiBoOQnX1Nzex5xTgwFXzjF5YJu', 'Internal', 1, NULL, 'lider', 'Coro 1,Coro 2,Coro 3', 'Voz Principal,Coro 1,Coro 2,Coro 3', '0,1,2,3,4,5,6', 10),
(3, 'Diego Paez', 'diego.paez@test.com', NULL, 'Internal', 1, NULL, 'musico', NULL, 'Bajo/Bass,Coro 1,Coro 2,Coro 3', '0,1,2,3,4,5,6', 10),
(4, 'Juan David Martinez', 'juan.martinez@test.com', NULL, 'External', 1, NULL, 'musico', NULL, 'Batería/Drums', '0,1,2,3,4,5,6', 10),
(5, 'Andres Benavides', 'andres.benavides@test.com', NULL, 'External', 1, NULL, 'musico', NULL, 'Batería/Drums', '0,1,2,3,4,5,6', 10),
(6, 'Camilo Baena', 'camilo.baena@test.com', '$2y$10$majE.B7cr3C/lMS1jfa1uefNtvvjt2XBqVYaWEmEIIaH4u1J2lewG', 'Internal', 1, NULL, 'lider', 'Guitarra 1,Guitarra 2', 'Guitarra 1,Guitarra 2', '0,6', 4),
(7, 'Samuel Sandoval', 'samuel.sandoval@test.com', '$2y$10$nwUK94ooC8/yrIMCnbnune0q3Yvt3wu8j671yayE713RjXdrHiYJ2', 'Internal', 1, NULL, 'musico', NULL, 'Bajo/Bass,Guitarra 1,Guitarra 2', '0,1,2,3,4,5,6', 10),
(8, 'Ivonne Giraldo', 'ivonne.giraldo@test.com', '$2y$10$jncNfaGTu6nkgo/eZHdERO1HI4F5bVshFCr0yRztVpcxk53kwSe5a', 'Internal', 1, NULL, 'musico', NULL, 'Coro 1,Coro 2,Coro 3', '0,1,2,3,4,5,6', 10),
(9, 'Sebastian Prada', 'sebas.prada@test.com', '$2y$10$z5F7e3lOQJ1x3X4jATRZ9.qGS5wFbyWbHYknqz2/P5XFZN8UjeGgG', 'Internal', 1, NULL, 'musico', NULL, 'Guitarra 1,Guitarra 2', '0,1,2,3,4,5,6', 10),
(10, 'Camilo Paez', 'camilo.paez@test.com', '$2y$10$uPzN.D/McpSunsTDbelWbu7WeiqBe/ZAEi2tb1ja.po9voiUQxNJy', 'Internal', 1, NULL, 'musico', NULL, 'Guitarra 1,Guitarra 2', '0', 1);

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
(3, 'Abres Caminos', 'Rojo', 'C', '', 129, 1, '', 'High', NULL, NULL, 0, NULL),
(4, 'Deseable', 'Marcos Brunet', 'G♯', '', 120, 1, '', 'Medium', NULL, NULL, 0, NULL),
(5, 'Al estar ante ti', 'Jesus Adrian Romero', 'D', '', 144, 1, '', 'Medium', NULL, NULL, 0, NULL),
(6, 'Al Estar Aqui', 'Marcos Witt', 'D', '', 110, 0, '', 'Medium', NULL, NULL, 0, NULL),
(7, 'Al que está sentado en el trono', 'Marcos Brunet', 'A', '', 65, 1, '', 'Medium', NULL, NULL, 0, NULL),
(8, 'Alabemos', 'Marcos Witt', 'C', '', 134, 1, '', 'Medium', NULL, NULL, 0, NULL),
(9, 'Digno', 'Marcos Brunet', 'D', '', 120, 1, '', 'Medium', NULL, NULL, 0, NULL),
(10, 'Aleluya (Agnus Dei)', 'Marco Barrientos', 'A', '', 146, 0, '', 'Medium', NULL, NULL, 0, NULL),
(11, 'Cambiare mi Tristeza', 'Vertical', 'A', '', 120, 1, '', 'Medium', NULL, NULL, 0, NULL),
(12, 'Canto Danzo', 'CFA Music', 'Dbm', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(13, 'Clama', 'Kike Pavon', 'Db', '', 104, 1, '', 'Medium', NULL, NULL, 0, NULL),
(14, 'Como Dijiste', 'Christine D\'Clario', 'Gb', '', 147, 1, '', 'Medium', NULL, NULL, 0, NULL),
(15, 'Como el Ciervo (llename)', 'Marcos Witt', 'G', '', 105, 0, '', 'Medium', NULL, NULL, 0, NULL),
(16, 'Como la Brisa', 'Jesus Adrian Romero', 'A', '', 132, 1, '', 'Medium', NULL, NULL, 0, NULL),
(17, 'Con solo un toque', 'Su Presencia', 'F', '', 76, 1, '', 'Medium', NULL, NULL, 0, NULL),
(18, 'Cordero y Leon', 'New Wine', 'F', '', 138, 1, '', 'Medium', NULL, NULL, 0, NULL),
(19, 'Creo en Ti', 'Julio Melgar', 'Am', '', 60, 0, '', 'Medium', NULL, NULL, 0, NULL),
(20, 'Cristo Tu', 'Diego Rossi', 'A', '', 174, 1, '', 'Medium', NULL, NULL, 0, NULL),
(21, 'Cuan Bello es el Señor', 'Marcos Witt', 'G', '', 60, 0, '', 'Medium', NULL, NULL, 0, NULL),
(22, 'Cuan Grande es El', 'Varios', 'G', '', 99, 1, '', 'Medium', NULL, NULL, 0, NULL),
(23, 'Dame de beber', 'Marco Barrientos', 'D', '', 114, 1, '', 'Medium', NULL, NULL, 0, NULL),
(24, 'Dios es mi Papa', 'Marcos Brunet', 'B', '', 120, 1, '', 'Medium', NULL, NULL, 0, NULL),
(25, 'Espiritu Ven', 'Generacion12', 'Abm', '', 156, 1, '', 'Medium', NULL, NULL, 0, NULL),
(26, 'Inundanos', 'Kabed', 'F', '', 136, 1, '', 'Medium', NULL, NULL, 0, NULL),
(27, 'De Tal Manera', 'Abel zabala', 'C', '', 105, 1, '', 'Medium', NULL, NULL, 0, NULL),
(28, 'Derrama de Tu fuego', 'Marcos Witt', 'Cm', '', 105, 1, '', 'Medium', NULL, NULL, 0, NULL),
(29, 'Jesucristo Basta', 'Un Corazon', 'Ab', '', 138, 1, '', 'Medium', NULL, NULL, 0, NULL),
(30, 'La Tierra canta', 'Barak', 'D', '', 72, 1, '', 'Medium', NULL, NULL, 0, NULL),
(31, 'Digno y Santo', 'Danilo Montero', 'E', '', 120, 0, '', 'Medium', NULL, NULL, 0, NULL),
(32, 'Libre', 'Miel San Marcos', 'C', '', 156, 1, '', 'Medium', NULL, NULL, 0, NULL),
(33, 'Dios incomparable', 'Generacion12', 'B', '', 78, 1, '', 'Medium', NULL, NULL, 0, NULL),
(34, 'Dios Me Ama', 'Thalles Roberto', 'A', '', 74, 1, '', 'Medium', NULL, NULL, 0, NULL),
(35, 'El himno', 'Planetshakers', 'Db', '', 76, 1, '', 'Medium', NULL, NULL, 0, NULL),
(36, 'El Poderoso de Israel', 'Marco Barrientos', 'Bm', '', 160, 0, '', 'Medium', NULL, NULL, 0, NULL),
(37, 'No Hay Lugar mas Alto', 'Miel San Marcos Ft. Christine D\'clario', 'A', '', 68, 1, '', 'Medium', NULL, NULL, 0, NULL),
(38, 'El señor Esta en este lugar', 'Varios', 'G', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(39, 'En los Montes', 'Marcos Witt', 'Dm', '', 107, 1, '', 'Medium', NULL, NULL, 0, NULL),
(40, 'Enamorame', 'Abel zabala', 'G', '', 60, 0, '', 'Medium', NULL, NULL, 0, NULL),
(41, 'Eres Digno de Gloria', 'Gery Marquez', 'C', '', 125, 1, '', 'Medium', NULL, NULL, 0, NULL),
(42, 'Que se Abra el Cielo', 'Christine D\'Clario', 'Bb', '', 0, 1, '', 'Medium', NULL, NULL, 0, NULL),
(43, 'Eres Todopoderoso', 'Rojo', 'Bm', '', 135, 1, '', 'Medium', NULL, NULL, 0, NULL),
(44, 'Escucharte Hablar', 'Marcos Witt', 'G', '', 105, 1, '', 'Medium', NULL, NULL, 0, NULL),
(45, 'Quiero Mirar tu Hermosura', 'Marco Barrientos', 'D', '', 65, 1, '', 'Medium', NULL, NULL, 0, NULL),
(46, 'Estoy Asombrado', 'Planetshakers', 'D', '', 77, 1, '', 'Medium', NULL, NULL, 0, NULL),
(47, 'Exaltate (al borde de tu gran trono)', 'Marcos Witt', 'G', '', 60, 0, '', 'Medium', NULL, NULL, 0, NULL),
(48, 'Fiesta - (Su presencia)', 'Su Presencia', 'Bbm', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(49, 'Solo en Ti', 'Seth Condrey', 'A', '', 142, 1, '', 'Medium', NULL, NULL, 0, NULL),
(50, 'Fuego', 'Su Presencia', 'D', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(51, 'Gracias', 'Marcos Witt', 'Dm', '', 60, 0, '', 'Medium', NULL, NULL, 0, NULL),
(52, 'Te Quiero Adorar', 'Barak', 'E', '', 73, 1, '', 'Medium', NULL, NULL, 0, NULL),
(53, 'Tu amor no tiene Fin', 'Generacion12', 'E', '', 136, 1, '', 'Medium', NULL, NULL, 0, NULL),
(54, 'Hay Libertad', 'Art Aguilera', 'F', '', 148, 1, '', 'Medium', NULL, NULL, 0, NULL),
(55, 'Hay Momentos', 'Marcos Witt', 'G', '', 115, 1, '', 'Medium', NULL, NULL, 0, NULL),
(56, 'Hermoso Eres', 'Marcos Witt', 'G', '', 120, 1, '', 'Medium', NULL, NULL, 0, NULL),
(57, 'Hossana', 'Hillsong', 'E', '', 72, 1, '', 'Medium', NULL, NULL, 0, NULL),
(58, 'Hossana Remix (Alabanza)', 'Marco Barrientos', 'D', '', 148, 1, '', 'Medium', NULL, NULL, 0, NULL),
(59, 'Hoy Me rindo', 'Hillsong', 'Dm', '', 154, 1, '', 'Medium', NULL, NULL, 0, NULL),
(60, 'Increible', 'Miel San Marcos', 'C', '', 132, 1, '', 'Medium', NULL, NULL, 0, NULL),
(61, 'Tu Eres Rey', 'Barak Ft. Christine D\'Clario', 'E', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(62, 'Ven Espíritu Santo', 'Barak', 'D#', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(63, 'Ya no soy Esclavo', 'Christine D\'Clario', 'Bb', '', 148, 1, '', 'Medium', NULL, NULL, 0, NULL),
(64, 'La niña de Tus Ojos', 'Abel zabala', 'F', '', 136, 1, '', 'Medium', NULL, NULL, 0, NULL),
(65, 'Preciosa Sangre', 'Marco Barrientos', 'A', '', 70, 1, '', 'Medium', NULL, NULL, 0, NULL),
(66, 'La Nube De Jehova', 'Varios', 'C', '', 100, 1, '', 'Medium', NULL, NULL, 0, NULL),
(67, 'Agradecido', 'Miel San Marcos', 'Eb', '', 146, 1, '', 'Medium', NULL, NULL, 0, NULL),
(68, 'Alegria', 'Miel San Marcos', 'Eb', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(69, 'Levanto mis Manos', 'Jaime Murrel', 'D', '', 105, 0, '', 'Medium', NULL, NULL, 0, NULL),
(70, 'Danzar', 'Barak', 'Cm', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(71, 'Los Muros Caerán', 'Miel San Marcos', 'Cm', '', 153, 1, '', 'Medium', NULL, NULL, 0, NULL),
(72, 'Danzo en el Rio', 'Miel San Marcos', 'D', '', 135, 1, '', 'Medium', NULL, NULL, 0, NULL),
(73, 'Me vuelvo a Ti', 'Generacion12', 'D', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(74, 'Medley - voy a perder la compostura - Sube Sube Sube', 'ICR', 'Am', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(75, 'Mientras Viva', 'Generacion12', 'D', '', 120, 1, '', 'Medium', NULL, NULL, 0, NULL),
(76, 'De Gloria en Gloria', 'Marco Barrientos', 'Bb', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(77, 'Nada es imposible', 'Planetshakers', 'C', '', 142, 1, '', 'Medium', NULL, NULL, 0, NULL),
(78, 'El Poderoso de Israel/ Eres Todopoderoso/Cuando pienso', 'Miel San Marcos', 'Eb', '', 80, 1, '', 'Medium', NULL, NULL, 0, NULL),
(79, 'Eres Señor Vencedor', 'Juan Carlos Alvarado', 'Am', '', 155, 1, '', 'Medium', NULL, NULL, 0, NULL),
(80, 'No hay Nadie como Tu', 'Ivonne Muñoz', 'G', '', 60, 1, '', 'Medium', NULL, NULL, 0, NULL),
(81, 'Pacientemente (Mi Fortaleza)', 'Freddy Rodriguez', 'Am', '', 60, 0, '', 'Medium', NULL, NULL, 0, NULL),
(82, 'Poderoso Dios', 'Marcos Witt', 'G', '', 120, 1, '', 'Medium', NULL, NULL, 0, NULL),
(83, 'Por Siempre te alabare', 'Planetshakers', 'D', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(84, 'Fiesta - MSM', 'Miel San Marcos', 'F', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(85, 'Que seria de Mi', 'Marcos Witt', 'D', '', 60, 0, '', 'Medium', NULL, NULL, 0, NULL),
(86, 'Quiero Llenar', 'Marcos Witt', 'C', '', 60, 0, '', 'Medium', NULL, NULL, 0, NULL),
(87, 'Grande y Fuerte', 'Miel San Marcos', 'C', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(88, 'Remolineando', 'Fernel Monroy', 'Bm', '', 160, 1, '', 'Medium', NULL, NULL, 0, NULL),
(89, 'Renuevame', 'Marcos Witt', 'D', '', 60, 1, '', 'Medium', NULL, NULL, 0, NULL),
(90, 'Rey De Reyes', 'Marco Barrientos', 'D', '', 147, 1, '', 'Medium', NULL, NULL, 0, NULL),
(91, 'Señor eres Fiel', 'Marco Barrientos', 'D', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(92, 'Señor Llevame a tus Atrios', 'Varios', 'G', '', 120, 1, '', 'Medium', NULL, NULL, 0, NULL),
(93, 'Grita, Canta, Danza', 'Ebenezer', 'Cm', '', 156, 1, '', 'Medium', NULL, NULL, 0, NULL),
(94, 'Impulso', 'Evan Craft', 'A', '', 96, 1, '', 'Medium', NULL, NULL, 0, NULL),
(95, 'Solo Cristo', 'Hillsong', 'B', '', 141, 1, '', 'Medium', NULL, NULL, 0, NULL),
(96, 'Invencible - Bautizame', 'Miel San Marcos', 'F', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(97, 'Le Llaman Guerrero - Jehova es mi Guerrero - Eres Señor Vencedor', 'Juan Carlos Alvarado', 'Am', '', 158, 1, '', 'Medium', NULL, NULL, 0, NULL),
(98, 'Me Gozare', 'Ebenezer', 'Am', '', 160, 1, '', 'Medium', NULL, NULL, 0, NULL),
(99, 'Sumergeme', 'Jesús Adrian Romero', 'C', '', 100, 1, '', 'Medium', NULL, NULL, 0, NULL),
(100, 'Te Doy Gloria', 'Marco Barrientos', 'C', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(101, 'Tengo Hambre de Ti', 'Jesús Adrian Romero', 'G', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(102, 'Toda Nacion', 'Su Presencia', 'E', '', 146, 1, '', 'Medium', NULL, NULL, 0, NULL),
(103, 'Levantando las Manos', 'Planetshakers', 'C#', '', 122, 1, '', 'Medium', NULL, NULL, 0, NULL),
(104, 'Mira, Asi Se Alaba A Cristo', 'La Reforma', 'C', '', 105, 0, '', 'Medium', NULL, NULL, 0, NULL),
(105, 'No Callare', 'Miel San Marcos', 'C', '', 156, 1, '', 'Medium', NULL, NULL, 0, NULL),
(106, 'Sobrenatural Merengue', 'New Wine', 'Am', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(107, 'Tu Fidelidad', 'Marcos Witt', 'C', '', 55, 0, '', 'Medium', NULL, NULL, 0, NULL),
(108, 'Tu habitas', 'Marcos Witt', 'G', '', 160, 0, '', 'Medium', NULL, NULL, 0, NULL),
(109, 'Tu presencia es el cielo', 'Israel Houghton', 'Bm', '', 132, 1, '', 'Medium', NULL, NULL, 0, NULL),
(110, 'Ven Aqui', 'Planetshakers', 'Ab', 'https://www.youtube.com/watch?v=9cjXEKYnXQ0', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(111, 'Soy Feliz', 'Miel San Marcos', 'C', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(112, 'Vengo a Adorarte', 'Hillsong', 'E', '', 135, 1, '', 'Medium', NULL, NULL, 0, NULL),
(113, 'Sube, Sube, Sube', 'Ebenezer', 'Cm', '', 156, 0, '', 'Medium', NULL, NULL, 0, NULL),
(114, 'Vivo Estas', 'Hillsong', 'C#m', '', 132, 1, '', 'Medium', NULL, NULL, 0, NULL),
(115, 'Viento Recio', 'Miel San Marcos', 'Bm', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(116, 'New Levels', 'Planetshakers', 'A', '', 145, 1, '', 'Medium', NULL, NULL, 0, NULL),
(117, 'Voy a perder la Compostura', 'Billy Bunster', 'Cm', '', 156, 1, '', 'Medium', NULL, NULL, 0, NULL),
(118, 'Yo quiero mas de ti (Menguar)', 'Jaime Murrel', 'G', '', 60, 1, '', 'Medium', NULL, NULL, 0, NULL),
(119, 'Yo vencere', 'Miel San Marcos', 'Cm', '', 160, 0, '', 'Medium', NULL, NULL, 0, NULL),
(120, 'Un Encuentro Sobrenatural', 'New Wine', 'Dm', '', 139, 1, '', 'Medium', NULL, NULL, 0, NULL),
(121, 'Lo haras Otra vez', 'Elevation Whorship', 'Bb', 'https://www.youtube.com/watch?v=F6cGUa-owhA', 86, 1, '', 'Medium', NULL, NULL, 0, NULL),
(122, 'Canto , Danzo, Salto - Yo vencere', 'Miel San Marcos', 'Cm', '', 155, 1, '', 'Medium', NULL, NULL, 0, NULL),
(123, 'Profetizar!', 'Planetshakers', 'Cm', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(124, 'Gozo', 'Miel San Marcos', 'D', '', 132, 1, '', 'Medium', NULL, NULL, 0, NULL),
(125, 'Alegras Mis dias', 'Su Presencia', 'B', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(126, 'obra en mi', 'Barak', 'C#m', '', 146, 1, '', 'Medium', NULL, NULL, 0, NULL),
(127, 'Yeshua', 'Image Whorship', 'F', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(128, 'Digno', 'Elevation Whorship', 'Eb', '', 67, 1, '', 'Medium', NULL, NULL, 0, NULL),
(129, 'Rey Vencedor/Fiesta/Viene ya', 'Miel San Marcos', 'C', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(130, 'Mega fe', 'New Wine', 'C', '', 155, 1, '', 'Medium', NULL, NULL, 0, NULL),
(131, 'Santo es el que vive', 'Montesanto', 'Ebm', '', 64, 1, '', 'Medium', NULL, NULL, 0, NULL),
(132, 'Solo Jamas Caminare', 'Hillsong', 'A', '', 72, 1, '', 'Medium', NULL, NULL, 0, NULL),
(133, 'Dios de lo imposible', 'Marco Barrientos', 'C#', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(134, 'Hay poder', 'Generacion12', 'B', '', 156, 1, '', 'Medium', NULL, NULL, 0, NULL),
(135, 'Lugar secreto', 'Gabriela Rocha/christine d´clario', 'C', '', 69, 1, '', 'Medium', NULL, NULL, 0, NULL),
(136, 'Todo lo haces nuevo', 'G12/Christine D´Clario', 'E', '', 75, 1, '', 'Medium', NULL, NULL, 0, NULL),
(137, 'Te Deseo', 'Majo y Dan', 'E', '', 144, 1, '', 'Medium', NULL, NULL, 0, NULL),
(138, 'Probar y Verte', 'Majo y Dan', 'E', '', 144, 1, '', 'Medium', NULL, NULL, 0, NULL),
(139, 'en el principio/pon aceite', 'Ebenezer', 'E', '', 156, 1, '', 'Medium', NULL, NULL, 0, NULL),
(140, 'Trae Tu Presencia', 'Pedro Gomez - Ft Barak', 'F#', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(141, 'Danzamos en tu atmosfera', 'Barak', 'Am', '', 145, 1, '', 'Medium', NULL, NULL, 0, NULL),
(142, 'Tu Nombre es Cristo', 'Marcos Witt', 'Dm', '', 116, 1, '', 'Medium', NULL, NULL, 0, NULL),
(143, 'Desciende', 'Miel San Marcos', 'D', 'https://www.youtube.com/watch?v=9OFpLxamcxk', 132, 1, '', 'Medium', NULL, NULL, 0, NULL),
(144, 'Fiesta en el desierto', 'Montesanto', 'E', '', 125, 1, '', 'Medium', NULL, NULL, 0, NULL),
(145, 'Derramo el perfume', 'Montesanto', 'Bb', '', 130, 1, '', 'Medium', NULL, NULL, 0, NULL),
(146, 'Yo Navegare/Espiritu de Dios', 'Jotta A', 'Dm', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(147, 'Goliat', 'Su Presencia', 'E', '', 135, 1, '', 'Medium', NULL, NULL, 0, NULL),
(148, 'Tomalo', 'Hillsong', 'B', '', 150, 1, '', 'Medium', NULL, NULL, 0, NULL),
(149, 'El Gran Yo Soy', 'Erick Porta', 'B', '', 148, 1, '', 'Medium', NULL, NULL, 0, NULL),
(150, 'Salmo 91', 'Su Presencia', 'Db', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(151, 'No hay Santo como el Señor', 'Erick Porta', 'A', '', 156, 1, '', 'Medium', NULL, NULL, 0, NULL),
(152, 'El Poderoso esta del lado de Nosotros', 'Erick Porta', 'Bm', '', 154, 1, '', 'Medium', NULL, NULL, 0, NULL),
(153, 'Tu puedes levantarte', 'Erick Porta', 'Eb', '', 154, 1, '', 'Medium', NULL, NULL, 0, NULL),
(154, 'Hay Libertad', 'Erick Porta', 'Bb', '', 158, 1, '', 'Medium', NULL, NULL, 0, NULL),
(155, 'Incomparable Dios', 'Erick Porta', 'Bb', '', 156, 1, '', 'Medium', NULL, NULL, 0, NULL),
(156, 'Pacientemente espere en Jehova', 'Erick Porta', 'Em', 'https://www.youtube.com/watch?v=LZcMsUy7fE0', 154, 1, '', 'Medium', NULL, NULL, 0, NULL),
(157, 'Danzando', 'Gateway worship ft CDC', 'D', '', 95, 1, '', 'Medium', NULL, NULL, 0, NULL),
(158, 'La sunamita', 'Montesanto', 'Ab', '', 66, 1, '', 'Medium', NULL, NULL, 0, NULL),
(159, 'Sucedera', 'Grupo Grace', 'Ab', '', 130, 1, '', 'Medium', NULL, NULL, 0, NULL),
(160, 'Sin Limites', 'Planetshakers', 'Em', '', 134, 1, '', 'Medium', NULL, NULL, 0, NULL),
(161, 'YAWHE - Se Manifestara', 'Oasis Ministry', 'Em', '', 139, 1, '', 'Medium', NULL, NULL, 0, NULL),
(162, 'Deseo Eterno', 'Yvonne Muñoz', 'E', '', 125, 1, '', 'Medium', NULL, NULL, 0, NULL),
(163, 'Quien es como tu', 'Marco Barrientos', 'Bm', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(164, 'En tu Presencia', 'Hillsong', 'A', '', 135, 1, '', 'Medium', NULL, NULL, 0, NULL),
(165, 'Vamos a Vencer', 'Erick Porta', '', '', 0, 0, '', 'Medium', NULL, NULL, 0, NULL),
(166, 'Conocerte Mas', 'Living', 'G', '', 77, 1, '', 'Medium', NULL, NULL, 0, NULL),
(167, 'Amor como Fuego', 'Hillsong', 'E', '', 162, 1, '', 'Medium', NULL, NULL, 0, NULL),
(168, 'Queremos Fuego', 'Jesus Worship center', 'C', '', 132, 1, '', 'Medium', NULL, NULL, 0, NULL),
(169, 'Inagotable', 'Living', 'Db', '', 168, 1, '', 'Medium', NULL, NULL, 0, NULL),
(171, 'Esto Suena bien', 'Redimi2, Alex Zurdo, Oveja Cosmica', 'Bm', '', 105, 1, '', 'Medium', NULL, NULL, 0, NULL),
(172, 'Sin Ti', 'Alex Zurdo', 'C#m', '', 91, 1, '', 'Medium', NULL, NULL, 0, NULL),
(173, 'Desde mi interior', 'Hillsong', 'E', '', 69, 1, '', 'Medium', NULL, NULL, 0, NULL),
(174, 'Necesito tu Amor', 'Hillsong Y&F', 'C', '', 126, 1, '', 'Medium', NULL, NULL, 0, NULL),
(175, 'Exaltado Estas', 'Miel san Marcos', 'C', 'https://www.youtube.com/watch?v=J6bKInA0MAs', 75, 1, '', 'Medium', NULL, NULL, 0, NULL),
(176, 'Vivo Danzando', 'Bani Muñoz', 'D', '', 132, 1, '', 'Medium', NULL, NULL, 0, NULL),
(177, 'Solo hay Uno', 'Miel San Marcos', 'C', '', 128, 1, '', 'Medium', NULL, NULL, 0, NULL),
(178, 'Hay poder en la Alabanza', 'Jesus Worship center', 'Dm', '', 123, 1, '', 'Medium', NULL, NULL, 0, NULL),
(179, 'La casa de Dios', 'Danilo Montero', 'D', '', 140, 1, '', 'Medium', NULL, NULL, 0, NULL),
(180, 'Uncion en el aire', 'World Worship - Cales Louma', 'Cm', '', 70, 1, '', 'Medium', NULL, NULL, 0, NULL),
(181, 'Grande es el señor - Cantare al señor por siempre', 'Apostolic Assembly', 'C#m', '', 145, 1, '', 'Medium', NULL, NULL, 0, NULL),
(182, 'Santo por siempre', 'La IBI', 'G', '', 70, 1, '', 'Medium', NULL, NULL, 0, NULL),
(183, 'Rey Glorioso', 'MSM', 'D', '', 65, 1, '', 'Medium', NULL, NULL, 0, NULL),
(184, 'Preciosa Sangre', 'Marco Barrientos', 'A', '', 70, 1, '', 'Medium', NULL, NULL, 0, NULL),
(185, 'melodia/lavoz de mi amado/libertad', 'CCINT', 'Am', '', 80, 1, '', 'Medium', NULL, NULL, 0, NULL),
(186, 'como no voy a creer', 'Brandon Lake Ft Bethel', 'C', '', 72, 1, '', 'Medium', NULL, NULL, 0, NULL),
(187, 'Coritos', 'Miel San Marcos', 'G', '', 115, 1, '', 'Medium', NULL, NULL, 0, NULL),
(188, 'Jubilo', 'Miel San Marcos', 'D', '', 135, 1, '', 'Medium', NULL, NULL, 0, NULL),
(189, 'Que ruja el Leon', 'FHOP', 'F#m', '', 69, 1, '', 'Medium', NULL, NULL, 0, NULL),
(190, 'La bondad de Dios', 'Chuch of the city', 'G', '', 68, 1, '', 'Medium', NULL, NULL, 0, NULL),
(191, 'Como david', 'Montesanto', 'Am', '', 134, 1, '', 'Medium', NULL, NULL, 0, NULL),
(192, 'Nuestro Dios', 'Montesanto', 'Em', 'https://www.youtube.com/watch?v=5kfa0Yl2i50', 144, 1, '', 'Medium', NULL, NULL, 0, NULL),
(193, 'Yo soy la ofrenda', 'Montesanto', '', '', 0, 0, '', 'Medium', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `song_tags`
--

CREATE TABLE `song_tags` (
  `song_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `song_tags`
--

INSERT INTO `song_tags` (`song_id`, `tag_id`) VALUES
(3, 1),
(2, 2),
(1, 3),
(1, 4),
(4, 4),
(5, 4),
(6, 4),
(7, 4),
(9, 4),
(10, 4),
(14, 4),
(15, 4),
(16, 4),
(17, 4),
(18, 4),
(19, 4),
(21, 4),
(22, 4),
(23, 4),
(24, 4),
(25, 4),
(26, 4),
(27, 4),
(29, 4),
(30, 4),
(31, 4),
(32, 4),
(33, 4),
(34, 4),
(35, 4),
(37, 4),
(40, 4),
(42, 4),
(44, 4),
(45, 4),
(46, 4),
(47, 4),
(49, 4),
(51, 4),
(52, 4),
(53, 4),
(55, 4),
(56, 4),
(57, 4),
(59, 4),
(61, 4),
(62, 4),
(63, 4),
(64, 4),
(66, 4),
(69, 4),
(73, 4),
(80, 4),
(81, 4),
(82, 4),
(85, 4),
(86, 4),
(89, 4),
(92, 4),
(95, 4),
(99, 4),
(101, 4),
(102, 4),
(107, 4),
(109, 4),
(112, 4),
(118, 4),
(120, 4),
(121, 4),
(126, 4),
(127, 4),
(128, 4),
(131, 4),
(132, 4),
(133, 4),
(134, 4),
(135, 4),
(136, 4),
(137, 4),
(138, 4),
(140, 4),
(145, 4),
(146, 4),
(158, 4),
(159, 4),
(161, 4),
(162, 4),
(167, 4),
(169, 4),
(173, 4),
(175, 4),
(180, 4),
(182, 4),
(183, 4),
(186, 4),
(189, 4),
(190, 4),
(192, 4),
(193, 4),
(8, 5),
(11, 5),
(12, 5),
(13, 5),
(20, 5),
(28, 5),
(36, 5),
(38, 5),
(39, 5),
(41, 5),
(43, 5),
(48, 5),
(50, 5),
(54, 5),
(58, 5),
(60, 5),
(67, 5),
(68, 5),
(70, 5),
(71, 5),
(72, 5),
(74, 5),
(75, 5),
(76, 5),
(77, 5),
(78, 5),
(79, 5),
(83, 5),
(84, 5),
(87, 5),
(88, 5),
(90, 5),
(91, 5),
(93, 5),
(96, 5),
(97, 5),
(98, 5),
(100, 5),
(105, 5),
(106, 5),
(108, 5),
(110, 5),
(111, 5),
(113, 5),
(114, 5),
(115, 5),
(117, 5),
(119, 5),
(122, 5),
(124, 5),
(125, 5),
(129, 5),
(130, 5),
(139, 5),
(141, 5),
(142, 5),
(143, 5),
(144, 5),
(147, 5),
(148, 5),
(149, 5),
(151, 5),
(152, 5),
(153, 5),
(154, 5),
(155, 5),
(156, 5),
(157, 5),
(163, 5),
(168, 5),
(176, 5),
(177, 5),
(178, 5),
(179, 5),
(181, 5),
(187, 5),
(188, 5),
(191, 5),
(94, 7),
(103, 7),
(104, 7),
(116, 7),
(123, 7),
(150, 7),
(160, 7),
(171, 7),
(172, 7),
(174, 7),
(65, 8),
(164, 8),
(165, 8),
(166, 8),
(184, 8),
(185, 8);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color_class` varchar(100) DEFAULT 'bg-slate-100 text-slate-600'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `color_class`) VALUES
(1, 'Alta', 'bg-red-100 text-red-600 border-red-200'),
(2, 'Media', 'bg-orange-100 text-orange-600 border-orange-200'),
(3, 'Baja', 'bg-blue-100 text-blue-600 border-blue-200'),
(4, 'Adoración', 'bg-violet-100 text-violet-600 border-violet-200'),
(5, 'Alabanza', 'bg-emerald-100 text-emerald-600 border-emerald-200'),
(6, 'Santa Cena', 'bg-blue-100 text-blue-600 border-blue-200'),
(7, 'Ocasional', 'bg-slate-100 text-slate-600 border-slate-200'),
(8, 'Por montar', 'bg-red-100 text-red-600 border-red-200');

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
-- Indexes for table `song_tags`
--
ALTER TABLE `song_tags`
  ADD PRIMARY KEY (`song_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `band_roles`
--
ALTER TABLE `band_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `event_assignments`
--
ALTER TABLE `event_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `event_confirmations`
--
ALTER TABLE `event_confirmations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `event_songs`
--
ALTER TABLE `event_songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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

--
-- Constraints for table `song_tags`
--
ALTER TABLE `song_tags`
  ADD CONSTRAINT `song_tags_ibfk_1` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `song_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
