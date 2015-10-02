-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 02, 2015 at 09:31 AM
-- Server version: 5.5.44-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ttnm`
--
CREATE DATABASE IF NOT EXISTS `ttnm` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `ttnm`;

-- --------------------------------------------------------

--
-- Table structure for table `gateway`
--

DROP TABLE IF EXISTS `gateway`;
CREATE TABLE IF NOT EXISTS `gateway` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `eui` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('up','down','dead','unknown') DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `remarks` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `eui_indx` (`eui`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=156 ;

-- --------------------------------------------------------

--
-- Table structure for table `status_update`
--

DROP TABLE IF EXISTS `status_update`;
CREATE TABLE IF NOT EXISTS `status_update` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `gateway_id` int(11) NOT NULL,
  `since_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `interval` varchar(100) DEFAULT NULL,
  `entries_seen` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `gateway_id` (`gateway_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19437 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `status_update`
--
ALTER TABLE `status_update`
  ADD CONSTRAINT `status_update_ibfk_1` FOREIGN KEY (`gateway_id`) REFERENCES `gateway` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
