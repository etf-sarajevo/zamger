-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 25, 2009 at 02:29 AM
-- Server version: 5.1.33
-- PHP Version: 5.2.9-2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

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
  `datum_otvaranja` datetime DEFAULT NULL,
  `datum_zatvaranja` datetime DEFAULT NULL,
  `naziv` char(255) NOT NULL,
  `opis` text,
  `aktivna` tinyint(1) DEFAULT '0',
  `editable` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `anketa`
--

INSERT INTO `anketa` (`id`, `datum_otvaranja`, `datum_zatvaranja`, `naziv`, `opis`, `aktivna`, `editable`) VALUES
(8, '2009-05-25 02:28:17', '2009-05-29 02:28:17', 'Nova Anketa', ' Anketa za studente bolonjskoga procesa   na ETF-u  ', 1, 1),
(9, NULL, NULL, 'Bolonjska anketa', NULL, 0, 0);

--
-- Table structure for table `izbori_pitanja`
--

DROP TABLE IF EXISTS `izbori_pitanja` ;
CREATE TABLE IF NOT EXISTS `izbori_pitanja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pitanje_id` int(10) unsigned NOT NULL,
  `izbor` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;


INSERT INTO `izbori_pitanja` (`id`, `pitanje_id`, `izbor`) VALUES
(1, 1, '1'),
(2, 1, '2'),
(3, 1, '3'),
(4, 1, '4'),
(5, 1, '5'),
(6, 2, '1'),
(7, 2, '2'),
(8, 2, '3'),
(9, 2, '4'),
(10, 2, '5'),
(11, 3, '1'),
(12, 3, '2'),
(13, 3, '3'),
(14, 3, '4'),
(15, 3, '5'),
(16, 4, '1'),
(17, 4, '2'),
(18, 4, '3'),
(19, 4, '4'),
(20, 4, '5'),
(21, 5, 'manje od 1'),
(22, 5, 'Od 1 do 2'),
(23, 5, 'Od 2 do 4'),
(24, 5, 'Od 4 do 6'),
(25, 5, 'Od 6 do 8');



--
-- Table structure for table `odgovor_rank`
--
DROP TABLE IF EXISTS `odgovor_rank` ;
CREATE TABLE IF NOT EXISTS `odgovor_rank` (
  `rezultat_id` int(10) unsigned NOT NULL,
  `pitanje_id` int(10) unsigned NOT NULL,
  `izbor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`rezultat_id`,`pitanje_id`,`izbor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



--
-- Table structure for table `odgovor_text`
--
DROP TABLE IF EXISTS `odgovor_text` ;
CREATE TABLE IF NOT EXISTS `odgovor_text` (
  `rezultat_id` int(10) unsigned NOT NULL,
  `pitanje_id` int(10) unsigned NOT NULL,
  `response` text,
  PRIMARY KEY (`rezultat_id`,`pitanje_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Table structure for table `pitanje`
--
DROP TABLE IF EXISTS `pitanje` ;
CREATE TABLE IF NOT EXISTS `pitanje` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa_id` int(10) unsigned NOT NULL,
  `tip_id` int(10) unsigned NOT NULL,
  `tekst` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=68 ;



--
-- Table structure for table `rezultat`
--
DROP TABLE IF EXISTS `rezultat`;
CREATE TABLE IF NOT EXISTS `rezultat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa_id` int(10) unsigned NOT NULL,
  `vrijeme` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `zavrsena` enum('Y','N') DEFAULT 'N',
  `predmet_id` int(11) DEFAULT NULL,
  `unique_id` varchar(50) DEFAULT NULL,
  `akad_god` int(10) NOT NULL DEFAULT '1',
  `studij` int(10) NOT NULL,
  `semestar` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=54 ;

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
