-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 05, 2011 at 11:31 AM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `zamger`
--

-- --------------------------------------------------------

--
-- Table structure for table `dogadjaj`
--

CREATE TABLE IF NOT EXISTS `dogadjaj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(45) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `dogadjaj`
--

