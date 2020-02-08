-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 18, 2019 at 07:35 PM
-- Server version: 5.7.26
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zamger`
--

-- --------------------------------------------------------

--
-- Table structure for table `akademska_godina`
--

DROP TABLE IF EXISTS `akademska_godina`;
CREATE TABLE IF NOT EXISTS `akademska_godina` (
  `id` int(11) NOT NULL,
  `naziv` varchar(20) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `aktuelna` tinyint(1) NOT NULL,
  `pocetak_zimskog_semestra` date NOT NULL,
  `kraj_zimskog_semestra` date NOT NULL,
  `pocetak_ljetnjeg_semestra` date NOT NULL,
  `kraj_ljetnjeg_semestra` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `akademska_godina`
--

INSERT INTO `akademska_godina` (`id`, `naziv`, `aktuelna`, `pocetak_zimskog_semestra`, `kraj_zimskog_semestra`, `pocetak_ljetnjeg_semestra`, `kraj_ljetnjeg_semestra`) VALUES
(1, '2015/2016', 1, '2015-10-05', '2016-01-17', '2016-02-22', '2016-06-06');

-- --------------------------------------------------------

--
-- Table structure for table `akademska_godina_predmet`
--

DROP TABLE IF EXISTS `akademska_godina_predmet`;
CREATE TABLE IF NOT EXISTS `akademska_godina_predmet` (
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  PRIMARY KEY (`akademska_godina`,`predmet`),
  KEY `tippredmeta` (`tippredmeta`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `akademska_godina_predmet`
--

INSERT INTO `akademska_godina_predmet` (`akademska_godina`, `predmet`, `tippredmeta`) VALUES
(1, 1, 1),
(1, 2, 1),
(1, 3, 1),
(1, 4, 1),
(1, 5, 1),
(1, 6, 1);

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
  PRIMARY KEY (`predmet`,`akademska_godina`,`osoba`),
  KEY `angazman_status` (`angazman_status`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `osoba` (`osoba`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `angazman`
--

INSERT INTO `angazman` (`predmet`, `akademska_godina`, `osoba`, `angazman_status`) VALUES
(1, 1, 9, 2);

-- --------------------------------------------------------

--
-- Table structure for table `angazman_status`
--

DROP TABLE IF EXISTS `angazman_status`;
CREATE TABLE IF NOT EXISTS `angazman_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum_otvaranja` datetime DEFAULT NULL,
  `datum_zatvaranja` datetime DEFAULT NULL,
  `naziv` char(255) COLLATE utf8_slovenian_ci NOT NULL,
  `opis` text COLLATE utf8_slovenian_ci,
  `aktivna` tinyint(1) DEFAULT '0',
  `editable` tinyint(1) DEFAULT '1',
  `akademska_godina` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_izbori_pitanja`
--

DROP TABLE IF EXISTS `anketa_izbori_pitanja`;
CREATE TABLE IF NOT EXISTS `anketa_izbori_pitanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pitanje` int(11) NOT NULL,
  `izbor` text COLLATE utf8_slovenian_ci NOT NULL,
  `dopisani_odgovor` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_dopisani`
--

DROP TABLE IF EXISTS `anketa_odgovor_dopisani`;
CREATE TABLE IF NOT EXISTS `anketa_odgovor_dopisani` (
  `rezultat` int(11) NOT NULL,
  `pitanje` int(11) NOT NULL,
  `odgovor` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`rezultat`,`pitanje`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_izbori`
--

DROP TABLE IF EXISTS `anketa_odgovor_izbori`;
CREATE TABLE IF NOT EXISTS `anketa_odgovor_izbori` (
  `rezultat` int(11) NOT NULL,
  `pitanje` int(11) NOT NULL,
  `izbor_id` int(11) NOT NULL,
  PRIMARY KEY (`rezultat`,`pitanje`,`izbor_id`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_rank`
--

DROP TABLE IF EXISTS `anketa_odgovor_rank`;
CREATE TABLE IF NOT EXISTS `anketa_odgovor_rank` (
  `rezultat` int(11) NOT NULL,
  `pitanje` int(11) NOT NULL,
  `izbor_id` int(11) NOT NULL,
  PRIMARY KEY (`rezultat`,`pitanje`,`izbor_id`),
  KEY `rezultat` (`rezultat`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_text`
--

DROP TABLE IF EXISTS `anketa_odgovor_text`;
CREATE TABLE IF NOT EXISTS `anketa_odgovor_text` (
  `rezultat` int(11) NOT NULL,
  `pitanje` int(11) NOT NULL,
  `odgovor` text COLLATE utf8_slovenian_ci,
  PRIMARY KEY (`rezultat`,`pitanje`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_pitanje`
--

DROP TABLE IF EXISTS `anketa_pitanje`;
CREATE TABLE IF NOT EXISTS `anketa_pitanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anketa` int(11) NOT NULL DEFAULT '0',
  `tip_pitanja` int(11) NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `anketa` (`anketa`),
  KEY `tip_pitanja` (`tip_pitanja`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_predmet`
--

DROP TABLE IF EXISTS `anketa_predmet`;
CREATE TABLE IF NOT EXISTS `anketa_predmet` (
  `anketa` int(11) NOT NULL,
  `predmet` int(11) DEFAULT NULL,
  `akademska_godina` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `aktivna` tinyint(1) NOT NULL,
  KEY `predmet` (`predmet`),
  KEY `anketa` (`anketa`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_rezultat`
--

DROP TABLE IF EXISTS `anketa_rezultat`;
CREATE TABLE IF NOT EXISTS `anketa_rezultat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anketa` int(11) NOT NULL,
  `zavrsena` enum('Y','N') COLLATE utf8_slovenian_ci DEFAULT 'N',
  `predmet` int(11) DEFAULT NULL,
  `unique_id` varchar(50) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `akademska_godina` int(10) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `student` int(11) DEFAULT NULL,
  `labgrupa` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unique_id` (`unique_id`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `studij` (`studij`),
  KEY `student` (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_tip_pitanja`
--

DROP TABLE IF EXISTS `anketa_tip_pitanja`;
CREATE TABLE IF NOT EXISTS `anketa_tip_pitanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tip` char(32) COLLATE utf8_slovenian_ci NOT NULL,
  `postoji_izbor` enum('Y','N') COLLATE utf8_slovenian_ci NOT NULL,
  `tabela_odgovora` char(32) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_tip_pitanja`
--

INSERT INTO `anketa_tip_pitanja` (`id`, `tip`, `postoji_izbor`, `tabela_odgovora`) VALUES
(1, 'Ocjena (skala 1..5)', 'Y', 'odgovor_rank'),
(2, 'Komentar', 'N', 'odgovor_text'),
(3, 'Izbor (pojedinačni)', 'Y', 'odgovor_izbor'),
(4, 'Izbor (višestruki)', 'Y', 'odgovor_izbor'),
(5, 'Naslov', 'N', ''),
(6, 'Podnaslov', 'N', '');

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
  `posljednji_pristup` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`id`,`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `auth`
--

INSERT INTO `auth` (`id`, `login`, `password`, `admin`, `external_id`, `aktivan`, `posljednji_pristup`) VALUES
(1, 'admin', 'admin', 0, '', 1, '2019-11-18 20:35:07'),
(2, 'profesor', 'sifra', 0, '', 1, '1970-01-01 00:00:00'),
(3, 'silvester', 'sifra', 0, '', 1, '1970-01-01 00:00:00'),
(4, 'jason', 'sifra', 0, '', 1, '1970-01-01 00:00:00'),
(5, 'semso', 'sifra', 0, '', 1, '1970-01-01 00:00:00'),
(6, 'djemka', 'sifra', 0, '', 1, '1970-01-01 00:00:00'),
(7, 'amina', 'sifra', 0, '', 1, '1970-01-01 00:00:00'),
(8, 'profa', 'sifra', 0, '', 1, '1970-01-01 00:00:00'),
(9, 'asistent', 'sifra', 0, '', 1, '1970-01-01 00:00:00');

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
  `pozicija_globala` enum('prije_svega','prije_maina') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'prije_maina',
  `stdin` text COLLATE utf8_slovenian_ci NOT NULL,
  `partial_match` tinyint(4) NOT NULL DEFAULT '0',
  `aktivan` tinyint(1) NOT NULL DEFAULT '1',
  `sakriven` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `zadaca` (`zadaca`),
  KEY `zadaca_2` (`zadaca`,`zadatak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  KEY `zadaca` (`zadaca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `trajanje` int(11) NOT NULL DEFAULT '0',
  `testni_sistem` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`autotest`,`student`),
  KEY `student` (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  PRIMARY KEY (`id`),
  KEY `osoba` (`osoba`),
  KEY `tema` (`tema`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bb_post_text`
--

DROP TABLE IF EXISTS `bb_post_text`;
CREATE TABLE IF NOT EXISTS `bb_post_text` (
  `post` int(11) NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `pregleda` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `prvi_post` (`prvi_post`),
  KEY `zadnji_post` (`zadnji_post`),
  KEY `osoba` (`osoba`),
  KEY `projekat` (`projekat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  PRIMARY KEY (`id`),
  KEY `osoba` (`osoba`),
  KEY `projekat` (`projekat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buildservice_tracking`
--

DROP TABLE IF EXISTS `buildservice_tracking`;
CREATE TABLE IF NOT EXISTS `buildservice_tracking` (
  `zadatak` int(11) NOT NULL,
  `buildhost` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`zadatak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  KEY `labgrupa` (`labgrupa`),
  KEY `nastavnik` (`nastavnik`),
  KEY `komponenta` (`komponenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  KEY `cron` (`cron`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drzava`
--

DROP TABLE IF EXISTS `drzava`;
CREATE TABLE IF NOT EXISTS `drzava` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
(10, 'Iran'),
(11, 'Libija'),
(12, 'Švedska'),
(13, 'Austrija'),
(14, 'SAD'),
(15, 'Italija'),
(16, 'Australija'),
(17, 'Velika Britanija'),
(18, 'Malezija'),
(19, 'Holandija'),
(20, 'Švicarska'),
(21, 'Tajland'),
(22, 'Češka'),
(23, 'Slovačka'),
(24, 'Norveška'),
(25, 'Južna Koreja'),
(26, 'Jordan'),
(27, 'Francuska'),
(28, 'Egipat'),
(29, 'Rusija'),
(30, 'Irak'),
(31, 'Kuvajt');

-- --------------------------------------------------------

--
-- Table structure for table `ekstenzije`
--

DROP TABLE IF EXISTS `ekstenzije`;
CREATE TABLE IF NOT EXISTS `ekstenzije` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  KEY `osoba` (`osoba`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `tipinstitucije` int(11) NOT NULL,
  `dekan` int(11) NOT NULL,
  `broj_protokola` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `institucija`
--

INSERT INTO `institucija` (`id`, `naziv`, `roditelj`, `kratki_naziv`, `tipinstitucije`, `dekan`, `broj_protokola`) VALUES
(1, 'Elektrotehnički fakultet Sarajevo', 0, 'ETF', 1, 3010, '06-4-1-'),
(2, 'Odsjek za računarstvo i informatiku', 1, 'RI', 0, 0, ''),
(3, 'Odsjek za automatiku i elektroniku', 1, 'AE', 0, 0, ''),
(4, 'Odsjek za elektroenergetiku', 1, 'EE', 0, 0, ''),
(5, 'Odsjek za telekomunikacije', 1, 'TK', 0, 0, '');

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
  PRIMARY KEY (`id`),
  KEY `predmet` (`predmet`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `komponenta` (`komponenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ispitocjene`
--

DROP TABLE IF EXISTS `ispitocjene`;
CREATE TABLE IF NOT EXISTS `ispitocjene` (
  `ispit` int(11) NOT NULL,
  `student` int(11) NOT NULL,
  `ocjena` float NOT NULL,
  PRIMARY KEY (`ispit`,`student`),
  KEY `student` (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  PRIMARY KEY (`id`),
  KEY `ispit` (`ispit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izbor`
--

DROP TABLE IF EXISTS `izbor`;
CREATE TABLE IF NOT EXISTS `izbor` (
  `osoba` int(11) NOT NULL,
  `zvanje` int(11) NOT NULL,
  `datum_izbora` date NOT NULL,
  `datum_isteka` date NOT NULL,
  `oblast` int(11) NOT NULL,
  `podoblast` int(11) NOT NULL,
  `dopunski` tinyint(1) NOT NULL,
  `druga_institucija` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izvoz_ocjena`
--

DROP TABLE IF EXISTS `izvoz_ocjena`;
CREATE TABLE IF NOT EXISTS `izvoz_ocjena` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY (`student`,`predmet`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izvoz_promjena_podataka`
--

DROP TABLE IF EXISTS `izvoz_promjena_podataka`;
CREATE TABLE IF NOT EXISTS `izvoz_promjena_podataka` (
  `student` int(11) NOT NULL,
  PRIMARY KEY (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izvoz_upis_prva`
--

DROP TABLE IF EXISTS `izvoz_upis_prva`;
CREATE TABLE IF NOT EXISTS `izvoz_upis_prva` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  PRIMARY KEY (`student`,`akademska_godina`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izvoz_upis_semestar`
--

DROP TABLE IF EXISTS `izvoz_upis_semestar`;
CREATE TABLE IF NOT EXISTS `izvoz_upis_semestar` (
  `student` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  PRIMARY KEY (`student`,`semestar`,`akademska_godina`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kandidati`
--

DROP TABLE IF EXISTS `kandidati`;
CREATE TABLE IF NOT EXISTS `kandidati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ime` varchar(64) COLLATE utf8_slovenian_ci NOT NULL,
  `prezime` varchar(64) COLLATE utf8_slovenian_ci NOT NULL,
  `ime_oca` varchar(64) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `prezime_oca` varchar(64) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `ime_majke` varchar(64) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `prezime_majke` varchar(64) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `spol` enum('M','Z','') COLLATE utf8_slovenian_ci DEFAULT NULL,
  `datum_rodjenja` date NOT NULL,
  `mjesto_rodjenja` int(11) NOT NULL,
  `nacionalnost` int(11) NOT NULL,
  `drzavljanstvo` int(11) NOT NULL,
  `boracka_kategorija` int(11) DEFAULT NULL,
  `boracka_kategorija_br_rjesenja` varchar(128) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `boracka_kategorija_datum_rjesenja` date DEFAULT NULL,
  `boracka_kategorija_organ_izdavanja` varchar(256) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `jmbg` varchar(64) COLLATE utf8_slovenian_ci NOT NULL,
  `ulica_prebivalista` varchar(100) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `mjesto_prebivalista` varchar(128) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `telefon` varchar(15) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `kanton` int(11) DEFAULT NULL,
  `studijski_program` int(11) NOT NULL,
  `naziv_skole` varchar(128) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina_skole` int(11) NOT NULL,
  `strana_skola` tinyint(1) DEFAULT '0',
  `skolska_godina_zavrsetka` int(11) NOT NULL,
  `opci_uspjeh` float NOT NULL,
  `znacajni_predmeti` float NOT NULL,
  `datum_kreiranja` datetime NOT NULL,
  `email` varchar(128) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `prijava_potvrdjena` tinyint(1) DEFAULT '0',
  `podaci_uvezeni` tinyint(1) DEFAULT '0',
  `osoba` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mjesto_rodjenja` (`mjesto_rodjenja`),
  KEY `nacionalnost` (`nacionalnost`),
  KEY `drzavljanstvo` (`drzavljanstvo`),
  KEY `boracka_kategorija` (`boracka_kategorija`),
  KEY `opcina_skole` (`opcina_skole`),
  KEY `studijski_program` (`studijski_program`),
  KEY `skolska_godina_zavrsetka` (`skolska_godina_zavrsetka`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kandidati_mjesto`
--

DROP TABLE IF EXISTS `kandidati_mjesto`;
CREATE TABLE IF NOT EXISTS `kandidati_mjesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(40) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `drzava` int(11) NOT NULL,
  `opcina_van_bih` varchar(40) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `opcina` (`opcina`),
  KEY `drzava` (`drzava`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kandidati_ocjene`
--

DROP TABLE IF EXISTS `kandidati_ocjene`;
CREATE TABLE IF NOT EXISTS `kandidati_ocjene` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kandidat_id` int(11) NOT NULL,
  `naziv_predmeta` varchar(128) COLLATE utf8_slovenian_ci NOT NULL,
  `prvi_razred` tinyint(4) NOT NULL,
  `drugi_razred` tinyint(4) NOT NULL,
  `treci_razred` tinyint(4) NOT NULL,
  `cetvrti_razred` tinyint(4) NOT NULL,
  `kljucni_predmet` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `kandidat_id` (`kandidat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  KEY `student` (`student`,`akademska_godina`),
  KEY `predmet` (`predmet`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

DROP TABLE IF EXISTS `komentar`;
CREATE TABLE IF NOT EXISTS `komentar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student` int(11) NOT NULL,
  `nastavnik` int(11) NOT NULL,
  `labgrupa` int(11) NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `komentar` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student` (`student`),
  KEY `nastavnik` (`nastavnik`),
  KEY `labgrupa` (`labgrupa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  PRIMARY KEY (`id`),
  KEY `tipkomponente` (`tipkomponente`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `komponenta`
--

INSERT INTO `komponenta` (`id`, `naziv`, `gui_naziv`, `kratki_gui_naziv`, `tipkomponente`, `maxbodova`, `prolaz`, `opcija`, `uslov`) VALUES
(1, 'I parcijalni', 'I parcijalni', 'I parc', 1, 20, 10, '', 0),
(2, 'II parcijalni', 'II parcijalni', 'II parc', 1, 20, 10, '', 0),
(3, 'Integralni', 'Integralni', 'Int', 2, 40, 20, '1+2', 0),
(4, 'Usmeni', 'Usmeni', 'Usmeni', 1, 40, 0, '', 0),
(5, 'Prisustvo', 'Prisustvo', 'Prisustvo', 3, 10, 0, '3', 0),
(6, 'Zadaće', 'Zadaće', 'Zadaće', 4, 10, 0, '', 0);

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
  PRIMARY KEY (`student`,`predmet`,`komponenta`),
  KEY `predmet` (`predmet`),
  KEY `komponenta` (`komponenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `odluka` int(11) DEFAULT NULL,
  `datum_provjeren` tinyint(1) NOT NULL DEFAULT '0',
  `pasos_predmeta` int(11) DEFAULT NULL,
  PRIMARY KEY (`student`,`predmet`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `odluka` (`odluka`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  KEY `predmet` (`predmet`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `labgrupa` (`labgrupa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  PRIMARY KEY (`id`),
  KEY `kviz_pitanje` (`kviz_pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  KEY `kviz` (`kviz`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labgrupa`
--

DROP TABLE IF EXISTS `labgrupa`;
CREATE TABLE IF NOT EXISTS `labgrupa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `tip` enum('predavanja','vjezbe','tutorijali','vjezbe+tutorijali') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'vjezbe+tutorijali',
  `predmet` int(11) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `virtualna` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `predmet` (`predmet`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `labgrupa`
--

INSERT INTO `labgrupa` (`id`, `naziv`, `tip`, `predmet`, `akademska_godina`, `virtualna`) VALUES
(1, '(Svi studenti)', 'vjezbe+tutorijali', 1, 1, 1),
(2, '(Svi studenti)', 'vjezbe+tutorijali', 2, 1, 1),
(3, '(Svi studenti)', 'vjezbe+tutorijali', 3, 1, 1),
(4, '(Svi studenti)', 'vjezbe+tutorijali', 4, 1, 1),
(5, '(Svi studenti)', 'vjezbe+tutorijali', 5, 1, 1),
(6, '(Svi studenti)', 'vjezbe+tutorijali', 6, 1, 1);

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
  KEY `dogadjaj` (`dogadjaj`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log2`
--

DROP TABLE IF EXISTS `log2`;
CREATE TABLE IF NOT EXISTS `log2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userid` int(11) NOT NULL,
  `modul` int(11) NOT NULL,
  `dogadjaj` int(11) NOT NULL,
  `objekat1` int(11) NOT NULL,
  `objekat2` int(11) NOT NULL,
  `objekat3` int(11) NOT NULL,
  `ipaddress` varchar(256) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `objekat1` (`objekat1`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `log2`
--

INSERT INTO `log2` (`id`, `vrijeme`, `userid`, `modul`, `dogadjaj`, `objekat1`, `objekat2`, `objekat3`, `ipaddress`) VALUES
(1, '2019-11-18 19:22:44', 1, 1, 65, 2, 0, 0, '::1'),
(2, '2019-11-18 19:22:51', 1, 2, 320, 23, 0, 0, '::1'),
(3, '2019-11-18 19:23:04', 1, 2, 321, 23, 0, 0, '::1'),
(4, '2019-11-18 19:23:20', 1, 3, 115, 2, 0, 0, '::1'),
(5, '2019-11-18 19:23:30', 1, 3, 141, 2, 0, 0, '::1'),
(6, '2019-11-18 19:23:32', 1, 3, 171, 2, 0, 0, '::1'),
(7, '2019-11-18 19:25:17', 1, 3, 115, 3, 0, 0, '::1'),
(8, '2019-11-18 19:25:24', 1, 3, 141, 3, 0, 0, '::1'),
(9, '2019-11-18 19:25:26', 1, 3, 171, 3, 0, 0, '::1'),
(10, '2019-11-18 19:25:48', 1, 3, 14, 0, 0, 0, '::1'),
(11, '2019-11-18 19:26:02', 1, 3, 115, 4, 0, 0, '::1'),
(12, '2019-11-18 19:26:08', 1, 3, 141, 4, 0, 0, '::1'),
(13, '2019-11-18 19:26:10', 1, 3, 171, 4, 0, 0, '::1'),
(14, '2019-11-18 19:28:07', 1, 3, 115, 5, 0, 0, '::1'),
(15, '2019-11-18 19:28:12', 1, 3, 141, 5, 0, 0, '::1'),
(16, '2019-11-18 19:28:14', 1, 3, 171, 5, 0, 0, '::1'),
(17, '2019-11-18 19:29:30', 1, 3, 115, 6, 0, 0, '::1'),
(18, '2019-11-18 19:29:36', 1, 3, 141, 6, 0, 0, '::1'),
(19, '2019-11-18 19:29:38', 1, 3, 171, 6, 0, 0, '::1'),
(20, '2019-11-18 19:29:44', 1, 3, 115, 7, 0, 0, '::1'),
(21, '2019-11-18 19:29:54', 1, 3, 141, 7, 0, 0, '::1'),
(22, '2019-11-18 19:29:56', 1, 3, 171, 7, 0, 0, '::1'),
(23, '2019-11-18 19:30:03', 1, 1, 305, 1, 1, 0, '::1'),
(24, '2019-11-18 19:30:16', 1, 1, 237, 1, 0, 0, '::1'),
(25, '2019-11-18 19:30:46', 1, 1, 278, 2, 0, 0, '::1'),
(26, '2019-11-18 19:31:16', 1, 3, 115, 8, 0, 0, '::1'),
(27, '2019-11-18 19:31:21', 1, 3, 141, 8, 0, 0, '::1'),
(28, '2019-11-18 19:31:24', 1, 3, 171, 8, 0, 0, '::1'),
(29, '2019-11-18 19:31:34', 1, 3, 115, 9, 0, 0, '::1'),
(30, '2019-11-18 19:31:41', 1, 3, 141, 9, 0, 0, '::1'),
(31, '2019-11-18 19:31:47', 1, 3, 171, 9, 0, 0, '::1'),
(32, '2019-11-18 19:32:01', 1, 3, 158, 9, 1, 1, '::1'),
(33, '2019-11-18 19:32:06', 1, 3, 158, 9, 1, 1, '::1'),
(34, '2019-11-18 19:32:31', 1, 1, 305, 2, 1, 0, '::1'),
(35, '2019-11-18 19:32:39', 1, 1, 237, 2, 0, 0, '::1'),
(36, '2019-11-18 19:33:07', 1, 1, 305, 3, 1, 0, '::1'),
(37, '2019-11-18 19:33:17', 1, 1, 237, 3, 0, 0, '::1'),
(38, '2019-11-18 19:33:40', 1, 1, 305, 4, 1, 0, '::1'),
(39, '2019-11-18 19:33:47', 1, 1, 237, 4, 0, 0, '::1'),
(40, '2019-11-18 19:34:01', 1, 1, 305, 5, 1, 0, '::1'),
(41, '2019-11-18 19:34:13', 1, 1, 237, 5, 0, 0, '::1'),
(42, '2019-11-18 19:34:36', 1, 1, 305, 6, 1, 0, '::1'),
(43, '2019-11-18 19:34:56', 1, 1, 237, 6, 0, 0, '::1');

-- --------------------------------------------------------

--
-- Table structure for table `log2_blob`
--

DROP TABLE IF EXISTS `log2_blob`;
CREATE TABLE IF NOT EXISTS `log2_blob` (
  `log2` int(11) NOT NULL,
  `tekst` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`log2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `log2_blob`
--

INSERT INTO `log2_blob` (`log2`, `tekst`) VALUES
(5, 'profesor'),
(6, 'nastavnik'),
(8, 'silvester'),
(9, 'student'),
(10, 'C:\\wamp64\\www\\zamger\\studentska\\osobe.php:1904: Duplicate entry \'3-silvester\' for key \'PRIMARY\''),
(12, 'jason'),
(13, 'student'),
(15, 'semso'),
(16, 'student'),
(18, 'djemka'),
(19, 'student'),
(21, 'amina'),
(22, 'student'),
(27, 'profa'),
(28, 'nastavnik'),
(30, 'asistent'),
(31, 'nastavnik');

-- --------------------------------------------------------

--
-- Table structure for table `log2_dogadjaj`
--

DROP TABLE IF EXISTS `log2_dogadjaj`;
CREATE TABLE IF NOT EXISTS `log2_dogadjaj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opis` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `nivo` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `opis` (`opis`)
) ENGINE=InnoDB AUTO_INCREMENT=322 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `log2_dogadjaj`
--

INSERT INTO `log2_dogadjaj` (`id`, `opis`, `nivo`) VALUES
(1, 'prisustvo azurirano', 2),
(2, 'sesija istekla', 3),
(3, 'poslana zadaca (textarea)', 2),
(4, 'login', 1),
(5, 'logout', 1),
(6, 'sintaksna greska', 3),
(7, 'nepoznat korisnik', 3),
(8, 'pogresna sifra', 3),
(9, 'ne postoji fajl za zadacu', 3),
(10, 'uradio kviz', 2),
(11, 'poslana poruka', 2),
(12, 'student ne slusa predmet', 3),
(13, 'poslao praznu zadacu', 2),
(14, 'SQL greska', 3),
(15, 'isteklo vrijeme za slanje zadace', 3),
(16, 'poslana zadaca (attachment)', 2),
(17, 'greska pri slanju zadace (attachment)', 3),
(18, 'prisustvo - istekla sesija', 3),
(19, 'dodao fajl na projektu', 2),
(20, 'svi projekti su jos otkljucani', 3),
(21, 'nepostojeci modul', 3),
(22, 'nepoznat predmet', 3),
(23, 'registrovan cas', 2),
(24, 'uredio fajl na projektu', 2),
(25, 'obrisao fajl na projektu', 2),
(26, 'dodao link na projektu', 2),
(27, 'promijenjen datum ocjene', 4),
(28, 'dodana ocjena', 4),
(29, 'ponisten datum za izvoz', 2),
(30, 'bodovanje zadace', 2),
(31, 'student upisan na predmet (manuelno)', 4),
(32, 'student ispisan sa predmeta (manuelno)', 4),
(33, 'obrisan cas', 2),
(34, 'nije saradnik na predmetu', 3),
(35, 'preimenovana labgrupa', 2),
(36, 'vrijeme isteklo', 3),
(37, 'kreirana nova zadaca', 2),
(38, 'azurirana zadaca', 2),
(39, 'dodan novi autotest', 2),
(40, 'izmijenjen autotest', 2),
(41, 'dodan komentar na studenta', 2),
(42, 'vec popunjavan kviz', 3),
(43, 'zatrazena promjena licnih podataka', 2),
(44, 'dodao fajl na zavrsni', 2),
(45, 'azuriran sazetak zavrsnog rada', 2),
(46, 'obrisan fajl za zavrsni rad', 2),
(47, 'obrisan komentar', 2),
(48, 'promijenjena grupa studenta', 2),
(49, 'student ispisan sa grupe', 2),
(50, 'student upisan u grupu', 2),
(51, 'predmet nema virtuelnu grupu', 3),
(52, 'niko nije poslao zadacu', 3),
(53, 'nije nastavnik na predmetu', 3),
(54, 'kreirao ugovor o ucenju', 2),
(55, 'dodana tema zavrsnog rada', 2),
(56, 'izbrisana tema zavrsnog rada', 2),
(57, 'nova poruka poslana', 2),
(58, 'obrisana poruka', 2),
(59, 'nepostojeca poruka', 3),
(60, 'izmjena bodova za fiksnu komponentu', 2),
(61, 'izmijenjen kviz', 2),
(62, 'prihvacen zahtjev za promjenu podataka', 2),
(63, 'upisan rezultat ispita', 2),
(64, 'kreiran novi ispitni termin', 2),
(65, 'nepostojeci predmet', 3),
(66, 'zatrazeno brisanje slike', 2),
(67, 'odbijen zahtjev za promjenu podataka', 2),
(68, 'ispit - vrijednost &gt; max', 3),
(69, 'citanje fajla za attachment nije uspjelo', 3),
(70, 'pogresan tip datoteke', 3),
(71, 'izmjena ocjene', 4),
(72, 'nastavniku data prava na predmetu', 4),
(73, 'korisnik pokusao pristupiti modulu za koji nema permisije', 3),
(74, 'nije studentska, a pristupa tudjem izvjestaju', 3),
(75, 'student nije u odgovarajucoj labgrupi', 3),
(76, 'pristup nedozvoljenom modulu', 3),
(77, 'nepoznata akademska godina', 3),
(78, 'aktiviran studentski modul', 2),
(79, 'izmijenjeni parametri projekata na predmetu', 2),
(80, 'dodao projekat', 2),
(81, 'student prijavljen na projekat', 2),
(82, 'ne postoji komponenta za zadace', 3),
(83, 'kreiran novi ispit', 4),
(84, 'prijavljen na termin', 2),
(85, 'odjavljen sa termina', 2),
(86, 'email adresa dodana', 2),
(87, 'ima ogranicenje na labgrupu', 3),
(88, 'izmijenjen ispitni termin', 2),
(89, 'obrisan ispit', 4),
(90, 'izmijenio temu zavrsnog rada', 2),
(91, 'csrf token nije dobar', 3),
(92, 'pristupa tudjoj slici a student je', 3),
(93, 'deaktiviran studentski modul', 2),
(94, 'nije na projektu', 3),
(95, 'ispit - istekla sesija', 3),
(96, 'izmjenjen rezultat ispita', 2),
(97, 'obrisana ocjena', 4),
(98, 'ispit - datum konacne ocjene nije u trazenom formatu', 3),
(99, 'izbrisan rezultat ispita', 2),
(100, 'kreiran novi termin za prijemni ispit', 2),
(101, 'kreirana labgrupa', 2),
(102, 'kreirana labgrupa (masovni unos)', 2),
(103, 'student upisan u grupu (masovni unos)', 2),
(104, 'obrisana labgrupa', 2),
(105, 'nepostojeca labgrupa (brisanje)', 3),
(106, 'izbrisan ispitni termin', 2),
(107, 'promijenjen datum ispita', 2),
(108, 'id termina i ispita se ne poklapaju', 3),
(109, 'brzo unesen kandidat za prijemni', 2),
(110, 'upisana nova srednja skola', 2),
(111, 'ispit - konacna ocjena manja od 6', 3),
(112, 'izbrisan termin ispita', 2),
(113, 'brisem osobu sa prijemnog', 2),
(114, 'korisnik nije pronadjen na LDAPu', 3),
(115, 'dodan novi korisnik', 4),
(116, 'promijenjeni licni podaci korisnika', 2),
(117, 'izmjena kandidata za prijemni', 2),
(118, 'kreiran tip predmeta', 2),
(119, 'iskljucio savjet dana', 2),
(120, 'obrisana zadaca', 4),
(121, 'student ne slusa predmet za zadacu', 3),
(122, 'novi kandidat za prijemni', 2),
(123, 'poziv zamgerlog2 funkcije nije ispravan', 3),
(136, 'upisano novo mjesto rodjenja', 2),
(137, 'promjena opcine / statusa domacinstva za skolu', 2),
(138, 'promijenjen tip predmeta', 4),
(139, 'upisano novo mjesto (adresa)', 2),
(140, 'promjena opcine/drzave za mjesto rodjenja', 2),
(141, 'dodan novi login za korisnika', 2),
(142, 'nije uspjelo brisanje fajla za zavrsni', 3),
(143, 'id fajla nepostojeci ili ne odgovara zavrsnom', 3),
(144, 'nepostojeci file na zavrsnom radu', 3),
(145, 'citanje fajla za attachment nije uspjelo - zavrsni', 3),
(146, 'kreirao zahtjev za koliziju', 2),
(147, 'nisu definisani kriteriji za upis', 3),
(148, 'promijenjeni kriteriji za prijemni ispit', 4),
(149, 'student upisan na studij', 4),
(150, 'izmijenjen login za korisnika', 4),
(151, 'nema pravo pristupa poruci', 3),
(152, 'osoba nije kandidat na prijemnom', 3),
(153, 'ne postoji obrazac za osobu', 3),
(154, 'upisan rezultat na prijemnom', 2),
(155, 'email adresa obrisana', 2),
(156, 'email adresa promijenjena', 2),
(157, 'nepostojeci ispit ili nije sa predmeta', 3),
(158, 'nastavnik angazovan na predmetu', 4),
(159, 'ispit - datum konacne ocjene je nemoguc', 3),
(160, 'dodao biljesku na zavrsni rad', 2),
(161, 'proglasen za studenta', 4),
(162, 'postavljen broj indeksa', 4),
(163, 'student ispisan sa studija', 4),
(164, 'student upisan na predmet (obavezan)', 4),
(165, 'student ispisan sa predmeta (ispis sa studija)', 4),
(166, 'nepostojeca osoba', 3),
(167, 'nastavnik deangazovan sa predmeta', 4),
(168, 'citanje fajla za attachment nije uspjelo - zadaca', 3),
(169, 'promijenjen tip ispita', 4),
(170, 'nastavniku oduzeta prava na predmetu', 4),
(171, 'osobi data privilegija', 4),
(172, 'student upisan na predmet (izborni)', 4),
(173, 'kreirana nova anketa', 4),
(174, 'promijenjeni podaci za anketu', 4),
(175, 'aktivirana anketa', 4),
(176, 'azurirani podaci o izboru', 2),
(177, 'greska prilikom slanja fajla na zavrsni', 3),
(178, 'student upisan na predmet (preneseni)', 4),
(179, 'kreirao ponudu kursa zbog studenta', 4),
(180, 'izmijenjena poruka', 2),
(181, 'student upisan na predmet (kolizija)', 4),
(182, 'prihvacen zahtjev za koliziju', 4),
(183, 'student ispisan sa predmeta', 4),
(184, 'id studenta i predmeta ne odgovaraju', 3),
(185, 'dodana stavka u raspored', 2),
(186, 'dodan zahtjev za promjenu odsjeka', 2),
(187, 'osoba nema sliku', 3),
(188, 'kopiranje sa predmeta na kojem nema grupa', 3),
(189, 'prekopirane labgrupe', 2),
(190, 'nijedna zadaca nije aktivna', 3),
(191, 'zatrazeno postavljanje/promjena slike', 2),
(192, 'nepoznat id zahtjeva za promjenu podataka', 3),
(193, 'kreiran raspored', 2),
(194, 'ažurirana stavka u rasporedu', 2),
(195, 'citanje fajla za attachment nije uspjelo - postavka', 3),
(196, 'kreirana labgrupa (kopiranje)', 2),
(197, 'student upisan u grupu (kopiranje)', 2),
(198, 'izmijenio projekat', 2),
(199, 'prijavljen na projekat', 2),
(200, 'izbrisan projekat', 2),
(201, 'obrisan autotest', 2),
(202, 'izmijenjena ogranicenja nastavniku', 4),
(203, 'student prebacen na projekat', 2),
(204, 'student ispisan sa grupe (brisanje)', 2),
(205, 'odjavljen sa projekta', 2),
(206, 'postavljena slika za korisnika', 2),
(207, 'pokusao ispisati studenta sa studija koji ne slusa', 3),
(208, 'ne postoji attachment', 3),
(209, 'student ispisan sa grupe (masovni unos)', 2),
(210, 'poruka izmijenjena', 2),
(211, 'student odjavljen sa projekta', 2),
(212, 'odjavljen sa starog projekta', 2),
(213, 'nepostojeca labgrupa', 3),
(214, 'tekst poruke je prekratak', 3),
(215, 'korisnik vec postoji u bazi', 3),
(216, 'uputio novi zahtjev za potvrdu', 2),
(217, 'odustao od zahtjeva za potvrdu', 2),
(218, 'korisnik nikada nije studirao', 3),
(219, 'obradjen zahtjev za potvrdu', 2),
(220, 'dodao temu na projektu', 2),
(221, 'obrisan zahtjev za potvrdu', 2),
(222, 'privilegije', 3),
(223, 'deaktivirana anketa', 4),
(224, 'kopiranje grupa sa istog predmeta', 3),
(225, 'webide nedozvoljen pristup', 3),
(226, 'id zadace pogresan', 3),
(227, 'zadaca i ponudakursa ne odgovaraju', 3),
(228, 'nije nastavnik na predmetu za zadacu', 3),
(229, 'zadaca nema toliko zadataka', 3),
(230, 'promijenjene zamger opcije', 2),
(231, 'ispit - pogresne privilegije', 3),
(232, 'obrisana postavka zadace', 2),
(233, 'postavka ne postoji', 3),
(234, 'obrisan zahtjev za promjenu odsjeka', 2),
(235, 'upisana nova nacionalnost', 2),
(236, 'id zadace i predmeta se ne poklapaju', 3),
(237, 'izmijenjeni podaci o predmetu', 4),
(238, 'korisnik nema nikakve privilegije', 3),
(239, 'uspjesno popunjena anketa', 2),
(240, 'anketa vec popunjena', 3),
(241, 'ilegalan hash code', 3),
(242, 'ilegalan CSRF token', 3),
(243, 'nije definisan predmet a korisnik je logiran', 3),
(244, 'preview ankete privilegije', 3),
(245, 'ag je sada', 2),
(246, 'predmet je', 2),
(247, 'naziv nije ispravan', 3),
(248, 'pokusao ispisati studenta sa semestra koji ne slusa', 3),
(249, 'student pristupa komentarima', 2),
(250, 'id ankete i godine ne odgovaraju', 2),
(251, 'nepostojeca anketa', 2),
(252, 'ispit - vrijednost nije ni broj ni /', 2),
(253, 'dodan kviz', 2),
(254, 'nepostojeca virtualna labgrupa', 2),
(255, 'dodano pitanje na kviz', 2),
(256, 'dodan odgovor na pitanje', 2),
(257, 'izmijenjeno pitanje na kvizu', 2),
(258, 'obrisan odgovor sa kviza', 2),
(259, 'dodan uslov za autotest', 2),
(260, 'izmijenjen uslov za autotest', 2),
(261, 'odgovor proglasen za (ne)tacan', 2),
(262, 'prisustvo - nepostojeci cas', 3),
(263, 'poslao popunjen kviz a nema stavke u student_kviz', 3),
(264, 'nema pravo pristupa zavrsnom radu', 3),
(265, 'popunjava alumni anketu a nije master', 3),
(266, 'popunjava alumni anketu a nije zavrsio master', 3),
(267, 'nepoznat student', 3),
(268, 'uredio link na projektu', 2),
(269, 'citanje fajla za attachment nije uspjelo - projekat', 3),
(270, 'nepostojeci file na projektu', 3),
(271, 'pristup nepostojecem kvizu', 3),
(272, 'student nije na predmetu', 3),
(273, 'nepostojeci rad', 3),
(274, 'nepostojeca zadaca', 3),
(275, 'dodani podaci o izboru', 2),
(276, 'nepoznat ispit', 3),
(277, 'dodana ponuda kursa na predmet', 4),
(278, 'obrisana ponudakursa', 4),
(279, 'pokusao ograniciti sve grupe nastavniku', 3),
(280, 'kreirana virtuelna labgrupa', 2),
(281, 'nije pronadjena ponudakursa', 3),
(282, 'promijenjena ponuda kursa za studenta', 4),
(283, 'ispisujem studenta sa dvostruke ponude kursa', 4),
(284, 'student upisan na predmet', 4),
(285, 'promijenjena a.g. ocjene', 2),
(286, 'student ne slusa predmet (ispis)', 3),
(287, 'pristup nepostojećoj anketi', 3),
(288, 'nije ponudjen predmet', 3),
(289, 'osobi oduzeta privilegija', 4),
(290, 'obrisan login za korisnika', 4),
(291, 'obrisan uslov za autotest', 2),
(292, 'pristup nepostojecoj poruci', 3),
(293, 'pokusao ispisati studenta koji nije upisan', 3),
(294, 'obrisano pitanje sa kviza', 2),
(295, 'smanjen broj zadataka u zadaci', 2),
(296, 'prekopirana pitanja sa kviza', 2),
(297, 'ispit - nepoznat ispit ili nije saradnik', 3),
(298, 'projekat popunjen', 3),
(299, 'pretraga - istekla sesija', 3),
(300, 'prijemni - istekla sesija', 3),
(301, 'popunjen kapacitet ekstra za predmet', 3),
(302, 'popunjen kapacitet za predmet', 3),
(303, 'iskopirani autotestovi', 2),
(304, 'nepostojeca tema zavrsnog rada', 3),
(305, 'kreiran novi predmet', 4),
(306, 'autotestiran student', 2),
(307, 'spoofing autotesta', 3),
(308, 'login ne postoji na LDAPu', 3),
(309, 'dodao temu zavrsnog rada', 2),
(310, 'id zavrsnog rada i predmeta se ne poklapaju', 3),
(311, 'neispravni parametri', 3),
(312, 'nije studentska', 3),
(313, 'nije nastavnik', 3),
(314, 'student ispisan sa grupe (kopiranje)', 2),
(315, 'pokusaj slanja/izmjene poruke sa opsegom', 3),
(316, 'poruka ima nepoznat opseg', 3),
(317, 'poruka ima nepoznatog primaoca (opseg: godina studija)', 3),
(318, 'nedostaje slog u tabeli akademska_godina_predmet', 3),
(319, 'obrisana slika za korisnika', 2),
(320, 'kreiran plan studija', 2),
(321, 'plan studija proglasen za vazeci', 2);

-- --------------------------------------------------------

--
-- Table structure for table `log2_modul`
--

DROP TABLE IF EXISTS `log2_modul`;
CREATE TABLE IF NOT EXISTS `log2_modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `naziv` (`naziv`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `log2_modul`
--

INSERT INTO `log2_modul` (`id`, `naziv`) VALUES
(3, 'studentska/osobe'),
(2, 'studentska/plan'),
(1, 'studentska/predmeti');

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
  `opcina_van_bih` varchar(40) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `opcina` (`opcina`),
  KEY `drzava` (`drzava`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `vrijeme_promjene` bigint(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `moodle_id` (`moodle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `nacin_studiranja`
--

INSERT INTO `nacin_studiranja` (`id`, `naziv`, `moguc_upis`) VALUES
(1, 'Redovan', 1),
(2, 'Paralelan', 0),
(3, 'Redovan samofinansirajući', 1),
(4, 'Vanredan', 1),
(5, 'DL', 1),
(6, 'Mobilnost', 0);

-- --------------------------------------------------------

--
-- Table structure for table `nacionalnost`
--

DROP TABLE IF EXISTS `nacionalnost`;
CREATE TABLE IF NOT EXISTS `nacionalnost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  KEY `akademska_godina` (`akademska_godina`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `naucni_stepen`
--

DROP TABLE IF EXISTS `naucni_stepen`;
CREATE TABLE IF NOT EXISTS `naucni_stepen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `naucni_stepen`
--

INSERT INTO `naucni_stepen` (`id`, `naziv`, `titula`) VALUES
(1, 'Doktor nauka', 'dr'),
(2, 'Magistar nauka', 'mr'),
(6, 'Bez naučnog stepena', '');

-- --------------------------------------------------------

--
-- Table structure for table `oblast`
--

DROP TABLE IF EXISTS `oblast`;
CREATE TABLE IF NOT EXISTS `oblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institucija` int(11) NOT NULL,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `institucija` (`institucija`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  PRIMARY KEY (`id`),
  KEY `student` (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ogranicenje`
--

DROP TABLE IF EXISTS `ogranicenje`;
CREATE TABLE IF NOT EXISTS `ogranicenje` (
  `nastavnik` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nastavnik`,`labgrupa`),
  KEY `labgrupa` (`labgrupa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opcina`
--

DROP TABLE IF EXISTS `opcina`;
CREATE TABLE IF NOT EXISTS `opcina` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `imeoca` varchar(30) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `prezimeoca` varchar(30) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `imemajke` varchar(30) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `prezimemajke` varchar(30) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `spol` enum('M','Z','') COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `brindexa` varchar(10) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `datum_rodjenja` date DEFAULT NULL,
  `mjesto_rodjenja` int(11) DEFAULT NULL,
  `nacionalnost` int(11) DEFAULT NULL,
  `drzavljanstvo` int(11) DEFAULT NULL,
  `boracke_kategorije` tinyint(1) NOT NULL DEFAULT '0',
  `jmbg` varchar(14) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `adresa` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `adresa_mjesto` int(11) DEFAULT NULL,
  `telefon` varchar(15) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `kanton` int(11) DEFAULT NULL,
  `treba_brisati` tinyint(1) NOT NULL DEFAULT '0',
  `strucni_stepen` int(11) NOT NULL DEFAULT '5',
  `naucni_stepen` int(11) NOT NULL DEFAULT '6',
  `slika` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `nacin_stanovanja` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mjesto_rodjenja` (`mjesto_rodjenja`),
  KEY `adresa_mjesto` (`adresa_mjesto`),
  KEY `kanton` (`kanton`),
  KEY `nacionalnost` (`nacionalnost`),
  KEY `drzavljanstvo` (`drzavljanstvo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `osoba`
--

INSERT INTO `osoba` (`id`, `ime`, `prezime`, `imeoca`, `prezimeoca`, `imemajke`, `prezimemajke`, `spol`, `brindexa`, `datum_rodjenja`, `mjesto_rodjenja`, `nacionalnost`, `drzavljanstvo`, `boracke_kategorije`, `jmbg`, `adresa`, `adresa_mjesto`, `telefon`, `kanton`, `treba_brisati`, `strucni_stepen`, `naucni_stepen`, `slika`, `nacin_stanovanja`) VALUES
(1, 'Site', 'Admin', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 0, '', '', NULL, '', NULL, 0, 5, 6, '', NULL),
(2, 'Profesor', 'Baltazar', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 0, '', '', NULL, '', NULL, 0, 5, 6, '', NULL),
(3, 'Silvester', 'Stalone', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 0, '', '', NULL, '', NULL, 0, 5, 6, '', NULL),
(4, 'Jason', 'Statham', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 0, '', '', NULL, '', NULL, 0, 5, 6, '', NULL),
(5, 'Šemso', 'Poplava', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 0, '', '', NULL, '', NULL, 0, 5, 6, '', NULL),
(6, 'Đemina', 'Karalić', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 0, '', '', NULL, '', NULL, 0, 5, 6, '', NULL),
(7, 'Amina', 'Spahić', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 0, '', '', NULL, '', NULL, 0, 5, 6, '', NULL),
(8, 'Profesor', 'Doktor', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 0, '', '', NULL, '', NULL, 0, 5, 6, '', NULL),
(9, 'Asistent', 'Najjači', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 0, '', '', NULL, '', NULL, 0, 5, 6, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `osoba_posebne_kategorije`
--

DROP TABLE IF EXISTS `osoba_posebne_kategorije`;
CREATE TABLE IF NOT EXISTS `osoba_posebne_kategorije` (
  `osoba` int(11) NOT NULL,
  `posebne_kategorije` int(11) NOT NULL,
  `br_rjesenja` varchar(128) COLLATE utf8_slovenian_ci NOT NULL,
  `datum_rjesenja` date NOT NULL,
  `organ_izdavanja` varchar(256) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pasos_predmeta`
--

DROP TABLE IF EXISTS `pasos_predmeta`;
CREATE TABLE IF NOT EXISTS `pasos_predmeta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `predmet` int(11) NOT NULL,
  `usvojen` tinyint(4) NOT NULL DEFAULT '0',
  `predlozio` int(11) NOT NULL,
  `vrijeme_prijedloga` datetime NOT NULL,
  `komentar_prijedloga` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `sifra` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv` text COLLATE utf8_slovenian_ci NOT NULL,
  `naziv_en` text COLLATE utf8_slovenian_ci NOT NULL,
  `ects` float NOT NULL,
  `sati_predavanja` int(11) NOT NULL,
  `sati_vjezbi` int(11) NOT NULL,
  `sati_tutorijala` int(11) NOT NULL,
  `cilj_kursa` text COLLATE utf8_slovenian_ci NOT NULL,
  `cilj_kursa_en` text COLLATE utf8_slovenian_ci NOT NULL,
  `program` text COLLATE utf8_slovenian_ci NOT NULL,
  `program_en` text COLLATE utf8_slovenian_ci NOT NULL,
  `obavezna_literatura` text COLLATE utf8_slovenian_ci NOT NULL,
  `dopunska_literatura` text COLLATE utf8_slovenian_ci NOT NULL,
  `didakticke_metode` text COLLATE utf8_slovenian_ci NOT NULL,
  `didakticke_metode_en` text COLLATE utf8_slovenian_ci NOT NULL,
  `nacin_provjere_znanja` text COLLATE utf8_slovenian_ci NOT NULL,
  `nacin_provjere_znanja_en` text COLLATE utf8_slovenian_ci NOT NULL,
  `napomene` text COLLATE utf8_slovenian_ci NOT NULL,
  `napomene_en` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=602 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci COMMENT='Tip je MyISAM jer sa InnoDB se dobija Error 139';

-- --------------------------------------------------------

--
-- Table structure for table `plan_izborni_slot`
--

DROP TABLE IF EXISTS `plan_izborni_slot`;
CREATE TABLE IF NOT EXISTS `plan_izborni_slot` (
  `id` int(11) NOT NULL,
  `pasos_predmeta` int(11) NOT NULL,
  PRIMARY KEY (`id`,`pasos_predmeta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plan_studija`
--

DROP TABLE IF EXISTS `plan_studija`;
CREATE TABLE IF NOT EXISTS `plan_studija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studij` int(11) NOT NULL,
  `godina_vazenja` int(11) DEFAULT NULL,
  `usvojen` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `studij` (`studij`),
  KEY `godina_vazenja` (`godina_vazenja`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `plan_studija`
--

INSERT INTO `plan_studija` (`id`, `studij`, `godina_vazenja`, `usvojen`) VALUES
(23, 2, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `plan_studija_permisije`
--

DROP TABLE IF EXISTS `plan_studija_permisije`;
CREATE TABLE IF NOT EXISTS `plan_studija_permisije` (
  `plan_studija` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  PRIMARY KEY (`plan_studija`,`predmet`,`osoba`),
  KEY `predmet` (`predmet`),
  KEY `osoba` (`osoba`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plan_studija_predmet`
--

DROP TABLE IF EXISTS `plan_studija_predmet`;
CREATE TABLE IF NOT EXISTS `plan_studija_predmet` (
  `plan_studija` int(11) NOT NULL,
  `pasos_predmeta` int(11) DEFAULT NULL,
  `plan_izborni_slot` int(11) DEFAULT NULL,
  `semestar` tinyint(3) NOT NULL,
  `obavezan` tinyint(1) NOT NULL,
  `potvrdjen` tinyint(1) NOT NULL,
  KEY `plan_studija` (`plan_studija`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `podoblast`
--

DROP TABLE IF EXISTS `podoblast`;
CREATE TABLE IF NOT EXISTS `podoblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oblast` int(11) NOT NULL,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  KEY `predmet` (`predmet`),
  KEY `studij` (`studij`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `ponudakursa`
--

INSERT INTO `ponudakursa` (`id`, `predmet`, `studij`, `semestar`, `obavezan`, `akademska_godina`) VALUES
(1, 1, 2, 1, 1, 1),
(3, 1, 3, 1, 1, 1),
(4, 1, 1, 1, 1, 1),
(5, 1, 4, 1, 1, 1),
(6, 2, 2, 2, 1, 1),
(7, 2, 3, 2, 1, 1),
(8, 2, 1, 2, 1, 1),
(9, 2, 4, 2, 1, 1),
(10, 3, 2, 3, 1, 1),
(11, 4, 2, 4, 1, 1),
(12, 5, 2, 5, 1, 1),
(13, 6, 2, 6, 1, 1);

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
  `procitana` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posebne_kategorije`
--

DROP TABLE IF EXISTS `posebne_kategorije`;
CREATE TABLE IF NOT EXISTS `posebne_kategorije` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `posebne_kategorije`
--

INSERT INTO `posebne_kategorije` (`id`, `naziv`) VALUES
(1, 'Djeca šehida i poginulih boraca'),
(2, 'Djeca ratnih vojnih invalida'),
(3, 'Djeca demobilisanih boraca'),
(4, 'Djeca nosilaca ratnih priznanja'),
(5, 'Djeca bez oba roditelja');

-- --------------------------------------------------------

--
-- Table structure for table `predmet`
--

DROP TABLE IF EXISTS `predmet`;
CREATE TABLE IF NOT EXISTS `predmet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sifra` varchar(30) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `institucija` int(11) DEFAULT NULL,
  `kratki_naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `ects` float NOT NULL DEFAULT '0',
  `sati_predavanja` int(11) NOT NULL DEFAULT '0',
  `sati_vjezbi` int(11) NOT NULL DEFAULT '0',
  `sati_tutorijala` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `institucija` (`institucija`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `predmet`
--

INSERT INTO `predmet` (`id`, `sifra`, `naziv`, `institucija`, `kratki_naziv`, `ects`, `sati_predavanja`, `sati_vjezbi`, `sati_tutorijala`) VALUES
(1, '1', 'Inženjerska Fizika I', 1, 'IFI', 6, 30, 20, 10),
(2, '2', 'Električni Krugovi I', 1, 'EKI', 5, 30, 20, 20),
(3, '3', 'Analogna elektronka', 1, 'AE', 30, 20, 20, 20),
(4, '4', 'Linearni sistemi automatskog upravljanja', 1, 'LSAU', 6, 30, 20, 20),
(5, '5', 'Signali i sistemi', 1, 'SIS', 5, 28, 18, 6),
(6, '6', 'Dinamika fluida i toplotnih sistema', 1, 'DFTS', 5, 26, 18, 14);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preduvjeti`
--

DROP TABLE IF EXISTS `preduvjeti`;
CREATE TABLE IF NOT EXISTS `preduvjeti` (
  `predmet` int(11) NOT NULL,
  `preduvjet` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preference`
--

DROP TABLE IF EXISTS `preference`;
CREATE TABLE IF NOT EXISTS `preference` (
  `korisnik` int(11) NOT NULL,
  `preferenca` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijednost` varchar(100) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `preference`
--

INSERT INTO `preference` (`korisnik`, `preferenca`, `vrijednost`) VALUES
(0, 'verzija-baze', '1536314785');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `predsjednik_komisije` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prijemni_vazni_datumi`
--

DROP TABLE IF EXISTS `prijemni_vazni_datumi`;
CREATE TABLE IF NOT EXISTS `prijemni_vazni_datumi` (
  `prijemni_termin` int(11) NOT NULL,
  `id_datuma` int(11) NOT NULL,
  `datum` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `privilegije`
--

DROP TABLE IF EXISTS `privilegije`;
CREATE TABLE IF NOT EXISTS `privilegije` (
  `osoba` int(11) NOT NULL,
  `privilegija` varchar(30) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `privilegije`
--

INSERT INTO `privilegije` (`osoba`, `privilegija`) VALUES
(1, 'siteadmin'),
(1, 'studentska'),
(1, 'student'),
(1, 'nastavnik'),
(2, 'nastavnik'),
(3, 'student'),
(4, 'student'),
(5, 'student'),
(6, 'student'),
(7, 'student'),
(8, 'nastavnik'),
(9, 'nastavnik');

-- --------------------------------------------------------

--
-- Table structure for table `priznavanje`
--

DROP TABLE IF EXISTS `priznavanje`;
CREATE TABLE IF NOT EXISTS `priznavanje` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `ciklus` int(1) NOT NULL,
  `naziv_predmeta` varchar(250) COLLATE utf8_slovenian_ci NOT NULL,
  `sifra_predmeta` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `ects` float NOT NULL,
  `ocjena` int(11) NOT NULL,
  `odluka` int(11) NOT NULL,
  `strana_institucija` varchar(250) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `ace` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `kompajler` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `opcije_kompajlera` text COLLATE utf8_slovenian_ci NOT NULL,
  `opcije_kompajlera_debug` text COLLATE utf8_slovenian_ci NOT NULL,
  `debugger` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `profiler` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `opcije_profilera` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `programskijezik`
--

INSERT INTO `programskijezik` (`id`, `naziv`, `geshi`, `ekstenzija`, `ace`, `kompajler`, `opcije_kompajlera`, `opcije_kompajlera_debug`, `debugger`, `profiler`, `opcije_profilera`) VALUES
(0, '---Nije odre&#273;en---', '', '', '', '', '', '', '', '', ''),
(1, 'C', 'C', '.c', 'c_cpp', 'gcc', '-O1 -Wall -Wuninitialized -Winit-self -Wno-unused-result -Wfloat-equal -Wno-sign-compare -Werror=implicit-function-declaration -Werror=vla -pedantic -lm -pass-exit-codes', '-ggdb -lm -pass-exit-codes', 'gdb', 'valgrind', '--leak-check=full'),
(2, 'C++', 'C++', '.cpp', 'c_cpp', 'g++', '-O1 -Wall -Wuninitialized -Winit-self -Wfloat-equal -Wno-sign-compare -Werror=implicit-function-declaration -Werror=vla -pedantic -lm -pass-exit-codes', '-ggdb -lm -pass-exit-codes', 'gdb', 'valgrind', '--leak-check=full'),
(3, 'Java', 'Java', '.java', 'java', 'javac', '-encoding cp1250', '', '', '', ''),
(4, 'Matlab .m', 'Matlab M', '.m', '', '', '', '', '', '', ''),
(5, 'HTML', 'HTML', '.html', 'html', '', '', '', '', '', ''),
(6, 'PHP', 'PHP', '.php', 'php', '', '', '', '', '', ''),
(7, 'C++11', 'C++', '.cpp', 'c_cpp', 'g++', '-std=c++11 -O1 -Wall -Wuninitialized -Winit-self -Wfloat-equal -Wno-sign-compare -Werror=implicit-function-declaration -Werror=vla -pedantic -lm -pass-exit-codes', '-std=c++11 -ggdb -lm -pass-exit-codes', 'gdb', 'valgrind', '--leak-check=full'),
(8, 'JavaScript', 'JAVASCRIPT', '.js', '', '', '', '', '', '', ''),
(9, 'Python', 'Python', '.py', 'python', 'python3', '', '', '', '', '');

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
  PRIMARY KEY (`id`),
  KEY `predmet` (`predmet`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projekat_file_diff`
--

DROP TABLE IF EXISTS `projekat_file_diff`;
CREATE TABLE IF NOT EXISTS `projekat_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `strucni_stepen` int(11) NOT NULL DEFAULT '5',
  `naucni_stepen` int(11) NOT NULL DEFAULT '6',
  `slika` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme_zahtjeva` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `dodatni_bodovi` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rss`
--

DROP TABLE IF EXISTS `rss`;
CREATE TABLE IF NOT EXISTS `rss` (
  `id` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `auth` int(11) NOT NULL,
  `access` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `rss`
--

INSERT INTO `rss` (`id`, `auth`, `access`) VALUES
('xehg7ab03E', 1, '1970-01-01 00:00:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `savjet_dana`
--

INSERT INTO `savjet_dana` (`id`, `tekst`, `vrsta_korisnika`) VALUES
(1, '<p>...da je Charles Babbage, matematičar i filozof iz 19. vijeka za kojeg se smatra da je otac ideje prvog programabilnog računara, u svojoj biografiji napisao:</p>\r\n\r\n<p><i>U dva navrata su me pitali</i></p>\r\n\r\n<p><i>\"Molim Vas gospodine Babbage, ako u Vašu mašinu stavite pogrešne brojeve, da li će izaći tačni odgovori?\"</i></p>\r\n\r\n<p><i>Jednom je to bio pripadnik Gornjeg, a jednom Donjeg doma. Ne mogu da potpuno shvatim tu vrstu konfuzije ideja koja bi rezultirala takvim pitanjem.</i></p>', 'nastavnik'),
(2, '<p>...da sada možete podesiti sistem bodovanja na vašem predmetu (broj bodova koje studenti dobijaju za ispite, prisustvo, zadaće, seminarski rad, projekte...)?</p>\r\n<ul><li>Kliknite na dugme [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite opciju <i>Sistem bodovanja</i>.</li>\r\n<li>Slijedite uputstva.</li></ul>\r\n<p><b>Važna napomena:</b> Promjena sistema bodovanja može dovesti do gubitka do sada upisanih bodova na predmetu!</p>', 'nastavnik'),
(3, '<p>...da možete pristupiti Dosjeu studenta sa svim podacima koji se tiču uspjeha studenta na datom predmetu? Dosje studenta sadrži, između ostalog:</p>\r\n<ul><li>Fotografiju studenta;</li>\r\n<li>Koliko puta je student ponavljao predmet, da li je u koliziji, da li je prenio predmet na višu godinu;</li>\r\n<li>Sve podatke sa pogleda grupe (prisustvo, zadaće, rezultati ispita, konačna ocjena) sa mogućnošću izmjene svakog podatka;</li>\r\n<li>Za ispite i konačnu ocjenu možete vidjeti dnevnik izmjena sa informacijom ko je i kada izmijenio podatak.</li>\r\n<li>Brze linkove na dosjee istog studenta sa ranijih akademskih godina (ako je ponavljao/la predmet).</li></ul>\r\n\r\n<p>Dosjeu studenta možete pristupiti tako što kliknete na ime studenta u pregledu grupe. Na vašem početnom ekranu kliknite na ime grupe ili link <i>(Svi studenti)</i>, a zatim na ime i prezime studenta.</p>\r\n	\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 6.\r\n</i></p>', 'nastavnik'),
(4, '<p>...da možete ostavljati kratke tekstualne komentare na rad studenata?</p>\r\n<p>Na vašem početnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>. Zatim kliknite na ikonu sa oblačićem pored imena studenta:<br>\r\n<img src=\"static/images/16x16/comment_blue.png\" width=\"16\" height=\"16\"></p>\r\n<p>Možete dobiti pregled studenata sa komentarima na sljedeći način:<br>\r\n<ul><li>Pored naziva predmeta kliknite na link [EDIT].</li>\r\n<li>Zatim s lijeve strane kliknite na link <i>Izvještaji</i>.</li>\r\n<li>Konačno, kliknite na opciju <i>Spisak studenata</i> - <i>Sa komentarima na rad</i>.</li></ul>\r\n<p>Na istog studenta možete ostaviti više komentara pri čemu je svaki komentar datiran i označeno je ko ga je ostavio.</p>	\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 7-8.</i></p>', 'nastavnik'),
(5, '<p>...da možete brzo i lako pomoću nekog spreadsheet programa (npr. MS Excel) kreirati grupe na predmetu?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src=\"static/images/32x32/excel.png\" width=\"32\" height=\"32\"><br>\r\nDobićete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte naziv grupe npr. \"Grupa 1\" (bez navodnika). Koristite Copy i Paste opcije Excela da biste brzo definisali grupu za sve studente.</li>\r\n<li>Kada završite definisanje grupa, koristeći tipku Shift i tipke sa strelicama označite imena studenata i imena grupa. Nemojte označiti naslov niti redni broj. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga \r\notvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja. Sada s lijeve strane izaberite opciju <i>Grupe za predavanja i vježbe</i>.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos studenata u grupe</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li></ul>\r\n<p>Ovim su grupe kreirane!</p>\r\n\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 16.</i></p>', 'nastavnik'),
(6, '<p>...da možete brzo i lako ocijeniti zadaću svim studentima na predmetu ili u grupi, koristeći neki spreadsheet program (npr. MS Excel)?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, a s desne izaberite izvještaj <i>Spisak studenata</i> - <i>Bez grupa</i>. Alternativno, ako želite unositi ocjene samo za jednu grupu, možete koristiti izvještaj <i>Jedna kolona po grupama</i> pa u Excelu pobrisati sve grupe osim one koja vas interesuje.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src=\"static/images/32x32/excel.png\" width=\"32\" height=\"32\"></li>\r\n<li>Pored imena svakog studenta nalazi se broj indeksa. <b>Umjesto broja indeksa</b> upišite broj bodova ostvarenih na određenom zadatku određene zadaće.</li>\r\n<li>Korištenjem tipke Shift i tipki sa strelicama izaberite samo imena studenata i bodove. Nemojte selektovati naslov ili redne brojeve. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite \r\nse na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja. Sada s lijeve strane izaberite opciju <i>Kreiranje i unos zadaća</i>.</li>\r\n<li>Uvjerite se da je na spisku <i>Postojeće zadaće</i> definisana zadaća koju želite unijeti. Ako nije, popunite formular ispod naslova <i>Kreiranje zadaće</i> sa odgovarajućim podacima.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos zadaća</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>U polju <i>Izaberite zadaću</i> odaberite upravo kreiranu zadaću. Ako zadaća ima više zadataka, u polju <i>Izaberite zadatak</i> odaberite koji zadatak masovno unosite.\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li>\r\n<li>Ovu proceduru sada vrlo lako možete ponoviti za sve zadatke i sve zadaće zato što već imate u Excelu sve podatke osim broja bodova.</li></ul>\r\n<p>Ovim su rezultati zadaće uneseni za sve studente!</p>\r\n\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 27-28.</i></p>', 'nastavnik'),
(7, '<p>...da možete preuzeti odjednom sve zadaće koje su poslali studenti u grupi u formi ZIP fajla, pri čemu su zadaće imenovane po sistemu Prezime_Ime_BrojIndeksa?</p>\r\n<ul><li>Na vašem početnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>.</li>\r\n<li>U zaglavlju tabele sa spiskom studenata možete vidjeti navedene zadaće: npr. Zadaća 1, Zadaća 2 itd.</li>\r\n<li>Ispod naziva svake zadaće nalazi se riječ <i>Download</i> koja predstavlja link - kliknite na njega.</li></ul>	\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 11-12.</i></p>', 'nastavnik'),
(8, '<p>...da možete imati više termina jednog ispita? Pri tome se datum termina ne mora poklapati sa datumom ispita.</p>\r\n<p>Datum ispita se daje samo okvirno, kako bi se po nečemu razlikovali npr. junski rok i septembarski rok. Datum koji studentu piše na prijavi je datum koji pridružite terminu za prijavu ispita.</p>\r\n<p>Da biste definisali termine ispita:</p>\r\n<ul><li>Najprije kreirajte ispit, tako što ćete kliknuti na link [EDIT] a zatim izabrati opciju Ispiti s lijeve strane. Zatim popunite formular ispod naslova <i>Kreiranje novog ispita</i>.</li>\r\n<li>U tabeli ispita možete vidjeti novi ispit. Desno od ispita možete vidjeti link <i>Termini</i>. Kliknite na njega.</li>\r\n<li>Zatim kreirajte proizvoljan broj termina popunjavajući formular ispod naslova <i>Registrovanje novog termina</i>.</li></ul>\r\n\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, poglavlje \"Prijavljivanje za ispit\" (str. 21-26).</i></p>', 'nastavnik'),
(9, '<p>...da, u slučaju da se neki student nije prijavio/la za vaš ispit, možete ih manuelno prijaviti na termin kako bi imao/la korektan datum na prijavi?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta. S lijeve strane izaberite link <i>Ispiti</i>.</li>\r\n<li>U tabeli ispita locirajte ispit koji želite i kliknite na link <i>Termini</i> desno od željenog ispita.</li>\r\n<li>Ispod naslova <i>Objavljeni termini</i> izaberite željeni termin i kliknite na link <i>Studenti</i> desno od željenog termina.</li>\r\n<li>Sada možete vidjeti sve studente koji su se prijavili za termin. Pored imena i prezimena studenta možete vidjeti dugme <i>Izbaci</i> kako student više ne bi bio prijavljen za taj termin.</li>\r\n<li>Ispod tabele studenata možete vidjeti padajući spisak svih studenata upisanih na vaš predmet. Izaberite na padajućem spisku studenta kojeg želite prijaviti za termin i kliknite na dugme <i>Dodaj</i>.</li></ul>\r\n\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" \r\ntarget=\"_new\">Uputstvima za upotrebu</a>, str. 26.</i></p>', 'nastavnik'),
(10, '<p>...da upisom studenata na predmete u Zamgeru sada u potpunosti rukuje Studentska služba?</p>\r\n<p>Ako vam se pojavi student kojeg nemate na spiskovima u Zamgeru, recite mu da se <b>obavezno</b> javi u Studentsku službu, ne samo radi vašeg predmeta nego generalno radi regulisanja statusa (npr. neplaćenih školarina, taksi i slično).</p>', 'nastavnik'),
(11, '<p>...da svaki korisnik može imati jedan od tri nivoa pristupa bilo kojem predmetu:</p><ul><li><i>asistent</i> - može unositi prisustvo časovima i ocjenjivati zadaće</li><li><i>super-asistent</i> - može unositi sve podatke osim konačne ocjene</li><li><i>nastavnik</i> - može unositi i konačnu ocjenu.</li></ul><p>Početni nivoi pristupa se određuju na osnovu zvanično usvojenog nastavnog ansambla, a u slučaju da želite promijeniti nivo pristupa bez izmjena u ansamblu (npr. kako biste asistentu dali privilegije unosa rezultata ispita), kontaktirajte Studentsku službu.</p>\r\n\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 3-4.</i></p>', 'nastavnik'),
(12, '<p>...da možete ograničiti format datoteke u kojem studenti šalju zadaću?</p>\r\n<p>Prilikom kreiranja nove zadaće, označite opciju pod nazivom <i>Slanje zadatka u formi attachmenta</i>. Pojaviće se spisak tipova datoteka koje studenti mogu koristiti prilikom slanja zadaće u formi attachmenta.</p>\r\n<p>Izaberite jedan ili više formata kako bi studenti dobili grešku u slučaju da pokušaju poslati zadaću u nekom od formata koje niste izabrali. Ako ne izaberete nijednu od ponuđenih opcija, biće dozvoljeni svi formati datoteka, uključujući i one koji nisu navedeni na spisku.</p>\r\n\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 26-27.</i></p>', 'nastavnik'),
(13, '<p>...da možete utjecati na format u kojem se izvještaj prosljeđuje Excelu kada kliknete na Excel ikonu u gornjem desnom uglu izvještaja?<br>\r\n<img src=\"static/images/32x32/excel.png\" width=\"32\" height=\"32\"></p>\r\n<p>Može se desiti da izvještaj ne izgleda potpuno kako treba u vašem spreadsheet programu. Podaci se šalju u CSV formatu pod pretpostavkom da koristite regionalne postavke za BiH (ili Hrvatsku ili Srbiju). Ako izvještaj u vašem programu ne izgleda kako treba, slijedi nekoliko savjeta kako možete utjecati na to.</p>\r\n<ul><li>Ako se svi podaci nalaze u jednoj koloni, vjerovatno je da koristite sistem sa Američkim regionalnim postavkama. U vašem Profilu možete pod Zamger opcije izabrati CSV separator \"zarez\" umjesto \"tačka-zarez\", ali vjerovatno je da vam naša slova i dalje neće izgledati kako treba.</li>\r\n<li>Moguće je da će dokument izgledati ispravno, osim slova sa afrikatima koja će biti zamijenjena nekim drugim. Na žalost, ne postoji način da se ovo riješi. Excel može učitati CSV datoteke \r\nisključivo u formatu koji ne podržava prikaz naših slova. Možete uraditi zamjenu koristeći Replace opciju vašeg programa. Nešto složenija varijanta je da koristite \"Save Link As\" opciju vašeg web preglednika, promijenite naziv dokumenta iz izvjestaj.csv u izvjestaj.txt, a zatim koristite <a href=\"http://office.microsoft.com/en-us/excel-help/text-import-wizard-HP010102244.aspx\">Excel Text Import Wizard</a>.</li>\r\n<li>Ako koristite OpenOffice.org uredski paket, prilikom otvaranja dokumenta izaberite Text encoding \"Eastern European (Windows-1250)\", a kao razdjelnik (Delimiter) izaberite tačka-zarez (Semicolon). Ostale opcije obavezno isključite. Takođe isključite opciju spajanja razdjelnika (Merge delimiters).</li>\r\n<li>Može se desiti da vaš program prepozna određene stavke (npr. redne brojeve ili ostvarene bodove) kao datum, pogotovo ako ste poslušali savjet iz prve tačke - odnosno, ako ste kao CSV separator podesili \"zarez\".</li>\r\n<li>U velikoj većini slučajeva možete dobiti potpuno zadovoljavajuće \r\nrezultate ako otvorite prazan dokument u vašem spreadsheet programu (npr. Excel) i zatim napravite copy-paste kompletnog sadržaja web stranice.</li></ul>\r\n\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, strana 32-33.</i></p>', 'nastavnik'),
(14, '<p>...da možete brzo i lako pomoću nekog spreadsheet programa (npr. MS Excel) unijeti rezultate ispita ili konačne ocjene?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>. Ili, ako vam je lakše unositi podatke po grupama, izaberite izvještaj <i>Jedna kolona po grupama</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src=\"static/images/32x32/excel.png\" width=\"32\" height=\"32\"><br>\r\nDobićete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte broj bodova koje je student ostvario na ispitu ili konačnu ocjenu.</li>\r\n<li>Kada završite unos rezultata/ocjena, koristeći tipku Shift i tipke sa strelicama označite imena studenata i ocjene. Nemojte označiti naslov niti redni broj studenta. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja.</li>\r\n<li>Ako unosite konačne ocjene, s lijeve strane izaberite opciju <i>Konačna ocjena</i>.</li>\r\n<li>Ako unosite rezultate ispita, s lijeve strane izaberite opciju <i>Ispiti</i>, kreirajte novi ispit, a zatim kliknite na link <i>Masovni unos rezultata</i> pored novokreiranog ispita.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos ocjena</i> i pritisnite Ctrl+V. Trebalo bi da ugledate rezultate ispita odnosno ocjene.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> (a ne Prezime[TAB]Ime), te da pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme \r\n<i>Potvrda</i>.</li></ul>\r\n<p>Ovim su unesene ocjene / rezultati ispita!</p>\r\n\r\n\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 18-20 (masovni unos ispita) i str. 28-29 (masovni unos konačne ocjene).</i></p>', 'nastavnik'),
(15, '<p>...da kod evidencije prisustva, pored stanja \"prisutan\" (zelena boja) i stanja \"odsutan\" (crvena boja) postoji i nedefinisano stanje (žuta boja). Ovo stanje se dodjeljuje ako je student upisan u grupu nakon što su održani određeni časovi.</p>\r\n<p>Drečavo žuta boja je odabrana kako bi se predmetni nastavnik odnosno asistent podsjetio da se mora odlučiti da li će studentu priznati časove kao prisustva ili ne. U međuvremenu, nedefinisano stanje će se tumačiti u korist studenta, odnosno neće ulaziti u broj izostanaka prilikom određivanja da li je student izgubio bodove za prisustvo.</p>\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik'),
(16, '<p>...da ne morate voditi evidenciju o prisustvu kroz Zamger ako ne želite, a i dalje možete imati ažuran broj bodova ostvarenih na prisustvo?</p>\r\n<p>Sistem bodovanja je takav da student dobija 10 bodova ako je odsustvovao manje od 4 puta, a 0 bodova ako je odsustvovao 4 ili više puta. Podaci o konkretnim održanim časovima u Zamgeru se ne koriste nigdje osim za internu evidenciju na predmetu.</p>\r\n<p>Dakle, u slučaju da imate vlastitu evidenciju, samo kreirajte četiri časa (datum je nebitan) i unesite četiri izostanka studentima koji nisu zadovoljili prisustvo.</p>	\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 4-5.</i></p>', 'nastavnik'),
(17, '<p>...da možete podesiti drugačiji sistem bodovanja za prisustvo od ponuđenog?</p>\r\n<p>Možete podesiti ukupan broj bodova za prisustvo (različit od 10). Možete promijeniti maksimalan broj dozvoljenih izostanaka (različit od 3) ili pak podesiti linearno bodovanje u odnosu na broj izostanaka (npr. ako je student od 14 časova izostao 2 puta, dobiće (12/14)*10 = 8,6 bodova). Konačno, umjesto evidencije pojedinačnih časova, možete odabrati da direktno unosite broj bodova za prisustvo po uzoru na rezultate ispita.</p>\r\n<p>Da biste aktivirali ovu mogućnost, trebate promijeniti sistem bodovanja samog predmeta.</p>', 'nastavnik'),
(18, '<p>...da možete unijeti bodove za zadaću čak i ako je student nije poslao kroz Zamger?</p>\r\n<p>Da biste to uradili, potrebno je da kliknete na link <i>Prikaži dugmad za kreiranje zadataka</i> koji se nalazi u dnu stranice sa prikazom grupe (vidi sliku). Nakon što ovo uradite, ćelije tabele koje odgovaraju neposlanim zadaćama će se popuniti ikonama za kreiranje zadaće koje imaju oblik sijalice.</p>\r\n<p><a href=\"static/doc/savjet_sijalice.png\" target=\"_new\">Slika</a> - ukoliko ne vidite detalje, raširite prozor!</p>	\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 10-11.</i></p>\r\n<p>U slučaju da se na vašem predmetu zadaće generalno ne šalju kroz Zamger, vjerovatno će brži način rada za vas biti da koristite masovni unos. Više informacija na str. 27-28. Uputstava.</p>', 'nastavnik'),
(19, '<p>...da pomoću Zamgera možete poslati cirkularni mail svim studentima na vašem predmetu ili u pojedinim grupama?</p>\r\n<p>Da biste pristupili ovoj opciji:</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta</li>\r\n<li>U meniju sa lijeve strane odaberite opciju <i>Obavještenja za studente</i>.</li>\r\n<li>Pod menijem <i>Obavještenje za:</i> odaberite da li obavještenje šaljete svim studentima na predmetu ili samo studentima koji su članovi određene grupe.</li>\r\n<li>Aktivirajte opciju <i>Slanje e-maila</i>. Ako ova opcija nije aktivna, studenti će i dalje vidjeti vaše obavještenje na svojoj Zamger početnoj stranici (sekcija Obavještenja) kao i putem RSSa.</li>\r\n<li>U dio pod naslovom <i>Kraći tekst</i> unesite udarnu liniju vaše informacije.</li>\r\n<li>U dio pod naslovom <i>Detaljan tekst</i> možete napisati dodatna pojašnjenja, a možete ga i ostaviti praznim.</li>\r\n<li>Kliknite na dugme <i>Pošalji</i>. Vidjećete jedno po jedno ime studenta kojem je poslan mail kao i e-mail adresu na \r\nkoju je mail poslan. Slanje veće količine mailova može potrajati nekoliko minuta.</li></ul>\r\n<p>Mailovi će biti poslani na adrese koje su studenti podesili koristeći svoj profil, ali i na zvanične fakultetske adrese.</p>\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 12-14.</i></p>', 'nastavnik'),
(20, '<p>...da je promjena grupe studenta destruktivna operacija kojom se nepovratno gube podaci o prisustvu studenta na časovima registrovanim za tu grupu?</p>\r\n<p>Studenta možete prebaciti u drugu grupu putem ekrana Dosje studenta: na pogledu grupe (npr. <i>Svi studenti</i>) kliknite na ime i prezime studenta da biste ušli u njegov ili njen dosje.</p>\r\n<p>Promjenom grupe nepovratno se gubi evidencija prisustva studenta na časovima registrovanim za prethodnu grupu. Naime, između časova registrovanih za dvije različite grupe ne postoji jednoznačno mapiranje. U nekom datom trenutku vremena u jednoj grupi može biti registrovano 10 časova a u drugoj 8. Kako znati koji od tih 10 časova odgovara kojem od onih 8? I šta raditi sa suvišnim časovima? Dakle, kada premjestite studenta u grupu u kojoj već postoje registrovani časovi, prisustvo studenta tim časovima će biti označeno kao nedefinisano (žuta boja). Prepušta se nastavnom ansamblu da odluči koje od tih časova će priznati kao prisutne, a koje markirati kao \r\nodsutne. Vjerovatno ćete se pitati šta ako se student ponovo vrati u polaznu grupu. Odgovor je da će podaci ponovo biti izgubljeni, jer šta raditi sa časovima registrovanim u međuvremenu?</p>\r\n<p>Preporučujemo da ne vršite promjene grupe nakon što počne akademska godina.</p>\r\n	\r\n<p><i>Više informacija u <a href=\"static/doc/zamger-uputstva-42-nastavnik.pdf\" target=\"_new\">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik');

-- --------------------------------------------------------

--
-- Table structure for table `septembar`
--

DROP TABLE IF EXISTS `septembar`;
CREATE TABLE IF NOT EXISTS `septembar` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `tipskole` enum('GIMNAZIJA','ELEKTROTEHNICKA','TEHNICKA','STRUCNA','MSS','ZANAT') COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `strucni_stepen`
--

DROP TABLE IF EXISTS `strucni_stepen`;
CREATE TABLE IF NOT EXISTS `strucni_stepen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `strucni_stepen`
--

INSERT INTO `strucni_stepen` (`id`, `naziv`, `titula`) VALUES
(1, 'Magistar diplomirani inžinjer elektrotehnike', 'Mr. dipl. ing.'),
(2, 'Bakalaureat/Bachelor inžinjer elektrotehnike', 'BA ing.'),
(3, 'Diplomirani inženjer elektrotehnike', 'dipl.ing.el.'),
(4, 'Diplomirani matematičar', 'dipl.mat.'),
(5, 'Srednja stručna sprema', ''),
(6, 'Diplomirani inženjer mašinstva', 'dipl.ing.'),
(7, 'Diplomirani inženjer građevinarstva', 'dipl.ing.'),
(8, 'Diplomirani ekonomista', 'dipl.ecc.'),
(9, 'Diplomirani fizičar', 'dipl.fiz.');

-- --------------------------------------------------------

--
-- Table structure for table `studentski_modul`
--

DROP TABLE IF EXISTS `studentski_modul`;
CREATE TABLE IF NOT EXISTS `studentski_modul` (
  `id` int(11) NOT NULL,
  `modul` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `gui_naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `novi_prozor` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `studentski_modul`
--

INSERT INTO `studentski_modul` (`id`, `modul`, `gui_naziv`, `novi_prozor`) VALUES
(1, 'student/moodle', 'Materijali (Moodle)', 1),
(2, 'student/zadaca', 'Slanje zadaće', 0),
(3, 'izvjestaj/predmet', 'Dnevnik', 1),
(4, 'student/projekti', 'Projekti', 0);

-- --------------------------------------------------------

--
-- Table structure for table `studentski_modul_predmet`
--

DROP TABLE IF EXISTS `studentski_modul_predmet`;
CREATE TABLE IF NOT EXISTS `studentski_modul_predmet` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `studentski_modul` int(11) NOT NULL,
  `aktivan` tinyint(1) NOT NULL,
  PRIMARY KEY (`predmet`,`akademska_godina`,`studentski_modul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_ispit_termin`
--

DROP TABLE IF EXISTS `student_ispit_termin`;
CREATE TABLE IF NOT EXISTS `student_ispit_termin` (
  `student` int(11) NOT NULL,
  `ispit_termin` int(11) NOT NULL,
  PRIMARY KEY (`student`,`ispit_termin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_labgrupa`
--

DROP TABLE IF EXISTS `student_labgrupa`;
CREATE TABLE IF NOT EXISTS `student_labgrupa` (
  `student` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`student`,`labgrupa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_predmet`
--

DROP TABLE IF EXISTS `student_predmet`;
CREATE TABLE IF NOT EXISTS `student_predmet` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY (`student`,`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_projekat`
--

DROP TABLE IF EXISTS `student_projekat`;
CREATE TABLE IF NOT EXISTS `student_projekat` (
  `student` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY (`student`,`projekat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `odluka` int(11) DEFAULT NULL,
  `plan_studija` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`student`,`studij`,`semestar`,`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `studij`
--

DROP TABLE IF EXISTS `studij`;
CREATE TABLE IF NOT EXISTS `studij` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `institucija` int(11) NOT NULL DEFAULT '0',
  `kratkinaziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `moguc_upis` tinyint(1) NOT NULL,
  `tipstudija` int(11) NOT NULL,
  `preduslov` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `studij`
--

INSERT INTO `studij` (`id`, `naziv`, `institucija`, `kratkinaziv`, `moguc_upis`, `tipstudija`, `preduslov`) VALUES
(1, 'Računarstvo i informatika (BSc)', 2, 'RI', 1, 1, 1),
(2, 'Automatika i elektronika (BSc)', 3, 'AE', 1, 1, 1),
(3, 'Elektroenergetika (BSc)', 4, 'EE', 1, 1, 1),
(4, 'Telekomunikacije (BSc)', 5, 'TK', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `svrha_potvrde`
--

DROP TABLE IF EXISTS `svrha_potvrde`;
CREATE TABLE IF NOT EXISTS `svrha_potvrde` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `svrha_potvrde`
--

INSERT INTO `svrha_potvrde` (`id`, `naziv`) VALUES
(1, 'regulisanja stipendije'),
(2, 'regulisanja prava na prevoz'),
(3, 'regulisanja zdravstvenog osiguranja'),
(4, 'regulisanja prava na šehidsku penziju'),
(5, 'regulisanja prava prijave na biro za zapošljavanje'),
(6, 'regulisanja socijalnog statusa'),
(7, 'regulisanja alimentacije'),
(8, 'regulisanja dječijeg dodatka'),
(9, 'regulisanja donacije'),
(10, 'regulisanja ferijalne prakse'),
(11, 'regulisanja penzije za civilne žrtve rata'),
(12, 'regulisanja prava na boračku penziju'),
(13, 'regulisanja prava na honorar'),
(14, 'regulisanja prava na invalidninu'),
(15, 'regulisanja prava na izdavanje pasoša'),
(16, 'regulisanja prava na poreske olakšice'),
(17, 'regulisanja prava na porodičnu penziju'),
(18, 'regulisanja prava na porodiljsku naknadu'),
(19, 'regulisanja prava na pristup internetu'),
(20, 'regulisanja prava na studentski dom'),
(21, 'regulisanja prava privremenog boravka'),
(22, 'regulisanja prava na izdavanje studentske kartice'),
(24, 'regulisanja radne vize'),
(25, 'regulisanja slobodnih dana za zaposlene studente'),
(26, 'regulisanja stambenog pitanja'),
(27, 'regulisanja statusnih pitanja'),
(29, 'regulisanja studentskog kredita'),
(30, 'regulisanja subvencije'),
(31, 'regulisanja turističke vize'),
(32, 'regulisanja vojne obaveze'),
(33, 'regulisanja vozačkog ispita'),
(35, 'učlanjenja u studentsku zadrugu'),
(36, 'regulisanja telekom usluga'),
(101, 'aplikacije na drugi fakultet'),
(102, 'hospitovanja u školi'),
(103, 'ostvarivanja prava na jednokratnu novčanu pomoć');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `tipkomponente`
--

INSERT INTO `tipkomponente` (`id`, `naziv`, `opis_opcija`) VALUES
(1, 'Ispit', ''),
(2, 'Integralni ispit', 'Ispiti koje zamjenjuje (razdvojeni sa +)'),
(3, 'Zadaće', ''),
(4, 'Prisustvo', 'Minimalan broj izostanaka (0=linearno)'),
(5, 'Fiksna', '');

-- --------------------------------------------------------

--
-- Table structure for table `tippredmeta`
--

DROP TABLE IF EXISTS `tippredmeta`;
CREATE TABLE IF NOT EXISTS `tippredmeta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(60) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2001 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `tippredmeta`
--

INSERT INTO `tippredmeta` (`id`, `naziv`) VALUES
(1, 'Bologna standard'),
(1000, 'Završni rad'),
(1001, 'Završni rad bez ocjena'),
(2000, 'Kolokvij');

-- --------------------------------------------------------

--
-- Table structure for table `tippredmeta_komponenta`
--

DROP TABLE IF EXISTS `tippredmeta_komponenta`;
CREATE TABLE IF NOT EXISTS `tippredmeta_komponenta` (
  `tippredmeta` int(11) NOT NULL,
  `komponenta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `ects` int(11) NOT NULL,
  `moguc_upis` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `tipstudija`
--

INSERT INTO `tipstudija` (`id`, `naziv`, `ciklus`, `trajanje`, `ects`, `moguc_upis`) VALUES
(1, 'Bakalaureat', 1, 6, 180, 1),
(2, 'Master', 2, 4, 120, 1),
(3, 'Doktorski studij', 3, 6, 180, 1),
(4, 'Stručni studij', 0, 4, 120, 1),
(5, 'Jednogodišnji master', 2, 2, 60, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tip_potvrde`
--

DROP TABLE IF EXISTS `tip_potvrde`;
CREATE TABLE IF NOT EXISTS `tip_potvrde` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `tip_potvrde`
--

INSERT INTO `tip_potvrde` (`id`, `naziv`) VALUES
(1, 'potvrda o redovnom studiju'),
(2, 'uvjerenje o položenim ispitima');

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
  `kod` varchar(20) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ugovoroucenju_izborni`
--

DROP TABLE IF EXISTS `ugovoroucenju_izborni`;
CREATE TABLE IF NOT EXISTS `ugovoroucenju_izborni` (
  `ugovoroucenju` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ugovoroucenju_kapacitet`
--

DROP TABLE IF EXISTS `ugovoroucenju_kapacitet`;
CREATE TABLE IF NOT EXISTS `ugovoroucenju_kapacitet` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `kapacitet` int(11) NOT NULL DEFAULT '-1' COMMENT '0 = predmet ne ide, -1 = nema ogranicenja',
  `kapacitet_izborni` int(11) NOT NULL DEFAULT '-1' COMMENT '0 = niko ne moze izabrati, -1 = nema ogranicenja',
  `kapacitet_kolizija` int(11) NOT NULL DEFAULT '-1' COMMENT '0 - predmet ne ide u koliziji',
  `kapacitet_drugi_odsjek` int(11) NOT NULL DEFAULT '-1' COMMENT '0 - predmet ne mogu birati sa drugog odsjeka',
  `drugi_odsjek_zabrane` varchar(50) COLLATE utf8_slovenian_ci NOT NULL COMMENT 'ako je prazno mogu svi, u suprotnom spisak odsjeka za koje je zabranjen'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci COMMENT='Tabela za pohranu kriterija za upis';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `automatsko_testiranje` tinyint(1) NOT NULL DEFAULT '0',
  `attachment` tinyint(1) NOT NULL DEFAULT '0',
  `dozvoljene_ekstenzije` varchar(255) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `postavka_zadace` varchar(255) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `komponenta` int(11) NOT NULL,
  `vrijemeobjave` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `readonly` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `izvjestaj_skripte` text COLLATE utf8_slovenian_ci,
  `vrijeme` datetime DEFAULT NULL,
  `komentar` text COLLATE utf8_slovenian_ci,
  `filename` varchar(200) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uobicajen` (`zadaca`,`redni_broj`,`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zadatakdiff`
--

DROP TABLE IF EXISTS `zadatakdiff`;
CREATE TABLE IF NOT EXISTS `zadatakdiff` (
  `zadatak` bigint(11) NOT NULL DEFAULT '0',
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`zadatak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zahtjev_za_potvrdu`
--

DROP TABLE IF EXISTS `zahtjev_za_potvrdu`;
CREATE TABLE IF NOT EXISTS `zahtjev_za_potvrdu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student` int(11) DEFAULT NULL,
  `tip_potvrde` int(11) DEFAULT NULL,
  `svrha_potvrde` int(11) DEFAULT NULL,
  `datum_zahtjeva` datetime DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `akademska_godina` int(11) NOT NULL,
  `besplatna` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `tip_potvrde` (`tip_potvrde`),
  KEY `svrha_potvrde` (`svrha_potvrde`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni`
--

DROP TABLE IF EXISTS `zavrsni`;
CREATE TABLE IF NOT EXISTS `zavrsni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naslov` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `podnaslov` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `rad_na_predmetu` int(11) DEFAULT NULL,
  `akademska_godina` varchar(10) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '0',
  `kratki_pregled` text COLLATE utf8_slovenian_ci NOT NULL,
  `literatura` text COLLATE utf8_slovenian_ci NOT NULL,
  `sazetak` text COLLATE utf8_slovenian_ci,
  `summary` text COLLATE utf8_slovenian_ci,
  `mentor` int(11) NOT NULL,
  `drugi_mentor` int(11) DEFAULT NULL,
  `student` int(11) DEFAULT NULL,
  `kandidat_potvrdjen` tinyint(4) NOT NULL,
  `biljeska` text COLLATE utf8_slovenian_ci,
  `predsjednik_komisije` int(11) DEFAULT NULL,
  `clan_komisije` int(11) DEFAULT NULL,
  `clan_komisije2` int(11) DEFAULT NULL,
  `termin_odbrane` datetime DEFAULT NULL,
  `broj_diplome` varchar(100) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `tema_odobrena` tinyint(4) NOT NULL DEFAULT '0',
  `sala` varchar(20) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `odluka` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `predmet` (`predmet`),
  KEY `rad_na_predmetu` (`rad_na_predmetu`),
  KEY `student` (`student`),
  KEY `mentor` (`mentor`),
  KEY `drugi_mentor` (`drugi_mentor`),
  KEY `predsjednik_komisije` (`predsjednik_komisije`),
  KEY `clan_komisije` (`clan_komisije`),
  KEY `clan_komisije2` (`clan_komisije2`)
) ENGINE=InnoDB AUTO_INCREMENT=2235 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_bb_post_text`
--

DROP TABLE IF EXISTS `zavrsni_bb_post_text`;
CREATE TABLE IF NOT EXISTS `zavrsni_bb_post_text` (
  `post` int(11) NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `pregleda` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `osoba` int(11) NOT NULL,
  `zavrsni` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `zvanje`
--

DROP TABLE IF EXISTS `zvanje`;
CREATE TABLE IF NOT EXISTS `zvanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zvanje`
--

INSERT INTO `zvanje` (`id`, `naziv`, `titula`) VALUES
(1, 'Redovni profesor', 'R. prof.'),
(2, 'Vanredni profesor', 'V. prof.'),
(3, 'Docent', 'Doc.'),
(4, 'Viši asistent', 'V. asis.'),
(5, 'Asistent', 'Asis.'),
(6, 'Profesor emeritus', ''),
(7, 'predavač', 'pred.'),
(8, 'viši lektor', 'v. lec.'),
(9, 'lektor', 'lec.');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `akademska_godina_predmet`
--
ALTER TABLE `akademska_godina_predmet`
  ADD CONSTRAINT `akademska_godina_predmet_ibfk_1` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`),
  ADD CONSTRAINT `akademska_godina_predmet_ibfk_11` FOREIGN KEY (`tippredmeta`) REFERENCES `tippredmeta` (`id`),
  ADD CONSTRAINT `akademska_godina_predmet_ibfk_2` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`);

--
-- Constraints for table `angazman`
--
ALTER TABLE `angazman`
  ADD CONSTRAINT `angazman_ibfk_10` FOREIGN KEY (`osoba`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `angazman_ibfk_11` FOREIGN KEY (`angazman_status`) REFERENCES `angazman_status` (`id`),
  ADD CONSTRAINT `angazman_ibfk_8` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `angazman_ibfk_9` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `anketa_anketa`
--
ALTER TABLE `anketa_anketa`
  ADD CONSTRAINT `anketa_anketa_ibfk_1` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `anketa_izbori_pitanja`
--
ALTER TABLE `anketa_izbori_pitanja`
  ADD CONSTRAINT `anketa_izbori_pitanja_ibfk_1` FOREIGN KEY (`pitanje`) REFERENCES `anketa_pitanje` (`id`);

--
-- Constraints for table `anketa_odgovor_dopisani`
--
ALTER TABLE `anketa_odgovor_dopisani`
  ADD CONSTRAINT `anketa_odgovor_dopisani_ibfk_1` FOREIGN KEY (`rezultat`) REFERENCES `anketa_rezultat` (`id`),
  ADD CONSTRAINT `anketa_odgovor_dopisani_ibfk_2` FOREIGN KEY (`pitanje`) REFERENCES `anketa_pitanje` (`id`);

--
-- Constraints for table `anketa_odgovor_izbori`
--
ALTER TABLE `anketa_odgovor_izbori`
  ADD CONSTRAINT `anketa_odgovor_izbori_ibfk_1` FOREIGN KEY (`pitanje`) REFERENCES `anketa_pitanje` (`id`);

--
-- Constraints for table `anketa_odgovor_rank`
--
ALTER TABLE `anketa_odgovor_rank`
  ADD CONSTRAINT `anketa_odgovor_rank_ibfk_2` FOREIGN KEY (`rezultat`) REFERENCES `anketa_rezultat` (`id`),
  ADD CONSTRAINT `anketa_odgovor_rank_ibfk_3` FOREIGN KEY (`pitanje`) REFERENCES `anketa_pitanje` (`id`);

--
-- Constraints for table `anketa_odgovor_text`
--
ALTER TABLE `anketa_odgovor_text`
  ADD CONSTRAINT `anketa_odgovor_text_ibfk_2` FOREIGN KEY (`rezultat`) REFERENCES `anketa_rezultat` (`id`),
  ADD CONSTRAINT `anketa_odgovor_text_ibfk_3` FOREIGN KEY (`pitanje`) REFERENCES `anketa_pitanje` (`id`);

--
-- Constraints for table `anketa_pitanje`
--
ALTER TABLE `anketa_pitanje`
  ADD CONSTRAINT `anketa_pitanje_ibfk_1` FOREIGN KEY (`anketa`) REFERENCES `anketa_anketa` (`id`),
  ADD CONSTRAINT `anketa_pitanje_ibfk_2` FOREIGN KEY (`tip_pitanja`) REFERENCES `anketa_tip_pitanja` (`id`);

--
-- Constraints for table `anketa_predmet`
--
ALTER TABLE `anketa_predmet`
  ADD CONSTRAINT `anketa_predmet_ibfk_1` FOREIGN KEY (`anketa`) REFERENCES `anketa_anketa` (`id`),
  ADD CONSTRAINT `anketa_predmet_ibfk_2` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `anketa_predmet_ibfk_3` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `auth`
--
ALTER TABLE `auth`
  ADD CONSTRAINT `auth_ibfk_1` FOREIGN KEY (`id`) REFERENCES `osoba` (`id`);

--
-- Constraints for table `autotest`
--
ALTER TABLE `autotest`
  ADD CONSTRAINT `autotest_ibfk_1` FOREIGN KEY (`zadaca`) REFERENCES `zadaca` (`id`);

--
-- Constraints for table `autotest_replace`
--
ALTER TABLE `autotest_replace`
  ADD CONSTRAINT `autotest_replace_ibfk_1` FOREIGN KEY (`zadaca`) REFERENCES `zadaca` (`id`);

--
-- Constraints for table `autotest_rezultat`
--
ALTER TABLE `autotest_rezultat`
  ADD CONSTRAINT `autotest_rezultat_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`);

--
-- Constraints for table `bb_post`
--
ALTER TABLE `bb_post`
  ADD CONSTRAINT `bb_post_ibfk_1` FOREIGN KEY (`osoba`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `bb_post_ibfk_2` FOREIGN KEY (`tema`) REFERENCES `bb_tema` (`id`);

--
-- Constraints for table `bb_post_text`
--
ALTER TABLE `bb_post_text`
  ADD CONSTRAINT `bb_post_text_ibfk_1` FOREIGN KEY (`post`) REFERENCES `bb_post` (`id`);

--
-- Constraints for table `bb_tema`
--
ALTER TABLE `bb_tema`
  ADD CONSTRAINT `bb_tema_ibfk_1` FOREIGN KEY (`prvi_post`) REFERENCES `bb_post` (`id`),
  ADD CONSTRAINT `bb_tema_ibfk_2` FOREIGN KEY (`zadnji_post`) REFERENCES `bb_post` (`id`),
  ADD CONSTRAINT `bb_tema_ibfk_3` FOREIGN KEY (`osoba`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `bb_tema_ibfk_4` FOREIGN KEY (`projekat`) REFERENCES `projekat` (`id`);

--
-- Constraints for table `bl_clanak`
--
ALTER TABLE `bl_clanak`
  ADD CONSTRAINT `bl_clanak_ibfk_1` FOREIGN KEY (`osoba`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `bl_clanak_ibfk_2` FOREIGN KEY (`projekat`) REFERENCES `projekat` (`id`);

--
-- Constraints for table `cas`
--
ALTER TABLE `cas`
  ADD CONSTRAINT `cas_ibfk_6` FOREIGN KEY (`labgrupa`) REFERENCES `labgrupa` (`id`),
  ADD CONSTRAINT `cas_ibfk_7` FOREIGN KEY (`nastavnik`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `cas_ibfk_8` FOREIGN KEY (`komponenta`) REFERENCES `komponenta` (`id`);

--
-- Constraints for table `cron_rezultat`
--
ALTER TABLE `cron_rezultat`
  ADD CONSTRAINT `cron_rezultat_ibfk_1` FOREIGN KEY (`cron`) REFERENCES `cron` (`id`);

--
-- Constraints for table `email`
--
ALTER TABLE `email`
  ADD CONSTRAINT `email_ibfk_1` FOREIGN KEY (`osoba`) REFERENCES `osoba` (`id`);

--
-- Constraints for table `ispit`
--
ALTER TABLE `ispit`
  ADD CONSTRAINT `ispit_ibfk_3` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `ispit_ibfk_4` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`),
  ADD CONSTRAINT `ispit_ibfk_5` FOREIGN KEY (`komponenta`) REFERENCES `komponenta` (`id`);

--
-- Constraints for table `ispitocjene`
--
ALTER TABLE `ispitocjene`
  ADD CONSTRAINT `ispitocjene_ibfk_1` FOREIGN KEY (`ispit`) REFERENCES `ispit` (`id`),
  ADD CONSTRAINT `ispitocjene_ibfk_2` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`);

--
-- Constraints for table `ispit_termin`
--
ALTER TABLE `ispit_termin`
  ADD CONSTRAINT `ispit_termin_ibfk_1` FOREIGN KEY (`ispit`) REFERENCES `ispit` (`id`);

--
-- Constraints for table `izvoz_ocjena`
--
ALTER TABLE `izvoz_ocjena`
  ADD CONSTRAINT `izvoz_ocjena_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `izvoz_ocjena_ibfk_2` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`);

--
-- Constraints for table `izvoz_promjena_podataka`
--
ALTER TABLE `izvoz_promjena_podataka`
  ADD CONSTRAINT `izvoz_promjena_podataka_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`);

--
-- Constraints for table `izvoz_upis_prva`
--
ALTER TABLE `izvoz_upis_prva`
  ADD CONSTRAINT `izvoz_upis_prva_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `izvoz_upis_prva_ibfk_2` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `izvoz_upis_semestar`
--
ALTER TABLE `izvoz_upis_semestar`
  ADD CONSTRAINT `izvoz_upis_semestar_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `izvoz_upis_semestar_ibfk_2` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `kandidati`
--
ALTER TABLE `kandidati`
  ADD CONSTRAINT `kandidati_ibfk_1` FOREIGN KEY (`mjesto_rodjenja`) REFERENCES `kandidati_mjesto` (`id`),
  ADD CONSTRAINT `kandidati_ibfk_2` FOREIGN KEY (`nacionalnost`) REFERENCES `nacionalnost` (`id`),
  ADD CONSTRAINT `kandidati_ibfk_3` FOREIGN KEY (`drzavljanstvo`) REFERENCES `drzava` (`id`),
  ADD CONSTRAINT `kandidati_ibfk_4` FOREIGN KEY (`boracka_kategorija`) REFERENCES `posebne_kategorije` (`id`),
  ADD CONSTRAINT `kandidati_ibfk_5` FOREIGN KEY (`opcina_skole`) REFERENCES `opcina` (`id`),
  ADD CONSTRAINT `kandidati_ibfk_6` FOREIGN KEY (`studijski_program`) REFERENCES `studij` (`id`),
  ADD CONSTRAINT `kandidati_ibfk_7` FOREIGN KEY (`skolska_godina_zavrsetka`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `kandidati_mjesto`
--
ALTER TABLE `kandidati_mjesto`
  ADD CONSTRAINT `kandidati_mjesto_ibfk_1` FOREIGN KEY (`opcina`) REFERENCES `opcina` (`id`),
  ADD CONSTRAINT `kandidati_mjesto_ibfk_2` FOREIGN KEY (`drzava`) REFERENCES `drzava` (`id`);

--
-- Constraints for table `kandidati_ocjene`
--
ALTER TABLE `kandidati_ocjene`
  ADD CONSTRAINT `kandidati_ocjene_ibfk_1` FOREIGN KEY (`kandidat_id`) REFERENCES `kandidati` (`id`);

--
-- Constraints for table `kolizija`
--
ALTER TABLE `kolizija`
  ADD CONSTRAINT `kolizija_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `kolizija_ibfk_2` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`),
  ADD CONSTRAINT `kolizija_ibfk_3` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`);

--
-- Constraints for table `komentar`
--
ALTER TABLE `komentar`
  ADD CONSTRAINT `komentar_ibfk_2` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `komentar_ibfk_3` FOREIGN KEY (`nastavnik`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `komentar_ibfk_4` FOREIGN KEY (`labgrupa`) REFERENCES `labgrupa` (`id`);

--
-- Constraints for table `komponenta`
--
ALTER TABLE `komponenta`
  ADD CONSTRAINT `komponenta_ibfk_1` FOREIGN KEY (`tipkomponente`) REFERENCES `tipkomponente` (`id`);

--
-- Constraints for table `komponentebodovi`
--
ALTER TABLE `komponentebodovi`
  ADD CONSTRAINT `komponentebodovi_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `komponentebodovi_ibfk_2` FOREIGN KEY (`predmet`) REFERENCES `ponudakursa` (`id`),
  ADD CONSTRAINT `komponentebodovi_ibfk_3` FOREIGN KEY (`komponenta`) REFERENCES `komponenta` (`id`);

--
-- Constraints for table `konacna_ocjena`
--
ALTER TABLE `konacna_ocjena`
  ADD CONSTRAINT `konacna_ocjena_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `konacna_ocjena_ibfk_2` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `konacna_ocjena_ibfk_3` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`),
  ADD CONSTRAINT `konacna_ocjena_ibfk_4` FOREIGN KEY (`odluka`) REFERENCES `odluka` (`id`);

--
-- Constraints for table `kviz`
--
ALTER TABLE `kviz`
  ADD CONSTRAINT `kviz_ibfk_1` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `kviz_ibfk_2` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`),
  ADD CONSTRAINT `kviz_ibfk_3` FOREIGN KEY (`labgrupa`) REFERENCES `labgrupa` (`id`);

--
-- Constraints for table `kviz_pitanje`
--
ALTER TABLE `kviz_pitanje`
  ADD CONSTRAINT `kviz_pitanje_ibfk_1` FOREIGN KEY (`kviz`) REFERENCES `kviz` (`id`);

--
-- Constraints for table `labgrupa`
--
ALTER TABLE `labgrupa`
  ADD CONSTRAINT `labgrupa_ibfk_1` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `labgrupa_ibfk_2` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `mjesto`
--
ALTER TABLE `mjesto`
  ADD CONSTRAINT `mjesto_ibfk_1` FOREIGN KEY (`drzava`) REFERENCES `drzava` (`id`);

--
-- Constraints for table `nastavnik_predmet`
--
ALTER TABLE `nastavnik_predmet`
  ADD CONSTRAINT `nastavnik_predmet_ibfk_1` FOREIGN KEY (`nastavnik`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `nastavnik_predmet_ibfk_2` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`),
  ADD CONSTRAINT `nastavnik_predmet_ibfk_3` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`);

--
-- Constraints for table `oblast`
--
ALTER TABLE `oblast`
  ADD CONSTRAINT `oblast_ibfk_1` FOREIGN KEY (`institucija`) REFERENCES `institucija` (`id`);

--
-- Constraints for table `odluka`
--
ALTER TABLE `odluka`
  ADD CONSTRAINT `odluka_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`);

--
-- Constraints for table `ogranicenje`
--
ALTER TABLE `ogranicenje`
  ADD CONSTRAINT `ogranicenje_ibfk_2` FOREIGN KEY (`nastavnik`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `ogranicenje_ibfk_3` FOREIGN KEY (`labgrupa`) REFERENCES `labgrupa` (`id`);

--
-- Constraints for table `osoba`
--
ALTER TABLE `osoba`
  ADD CONSTRAINT `osoba_ibfk_3` FOREIGN KEY (`mjesto_rodjenja`) REFERENCES `mjesto` (`id`),
  ADD CONSTRAINT `osoba_ibfk_4` FOREIGN KEY (`adresa_mjesto`) REFERENCES `mjesto` (`id`);

--
-- Constraints for table `plan_studija`
--
ALTER TABLE `plan_studija`
  ADD CONSTRAINT `plan_studija_ibfk_1` FOREIGN KEY (`studij`) REFERENCES `studij` (`id`),
  ADD CONSTRAINT `plan_studija_ibfk_2` FOREIGN KEY (`godina_vazenja`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `plan_studija_permisije`
--
ALTER TABLE `plan_studija_permisije`
  ADD CONSTRAINT `plan_studija_permisije_ibfk_1` FOREIGN KEY (`plan_studija`) REFERENCES `plan_studija` (`id`),
  ADD CONSTRAINT `plan_studija_permisije_ibfk_2` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `plan_studija_permisije_ibfk_3` FOREIGN KEY (`osoba`) REFERENCES `osoba` (`id`);

--
-- Constraints for table `plan_studija_predmet`
--
ALTER TABLE `plan_studija_predmet`
  ADD CONSTRAINT `plan_studija_predmet_ibfk_6` FOREIGN KEY (`plan_studija`) REFERENCES `plan_studija` (`id`);

--
-- Constraints for table `ponudakursa`
--
ALTER TABLE `ponudakursa`
  ADD CONSTRAINT `ponudakursa_ibfk_1` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `ponudakursa_ibfk_2` FOREIGN KEY (`studij`) REFERENCES `studij` (`id`),
  ADD CONSTRAINT `ponudakursa_ibfk_3` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `predmet`
--
ALTER TABLE `predmet`
  ADD CONSTRAINT `predmet_ibfk_1` FOREIGN KEY (`institucija`) REFERENCES `institucija` (`id`);

--
-- Constraints for table `projekat`
--
ALTER TABLE `projekat`
  ADD CONSTRAINT `projekat_ibfk_1` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `projekat_ibfk_2` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `zahtjev_za_potvrdu`
--
ALTER TABLE `zahtjev_za_potvrdu`
  ADD CONSTRAINT `zahtjev_za_potvrdu_ibfk_4` FOREIGN KEY (`tip_potvrde`) REFERENCES `tip_potvrde` (`id`),
  ADD CONSTRAINT `zahtjev_za_potvrdu_ibfk_5` FOREIGN KEY (`svrha_potvrde`) REFERENCES `svrha_potvrde` (`id`),
  ADD CONSTRAINT `zahtjev_za_potvrdu_ibfk_6` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`);

--
-- Constraints for table `zavrsni`
--
ALTER TABLE `zavrsni`
  ADD CONSTRAINT `zavrsni_ibfk_18` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_19` FOREIGN KEY (`rad_na_predmetu`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_20` FOREIGN KEY (`mentor`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_21` FOREIGN KEY (`drugi_mentor`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_22` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_23` FOREIGN KEY (`predsjednik_komisije`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_24` FOREIGN KEY (`clan_komisije`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_25` FOREIGN KEY (`clan_komisije2`) REFERENCES `osoba` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
