-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 07, 2025 at 04:37 AM
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
-- Database: `easyTrip2`
--

-- --------------------------------------------------------

--
-- Table structure for table `agency`
--

CREATE TABLE `agency` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agency`
--

INSERT INTO `agency` (`id`, `name`, `address`, `phone`, `email`, `image`) VALUES
(1, 'Agence1', 'tounes', '55448878', 'aminesouissi681@gmail.com', 'file:/home/cardinal/Downloads/defaultPic.jpg'),
(2, 'Agence2', 'asds', '25649878', 'aminesouissi682@gmail.com', 'file:/home/cardinal/Downloads/ticket.jpg'),
(3, 'agence3', 'ariana,tunis', '25664987', 'oussamabani14@gmail.com', 'file:/home/cardinal/Downloads/istockphoto-119926339-612x612.jpg'),
(4, 'f', 'f', '12345678', 'youssefcarma@gmail.com', 'file:/home/cardinal/Downloads/hotel2.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `model` varchar(255) NOT NULL,
  `seats` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `price_per_day` float NOT NULL,
  `image` varchar(255) NOT NULL,
  `availability` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `model`, `seats`, `location`, `price_per_day`, `image`, `availability`) VALUES
(15, 'golf8', 5, 'italy', 10, 'C:\\Users\\HP\\Downloads\\JDBC-main\\JDBC-main\\src\\main\\resources\\images\\Golf_GTE-1.png', 'AVAILABLE'),
(17, 'golf4', 1, 'MgMaxiAriana', 17, 'C:\\Users\\HP\\Downloads\\JDBC-main\\JDBC-main\\src\\main\\resources\\images\\252841.png', 'AVAILABLE'),
(21, 'BMW serie 8', 2, 'Monaco', 140, 'C:\\Users\\HP\\Downloads\\JDBC-main\\JDBC-main\\src\\main\\resources\\images\\bmw-8series-coupe-modellfinder.png', 'AVAILABLE'),
(22, 'toktok', 3, 'India', 5, 'C:\\Users\\HP\\Downloads\\JDBC-main\\JDBC-main\\src\\main\\resources\\images\\toktok.png', 'AVAILABLE'),
(23, 'Bugati Shiron', 2, 'DubaiMall', 2100, 'C:\\Users\\HP\\Downloads\\JDBC-main\\JDBC-main\\src\\main\\resources\\images\\Bugatti-Chiron-Car.png', 'AVAILABLE');

-- --------------------------------------------------------

--
-- Table structure for table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250407015828', '2025-04-07 02:00:00', 0),
('DoctrineMigrations\\Version20250407021524', '2025-04-07 02:00:00', 0),
('DoctrineMigrations\\Version20250407023443', '2025-04-07 02:34:56', 33),
('DoctrineMigrations\\Version20250407033000', '2025-04-07 02:02:44', 46),
('DoctrineMigrations\\Version20250407033500', '2025-04-07 02:04:50', 40),
('DoctrineMigrations\\Version20250407040000', '2025-04-07 02:16:53', 637);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `message` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `offer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `rating`, `message`, `date`, `offer_id`) VALUES
(41, 1, 'asdasd', '2025-03-05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id_hotel` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL,
  `description` longtext NOT NULL,
  `price` double NOT NULL,
  `type_room` varchar(255) NOT NULL,
  `num_room` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id_hotel`, `name`, `adresse`, `city`, `rating`, `description`, `price`, `type_room`, `num_room`, `image`, `promotion_id`, `agency_id`) VALUES
(1, 'Hotel Paradise', '123 Beach Road', 'Miami', 5, 'A luxurious beachside hotel with excellent amenities.', 150, 'Double', 50, 'http://localhost/img/hotel.jpg', 2, 1),
(2, 'Hotel Paradise', '123 Beach Road', 'Miami', 5, 'A luxurious beachside hotel with excellent amenities.', 150, 'Simple', 50, 'http://localhost/img/hotel2.jpg', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` varchar(255) NOT NULL,
  `available_at` varchar(255) NOT NULL,
  `delivered_at` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offer_travel`
--

CREATE TABLE `offer_travel` (
  `id` int(11) NOT NULL,
  `departure` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `departure_date` date NOT NULL,
  `arrival_date` date NOT NULL,
  `hotel_name` varchar(50) NOT NULL,
  `discription` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `image` varchar(255) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `flight_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offer_travel`
--

INSERT INTO `offer_travel` (`id`, `departure`, `destination`, `departure_date`, `arrival_date`, `hotel_name`, `discription`, `category`, `price`, `image`, `agency_id`, `promotion_id`, `flight_name`) VALUES
(5, 'Tunis', 'Maroc', '2025-02-05', '2025-02-26', 'Robeca', 'belle description', 'TOURISTIQUE', 130, 'C:\\Users\\user\\Pictures\\Screenshots\\Capture d\'écran 2025-01-12 224849.png', 1, 7, ''),
(12, 'Tunisia', 'Istanbul', '2025-03-08', '2025-03-09', 'Regency', 'tunisia', 'SPORTIVE', 76.5, 'C:\\xampp\\htdocs\\img\\téléchargement.jpeg', 1, 2, '');

-- --------------------------------------------------------

--
-- Table structure for table `Option`
--

CREATE TABLE `Option` (
  `id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `option_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Panier`
--

CREATE TABLE `Panier` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `coupon_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotion`
--

CREATE TABLE `promotion` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `discount_percentage` double NOT NULL,
  `valid_until` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotion`
--

INSERT INTO `promotion` (`id`, `title`, `description`, `discount_percentage`, `valid_until`) VALUES
(1, 'Winter Sale', 'Get 20% off on all rooms for the winter season', 15, '2025-03-31'),
(3, 'Ramadhan', 'karim', 25, '2025-03-08');

-- --------------------------------------------------------

--
-- Table structure for table `Question`
--

CREATE TABLE `Question` (
  `id` int(11) NOT NULL,
  `survey_id` int(11) DEFAULT NULL,
  `question_text` longtext NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reclamation`
--

CREATE TABLE `reclamation` (
  `id` int(11) NOT NULL,
  `status` varchar(15) NOT NULL,
  `date` date NOT NULL,
  `issue` varchar(15) NOT NULL,
  `category` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reclamation`
--

INSERT INTO `reclamation` (`id`, `status`, `date`, `issue`, `category`) VALUES
(29, 'bug', '2025-02-19', 'site', 'En cours'),
(30, 'Optimisation ', '2025-02-20', 'Performance', 'Closed'),
(32, 'bug', '2025-02-20', 'aa', 'En cours'),
(34, 'jqsx', '2025-02-22', 'qsxqsx', 'En attente'),
(36, 'LKO', '2025-02-25', 'ASDASdddd', 'En attente'),
(37, 'a', '2025-02-27', 'b', 'En attente'),
(38, 'asds', '2025-02-27', 'asdasd', 'En attente'),
(39, 'asdasd', '2025-02-27', 'asdasda', 'En attente'),
(40, 'looll', '2025-03-02', 'lool', 'En attente'),
(41, 'En attente', '2025-03-05', 'issues', 'Bug');

-- --------------------------------------------------------

--
-- Table structure for table `Reservation`
--

CREATE TABLE `Reservation` (
  `id_reservation` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `travel_id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `places` varchar(255) NOT NULL,
  `order_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Reservation`
--

INSERT INTO `Reservation` (`id_reservation`, `user_id`, `travel_id`, `status`, `ticket_id`, `hotel_id`, `nom`, `prenom`, `phone`, `email`, `places`, `order_date`) VALUES
(1, 16, -1, 'En attente', 1, -1, 'Amine', 'user', '12312312', 'aminesouissi682@gmail.com', '4', '2025-04-07'),
(2, 16, -1, 'En attente', 1, -1, 'Amine', 'user', '12312312', 'aminesouissi682@gmail.com', '3', '2025-04-07'),
(3, 16, -1, 'En attente', 1, -1, 'Amine', 'user', '12345123', 'aminesouissi682@gmail.com', '8', '2025-04-07');

-- --------------------------------------------------------

--
-- Table structure for table `res_transport`
--

CREATE TABLE `res_transport` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `res_transport`
--

INSERT INTO `res_transport` (`id`, `user_id`, `car_id`, `start_date`, `end_date`, `status`) VALUES
(15, 1, 15, '2025-04-06', '2025-05-09', 'IN_PROGRESS'),
(19, 1, 15, '2025-02-28', '2025-03-22', 'IN_PROGRESS'),
(20, 1, 15, '2025-03-06', '2025-03-19', 'IN_PROGRESS');

-- --------------------------------------------------------

--
-- Table structure for table `Survey`
--

CREATE TABLE `Survey` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `category` varchar(50) NOT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `survey_response`
--

CREATE TABLE `survey_response` (
  `id` int(11) NOT NULL,
  `response_data` longtext NOT NULL,
  `recommendations` longtext NOT NULL,
  `completed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id_ticket` int(11) NOT NULL,
  `flight_number` int(11) NOT NULL,
  `airline` varchar(255) NOT NULL,
  `departure_city` varchar(255) NOT NULL,
  `arrival_city` varchar(255) NOT NULL,
  `departure_date` date NOT NULL,
  `departure_time` varchar(255) NOT NULL,
  `arrival_date` date NOT NULL,
  `arrival_time` varchar(255) NOT NULL,
  `ticket_class` varchar(50) NOT NULL,
  `price` double NOT NULL,
  `ticket_type` varchar(50) NOT NULL,
  `image_airline` varchar(255) NOT NULL,
  `city_image` varchar(1000) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id_ticket`, `flight_number`, `airline`, `departure_city`, `arrival_city`, `departure_date`, `departure_time`, `arrival_date`, `arrival_time`, `ticket_class`, `price`, `ticket_type`, `image_airline`, `city_image`, `agency_id`, `promotion_id`) VALUES
(1, 123, 'Air France', 'Paris', 'New York', '2025-03-15', '08:30:00', '2025-03-15', '14:15:00', 'Economy', 750, 'one-way', 'airfrance.png', 'http://localhost/img/hotel2.jpg', 2, 1),
(2, 221, 'asdasd', 'asdasda', 'ILL', '2025-02-27', '09:22:00', '2025-02-28', '12:00:00', 'Economy', 2000, 'one-way', 'default_airline.png', 'http://localhost/img/ticket.jpg', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `addresse` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `profile_photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`id`, `name`, `surname`, `password`, `email`, `phone`, `addresse`, `role`, `profile_photo`) VALUES
(1, 'Admin', 'Admin', '$2a$10$HbkdD6VU6DUuIRBqTKfL8OfNn2jSAODQrW2UlgNZT2k0BFzcmHSLW', 'admin@admin.com', '12345678', '123 Admin Street', 'Admin', ''),
(2, 'Agent', 'user', '$2a$10$HbkdD6VU6DUuIRBqTKfL8OfNn2jSAODQrW2UlgNZT2k0BFzcmHSLW', 'agent@agent.com', '98987656', '456 Agent Avenue', 'Agent', ''),
(4, 'Client', 'User', '$2a$10$HbkdD6VU6DUuIRBqTKfL8OfNn2jSAODQrW2UlgNZT2k0BFzcmHSLW', 'client@gmail.com', '22545445', 'Client Avenue 123', 'Client', ''),
(5, 'Cardinal', 'cardinal', '$2a$10$HbkdD6VU6DUuIRBqTKfL8OfNn2jSAODQrW2UlgNZT2k0BFzcmHSLW', 'home@home.com', '28619223', 'Tunisia', 'Client', ''),
(13, 'cardinal', 'user', '$2a$10$HbkdD6VU6DUuIRBqTKfL8OfNn2jSAODQrW2UlgNZT2k0BFzcmHSLW', 'omsehli@gmail.com', '+21628619391', 'Tunis', 'Agent', ''),
(16, 'Amine', 'user', '$2a$10$LgwUyGPRlUGqU7DmletL7eZo2T2QfK6j9Uw9n/RVOc4/0PKxwoQD6', 'aminesouissi682@gmail.com', '+21628657499', 'CHANGE ME', 'Client', ''),
(17, 'User1', 'user', '$2a$10$PkkRCgLBjfEbpFZxZL6dT.4eVL4Gc5nO5wOqKJt/7U36QiC2nmla.', 'user@client.com', '25649879', 'tunis', 'Client', ''),
(19, 'Quin', 'Buck', '$2y$13$U0FrfEUeplVWklThihxLl.ab9sMrWmse6pWQDcmlxM7aYyrmAizwm', 'xamywubige@mailinator.com', '+1 (944) 762-5625', 'Minima possimus mag', 'Client', 'Repellendus Corrupt');

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

CREATE TABLE `verification_codes` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webinaire`
--

CREATE TABLE `webinaire` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `link` varchar(255) NOT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `room_id` varchar(255) NOT NULL,
  `debut_date_time` datetime NOT NULL,
  `finit_date_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agency`
--
ALTER TABLE `agency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id_hotel`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offer_travel`
--
ALTER TABLE `offer_travel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Option`
--
ALTER TABLE `Option`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5A8600B01E27F6BF` (`question_id`);

--
-- Indexes for table `Panier`
--
ALTER TABLE `Panier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_24CC0DF2A76ED395` (`user_id`),
  ADD KEY `IDX_24CC0DF2B83297E7` (`reservation_id`);

--
-- Indexes for table `promotion`
--
ALTER TABLE `promotion`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Question`
--
ALTER TABLE `Question`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B6F7494EB3FE509D` (`survey_id`);

--
-- Indexes for table `reclamation`
--
ALTER TABLE `reclamation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Reservation`
--
ALTER TABLE `Reservation`
  ADD PRIMARY KEY (`id_reservation`),
  ADD KEY `IDX_42C84955A76ED395` (`user_id`);

--
-- Indexes for table `res_transport`
--
ALTER TABLE `res_transport`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Survey`
--
ALTER TABLE `Survey`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_AD5F9BFCDE12AB56` (`created_by`);

--
-- Indexes for table `survey_response`
--
ALTER TABLE `survey_response`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id_ticket`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `webinaire`
--
ALTER TABLE `webinaire`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1B5D30753243BB18` (`hotel_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Option`
--
ALTER TABLE `Option`
  ADD CONSTRAINT `Option_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `Question` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `Panier`
--
ALTER TABLE `Panier`
  ADD CONSTRAINT `FK_24CC0DF2A76ED395` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_24CC0DF2B83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `Reservation` (`id_reservation`) ON DELETE CASCADE;

--
-- Constraints for table `Question`
--
ALTER TABLE `Question`
  ADD CONSTRAINT `Question_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `Survey` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `Reservation`
--
ALTER TABLE `Reservation`
  ADD CONSTRAINT `FK_42C84955A76ED395` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `Survey`
--
ALTER TABLE `Survey`
  ADD CONSTRAINT `FK_SURVEY_USER` FOREIGN KEY (`created_by`) REFERENCES `User` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `webinaire`
--
ALTER TABLE `webinaire`
  ADD CONSTRAINT `FK_1B5D30753243BB18` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id_hotel`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
