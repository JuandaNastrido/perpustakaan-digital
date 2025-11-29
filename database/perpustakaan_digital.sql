-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20250909.be01432c56
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 29, 2025 at 03:13 PM
-- Server version: 8.4.3
-- PHP Version: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan_digital`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `publication_year` year DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `average_rating` decimal(3,2) DEFAULT '0.00',
  `total_ratings` int DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  `max_concurrent_users` int DEFAULT '1',
  `current_users` int DEFAULT '0',
  `total_copies` int DEFAULT '1',
  `available_copies` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `category_id`, `publication_year`, `publisher`, `cover_image`, `description`, `created_at`, `average_rating`, `total_ratings`, `status`, `max_concurrent_users`, `current_users`, `total_copies`, `available_copies`) VALUES
(61, 'Pemrograman PHP & MySQL Modern', 'Budi Santoso', '9786234567890', 22, '2024', 'Penerbit Informatika', 'uploads/covers/692af80505c22_Pemrograman_PHP___MySQL_Modern.jpg', 'Panduan lengkap pemrograman web dengan PHP dan MySQL...', '2025-11-29 13:34:50', 0.00, 0, 'active', 1, 0, 1, 1),
(62, 'JavaScript ES6+ Mastery', 'Sarah Wijaya', '9786234567891', 22, '2023', 'Tech Publishing', 'uploads/covers/692af82a7c038_JavaScript_ES6__Mastery.jpg', 'Belajar fitur-fitur terbaru JavaScript...', '2025-11-29 13:34:50', 0.00, 0, 'active', 1, 0, 1, 1),
(63, 'Data Science dengan Python', 'Ahmad Fauzi', '9786234567892', 22, '2024', 'Data Science Press', 'uploads/covers/692af84e2eb20_Data_Science_dengan_Python.jpeg', 'Pengolahan data dan machine learning...', '2025-11-29 13:34:50', 0.00, 0, 'active', 1, 0, 1, 1),
(64, 'Dimensi Paralel', 'Dian Sastrowardoyo', '9786234567895', 21, '2023', 'Penerbit Fiksi Nusantara', 'uploads/covers/692af89c98933_Dimensi_Paralel.jpeg', 'Petualangan menembus dimensi paralel...', '2025-11-29 13:34:50', 0.00, 0, 'active', 1, 0, 1, 1),
(65, 'Planet X-237', 'Rizky Pratama', '9786234567896', 21, '2024', 'Galaxy Press', 'uploads/covers/692af8debe71e_Planet_X_237.jpeg', 'Kisah eksplorasi planet misterius...', '2025-11-29 13:34:50', 0.00, 0, 'active', 1, 0, 1, 1),
(66, 'Startup dari Nol sampai Exit', 'William Tanuwijaya', '9786234567899', 24, '2024', 'Entrepreneur Press', 'uploads/covers/692af9091ad67_Startup_dari_Nol_sampai_Exit.jpg', 'Panduan membangun startup dari ide hingga sukses...', '2025-11-29 13:34:50', 0.00, 0, 'active', 1, 0, 1, 1),
(67, 'Sejarah Indonesia Modern 1945-2024', 'Prof. Sri Wahyuni', '9786234567802', 23, '2024', 'Penerbit Sejarah', 'uploads/covers/692af92a4bab7_Sejarah_Indonesia_Modern_1945_2024.jpeg', 'Analisis mendalam sejarah Indonesia...', '2025-11-29 13:34:50', 0.00, 0, 'active', 1, 0, 1, 1),
(68, 'Seni Berpikir Jernih', 'Dr. Amanda Putri', '9786234567805', 25, '2024', 'Mindfulness Press', 'uploads/covers/692af94a2f22c_Seni_Berpikir_Jernih.jpg', 'Teknik mengasah kemampuan berpikir kritis...', '2025-11-29 13:34:50', 0.00, 0, 'active', 1, 0, 1, 1),
(69, 'Hujan', 'Tere Liye', '9781234567893', 21, '2016', 'Gramedia Pustaka Utama', 'uploads/covers/692af98743dc8_Hujan.jpg', 'Novel Hujan karya Tere Liye adalah cerita fiksi ilmiah tentang Lail, seorang gadis yang selamat dari bencana alam dahsyat di tahun 2042, dan pertemuannya dengan Esok, pemuda yang menjadi sahabat dan penyemangatnya. Novel ini mengeksplorasi tema cinta, kehilangan, memori, dan trauma melalui latar teknologi futuristik dan membahas dilema antara menghapus kenangan pahit atau menerimanya untuk melanjutkan hidup', '2025-11-29 13:47:51', 0.00, 0, 'active', 1, 0, 5, 4);

-- --------------------------------------------------------

--
-- Table structure for table `borrowings`
--

CREATE TABLE `borrowings` (
  `id` int NOT NULL,
  `member_id` int DEFAULT NULL,
  `book_id` int DEFAULT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') DEFAULT 'borrowed',
  `fine_amount` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `borrowings`
--

INSERT INTO `borrowings` (`id`, `member_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`, `fine_amount`, `created_at`) VALUES
(4, 6, 69, '2025-11-29', '2025-12-06', NULL, 'borrowed', 0.00, '2025-11-29 14:40:50');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(21, 'Sains Fiksi', 'Buku-buku fiksi ilmiah dan fantasi', '2025-11-29 13:34:38'),
(22, 'Teknologi', 'Buku programming, IT, dan teknologi', '2025-11-29 13:34:38'),
(23, 'Sejarah', 'Buku sejarah dan biografi', '2025-11-29 13:34:38'),
(24, 'Bisnis', 'Buku manajemen dan kewirausahaan', '2025-11-29 13:34:38'),
(25, 'Psikologi', 'Buku pengembangan diri dan psikologi populer', '2025-11-29 13:34:38');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `membership_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `user_id`, `full_name`, `phone`, `address`, `membership_date`, `status`, `created_at`) VALUES
(6, 7, 'Juanda Nastrido', '085163648108', 'JL limau manis', '2025-11-29', 'active', '2025-11-29 13:52:29');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `book_id` int DEFAULT NULL,
  `member_id` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text,
  `review_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(7, 'Najuuu', '$2y$12$6C/kU/gbJQ2fiq/zFBzCje0CtVWCljFUnJYLHPS2fQLAmbyR4zGTC', 'juandanastrido30@gmail.com', 'member', '2025-11-29 13:52:29'),
(8, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@perpustakaan.com', 'admin', '2025-11-29 14:15:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member_book` (`member_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `borrowings`
--
ALTER TABLE `borrowings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD CONSTRAINT `borrowings_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  ADD CONSTRAINT `borrowings_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
