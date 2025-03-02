-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 02, 2025 at 11:12 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `canteengo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_sessions`
--

CREATE TABLE `active_sessions` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `last_activity` datetime NOT NULL,
  `session_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `active_sessions`
--

INSERT INTO `active_sessions` (`user_id`, `username`, `last_activity`, `session_id`) VALUES
(3, 'asd', '2025-03-02 18:08:47', 'u3ttml9kj23rpf35oavr1be7h3'),
(7, 'colline', '2025-03-02 16:58:49', '8uv7mrt1r8jlfhvvalpt442cu7'),
(11, 'staff', '2025-03-02 18:11:19', '8hlom31to0b7mqnu0difasi0hc');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `student_username` varchar(50) NOT NULL,
  `order_details` text NOT NULL,
  `payment_method` enum('cash','cashless') NOT NULL,
  `status` enum('pending','processing','claimable','complete') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `student_username`, `order_details`, `payment_method`, `status`, `created_at`) VALUES
(1, 'asd', 'Pastil (Qty: 1) | Total: ₱25.00', 'cash', 'complete', '2025-03-01 15:04:07'),
(2, 'colline', 'Bicol Express (Qty: 1), Coke (Qty: 1) | Total: ₱76.00', 'cash', 'complete', '2025-03-01 15:13:20'),
(3, 'asd', 'Pastil (Qty: 2) | Total: ₱50.00', 'cashless', 'pending', '2025-03-01 15:24:16'),
(4, 'asd', 'Coke (Qty: 1), Juice (Qty: 2) | Total: ₱70.00', 'cashless', 'processing', '2025-03-01 16:00:10'),
(5, 'kolen', 'Juice (Qty: 2), Water (Qty: 1) | Total: ₱55.00', 'cash', 'pending', '2025-03-01 16:00:57'),
(6, 'colline', 'Pastil (Qty: 1) | Total: ₱25.00', 'cashless', 'processing', '2025-03-01 16:24:02'),
(7, 'colline', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cashless', 'pending', '2025-03-01 16:29:32'),
(8, 'colline', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cashless', 'pending', '2025-03-01 16:29:36'),
(9, 'colline', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cashless', 'complete', '2025-03-01 16:30:01'),
(10, 'colline', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cashless', 'pending', '2025-03-01 16:30:39'),
(11, 'colline', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cashless', 'processing', '2025-03-01 16:31:34'),
(12, 'asd', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cash', 'complete', '2025-03-01 17:32:38'),
(13, 'asd', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cash', 'processing', '2025-03-01 17:33:40'),
(14, 'asd', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cash', 'pending', '2025-03-01 17:34:18'),
(15, 'asd', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cash', 'processing', '2025-03-01 17:40:08'),
(16, 'asd', 'Bicol Express (Qty: 1) | Total: ₱46.00', 'cash', 'pending', '2025-03-01 17:43:34'),
(17, 'asd', 'Water (Qty: 1), C2 Yellow (Qty: 1) | Total: ₱50.00', 'cash', 'pending', '2025-03-01 18:32:47'),
(18, 'colline', 'Burger (Qty: 3) | Total: ₱60.00', 'cashless', 'complete', '2025-03-01 18:33:41'),
(19, 'colline', 'Burger (Qty: 3) | Total: ₱60.00', 'cashless', 'pending', '2025-03-01 18:33:55'),
(20, 'asd', 'Burger (Qty: 1), Pastil (Qty: 1) | Total: ₱45.00', 'cash', 'complete', '2025-03-02 00:25:10'),
(21, 'asd', 'Burger (Qty: 1) | Total: ₱20.00', 'cashless', 'pending', '2025-03-02 00:32:16'),
(22, 'asd', 'Burger (Qty: 1) | Total: ₱20.00', 'cashless', 'complete', '2025-03-02 00:39:22'),
(23, 'asd', 'Fried Chicken (Qty: 1) | Total: ₱55.00', 'cash', 'complete', '2025-03-02 00:55:18'),
(24, 'User', 'Burger (Qty: 1), Pastil (Qty: 1), C2 Red (Qty: 1), Coffee (Qty: 1) | Total: ₱115.00', 'cash', 'complete', '2025-03-02 00:56:41'),
(25, 'asd', 'Juice (Qty: 2), Water (Qty: 2), Fried Chicken (Qty: 1), Adobo (Qty: 2) | Total: ₱215.00', 'cash', 'complete', '2025-03-02 03:51:03'),
(26, 'asd', 'Burger (Qty: 1) | Total: ₱20.00', 'cashless', 'claimable', '2025-03-02 07:38:39'),
(27, 'asd', 'Bicol Express (Qty: 3) | Total: ₱138.00', 'cashless', 'pending', '2025-03-02 08:44:26'),
(28, 'colline', 'Burger (Qty: 2) | Total: ₱40.00', 'cashless', 'pending', '2025-03-02 08:58:56'),
(29, 'colline', 'Coke (Qty: 2), Juice (Qty: 1) | Total: ₱110.00', 'cashless', 'pending', '2025-03-02 08:59:14'),
(30, 'asd', 'Coke (Qty: 1), Juice (Qty: 1) | Total: ₱65.00', 'cashless', 'complete', '2025-03-02 10:09:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','staff') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(2, 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '2025-03-01 13:42:52'),
(3, 'asd', '$2y$10$kHkHckGZMuwg2T8jGTK.IOKJBp35BnlwLpJGvqfbTRp1A/BdbcP5.', 'student', '2025-03-01 13:43:52'),
(7, 'colline', '$2y$10$V7ZhRDOPz9PTC0PrtNT2F.x6qvX5rac8zzPkeiujtqEgharesmjmS', 'student', '2025-03-01 15:13:07'),
(11, 'staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', '2025-03-01 15:31:20'),
(12, 'kolen', '$2y$10$vbfi9AQIoqZ0oby/gZmFHusXGzDnFeXnYIueWkYo4GeOO0g0vMiQG', 'student', '2025-03-01 16:00:46'),
(13, 'User', '$2y$10$9s2C6Xdn5gny59jgUtoz3eP67yDTm./NXpgirSg10SWQj5kIv8Usa', 'student', '2025-03-02 00:56:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_sessions`
--
ALTER TABLE `active_sessions`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
