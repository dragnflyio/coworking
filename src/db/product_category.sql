-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 13, 2016 at 05:29 PM
-- Server version: 5.5.46-0ubuntu0.14.04.2
-- PHP Version: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `gp_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `product_category`
--

CREATE TABLE IF NOT EXISTS `product_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `weight` decimal(11,5) NOT NULL,
  `depth` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;

--
-- Dumping data for table `product_category`
--

INSERT INTO `product_category` (`id`, `name`, `code`, `weight`, `depth`, `parent_id`) VALUES
(20, 'Danh mục hàng hóa', 'ROOT', 1.00000, 0, NULL),
(21, 'Sản phẩm', 'SP', 4.00000, 1, 20),
(22, 'Hàng hóa', 'HH', 2.00000, 1, 20),
(23, 'Đồ uống', 'DU', 1.00000, 2, 22),
(24, 'OTHERS', 'OTHERS', 1.00000, 3, 23),
(25, 'DRINKS', 'DRINK', 2.00000, 3, 23),
(26, 'Sinh tố', 'ST', 3.00000, 3, 23),
(27, 'Dịch vụ', 'DVNH', 2.00000, 2, 22),
(28, 'Công cụ dụng cụ', 'CC', 3.00000, 1, 20),
(29, 'COFFEE', 'COFFEE', 4.00000, 3, 23);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
