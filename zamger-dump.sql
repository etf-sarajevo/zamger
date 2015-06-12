-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 08, 2011 at 07:49 AM
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
-- Table structure for table `akademska_godina`
--

CREATE TABLE IF NOT EXISTS `akademska_godina` (
  `id` int(11) NOT NULL,
  `naziv` varchar(20) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `aktuelna` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `akademska_godina`
--

INSERT INTO `akademska_godina` (`id`, `naziv`, `aktuelna`) VALUES
(1, '2014/2015', 1);

-- --------------------------------------------------------

--
-- Table structure for table `akademska_godina_predmet`
--

CREATE TABLE IF NOT EXISTS `akademska_godina_predmet` (
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  PRIMARY KEY (`akademska_godina`,`predmet`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `akademska_godina_predmet`
--

-- --------------------------------------------------------

--
-- Table structure for table `angazman`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `angazman_status`
--

CREATE TABLE IF NOT EXISTS `angazman_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_anketa`
--

-- --------------------------------------------------------

--
-- Table structure for table `anketa_izbori_pitanja`
--

CREATE TABLE IF NOT EXISTS `anketa_izbori_pitanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pitanje` int(11) NOT NULL,
  `izbor` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_izbori_pitanja`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_rank`
--

CREATE TABLE IF NOT EXISTS `anketa_odgovor_rank` (
  `rezultat` int(11) NOT NULL,
  `pitanje` int(11) NOT NULL,
  `izbor_id` int(11) NOT NULL,
  PRIMARY KEY (`rezultat`,`pitanje`,`izbor_id`),
  KEY `rezultat` (`rezultat`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_odgovor_rank`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_text`
--

CREATE TABLE IF NOT EXISTS `anketa_odgovor_text` (
  `rezultat` int(11) NOT NULL,
  `pitanje` int(11) NOT NULL,
  `odgovor` text COLLATE utf8_slovenian_ci,
  PRIMARY KEY (`rezultat`,`pitanje`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_odgovor_text`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_pitanje`
--

CREATE TABLE IF NOT EXISTS `anketa_pitanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anketa` int(11) NOT NULL DEFAULT '0',
  `tip_pitanja` int(11) NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `anketa` (`anketa`),
  KEY `tip_pitanja` (`tip_pitanja`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_pitanje`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_predmet`
--

CREATE TABLE IF NOT EXISTS `anketa_predmet` (
  `anketa` int(11) NOT NULL,
  `predmet` int(11) default NULL,
  `akademska_godina` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `aktivna` tinyint(1) NOT NULL,
  KEY `predmet` (`predmet`),
  KEY `anketa` (`anketa`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_predmet`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_rezultat`
--

CREATE TABLE IF NOT EXISTS `anketa_rezultat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anketa` int(11) NOT NULL,
  `zavrsena` enum('Y','N') COLLATE utf8_slovenian_ci DEFAULT 'N',
  `predmet` int(11) DEFAULT NULL,
  `unique_id` varchar(50) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `akademska_godina` int(10) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `student` int(11) default NULL,
  `labgrupa` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unique_id` (`unique_id`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `studij` (`studij`),
  KEY `student` (`student`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_rezultat`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_student_zavrsio`
--

CREATE TABLE IF NOT EXISTS `anketa_student_zavrsio` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `anketa` int(11) NOT NULL,
  `zavrsena` enum('Y','N') collate utf8_slovenian_ci NOT NULL default 'N',
  `anketa_rezultat` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`predmet`,`akademska_godina`,`anketa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `anketa_tip_pitanja`
--

CREATE TABLE IF NOT EXISTS `anketa_tip_pitanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tip` char(32) COLLATE utf8_slovenian_ci NOT NULL,
  `postoji_izbor` enum('Y','N') COLLATE utf8_slovenian_ci NOT NULL,
  `tabela_odgovora` char(32) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=3 ;

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

CREATE TABLE IF NOT EXISTS `auth` (
  `id` int(11) NOT NULL DEFAULT '0',
  `login` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `password` varchar(20) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `external_id` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `aktivan` tinyint(1) NOT NULL DEFAULT '1',
  `posljednji_pristup` datetime NOT NULL,
  PRIMARY KEY (`id`,`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `auth`
--

INSERT INTO `auth` (`id`, `login`, `password`, `admin`, `external_id`, `aktivan`, `posljednji_pristup`) VALUES
(1, 'admin', 'admin', 0, '', 1, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `autotest`
--

CREATE TABLE IF NOT EXISTS `autotest` (
  `id` int(11) NOT NULL auto_increment,
  `zadaca` int(11) NOT NULL,
  `zadatak` int(11) NOT NULL,
  `kod` text collate utf8_slovenian_ci NOT NULL,
  `rezultat` text collate utf8_slovenian_ci NOT NULL,
  `alt_rezultat` text collate utf8_slovenian_ci NOT NULL,
  `fuzzy` tinyint(1) NOT NULL default '0',
  `global_scope` text collate utf8_slovenian_ci NOT NULL,
  `pozicija_globala` enum('prije_svega','prije_maina') collate utf8_slovenian_ci NOT NULL default 'prije_maina',
  `stdin` text collate utf8_slovenian_ci NOT NULL,
  `partial_match` tinyint(4) NOT NULL default '0',
  `aktivan` tinyint(1) NOT NULL default '1',
  `sakriven` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `zadaca` (`zadaca`),
  KEY `zadaca_2` (`zadaca`,`zadatak`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `autotest_replace`
--

CREATE TABLE IF NOT EXISTS `autotest_replace` (
  `id` int(11) NOT NULL auto_increment,
  `zadaca` int(11) NOT NULL,
  `zadatak` int(11) NOT NULL,
  `tip` enum('funkcija','klasa','metoda') collate utf8_slovenian_ci NOT NULL,
  `specifikacija` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `zamijeni` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `zadaca` (`zadaca`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `autotest_rezultat`
--

CREATE TABLE IF NOT EXISTS `autotest_rezultat` (
  `autotest` int(11) NOT NULL,
  `student` int(11) NOT NULL,
  `izlaz_programa` text collate utf8_slovenian_ci NOT NULL,
  `status` enum('ok','wrong','error','no_func','exec_fail','too_long','crash','find_fail','oob','uninit','memleak','invalid_free','mismatched_free') collate utf8_slovenian_ci NOT NULL default 'error',
  `nalaz` text collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `trajanje` int(11) NOT NULL default '0',
  `testni_sistem` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`autotest`,`student`),
  KEY `student` (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `bb_post`
--

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

--
-- Dumping data for table `bb_post`
--


-- --------------------------------------------------------

--
-- Table structure for table `bb_post_text`
--

CREATE TABLE IF NOT EXISTS `bb_post_text` (
  `post` int(11) NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `bb_post_text`
--


-- --------------------------------------------------------

--
-- Table structure for table `bb_tema`
--

CREATE TABLE IF NOT EXISTS `bb_tema` (
  `id` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prvi_post` int(11) NOT NULL DEFAULT '0',
  `zadnji_post` int(11) NOT NULL DEFAULT '0',
  `pregleda` int(11) unsigned NOT NULL DEFAULT '0',
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `prvi_post` (`prvi_post`),
  KEY `zadnji_post` (`zadnji_post`),
  KEY `osoba` (`osoba`),
  KEY `projekat` (`projekat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `bb_tema`
--


-- --------------------------------------------------------

--
-- Table structure for table `bl_clanak`
--

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

--
-- Dumping data for table `bl_clanak`
--


-- --------------------------------------------------------

--
-- Table structure for table `buildservice_tracking`
--

CREATE TABLE IF NOT EXISTS `buildservice_tracking` (
  `zadatak` int(11) NOT NULL,
  `buildhost` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`zadatak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `cas`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `cas`
--


-- --------------------------------------------------------

--
-- Table structure for table `cron`
--

CREATE TABLE IF NOT EXISTS `cron` (
  `id` int(11) NOT NULL auto_increment,
  `path` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `aktivan` tinyint(1) NOT NULL,
  `godina` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `mjesec` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `dan` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `sat` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `minuta` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `sekunda` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `zadnje_izvrsenje` datetime NOT NULL,
  `sljedece_izvrsenje` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `cron_rezultat`
--

CREATE TABLE IF NOT EXISTS `cron_rezultat` (
  `id` int(11) NOT NULL auto_increment,
  `cron` int(11) NOT NULL,
  `izlaz` mediumtext collate utf8_slovenian_ci NOT NULL,
  `return_value` int(11) NOT NULL,
  `vrijeme` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `cron` (`cron`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------


--
-- Table structure for table `drzava`
--

CREATE TABLE IF NOT EXISTS `drzava` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=32 ;

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

CREATE TABLE IF NOT EXISTS `ekstenzije` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=19 ;

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

CREATE TABLE IF NOT EXISTS `email` (
  `id` int(11) NOT NULL auto_increment,
  `osoba` int(11) NOT NULL,
  `adresa` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `sistemska` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `osoba` (`osoba`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `hr_kompetencije`
--

CREATE TABLE IF NOT EXISTS `hr_kompetencije` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `jezik` int(11) NOT NULL,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `razumjevanje` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `govor` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `pisanje` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hr_mentorstvo`
--

CREATE TABLE IF NOT EXISTS `hr_mentorstvo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `fk_fakultet` int(11) NOT NULL,
  `fk_vrsta_mentora` int(11) NOT NULL,
  `datum` date NOT NULL,
  `ime_kandidata` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv_teme` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hr_nagrade_priznanja`
--

CREATE TABLE IF NOT EXISTS `hr_nagrade_priznanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `datum` date NOT NULL,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `opis` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hr_naucni_radovi`
--

CREATE TABLE IF NOT EXISTS `hr_naucni_radovi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `datum` date NOT NULL,
  `naziv_rada` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv_casopisa` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv_izdavaca` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hr_publikacija`
--

CREATE TABLE IF NOT EXISTS `hr_publikacija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `fk_tip_publikacije` int(11) NOT NULL,
  `datum` date NOT NULL,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `casopis` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hr_radno_iskustvo`
--

CREATE TABLE IF NOT EXISTS `hr_radno_iskustvo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `datum_pocetka` date NOT NULL,
  `datum_kraja` date NOT NULL,
  `poslodavac` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `adresa_poslodavca` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `radno_mjesto` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `radno_mjesto_en` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `opis_radnog_mjesta` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `hr_radno_iskustvo`
--

-- --------------------------------------------------------

--
-- Table structure for table `hr_usavrsavanje`
--

CREATE TABLE IF NOT EXISTS `hr_usavrsavanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `datum` date NOT NULL,
  `naziv_usavrsavanja` int(11) NOT NULL,
  `obrazovna_institucija` int(11) NOT NULL,
  `kvalifikacija` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `institucija`
--

CREATE TABLE IF NOT EXISTS `institucija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `roditelj` int(11) NOT NULL DEFAULT '0',
  `kratki_naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `tipinstitucije` int(11) NOT NULL,
  `dekan` int(11) NOT NULL,
  `broj_protokola` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `institucija`
--

INSERT INTO `institucija` (`id`, `naziv`, `roditelj`, `kratki_naziv`, `tipinstitucije`, `dekan`, `broj_protokola`) VALUES
(0, 'Nepoznato', 0, 'N', 0, 0, ''),
(1, 'Elektrotehnički fakultet Sarajevo', 0, 'ETF', 1, 3010, '06-4-1-'),
(2, 'Odsjek za računarstvo i informatiku', 1, 'RI', 0, 0, ''),
(3, 'Odsjek za automatiku i elektroniku', 1, 'AE', 0, 0, ''),
(4, 'Odsjek za elektroenergetiku', 1, 'EE', 0, 0, ''),
(5, 'Odsjek za telekomunikacije', 1, 'TK', 0, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `ispit`
--

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

--
-- Dumping data for table `ispit`
--


-- --------------------------------------------------------

--
-- Table structure for table `ispitocjene`
--

CREATE TABLE IF NOT EXISTS `ispitocjene` (
  `ispit` int(11) NOT NULL,
  `student` int(11) NOT NULL,
  `ocjena` float NOT NULL,
  PRIMARY KEY (`ispit`,`student`),
  KEY `student` (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `ispitocjene`
--


-- --------------------------------------------------------

--
-- Table structure for table `ispit_termin`
--

CREATE TABLE IF NOT EXISTS `ispit_termin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datumvrijeme` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `maxstudenata` int(11) NOT NULL,
  `deadline` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ispit` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ispit` (`ispit`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `ispit_termin`
--


-- --------------------------------------------------------

--
-- Table structure for table `izbor`
--

CREATE TABLE IF NOT EXISTS `izbor` (
  `fk_osoba` int(11) NOT NULL,
  `fk_naucnonastavno_zvanje` int(11) NOT NULL,
  `datum_izbora` date NOT NULL,
  `datum_isteka` date NOT NULL,
  `fk_naucna_oblast` int(11) NOT NULL,
  `fk_uza_naucna_oblast` int(11) NOT NULL,
  `dopunski` tinyint(1) NOT NULL,
  `druga_institucija` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `izbor`
--


-- --------------------------------------------------------

--
-- Table structure for table `izborni_slot`
--

CREATE TABLE IF NOT EXISTS `izborni_slot` (
  `id` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY (`id`,`predmet`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `izborni_slot`
--


-- --------------------------------------------------------

--
-- Table structure for table `kanton`
--

CREATE TABLE IF NOT EXISTS `kanton` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `kratki_naziv` varchar(5) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

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

CREATE TABLE IF NOT EXISTS `kolizija` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  KEY `student` (`student`,`akademska_godina`),
  KEY `predmet` (`predmet`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `kolizija`
--


-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

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

--
-- Dumping data for table `komentar`
--


-- --------------------------------------------------------

--
-- Table structure for table `komponenta`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

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

CREATE TABLE IF NOT EXISTS `komponentebodovi` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `komponenta` int(11) NOT NULL,
  `bodovi` double NOT NULL,
  PRIMARY KEY (`student`,`predmet`,`komponenta`),
  KEY `predmet` (`predmet`),
  KEY `komponenta` (`komponenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `komponentebodovi`
--

-- --------------------------------------------------------

--
-- Table structure for table `konacna_ocjena`
--

CREATE TABLE IF NOT EXISTS `konacna_ocjena` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `ocjena` int(3) NOT NULL,
  `datum` datetime NOT NULL,
  `datum_u_indeksu` date NOT NULL,
  `odluka` int(11) default NULL,
  `datum_provjeren` tinyint(1) NOT NULL default '0',
  PRIMARY KEY (`student`,`predmet`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `odluka` (`odluka`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `konacna_ocjena`
--


-- --------------------------------------------------------

--
-- Table structure for table `kviz`
--

CREATE TABLE IF NOT EXISTS `kviz` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `vrijeme_pocetak` datetime NOT NULL,
  `vrijeme_kraj` datetime NOT NULL,
  `labgrupa` int(11) NOT NULL,
  `ip_adrese` text collate utf8_slovenian_ci NOT NULL,
  `prolaz_bodova` float NOT NULL,
  `broj_pitanja` int(11) NOT NULL,
  `trajanje_kviza` int(11) NOT NULL COMMENT 'u sekundama',
  `aktivan` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `predmet` (`predmet`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `labgrupa` (`labgrupa`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `kviz_odgovor`
--

CREATE TABLE IF NOT EXISTS `kviz_odgovor` (
  `id` int(11) NOT NULL auto_increment,
  `kviz_pitanje` int(11) NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  `tacan` tinyint(1) NOT NULL,
  `vidljiv` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `kviz_pitanje` (`kviz_pitanje`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `kviz_pitanje`
--
	
CREATE TABLE IF NOT EXISTS `kviz_pitanje` (
  `id` int(11) NOT NULL auto_increment,
  `kviz` int(11) NOT NULL,
  `tip` enum('mcsa','mcma','tekstualno') collate utf8_slovenian_ci NOT NULL default 'mcsa',
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  `bodova` float NOT NULL default '1',
  `vidljivo` tinyint(1) NOT NULL default '1',
  `ukupno` int(11) NOT NULL,
  `tacnih` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `kviz` (`kviz`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `kviz_student`
--

CREATE TABLE IF NOT EXISTS `kviz_student` (
  `student` int(11) NOT NULL,
  `kviz` int(11) NOT NULL,
  `dovrsen` tinyint(1) NOT NULL default '0',
  `bodova` float NOT NULL,
  `vrijeme_aktivacije` datetime NOT NULL,
  PRIMARY KEY  (`student`,`kviz`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `labgrupa`
--

CREATE TABLE IF NOT EXISTS `labgrupa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `predmet` int(11) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `virtualna` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `predmet` (`predmet`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `labgrupa`
--

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `userid` int(11) NOT NULL DEFAULT '0',
  `dogadjaj` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `nivo` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dogadjaj` (`dogadjaj`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `log`
--
-- --------------------------------------------------------

--
-- Table structure for table `mjesto`
--

CREATE TABLE IF NOT EXISTS `mjesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(40) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `drzava` int(11) NOT NULL,
  `opcina_van_bih` varchar(40) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `opcina` (`opcina`),
  KEY `drzava` (`drzava`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=79 ;

--
-- Dumping data for table `mjesto`
--

INSERT INTO `mjesto` (`id`, `naziv`, `opcina`, `drzava`) VALUES
(1, 'Sarajevo', 0, 1),
(2, 'Sarajevo', 13, 1),
-- Sarajevo je mjesto koje se prostire na vise opcina,
-- ali dodajemo i varijantu sa opcinom Centar radi oznacavanja
-- mjesta rodjenja
(3, 'Zenica', 77, 1),
(4, 'Mostar', 46, 1),
(5, 'Banja Luka', 93, 1),
(6, 'Bihać', 2, 1),
(7, 'Tuzla', 69, 1);

-- --------------------------------------------------------

--
-- Table structure for table `moodle_predmet_id`
--

CREATE TABLE IF NOT EXISTS `moodle_predmet_id` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `moodle_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `moodle_predmet_id`
--


-- --------------------------------------------------------

--
-- Table structure for table `moodle_predmet_rss`
--

CREATE TABLE IF NOT EXISTS `moodle_predmet_rss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vrstanovosti` int(2) NOT NULL,
  `moodle_id` int(11) NOT NULL,
  `sadrzaj` text COLLATE utf8_slovenian_ci NOT NULL,
  `vrijeme_promjene` bigint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `moodle_id` (`moodle_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `moodle_predmet_rss`
--


-- --------------------------------------------------------

--
-- Table structure for table `nacin_studiranja`
--

CREATE TABLE IF NOT EXISTS `nacin_studiranja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `moguc_upis` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `nacionalnost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

CREATE TABLE IF NOT EXISTS `nastavnik_predmet` (
  `nastavnik` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `nivo_pristupa` enum('nastavnik','super_asistent','asistent') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'asistent',
  PRIMARY KEY (`nastavnik`,`akademska_godina`,`predmet`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `nastavnik_predmet`
--

-- --------------------------------------------------------

--
-- Table structure for table `odluka`
--

CREATE TABLE IF NOT EXISTS `odluka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `broj_protokola` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `student` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student` (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `odluka`
--


-- --------------------------------------------------------

--
-- Table structure for table `ogranicenje`
--

CREATE TABLE IF NOT EXISTS `ogranicenje` (
  `nastavnik` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0',
  `zavrsnirad` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`nastavnik`,`labgrupa`),
  KEY `labgrupa` (`labgrupa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `ogranicenje`
--


-- --------------------------------------------------------

--
-- Table structure for table `opcina`
--

CREATE TABLE IF NOT EXISTS `opcina` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=145 ;

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
  `datum_rodjenja` date default NULL,
  `mjesto_rodjenja` int(11) default NULL,
  `nacionalnost` int(11) default NULL,
  `drzavljanstvo` int(11) default NULL,
  `boracke_kategorije` tinyint(1) NOT NULL,
  `jmbg` varchar(14) COLLATE utf8_slovenian_ci NOT NULL,
  `adresa` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `adresa_mjesto` int(11) default NULL,
  `telefon` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `kanton` int(11) default NULL,
  `treba_brisati` tinyint(1) NOT NULL DEFAULT '0',
  `fk_akademsko_zvanje` int(11) NOT NULL DEFAULT '5', -- 5 = srednja strucna sprema
  `fk_naucni_stepen` int(11) NOT NULL DEFAULT '6', -- 6 = bez naucnog stepena
  `slika` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `djevojacko_prezime` VARCHAR(30) NOT NULL,
  `maternji_jezik` INT NOT NULL,
  `vozacka_dozvola` INT NOT NULL,
  `nacin_stanovanja` INT NOT NULL,
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

INSERT INTO `osoba` (`id`, `ime`, `prezime`, `brindexa`, `datum_rodjenja`, `mjesto_rodjenja`, `drzavljanstvo`, `jmbg`, `adresa`, `adresa_mjesto`, `telefon`, `kanton`, `treba_brisati`) VALUES
(1, 'Site', 'Admin', '', NULL,NULL, NULL, '', '', NULL, '', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `plan_studija`
--

CREATE TABLE IF NOT EXISTS `plan_studija` (
  `godina_vazenja` int(11) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `obavezan` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `plan_studija`
--

-- --------------------------------------------------------


--
-- Table structure for table `ponudakursa`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

--
-- Dumping data for table `ponudakursa`
--

-- --------------------------------------------------------

--
-- Table structure for table `poruka`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `poruka`
--


-- --------------------------------------------------------

--
-- Table structure for table `predmet`
--

CREATE TABLE IF NOT EXISTS `predmet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sifra` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `institucija` int(11) NOT NULL DEFAULT '0',
  `kratki_naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  `ects` float NOT NULL,
  `sati_predavanja` int(11) NOT NULL DEFAULT '0',
  `sati_vjezbi` int(11) NOT NULL DEFAULT '0',
  `sati_tutorijala` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `institucija` (`institucija`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `predmet`
--

-- --------------------------------------------------------

--
-- Table structure for table `predmet_projektni_parametri`
--

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

--
-- Dumping data for table `predmet_projektni_parametri`
--


-- --------------------------------------------------------

--
-- Table structure for table `preference`
--

CREATE TABLE IF NOT EXISTS `preference` (
  `korisnik` int(11) NOT NULL,
  `preferenca` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijednost` varchar(100) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `preference`
--


-- --------------------------------------------------------

--
-- Table structure for table `prijemni_obrazac`
--

CREATE TABLE IF NOT EXISTS `prijemni_obrazac` (
  `prijemni_termin` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `sifra` varchar(6) collate utf8_slovenian_ci NOT NULL,
  `jezik` enum('bs','en') collate utf8_slovenian_ci NOT NULL default 'bs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;



-- --------------------------------------------------------

--
-- Table structure for table `prijemni_prijava`
--

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

--
-- Dumping data for table `prijemni_prijava`
--


-- --------------------------------------------------------

--
-- Table structure for table `prijemni_termin`
--

CREATE TABLE IF NOT EXISTS `prijemni_termin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `akademska_godina` int(11) NOT NULL,
  `datum` date NOT NULL,
  `ciklus_studija` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `prijemni_termin`
--


-- --------------------------------------------------------

--
-- Table structure for table `prisustvo`
--

CREATE TABLE IF NOT EXISTS `prisustvo` (
  `student` int(11) NOT NULL DEFAULT '0',
  `cas` int(11) NOT NULL DEFAULT '0',
  `prisutan` tinyint(1) NOT NULL DEFAULT '0',
  `plus_minus` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`student`,`cas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `prisustvo`
--

-- --------------------------------------------------------

--
-- Table structure for table `privilegije`
--

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
(1, 'nastavnik');

-- --------------------------------------------------------

--
-- Table structure for table `priznavanje`
--

CREATE TABLE IF NOT EXISTS `priznavanje` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `ciklus` int(1) NOT NULL,
  `naziv_predmeta` varchar(250) collate utf8_slovenian_ci NOT NULL,
  `sifra_predmeta` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `ects` float NOT NULL,
  `ocjena` int(11) NOT NULL,
  `odluka` int(11) NOT NULL,
  `strana_institucija` varchar(250) collate utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `priznavanje`
--

-- --------------------------------------------------------

--
-- Table structure for table `programskijezik`
--

CREATE TABLE IF NOT EXISTS `programskijezik` (
  `id` int(10) NOT NULL DEFAULT '0',
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `geshi` varchar(20) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `ekstenzija` varchar(10) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `ace` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `kompajler` varchar(10) collate utf8_slovenian_ci NOT NULL,
  `opcije_kompajlera` text collate utf8_slovenian_ci NOT NULL,
  `opcije_kompajlera_debug` text collate utf8_slovenian_ci NOT NULL,
  `debugger` varchar(10) collate utf8_slovenian_ci NOT NULL,
  `profiler` varchar(10) collate utf8_slovenian_ci NOT NULL,
  `opcije_profilera` varchar(200) collate utf8_slovenian_ci NOT NULL,
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

--
-- Dumping data for table `projekat`
--


-- --------------------------------------------------------

--
-- Table structure for table `projekat_file`
--

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

--
-- Dumping data for table `projekat_file`
--


-- --------------------------------------------------------

--
-- Table structure for table `projekat_file_diff`
--

CREATE TABLE IF NOT EXISTS `projekat_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `projekat_file_diff`
--


-- --------------------------------------------------------

--
-- Table structure for table `projekat_link`
--

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

--
-- Dumping data for table `projekat_link`
--


-- --------------------------------------------------------

--
-- Table structure for table `projekat_rss`
--

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

--
-- Dumping data for table `projekat_rss`
--


-- --------------------------------------------------------

--
-- Table structure for table `promjena_odsjeka`
--

CREATE TABLE IF NOT EXISTS `promjena_odsjeka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osoba` int(11) NOT NULL,
  `iz_odsjeka` int(11) NOT NULL,
  `u_odsjek` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `promjena_odsjeka`
--


-- --------------------------------------------------------

--
-- Table structure for table `promjena_podataka`
--

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
  `fk_akademsko_zvanje` int(11) NOT NULL DEFAULT '5', -- 5 = srednja strucna sprema
  `fk_naucni_stepen` int(11) NOT NULL DEFAULT '6', -- 6 = bez naucnog stepena
  `slika` VARCHAR(50) NOT NULL,
  `djevojacko_prezime` VARCHAR(30) NOT NULL,
  `maternji_jezik` INT NOT NULL,
  `vozacka_dozvola` INT NOT NULL,
  `mobilni_telefon` VARCHAR( 15 ) NOT NULL,
  `nacin_stanovanja` INT NOT NULL,
  `vrijeme_zahtjeva` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `promjena_podataka`
--


-- --------------------------------------------------------

--
-- Table structure for table `prosliciklus_ocjene`
--

CREATE TABLE IF NOT EXISTS `prosliciklus_ocjene` (
  `osoba` int(11) NOT NULL,
  `redni_broj` int(11) NOT NULL,
  `ocjena` tinyint(5) NOT NULL,
  `ects` float NOT NULL,
  PRIMARY KEY (`osoba`,`redni_broj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `prosliciklus_ocjene`
--


-- --------------------------------------------------------

--
-- Table structure for table `prosliciklus_uspjeh`
--

CREATE TABLE IF NOT EXISTS `prosliciklus_uspjeh` (
  `osoba` int(11) NOT NULL,
  `fakultet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `broj_semestara` int(11) NOT NULL,
  `opci_uspjeh` double NOT NULL,
  `dodatni_bodovi` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `prosliciklus_uspjeh`
--


-- --------------------------------------------------------

--
-- Table structure for table `raspored`
--

CREATE TABLE IF NOT EXISTS `raspored` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studij` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `raspored`
--
-- --------------------------------------------------------

--
-- Table structure for table `raspored_sala`
--

CREATE TABLE IF NOT EXISTS `raspored_sala` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `kapacitet` int(5) DEFAULT NULL,
  `tip` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `raspored_sala`
--


-- --------------------------------------------------------

--
-- Table structure for table `raspored_stavka`
--

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

--
-- Dumping data for table `raspored_stavka`
--


-- --------------------------------------------------------

--
-- Table structure for table `ras_sati`
--

CREATE TABLE IF NOT EXISTS `ras_sati` (
  `idS` tinyint(1) NOT NULL AUTO_INCREMENT,
  `satS` varchar(13) NOT NULL,
  PRIMARY KEY (`idS`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

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

CREATE TABLE IF NOT EXISTS `rss` (
  `id` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `auth` int(11) NOT NULL,
  `access` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `rss`
--

-- --------------------------------------------------------

--
-- Table structure for table `savjet_dana`
--

CREATE TABLE IF NOT EXISTS `savjet_dana` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  `vrsta_korisnika` enum('nastavnik','student','studentska','siteadmin') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'nastavnik',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=21 ;

--
-- Dumping data for table `savjet_dana`
--

INSERT INTO `savjet_dana` (`id`, `tekst`, `vrsta_korisnika`) VALUES
(1, '<p>...da je Charles Babbage, matematičar i filozof iz 19. vijeka za kojeg se smatra da je otac ideje prvog programabilnog računara, u svojoj biografiji napisao:</p>\r\n\r\n<p><i>U dva navrata su me pitali</i></p>\r\n\r\n<p><i>"Molim Vas gospodine Babbage, ako u Vašu mašinu stavite pogrešne brojeve, da li će izaći tačni odgovori?"</i></p>\r\n\r\n<p><i>Jednom je to bio pripadnik Gornjeg, a jednom Donjeg doma. Ne mogu da potpuno shvatim tu vrstu konfuzije ideja koja bi rezultirala takvim pitanjem.</i></p>', 'nastavnik'),
(2, '<p>...da sada možete podesiti sistem bodovanja na vašem predmetu (broj bodova koje studenti dobijaju za ispite, prisustvo, zadaće, seminarski rad, projekte...)?</p>\r\n<ul><li>Kliknite na dugme [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite opciju <i>Sistem bodovanja</i>.</li>\r\n<li>Slijedite uputstva.</li></ul>\r\n<p><b>Važna napomena:</b> Promjena sistema bodovanja može dovesti do gubitka do sada upisanih bodova na predmetu!</p>', 'nastavnik'),
(3, '<p>...da možete pristupiti Dosjeu studenta sa svim podacima koji se tiču uspjeha studenta na datom predmetu? Dosje studenta sadrži, između ostalog:</p>\r\n<ul><li>Fotografiju studenta;</li>\r\n<li>Koliko puta je student ponavljao predmet, da li je u koliziji, da li je prenio predmet na višu godinu;</li>\r\n<li>Sve podatke sa pogleda grupe (prisustvo, zadaće, rezultati ispita, konačna ocjena) sa mogućnošću izmjene svakog podatka;</li>\r\n<li>Za ispite i konačnu ocjenu možete vidjeti dnevnik izmjena sa informacijom ko je i kada izmijenio podatak.</li>\r\n<li>Brze linkove na dosjee istog studenta sa ranijih akademskih godina (ako je ponavljao/la predmet).</li></ul>\r\n\r\n<p>Dosjeu studenta možete pristupiti tako što kliknete na ime studenta u pregledu grupe. Na vašem početnom ekranu kliknite na ime grupe ili link <i>(Svi studenti)</i>, a zatim na ime i prezime studenta.</p>\r\n	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.
</i></p>', 'nastavnik'),
(4, '<p>...da možete ostavljati kratke tekstualne komentare na rad studenata?</p>\r\n<p>Na vašem početnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>. Zatim kliknite na ikonu sa oblačićem pored imena studenta:<br>\r\n<img src="images/16x16/komentar-plavi.png" width="16" height="16"></p>\r\n<p>Možete dobiti pregled studenata sa komentarima na sljedeći način:<br>\r\n<ul><li>Pored naziva predmeta kliknite na link [EDIT].</li>\r\n<li>Zatim s lijeve strane kliknite na link <i>Izvještaji</i>.</li>\r\n<li>Konačno, kliknite na opciju <i>Spisak studenata</i> - <i>Sa komentarima na rad</i>.</li></ul>\r\n<p>Na istog studenta možete ostaviti više komentara pri čemu je svaki komentar datiran i označeno je ko ga je ostavio.</p>	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 7-8.</i></p>', 'nastavnik'),
(5, '<p>...da možete brzo i lako pomoću nekog spreadsheet programa (npr. MS Excel) kreirati grupe na predmetu?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"><br>\r\nDobićete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte naziv grupe npr. "Grupa 1" (bez navodnika). Koristite Copy i Paste opcije Excela da biste brzo definisali grupu za sve studente.</li>\r\n<li>Kada završite definisanje grupa, koristeći tipku Shift i tipke sa strelicama označite imena studenata i imena grupa. Nemojte označiti naslov niti redni broj. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga 
otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja. Sada s lijeve strane izaberite opciju <i>Grupe za predavanja i vježbe</i>.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos studenata u grupe</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li></ul>\r\n<p>Ovim su grupe kreirane!</p>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 16.</i></p>', 'nastavnik'),
(6, '<p>...da možete brzo i lako ocijeniti zadaću svim studentima na predmetu ili u grupi, koristeći neki spreadsheet program (npr. MS Excel)?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, a s desne izaberite izvještaj <i>Spisak studenata</i> - <i>Bez grupa</i>. Alternativno, ako želite unositi ocjene samo za jednu grupu, možete koristiti izvještaj <i>Jedna kolona po grupama</i> pa u Excelu pobrisati sve grupe osim one koja vas interesuje.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"></li>\r\n<li>Pored imena svakog studenta nalazi se broj indeksa. <b>Umjesto broja indeksa</b> upišite broj bodova ostvarenih na određenom zadatku određene zadaće.</li>\r\n<li>Korištenjem tipke Shift i tipki sa strelicama izaberite samo imena studenata i bodove. Nemojte selektovati naslov ili redne brojeve. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite 
se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja. Sada s lijeve strane izaberite opciju <i>Kreiranje i unos zadaća</i>.</li>\r\n<li>Uvjerite se da je na spisku <i>Postojeće zadaće</i> definisana zadaća koju želite unijeti. Ako nije, popunite formular ispod naslova <i>Kreiranje zadaće</i> sa odgovarajućim podacima.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos zadaća</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>U polju <i>Izaberite zadaću</i> odaberite upravo kreiranu zadaću. Ako zadaća ima više zadataka, u polju <i>Izaberite zadatak</i> odaberite koji zadatak masovno unosite.\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>
\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li>\r\n<li>Ovu proceduru sada vrlo lako možete ponoviti za sve zadatke i sve zadaće zato što već imate u Excelu sve podatke osim broja bodova.</li></ul>\r\n<p>Ovim su rezultati zadaće uneseni za sve studente!</p>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 27-28.</i></p>', 'nastavnik'),
(12, '<p>...da možete ograničiti format datoteke u kojem studenti šalju zadaću?</p>\r\n<p>Prilikom kreiranja nove zadaće, označite opciju pod nazivom <i>Slanje zadatka u formi attachmenta</i>. Pojaviće se spisak tipova datoteka koje studenti mogu koristiti prilikom slanja zadaće u formi attachmenta.</p>\r\n<p>Izaberite jedan ili više formata kako bi studenti dobili grešku u slučaju da pokušaju poslati zadaću u nekom od formata koje niste izabrali. Ako ne izaberete nijednu od ponuđenih opcija, biće dozvoljeni svi formati datoteka, uključujući i one koji nisu navedeni na spisku.</p>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 26-27.</i></p>', 'nastavnik'),
(7, '<p>...da možete preuzeti odjednom sve zadaće koje su poslali studenti u grupi u formi ZIP fajla, pri čemu su zadaće imenovane po sistemu Prezime_Ime_BrojIndeksa?</p>\r\n<ul><li>Na vašem početnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>.</li>\r\n<li>U zaglavlju tabele sa spiskom studenata možete vidjeti navedene zadaće: npr. Zadaća 1, Zadaća 2 itd.</li>\r\n<li>Ispod naziva svake zadaće nalazi se riječ <i>Download</i> koja predstavlja link - kliknite na njega.</li></ul>	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 11-12.</i></p>', 'nastavnik'),
(8, '<p>...da možete imati više termina jednog ispita? Pri tome se datum termina ne mora poklapati sa datumom ispita.</p>\r\n<p>Datum ispita se daje samo okvirno, kako bi se po nečemu razlikovali npr. junski rok i septembarski rok. Datum koji studentu piše na prijavi je datum koji pridružite terminu za prijavu ispita.</p>\r\n<p>Da biste definisali termine ispita:</p>\r\n<ul><li>Najprije kreirajte ispit, tako što ćete kliknuti na link [EDIT] a zatim izabrati opciju Ispiti s lijeve strane. Zatim popunite formular ispod naslova <i>Kreiranje novog ispita</i>.</li>\r\n<li>U tabeli ispita možete vidjeti novi ispit. Desno od ispita možete vidjeti link <i>Termini</i>. Kliknite na njega.</li>\r\n<li>Zatim kreirajte proizvoljan broj termina popunjavajući formular ispod naslova <i>Registrovanje novog termina</i>.</li></ul>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, poglavlje "Prijavljivanje za ispit" (str. 21-26).</i></p>', 'nastavnik'),
(9, '<p>...da, u slučaju da se neki student nije prijavio/la za vaš ispit, možete ih manuelno prijaviti na termin kako bi imao/la korektan datum na prijavi?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta. S lijeve strane izaberite link <i>Ispiti</i>.</li>\r\n<li>U tabeli ispita locirajte ispit koji želite i kliknite na link <i>Termini</i> desno od željenog ispita.</li>\r\n<li>Ispod naslova <i>Objavljeni termini</i> izaberite željeni termin i kliknite na link <i>Studenti</i> desno od željenog termina.</li>\r\n<li>Sada možete vidjeti sve studente koji su se prijavili za termin. Pored imena i prezimena studenta možete vidjeti dugme <i>Izbaci</i> kako student više ne bi bio prijavljen za taj termin.</li>\r\n<li>Ispod tabele studenata možete vidjeti padajući spisak svih studenata upisanih na vaš predmet. Izaberite na padajućem spisku studenta kojeg želite prijaviti za termin i kliknite na dugme <i>Dodaj</i>.</li></ul>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" 
target="_new">Uputstvima za upotrebu</a>, str. 26.</i></p>', 'nastavnik'),
(10, '<p>...da upisom studenata na predmete u Zamgeru sada u potpunosti rukuje Studentska služba?</p>\r\n<p>Ako vam se pojavi student kojeg nemate na spiskovima u Zamgeru, recite mu da se <b>obavezno</b> javi u Studentsku službu, ne samo radi vašeg predmeta nego generalno radi regulisanja statusa (npr. neplaćenih školarina, taksi i slično).</p>', 'nastavnik'),
(11, '<p>...da svaki korisnik može imati jedan od tri nivoa pristupa bilo kojem predmetu:</p><ul><li><i>asistent</i> - može unositi prisustvo časovima i ocjenjivati zadaće</li><li><i>super-asistent</i> - može unositi sve podatke osim konačne ocjene</li><li><i>nastavnik</i> - može unositi i konačnu ocjenu.</li></ul><p>Početni nivoi pristupa se određuju na osnovu zvanično usvojenog nastavnog ansambla, a u slučaju da želite promijeniti nivo pristupa bez izmjena u ansamblu (npr. kako biste asistentu dali privilegije unosa rezultata ispita), kontaktirajte Studentsku službu.</p>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 3-4.</i></p>', 'nastavnik'),
(13, '<p>...da možete utjecati na format u kojem se izvještaj prosljeđuje Excelu kada kliknete na Excel ikonu u gornjem desnom uglu izvještaja?<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"></p>\r\n<p>Može se desiti da izvještaj ne izgleda potpuno kako treba u vašem spreadsheet programu. Podaci se šalju u CSV formatu pod pretpostavkom da koristite regionalne postavke za BiH (ili Hrvatsku ili Srbiju). Ako izvještaj u vašem programu ne izgleda kako treba, slijedi nekoliko savjeta kako možete utjecati na to.</p>\r\n<ul><li>Ako se svi podaci nalaze u jednoj koloni, vjerovatno je da koristite sistem sa Američkim regionalnim postavkama. U vašem Profilu možete pod Zamger opcije izabrati CSV separator "zarez" umjesto "tačka-zarez", ali vjerovatno je da vam naša slova i dalje neće izgledati kako treba.</li>\r\n<li>Moguće je da će dokument izgledati ispravno, osim slova sa afrikatima koja će biti zamijenjena nekim drugim. Na žalost, ne postoji način da se ovo riješi. Excel može učitati CSV datoteke 
isključivo u formatu koji ne podržava prikaz naših slova. Možete uraditi zamjenu koristeći Replace opciju vašeg programa. Nešto složenija varijanta je da koristite "Save Link As" opciju vašeg web preglednika, promijenite naziv dokumenta iz izvjestaj.csv u izvjestaj.txt, a zatim koristite <a href="http://office.microsoft.com/en-us/excel-help/text-import-wizard-HP010102244.aspx">Excel Text Import Wizard</a>.</li>\r\n<li>Ako koristite OpenOffice.org uredski paket, prilikom otvaranja dokumenta izaberite Text encoding "Eastern European (Windows-1250)", a kao razdjelnik (Delimiter) izaberite tačka-zarez (Semicolon). Ostale opcije obavezno isključite. Takođe isključite opciju spajanja razdjelnika (Merge delimiters).</li>\r\n<li>Može se desiti da vaš program prepozna određene stavke (npr. redne brojeve ili ostvarene bodove) kao datum, pogotovo ako ste poslušali savjet iz prve tačke - odnosno, ako ste kao CSV separator podesili "zarez".</li>\r\n<li>U velikoj većini slučajeva možete dobiti potpuno zadovoljavajuće 
rezultate ako otvorite prazan dokument u vašem spreadsheet programu (npr. Excel) i zatim napravite copy-paste kompletnog sadržaja web stranice.</li></ul>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, strana 32-33.</i></p>', 'nastavnik'),
(14, '<p>...da možete brzo i lako pomoću nekog spreadsheet programa (npr. MS Excel) unijeti rezultate ispita ili konačne ocjene?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>. Ili, ako vam je lakše unositi podatke po grupama, izaberite izvještaj <i>Jedna kolona po grupama</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"><br>\r\nDobićete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte broj bodova koje je student ostvario na ispitu ili konačnu ocjenu.</li>\r\n<li>Kada završite unos rezultata/ocjena, koristeći tipku Shift i tipke sa strelicama označite imena studenata i ocjene. Nemojte označiti naslov niti redni broj studenta. Držeći tipku Ctrl pritisnite tipku C.</li>
\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja.</li>\r\n<li>Ako unosite konačne ocjene, s lijeve strane izaberite opciju <i>Konačna ocjena</i>.</li>\r\n<li>Ako unosite rezultate ispita, s lijeve strane izaberite opciju <i>Ispiti</i>, kreirajte novi ispit, a zatim kliknite na link <i>Masovni unos rezultata</i> pored novokreiranog ispita.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos ocjena</i> i pritisnite Ctrl+V. Trebalo bi da ugledate rezultate ispita odnosno ocjene.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> (a ne Prezime[TAB]Ime), te da pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme 
<i>Potvrda</i>.</li></ul>\r\n<p>Ovim su unesene ocjene / rezultati ispita!</p>\r\n\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 18-20 (masovni unos ispita) i str. 28-29 (masovni unos konačne ocjene).</i></p>', 'nastavnik'),
(15, '<p>...da kod evidencije prisustva, pored stanja "prisutan" (zelena boja) i stanja "odsutan" (crvena boja) postoji i nedefinisano stanje (žuta boja). Ovo stanje se dodjeljuje ako je student upisan u grupu nakon što su održani određeni časovi.</p>\r\n<p>Drečavo žuta boja je odabrana kako bi se predmetni nastavnik odnosno asistent podsjetio da se mora odlučiti da li će studentu priznati časove kao prisustva ili ne. U međuvremenu, nedefinisano stanje će se tumačiti u korist studenta, odnosno neće ulaziti u broj izostanaka prilikom određivanja da li je student izgubio bodove za prisustvo.</p>\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik'),
(16, '<p>...da ne morate voditi evidenciju o prisustvu kroz Zamger ako ne želite, a i dalje možete imati ažuran broj bodova ostvarenih na prisustvo?</p>\r\n<p>Sistem bodovanja je takav da student dobija 10 bodova ako je odsustvovao manje od 4 puta, a 0 bodova ako je odsustvovao 4 ili više puta. Podaci o konkretnim održanim časovima u Zamgeru se ne koriste nigdje osim za internu evidenciju na predmetu.</p>\r\n<p>Dakle, u slučaju da imate vlastitu evidenciju, samo kreirajte četiri časa (datum je nebitan) i unesite četiri izostanka studentima koji nisu zadovoljili prisustvo.</p>	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 4-5.</i></p>', 'nastavnik'),
(17, '<p>...da možete podesiti drugačiji sistem bodovanja za prisustvo od ponuđenog?</p>\r\n<p>Možete podesiti ukupan broj bodova za prisustvo (različit od 10). Možete promijeniti maksimalan broj dozvoljenih izostanaka (različit od 3) ili pak podesiti linearno bodovanje u odnosu na broj izostanaka (npr. ako je student od 14 časova izostao 2 puta, dobiće (12/14)*10 = 8,6 bodova). Konačno, umjesto evidencije pojedinačnih časova, možete odabrati da direktno unosite broj bodova za prisustvo po uzoru na rezultate ispita.</p>\r\n<p>Da biste aktivirali ovu mogućnost, trebate promijeniti sistem bodovanja samog predmeta.</p>', 'nastavnik'),
(18, '<p>...da možete unijeti bodove za zadaću čak i ako je student nije poslao kroz Zamger?</p>\r\n<p>Da biste to uradili, potrebno je da kliknete na link <i>Prikaži dugmad za kreiranje zadataka</i> koji se nalazi u dnu stranice sa prikazom grupe (vidi sliku). Nakon što ovo uradite, ćelije tabele koje odgovaraju neposlanim zadaćama će se popuniti ikonama za kreiranje zadaće koje imaju oblik sijalice.</p>\r\n<p><a href="doc/savjet_sijalice.png" target="_new">Slika</a> - ukoliko ne vidite detalje, raširite prozor!</p>	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 10-11.</i></p>\r\n<p>U slučaju da se na vašem predmetu zadaće generalno ne šalju kroz Zamger, vjerovatno će brži način rada za vas biti da koristite masovni unos. Više informacija na str. 27-28. Uputstava.</p>', 'nastavnik'),
(19, '<p>...da pomoću Zamgera možete poslati cirkularni mail svim studentima na vašem predmetu ili u pojedinim grupama?</p>\r\n<p>Da biste pristupili ovoj opciji:</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta</li>\r\n<li>U meniju sa lijeve strane odaberite opciju <i>Obavještenja za studente</i>.</li>\r\n<li>Pod menijem <i>Obavještenje za:</i> odaberite da li obavještenje šaljete svim studentima na predmetu ili samo studentima koji su članovi određene grupe.</li>\r\n<li>Aktivirajte opciju <i>Slanje e-maila</i>. Ako ova opcija nije aktivna, studenti će i dalje vidjeti vaše obavještenje na svojoj Zamger početnoj stranici (sekcija Obavještenja) kao i putem RSSa.</li>\r\n<li>U dio pod naslovom <i>Kraći tekst</i> unesite udarnu liniju vaše informacije.</li>\r\n<li>U dio pod naslovom <i>Detaljan tekst</i> možete napisati dodatna pojašnjenja, a možete ga i ostaviti praznim.</li>\r\n<li>Kliknite na dugme <i>Pošalji</i>. Vidjećete jedno po jedno ime studenta kojem je poslan mail kao i e-mail adresu na 
koju je mail poslan. Slanje veće količine mailova može potrajati nekoliko minuta.</li></ul>\r\n<p>Mailovi će biti poslani na adrese koje su studenti podesili koristeći svoj profil, ali i na zvanične fakultetske adrese.</p>\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 12-14.</i></p>', 'nastavnik'),
(20, '<p>...da je promjena grupe studenta destruktivna operacija kojom se nepovratno gube podaci o prisustvu studenta na časovima registrovanim za tu grupu?</p>\r\n<p>Studenta možete prebaciti u drugu grupu putem ekrana Dosje studenta: na pogledu grupe (npr. <i>Svi studenti</i>) kliknite na ime i prezime studenta da biste ušli u njegov ili njen dosje.</p>\r\n<p>Promjenom grupe nepovratno se gubi evidencija prisustva studenta na časovima registrovanim za prethodnu grupu. Naime, između časova registrovanih za dvije različite grupe ne postoji jednoznačno mapiranje. U nekom datom trenutku vremena u jednoj grupi može biti registrovano 10 časova a u drugoj 8. Kako znati koji od tih 10 časova odgovara kojem od onih 8? I šta raditi sa suvišnim časovima? Dakle, kada premjestite studenta u grupu u kojoj već postoje registrovani časovi, prisustvo studenta tim časovima će biti označeno kao nedefinisano (žuta boja). Prepušta se nastavnom ansamblu da odluči koje od tih časova će priznati kao prisutne, a koje markirati kao 
odsutne. Vjerovatno ćete se pitati šta ako se student ponovo vrati u polaznu grupu. Odgovor je da će podaci ponovo biti izgubljeni, jer šta raditi sa časovima registrovanim u međuvremenu?</p>\r\n<p>Preporučujemo da ne vršite promjene grupe nakon što počne akademska godina.</p>\r\n	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik');

-- --------------------------------------------------------

--
-- Table structure for table `septembar`
--

CREATE TABLE IF NOT EXISTS `septembar` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `septembar`
--


-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_akademsko_zvanje`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_akademsko_zvanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

--
-- Dumping data for table `sifrarnik_akademsko_zvanje`
--

INSERT INTO `sifrarnik_akademsko_zvanje` (`id`, `naziv`, `titula`) VALUES
(1, 'magistar elektrotehnike - diplomirani inženjer elektrotehnike', 'M.E.'),
(2, 'bakalaureat elektrotehnike - inženjer elektrotehnike', 'B.E.'),
(3, 'diplomirani inženjer elektrotehnike', 'dipl.ing.el.'),
(4, 'diplomirani matematičar', 'dipl.mat.'),
(5, 'srednja stručna sprema', ''),
(6, 'diplomirani inženjer mašinstva', 'dipl.ing.'),
(7, 'diplomirani inženjer građevinarstva', 'dipl.ing.'),
(8, 'diplomirani ekonomista', 'dipl.ecc.');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_banka`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_banka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_fakulteti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_fascati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_fascati_podoblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_jezik`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_jezik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `sifrarnik_maternji_jezik`
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

CREATE TABLE IF NOT EXISTS `sifrarnik_nacin_stanovanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_naucna_oblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `fk_maticna_institucija` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `sifrarnik_naucna_oblast`
--

INSERT INTO `sifrarnik_naucna_oblast` (`id`, `naziv`) VALUES
(302, 'Automatsko upravljanje'),
(304, 'Industrijska i procesna automatika'),
(306, 'Robotika i mehatronika'),
(308, 'Zaštita i upravljanje elektroenergetskim sistemima'),
(310, 'Sistemi i ekonomski inženjering u elektrotehnici'),
(312, 'Elektroničke komponenete i sistemi'),
(314, 'Digitalne strukture i obrada signala'),
(316, 'Bimedicinska elektronika'),
(318, 'Elektroenergetski sistemi'),
(320, 'Eolektroenergetska tehnologija'),
(322, 'Industrijska elektroenergetika'),
(324, 'Energija i okolina'),
(326, 'Teoretska elektrotehnika'),
(328, 'Arhitektura računarskih sistema i mreža'),
(330, 'Računarski informacioni sistemi'),
(332, 'Računarske nauke i obrada informacija'),
(334, 'Softver inžinjering'),
(336, 'Vještačka inteligencija i bioinformatika'),
(338, 'Matematske metode u računarstvu i informatici'),
(340, 'Tehnička informatika i procesno računarstvo'),
(342, 'Teorija telekomunikacija'),
(344, 'Telekomunikacijske tehnike'),
(346, 'Računarske i telekomunikacijske mreže'),
(348, 'Bežične telekomunikacije'),
(350, 'Automatika'),
(352, 'Elektronika'),
(354, 'Elektroenergetika'),
(356, 'Računarstvo i informatika'),
(358, 'Telekomunikacije');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_naucni_stepen`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_naucni_stepen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `naucni_stepen`
--

INSERT INTO `sifrarnik_naucni_stepen` (`id`, `naziv`, `titula`) VALUES
(1, 'doktor nauka', 'dr'),
(2, 'magistar nauka', 'mr'),
(6, 'bez naučnog stepena', '');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_naucnonastavno_zvanje`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_naucnonastavno_zvanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(20) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_nivo_jezika` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_oblik_zaposlenja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_radno_mjesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_strucna_sprema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_tip_mentorstva` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_tip_publikacije` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_uza_naucna_oblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  `fk_naucna_oblast` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_vozacki_kategorija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;


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
-- Table structure for table `srednja_ocjene`
--

CREATE TABLE IF NOT EXISTS `srednja_ocjene` (
  `osoba` int(11) NOT NULL,
  `razred` tinyint(4) NOT NULL,
  `redni_broj` int(1) NOT NULL,
  `ocjena` tinyint(5) NOT NULL,
  `tipocjene` tinyint(5) NOT NULL,
  PRIMARY KEY (`osoba`,`razred`,`redni_broj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `srednja_ocjene`
--


-- --------------------------------------------------------

--
-- Table structure for table `srednja_skola`
--

CREATE TABLE IF NOT EXISTS `srednja_skola` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `domaca` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `srednja_skola`
--


-- --------------------------------------------------------

--
-- Table structure for table `stdin`
--

CREATE TABLE IF NOT EXISTS `stdin` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `zadaca` bigint(20) NOT NULL DEFAULT '0',
  `redni_broj` int(11) NOT NULL DEFAULT '0',
  `ulaz` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stdin`
--


-- --------------------------------------------------------

--
-- Table structure for table `studentski_modul`
--

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

CREATE TABLE IF NOT EXISTS `studentski_modul_predmet` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `studentski_modul` int(11) NOT NULL,
  `aktivan` tinyint(1) NOT NULL,
  PRIMARY KEY (`predmet`,`akademska_godina`,`studentski_modul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `studentski_modul_predmet`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_ispit_termin`
--

CREATE TABLE IF NOT EXISTS `student_ispit_termin` (
  `student` int(11) NOT NULL,
  `ispit_termin` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`ispit_termin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `student_ispit_termin`
--


-- --------------------------------------------------------

--
-- Table structure for table `student_labgrupa`
--

CREATE TABLE IF NOT EXISTS `student_labgrupa` (
  `student` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`student`,`labgrupa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_labgrupa`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_predmet`
--

CREATE TABLE IF NOT EXISTS `student_predmet` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY (`student`,`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_predmet`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_projekat`
--

CREATE TABLE IF NOT EXISTS `student_projekat` (
  `student` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY (`student`,`projekat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_projekat`
--


-- --------------------------------------------------------

--
-- Table structure for table `student_studij`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_studij`
--

-- --------------------------------------------------------

--
-- Table structure for table `studij`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `studij`
--

INSERT INTO `studij` (`id`, `naziv`, `zavrsni_semestar`, `institucija`, `kratkinaziv`, `moguc_upis`, `tipstudija`, `preduslov`) VALUES
(1, 'Računarstvo i informatika (BSc)', 6, 2, 'RI', 1, 1, 1),
(2, 'Automatika i elektronika (BSc)', 6, 3, 'AE', 1, 1, 1),
(3, 'Elektroenergetika (BSc)', 6, 4, 'EE', 1, 1, 1),
(4, 'Telekomunikacije (BSc)', 6, 5, 'TK', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `svrha_potvrde`
--

CREATE TABLE IF NOT EXISTS `svrha_potvrde` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=37 ;

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
(3, 'Zadace', ''),
(4, 'Prisustvo', 'Minimalan broj izostanaka (0=linearno)'),
(5, 'Fiksna', '');

-- --------------------------------------------------------

--
-- Table structure for table `tippredmeta`
--

CREATE TABLE IF NOT EXISTS `tippredmeta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `tippredmeta`
--

INSERT INTO `tippredmeta` (`id`, `naziv`) VALUES
(1, 'ETF Bologna standard');

-- --------------------------------------------------------

--
-- Table structure for table `tippredmeta_komponenta`
--

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

CREATE TABLE IF NOT EXISTS `tipstudija` (
  `id` int(11) NOT NULL,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `ciklus` tinyint(2) NOT NULL,
  `trajanje` tinyint(3) NOT NULL,
  `moguc_upis` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `tipstudija`
--

INSERT INTO `tipstudija` (`id`, `naziv`, `ciklus`, `trajanje`, `moguc_upis`) VALUES
(1, 'Bakalaureat', 1, 6, 1),
(2, 'Master', 2, 4, 1),
(3, 'Doktorski studij', 3, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tip_potvrde`
--

CREATE TABLE IF NOT EXISTS `tip_potvrde` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=3 ;

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

CREATE TABLE IF NOT EXISTS `ugovoroucenju` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ugovoroucenju`
--


-- --------------------------------------------------------

--
-- Table structure for table `ugovoroucenju_izborni`
--

CREATE TABLE IF NOT EXISTS `ugovoroucenju_izborni` (
  `ugovoroucenju` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `ugovoroucenju_izborni`
--


-- --------------------------------------------------------

--
-- Table structure for table `ugovoroucenju_kapacitet`
--

CREATE TABLE IF NOT EXISTS `ugovoroucenju_kapacitet` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `kapacitet` int(11) NOT NULL default '0',
  `kapacitet_ekstra` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `ugovoroucenju_kapacitet`
--


-- --------------------------------------------------------

--
-- Table structure for table `upis_kriterij`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci COMMENT='Tabela za pohranu kriterija za upis' ;

--
-- Dumping data for table `upis_kriterij`
--


-- --------------------------------------------------------

--
-- Table structure for table `uspjeh_u_srednjoj`
--

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

--
-- Dumping data for table `uspjeh_u_srednjoj`
--


-- --------------------------------------------------------

--
-- Table structure for table `vozacka_dozvola`
--

CREATE TABLE IF NOT EXISTS `vozacka_dozvola` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `fk_vozacki_kategorija` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zadaca`
--

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
  `automatsko_testiranje` tinyint(1) NOT NULL default '0',
  `attachment` tinyint(1) NOT NULL DEFAULT '0',
  `dozvoljene_ekstenzije` varchar(255) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `postavka_zadace` varchar(255) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `komponenta` int(11) NOT NULL,
  `vrijemeobjave` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zadaca`
--


-- --------------------------------------------------------

--
-- Table structure for table `zadatak`
--

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
  KEY `uobicajen` (`zadaca`,`redni_broj`,`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zadatak`
--


-- --------------------------------------------------------

--
-- Table structure for table `zadatakdiff`
--

CREATE TABLE IF NOT EXISTS `zadatakdiff` (
  `zadatak` bigint(11) NOT NULL DEFAULT '0',
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`zadatak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zadatakdiff`
--


-- --------------------------------------------------------

--
-- Table structure for table `zahtjev_za_potvrdu`
--

CREATE TABLE IF NOT EXISTS `zahtjev_za_potvrdu` (
  `id` int(11) NOT NULL auto_increment,
  `student` int(11) default NULL,
  `tip_potvrde` int(11) default NULL,
  `svrha_potvrde` int(11) default NULL,
  `datum_zahtjeva` datetime default NULL,
  `status` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10972 ;


-- --------------------------------------------------------

--
-- Table structure for table `zavrsni`
--

CREATE TABLE IF NOT EXISTS `zavrsni` (
  `id` int(11) NOT NULL,
  `naslov` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `podnaslov` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `predmet` int(11) COLLATE utf8_slovenian_ci NOT NULL,
  `rad_na_predmetu` int(11) COLLATE utf8_slovenian_ci NOT NULL,
  `akademska_godina` varchar(10) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '0',
  `kratki_pregled` text COLLATE utf8_slovenian_ci NOT NULL,
  `literatura` text COLLATE utf8_slovenian_ci NOT NULL,
  `sazetak` text COLLATE utf8_slovenian_ci NOT NULL,
  `summary` text COLLATE utf8_slovenian_ci NOT NULL,
  `mentor` INT(11) NOT NULL,
  `student` INT(11) NOT NULL,
  `kandidat_potvrdjen` tinyint(4) NOT NULL,
  `biljeska` text COLLATE utf8_slovenian_ci NOT NULL,
  `predsjednik_komisije` INT(11) NOT NULL,
  `clan_komisije` INT(11) NOT NULL,
  `termin_odbrane` datetime NOT NULL,
  `konacna_ocjena` int(11) NOT NULL DEFAULT '5',
  `broj_diplome` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zavrsni`
--

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_bb_post`
--

CREATE TABLE IF NOT EXISTS `zavrsni_bb_post` (
  `id` int(11) NOT NULL,
  `naslov` varchar(300) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  `tema` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_bb_post_text`
--

CREATE TABLE IF NOT EXISTS `zavrsni_bb_post_text` (
  `post` int(11) NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_bb_tema`
--

CREATE TABLE IF NOT EXISTS `zavrsni_bb_tema` (
  `id` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `prvi_post` int(11) NOT NULL default '0',
  `zadnji_post` int(11) NOT NULL default '0',
  `pregleda` int(11) unsigned NOT NULL default '0',
  `osoba` int(11) NOT NULL,
  `zavrsni` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_file`
--

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

--
-- Dumping data for table `zavrsni_file`
--

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_file_diff`
--

CREATE TABLE IF NOT EXISTS `zavrsni_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zavrsni_file_diff`
--

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_rad_predmet`
--

CREATE TABLE IF NOT EXISTS `zavrsni_rad_predmet` (
  `id` int(11) NOT NULL,
  `naziv` varchar(11) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'Završni rad',
  `predmet` varchar(100) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `akademska_godina` varchar(9) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `student` varchar(100) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `nastavnik` varchar(100) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zavrsni_rad_predmet`
--



--
-- Constraints for dumped tables
--

--
-- Constraints for table `akademska_godina_predmet`
--
ALTER TABLE `akademska_godina_predmet`
  ADD CONSTRAINT `akademska_godina_predmet_ibfk_1` FOREIGN KEY (`akademska_godina`) REFERENCES `akademska_godina` (`id`),
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
-- Constraints for table `izborni_slot`
--
ALTER TABLE `izborni_slot`
  ADD CONSTRAINT `izborni_slot_ibfk_1` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`);

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
