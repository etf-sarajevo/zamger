-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 20, 2009 at 03:12 PM
-- Server version: 5.1.33
-- PHP Version: 5.2.9-2

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
-- Table structure for table `anketa`
--

DROP TABLE IF EXISTS `anketa`;
CREATE TABLE IF NOT EXISTS `anketa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_date` datetime DEFAULT NULL,
  `close_date` datetime DEFAULT NULL,
  `title` char(255) NOT NULL,
  `info` text,
  `aktivna` tinyint(1) DEFAULT '0',
  `editable` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `izbori_pitanja`
--

DROP TABLE IF EXISTS `izbori_pitanja`;
CREATE TABLE IF NOT EXISTS `izbori_pitanja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pitanje_id` int(10) unsigned NOT NULL,
  `izbor` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `izbori_pitanja`
--

INSERT INTO `izbori_pitanja` (`id`, `pitanje_id`, `izbor`) VALUES
(1, 1, '1'),
(2, 1, '2'),
(3, 1, '3'),
(4, 1, '4'),
(5, 1, '5');

-- --------------------------------------------------------

--
-- Table structure for table `odgovor_rank`
--

DROP TABLE IF EXISTS `odgovor_rank`;
CREATE TABLE IF NOT EXISTS `odgovor_rank` (
  `rezultat_id` int(10) unsigned NOT NULL,
  `pitanje_id` int(10) unsigned NOT NULL,
  `izbor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`rezultat_id`,`pitanje_id`,`izbor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `odgovor_text`
--

DROP TABLE IF EXISTS `odgovor_text`;
CREATE TABLE IF NOT EXISTS `odgovor_text` (
  `rezultat_id` int(10) unsigned NOT NULL,
  `pitanje_id` int(10) unsigned NOT NULL,
  `response` text,
  PRIMARY KEY (`rezultat_id`,`pitanje_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pitanje`
--

DROP TABLE IF EXISTS `pitanje`;
CREATE TABLE IF NOT EXISTS `pitanje` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa_id` int(10) unsigned NOT NULL,
  `tip_id` int(10) unsigned NOT NULL,
  `tekst` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;

-- --------------------------------------------------------

--
-- Table structure for table `rezultat`
--

DROP TABLE IF EXISTS `rezultat`;
CREATE TABLE IF NOT EXISTS `rezultat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa_id` int(10) unsigned NOT NULL,
  `submitted` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `complete` enum('Y','N') DEFAULT 'N',
  `predmet_id` int(11) DEFAULT NULL,
  `osoba_id` varchar(50) DEFAULT NULL,
  `akad_god` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=50 ;

-- --------------------------------------------------------

--
-- Table structure for table `tip_pitanja`
--

DROP TABLE IF EXISTS `tip_pitanja`;
CREATE TABLE IF NOT EXISTS `tip_pitanja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tip` char(32) NOT NULL,
  `postoji_izbor` enum('Y','N') NOT NULL,
  `tabela_odgovora` char(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `tip_pitanja`
--

INSERT INTO `tip_pitanja` (`id`, `tip`, `postoji_izbor`, `tabela_odgovora`) VALUES
(1, 'Ocjena (scale 1..5)', 'Y', 'odgovor_rank'),
(2, 'Komentar', 'N', 'odgovor_text');


