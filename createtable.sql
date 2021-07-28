-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 28, 2021 at 09:23 AM
-- Server version: 5.7.31
-- PHP Version: 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `lop_survey`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_valid_data`
--

DROP TABLE IF EXISTS `tbl_valid_data`;
CREATE TABLE IF NOT EXISTS `tbl_valid_data` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `listid` int(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `request` text,
  `response` text,
  `action_taken` varchar(5) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
COMMIT;

--
-- Table structure for table `tbl_invalid_data`
--

DROP TABLE IF EXISTS `tbl_invalid_data`;
CREATE TABLE IF NOT EXISTS `tbl_invalid_data` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `listid` int(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `request` text,
  `response` text,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
COMMIT;
