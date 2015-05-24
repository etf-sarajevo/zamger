-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2015 at 10:27 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `zamger`
--
CREATE DATABASE IF NOT EXISTS `zamger` DEFAULT CHARACTER SET utf8 COLLATE utf8_slovenian_ci;
USE `zamger`;

-- --------------------------------------------------------

--
-- Table structure for table `akademska_godina`
--

DROP TABLE IF EXISTS `akademska_godina`;
CREATE TABLE IF NOT EXISTS `akademska_godina` (
  `id` int(11) NOT NULL,
  `naziv` varchar(20) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `aktuelna` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `akademska_godina`
--

TRUNCATE TABLE `akademska_godina`;
--
-- Dumping data for table `akademska_godina`
--

INSERT INTO `akademska_godina` (`id`, `naziv`, `aktuelna`) VALUES
(1, '2014/2015', 1);

-- --------------------------------------------------------

--
-- Table structure for table `akademska_godina_predmet`
--

DROP TABLE IF EXISTS `akademska_godina_predmet`;
CREATE TABLE IF NOT EXISTS `akademska_godina_predmet` (
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  PRIMARY KEY (`akademska_godina`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `akademska_godina_predmet`
--

TRUNCATE TABLE `akademska_godina_predmet`;
-- --------------------------------------------------------

--
-- Table structure for table `angazman`
--

DROP TABLE IF EXISTS `angazman`;
CREATE TABLE IF NOT EXISTS `angazman` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `angazman_status` int(11) NOT NULL,
  PRIMARY KEY (`predmet`,`akademska_godina`,`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `angazman`
--

TRUNCATE TABLE `angazman`;
-- --------------------------------------------------------

--
-- Table structure for table `angazman_status`
--

DROP TABLE IF EXISTS `angazman_status`;
CREATE TABLE IF NOT EXISTS `angazman_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Truncate table before insert `angazman_status`
--

TRUNCATE TABLE `angazman_status`;
--
-- Dumping data for table `angazman_status`
--

INSERT INTO `angazman_status` (`id`, `naziv`) VALUES
(1, 'odgovorni nastavnik'),
(2, 'asistent'),
(3, 'demonstrator'),
(4, 'predavač - istaknuti stručnjak iz prakse'),
(5, 'asistent - istaknuti stručnjak iz prakse'),
(6, 'profesor emeritus');

-- --------------------------------------------------------

--
-- Table structure for table `anketa_anketa`
--

DROP TABLE IF EXISTS `anketa_anketa`;
CREATE TABLE IF NOT EXISTS `anketa_anketa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datum_otvaranja` datetime DEFAULT NULL,
  `datum_zatvaranja` datetime DEFAULT NULL,
  `naziv` char(255) COLLATE utf8_slovenian_ci NOT NULL,
  `opis` text COLLATE utf8_slovenian_ci,
  `aktivna` tinyint(1) DEFAULT '0',
  `editable` tinyint(1) DEFAULT '1',
  `akademska_godina` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=16 ;

--
-- Truncate table before insert `anketa_anketa`
--

TRUNCATE TABLE `anketa_anketa`;
-- --------------------------------------------------------

--
-- Table structure for table `anketa_izbori_pitanja`
--

DROP TABLE IF EXISTS `anketa_izbori_pitanja`;
CREATE TABLE IF NOT EXISTS `anketa_izbori_pitanja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pitanje` int(10) unsigned NOT NULL,
  `izbor` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=26 ;

--
-- Truncate table before insert `anketa_izbori_pitanja`
--

TRUNCATE TABLE `anketa_izbori_pitanja`;
-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_rank`
--

DROP TABLE IF EXISTS `anketa_odgovor_rank`;
CREATE TABLE IF NOT EXISTS `anketa_odgovor_rank` (
  `rezultat` int(10) unsigned NOT NULL,
  `pitanje` int(10) unsigned NOT NULL,
  `izbor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`rezultat`,`pitanje`,`izbor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `anketa_odgovor_rank`
--

TRUNCATE TABLE `anketa_odgovor_rank`;
-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_text`
--

DROP TABLE IF EXISTS `anketa_odgovor_text`;
CREATE TABLE IF NOT EXISTS `anketa_odgovor_text` (
  `rezultat` int(10) unsigned NOT NULL,
  `pitanje` int(10) unsigned NOT NULL,
  `odgovor` text COLLATE utf8_slovenian_ci,
  PRIMARY KEY (`rezultat`,`pitanje`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `anketa_odgovor_text`
--

TRUNCATE TABLE `anketa_odgovor_text`;
-- --------------------------------------------------------

--
-- Table structure for table `anketa_pitanje`
--

DROP TABLE IF EXISTS `anketa_pitanje`;
CREATE TABLE IF NOT EXISTS `anketa_pitanje` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa` int(10) unsigned NOT NULL DEFAULT '0',
  `tip_pitanja` int(10) unsigned NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=92 ;

--
-- Truncate table before insert `anketa_pitanje`
--

TRUNCATE TABLE `anketa_pitanje`;
-- --------------------------------------------------------

--
-- Table structure for table `anketa_predmet`
--

DROP TABLE IF EXISTS `anketa_predmet`;
CREATE TABLE IF NOT EXISTS `anketa_predmet` (
  `anketa` int(11) NOT NULL,
  `predmet` int(11) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL,
  `aktivna` tinyint(1) NOT NULL,
  PRIMARY KEY (`anketa`,`predmet`,`akademska_godina`),
  KEY `anketa_predmet_ibfk_2` (`predmet`),
  KEY `anketa_predmet_ibfk_3` (`akademska_godina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `anketa_predmet`
--

TRUNCATE TABLE `anketa_predmet`;
-- --------------------------------------------------------

--
-- Table structure for table `anketa_rezultat`
--

DROP TABLE IF EXISTS `anketa_rezultat`;
CREATE TABLE IF NOT EXISTS `anketa_rezultat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa` int(10) unsigned NOT NULL,
  `zavrsena` enum('Y','N') COLLATE utf8_slovenian_ci DEFAULT 'N',
  `predmet` int(11) DEFAULT NULL,
  `unique_id` varchar(50) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `akademska_godina` int(10) NOT NULL,
  `studij` int(10) NOT NULL,
  `semestar` int(10) NOT NULL,
  `student` int(11) NOT NULL,
  `labgrupa` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unique_id` (`unique_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=27 ;

--
-- Truncate table before insert `anketa_rezultat`
--

TRUNCATE TABLE `anketa_rezultat`;
-- --------------------------------------------------------

--
-- Table structure for table `anketa_student_zavrsio`
--

DROP TABLE IF EXISTS `anketa_student_zavrsio`;
CREATE TABLE IF NOT EXISTS `anketa_student_zavrsio` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `anketa` int(11) NOT NULL,
  `zavrsena` enum('Y','N') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'N',
  `anketa_rezultat` int(11) NOT NULL,
  PRIMARY KEY (`student`,`predmet`,`akademska_godina`,`anketa`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `anketa_student_zavrsio`
--

TRUNCATE TABLE `anketa_student_zavrsio`;
-- --------------------------------------------------------

--
-- Table structure for table `anketa_tip_pitanja`
--

DROP TABLE IF EXISTS `anketa_tip_pitanja`;
CREATE TABLE IF NOT EXISTS `anketa_tip_pitanja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tip` char(32) COLLATE utf8_slovenian_ci NOT NULL,
  `postoji_izbor` enum('Y','N') COLLATE utf8_slovenian_ci NOT NULL,
  `tabela_odgovora` char(32) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=3 ;

--
-- Truncate table before insert `anketa_tip_pitanja`
--

TRUNCATE TABLE `anketa_tip_pitanja`;
--
-- Dumping data for table `anketa_tip_pitanja`
--

INSERT INTO `anketa_tip_pitanja` (`id`, `tip`, `postoji_izbor`, `tabela_odgovora`) VALUES
(1, 'Ocjena (skala 1..5)', 'Y', 'odgovor_rank'),
(2, 'Komentar', 'N', 'odgovor_text');

-- --------------------------------------------------------

--
-- Table structure for table `auth`
--

DROP TABLE IF EXISTS `auth`;
CREATE TABLE IF NOT EXISTS `auth` (
  `id` int(11) NOT NULL DEFAULT '0',
  `login` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `password` varchar(20) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `external_id` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `aktivan` tinyint(1) NOT NULL DEFAULT '1',
  `posljednji_pristup` datetime NOT NULL,
  PRIMARY KEY (`id`,`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `auth`
--

TRUNCATE TABLE `auth`;
--
-- Dumping data for table `auth`
--

INSERT INTO `auth` (`id`, `login`, `password`, `admin`, `external_id`, `aktivan`, `posljednji_pristup`) VALUES
(1, 'admin', 'admin', 0, '', 1, '2015-05-23 18:47:44');

-- --------------------------------------------------------

--
-- Table structure for table `autotest`
--

DROP TABLE IF EXISTS `autotest`;
CREATE TABLE IF NOT EXISTS `autotest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zadaca` int(11) NOT NULL,
  `zadatak` int(11) NOT NULL,
  `kod` text COLLATE utf8_slovenian_ci NOT NULL,
  `rezultat` text COLLATE utf8_slovenian_ci NOT NULL,
  `alt_rezultat` text COLLATE utf8_slovenian_ci NOT NULL,
  `fuzzy` tinyint(1) NOT NULL DEFAULT '0',
  `global_scope` text COLLATE utf8_slovenian_ci NOT NULL,
  `pozicija_globala` enum('prije_svega','prije_maina','','') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'prije_maina',
  PRIMARY KEY (`id`),
  KEY `autotest_ibfk_1` (`zadaca`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=190 ;

--
-- Truncate table before insert `autotest`
--

TRUNCATE TABLE `autotest`;
-- --------------------------------------------------------

--
-- Table structure for table `autotest_replace`
--

DROP TABLE IF EXISTS `autotest_replace`;
CREATE TABLE IF NOT EXISTS `autotest_replace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zadaca` int(11) NOT NULL,
  `zadatak` int(11) NOT NULL,
  `tip` enum('funkcija','klasa','metoda') COLLATE utf8_slovenian_ci NOT NULL,
  `specifikacija` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `zamijeni` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `autotest_replace_ibfk_1` (`zadaca`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=36 ;

--
-- Truncate table before insert `autotest_replace`
--

TRUNCATE TABLE `autotest_replace`;
-- --------------------------------------------------------

--
-- Table structure for table `autotest_rezultat`
--

DROP TABLE IF EXISTS `autotest_rezultat`;
CREATE TABLE IF NOT EXISTS `autotest_rezultat` (
  `autotest` int(11) NOT NULL,
  `student` int(11) NOT NULL,
  `izlaz_programa` text COLLATE utf8_slovenian_ci NOT NULL,
  `status` enum('ok','wrong','error','no_func','exec_fail','too_long','crash','find_fail','oob','uninit','memleak','invalid_free','mismatched_free') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'error',
  `nalaz` text COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`autotest`,`student`),
  KEY `autotest_rezultat_ibfk_1` (`student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `autotest_rezultat`
--

TRUNCATE TABLE `autotest_rezultat`;
-- --------------------------------------------------------

--
-- Table structure for table `bb_post`
--

DROP TABLE IF EXISTS `bb_post`;
CREATE TABLE IF NOT EXISTS `bb_post` (
  `id` int(11) NOT NULL,
  `naslov` varchar(300) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  `tema` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `bb_post`
--

TRUNCATE TABLE `bb_post`;
-- --------------------------------------------------------

--
-- Table structure for table `bb_post_text`
--

DROP TABLE IF EXISTS `bb_post_text`;
CREATE TABLE IF NOT EXISTS `bb_post_text` (
  `post` int(11) NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`post`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `bb_post_text`
--

TRUNCATE TABLE `bb_post_text`;
-- --------------------------------------------------------

--
-- Table structure for table `bb_tema`
--

DROP TABLE IF EXISTS `bb_tema`;
CREATE TABLE IF NOT EXISTS `bb_tema` (
  `id` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prvi_post` int(11) NOT NULL DEFAULT '0',
  `zadnji_post` int(11) NOT NULL DEFAULT '0',
  `pregleda` int(11) unsigned NOT NULL DEFAULT '0',
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `bb_tema`
--

TRUNCATE TABLE `bb_tema`;
-- --------------------------------------------------------

--
-- Table structure for table `bl_clanak`
--

DROP TABLE IF EXISTS `bl_clanak`;
CREATE TABLE IF NOT EXISTS `bl_clanak` (
  `id` int(11) NOT NULL,
  `naslov` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  `slika` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `bl_clanak`
--

TRUNCATE TABLE `bl_clanak`;
-- --------------------------------------------------------

--
-- Table structure for table `cas`
--

DROP TABLE IF EXISTS `cas`;
CREATE TABLE IF NOT EXISTS `cas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `vrijeme` time NOT NULL DEFAULT '00:00:00',
  `labgrupa` int(11) NOT NULL,
  `nastavnik` int(11) NOT NULL,
  `komponenta` int(11) NOT NULL,
  `kviz` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cas_ibfk_6` (`labgrupa`),
  KEY `cas_ibfk_7` (`nastavnik`),
  KEY `cas_ibfk_8` (`komponenta`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

--
-- Truncate table before insert `cas`
--

TRUNCATE TABLE `cas`;
-- --------------------------------------------------------

--
-- Table structure for table `cron`
--

DROP TABLE IF EXISTS `cron`;
CREATE TABLE IF NOT EXISTS `cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `aktivan` tinyint(1) NOT NULL,
  `godina` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `mjesec` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `dan` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `sat` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `minuta` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `sekunda` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `zadnje_izvrsenje` datetime NOT NULL,
  `sljedece_izvrsenje` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

--
-- Truncate table before insert `cron`
--

TRUNCATE TABLE `cron`;
-- --------------------------------------------------------

--
-- Table structure for table `cron_rezultat`
--

DROP TABLE IF EXISTS `cron_rezultat`;
CREATE TABLE IF NOT EXISTS `cron_rezultat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cron` int(11) NOT NULL,
  `izlaz` mediumtext COLLATE utf8_slovenian_ci NOT NULL,
  `return_value` int(11) NOT NULL,
  `vrijeme` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cron_rezultat_ibfk_1` (`cron`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=15 ;

--
-- Truncate table before insert `cron_rezultat`
--

TRUNCATE TABLE `cron_rezultat`;
-- --------------------------------------------------------

--
-- Table structure for table `drzava`
--

DROP TABLE IF EXISTS `drzava`;
CREATE TABLE IF NOT EXISTS `drzava` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=12 ;

--
-- Truncate table before insert `drzava`
--

TRUNCATE TABLE `drzava`;
--
-- Dumping data for table `drzava`
--

INSERT INTO `drzava` (`id`, `naziv`) VALUES
(1, 'Bosna i Hercegovina'),
(2, 'Srbija'),
(3, 'Hrvatska'),
(4, 'Crna Gora'),
(5, 'Slovenija'),
(6, 'Kosovo'),
(7, 'Turska'),
(8, 'Njemačka'),
(9, 'Makedonija'),
(10, 'Iran');

-- --------------------------------------------------------

--
-- Table structure for table `ekstenzije`
--

DROP TABLE IF EXISTS `ekstenzije`;
CREATE TABLE IF NOT EXISTS `ekstenzije` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=26 ;

--
-- Truncate table before insert `ekstenzije`
--

TRUNCATE TABLE `ekstenzije`;
--
-- Dumping data for table `ekstenzije`
--

INSERT INTO `ekstenzije` (`id`, `naziv`) VALUES
(1, '.zip'),
(2, '.doc'),
(3, '.pdf'),
(4, '.odt'),
(5, '.docx'),
(6, '.txt'),
(7, '.rtf'),
(8, '.7z'),
(9, '.rar'),
(10, '.c'),
(11, '.cpp'),
(12, '.m'),
(13, '.fig'),
(14, '.jar'),
(15, '.java'),
(16, '.gz'),
(17, '.html'),
(18, '.php');

-- --------------------------------------------------------

--
-- Table structure for table `email`
--

DROP TABLE IF EXISTS `email`;
CREATE TABLE IF NOT EXISTS `email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osoba` int(11) NOT NULL,
  `adresa` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `sistemska` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `email_ibfk_1` (`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `email`
--

TRUNCATE TABLE `email`;
-- --------------------------------------------------------

--
-- Table structure for table `hr_kompetencije`
--

DROP TABLE IF EXISTS `hr_kompetencije`;
CREATE TABLE IF NOT EXISTS `hr_kompetencije` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `jezik` int(11) NOT NULL,
  `razumjevanje` int(11) NOT NULL,
  `govor` int(11) NOT NULL,
  `pisanje` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=3 ;

--
-- Truncate table before insert `hr_kompetencije`
--

TRUNCATE TABLE `hr_kompetencije`;
--
-- Dumping data for table `hr_kompetencije`
--

INSERT INTO `hr_kompetencije` (`id`, `fk_osoba`, `jezik`, `razumjevanje`, `govor`, `pisanje`) VALUES
(1, 1, 1, 1, 1, 1),
(2, 1, 11, 2, 3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `hr_mentorstvo`
--

DROP TABLE IF EXISTS `hr_mentorstvo`;
CREATE TABLE IF NOT EXISTS `hr_mentorstvo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `ime_kandidata` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv_teme` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `fk_fakultet` int(11) NOT NULL,
  `fk_vrsta_mentora` int(11) NOT NULL,
  `datum` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=8 ;

--
-- Truncate table before insert `hr_mentorstvo`
--

TRUNCATE TABLE `hr_mentorstvo`;
--
-- Dumping data for table `hr_mentorstvo`
--

INSERT INTO `hr_mentorstvo` (`id`, `fk_osoba`, `ime_kandidata`, `naziv_teme`, `fk_fakultet`, `fk_vrsta_mentora`, `datum`) VALUES
(1, 1, 'Teo', 'Tema', 1, 1, '2012-01-23 23:00:00'),
(2, 1, 'asdassad', 'daasdads', 0, 0, '2012-01-18 00:00:00'),
(3, 1, 'adsads', 'dsads', 0, 0, '2012-01-09 00:00:00'),
(4, 1, 'adsdsadas', 'sadads', 2, 1, '2012-01-02 00:00:00'),
(5, 1, 'saddasdsa', 'dsadas', 0, 0, '2012-01-09 00:00:00'),
(6, 1, 'adsasd', 'assadasd', 3, 3, '2012-01-03 00:00:00'),
(7, 1, 'aaaaaaa', 'bbbb', 13, 4, '2012-01-12 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `hr_nagrade_priznanja`
--

DROP TABLE IF EXISTS `hr_nagrade_priznanja`;
CREATE TABLE IF NOT EXISTS `hr_nagrade_priznanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `datum` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `opis` mediumtext COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=4 ;

--
-- Truncate table before insert `hr_nagrade_priznanja`
--

TRUNCATE TABLE `hr_nagrade_priznanja`;
--
-- Dumping data for table `hr_nagrade_priznanja`
--

INSERT INTO `hr_nagrade_priznanja` (`id`, `fk_osoba`, `datum`, `naziv`, `opis`) VALUES
(1, 1, '2012-01-23 23:00:00', 'Naziv', 'Blabla'),
(2, 1, '0000-00-00 00:00:00', 'dasasd', 'asdsad'),
(3, 1, '2012-01-09 00:00:00', 'dasads', 'sadads');

-- --------------------------------------------------------

--
-- Table structure for table `hr_naucni_radovi`
--

DROP TABLE IF EXISTS `hr_naucni_radovi`;
CREATE TABLE IF NOT EXISTS `hr_naucni_radovi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `naziv_rada` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv_casopisa` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv_izdavaca` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  `datum` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=4 ;

--
-- Truncate table before insert `hr_naucni_radovi`
--

TRUNCATE TABLE `hr_naucni_radovi`;
--
-- Dumping data for table `hr_naucni_radovi`
--

INSERT INTO `hr_naucni_radovi` (`id`, `fk_osoba`, `naziv_rada`, `naziv_casopisa`, `naziv_izdavaca`, `datum`) VALUES
(1, 1, 'Naucni', 'Casopis', 'Izdavac', '0000-00-00 00:00:00'),
(2, 1, '$naziv', '$naziv_casopisa', '$naziv_izdavaca', '1970-01-01 00:00:01'),
(3, 1, 'dasmlk', 'jljkl', 'lkjlk', '2012-01-04 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `hr_publikacija`
--

DROP TABLE IF EXISTS `hr_publikacija`;
CREATE TABLE IF NOT EXISTS `hr_publikacija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `casopis` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `fk_tip_publikacije` int(11) NOT NULL,
  `fk_osoba` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10 ;

--
-- Truncate table before insert `hr_publikacija`
--

TRUNCATE TABLE `hr_publikacija`;
--
-- Dumping data for table `hr_publikacija`
--

INSERT INTO `hr_publikacija` (`id`, `datum`, `naziv`, `casopis`, `fk_tip_publikacije`, `fk_osoba`) VALUES
(1, '2012-01-23 23:00:00', 'Publikacija', 'Casopis', 1, 1),
(2, '2012-01-24 20:52:20', 'a', 'b', 1, 1),
(3, '0000-00-00 00:00:00', '2012-01-12 01:00:00', 's', 0, 3),
(4, '0000-00-00 00:00:00', '2012-01-04 01:00:00', 'q', 0, 3),
(5, '0000-00-00 00:00:00', '2012-01-04 01:00:00', 'dasdas', 0, 1),
(6, '0000-00-00 00:00:00', '2012-01-09 01:00:00', 'adas', 0, 2),
(7, '0000-00-00 00:00:00', '2012-01-18 01:00:00', 'jopop', 0, 6),
(8, '0000-00-00 00:00:00', '1970-01-01 01:00:00', '$naziv', 0, 1),
(9, '2012-01-11 00:00:00', 'das', 'sadsasd', 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `hr_radno_iskustvo`
--

DROP TABLE IF EXISTS `hr_radno_iskustvo`;
CREATE TABLE IF NOT EXISTS `hr_radno_iskustvo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `fk_radno_mjesto` int(11) NOT NULL,
  `fk_oblik_zaposlenja` int(11) NOT NULL,
  `pocetak_zaposlenja` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `kraj_zaposlenja` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `radni_staz` int(11) NOT NULL,
  `radni_staz_nastava` int(11) NOT NULL,
  `broj_radne_knjizice` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `koeficijent` decimal(10,0) NOT NULL,
  `ugovorena_placa` decimal(10,0) NOT NULL,
  `fk_banka` int(11) NOT NULL,
  `broj` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `napomena` mediumtext COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `hr_radno_iskustvo`
--

TRUNCATE TABLE `hr_radno_iskustvo`;
-- --------------------------------------------------------

--
-- Table structure for table `hr_usavrsavanje`
--

DROP TABLE IF EXISTS `hr_usavrsavanje`;
CREATE TABLE IF NOT EXISTS `hr_usavrsavanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `naziv_usavrsavanja` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  `obrazovna_institucija` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  `kvalifikacija` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10 ;

--
-- Truncate table before insert `hr_usavrsavanje`
--

TRUNCATE TABLE `hr_usavrsavanje`;
--
-- Dumping data for table `hr_usavrsavanje`
--

INSERT INTO `hr_usavrsavanje` (`id`, `fk_osoba`, `datum`, `naziv_usavrsavanja`, `obrazovna_institucija`, `kvalifikacija`) VALUES
(1, 1, '2012-01-23 23:00:00', 'Usavrsen', 'Neka institucija', 'Kvalifikovan'),
(2, 0, '2012-01-23 23:00:00', 'Usavrsen', 'Neka institucija', 'Kvalifikovan'),
(3, 1, '2012-01-23 23:00:00', 'n', 'o', 'k'),
(4, 1, '2004-01-19 23:00:00', 'a', '', 'x'),
(5, 1, '2011-01-19 23:00:00', 'asddas', 'sadsad', 'sadsad'),
(6, 1, '2011-01-20 10:00:00', 'a', 'w', 'w'),
(7, 1, '2012-01-24 00:00:00', 'dasasd', 'asdads', 'asdsad'),
(8, 1, '2012-01-02 00:00:00', 'as', 's', 'as'),
(9, 1, '2012-01-10 00:00:00', 'sadas', 'saddsa', 'adsasddas');

-- --------------------------------------------------------

--
-- Table structure for table `institucija`
--

DROP TABLE IF EXISTS `institucija`;
CREATE TABLE IF NOT EXISTS `institucija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `roditelj` int(11) NOT NULL DEFAULT '0',
  `kratki_naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

--
-- Truncate table before insert `institucija`
--

TRUNCATE TABLE `institucija`;
--
-- Dumping data for table `institucija`
--

INSERT INTO `institucija` (`id`, `naziv`, `roditelj`, `kratki_naziv`) VALUES
(2, 'Odsjek za računarstvo i informatiku', 1, 'RI'),
(3, 'Odsjek za automatiku i elektroniku', 1, 'AE'),
(4, 'Odsjek za elektroenergetiku', 1, 'EE'),
(5, 'Odsjek za telekomunikacije', 1, 'TK'),
(1, 'Elektrotehnički fakultet Sarajevo', 0, 'ETF');

-- --------------------------------------------------------

--
-- Table structure for table `ispit`
--

DROP TABLE IF EXISTS `ispit`;
CREATE TABLE IF NOT EXISTS `ispit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `predmet` int(11) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `komponenta` int(2) NOT NULL DEFAULT '0',
  `vrijemeobjave` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `ispit`
--

TRUNCATE TABLE `ispit`;
-- --------------------------------------------------------

--
-- Table structure for table `ispitocjene`
--

DROP TABLE IF EXISTS `ispitocjene`;
CREATE TABLE IF NOT EXISTS `ispitocjene` (
  `ispit` int(11) NOT NULL DEFAULT '0',
  `student` int(11) NOT NULL DEFAULT '0',
  `ocjena` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ispit`,`student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `ispitocjene`
--

TRUNCATE TABLE `ispitocjene`;
-- --------------------------------------------------------

--
-- Table structure for table `ispit_termin`
--

DROP TABLE IF EXISTS `ispit_termin`;
CREATE TABLE IF NOT EXISTS `ispit_termin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datumvrijeme` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `maxstudenata` int(11) NOT NULL,
  `deadline` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ispit` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;

--
-- Truncate table before insert `ispit_termin`
--

TRUNCATE TABLE `ispit_termin`;
-- --------------------------------------------------------

--
-- Table structure for table `izbor`
--

DROP TABLE IF EXISTS `izbor`;
CREATE TABLE IF NOT EXISTS `izbor` (
  `fk_osoba` int(11) NOT NULL,
  `fk_naucnonastavno_zvanje` int(11) NOT NULL,
  `datum_izbora` date NOT NULL,
  `datum_isteka` date NOT NULL,
  `fk_naucna_oblast` int(11) NOT NULL,
  `fk_uza_naucna_oblast` int(11) NOT NULL,
  `dopunski` tinyint(1) NOT NULL,
  `druga_institucija` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `izbor`
--

TRUNCATE TABLE `izbor`;
-- --------------------------------------------------------

--
-- Table structure for table `izborni_slot`
--

DROP TABLE IF EXISTS `izborni_slot`;
CREATE TABLE IF NOT EXISTS `izborni_slot` (
  `id` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY (`id`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `izborni_slot`
--

TRUNCATE TABLE `izborni_slot`;
-- --------------------------------------------------------

--
-- Table structure for table `kanton`
--

DROP TABLE IF EXISTS `kanton`;
CREATE TABLE IF NOT EXISTS `kanton` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `kratki_naziv` varchar(5) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

--
-- Truncate table before insert `kanton`
--

TRUNCATE TABLE `kanton`;
--
-- Dumping data for table `kanton`
--

INSERT INTO `kanton` (`id`, `naziv`, `kratki_naziv`) VALUES
(1, 'Bosansko-Podrinjski kanton', 'BPK'),
(2, 'Hercegovačko-Neretvanski kanton', 'HNK'),
(3, 'Livanjski kanton', 'LK'),
(4, 'Posavski kanton', 'PK'),
(5, 'Sarajevski kanton', 'SK'),
(6, 'Srednjobosanski kanton', 'SBK'),
(7, 'Tuzlanski kanton', 'TK'),
(8, 'Unsko-Sanski kanton', 'USK'),
(9, 'Zapadno-Hercegovački kanton', 'ZHK'),
(10, 'Zeničko-Dobojski kanton', 'ZDK'),
(11, 'Republika Srpska', 'RS'),
(12, 'Distrikt Brčko', 'DB'),
(13, 'Strani državljanin', 'SD');

-- --------------------------------------------------------

--
-- Table structure for table `kolizija`
--

DROP TABLE IF EXISTS `kolizija`;
CREATE TABLE IF NOT EXISTS `kolizija` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  KEY `student` (`student`,`akademska_godina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `kolizija`
--

TRUNCATE TABLE `kolizija`;
-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

DROP TABLE IF EXISTS `komentar`;
CREATE TABLE IF NOT EXISTS `komentar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student` int(11) NOT NULL DEFAULT '0',
  `nastavnik` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `komentar` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `komentar`
--

TRUNCATE TABLE `komentar`;
-- --------------------------------------------------------

--
-- Table structure for table `komponenta`
--

DROP TABLE IF EXISTS `komponenta`;
CREATE TABLE IF NOT EXISTS `komponenta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(40) COLLATE utf8_slovenian_ci NOT NULL,
  `gui_naziv` varchar(20) COLLATE utf8_slovenian_ci NOT NULL,
  `kratki_gui_naziv` varchar(20) COLLATE utf8_slovenian_ci NOT NULL,
  `tipkomponente` int(11) NOT NULL,
  `maxbodova` double NOT NULL,
  `prolaz` double NOT NULL,
  `opcija` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `uslov` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Truncate table before insert `komponenta`
--

TRUNCATE TABLE `komponenta`;
--
-- Dumping data for table `komponenta`
--

INSERT INTO `komponenta` (`id`, `naziv`, `gui_naziv`, `kratki_gui_naziv`, `tipkomponente`, `maxbodova`, `prolaz`, `opcija`, `uslov`) VALUES
(1, 'I parcijalni (ETF BSc)', 'I parcijalni', 'I parc', 1, 20, 10, '', 0),
(2, 'II parcijalni (ETF BSc)', 'II parcijalni', 'II parc', 1, 20, 10, '', 0),
(3, 'Integralni (ETF BSc)', 'Integralni', 'Int', 2, 40, 20, '1+2', 0),
(4, 'Usmeni (ETF BSc)', 'Usmeni', 'Usmeni', 1, 40, 0, '', 0),
(5, 'Prisustvo (ETF BSc)', 'Prisustvo', 'Prisustvo', 3, 10, 0, '3', 0),
(6, 'Zadace (ETF BSc)', 'Zadace', 'Zadace', 4, 10, 0, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `komponentebodovi`
--

DROP TABLE IF EXISTS `komponentebodovi`;
CREATE TABLE IF NOT EXISTS `komponentebodovi` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `komponenta` int(11) NOT NULL,
  `bodovi` double NOT NULL,
  PRIMARY KEY (`student`,`predmet`,`komponenta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `komponentebodovi`
--

TRUNCATE TABLE `komponentebodovi`;
-- --------------------------------------------------------

--
-- Table structure for table `konacna_ocjena`
--

DROP TABLE IF EXISTS `konacna_ocjena`;
CREATE TABLE IF NOT EXISTS `konacna_ocjena` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `ocjena` int(3) NOT NULL,
  `datum` datetime NOT NULL,
  `datum_u_indeksu` date NOT NULL,
  `odluka` int(11) NOT NULL,
  `datum_provjeren` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`student`,`predmet`),
  KEY `konacna_ocjena_ibfk_2` (`predmet`),
  KEY `konacna_ocjena_ibfk_3` (`akademska_godina`),
  KEY `konacna_ocjena_ibfk_4` (`odluka`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `konacna_ocjena`
--

TRUNCATE TABLE `konacna_ocjena`;
-- --------------------------------------------------------

--
-- Table structure for table `kviz`
--

DROP TABLE IF EXISTS `kviz`;
CREATE TABLE IF NOT EXISTS `kviz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `vrijeme_pocetak` datetime NOT NULL,
  `vrijeme_kraj` datetime NOT NULL,
  `labgrupa` int(11) NOT NULL,
  `ip_adrese` text COLLATE utf8_slovenian_ci NOT NULL,
  `prolaz_bodova` float NOT NULL,
  `broj_pitanja` int(11) NOT NULL,
  `trajanje_kviza` int(11) NOT NULL COMMENT 'u sekundama',
  `aktivan` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `kviz_ibfk_1` (`predmet`),
  KEY `kviz_ibfk_2` (`akademska_godina`),
  KEY `kviz_ibfk_3` (`labgrupa`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=112 ;

--
-- Truncate table before insert `kviz`
--

TRUNCATE TABLE `kviz`;
-- --------------------------------------------------------

--
-- Table structure for table `kviz_odgovor`
--

DROP TABLE IF EXISTS `kviz_odgovor`;
CREATE TABLE IF NOT EXISTS `kviz_odgovor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kviz_pitanje` int(11) NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  `tacan` tinyint(1) NOT NULL,
  `vidljiv` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1867 ;

--
-- Truncate table before insert `kviz_odgovor`
--

TRUNCATE TABLE `kviz_odgovor`;
-- --------------------------------------------------------

--
-- Table structure for table `kviz_pitanje`
--

DROP TABLE IF EXISTS `kviz_pitanje`;
CREATE TABLE IF NOT EXISTS `kviz_pitanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kviz` int(11) NOT NULL,
  `tip` enum('mcsa','mcma','tekstualno') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'mcsa',
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  `bodova` float NOT NULL DEFAULT '1',
  `vidljivo` tinyint(1) NOT NULL DEFAULT '1',
  `ukupno` int(11) NOT NULL,
  `tacnih` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `kviz_pitanje_ibfk_1` (`kviz`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1193 ;

--
-- Truncate table before insert `kviz_pitanje`
--

TRUNCATE TABLE `kviz_pitanje`;
-- --------------------------------------------------------

--
-- Table structure for table `kviz_student`
--

DROP TABLE IF EXISTS `kviz_student`;
CREATE TABLE IF NOT EXISTS `kviz_student` (
  `student` int(11) NOT NULL,
  `kviz` int(11) NOT NULL,
  `dovrsen` tinyint(1) NOT NULL DEFAULT '0',
  `bodova` float NOT NULL,
  `vrijeme_aktivacije` datetime NOT NULL,
  PRIMARY KEY (`student`,`kviz`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `kviz_student`
--

TRUNCATE TABLE `kviz_student`;
-- --------------------------------------------------------

--
-- Table structure for table `labgrupa`
--

DROP TABLE IF EXISTS `labgrupa`;
CREATE TABLE IF NOT EXISTS `labgrupa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `predmet` int(11) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `virtualna` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `labgrupa_ibfk_1` (`predmet`),
  KEY `labgrupa_ibfk_2` (`akademska_godina`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

--
-- Truncate table before insert `labgrupa`
--

TRUNCATE TABLE `labgrupa`;
-- --------------------------------------------------------

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `userid` int(11) NOT NULL DEFAULT '0',
  `dogadjaj` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `nivo` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `log_ibfk_1` (`dogadjaj`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=645 ;

--
-- Truncate table before insert `log`
--

TRUNCATE TABLE `log`;
--
-- Dumping data for table `log`
--

INSERT INTO `log` (`id`, `vrijeme`, `userid`, `dogadjaj`, `nivo`) VALUES
(600, '2015-05-23 16:47:44', 1, 'login', 1),
(601, '2015-05-23 16:47:44', 1, 'SQL greska (C:\\wamp\\www\\zamger\\common\\cron.php : 51): Table ''zamger.cron'' doesn''t exist', 3),
(602, '2015-05-23 17:33:22', 1, '/zamger/index.php?', 1),
(603, '2015-05-23 17:33:27', 1, 'student/intro', 1),
(604, '2015-05-23 17:33:29', 1, 'studentska/intro', 1),
(605, '2015-05-23 17:33:29', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\intro.php : 547): Table ''zamger.zahtjev_za_potvrdu'' doesn''t exist', 3),
(606, '2015-05-23 17:33:39', 1, 'studentska/osobe', 1),
(607, '2015-05-23 17:33:39', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\osobe.php : 2688): Table ''zamger.sifrarnik_naucni_stepen'' doesn''t exist', 3),
(608, '2015-05-23 17:33:44', 1, 'studentska/plan', 1),
(609, '2015-05-23 17:33:46', 1, 'studentska/predmeti', 1),
(610, '2015-05-23 17:33:57', 1, 'studentska/osobe', 1),
(611, '2015-05-23 17:33:57', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\osobe.php : 2688): Table ''zamger.sifrarnik_naucni_stepen'' doesn''t exist', 3),
(612, '2015-05-23 17:34:01', 1, 'admin/intro', 1),
(613, '2015-05-23 17:34:03', 1, 'student/intro', 1),
(614, '2015-05-23 17:34:06', 1, 'studentska/intro', 1),
(615, '2015-05-23 17:34:06', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\intro.php : 547): Table ''zamger.zahtjev_za_potvrdu'' doesn''t exist', 3),
(616, '2015-05-23 17:34:09', 1, 'studentska/osobe', 1),
(617, '2015-05-23 17:34:09', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\osobe.php : 2688): Table ''zamger.sifrarnik_naucni_stepen'' doesn''t exist', 3),
(618, '2015-05-23 18:34:00', 1, '/zamger/index.php?', 1),
(619, '2015-05-23 18:34:05', 1, 'studentska/intro', 1),
(620, '2015-05-23 18:34:05', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\intro.php : 547): Table ''zamger.zahtjev_za_potvrdu'' doesn''t exist', 3),
(621, '2015-05-23 18:34:11', 1, 'studentska/osobe', 1),
(622, '2015-05-23 18:34:11', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\osobe.php : 2688): Table ''zamger.sifrarnik_naucni_stepen'' doesn''t exist', 3),
(623, '2015-05-23 20:14:35', 1, '/zamger/index.php?', 1),
(624, '2015-05-23 20:14:38', 1, 'studentska/intro', 1),
(625, '2015-05-23 20:14:38', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\intro.php : 547): Table ''zamger.zahtjev_za_potvrdu'' doesn''t exist', 3),
(626, '2015-05-23 20:14:40', 1, 'studentska/osobe', 1),
(627, '2015-05-23 20:14:44', 1, 'studentska/obavijest', 1),
(628, '2015-05-23 20:14:45', 1, 'studentska/kreiranje_plana', 1),
(629, '2015-05-23 20:14:48', 1, 'studentska/izvjestaji', 1),
(630, '2015-05-23 20:14:49', 1, 'studentska/intro', 1),
(631, '2015-05-23 20:14:49', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\intro.php : 547): Table ''zamger.zahtjev_za_potvrdu'' doesn''t exist', 3),
(632, '2015-05-23 20:14:54', 1, 'studentska/anketa', 1),
(633, '2015-05-23 20:15:00', 1, 'studentska/zavrsni', 1),
(634, '2015-05-23 20:15:08', 1, 'studentska/raspored1', 1),
(635, '2015-05-23 20:15:10', 1, 'studentska/prodsjeka', 1),
(636, '2015-05-23 20:15:11', 1, 'studentska/prijemni', 1),
(637, '2015-05-23 20:15:17', 1, 'studentska/predmeti', 1),
(638, '2015-05-23 20:15:19', 1, 'studentska/plan', 1),
(639, '2015-05-23 20:15:20', 1, 'studentska/osobe', 1),
(640, '2015-05-23 20:15:21', 1, 'studentska/obavijest', 1),
(641, '2015-05-23 20:15:24', 1, 'studentska/kreiranje_plana', 1),
(642, '2015-05-23 20:15:28', 1, 'studentska/izvjestaji', 1),
(643, '2015-05-23 20:15:30', 1, 'studentska/intro', 1),
(644, '2015-05-23 20:15:30', 1, 'SQL greska (C:\\wamp\\www\\zamger\\studentska\\intro.php : 547): Table ''zamger.zahtjev_za_potvrdu'' doesn''t exist', 3);

-- --------------------------------------------------------

--
-- Table structure for table `mjesto`
--

DROP TABLE IF EXISTS `mjesto`;
CREATE TABLE IF NOT EXISTS `mjesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(40) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `drzava` int(11) NOT NULL,
  `opcina_van_bih` varchar(40) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mjesto_ibfk_1` (`opcina`),
  KEY `mjesto_ibfk_2` (`drzava`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=79 ;

--
-- Truncate table before insert `mjesto`
--

TRUNCATE TABLE `mjesto`;
--
-- Dumping data for table `mjesto`
--

INSERT INTO `mjesto` (`id`, `naziv`, `opcina`, `drzava`, `opcina_van_bih`) VALUES
(1, 'Sarajevo', 0, 1, ''),
(2, 'Sarajevo', 13, 1, ''),
(3, 'Zenica', 77, 1, ''),
(4, 'Mostar', 46, 1, ''),
(5, 'Banja Luka', 93, 1, ''),
(6, 'Bihać', 2, 1, ''),
(7, 'Tuzla', 69, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `moodle_predmet_id`
--

DROP TABLE IF EXISTS `moodle_predmet_id`;
CREATE TABLE IF NOT EXISTS `moodle_predmet_id` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `moodle_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `moodle_predmet_id`
--

TRUNCATE TABLE `moodle_predmet_id`;
-- --------------------------------------------------------

--
-- Table structure for table `moodle_predmet_rss`
--

DROP TABLE IF EXISTS `moodle_predmet_rss`;
CREATE TABLE IF NOT EXISTS `moodle_predmet_rss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vrstanovosti` int(2) NOT NULL,
  `moodle_id` int(11) NOT NULL,
  `sadrzaj` text COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme_promjene` bigint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

--
-- Truncate table before insert `moodle_predmet_rss`
--

TRUNCATE TABLE `moodle_predmet_rss`;
-- --------------------------------------------------------

--
-- Table structure for table `nacin_studiranja`
--

DROP TABLE IF EXISTS `nacin_studiranja`;
CREATE TABLE IF NOT EXISTS `nacin_studiranja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `moguc_upis` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=5 ;

--
-- Truncate table before insert `nacin_studiranja`
--

TRUNCATE TABLE `nacin_studiranja`;
--
-- Dumping data for table `nacin_studiranja`
--

INSERT INTO `nacin_studiranja` (`id`, `naziv`, `moguc_upis`) VALUES
(1, 'Redovan', 1),
(2, 'Paralelan', 0),
(3, 'Redovan samofinansirajući', 1),
(0, 'Nepoznat status', 0),
(4, 'Vanredan', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nacionalnost`
--

DROP TABLE IF EXISTS `nacionalnost`;
CREATE TABLE IF NOT EXISTS `nacionalnost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

--
-- Truncate table before insert `nacionalnost`
--

TRUNCATE TABLE `nacionalnost`;
--
-- Dumping data for table `nacionalnost`
--

INSERT INTO `nacionalnost` (`id`, `naziv`) VALUES
(1, 'Bošnjak/Bošnjakinja'),
(2, 'Srbin/Srpkinja'),
(3, 'Hrvat/Hrvatica'),
(4, 'Rom/Romkinja'),
(5, 'Ostalo'),
(6, 'Nepoznato / Nije se izjasnio/la');

-- --------------------------------------------------------

--
-- Table structure for table `nastavnik_predmet`
--

DROP TABLE IF EXISTS `nastavnik_predmet`;
CREATE TABLE IF NOT EXISTS `nastavnik_predmet` (
  `nastavnik` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `nivo_pristupa` enum('nastavnik','super_asistent','asistent') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'asistent',
  PRIMARY KEY (`nastavnik`,`akademska_godina`,`predmet`),
  KEY `nastavnik_predmet_ibfk_2` (`akademska_godina`),
  KEY `nastavnik_predmet_ibfk_3` (`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `nastavnik_predmet`
--

TRUNCATE TABLE `nastavnik_predmet`;
-- --------------------------------------------------------

--
-- Table structure for table `odluka`
--

DROP TABLE IF EXISTS `odluka`;
CREATE TABLE IF NOT EXISTS `odluka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `broj_protokola` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `student` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `odluka`
--

TRUNCATE TABLE `odluka`;
-- --------------------------------------------------------

--
-- Table structure for table `ogranicenje`
--

DROP TABLE IF EXISTS `ogranicenje`;
CREATE TABLE IF NOT EXISTS `ogranicenje` (
  `nastavnik` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0',
  `zavrsnirad` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `ogranicenje`
--

TRUNCATE TABLE `ogranicenje`;
-- --------------------------------------------------------

--
-- Table structure for table `opcina`
--

DROP TABLE IF EXISTS `opcina`;
CREATE TABLE IF NOT EXISTS `opcina` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=145 ;

--
-- Truncate table before insert `opcina`
--

TRUNCATE TABLE `opcina`;
--
-- Dumping data for table `opcina`
--

INSERT INTO `opcina` (`id`, `naziv`) VALUES
(1, 'Banovići'),
(2, 'Bihać'),
(3, 'Bosanska Krupa'),
(4, 'Bosanski Petrovac'),
(5, 'Bosansko Grahovo'),
(6, 'Breza'),
(7, 'Bugojno'),
(8, 'Busovača'),
(9, 'Bužim'),
(10, 'Čapljina'),
(11, 'Cazin'),
(12, 'Čelić'),
(13, 'Centar, Sarajevo'),
(14, 'Čitluk'),
(15, 'Drvar'),
(16, 'Doboj Istok'),
(17, 'Doboj Jug'),
(18, 'Dobretići'),
(19, 'Domaljevac-Šamac'),
(20, 'Donji Vakuf'),
(21, 'Foča-Ustikolina'),
(22, 'Fojnica'),
(23, 'Glamoč'),
(24, 'Goražde'),
(25, 'Gornji Vakuf-Uskoplje'),
(26, 'Gračanica'),
(27, 'Gradačac'),
(28, 'Grude'),
(29, 'Hadžići'),
(30, 'Ilidža'),
(31, 'Ilijaš'),
(32, 'Jablanica'),
(33, 'Jajce'),
(34, 'Kakanj'),
(35, 'Kalesija'),
(36, 'Kiseljak'),
(37, 'Kladanj'),
(38, 'Ključ'),
(39, 'Konjic'),
(40, 'Kreševo'),
(41, 'Kupres'),
(42, 'Livno'),
(43, 'Ljubuški'),
(44, 'Lukavac'),
(45, 'Maglaj'),
(46, 'Mostar'),
(47, 'Neum'),
(48, 'Novi Grad, Sarajevo'),
(49, 'Novo Sarajevo'),
(50, 'Novi Travnik'),
(51, 'Odžak'),
(52, 'Olovo'),
(53, 'Orašje'),
(54, 'Pale-Prača'),
(55, 'Posušje'),
(56, 'Prozor-Rama'),
(57, 'Ravno'),
(58, 'Sanski Most'),
(59, 'Sapna'),
(60, 'Široki Brijeg'),
(61, 'Srebrenik'),
(62, 'Stari Grad, Sarajevo'),
(63, 'Stolac'),
(64, 'Teočak'),
(65, 'Tešanj'),
(66, 'Tomislavgrad'),
(67, 'Travnik'),
(68, 'Trnovo (FBiH)'),
(69, 'Tuzla'),
(70, 'Usora'),
(71, 'Vareš'),
(72, 'Velika Kladuša'),
(73, 'Visoko'),
(74, 'Vitez'),
(75, 'Vogošća'),
(76, 'Zavidovići'),
(77, 'Zenica'),
(78, 'Žepče'),
(79, 'Živinice'),
(80, 'Berkovići'),
(81, 'Bijeljina'),
(82, 'Bileća'),
(83, 'Bosanska Kostajnica'),
(84, 'Bosanski Brod'),
(85, 'Bratunac'),
(86, 'Čajniče'),
(87, 'Čelinac'),
(88, 'Derventa'),
(89, 'Doboj'),
(90, 'Donji Žabar'),
(91, 'Foča'),
(92, 'Gacko'),
(93, 'Banja Luka'),
(94, 'Gradiška'),
(95, 'Han Pijesak'),
(96, 'Istočni Drvar'),
(97, 'Istočna Ilidža'),
(98, 'Istočni Mostar'),
(99, 'Istočni Stari Grad'),
(100, 'Istočno Novo Sarajevo'),
(101, 'Jezero'),
(102, 'Kalinovik'),
(103, 'Kneževo'),
(104, 'Kozarska Dubica'),
(105, 'Kotor Varoš'),
(106, 'Krupa na Uni'),
(107, 'Kupres (RS)'),
(108, 'Laktaši'),
(109, 'Ljubinje'),
(110, 'Lopare'),
(111, 'Milići'),
(112, 'Modriča'),
(113, 'Mrkonjić Grad'),
(114, 'Nevesinje'),
(115, 'Novi Grad (RS)'),
(116, 'Novo Goražde'),
(117, 'Osmaci'),
(118, 'Oštra Luka'),
(119, 'Pale'),
(120, 'Pelagićevo'),
(121, 'Petrovac'),
(122, 'Petrovo'),
(123, 'Prijedor'),
(124, 'Prnjavor'),
(125, 'Ribnik'),
(126, 'Rogatica'),
(127, 'Rudo'),
(128, 'Šamac'),
(129, 'Šekovići'),
(130, 'Šipovo'),
(131, 'Sokolac'),
(132, 'Srbac'),
(133, 'Srebrenica'),
(134, 'Teslić'),
(135, 'Trebinje'),
(136, 'Trnovo (RS)'),
(137, 'Ugljevik'),
(138, 'Višegrad'),
(139, 'Vlasenica'),
(140, 'Vukosavlje'),
(141, 'Zvornik'),
(142, 'Brčko'),
(143, '(nije u BiH)');

-- --------------------------------------------------------

--
-- Table structure for table `osoba`
--

DROP TABLE IF EXISTS `osoba`;
CREATE TABLE IF NOT EXISTS `osoba` (
  `id` int(11) NOT NULL,
  `ime` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `prezime` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `imeoca` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `prezimeoca` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `imemajke` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `prezimemajke` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `spol` enum('M','Z','') COLLATE utf8_slovenian_ci NOT NULL,
  `brindexa` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `datum_rodjenja` date NOT NULL,
  `mjesto_rodjenja` int(11) NOT NULL,
  `nacionalnost` int(11) NOT NULL,
  `drzavljanstvo` int(11) NOT NULL,
  `boracke_kategorije` tinyint(1) NOT NULL,
  `jmbg` varchar(14) COLLATE utf8_slovenian_ci NOT NULL,
  `adresa` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `adresa_mjesto` int(11) NOT NULL,
  `telefon` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `kanton` int(11) NOT NULL,
  `treba_brisati` tinyint(1) NOT NULL DEFAULT '0',
  `fk_akademsko_zvanje` int(11) NOT NULL,
  `fk_naucni_stepen` int(11) NOT NULL,
  `slika` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `djevojacko_prezime` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `maternji_jezik` int(11) NOT NULL,
  `vozacka_dozvola` int(11) NOT NULL,
  `nacin_stanovanja` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `osoba_ibfk_3` (`mjesto_rodjenja`),
  KEY `osoba_ibfk_4` (`adresa_mjesto`),
  KEY `osoba_ibfk_5` (`kanton`),
  KEY `osoba_ibfk_6` (`nacionalnost`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `osoba`
--

TRUNCATE TABLE `osoba`;
--
-- Dumping data for table `osoba`
--

INSERT INTO `osoba` (`id`, `ime`, `prezime`, `imeoca`, `prezimeoca`, `imemajke`, `prezimemajke`, `spol`, `brindexa`, `datum_rodjenja`, `mjesto_rodjenja`, `nacionalnost`, `drzavljanstvo`, `boracke_kategorije`, `jmbg`, `adresa`, `adresa_mjesto`, `telefon`, `kanton`, `treba_brisati`, `fk_akademsko_zvanje`, `fk_naucni_stepen`, `slika`, `djevojacko_prezime`, `maternji_jezik`, `vozacka_dozvola`, `nacin_stanovanja`) VALUES
(1, 'Site', 'Admin', '', '', '', '', 'M', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, '', '', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `plan_studija`
--

DROP TABLE IF EXISTS `plan_studija`;
CREATE TABLE IF NOT EXISTS `plan_studija` (
  `godina_vazenja` int(11) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `obavezan` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `plan_studija`
--

TRUNCATE TABLE `plan_studija`;
-- --------------------------------------------------------

--
-- Table structure for table `ponudakursa`
--

DROP TABLE IF EXISTS `ponudakursa`;
CREATE TABLE IF NOT EXISTS `ponudakursa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `predmet` int(11) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `obavezan` tinyint(1) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ponudakursa_ibfk_1` (`predmet`),
  KEY `ponudakursa_ibfk_2` (`studij`),
  KEY `ponudakursa_ibfk_3` (`akademska_godina`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=12 ;

--
-- Truncate table before insert `ponudakursa`
--

TRUNCATE TABLE `ponudakursa`;
-- --------------------------------------------------------

--
-- Table structure for table `poruka`
--

DROP TABLE IF EXISTS `poruka`;
CREATE TABLE IF NOT EXISTS `poruka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tip` tinyint(4) NOT NULL,
  `opseg` tinyint(4) NOT NULL,
  `primalac` int(11) NOT NULL,
  `posiljalac` int(11) NOT NULL,
  `vrijeme` datetime NOT NULL,
  `ref` int(11) NOT NULL DEFAULT '0',
  `naslov` text COLLATE utf8_slovenian_ci NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `poruka`
--

TRUNCATE TABLE `poruka`;
-- --------------------------------------------------------

--
-- Table structure for table `predmet`
--

DROP TABLE IF EXISTS `predmet`;
CREATE TABLE IF NOT EXISTS `predmet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sifra` varchar(20) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `institucija` int(11) NOT NULL DEFAULT '0',
  `kratki_naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  `ects` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

--
-- Truncate table before insert `predmet`
--

TRUNCATE TABLE `predmet`;
-- --------------------------------------------------------

--
-- Table structure for table `predmet_projektni_parametri`
--

DROP TABLE IF EXISTS `predmet_projektni_parametri`;
CREATE TABLE IF NOT EXISTS `predmet_projektni_parametri` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `min_timova` tinyint(3) NOT NULL,
  `max_timova` tinyint(3) NOT NULL,
  `min_clanova_tima` tinyint(3) NOT NULL,
  `max_clanova_tima` tinyint(3) NOT NULL,
  `zakljucani_projekti` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`predmet`,`akademska_godina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `predmet_projektni_parametri`
--

TRUNCATE TABLE `predmet_projektni_parametri`;
-- --------------------------------------------------------

--
-- Table structure for table `preference`
--

DROP TABLE IF EXISTS `preference`;
CREATE TABLE IF NOT EXISTS `preference` (
  `korisnik` int(11) NOT NULL,
  `preferenca` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijednost` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`korisnik`,`preferenca`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `preference`
--

TRUNCATE TABLE `preference`;
-- --------------------------------------------------------

--
-- Table structure for table `prijemni_obrazac`
--

DROP TABLE IF EXISTS `prijemni_obrazac`;
CREATE TABLE IF NOT EXISTS `prijemni_obrazac` (
  `prijemni_termin` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `sifra` varchar(6) COLLATE utf8_slovenian_ci NOT NULL,
  `jezik` enum('bs','en') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'bs'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `prijemni_obrazac`
--

TRUNCATE TABLE `prijemni_obrazac`;
-- --------------------------------------------------------

--
-- Table structure for table `prijemni_prijava`
--

DROP TABLE IF EXISTS `prijemni_prijava`;
CREATE TABLE IF NOT EXISTS `prijemni_prijava` (
  `prijemni_termin` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `broj_dosjea` int(11) NOT NULL,
  `nacin_studiranja` tinyint(1) NOT NULL DEFAULT '1',
  `studij_prvi` int(11) NOT NULL,
  `studij_drugi` int(11) NOT NULL,
  `studij_treci` int(11) NOT NULL,
  `studij_cetvrti` int(11) NOT NULL,
  `izasao` tinyint(1) NOT NULL,
  `rezultat` double NOT NULL,
  PRIMARY KEY (`prijemni_termin`,`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `prijemni_prijava`
--

TRUNCATE TABLE `prijemni_prijava`;
-- --------------------------------------------------------

--
-- Table structure for table `prijemni_termin`
--

DROP TABLE IF EXISTS `prijemni_termin`;
CREATE TABLE IF NOT EXISTS `prijemni_termin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `akademska_godina` int(11) NOT NULL,
  `datum` date NOT NULL,
  `ciklus_studija` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Truncate table before insert `prijemni_termin`
--

TRUNCATE TABLE `prijemni_termin`;
-- --------------------------------------------------------

--
-- Table structure for table `prisustvo`
--

DROP TABLE IF EXISTS `prisustvo`;
CREATE TABLE IF NOT EXISTS `prisustvo` (
  `student` int(11) NOT NULL DEFAULT '0',
  `cas` int(11) NOT NULL DEFAULT '0',
  `prisutan` tinyint(1) NOT NULL DEFAULT '0',
  `plus_minus` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`student`,`cas`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `prisustvo`
--

TRUNCATE TABLE `prisustvo`;
-- --------------------------------------------------------

--
-- Table structure for table `privilegije`
--

DROP TABLE IF EXISTS `privilegije`;
CREATE TABLE IF NOT EXISTS `privilegije` (
  `osoba` int(11) NOT NULL,
  `privilegija` varchar(30) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `privilegije`
--

TRUNCATE TABLE `privilegije`;
--
-- Dumping data for table `privilegije`
--

INSERT INTO `privilegije` (`osoba`, `privilegija`) VALUES
(1, 'siteadmin'),
(1, 'studentska'),
(1, 'student'),
(1, 'nastavnik');

-- --------------------------------------------------------

--
-- Table structure for table `programskijezik`
--

DROP TABLE IF EXISTS `programskijezik`;
CREATE TABLE IF NOT EXISTS `programskijezik` (
  `id` int(10) NOT NULL DEFAULT '0',
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `geshi` varchar(20) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `ekstenzija` varchar(10) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `programskijezik`
--

TRUNCATE TABLE `programskijezik`;
--
-- Dumping data for table `programskijezik`
--

INSERT INTO `programskijezik` (`id`, `naziv`, `geshi`, `ekstenzija`) VALUES
(0, '--Nije odredjen--', '', ''),
(1, 'C', 'C', '.c'),
(2, 'C++', 'C++', '.cpp');

-- --------------------------------------------------------

--
-- Table structure for table `projekat`
--

DROP TABLE IF EXISTS `projekat`;
CREATE TABLE IF NOT EXISTS `projekat` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `opis` text COLLATE utf8_slovenian_ci NOT NULL,
  `biljeska` text COLLATE utf8_slovenian_ci,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `projekat`
--

TRUNCATE TABLE `projekat`;
-- --------------------------------------------------------

--
-- Table structure for table `projekat_file`
--

DROP TABLE IF EXISTS `projekat_file`;
CREATE TABLE IF NOT EXISTS `projekat_file` (
  `id` int(11) NOT NULL,
  `filename` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revizija` tinyint(4) NOT NULL,
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  `file` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `projekat_file`
--

TRUNCATE TABLE `projekat_file`;
-- --------------------------------------------------------

--
-- Table structure for table `projekat_file_diff`
--

DROP TABLE IF EXISTS `projekat_file_diff`;
CREATE TABLE IF NOT EXISTS `projekat_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `projekat_file_diff`
--

TRUNCATE TABLE `projekat_file_diff`;
-- --------------------------------------------------------

--
-- Table structure for table `projekat_link`
--

DROP TABLE IF EXISTS `projekat_link`;
CREATE TABLE IF NOT EXISTS `projekat_link` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `url` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `opis` text COLLATE utf8_slovenian_ci NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `projekat_link`
--

TRUNCATE TABLE `projekat_link`;
-- --------------------------------------------------------

--
-- Table structure for table `projekat_rss`
--

DROP TABLE IF EXISTS `projekat_rss`;
CREATE TABLE IF NOT EXISTS `projekat_rss` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `url` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `opis` text COLLATE utf8_slovenian_ci NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `projekat_rss`
--

TRUNCATE TABLE `projekat_rss`;
-- --------------------------------------------------------

--
-- Table structure for table `promjena_odsjeka`
--

DROP TABLE IF EXISTS `promjena_odsjeka`;
CREATE TABLE IF NOT EXISTS `promjena_odsjeka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osoba` int(11) NOT NULL,
  `iz_odsjeka` int(11) NOT NULL,
  `u_odsjek` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `promjena_odsjeka`
--

TRUNCATE TABLE `promjena_odsjeka`;
-- --------------------------------------------------------

--
-- Table structure for table `promjena_podataka`
--

DROP TABLE IF EXISTS `promjena_podataka`;
CREATE TABLE IF NOT EXISTS `promjena_podataka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osoba` int(11) NOT NULL,
  `ime` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `prezime` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `imeoca` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `prezimeoca` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `imemajke` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `prezimemajke` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `spol` enum('M','Z','','') COLLATE utf8_slovenian_ci NOT NULL,
  `brindexa` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `datum_rodjenja` date NOT NULL,
  `mjesto_rodjenja` int(11) NOT NULL,
  `nacionalnost` int(11) NOT NULL,
  `drzavljanstvo` int(11) NOT NULL,
  `boracke_kategorije` tinyint(1) NOT NULL,
  `jmbg` varchar(14) COLLATE utf8_slovenian_ci NOT NULL,
  `adresa` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `adresa_mjesto` int(11) NOT NULL,
  `telefon` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `kanton` int(11) NOT NULL,
  `fk_akademsko_zvanje` int(11) NOT NULL DEFAULT '5',
  `fk_naucni_stepen` int(11) NOT NULL DEFAULT '6',
  `slika` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `djevojacko_prezime` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `maternji_jezik` int(11) NOT NULL,
  `vozacka_dozvola` int(11) NOT NULL,
  `mobilni_telefon` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `nacin_stanovanja` int(11) NOT NULL,
  `vrijeme_zahtjeva` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `promjena_podataka`
--

TRUNCATE TABLE `promjena_podataka`;
-- --------------------------------------------------------

--
-- Table structure for table `prosliciklus_ocjene`
--

DROP TABLE IF EXISTS `prosliciklus_ocjene`;
CREATE TABLE IF NOT EXISTS `prosliciklus_ocjene` (
  `osoba` int(11) NOT NULL,
  `redni_broj` int(11) NOT NULL,
  `ocjena` tinyint(5) NOT NULL,
  `ects` float NOT NULL,
  PRIMARY KEY (`osoba`,`redni_broj`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `prosliciklus_ocjene`
--

TRUNCATE TABLE `prosliciklus_ocjene`;
-- --------------------------------------------------------

--
-- Table structure for table `prosliciklus_uspjeh`
--

DROP TABLE IF EXISTS `prosliciklus_uspjeh`;
CREATE TABLE IF NOT EXISTS `prosliciklus_uspjeh` (
  `osoba` int(11) NOT NULL,
  `fakultet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `broj_semestara` int(11) NOT NULL,
  `opci_uspjeh` double NOT NULL,
  `dodatni_bodovi` double NOT NULL,
  PRIMARY KEY (`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `prosliciklus_uspjeh`
--

TRUNCATE TABLE `prosliciklus_uspjeh`;
-- --------------------------------------------------------

--
-- Table structure for table `raspored`
--

DROP TABLE IF EXISTS `raspored`;
CREATE TABLE IF NOT EXISTS `raspored` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studij` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

--
-- Truncate table before insert `raspored`
--

TRUNCATE TABLE `raspored`;
-- --------------------------------------------------------

--
-- Table structure for table `raspored_sala`
--

DROP TABLE IF EXISTS `raspored_sala`;
CREATE TABLE IF NOT EXISTS `raspored_sala` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `kapacitet` int(5) DEFAULT NULL,
  `tip` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `raspored_sala`
--

TRUNCATE TABLE `raspored_sala`;
-- --------------------------------------------------------

--
-- Table structure for table `raspored_stavka`
--

DROP TABLE IF EXISTS `raspored_stavka`;
CREATE TABLE IF NOT EXISTS `raspored_stavka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `raspored` int(11) NOT NULL,
  `dan_u_sedmici` tinyint(1) NOT NULL,
  `predmet` int(11) NOT NULL,
  `vrijeme_pocetak` int(11) NOT NULL,
  `vrijeme_kraj` int(11) NOT NULL,
  `sala` int(11) NOT NULL,
  `tip` varchar(1) CHARACTER SET latin1 NOT NULL DEFAULT 'P',
  `labgrupa` int(11) NOT NULL,
  `dupla` int(11) NOT NULL DEFAULT '0',
  `isjeckana` tinyint(1) NOT NULL DEFAULT '0',
  `fini_pocetak` time NOT NULL,
  `fini_kraj` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `raspored_stavka`
--

TRUNCATE TABLE `raspored_stavka`;
-- --------------------------------------------------------

--
-- Table structure for table `ras_sati`
--

DROP TABLE IF EXISTS `ras_sati`;
CREATE TABLE IF NOT EXISTS `ras_sati` (
  `idS` tinyint(1) NOT NULL AUTO_INCREMENT,
  `satS` varchar(13) NOT NULL,
  PRIMARY KEY (`idS`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Truncate table before insert `ras_sati`
--

TRUNCATE TABLE `ras_sati`;
--
-- Dumping data for table `ras_sati`
--

INSERT INTO `ras_sati` (`idS`, `satS`) VALUES
(1, '09:00'),
(2, '10:00'),
(3, '11:00'),
(4, '12:00'),
(5, '13:00'),
(6, '14:00'),
(7, '15:00'),
(8, '16:00'),
(9, '17:00'),
(10, '18:00');

-- --------------------------------------------------------

--
-- Table structure for table `rss`
--

DROP TABLE IF EXISTS `rss`;
CREATE TABLE IF NOT EXISTS `rss` (
  `id` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `auth` int(11) NOT NULL,
  `access` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `rss`
--

TRUNCATE TABLE `rss`;
--
-- Dumping data for table `rss`
--

INSERT INTO `rss` (`id`, `auth`, `access`) VALUES
('b4h4VAbsxe', 1, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `savjet_dana`
--

DROP TABLE IF EXISTS `savjet_dana`;
CREATE TABLE IF NOT EXISTS `savjet_dana` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  `vrsta_korisnika` enum('nastavnik','student','studentska','siteadmin') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'nastavnik',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=21 ;

--
-- Truncate table before insert `savjet_dana`
--

TRUNCATE TABLE `savjet_dana`;
-- --------------------------------------------------------

--
-- Table structure for table `septembar`
--

DROP TABLE IF EXISTS `septembar`;
CREATE TABLE IF NOT EXISTS `septembar` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `septembar`
--

TRUNCATE TABLE `septembar`;
-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_akademsko_zvanje`
--

DROP TABLE IF EXISTS `sifrarnik_akademsko_zvanje`;
CREATE TABLE IF NOT EXISTS `sifrarnik_akademsko_zvanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `izborni_perion` int(11) NOT NULL,
  `period_reizbora` int(11) NOT NULL,
  `skracenica` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

--
-- Truncate table before insert `sifrarnik_akademsko_zvanje`
--

TRUNCATE TABLE `sifrarnik_akademsko_zvanje`;
--
-- Dumping data for table `sifrarnik_akademsko_zvanje`
--

INSERT INTO `sifrarnik_akademsko_zvanje` (`id`, `naziv`, `izborni_perion`, `period_reizbora`, `skracenica`, `titula`) VALUES
(5, 'srednja stručna sprema', 0, 0, '', ''),
(4, 'diplomirani matematičar', 0, 0, '', 'dipl.mat.'),
(3, 'diplomirani inženjer elektrotehnike', 0, 0, '', 'dipl.ing.el.'),
(2, 'bakalaureat elektrotehnike - inženjer elektrotehnike', 0, 0, '', 'B.E.'),
(1, 'magistar elektrotehnike - diplomirani inženjer elektrotehnike', 0, 0, '', 'M.E.'),
(6, 'diplomirani inženjer mašinstva', 0, 0, '', 'dipl.ing.'),
(7, 'diplomirani inženjer građevinarstva', 0, 0, '', 'dipl.ing.'),
(8, 'diplomirani ekonomista', 0, 0, '', 'dipl.ecc.');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_banka`
--

DROP TABLE IF EXISTS `sifrarnik_banka`;
CREATE TABLE IF NOT EXISTS `sifrarnik_banka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=214 ;

--
-- Truncate table before insert `sifrarnik_banka`
--

TRUNCATE TABLE `sifrarnik_banka`;
--
-- Dumping data for table `sifrarnik_banka`
--

INSERT INTO `sifrarnik_banka` (`id`, `naziv`) VALUES
(1, 'ABS Banka Sarajevo'),
(200, 'Balkan Investment Bank Banja Luka'),
(202, 'Bobar banka dd Bijeljina'),
(102, 'BOR banka Sarajevo 12'),
(101, 'Bosna bank international d.d. Sarajevo'),
(105, 'Hypo Alpe-Adria-Bank d.d. Mostar'),
(123, 'Hypo-Alpe-Adria Bank d.d. Sarajevo'),
(211, 'INTESA SAN PAOLO BANKA d.d. BiH'),
(108, 'Investiciono-komercijalna banka d.d. Zenica'),
(110, 'Komercijalno-investiciona banka d.d. V.Kladuša'),
(109, 'NLB - Tuzlanska banka'),
(205, 'Nova banka ad Bijeljina'),
(206, 'Pavlović International Bank a.d. Bijeljina'),
(113, 'Privredna banka Sarajevo d.d. Sarajevo'),
(112, 'ProCredit bank'),
(212, 'ProCredit bank BiH'),
(111, 'Raiffeisen Bank d.d. BiH Sarajevo'),
(213, 'Sparkasse bank dd BiH'),
(114, 'Turkish Ziraat Bank Bosnia d.d. Sarajevo'),
(201, 'UniCredit Bank Banja Luka'),
(210, 'UniCredit bank dd BiH'),
(116, 'Union banka d.d. Sarajevo'),
(119, 'Vakufska banka d.d. Sarajevo'),
(120, 'Volksbank BiH d.d. Sarajevo');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_fakulteti`
--

DROP TABLE IF EXISTS `sifrarnik_fakulteti`;
CREATE TABLE IF NOT EXISTS `sifrarnik_fakulteti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=28 ;

--
-- Truncate table before insert `sifrarnik_fakulteti`
--

TRUNCATE TABLE `sifrarnik_fakulteti`;
--
-- Dumping data for table `sifrarnik_fakulteti`
--

INSERT INTO `sifrarnik_fakulteti` (`id`, `naziv`) VALUES
(1, 'Elektrotehnički fakultet u Sarajevu'),
(2, 'Ekonomski fakultet'),
(3, 'Prirodno-matematički fakultet'),
(4, 'Akademija scenskih umjetnosti'),
(5, 'Fakultet sporta i tjelesnog odgoja'),
(6, 'Medicinski fakultet'),
(7, 'Filozofski fakultet u Sarajevu'),
(8, 'Stomatološki fakultet sa klinikama'),
(9, 'Mašinski fakultet Sarajevo'),
(10, 'Fakultet političkih nauka'),
(11, 'Akademija likovnih umjetnosti'),
(12, 'Arhitektonski fakultet'),
(13, 'Farmaceutski fakultet'),
(14, 'Građevinski fakultet u Sarajevu'),
(15, 'Muzička akademija'),
(16, 'Poljoprivredno-prehrambeni fakultet'),
(17, 'Pravni fakultet'),
(18, 'Fakultet za saobraćaj i komunikacije'),
(19, 'Šumarski fakultet'),
(20, 'Veterinarski fakultet'),
(21, 'Fakultet zdravstvenih studija'),
(22, 'Fakultet za kriminalistiku, kriminologiju i sigurnosne studije'),
(23, 'Pedagoški fakultet'),
(24, 'Fakultet islamskih nauka'),
(25, 'Centar za interdisciplinarne postdiplomske studije'),
(26, 'Rektorat Univerziteta u Sarajevu'),
(27, 'Drugi');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_fascati`
--

DROP TABLE IF EXISTS `sifrarnik_fascati`;
CREATE TABLE IF NOT EXISTS `sifrarnik_fascati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=351 ;

--
-- Truncate table before insert `sifrarnik_fascati`
--

TRUNCATE TABLE `sifrarnik_fascati`;
--
-- Dumping data for table `sifrarnik_fascati`
--

INSERT INTO `sifrarnik_fascati` (`id`, `naziv`) VALUES
(320, 'Robotika i automatska kontrola'),
(330, 'Automatika i kontrolni sistem'),
(340, 'Komunikacije i sistemi'),
(342, 'Telekomunikacije'),
(344, 'Računarske i informacione nauke'),
(350, 'Kompjutorski hardware i arhitekture');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_fascati_podoblast`
--

DROP TABLE IF EXISTS `sifrarnik_fascati_podoblast`;
CREATE TABLE IF NOT EXISTS `sifrarnik_fascati_podoblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_fascati` int(11) NOT NULL,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `sifrarnik_fascati_podoblast`
--

TRUNCATE TABLE `sifrarnik_fascati_podoblast`;
-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_jezik`
--

DROP TABLE IF EXISTS `sifrarnik_jezik`;
CREATE TABLE IF NOT EXISTS `sifrarnik_jezik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=19 ;

--
-- Truncate table before insert `sifrarnik_jezik`
--

TRUNCATE TABLE `sifrarnik_jezik`;
--
-- Dumping data for table `sifrarnik_jezik`
--

INSERT INTO `sifrarnik_jezik` (`id`, `naziv`) VALUES
(1, 'Bosanski jezik'),
(2, 'Hrvatski jezik'),
(3, 'Srpski jezik'),
(4, 'Engleski jezik'),
(5, 'Bosanski/hrvatski/srpski jezik'),
(6, 'Slovenski jezik'),
(7, 'Francuski jezik'),
(8, 'Turski jezik'),
(9, 'Perzijski jezik'),
(10, 'Mađarski jezik'),
(11, 'Makedonski jezik'),
(12, 'Bugarski jezik'),
(13, 'Talijanski jezik'),
(14, 'Španski jezik'),
(15, 'Njemački jezik'),
(16, 'Esperanto jezik'),
(17, 'Ruski jezik'),
(18, 'Latinski jezik');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_nacin_stanovanja`
--

DROP TABLE IF EXISTS `sifrarnik_nacin_stanovanja`;
CREATE TABLE IF NOT EXISTS `sifrarnik_nacin_stanovanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10 ;

--
-- Truncate table before insert `sifrarnik_nacin_stanovanja`
--

TRUNCATE TABLE `sifrarnik_nacin_stanovanja`;
--
-- Dumping data for table `sifrarnik_nacin_stanovanja`
--

INSERT INTO `sifrarnik_nacin_stanovanja` (`id`, `naziv`) VALUES
(1, 'u vlastitom stanu'),
(2, 'u vlastitoj kući'),
(3, 'podstanar'),
(4, 'kolektivni smještaj'),
(5, 'privremeni smještaj'),
(6, 'u hotelu'),
(7, 'u specijaliziranoj ustanovi'),
(8, 'drugo'),
(9, 'kod roditelja');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_naucna_oblast`
--

DROP TABLE IF EXISTS `sifrarnik_naucna_oblast`;
CREATE TABLE IF NOT EXISTS `sifrarnik_naucna_oblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  `fk_maticna_institucija` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=359 ;

--
-- Truncate table before insert `sifrarnik_naucna_oblast`
--

TRUNCATE TABLE `sifrarnik_naucna_oblast`;
--
-- Dumping data for table `sifrarnik_naucna_oblast`
--

INSERT INTO `sifrarnik_naucna_oblast` (`id`, `naziv`, `fk_maticna_institucija`) VALUES
(302, 'Automatsko upravljanje', 0),
(304, 'Industrijska i procesna automatika', 0),
(306, 'Robotika i mehatronika', 0),
(308, 'Zaštita i upravljanje elektroenergetskim sistemima', 0),
(310, 'Sistemi i ekonomski inženjering u elektrotehnici', 0),
(312, 'Elektroničke komponenete i sistemi', 0),
(314, 'Digitalne strukture i obrada signala', 0),
(316, 'Bimedicinska elektronika', 0),
(318, 'Elektroenergetski sistemi', 0),
(320, 'Eolektroenergetska tehnologija', 0),
(322, 'Industrijska elektroenergetika', 0),
(324, 'Energija i okolina', 0),
(326, 'Teoretska elektrotehnika', 0),
(328, 'Arhitektura računarskih sistema i mreža', 0),
(330, 'Računarski informacioni sistemi', 0),
(332, 'Računarske nauke i obrada informacija', 0),
(334, 'Softver inžinjering', 0),
(336, 'Vještačka inteligencija i bioinformatika', 0),
(338, 'Matematske metode u računarstvu i informatici', 0),
(340, 'Tehnička informatika i procesno računarstvo', 0),
(342, 'Teorija telekomunikacija', 0),
(344, 'Telekomunikacijske tehnike', 0),
(346, 'Računarske i telekomunikacijske mreže', 0),
(348, 'Bežične telekomunikacije', 0),
(350, 'Automatika', 0),
(352, 'Elektronika', 0),
(354, 'Elektroenergetika', 0),
(356, 'Računarstvo i informatika', 0),
(358, 'Telekomunikacije', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_naucni_stepen`
--

DROP TABLE IF EXISTS `sifrarnik_naucni_stepen`;
CREATE TABLE IF NOT EXISTS `sifrarnik_naucni_stepen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Truncate table before insert `sifrarnik_naucni_stepen`
--

TRUNCATE TABLE `sifrarnik_naucni_stepen`;
--
-- Dumping data for table `sifrarnik_naucni_stepen`
--

INSERT INTO `sifrarnik_naucni_stepen` (`id`, `naziv`, `titula`) VALUES
(1, 'doktor nauka', 'dr'),
(2, 'magistar nauka', 'mr'),
(6, 'bez naučnog stepena', '');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_naucnonastavno_zvanje`
--

DROP TABLE IF EXISTS `sifrarnik_naucnonastavno_zvanje`;
CREATE TABLE IF NOT EXISTS `sifrarnik_naucnonastavno_zvanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(20) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10 ;

--
-- Truncate table before insert `sifrarnik_naucnonastavno_zvanje`
--

TRUNCATE TABLE `sifrarnik_naucnonastavno_zvanje`;
--
-- Dumping data for table `sifrarnik_naucnonastavno_zvanje`
--

INSERT INTO `sifrarnik_naucnonastavno_zvanje` (`id`, `naziv`, `titula`) VALUES
(1, 'redovni profesor', 'r. prof.'),
(2, 'vanredni profesor', 'v. prof.'),
(3, 'docent', 'doc.'),
(4, 'viši asistent', 'v. asis.'),
(5, 'asistent', 'asis.'),
(6, 'profesor emeritus', 'prof. emer.'),
(7, 'predavač', 'pred.'),
(8, 'viši lektor', 'v. lec.'),
(9, 'lektor', 'lec.');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_nivo_jezika`
--

DROP TABLE IF EXISTS `sifrarnik_nivo_jezika`;
CREATE TABLE IF NOT EXISTS `sifrarnik_nivo_jezika` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Truncate table before insert `sifrarnik_nivo_jezika`
--

TRUNCATE TABLE `sifrarnik_nivo_jezika`;
--
-- Dumping data for table `sifrarnik_nivo_jezika`
--

INSERT INTO `sifrarnik_nivo_jezika` (`id`, `naziv`) VALUES
(1, 'Početni nivo (A1)'),
(2, 'Srednji nivo (A2)'),
(3, 'Prag nivo (B1)'),
(4, 'Napredni nivo (B2)'),
(5, 'Samostalni nivo (C1)'),
(6, 'Vladanje jezikom (C2)');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_oblik_zaposlenja`
--

DROP TABLE IF EXISTS `sifrarnik_oblik_zaposlenja`;
CREATE TABLE IF NOT EXISTS `sifrarnik_oblik_zaposlenja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=16 ;

--
-- Truncate table before insert `sifrarnik_oblik_zaposlenja`
--

TRUNCATE TABLE `sifrarnik_oblik_zaposlenja`;
--
-- Dumping data for table `sifrarnik_oblik_zaposlenja`
--

INSERT INTO `sifrarnik_oblik_zaposlenja` (`id`, `naziv`) VALUES
(1, 'radni odnos na neodređeno vrijeme'),
(2, 'radni odnos na određeno vrijeme'),
(3, 'privremeni i povremeni poslovi'),
(4, 'ugovor o djelu'),
(5, 'dopunski rad'),
(6, 'volonterski rad'),
(7, 'probni rad'),
(8, 'dopuna norme'),
(9, 'pripravnik'),
(10, 'penzioner'),
(11, 'na čekanju'),
(12, 'rad po projektu stranih organizacija'),
(13, 'rad po projektu domaćih organizacija'),
(14, 'ugovor o radu na određeno vrijeme'),
(15, 'ugovor o radu na neodređeno vrijeme');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_radno_mjesto`
--

DROP TABLE IF EXISTS `sifrarnik_radno_mjesto`;
CREATE TABLE IF NOT EXISTS `sifrarnik_radno_mjesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10004 ;

--
-- Truncate table before insert `sifrarnik_radno_mjesto`
--

TRUNCATE TABLE `sifrarnik_radno_mjesto`;
--
-- Dumping data for table `sifrarnik_radno_mjesto`
--

INSERT INTO `sifrarnik_radno_mjesto` (`id`, `naziv`) VALUES
(1, 'Dekan'),
(12, 'Prodekan za nastavu'),
(14, 'Prodekan za nauku'),
(16, 'Prodekan za finansije'),
(18, 'Koordinator za međunarodnu saradnju'),
(20, 'Sekretar postdiplomskog studija'),
(22, 'Šef službe za nastavu'),
(24, 'Voditelj odjela'),
(26, 'Referent za nastavu'),
(28, 'Referent za nabavku'),
(30, 'Domar'),
(32, 'Knjižničar'),
(100, 'diplomirani inženjer'),
(101, 'direktor'),
(102, 'rektor'),
(103, 'prorektor (redovni profesor)'),
(104, 'prorektor (vanredni profesor)'),
(105, 'generalni sekretar'),
(106, 'rukovodilac službe'),
(107, 'stručni saradnik za pravne poslove'),
(108, 'viši samostalni referent za pravne poslove'),
(109, 'stručni saradnik za nastavne planove i programe'),
(110, 'stručni saradnik za poslove ECTS'),
(111, 'stručni saradnik za sistem kvaliteta'),
(112, 'stručni saradnik za poslove naučno-istraživačkog rada'),
(113, 'stručni saradnik za naučno-istraživačke projekte'),
(114, 'stručni saradnik za međunarodne ugovore i saradnju'),
(115, 'stručni saradnik za odnose s javnošću'),
(116, 'stručni saradnik za poslove izdavačke djelatnosti'),
(117, 'stručni saradnik za finansije'),
(118, 'stručni saradnik za poslove investicije'),
(119, 'stručni saradnik za poslove održavanja'),
(120, 'stručni saradnik za poslove nabavke'),
(121, 'rukovodilac službe/šef kabineta'),
(122, 'poslovni sekretar rektora (stručni saradnik)'),
(123, 'Prevodilac (stručni saradnik)'),
(124, 'Lektor (stručni saradnik)'),
(125, 'stručni saradnik za poslove dizajna'),
(126, 'prijem i oprema pošte i arhive'),
(127, ' radnik-ekonom'),
(128, 'vozač'),
(129, 'kurir'),
(130, 'kafe-kuharica'),
(1000, 'asistent'),
(1010, 'profesor'),
(5000, 'računovođa'),
(5001, 'tehnički sekretar'),
(5002, 'referent'),
(5003, 'administrator servisa'),
(5004, 'sistem administrator'),
(5005, 'rukovodilac odjela za opće poslove'),
(5006, 'blagajnik'),
(5500, 'tehničar'),
(5510, 'laborant'),
(6000, 'sekretar fakulteta'),
(6010, 'šef studentske službe'),
(6500, 'bibliotekar'),
(7000, 'pomoćni radnik'),
(7100, 'portir'),
(10001, 'radnici bez zanimanja'),
(10002, 'referent za pravne poslove'),
(10003, 'spremačica');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_strucna_sprema`
--

DROP TABLE IF EXISTS `sifrarnik_strucna_sprema`;
CREATE TABLE IF NOT EXISTS `sifrarnik_strucna_sprema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

--
-- Truncate table before insert `sifrarnik_strucna_sprema`
--

TRUNCATE TABLE `sifrarnik_strucna_sprema`;
--
-- Dumping data for table `sifrarnik_strucna_sprema`
--

INSERT INTO `sifrarnik_strucna_sprema` (`id`, `naziv`) VALUES
(2, 'NKV'),
(3, 'PK'),
(4, 'KV'),
(5, 'VKV'),
(6, 'SSS'),
(7, 'VŠS'),
(8, 'VSS'),
(9, 'magistar nauka/umjetnosti'),
(10, 'doktor nauka'),
(11, 'bakalaureat/bachelor (Bolonja I ciklus)'),
(12, 'magistar (Bolonja II ciklus)'),
(13, 'doktor (Bolonja III ciklus)');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_tip_mentorstva`
--

DROP TABLE IF EXISTS `sifrarnik_tip_mentorstva`;
CREATE TABLE IF NOT EXISTS `sifrarnik_tip_mentorstva` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=5 ;

--
-- Truncate table before insert `sifrarnik_tip_mentorstva`
--

TRUNCATE TABLE `sifrarnik_tip_mentorstva`;
--
-- Dumping data for table `sifrarnik_tip_mentorstva`
--

INSERT INTO `sifrarnik_tip_mentorstva` (`id`, `naziv`) VALUES
(1, 'magistar nauka'),
(2, 'doktor nauka'),
(3, 'magistar (Bolonja II ciklus)'),
(4, 'doktor (Bolonja III ciklus)');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_tip_publikacije`
--

DROP TABLE IF EXISTS `sifrarnik_tip_publikacije`;
CREATE TABLE IF NOT EXISTS `sifrarnik_tip_publikacije` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

--
-- Truncate table before insert `sifrarnik_tip_publikacije`
--

TRUNCATE TABLE `sifrarnik_tip_publikacije`;
--
-- Dumping data for table `sifrarnik_tip_publikacije`
--

INSERT INTO `sifrarnik_tip_publikacije` (`id`, `naziv`) VALUES
(1, 'udžbenik'),
(2, 'monografija'),
(3, 'knjiga'),
(4, 'priručnik'),
(5, 'naučni članak (ne-indeksirani časopis)'),
(6, 'naučni članak (indeksirani časopis)'),
(7, 'stručni članak'),
(8, 'ostalo');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_uza_naucna_oblast`
--

DROP TABLE IF EXISTS `sifrarnik_uza_naucna_oblast`;
CREATE TABLE IF NOT EXISTS `sifrarnik_uza_naucna_oblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  `fk_naucna_oblast` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=247 ;

--
-- Truncate table before insert `sifrarnik_uza_naucna_oblast`
--

TRUNCATE TABLE `sifrarnik_uza_naucna_oblast`;
--
-- Dumping data for table `sifrarnik_uza_naucna_oblast`
--

INSERT INTO `sifrarnik_uza_naucna_oblast` (`id`, `naziv`, `fk_naucna_oblast`) VALUES
(1, 'Linearni sistemi automatskog upravljanja', 302),
(2, 'Modeliranje i simulacija', 302),
(3, 'Osnove sistema automatskog upravljanja', 302),
(4, 'Praktikum automatike i informatike', 302),
(5, 'Digitalni sistemi upravljanja', 302),
(6, 'Praktikum Automatike', 302),
(7, 'Nelinearni sistemi automatskog upravljanja', 302),
(8, 'Optimalno upravljanje', 302),
(9, 'Inteligentno upravljenje', 302),
(10, 'Digitalni sistemi upravljanja', 302),
(11, 'Teorija automatskog upravljanja', 302),
(12, 'Automatsko upravljanje', 302),
(13, 'Teorija optimalnih rješenja', 302),
(14, 'Senzori i pretvarači', 304),
(15, 'Aktuatori', 304),
(16, 'Analiza signala i sistema', 304),
(17, 'Distribuirani sistemi', 304),
(18, 'Identifikacija dinamičkih sistema', 304),
(19, 'Projektiranje sistema automatskog upravljanja', 304),
(20, 'Senzori i mjerenja (AB)', 304),
(21, 'Analiza signala i sistema', 304),
(22, 'Projektovanje sistema automatskog upravljanja', 304),
(23, 'Akvizicija i prenos podataka', 304),
(24, 'Specijalna mjerenja', 304),
(25, 'Mehatronika', 306),
(26, 'Robotika 1', 306),
(27, 'Mobilna robotika', 306),
(28, 'Robotika i upravljanje proizvodnim sistemima', 306),
(29, 'Strukture i režimi rada elektroenergetskih sistema', 308),
(30, 'Zaštita i upravljanje elektroenergetskih sistemima', 308),
(31, 'Sistemi zaštite i upravljanja elektroenergetskih sistemima', 308),
(32, 'Strukture i režimi rada elektroenergetskih sistema', 308),
(33, 'Principi sistemskog inženjeringa', 310),
(34, 'Elektronički sistemi i sklopovi', 312),
(35, 'Analogna elektornika', 312),
(36, 'Elektornika', 312),
(37, 'Osnove Optoelektornika', 312),
(38, 'Praktikum elektrotehnike i elektronike', 312),
(39, 'Praktikum eletronike', 312),
(40, 'Energetska eletronika', 312),
(41, 'Mikroelektroničke komponenete i modeliranje', 312),
(42, 'Napredne eletroničke komponente i strukture', 312),
(43, 'Osnovi elektronike', 312),
(44, 'Elektronika (AE i TK)', 312),
(45, 'Elektronika (EE)', 312),
(46, 'Elektronika (RI)', 312),
(47, 'Elektornski sklopovi', 312),
(48, 'Energetska elektronika ', 312),
(49, 'Elektronika (TK 2)', 314),
(50, 'Digitalna elektronika', 314),
(51, 'Digitalni integrirani krugovi', 314),
(52, 'Projektovanje logičkih sistema', 314),
(53, 'Projektovanje mikroprocesorskih sistema', 314),
(54, 'Digitalna obrada signalan', 314),
(55, 'Digitalni računari i organizacija softvera I', 314),
(56, 'Digitalni računari i obrada softvera II', 314),
(57, 'Praktikum mikroračunarskih baziranih sistema', 314),
(58, 'Projektovanje sistema u čipu', 314),
(59, 'Impulsna elektronika', 314),
(60, 'Digitalna elektronika', 314),
(61, 'Projektovanje digitalnih sistema', 314),
(62, 'Digitalni računari i organizacija softvera', 314),
(63, 'Digitalna obrada signala', 314),
(64, 'Računarski sistemi u realnom vremenu', 314),
(65, 'Biomedicinski signali i sistemi', 316),
(66, 'Osnove elektroenergetskih sistema', 318),
(67, 'ElektroenergetskI sistemi', 318),
(68, 'Praktikum iz elektroenergetike 1', 318),
(69, 'Održavanje električnih sistema', 318),
(70, 'Praktikum iz elektroenergetike 2', 318),
(71, 'Analiza elektroenergetskih sistema', 318),
(72, 'Automatizirano mjerenje i upravljanje', 318),
(73, 'Elektroenergetski sistemi II', 318),
(74, 'Numeričko modeliranje', 318),
(75, 'Kvaliteta električne energije', 318),
(76, 'Metodologija inženjerskog projektiranja', 318),
(77, 'Eksploatacija i upravljanje elektroenergetskim sistemima', 318),
(78, 'Industrijski i distributivni elektroenergetski sistemi', 318),
(79, 'Planiranje elektroenergetskih sistema', 318),
(80, 'Elektroenergetske mreže i sistemi ', 318),
(81, 'Računarske metode u elektroenergetici', 318),
(82, 'Eksploatacija i upravljanje elektroenergetskim sistemima', 318),
(83, 'Planiranje elektroenergetskih sistema', 318),
(84, 'Pouzdanost električnih elemenata i sistema', 320),
(85, 'Vjerovatnoća i statistika RI', 320),
(86, 'Elektrotehnički materijali', 320),
(87, 'Komponente i tehnologije', 320),
(88, 'Tehnika visokog napona', 320),
(89, 'Tehnologija visokonaponske izolacije', 320),
(90, 'Prenaponi i koordinacija izolacije', 320),
(91, 'Nove tehnologije u elektroenergetici', 320),
(92, 'Monitoring i održavanje elektroenergetskih sistema', 320),
(93, 'Tehnika visokog napona', 320),
(94, 'Elektrotehnička tehnologija', 320),
(95, 'Osnove mehatronike', 322),
(96, 'Električne mašine', 322),
(97, 'Električni sistemi u transportu', 322),
(98, 'Energetska elektronika', 322),
(99, 'Električna postrojenja', 322),
(100, 'Elektromotorni pogoni', 322),
(101, 'Kvaliteta električne energije', 322),
(102, 'Elektromotorni pogoni i dinamika električnih mašina', 322),
(103, 'Električne mašine II', 322),
(104, 'Projektiranje i automatizacija elektroenergetskih postrojenja', 322),
(105, 'Električni aparati 1', 322),
(106, 'Električne mašine 1', 322),
(107, 'Elektroenergetska postrojenja', 322),
(108, 'Elektromotorni pogoni', 322),
(109, 'Električni aparati 2', 322),
(110, 'Električne mašine 2', 322),
(111, 'Inženjerska ekonomika', 324),
(112, 'Električne instalacije i mjere sigurnosti', 324),
(113, 'Elektrotermička konverzija energije', 324),
(114, 'Proizvodnja električne energije', 324),
(115, 'Upravljanje potrošnjom električne energije', 324),
(116, 'Distribuirana proizvodnje energije', 324),
(117, 'Niskonaponski sistemi i upotreba električne energije', 324),
(118, 'Elektroenergetski sistemi i okolina', 324),
(119, 'Energetska ekonomika', 324),
(120, 'Elektroenergetski izvori', 324),
(121, 'Elektroenergetski sistem i okolina', 324),
(122, 'Osnove elektrotehnike', 326),
(123, 'Električni krugovi 1', 326),
(124, 'Električni krugovi 2', 326),
(125, 'Električna mjerenja', 326),
(126, 'Inženjerska elektromagnetika', 326),
(127, 'Osnove elektrotehnike', 326),
(128, 'Električna mjerenja', 326),
(129, 'Elektromagnetika', 326),
(130, 'Teorija električnih kola', 326),
(131, 'Teorija elektromagnetnih polja', 326),
(132, 'Logički dizajn', 328),
(133, 'Operativni sistemi', 328),
(134, 'Računarske arhitekture', 328),
(135, 'Administracija računarskih mreža', 328),
(136, 'Osnove računarskih mreža', 328),
(137, 'Paralelni računarski sistemi', 328),
(138, 'Računarske mreže', 328),
(139, 'Programska organizacija računara i operativni sistemi', 328),
(140, 'Digitalni računari I', 328),
(141, 'Digitalni računari TI', 328),
(142, 'Digitalni računari T2', 328),
(143, 'Računarske arhitekture', 328),
(144, 'Računarske komunikacije i mreže računara', 328),
(145, 'Specijalna poglavlja računarskih sistema', 328),
(146, 'Internet ekonomija', 330),
(147, 'Osnove baza podataka', 330),
(148, 'Osnove informacionih sistema', 330),
(149, 'Informacioni sistemi', 330),
(150, 'Baze podataka', 330),
(151, 'Praktikum-poslovni informacioni sistemi', 330),
(152, 'Inovacije u projektovanju i menadžmentu informacionih sistema', 330),
(153, 'Sistemi za podršku odlučivanju', 330),
(154, 'Informacioni sistemi', 330),
(155, 'Strukture i baze podataka', 330),
(156, 'Projektovanje informacionih sistema', 330),
(157, 'Specijalna poglavlja informacionih sistema', 330),
(158, 'Osnove računarstva', 332),
(159, 'Tehnike programiranja', 332),
(160, 'Algoritmi i strukture podataka', 332),
(161, 'Sistemsko programiranje', 332),
(162, 'Automati i formalni jezici', 332),
(163, 'Tehnologije sigurnosti', 332),
(164, 'Osnovi računarstva', 332),
(165, 'Algoritmi', 332),
(166, 'Programiranje i programski jezici', 332),
(167, 'Teorija sistema', 332),
(168, 'Razvoj programskih rješenja', 334),
(169, 'Objektno-orijentisana analiza i dizajn', 334),
(170, 'Pouzdanost i kontrola kvaliteta softvera', 334),
(171, 'Softver inžinjering', 334),
(172, 'Web tehnologije', 334),
(173, 'Multimedijalni sistemi', 334),
(174, 'Praktikum-napredne web tehnologije', 334),
(175, 'Napredni softver inžinjering', 334),
(176, 'Računarski sistemi u realnom vremenu', 334),
(177, 'Projektovanje sistemskog softvera', 334),
(178, 'Računarska grafika', 336),
(179, 'Vještačka inteligencija', 336),
(180, 'Numerička grafika i animacija', 336),
(181, 'Metode i primjena vještačke inteligencije', 336),
(182, 'Data mining', 336),
(183, 'Računarski algoritmi u bioinformatici', 336),
(184, 'Računarska grafika i komunikacija čovjek', 336),
(185, 'Vještačka inteligencija i ekspertni sistemi', 336),
(186, 'Sistemi za podršku odlučivanju', 336),
(187, 'Optimizacija resursa', 338),
(188, 'Operaciona istraživanja', 338),
(189, 'CAD-CAM inžinjering', 340),
(190, 'Digitalno procesiranje signala', 340),
(191, 'Inžinjering i tehnologija sistema upravljanja', 340),
(192, 'Računarsko modeliranje i simulacija', 340),
(193, 'Prepoznavanje oblika i obrada slike', 340),
(194, 'Specijalna poglavlja sistema u realnom vremenu', 340),
(195, 'Specijalna poglavlja softverskih sistema', 340),
(196, 'Teorija informacija i izvorno kodiranje', 342),
(197, 'Teorija signala', 342),
(198, 'Statistička teorija signala', 342),
(199, 'Kanalno kodiranje', 342),
(200, 'Telekomunikacioni softver inženjering', 342),
(201, 'Kriptografija i sigurnost sistema', 342),
(202, 'Napredna poglavlja iz procesiranja signala', 342),
(203, 'Poslovni modeli u telekomunikacijama', 342),
(204, 'Statistička teorija telekomunikacija', 342),
(205, 'Teorija korekcionih kodova', 342),
(206, 'Teorija elektromagnetnih polja', 344),
(207, 'Osnove optoelektronike', 344),
(208, 'Antene i prostiranje talasa', 344),
(209, 'Telekomunikacione tehnike I', 344),
(210, 'Telekomunikacione tehnike II', 344),
(211, 'Radiotehnika', 344),
(212, 'Mikrovalni komunikacijski sistemi', 344),
(213, 'Komutacioni sistemi', 344),
(214, 'Tehnologije televizije', 344),
(215, 'Simulacija procesa u telekomunikacijskom kanalu', 344),
(216, 'Optički telekomunikacijski sistemi', 344),
(217, 'Simulacija procesa u telekomunikacijskim mrežama', 344),
(218, 'Kompresija slike i videa', 344),
(219, 'Sistemski aspekti u telekomunikacijama', 344),
(220, 'Napredna poglavlja u analizi IP saobraćaja', 344),
(221, 'Optoelektronika', 344),
(222, 'Osnove digitalnih telekomunikacija', 344),
(223, 'Radiotehnika', 344),
(224, 'Komutacioni sistemi', 344),
(225, 'Antene i prostiranje talasa', 344),
(226, 'Digitalni telekomunikacioni sistemi I', 344),
(227, 'Digitalni telekomunikacioni sistemi II', 344),
(228, 'Televizijska tehnika', 344),
(229, 'Mikrotalasni i satelitski sistemi', 344),
(230, 'Nove generacije mreža i usluga', 346),
(231, 'Teorija prometa', 346),
(232, 'Komunikacijski protokoli i mreže', 346),
(233, 'Osnovi signalizacionih protokola', 346),
(234, 'Algoritmi i metodi optimizacije', 346),
(235, 'Organizacija i osnove upravljanja mrežom', 346),
(236, 'Arhitekture paketskih čvorišta', 346),
(237, 'Kvaliteta usluga u telekomunikacijskim mrežama', 346),
(238, 'Mrežni multimedijalni servisi', 346),
(239, 'Softverski dizajn protokola', 346),
(240, 'Napredni telekomunikacijski protokoli i mreže nove generacije', 346),
(241, 'Računarske komunikacije i mreže računara', 346),
(242, 'Mobilne komunikacije', 348),
(243, 'Tehnologije pristupnih bežičnih mreža', 348),
(244, 'Upravljanje telekomunikacijskim mrežama', 348),
(245, 'Sistemi i servisi mobilnih telekomunikacija', 348),
(246, 'Mobilne radio komunikacije', 348);

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_vozacki_kategorija`
--

DROP TABLE IF EXISTS `sifrarnik_vozacki_kategorija`;
CREATE TABLE IF NOT EXISTS `sifrarnik_vozacki_kategorija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

--
-- Truncate table before insert `sifrarnik_vozacki_kategorija`
--

TRUNCATE TABLE `sifrarnik_vozacki_kategorija`;
--
-- Dumping data for table `sifrarnik_vozacki_kategorija`
--

INSERT INTO `sifrarnik_vozacki_kategorija` (`id`, `naziv`) VALUES
(1, 'A kategorija'),
(2, 'A1 kategorija'),
(3, 'A2 kategorija'),
(4, 'B kategorija'),
(5, 'B+E kategorija'),
(6, 'C1 kategorija'),
(7, 'C1+E kategorija'),
(8, 'C kategorija'),
(9, 'C+E kategorija'),
(10, 'D kategorija'),
(11, 'D+E kategorija'),
(12, 'F kategorija'),
(13, 'G kategorija');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_zvanje`
--

DROP TABLE IF EXISTS `sifrarnik_zvanje`;
CREATE TABLE IF NOT EXISTS `sifrarnik_zvanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `sifrarnik_zvanje`
--

TRUNCATE TABLE `sifrarnik_zvanje`;
-- --------------------------------------------------------

--
-- Table structure for table `srednja_ocjene`
--

DROP TABLE IF EXISTS `srednja_ocjene`;
CREATE TABLE IF NOT EXISTS `srednja_ocjene` (
  `osoba` int(11) NOT NULL,
  `razred` tinyint(4) NOT NULL,
  `redni_broj` int(1) NOT NULL,
  `ocjena` tinyint(5) NOT NULL,
  `tipocjene` tinyint(5) NOT NULL,
  PRIMARY KEY (`osoba`,`razred`,`redni_broj`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `srednja_ocjene`
--

TRUNCATE TABLE `srednja_ocjene`;
-- --------------------------------------------------------

--
-- Table structure for table `srednja_skola`
--

DROP TABLE IF EXISTS `srednja_skola`;
CREATE TABLE IF NOT EXISTS `srednja_skola` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `domaca` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `srednja_skola`
--

TRUNCATE TABLE `srednja_skola`;
-- --------------------------------------------------------

--
-- Table structure for table `stdin`
--

DROP TABLE IF EXISTS `stdin`;
CREATE TABLE IF NOT EXISTS `stdin` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `zadaca` bigint(20) NOT NULL DEFAULT '0',
  `redni_broj` int(11) NOT NULL DEFAULT '0',
  `ulaz` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `stdin`
--

TRUNCATE TABLE `stdin`;
-- --------------------------------------------------------

--
-- Table structure for table `student_ispit_termin`
--

DROP TABLE IF EXISTS `student_ispit_termin`;
CREATE TABLE IF NOT EXISTS `student_ispit_termin` (
  `student` int(11) NOT NULL,
  `ispit_termin` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncate table before insert `student_ispit_termin`
--

TRUNCATE TABLE `student_ispit_termin`;
-- --------------------------------------------------------

--
-- Table structure for table `student_labgrupa`
--

DROP TABLE IF EXISTS `student_labgrupa`;
CREATE TABLE IF NOT EXISTS `student_labgrupa` (
  `student` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `student_labgrupa`
--

TRUNCATE TABLE `student_labgrupa`;
-- --------------------------------------------------------

--
-- Table structure for table `student_predmet`
--

DROP TABLE IF EXISTS `student_predmet`;
CREATE TABLE IF NOT EXISTS `student_predmet` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY (`student`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `student_predmet`
--

TRUNCATE TABLE `student_predmet`;
-- --------------------------------------------------------

--
-- Table structure for table `student_projekat`
--

DROP TABLE IF EXISTS `student_projekat`;
CREATE TABLE IF NOT EXISTS `student_projekat` (
  `student` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY (`student`,`projekat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `student_projekat`
--

TRUNCATE TABLE `student_projekat`;
-- --------------------------------------------------------

--
-- Table structure for table `student_studij`
--

DROP TABLE IF EXISTS `student_studij`;
CREATE TABLE IF NOT EXISTS `student_studij` (
  `student` int(11) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(3) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `nacin_studiranja` int(11) NOT NULL,
  `ponovac` tinyint(4) NOT NULL DEFAULT '0',
  `odluka` int(11) NOT NULL DEFAULT '0',
  `plan_studija` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`student`,`studij`,`semestar`,`akademska_godina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `student_studij`
--

TRUNCATE TABLE `student_studij`;
-- --------------------------------------------------------

--
-- Table structure for table `studij`
--

DROP TABLE IF EXISTS `studij`;
CREATE TABLE IF NOT EXISTS `studij` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `zavrsni_semestar` int(11) NOT NULL DEFAULT '0',
  `institucija` int(11) NOT NULL DEFAULT '0',
  `kratkinaziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `moguc_upis` tinyint(1) NOT NULL,
  `tipstudija` int(11) NOT NULL,
  `preduslov` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

--
-- Truncate table before insert `studij`
--

TRUNCATE TABLE `studij`;
--
-- Dumping data for table `studij`
--

INSERT INTO `studij` (`id`, `naziv`, `zavrsni_semestar`, `institucija`, `kratkinaziv`, `moguc_upis`, `tipstudija`, `preduslov`) VALUES
(4, 'Telekomunikacije (BSc)', 6, 5, 'TK', 1, 1, 1),
(3, 'Elektroenergetika (BSc)', 6, 4, 'EE', 1, 1, 1),
(2, 'Automatika i elektronika (BSc)', 6, 3, 'AE', 1, 1, 1),
(1, 'Računarstvo i informatika (BSc)', 6, 2, 'RI', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tipkomponente`
--

DROP TABLE IF EXISTS `tipkomponente`;
CREATE TABLE IF NOT EXISTS `tipkomponente` (
  `id` int(11) NOT NULL,
  `naziv` varchar(20) COLLATE utf8_slovenian_ci NOT NULL,
  `opis_opcija` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `tipkomponente`
--

TRUNCATE TABLE `tipkomponente`;
--
-- Dumping data for table `tipkomponente`
--

INSERT INTO `tipkomponente` (`id`, `naziv`, `opis_opcija`) VALUES
(1, 'Ispit', ''),
(2, 'Integralni ispit', 'Ispiti koje zamjenjuje (razdvojeni sa +)'),
(3, 'Zadace', ''),
(4, 'Prisustvo', 'Minimalan broj izostanaka (0=linearno)'),
(5, 'Fiksna', '');

-- --------------------------------------------------------

--
-- Table structure for table `tippredmeta`
--

DROP TABLE IF EXISTS `tippredmeta`;
CREATE TABLE IF NOT EXISTS `tippredmeta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

--
-- Truncate table before insert `tippredmeta`
--

TRUNCATE TABLE `tippredmeta`;
--
-- Dumping data for table `tippredmeta`
--

INSERT INTO `tippredmeta` (`id`, `naziv`) VALUES
(1, 'ETF Bologna standard');

-- --------------------------------------------------------

--
-- Table structure for table `tippredmeta_komponenta`
--

DROP TABLE IF EXISTS `tippredmeta_komponenta`;
CREATE TABLE IF NOT EXISTS `tippredmeta_komponenta` (
  `tippredmeta` int(11) NOT NULL,
  `komponenta` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `tippredmeta_komponenta`
--

TRUNCATE TABLE `tippredmeta_komponenta`;
--
-- Dumping data for table `tippredmeta_komponenta`
--

INSERT INTO `tippredmeta_komponenta` (`tippredmeta`, `komponenta`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6);

-- --------------------------------------------------------

--
-- Table structure for table `tipstudija`
--

DROP TABLE IF EXISTS `tipstudija`;
CREATE TABLE IF NOT EXISTS `tipstudija` (
  `id` int(11) NOT NULL,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `ciklus` tinyint(2) NOT NULL,
  `trajanje` tinyint(3) NOT NULL,
  `moguc_upis` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `tipstudija`
--

TRUNCATE TABLE `tipstudija`;
--
-- Dumping data for table `tipstudija`
--

INSERT INTO `tipstudija` (`id`, `naziv`, `ciklus`, `trajanje`, `moguc_upis`) VALUES
(1, 'Bakalaureat', 1, 6, 1),
(2, 'Master', 2, 4, 1),
(3, 'Doktorski studij', 3, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ugovoroucenju`
--

DROP TABLE IF EXISTS `ugovoroucenju`;
CREATE TABLE IF NOT EXISTS `ugovoroucenju` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `ugovoroucenju`
--

TRUNCATE TABLE `ugovoroucenju`;
-- --------------------------------------------------------

--
-- Table structure for table `ugovoroucenju_izborni`
--

DROP TABLE IF EXISTS `ugovoroucenju_izborni`;
CREATE TABLE IF NOT EXISTS `ugovoroucenju_izborni` (
  `ugovoroucenju` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `ugovoroucenju_izborni`
--

TRUNCATE TABLE `ugovoroucenju_izborni`;
-- --------------------------------------------------------

--
-- Table structure for table `upis_kriterij`
--

DROP TABLE IF EXISTS `upis_kriterij`;
CREATE TABLE IF NOT EXISTS `upis_kriterij` (
  `prijemni_termin` int(11) NOT NULL AUTO_INCREMENT,
  `donja_granica` float NOT NULL,
  `gornja_granica` float NOT NULL,
  `kandidati_strani` int(5) NOT NULL,
  `kandidati_sami_placaju` int(5) NOT NULL,
  `kandidati_kanton_placa` int(5) NOT NULL,
  `kandidati_vanredni` int(5) NOT NULL,
  `prijemni_max` int(5) NOT NULL,
  `studij` int(11) NOT NULL,
  PRIMARY KEY (`prijemni_termin`,`studij`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci COMMENT='Tabela za pohranu kriterija za upis' AUTO_INCREMENT=5 ;

--
-- Truncate table before insert `upis_kriterij`
--

TRUNCATE TABLE `upis_kriterij`;
-- --------------------------------------------------------

--
-- Table structure for table `uspjeh_u_srednjoj`
--

DROP TABLE IF EXISTS `uspjeh_u_srednjoj`;
CREATE TABLE IF NOT EXISTS `uspjeh_u_srednjoj` (
  `osoba` int(11) NOT NULL,
  `srednja_skola` int(11) NOT NULL,
  `godina` int(11) NOT NULL,
  `opci_uspjeh` double NOT NULL,
  `kljucni_predmeti` double NOT NULL,
  `dodatni_bodovi` double NOT NULL,
  `ucenik_generacije` tinyint(1) NOT NULL,
  PRIMARY KEY (`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `uspjeh_u_srednjoj`
--

TRUNCATE TABLE `uspjeh_u_srednjoj`;
-- --------------------------------------------------------

--
-- Table structure for table `vozacka_dozvola`
--

DROP TABLE IF EXISTS `vozacka_dozvola`;
CREATE TABLE IF NOT EXISTS `vozacka_dozvola` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `fk_vozacki_kategorija` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `vozacka_dozvola`
--

TRUNCATE TABLE `vozacka_dozvola`;
-- --------------------------------------------------------

--
-- Table structure for table `zadaca`
--

DROP TABLE IF EXISTS `zadaca`;
CREATE TABLE IF NOT EXISTS `zadaca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `zadataka` tinyint(4) NOT NULL DEFAULT '0',
  `bodova` float NOT NULL DEFAULT '0',
  `rok` datetime DEFAULT NULL,
  `aktivna` tinyint(1) NOT NULL DEFAULT '0',
  `programskijezik` int(10) NOT NULL DEFAULT '0',
  `attachment` tinyint(1) NOT NULL DEFAULT '0',
  `dozvoljene_ekstenzije` varchar(255) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `postavka_zadace` varchar(255) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `komponenta` int(11) NOT NULL,
  `vrijemeobjave` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `zadaca`
--

TRUNCATE TABLE `zadaca`;
-- --------------------------------------------------------

--
-- Table structure for table `zadatak`
--

DROP TABLE IF EXISTS `zadatak`;
CREATE TABLE IF NOT EXISTS `zadatak` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `zadaca` int(11) NOT NULL DEFAULT '0',
  `redni_broj` int(11) NOT NULL DEFAULT '0',
  `student` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `bodova` float NOT NULL DEFAULT '0',
  `izvjestaj_skripte` text COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme` datetime DEFAULT NULL,
  `komentar` text COLLATE utf8_slovenian_ci NOT NULL,
  `filename` varchar(200) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pomocni` (`zadaca`,`redni_broj`,`student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `zadatak`
--

TRUNCATE TABLE `zadatak`;
-- --------------------------------------------------------

--
-- Table structure for table `zadatakdiff`
--

DROP TABLE IF EXISTS `zadatakdiff`;
CREATE TABLE IF NOT EXISTS `zadatakdiff` (
  `zadatak` bigint(11) NOT NULL DEFAULT '0',
  `diff` text COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `zadatakdiff`
--

TRUNCATE TABLE `zadatakdiff`;
-- --------------------------------------------------------

--
-- Table structure for table `zavrsni`
--

DROP TABLE IF EXISTS `zavrsni`;
CREATE TABLE IF NOT EXISTS `zavrsni` (
  `id` int(11) NOT NULL,
  `naslov` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `podnaslov` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `rad_na_predmetu` int(11) NOT NULL,
  `akademska_godina` varchar(10) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '0',
  `kratki_pregled` text COLLATE utf8_slovenian_ci NOT NULL,
  `literatura` text COLLATE utf8_slovenian_ci NOT NULL,
  `sazetak` text COLLATE utf8_slovenian_ci NOT NULL,
  `summary` text COLLATE utf8_slovenian_ci NOT NULL,
  `mentor` int(11) NOT NULL,
  `student` int(11) NOT NULL,
  `kandidat_potvrdjen` tinyint(4) NOT NULL,
  `biljeska` text COLLATE utf8_slovenian_ci NOT NULL,
  `predsjednik_komisije` int(11) NOT NULL,
  `clan_komisije` int(11) NOT NULL,
  `termin_odbrane` datetime NOT NULL,
  `konacna_ocjena` int(11) NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `zavrsni`
--

TRUNCATE TABLE `zavrsni`;
-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_bb_post`
--

DROP TABLE IF EXISTS `zavrsni_bb_post`;
CREATE TABLE IF NOT EXISTS `zavrsni_bb_post` (
  `id` int(11) NOT NULL,
  `naslov` varchar(300) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  `tema` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `zavrsni_bb_post`
--

TRUNCATE TABLE `zavrsni_bb_post`;
-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_bb_post_text`
--

DROP TABLE IF EXISTS `zavrsni_bb_post_text`;
CREATE TABLE IF NOT EXISTS `zavrsni_bb_post_text` (
  `post` int(11) NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`post`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `zavrsni_bb_post_text`
--

TRUNCATE TABLE `zavrsni_bb_post_text`;
-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_bb_tema`
--

DROP TABLE IF EXISTS `zavrsni_bb_tema`;
CREATE TABLE IF NOT EXISTS `zavrsni_bb_tema` (
  `id` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prvi_post` int(11) NOT NULL DEFAULT '0',
  `zadnji_post` int(11) NOT NULL DEFAULT '0',
  `pregleda` int(11) unsigned NOT NULL DEFAULT '0',
  `osoba` int(11) NOT NULL,
  `zavrsni` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `zavrsni_bb_tema`
--

TRUNCATE TABLE `zavrsni_bb_tema`;
-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_file`
--

DROP TABLE IF EXISTS `zavrsni_file`;
CREATE TABLE IF NOT EXISTS `zavrsni_file` (
  `id` int(11) NOT NULL,
  `filename` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revizija` tinyint(4) NOT NULL,
  `osoba` int(11) NOT NULL,
  `zavrsni` int(11) NOT NULL,
  `file` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `zavrsni_file`
--

TRUNCATE TABLE `zavrsni_file`;
-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_file_diff`
--

DROP TABLE IF EXISTS `zavrsni_file_diff`;
CREATE TABLE IF NOT EXISTS `zavrsni_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `zavrsni_file_diff`
--

TRUNCATE TABLE `zavrsni_file_diff`;
-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_rad_predmet`
--

DROP TABLE IF EXISTS `zavrsni_rad_predmet`;
CREATE TABLE IF NOT EXISTS `zavrsni_rad_predmet` (
  `id` int(11) NOT NULL,
  `naziv` varchar(11) COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'Završni rad',
  `predmet` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `akademska_godina` varchar(9) COLLATE utf8_slovenian_ci NOT NULL,
  `student` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `nastavnik` varchar(100) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Truncate table before insert `zavrsni_rad_predmet`
--

TRUNCATE TABLE `zavrsni_rad_predmet`;