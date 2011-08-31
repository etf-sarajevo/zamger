-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 01, 2011 at 02:37 AM
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `akademska_godina`
--

INSERT INTO `akademska_godina` (`id`, `naziv`, `aktuelna`) VALUES
(1, '2009/2010', 1);

-- --------------------------------------------------------

--
-- Table structure for table `akademska_godina_predmet`
--

CREATE TABLE IF NOT EXISTS `akademska_godina_predmet` (
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  PRIMARY KEY (`akademska_godina`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `akademska_godina_predmet`
--

INSERT INTO `akademska_godina_predmet` (`akademska_godina`, `predmet`, `tippredmeta`) VALUES
(1, 1, 1),
(1, 2, 1),
(1, 3, 1),
(1, 4, 1),
(1, 5, 1),
(1, 6, 1),
(1, 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `angazman`
--

CREATE TABLE IF NOT EXISTS `angazman` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `angazman_status` int(11) NOT NULL,
  PRIMARY KEY (`predmet`,`akademska_godina`,`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `angazman`
--

INSERT INTO `angazman` (`predmet`, `akademska_godina`, `osoba`, `angazman_status`) VALUES
(4, 1, 12, 1),
(2, 1, 12, 2),
(3, 1, 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `angazman_status`
--

CREATE TABLE IF NOT EXISTS `angazman_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

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
-- Dumping data for table `anketa_anketa`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_izbori_pitanja`
--

CREATE TABLE IF NOT EXISTS `anketa_izbori_pitanja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pitanje` int(10) unsigned NOT NULL,
  `izbor` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=26 ;

--
-- Dumping data for table `anketa_izbori_pitanja`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_rank`
--

CREATE TABLE IF NOT EXISTS `anketa_odgovor_rank` (
  `rezultat` int(10) unsigned NOT NULL,
  `pitanje` int(10) unsigned NOT NULL,
  `izbor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`rezultat`,`pitanje`,`izbor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_odgovor_rank`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_text`
--

CREATE TABLE IF NOT EXISTS `anketa_odgovor_text` (
  `rezultat` int(10) unsigned NOT NULL,
  `pitanje` int(10) unsigned NOT NULL,
  `odgovor` text COLLATE utf8_slovenian_ci,
  PRIMARY KEY (`rezultat`,`pitanje`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `anketa_odgovor_text`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_pitanje`
--

CREATE TABLE IF NOT EXISTS `anketa_pitanje` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa` int(10) unsigned NOT NULL DEFAULT '0',
  `tip_pitanja` int(10) unsigned NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=92 ;

--
-- Dumping data for table `anketa_pitanje`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_rezultat`
--

CREATE TABLE IF NOT EXISTS `anketa_rezultat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa` int(10) unsigned NOT NULL,
  `vrijeme` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `zavrsena` enum('Y','N') COLLATE utf8_slovenian_ci DEFAULT 'N',
  `predmet` int(11) DEFAULT NULL,
  `unique_id` varchar(50) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `akademska_godina` int(10) NOT NULL,
  `studij` int(10) NOT NULL,
  `semestar` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unique_id` (`unique_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=27 ;

--
-- Dumping data for table `anketa_rezultat`
--


-- --------------------------------------------------------

--
-- Table structure for table `anketa_tip_pitanja`
--

CREATE TABLE IF NOT EXISTS `anketa_tip_pitanja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tip` char(32) COLLATE utf8_slovenian_ci NOT NULL,
  `postoji_izbor` enum('Y','N') COLLATE utf8_slovenian_ci NOT NULL,
  `tabela_odgovora` char(32) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=3 ;

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

CREATE TABLE IF NOT EXISTS `auth` (
  `id` int(11) NOT NULL DEFAULT '0',
  `login` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `password` varchar(20) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `external_id` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `aktivan` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`,`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `auth`
--

INSERT INTO `auth` (`id`, `login`, `password`, `admin`, `external_id`, `aktivan`) VALUES
(1, 'admin', 'admin', 0, '', 1),
(3, 'test', '1', 0, '', 1),
(7, 'asistent', 'asistent', 0, '', 1),
(5, 'nastavnik', 'nastavnik', 0, '', 1),
(6, 'studentska', 'studentska', 0, '', 1),
(8, 'administrator', 'administrator', 0, '', 1),
(4, 'student', 'student', 0, '', 1),
(9, 'pg', '1', 0, '', 1),
(10, 'akozar', '123', 0, '', 1),
(11, 'mk15000', '123', 0, '', 1),
(12, 'zavrsni', '1', 0, '', 1),
(13, 'maja', '123', 0, '', 1),
(14, '123', '123', 0, '', 1);

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `zavrsni` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `zavrsni` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `bl_clanak`
--


-- --------------------------------------------------------

--
-- Table structure for table `cas`
--

CREATE TABLE IF NOT EXISTS `cas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `vrijeme` time NOT NULL DEFAULT '00:00:00',
  `labgrupa` int(11) NOT NULL DEFAULT '0',
  `nastavnik` int(11) NOT NULL DEFAULT '0',
  `komponenta` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=12 ;

--
-- Dumping data for table `cas`
--

INSERT INTO `cas` (`id`, `datum`, `vrijeme`, `labgrupa`, `nastavnik`, `komponenta`) VALUES
(1, '2011-08-29', '23:35:00', 2, 5, 5),
(2, '2011-08-29', '23:35:00', 2, 5, 5),
(3, '2011-08-29', '23:35:00', 2, 5, 5),
(4, '2011-08-29', '23:35:00', 2, 5, 5),
(5, '2011-08-29', '23:35:00', 2, 5, 5),
(6, '2011-08-29', '23:36:00', 4, 5, 5),
(7, '2011-08-29', '23:36:00', 4, 5, 5),
(8, '2011-08-29', '23:36:00', 4, 5, 5),
(9, '2011-08-29', '23:36:00', 5, 5, 5),
(10, '2011-08-29', '23:36:00', 5, 5, 5),
(11, '2011-08-29', '23:36:00', 5, 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `drzava`
--

CREATE TABLE IF NOT EXISTS `drzava` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=12 ;

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

CREATE TABLE IF NOT EXISTS `ekstenzije` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naziv` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=26 ;

--
-- Dumping data for table `ekstenzije`
--


-- --------------------------------------------------------

--
-- Table structure for table `institucija`
--

CREATE TABLE IF NOT EXISTS `institucija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `roditelj` int(11) NOT NULL DEFAULT '0',
  `kratki_naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

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
-- Dumping data for table `ispit`
--


-- --------------------------------------------------------

--
-- Table structure for table `ispitocjene`
--

CREATE TABLE IF NOT EXISTS `ispitocjene` (
  `ispit` int(11) NOT NULL DEFAULT '0',
  `student` int(11) NOT NULL DEFAULT '0',
  `ocjena` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ispit`,`student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;

--
-- Dumping data for table `ispit_termin`
--


-- --------------------------------------------------------

--
-- Table structure for table `izbor`
--

CREATE TABLE IF NOT EXISTS `izbor` (
  `osoba` int(11) NOT NULL,
  `zvanje` int(11) NOT NULL,
  `datum_izbora` date NOT NULL,
  `datum_isteka` date NOT NULL,
  `oblast` int(11) NOT NULL,
  `podoblast` int(11) NOT NULL,
  `dopunski` tinyint(1) NOT NULL,
  `druga_institucija` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `izbor`
--

INSERT INTO `izbor` (`osoba`, `zvanje`, `datum_izbora`, `datum_isteka`, `oblast`, `podoblast`, `dopunski`, `druga_institucija`) VALUES
(12, 1, '1969-12-31', '2999-01-01', -1, -1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `izborni_slot`
--

CREATE TABLE IF NOT EXISTS `izborni_slot` (
  `id` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY (`id`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

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
  KEY `student` (`student`,`akademska_godina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `kolizija`
--


-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

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
  PRIMARY KEY (`student`,`predmet`,`komponenta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `komponentebodovi`
--

INSERT INTO `komponentebodovi` (`student`, `predmet`, `komponenta`, `bodovi`) VALUES
(10, 3, 5, 10),
(11, 4, 5, 10),
(11, 3, 5, 10),
(11, 2, 5, 10),
(11, 5, 5, 10),
(13, 6, 5, 10),
(13, 8, 5, 10);

-- --------------------------------------------------------

--
-- Table structure for table `konacna_ocjena`
--

CREATE TABLE IF NOT EXISTS `konacna_ocjena` (
  `student` int(11) NOT NULL DEFAULT '0',
  `predmet` int(11) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `ocjena` int(3) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL,
  `odluka` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`student`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `konacna_ocjena`
--


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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=8 ;

--
-- Dumping data for table `labgrupa`
--

INSERT INTO `labgrupa` (`id`, `naziv`, `predmet`, `akademska_godina`, `virtualna`) VALUES
(1, '(Svi studenti)', 1, 1, 1),
(2, '(Svi studenti)', 2, 1, 1),
(3, '(Svi studenti)', 3, 1, 1),
(4, '(Svi studenti)', 4, 1, 1),
(5, '(Svi studenti)', 5, 1, 1),
(6, '(Svi studenti)', 6, 1, 1),
(7, '(Svi studenti)', 7, 1, 1);

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1004 ;

--
-- Dumping data for table `log`
--

INSERT INTO `log` (`id`, `vrijeme`, `userid`, `dogadjaj`, `nivo`) VALUES
(1, '2011-08-19 22:53:51', 1, 'login', 1),
(2, '2011-08-19 22:53:51', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(3, '2011-08-19 22:53:51', 1, 'common/savjet_dana', 1),
(4, '2011-08-19 22:54:39', 1, 'admin/novagodina', 1),
(5, '2011-08-19 22:54:42', 1, 'admin/novagodina akcija=novagodina godina=2009/2010', 1),
(6, '2011-08-19 22:54:55', 1, 'student/intro', 1),
(7, '2011-08-19 22:55:05', 1, 'studentska/intro', 1),
(8, '2011-08-19 22:55:44', 1, 'studentska/osobe', 1),
(9, '2011-08-19 22:55:51', 1, 'studentska/osobe offset=0 search=', 1),
(10, '2011-08-19 22:56:07', 1, 'studentska/osobe offset=0 search= akcija=novi ime=Niko prezime=Neznanovic', 1),
(11, '2011-08-19 22:56:07', 1, 'dodan novi korisnik u2 (ID: 2)', 4),
(12, '2011-08-19 22:56:16', 1, 'studentska/osobe', 1),
(13, '2011-08-19 22:56:20', 1, 'studentska/osobe', 1),
(14, '2011-08-19 22:56:25', 1, 'studentska/osobe search=sve', 1),
(15, '2011-08-19 22:56:30', 1, 'studentska/osobe search=sve akcija=edit osoba=1', 1),
(16, '2011-08-19 22:56:30', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(17, '2011-08-19 22:56:41', 1, 'studentska/osobe search=sve akcija=edit osoba=1 subakcija=auth stari_login=admin login=admin password=admin aktivan=1', 1),
(18, '2011-08-19 22:56:41', 1, 'izmijenjen login ''admin'' u ''admin'' za korisnika u1', 4),
(19, '2011-08-19 22:56:41', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(20, '2011-08-19 23:00:36', 1, '/zamger/index.php?', 1),
(21, '2011-08-19 23:02:46', 1, 'saradnik/intro', 1),
(22, '2011-08-19 23:02:48', 1, 'studentska/intro', 1),
(23, '2011-08-19 23:02:50', 1, 'studentska/osobe', 1),
(24, '2011-08-19 23:03:08', 1, 'studentska/osobe akcija=novi ime=Mujo prezime=Mujic', 1),
(25, '2011-08-19 23:03:08', 1, 'dodan novi korisnik u3 (ID: 3)', 4),
(26, '2011-08-19 23:03:10', 1, 'studentska/osobe akcija=edit osoba=3', 1),
(27, '2011-08-19 23:03:32', 1, 'studentska/osobe akcija=edit osoba=3 subakcija=auth stari_login= login=test password=1 aktivan=1', 1),
(28, '2011-08-19 23:03:32', 1, 'dodan novi login ''test'' za korisnika u3', 4),
(29, '2011-08-19 23:03:48', 1, 'studentska/osobe akcija=edit osoba=3 subakcija=uloga stari_login= login=test password=1 aktivan=1 student=1 nastavnik=1 studentska=1 siteadmin=1', 1),
(30, '2011-08-19 23:03:48', 1, 'osobi u3 data privilegija siteadmin', 4),
(31, '2011-08-19 23:03:48', 1, 'osobi u3 data privilegija studentska', 4),
(32, '2011-08-19 23:03:48', 1, 'osobi u3 data privilegija nastavnik', 4),
(33, '2011-08-19 23:03:48', 1, 'osobi u3 data privilegija student', 4),
(34, '2011-08-19 23:03:48', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(35, '2011-08-19 23:03:51', 1, 'studentska/predmeti', 1),
(36, '2011-08-19 23:03:54', 1, 'studentska/predmeti offset=0 ag=1 search=', 1),
(37, '2011-08-19 23:04:00', 1, 'studentska/predmeti offset=0 ag=1 search=', 1),
(38, '2011-08-19 23:04:05', 1, 'studentska/prijemni', 1),
(39, '2011-08-19 23:07:14', 1, 'studentska/prijemni', 1),
(40, '2011-08-20 16:58:03', 1, 'login', 1),
(41, '2011-08-20 16:58:03', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(42, '2011-08-20 16:58:04', 1, 'common/savjet_dana', 1),
(43, '2011-08-20 16:58:24', 1, 'admin/kompakt', 1),
(44, '2011-08-20 16:58:29', 1, 'admin/log', 1),
(45, '2011-08-20 16:58:35', 1, 'admin/log nivo=2 pretraga=', 1),
(46, '2011-08-20 16:58:45', 1, 'admin/studij', 1),
(47, '2011-08-20 16:58:51', 1, 'admin/novagodina', 1),
(48, '2011-08-20 16:58:54', 1, 'admin/novagodina akcija=novagodina godina=2009/2010', 1),
(49, '2011-08-20 16:59:05', 1, 'admin/novagodina akcija=novagodina godina=2009/2010 fakatradi=1', 1),
(50, '2011-08-20 16:59:15', 1, 'studentska/intro', 1),
(51, '2011-08-20 16:59:24', 1, 'studentska/osobe', 1),
(52, '2011-08-20 16:59:39', 1, 'studentska/osobe akcija=novi ime=Student prezime=Student', 1),
(53, '2011-08-20 16:59:39', 1, 'dodan novi korisnik u4 (ID: 4)', 4),
(54, '2011-08-20 16:59:45', 1, 'studentska/osobe', 1),
(55, '2011-08-20 16:59:55', 1, 'studentska/osobe akcija=novi ime=Nastavnik prezime=Nastavnik', 1),
(56, '2011-08-20 16:59:55', 1, 'dodan novi korisnik u5 (ID: 5)', 4),
(57, '2011-08-20 16:59:58', 1, 'studentska/osobe', 1),
(58, '2011-08-20 17:00:11', 1, 'studentska/osobe akcija=novi ime=Studentska prezime=Sluzba', 1),
(59, '2011-08-20 17:00:11', 1, 'dodan novi korisnik u6 (ID: 6)', 4),
(60, '2011-08-20 17:00:13', 1, 'studentska/osobe', 1),
(61, '2011-08-20 17:00:26', 1, 'studentska/osobe akcija=novi ime=Asistent prezime=Asistent', 1),
(62, '2011-08-20 17:00:26', 1, 'dodan novi korisnik u7 (ID: 7)', 4),
(63, '2011-08-20 17:00:29', 1, 'studentska/osobe', 1),
(64, '2011-08-20 17:00:39', 1, 'studentska/osobe akcija=novi ime=Administrator prezime=Stranice', 1),
(65, '2011-08-20 17:00:39', 1, 'dodan novi korisnik u8 (ID: 8)', 4),
(66, '2011-08-20 17:00:42', 1, 'studentska/osobe', 1),
(67, '2011-08-20 17:00:44', 1, 'studentska/osobe offset=0 search=', 1),
(68, '2011-08-20 17:00:52', 1, 'studentska/predmeti', 1),
(69, '2011-08-20 17:00:56', 1, 'studentska/predmeti offset=0 ag=1 search=', 1),
(70, '2011-08-20 17:01:02', 1, 'studentska/predmeti offset=0 ag=1 search= akcija=novi naziv=IM1', 1),
(71, '2011-08-20 17:01:02', 1, 'potpuno novi predmet pp1, akademska godina ag1', 4),
(72, '2011-08-20 17:01:08', 1, 'studentska/predmeti akcija=edit predmet=1 ag=1', 1),
(73, '2011-08-20 17:01:22', 1, 'studentska/predmeti predmet=1 ag=1 akcija=realedit', 1),
(74, '2011-08-20 17:01:49', 1, 'studentska/predmeti predmet=1 ag=1 akcija=realedit', 1),
(75, '2011-08-20 17:01:49', 1, 'izmijenjeni podaci o predmetu pp1', 4),
(76, '2011-08-20 17:01:52', 1, 'studentska/predmeti akcija=edit predmet=1 ag=1', 1),
(77, '2011-08-20 17:02:02', 1, 'studentska/predmeti predmet=1 ag=1 akcija=dodaj_pk', 1),
(78, '2011-08-20 17:02:26', 1, 'studentska/predmeti predmet=1 ag=1 akcija=dodaj_pk', 1),
(79, '2011-08-20 17:02:26', 1, 'dodana ponuda kursa na predmet pp1', 4),
(80, '2011-08-20 17:02:31', 1, 'studentska/predmeti', 1),
(81, '2011-08-20 17:02:39', 1, 'studentska/predmeti offset=0 ag=1 search=', 1),
(82, '2011-08-20 17:03:05', 1, 'studentska/predmeti offset=0 ag=1 search= akcija=novi naziv=Diskretna matematika', 1),
(83, '2011-08-20 17:03:05', 1, 'potpuno novi predmet pp2, akademska godina ag1', 4),
(84, '2011-08-20 17:03:07', 1, 'studentska/predmeti akcija=edit predmet=2 ag=1', 1),
(85, '2011-08-20 17:03:09', 1, 'studentska/predmeti predmet=2 ag=1 akcija=realedit', 1),
(86, '2011-08-20 17:03:19', 1, 'studentska/predmeti predmet=2 ag=1 akcija=realedit', 1),
(87, '2011-08-20 17:03:19', 1, 'izmijenjeni podaci o predmetu pp2', 4),
(88, '2011-08-20 17:03:22', 1, 'studentska/predmeti akcija=edit predmet=2 ag=1', 1),
(89, '2011-08-20 17:03:24', 1, 'studentska/predmeti predmet=2 ag=1 akcija=dodaj_pk', 1),
(90, '2011-08-20 17:03:32', 1, 'studentska/predmeti predmet=2 ag=1 akcija=dodaj_pk', 1),
(91, '2011-08-20 17:03:32', 1, 'dodana ponuda kursa na predmet pp2', 4),
(92, '2011-08-20 17:03:35', 1, 'studentska/predmeti', 1),
(93, '2011-08-20 17:03:53', 1, 'studentska/predmeti akcija=novi naziv=Elektricčni krugovi II', 1),
(94, '2011-08-20 17:03:53', 1, 'potpuno novi predmet pp3, akademska godina ag1', 4),
(95, '2011-08-20 17:03:54', 1, 'studentska/predmeti akcija=edit predmet=3 ag=1', 1),
(96, '2011-08-20 17:03:56', 1, 'studentska/predmeti predmet=3 ag=1 akcija=realedit', 1),
(97, '2011-08-20 17:04:07', 1, 'studentska/predmeti predmet=3 ag=1 akcija=realedit', 1),
(98, '2011-08-20 17:04:07', 1, 'izmijenjeni podaci o predmetu pp3', 4),
(99, '2011-08-20 17:04:10', 1, 'studentska/predmeti akcija=edit predmet=3 ag=1', 1),
(100, '2011-08-20 17:04:13', 1, 'studentska/predmeti predmet=3 ag=1 akcija=dodaj_pk', 1),
(101, '2011-08-20 17:04:22', 1, 'studentska/predmeti predmet=3 ag=1 akcija=dodaj_pk', 1),
(102, '2011-08-20 17:04:22', 1, 'dodana ponuda kursa na predmet pp3', 4),
(103, '2011-08-20 17:04:26', 1, 'studentska/predmeti akcija=edit predmet=3 ag=1', 1),
(104, '2011-08-20 17:04:39', 1, 'studentska/predmeti predmet=3 ag=1 akcija=edit subakcija=dodaj_nastavnika nastavnik=3', 1),
(105, '2011-08-20 17:04:39', 1, 'nastavnik u3 dodan na predmet pp3', 4),
(106, '2011-08-20 17:04:49', 1, 'studentska/predmeti predmet=3 ag=1 subakcija=postavi_nivo_pristupa nastavnik=3 akcija=edit nivo_pristupa=nastavnik', 1),
(107, '2011-08-20 17:04:49', 1, 'nastavnik u3 dat nivo ''nastavnik'' na predmetu pp3', 4),
(108, '2011-08-20 17:05:00', 1, 'studentska/osobe', 1),
(109, '2011-08-20 17:05:02', 1, 'studentska/osobe offset=0 search=', 1),
(110, '2011-08-20 17:05:05', 1, 'studentska/osobe offset=0 search=sve', 1),
(111, '2011-08-20 17:05:10', 1, 'studentska/osobe offset=0 search=sve akcija=edit osoba=7', 1),
(112, '2011-08-20 17:05:27', 1, 'studentska/osobe offset=0 search=sve akcija=edit osoba=7 subakcija=auth stari_login= login=asistent password=asistent aktivan=1', 1),
(113, '2011-08-20 17:05:27', 1, 'dodan novi login ''asistent'' za korisnika u7', 4),
(114, '2011-08-20 17:05:43', 1, 'studentska/osobe offset=0 search=sve akcija=edit osoba=7 subakcija=uloga stari_login= login=asistent password=asistent aktivan=1 nastavnik=1', 1),
(115, '2011-08-20 17:05:43', 1, 'osobi u7 data privilegija nastavnik', 4),
(116, '2011-08-20 17:05:43', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(117, '2011-08-20 17:05:46', 1, 'studentska/osobe', 1),
(118, '2011-08-20 17:05:49', 1, 'studentska/osobe search=sve', 1),
(119, '2011-08-20 17:05:54', 1, 'studentska/osobe search=sve akcija=edit osoba=5', 1),
(120, '2011-08-20 17:05:59', 1, 'studentska/osobe search=sve akcija=edit osoba=5 subakcija=uloga nastavnik=1', 1),
(121, '2011-08-20 17:05:59', 1, 'osobi u5 data privilegija nastavnik', 4),
(122, '2011-08-20 17:05:59', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(123, '2011-08-20 17:06:33', 1, 'studentska/osobe search=sve akcija=edit osoba=5 subakcija=auth nastavnik=1 stari_login= login=nastavnik password=nastavnik aktivan=1', 1),
(124, '2011-08-20 17:06:33', 1, 'dodan novi login ''nastavnik'' za korisnika u5', 4),
(125, '2011-08-20 17:06:33', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(126, '2011-08-20 17:06:35', 1, 'studentska/osobe', 1),
(127, '2011-08-20 17:06:37', 1, 'studentska/osobe search=sve', 1),
(128, '2011-08-20 17:06:43', 1, 'studentska/osobe search=sve akcija=edit osoba=6', 1),
(129, '2011-08-20 17:07:02', 1, 'studentska/osobe search=sve akcija=edit osoba=6 subakcija=auth stari_login= login=studentska password=studentska aktivan=1', 1),
(130, '2011-08-20 17:07:02', 1, 'dodan novi login ''studentska'' za korisnika u6', 4),
(131, '2011-08-20 17:07:07', 1, 'studentska/osobe search=sve akcija=edit osoba=6 subakcija=uloga stari_login= login=studentska password=studentska aktivan=1 studentska=1', 1),
(132, '2011-08-20 17:07:07', 1, 'osobi u6 data privilegija studentska', 4),
(133, '2011-08-20 17:07:10', 1, 'studentska/osobe', 1),
(134, '2011-08-20 17:07:12', 1, 'studentska/osobe search=sve', 1),
(135, '2011-08-20 17:07:19', 1, 'studentska/osobe search=sve akcija=edit osoba=8', 1),
(136, '2011-08-20 17:07:38', 1, 'studentska/osobe search=sve akcija=edit osoba=8 subakcija=auth stari_login= login=administrator password=administrator aktivan=1', 1),
(137, '2011-08-20 17:07:38', 1, 'dodan novi login ''administrator'' za korisnika u8', 4),
(138, '2011-08-20 17:07:42', 1, 'studentska/osobe search=sve akcija=edit osoba=8 subakcija=uloga stari_login= login=administrator password=administrator aktivan=1 siteadmin=1', 1),
(139, '2011-08-20 17:07:42', 1, 'osobi u8 data privilegija siteadmin', 4),
(140, '2011-08-20 17:07:46', 1, 'studentska/osobe', 1),
(141, '2011-08-20 17:07:47', 1, 'studentska/osobe search=sve', 1),
(142, '2011-08-20 17:07:51', 1, 'studentska/osobe search=sve akcija=edit osoba=4', 1),
(143, '2011-08-20 17:08:03', 1, 'studentska/osobe search=sve akcija=edit osoba=4 subakcija=auth stari_login= login=student password=student aktivan=1', 1),
(144, '2011-08-20 17:08:03', 1, 'dodan novi login ''student'' za korisnika u4', 4),
(145, '2011-08-20 17:08:06', 1, 'studentska/osobe search=sve akcija=edit osoba=4 subakcija=uloga stari_login= login=student password=student aktivan=1 student=1', 1),
(146, '2011-08-20 17:08:06', 1, 'osobi u4 data privilegija student', 4),
(147, '2011-08-20 17:08:11', 1, 'studentska/osobe', 1),
(148, '2011-08-20 17:08:15', 1, 'studentska/predmeti', 1),
(149, '2011-08-20 17:08:18', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(150, '2011-08-20 17:08:24', 1, 'studentska/predmeti', 1),
(151, '2011-08-20 17:08:36', 1, 'studentska/predmeti akcija=novi naziv=Elektronika', 1),
(152, '2011-08-20 17:08:36', 1, 'potpuno novi predmet pp4, akademska godina ag1', 4),
(153, '2011-08-20 17:08:38', 1, 'studentska/predmeti akcija=edit predmet=4 ag=1', 1),
(154, '2011-08-20 17:08:39', 1, 'studentska/predmeti predmet=4 ag=1 akcija=realedit', 1),
(155, '2011-08-20 17:08:47', 1, 'studentska/predmeti predmet=4 ag=1 akcija=realedit', 1),
(156, '2011-08-20 17:08:47', 1, 'izmijenjeni podaci o predmetu pp4', 4),
(157, '2011-08-20 17:08:49', 1, 'studentska/predmeti akcija=edit predmet=4 ag=1', 1),
(158, '2011-08-20 17:08:51', 1, 'studentska/predmeti predmet=4 ag=1 akcija=dodaj_pk', 1),
(159, '2011-08-20 17:08:55', 1, 'studentska/predmeti predmet=4 ag=1 akcija=dodaj_pk', 1),
(160, '2011-08-20 17:08:55', 1, 'dodana ponuda kursa na predmet pp4', 4),
(161, '2011-08-20 17:08:57', 1, 'studentska/predmeti akcija=edit predmet=4 ag=1', 1),
(162, '2011-08-20 17:09:00', 1, 'studentska/predmeti', 1),
(163, '2011-08-20 17:09:14', 1, 'studentska/predmeti akcija=novi naziv=Osnove telekomunikacija', 1),
(164, '2011-08-20 17:09:14', 1, 'potpuno novi predmet pp5, akademska godina ag1', 4),
(165, '2011-08-20 17:09:16', 1, 'studentska/predmeti akcija=edit predmet=5 ag=1', 1),
(166, '2011-08-20 17:09:19', 1, 'studentska/predmeti predmet=5 ag=1 akcija=realedit', 1),
(167, '2011-08-20 17:09:27', 1, 'studentska/predmeti predmet=5 ag=1 akcija=realedit', 1),
(168, '2011-08-20 17:09:27', 1, 'izmijenjeni podaci o predmetu pp5', 4),
(169, '2011-08-20 17:09:29', 1, 'studentska/predmeti predmet=5 ag=1 akcija=realedit', 1),
(170, '2011-08-20 17:09:29', 1, 'izmijenjeni podaci o predmetu pp5', 4),
(171, '2011-08-20 17:09:31', 1, 'studentska/predmeti akcija=edit predmet=5 ag=1', 1),
(172, '2011-08-20 17:09:33', 1, 'studentska/predmeti predmet=5 ag=1 akcija=dodaj_pk', 1),
(173, '2011-08-20 17:09:40', 1, 'studentska/predmeti predmet=5 ag=1 akcija=dodaj_pk', 1),
(174, '2011-08-20 17:09:41', 1, 'dodana ponuda kursa na predmet pp5', 4),
(175, '2011-08-20 17:09:43', 1, 'studentska/prijemni', 1),
(176, '2011-08-20 17:09:52', 1, 'studentska/raspored1', 1),
(177, '2011-08-20 17:09:56', 1, 'studentska/izvjestaji', 1),
(178, '2011-08-20 17:10:08', 1, 'studentska/obavijest', 1),
(179, '2011-08-20 17:10:13', 1, 'studentska/prodsjeka', 1),
(180, '2011-08-20 17:10:26', 1, 'studentska/anketa', 1),
(181, '2011-08-20 17:10:35', 1, 'studentska/plan', 1),
(182, '2011-08-20 17:10:39', 1, 'studentska/kreiranje_plana', 1),
(183, '2011-08-20 17:10:50', 1, 'studentska/intro', 1),
(184, '2011-08-20 17:10:51', 1, 'studentska/osobe', 1),
(185, '2011-08-20 17:10:54', 1, 'studentska/predmeti', 1),
(186, '2011-08-20 17:10:58', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(187, '2011-08-20 17:11:15', 1, 'nastavnik/predmet predmet=2 ag=1 akcija=set_smodul smodul=1 aktivan=0', 1),
(188, '2011-08-20 17:11:15', 1, 'aktiviran studentski modul 1 (predmet pp2)', 2),
(189, '2011-08-20 17:11:26', 1, 'nastavnik/predmet predmet=2 ag=1 akcija=set_smodul smodul=2 aktivan=0', 1),
(190, '2011-08-20 17:11:26', 1, 'aktiviran studentski modul 2 (predmet pp2)', 2),
(191, '2011-08-20 17:11:28', 1, 'nastavnik/predmet predmet=2 ag=1 akcija=set_smodul smodul=3 aktivan=0', 1),
(192, '2011-08-20 17:11:28', 1, 'aktiviran studentski modul 3 (predmet pp2)', 2),
(193, '2011-08-20 17:11:31', 1, 'nastavnik/predmet predmet=2 ag=1 akcija=set_smodul smodul=4 aktivan=0', 1),
(194, '2011-08-20 17:11:31', 1, 'aktiviran studentski modul 4 (predmet pp2)', 2),
(195, '2011-08-20 17:11:36', 1, 'nastavnik/predmet predmet=2 ag=1 akcija=set_smodul smodul=5 aktivan=0', 1),
(196, '2011-08-20 17:11:36', 1, 'aktiviran studentski modul 5 (predmet pp2)', 2),
(197, '2011-08-20 17:11:36', 1, 'nastavnik/obavjestenja predmet=2 ag=1', 1),
(198, '2011-08-20 17:11:44', 1, 'nastavnik/grupe predmet=2 ag=1', 1),
(199, '2011-08-20 17:12:11', 1, 'nastavnik/ispiti predmet=2 ag=1', 1),
(200, '2011-08-20 17:12:20', 1, 'nastavnik/zadace predmet=2 ag=1', 1),
(201, '2011-08-20 17:12:31', 1, 'nastavnik/ocjena predmet=2 ag=1', 1),
(202, '2011-08-20 17:12:41', 1, 'nastavnik/izvjestaji predmet=2 ag=1', 1),
(203, '2011-08-20 17:12:48', 1, 'nastavnik/raspored predmet=2 ag=1', 1),
(204, '2011-08-20 17:12:50', 1, 'nastavnik/tip predmet=2 ag=1', 1),
(205, '2011-08-20 17:12:59', 1, 'nastavnik/projekti predmet=2 ag=1', 1),
(206, '2011-08-20 17:13:39', 1, 'saradnik/intro', 1),
(207, '2011-08-20 17:13:47', 1, 'saradnik/grupa id=5', 1),
(208, '2011-08-20 17:13:50', 1, 'saradnik/intro', 1),
(209, '2011-08-20 17:14:00', 1, 'student/intro', 1),
(210, '2011-08-20 17:14:04', 1, 'logout', 1),
(211, '2011-08-20 17:14:10', 4, 'login', 1),
(212, '2011-08-20 17:14:10', 4, '/zamger/index.php?loginforma=1 login=student', 1),
(213, '2011-08-20 17:14:23', 4, 'izvjestaj/index student=4', 1),
(214, '2011-08-20 17:14:36', 4, '/zamger/index.php?', 1),
(215, '2011-08-20 17:14:51', 4, 'logout', 1),
(216, '2011-08-20 17:15:04', 5, 'login', 1),
(217, '2011-08-20 17:15:04', 5, '/zamger/index.php?loginforma=1 login=nastavnik', 1),
(218, '2011-08-20 17:15:04', 5, 'common/savjet_dana', 1),
(219, '2011-08-20 17:15:20', 5, '/zamger/index.php?loginforma=1 login=nastavnik sve=1', 1),
(220, '2011-08-20 17:15:26', 5, 'logout', 1),
(221, '2011-08-20 17:15:32', 3, 'login', 1),
(222, '2011-08-20 17:15:32', 3, '/zamger/index.php?loginforma=1 login=test', 1),
(223, '2011-08-20 17:15:32', 3, 'common/savjet_dana', 1),
(224, '2011-08-20 17:15:43', 3, 'logout', 1),
(225, '2011-08-20 17:15:48', 7, 'login', 1),
(226, '2011-08-20 17:15:48', 7, '/zamger/index.php?loginforma=1 login=asistent', 1),
(227, '2011-08-20 17:15:48', 7, 'common/savjet_dana', 1),
(228, '2011-08-20 17:15:58', 7, '/zamger/index.php?loginforma=1 login=asistent sve=1', 1),
(229, '2011-08-20 17:16:03', 7, 'logout', 1),
(230, '2011-08-20 17:16:14', 8, 'login', 1),
(231, '2011-08-20 17:16:14', 8, '/zamger/index.php?loginforma=1 login=administrator', 1),
(232, '2011-08-20 17:16:42', 8, 'admin/intro', 1),
(233, '2011-08-20 17:16:43', 8, 'admin/intro grupe=1', 1),
(234, '2011-08-20 17:16:45', 8, 'admin/intro', 1),
(235, '2011-08-20 17:16:46', 8, 'admin/intro grupe=1', 1),
(236, '2011-08-20 17:16:47', 8, 'admin/intro', 1),
(237, '2011-08-20 17:16:48', 8, 'admin/intro grupe=1', 1),
(238, '2011-08-20 17:16:49', 8, 'admin/intro', 1),
(239, '2011-08-20 17:16:57', 8, 'admin/kompakt', 1),
(240, '2011-08-20 17:16:58', 8, 'admin/log', 1),
(241, '2011-08-20 17:17:11', 8, 'admin/studij', 1),
(242, '2011-08-20 17:17:13', 8, 'admin/log', 1),
(243, '2011-08-20 17:17:17', 8, 'admin/log nivo=4', 1),
(244, '2011-08-20 17:17:17', 8, 'admin/log nivo=2 pretraga=', 1),
(245, '2011-08-20 17:17:23', 8, 'admin/studij', 1),
(246, '2011-08-20 17:17:26', 8, 'admin/studij akcija=inst', 1),
(247, '2011-08-20 17:17:32', 8, 'admin/studij akcija=kanton', 1),
(248, '2011-08-20 17:17:35', 8, 'admin/studij akcija=komponenta', 1),
(249, '2011-08-20 17:17:41', 8, 'admin/studij akcija=studij', 1),
(250, '2011-08-20 17:17:43', 8, 'admin/studij akcija=tippr', 1),
(251, '2011-08-20 17:17:48', 8, 'admin/studij akcija=ag', 1),
(252, '2011-08-20 17:17:50', 8, 'admin/novagodina', 1),
(253, '2011-08-20 17:17:58', 8, 'logout', 1),
(254, '2011-08-20 17:18:30', 6, 'login', 1),
(255, '2011-08-20 17:18:30', 6, '/zamger/index.php?loginforma=1 login=studentska', 1),
(256, '2011-08-20 17:18:38', 6, 'common/profil', 1),
(257, '2011-08-20 17:18:46', 6, 'studentska/intro', 1),
(258, '2011-08-20 17:18:53', 6, 'studentska/osobe', 1),
(259, '2011-08-20 17:18:58', 6, 'studentska/predmeti', 1),
(260, '2011-08-20 17:19:02', 6, 'studentska/predmeti akcija=edit predmet=2 ag=1', 1),
(261, '2011-08-20 17:19:15', 6, 'studentska/predmeti predmet=2 ag=1 akcija=edit subakcija=dodaj_nastavnika nastavnik=7', 1),
(262, '2011-08-20 17:19:15', 6, 'nastavnik u7 dodan na predmet pp2', 4),
(263, '2011-08-20 17:19:26', 6, 'studentska/predmeti predmet=2 ag=1 subakcija=postavi_nivo_pristupa nastavnik=7 akcija=edit nivo_pristupa=super_asistent', 1),
(264, '2011-08-20 17:19:26', 6, 'nastavnik u7 dat nivo ''super_asistent'' na predmetu pp2', 4),
(265, '2011-08-20 17:19:29', 6, 'studentska/predmeti predmet=2 ag=1 subakcija=postavi_nivo_pristupa nastavnik=7 nivo_pristupa=super_asistent akcija=ogranicenja', 1),
(266, '2011-08-20 17:19:32', 6, 'studentska/predmeti akcija=edit predmet=2 ag=1', 1),
(267, '2011-08-20 17:19:38', 6, 'studentska/predmeti predmet=2 ag=1 akcija=edit subakcija=dodaj_nastavnika nastavnik=5', 1),
(268, '2011-08-20 17:19:38', 6, 'nastavnik u5 dodan na predmet pp2', 4),
(269, '2011-08-20 17:19:43', 6, 'studentska/predmeti predmet=2 ag=1 subakcija=postavi_nivo_pristupa nastavnik=5 akcija=edit nivo_pristupa=nastavnik', 1),
(270, '2011-08-20 17:19:43', 6, 'nastavnik u5 dat nivo ''nastavnik'' na predmetu pp2', 4),
(271, '2011-08-20 17:19:48', 6, 'studentska/predmeti', 1),
(272, '2011-08-20 17:19:51', 6, 'studentska/predmeti akcija=edit predmet=3 ag=1', 1),
(273, '2011-08-20 17:19:59', 6, 'studentska/predmeti predmet=3 ag=1 akcija=edit subakcija=dodaj_nastavnika nastavnik=7', 1),
(274, '2011-08-20 17:19:59', 6, 'nastavnik u7 dodan na predmet pp3', 4),
(275, '2011-08-20 17:20:10', 6, 'izvjestaj/grupe predmet=3 ag=1', 1),
(276, '2011-08-20 17:20:19', 6, 'studentska/predmeti akcija=edit predmet=3 ag=1', 1),
(277, '2011-08-20 17:20:27', 6, 'studentska/predmeti', 1),
(278, '2011-08-20 17:20:31', 6, 'studentska/predmeti akcija=edit predmet=4 ag=1', 1),
(279, '2011-08-20 17:20:40', 6, 'studentska/predmeti predmet=4 ag=1 akcija=edit subakcija=dodaj_nastavnika nastavnik=3', 1),
(280, '2011-08-20 17:20:40', 6, 'nastavnik u3 dodan na predmet pp4', 4),
(281, '2011-08-20 17:20:47', 6, 'studentska/predmeti predmet=4 ag=1 subakcija=dodaj_nastavnika nastavnik=5 akcija=edit', 1),
(282, '2011-08-20 17:20:47', 6, 'nastavnik u5 dodan na predmet pp4', 4),
(283, '2011-08-20 17:20:53', 6, 'studentska/predmeti predmet=4 ag=1 subakcija=postavi_nivo_pristupa nastavnik=3 akcija=edit nivo_pristupa=super_asistent', 1),
(284, '2011-08-20 17:20:53', 6, 'nastavnik u3 dat nivo ''super_asistent'' na predmetu pp4', 4),
(285, '2011-08-20 17:20:56', 6, 'studentska/predmeti predmet=4 ag=1 subakcija=postavi_nivo_pristupa nastavnik=5 nivo_pristupa=asistent akcija=edit', 1),
(286, '2011-08-20 17:20:56', 6, 'nastavnik u5 dat nivo ''asistent'' na predmetu pp4', 4),
(287, '2011-08-20 17:21:00', 6, 'studentska/predmeti predmet=4 ag=1 subakcija=postavi_nivo_pristupa nastavnik=5 nivo_pristupa=nastavnik akcija=edit', 1),
(288, '2011-08-20 17:21:00', 6, 'nastavnik u5 dat nivo ''nastavnik'' na predmetu pp4', 4),
(289, '2011-08-20 17:21:07', 6, 'studentska/predmeti', 1),
(290, '2011-08-20 17:21:10', 6, 'studentska/predmeti akcija=edit predmet=2 ag=1', 1),
(291, '2011-08-20 17:21:15', 6, 'studentska/predmeti', 1),
(292, '2011-08-20 17:21:16', 6, 'studentska/predmeti akcija=edit predmet=3 ag=1', 1),
(293, '2011-08-20 17:21:21', 6, 'studentska/predmeti', 1),
(294, '2011-08-20 17:21:23', 6, 'studentska/predmeti akcija=edit predmet=1 ag=1', 1),
(295, '2011-08-20 17:21:29', 6, 'studentska/predmeti predmet=1 ag=1 akcija=edit subakcija=dodaj_nastavnika nastavnik=7', 1),
(296, '2011-08-20 17:21:29', 6, 'nastavnik u7 dodan na predmet pp1', 4),
(297, '2011-08-20 17:21:32', 6, 'studentska/predmeti predmet=1 ag=1 subakcija=postavi_nivo_pristupa nastavnik=7 akcija=edit nivo_pristupa=asistent', 1),
(298, '2011-08-20 17:21:32', 6, 'nastavnik u7 dat nivo ''asistent'' na predmetu pp1', 4),
(299, '2011-08-20 17:21:36', 6, 'studentska/predmeti predmet=1 ag=1 subakcija=dodaj_nastavnika nastavnik=3 nivo_pristupa=asistent akcija=edit', 1),
(300, '2011-08-20 17:21:36', 6, 'nastavnik u3 dodan na predmet pp1', 4),
(301, '2011-08-20 17:21:40', 6, 'studentska/predmeti predmet=1 ag=1 subakcija=postavi_nivo_pristupa nastavnik=3 nivo_pristupa=nastavnik akcija=edit', 1),
(302, '2011-08-20 17:21:40', 6, 'nastavnik u3 dat nivo ''nastavnik'' na predmetu pp1', 4),
(303, '2011-08-20 17:21:43', 6, 'studentska/predmeti', 1),
(304, '2011-08-20 17:21:45', 6, 'studentska/predmeti akcija=edit predmet=5 ag=1', 1),
(305, '2011-08-20 17:21:49', 6, 'studentska/predmeti predmet=5 ag=1 akcija=edit subakcija=dodaj_nastavnika nastavnik=3', 1),
(306, '2011-08-20 17:21:49', 6, 'nastavnik u3 dodan na predmet pp5', 4),
(307, '2011-08-20 17:21:52', 6, 'studentska/predmeti predmet=5 ag=1 subakcija=postavi_nivo_pristupa nastavnik=3 akcija=edit nivo_pristupa=asistent', 1),
(308, '2011-08-20 17:21:52', 6, 'nastavnik u3 dat nivo ''asistent'' na predmetu pp5', 4),
(309, '2011-08-20 17:21:57', 6, 'studentska/predmeti predmet=5 ag=1 subakcija=dodaj_nastavnika nastavnik=5 nivo_pristupa=asistent akcija=edit', 1),
(310, '2011-08-20 17:21:57', 6, 'nastavnik u5 dodan na predmet pp5', 4),
(311, '2011-08-20 17:22:02', 6, 'studentska/predmeti predmet=5 ag=1 subakcija=postavi_nivo_pristupa nastavnik=5 nivo_pristupa=nastavnik akcija=edit', 1),
(312, '2011-08-20 17:22:02', 6, 'nastavnik u5 dat nivo ''nastavnik'' na predmetu pp5', 4),
(313, '2011-08-20 17:22:04', 6, 'studentska/predmeti', 1),
(314, '2011-08-20 17:22:08', 6, 'studentska/prijemni', 1),
(315, '2011-08-20 17:22:14', 6, 'studentska/raspored1', 1),
(316, '2011-08-20 17:22:31', 6, 'studentska/raspored1 akcija=kopiraj_raspored izvor=1 odrediste=1', 1),
(317, '2011-08-20 17:22:35', 6, 'studentska/izvjestaji', 1),
(318, '2011-08-20 17:22:51', 6, 'studentska/obavijest', 1),
(319, '2011-08-20 17:22:55', 6, 'studentska/prodsjeka', 1),
(320, '2011-08-20 17:22:59', 6, 'studentska/anketa', 1),
(321, '2011-08-20 17:23:03', 6, 'studentska/plan', 1),
(322, '2011-08-20 17:23:06', 6, 'studentska/kreiranje_plana', 1),
(323, '2011-08-20 17:23:20', 6, 'studentska/kreiranje_plana godina_vazenja=1 studij=1 semestar=1 predmet=1 obavezan=on create=Potvrdi', 1),
(324, '2011-08-20 17:23:24', 6, 'studentska/kreiranje_plana', 1),
(325, '2011-08-20 17:23:35', 6, 'studentska/kreiranje_plana godina_vazenja=1 studij=2 semestar=3 predmet=2 obavezan=on create=Potvrdi', 1),
(326, '2011-08-20 17:23:37', 6, 'studentska/kreiranje_plana', 1),
(327, '2011-08-20 17:23:49', 6, 'studentska/kreiranje_plana godina_vazenja=1 studij=4 semestar=3 predmet=3 obavezan=on create=Potvrdi', 1),
(328, '2011-08-20 17:23:50', 6, 'studentska/kreiranje_plana', 1),
(329, '2011-08-20 17:24:01', 6, 'studentska/kreiranje_plana godina_vazenja=1 studij=3 semestar=4 predmet=4 obavezan=on create=Potvrdi', 1),
(330, '2011-08-20 17:24:04', 6, 'studentska/kreiranje_plana', 1),
(331, '2011-08-20 17:24:13', 6, 'studentska/kreiranje_plana godina_vazenja=1 studij=5 semestar=6 predmet=5 obavezan=on create=Potvrdi', 1),
(332, '2011-08-20 17:24:14', 6, 'studentska/plan', 1),
(333, '2011-08-20 17:24:21', 6, 'studentska/raspored1', 1),
(334, '2011-08-20 17:24:25', 6, 'studentska/raspored1 akcija=unos_novog_rasporeda akademska_godina=1 studij=1 semestar=1', 1),
(335, '2011-08-20 17:24:25', 6, 'Unesen novi raspored', 2),
(336, '2011-08-20 17:24:31', 6, 'studentska/raspored1 raspored_za_edit=1', 1),
(337, '2011-08-20 17:24:41', 6, 'studentska/raspored1 raspored_za_edit=1 akcija=unos_novog_casa_predfaza cas_sa_konfliktima=0 dan=2 tip=P predmet=0 sala=0 pocetakSat=-1 pocetakMin=0 krajSat=-1 krajMin=0', 1),
(338, '2011-08-20 17:24:45', 6, 'studentska/raspored1 raspored_za_edit=1 akcija=unos_novog_casa_predfaza cas_sa_konfliktima=0 dan=2 tip=P predmet=1 sala=0 pocetakSat=-1 pocetakMin=0 krajSat=-1 krajMin=0', 1),
(339, '2011-08-20 17:25:14', 6, 'studentska/raspored1 raspored_za_edit=1 akcija=unos_novog_casa cas_sa_konfliktima=0 dan=2 tip=P predmet=1 sala=0 pocetakSat=1 pocetakMin=1 krajSat=1 krajMin=4', 1),
(340, '2011-08-20 17:25:49', 6, 'studentska/osobe', 1),
(341, '2011-08-20 17:25:52', 6, 'studentska/predmeti', 1),
(342, '2011-08-20 17:25:57', 6, 'logout', 1),
(343, '2011-08-20 17:26:04', 5, 'login', 1),
(344, '2011-08-20 17:26:04', 5, '/zamger/index.php?loginforma=1 login=nastavnik', 1),
(345, '2011-08-20 17:26:04', 5, 'common/savjet_dana', 1),
(346, '2011-08-20 17:26:12', 5, 'saradnik/grupa id=2', 1),
(347, '2011-08-20 17:26:15', 5, 'saradnik/grupa id=2', 1),
(348, '2011-08-20 17:26:17', 5, 'saradnik/intro', 1),
(349, '2011-08-20 17:26:19', 5, 'saradnik/grupa id=4', 1),
(350, '2011-08-20 17:26:21', 5, 'saradnik/intro', 1),
(351, '2011-08-20 17:26:22', 5, 'saradnik/grupa id=5', 1),
(352, '2011-08-20 17:26:23', 5, 'saradnik/intro', 1),
(353, '2011-08-20 17:26:33', 5, 'logout', 1),
(354, '2011-08-20 17:26:53', 3, 'login', 1),
(355, '2011-08-20 17:26:53', 3, '/zamger/index.php?loginforma=1 login=test', 1),
(356, '2011-08-20 17:26:54', 3, 'common/savjet_dana', 1),
(357, '2011-08-20 17:27:01', 3, 'saradnik/intro', 1),
(358, '2011-08-20 17:27:11', 3, 'studentska/intro', 1),
(359, '2011-08-20 17:27:12', 3, 'studentska/predmeti', 1),
(360, '2011-08-20 17:27:17', 3, 'nastavnik/predmet predmet=2 ag=1', 1),
(361, '2011-08-20 17:27:28', 3, 'nastavnik/projekti predmet=2 ag=1', 1),
(362, '2011-08-20 17:27:29', 3, 'nastavnik/projekti predmet=2 ag=1 akcija=param', 1),
(363, '2011-08-20 17:27:46', 3, 'nastavnik/projekti predmet=2 ag=1 akcija=dodaj_projekat', 1),
(364, '2011-08-20 17:27:50', 3, 'nastavnik/projekti predmet=2 ag=1', 1),
(365, '2011-08-20 17:27:56', 3, 'logout', 1),
(366, '2011-08-23 15:11:48', 1, 'login', 1),
(367, '2011-08-23 15:11:48', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(368, '2011-08-23 15:11:49', 1, 'common/savjet_dana', 1),
(369, '2011-08-23 16:39:58', 1, 'studentska/intro', 1),
(370, '2011-08-23 16:40:00', 1, 'studentska/osobe', 1),
(371, '2011-08-23 16:40:18', 1, 'studentska/osobe akcija=novi ime=Prva prezime=Godina', 1),
(372, '2011-08-23 16:40:18', 1, 'dodan novi korisnik u9 (ID: 9)', 4),
(373, '2011-08-23 16:40:20', 1, 'studentska/osobe akcija=edit osoba=9', 1),
(374, '2011-08-23 16:40:24', 1, 'studentska/osobe akcija=podaci osoba=9', 1),
(375, '2011-08-23 16:44:02', 1, 'studentska/osobe akcija=podaci osoba=9 subakcija=potvrda ime=Prva prezime=Godina spol=M jmbg=1506988197856 nacionalnost=Nepoznato / Nije se izjasnio/la brindexa=15000 imeoca=Otac prezimeoca=Nesto imemajke=Majka prezimemajke=Nesto datum_rodjenja=03.02.1992', 1),
(376, '2011-08-23 16:44:02', 1, 'promijenjeni licni podaci korisnika u9', 4),
(377, '2011-08-23 16:44:02', 1, 'studentska/osobe osoba=9 akcija=edit', 1),
(378, '2011-08-23 16:44:16', 1, 'studentska/osobe osoba=9 akcija=edit subakcija=auth stari_login= login=pg password=1 aktivan=1', 1),
(379, '2011-08-23 16:44:16', 1, 'dodan novi login ''pg'' za korisnika u9', 4),
(380, '2011-08-23 16:44:21', 1, 'studentska/osobe osoba=9 akcija=edit subakcija=uloga stari_login= login=pg password=1 aktivan=1 student=1', 1),
(381, '2011-08-23 16:44:21', 1, 'osobi u9 data privilegija student', 4),
(382, '2011-08-23 16:44:29', 1, 'studentska/osobe osoba=9 akcija=upis studij= semestar=1 godina=1', 1),
(383, '2011-08-23 16:44:50', 1, 'studentska/osobe osoba=9 akcija=upis studij= semestar=1 godina=1 subakcija=upis_potvrda novi_studij=3 nacin_studiranja=1 novi_brindexa=15865', 1),
(384, '2011-08-23 16:44:55', 1, 'studentska/osobe osoba=9 akcija=upis studij=3 semestar=1 godina=1 subakcija=upis_potvrda novi_studij=0 nacin_studiranja=1 novi_brindexa=15865', 1),
(385, '2011-08-23 16:44:55', 1, 'Student u9 upisan na studij s3, semestar 1, godina ag1', 4),
(386, '2011-08-23 16:45:01', 1, 'studentska/osobe akcija=edit osoba=9', 1),
(387, '2011-08-23 16:45:08', 1, 'studentska/osobe osoba=9 akcija=predmeti', 1),
(388, '2011-08-23 16:45:13', 1, 'studentska/osobe osoba=9 akcija=predmeti spisak=0', 1),
(389, '2011-08-23 16:45:17', 1, 'studentska/osobe osoba=9 akcija=predmeti spisak=0', 1),
(390, '2011-08-23 16:45:26', 1, 'studentska/osobe osoba=9 akcija=edit', 1),
(391, '2011-08-23 16:45:31', 1, 'izvjestaj/index student=9', 1),
(392, '2011-08-23 16:45:35', 1, 'studentska/osobe osoba=9 akcija=edit', 1),
(393, '2011-08-23 16:45:40', 1, 'studentska/osobe search= offset=', 1),
(394, '2011-08-23 16:45:46', 1, 'studentska/osobe search= offset= akcija=novi ime=Arnela prezime=Kozar', 1),
(395, '2011-08-23 16:45:46', 1, 'dodan novi korisnik u10 (ID: 10)', 4),
(396, '2011-08-23 16:45:49', 1, 'studentska/osobe akcija=edit osoba=10', 1),
(397, '2011-08-23 16:45:52', 1, 'studentska/osobe akcija=podaci osoba=10', 1),
(398, '2011-08-23 16:47:51', 1, 'studentska/osobe akcija=podaci osoba=10 subakcija=potvrda ime=Arnela prezime=Kozar spol=Z jmbg=0602992198067 nacionalnost=Bošnjak/Bošnjakinja brindexa=15856 imeoca=Senad prezimeoca=Kozar imemajke=Borka prezimemajke=Kozar datum_rodjenja=03.02.1992. mjesto_', 1),
(399, '2011-08-23 16:47:51', 1, 'promijenjeni licni podaci korisnika u10', 4),
(400, '2011-08-23 16:47:51', 1, 'studentska/osobe osoba=10 akcija=edit', 1),
(401, '2011-08-23 16:48:05', 1, 'studentska/osobe osoba=10 akcija=edit subakcija=auth stari_login= login=akozar password=123 aktivan=1', 1),
(402, '2011-08-23 16:48:05', 1, 'dodan novi login ''akozar'' za korisnika u10', 4),
(403, '2011-08-23 16:48:13', 1, 'studentska/osobe osoba=10 akcija=edit subakcija=uloga stari_login= login=akozar password=123 aktivan=1 student=1', 1),
(404, '2011-08-23 16:48:13', 1, 'osobi u10 data privilegija student', 4),
(405, '2011-08-23 16:48:18', 1, 'studentska/osobe osoba=10 akcija=predmeti', 1),
(406, '2011-08-23 16:48:34', 1, 'studentska/osobe osoba=10 akcija=predmeti spisak=1', 1),
(407, '2011-08-23 16:48:46', 1, 'studentska/osobe akcija=predmeti osoba=10 subakcija=upisi ponudakursa=3 spisak=2', 1),
(408, '2011-08-23 16:48:46', 1, 'student u10 manuelno upisan na predmet p3', 4),
(409, '2011-08-23 16:48:55', 1, 'studentska/osobe akcija=predmeti osoba=10 ponudakursa=3 spisak=0', 1),
(410, '2011-08-23 16:49:02', 1, 'studentska/osobe osoba=10 akcija=edit', 1),
(411, '2011-08-23 16:49:07', 1, 'studentska/osobe osoba=10 akcija=upis studij= semestar=1 godina=1', 1),
(412, '2011-08-23 16:49:13', 1, 'studentska/osobe osoba=10 akcija=upis studij= semestar=1 godina=1 subakcija=upis_potvrda novi_studij=4 nacin_studiranja=1 novi_brindexa=15856', 1),
(413, '2011-08-23 16:49:17', 1, 'studentska/osobe osoba=10 akcija=upis studij=4 semestar=1 godina=1 subakcija=upis_potvrda novi_studij=0 nacin_studiranja=1 novi_brindexa=15856', 1),
(414, '2011-08-23 16:49:17', 1, 'Student u10 upisan na studij s4, semestar 1, godina ag1', 4),
(415, '2011-08-23 16:49:25', 1, 'studentska/osobe akcija=edit osoba=10', 1),
(416, '2011-08-23 16:49:32', 1, 'izvjestaj/progress student=10 razdvoji_ispite=0', 1),
(417, '2011-08-23 16:49:41', 1, 'studentska/osobe akcija=edit osoba=10', 1),
(418, '2011-08-23 16:49:55', 1, 'studentska/osobe search= offset=', 1),
(419, '2011-08-23 16:50:03', 1, 'studentska/osobe search= offset= akcija=novi ime=Maja  prezime=Kozar', 1),
(420, '2011-08-23 16:50:03', 1, 'dodan novi korisnik u11 (ID: 11)', 4),
(421, '2011-08-23 16:50:05', 1, 'studentska/osobe akcija=edit osoba=11', 1),
(422, '2011-08-23 16:50:08', 1, 'studentska/osobe akcija=podaci osoba=11', 1),
(423, '2011-08-23 16:51:57', 1, 'studentska/osobe akcija=podaci osoba=11 subakcija=potvrda ime=Maja  prezime=Kozar spol=Z jmbg=1506988198069 nacionalnost=Bošnjak/Bošnjakinja brindexa=15000 imeoca=Senad prezimeoca=Kozar imemajke=Borka prezimemajke=Kozar datum_rodjenja=15.06.1988. mjesto_r', 1),
(424, '2011-08-23 16:51:57', 1, 'promijenjeni licni podaci korisnika u11', 4),
(425, '2011-08-23 16:51:58', 1, 'studentska/osobe osoba=11 akcija=edit', 1),
(426, '2011-08-23 16:52:10', 1, 'studentska/osobe osoba=11 akcija=edit subakcija=auth stari_login= login=mk15000 password=123 aktivan=1', 1),
(427, '2011-08-23 16:52:10', 1, 'dodan novi login ''mk15000'' za korisnika u11', 4),
(428, '2011-08-23 16:52:15', 1, 'studentska/osobe osoba=11 akcija=edit subakcija=uloga stari_login= login=mk15000 password=123 aktivan=1 student=1', 1),
(429, '2011-08-23 16:52:15', 1, 'osobi u11 data privilegija student', 4),
(430, '2011-08-23 16:52:19', 1, 'studentska/osobe osoba=11 akcija=predmeti', 1),
(431, '2011-08-23 16:52:22', 1, 'studentska/osobe akcija=predmeti osoba=11 subakcija=upisi ponudakursa=4 spisak=2', 1),
(432, '2011-08-23 16:52:22', 1, 'student u11 manuelno upisan na predmet p4', 4),
(433, '2011-08-23 16:52:25', 1, 'studentska/osobe akcija=predmeti osoba=11 subakcija=upisi ponudakursa=3 spisak=2', 1),
(434, '2011-08-23 16:52:25', 1, 'student u11 manuelno upisan na predmet p3', 4),
(435, '2011-08-23 16:52:28', 1, 'studentska/osobe akcija=predmeti osoba=11 subakcija=upisi ponudakursa=2 spisak=2', 1),
(436, '2011-08-23 16:52:28', 1, 'student u11 manuelno upisan na predmet p2', 4),
(437, '2011-08-23 16:52:32', 1, 'studentska/osobe akcija=predmeti osoba=11 subakcija=upisi ponudakursa=5 spisak=2', 1),
(438, '2011-08-23 16:52:32', 1, 'student u11 manuelno upisan na predmet p5', 4),
(439, '2011-08-23 16:52:36', 1, 'studentska/osobe akcija=predmeti osoba=11 ponudakursa=5 spisak=2', 1),
(440, '2011-08-23 16:52:41', 1, 'studentska/osobe osoba=11 akcija=edit', 1),
(441, '2011-08-23 16:52:46', 1, 'izvjestaj/progress student=11 razdvoji_ispite=1', 1),
(442, '2011-08-23 16:52:57', 1, 'izvjestaj/csv_converter koji_izvjestaj=izvjestaj/progress student=11 razdvoji_ispite=1', 1),
(443, '2011-08-23 16:53:08', 1, 'studentska/osobe osoba=11 akcija=edit', 1),
(444, '2011-08-23 16:53:11', 1, 'studentska/osobe search= offset=', 1),
(445, '2011-08-23 16:53:15', 1, 'studentska/predmeti', 1),
(446, '2011-08-23 16:53:19', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(447, '2011-08-23 16:53:26', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(448, '2011-08-23 16:53:26', 1, 'SQL greska (astavnik\\zavrsni.php : 34):Unknown column ''zakljucani_zavrsni'' in ''field list''', 3),
(449, '2011-08-23 19:10:27', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(450, '2011-08-23 19:10:29', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(451, '2011-08-23 19:10:29', 1, 'SQL greska (astavnik\\zavrsni.php : 34):Unknown column ''zakljucani_zavrsni'' in ''field list''', 3),
(452, '2011-08-23 19:14:20', 1, '/zamger/index.php?', 1),
(453, '2011-08-23 19:14:24', 1, 'studentska/intro', 1),
(454, '2011-08-23 19:14:25', 1, 'studentska/predmeti', 1),
(455, '2011-08-23 19:14:27', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(456, '2011-08-23 19:14:31', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(457, '2011-08-23 19:14:31', 1, 'SQL greska (astavnik\\zavrsni.php : 65):Unknown column ''naziv'' in ''field list''', 3),
(458, '2011-08-23 19:15:49', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(459, '2011-08-23 19:15:53', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(460, '2011-08-23 19:16:11', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_timova=0 max_timova=5', 1),
(461, '2011-08-23 19:16:14', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(462, '2011-08-23 19:16:22', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda lock=on min_timova=1 max_timova=5', 1),
(463, '2011-08-23 19:16:24', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(464, '2011-08-23 19:16:38', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_timova=5 max_timova=0', 1),
(465, '2011-08-23 19:16:42', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(466, '2011-08-23 19:16:46', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(467, '2011-08-23 19:16:47', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(468, '2011-08-23 19:16:52', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodjela_studenata', 1),
(469, '2011-08-23 19:16:52', 1, 'SQL greska (astavnik\\zavrsni.php : 529):Unknown column ''sp.predmet'' in ''where clause''', 3),
(470, '2011-08-23 19:18:18', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(471, '2011-08-23 19:18:22', 1, 'nastavnik/projekti predmet=2 ag=1', 1),
(472, '2011-08-23 19:18:24', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=param', 1),
(473, '2011-08-23 19:18:37', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=param subakcija=potvrda min_timova=0 max_timova=10 min_clanova_tima=1 max_clanova_tima=5', 1),
(474, '2011-08-23 19:18:40', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=param', 1),
(475, '2011-08-23 19:18:57', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=param subakcija=potvrda lock=on min_timova=1 max_timova=11 min_clanova_tima=2 max_clanova_tima=5', 1),
(476, '2011-08-23 19:18:57', 1, 'izmijenio parametre projekata na predmetu pp2', 2),
(477, '2011-08-23 19:19:00', 1, 'nastavnik/projekti predmet=2 ag=1', 1),
(478, '2011-08-23 19:19:02', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=dodaj_projekat', 1),
(479, '2011-08-23 19:19:10', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=dodjela_studenata', 1),
(480, '2011-08-23 19:19:16', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=dodjela_studenata subakcija=dodaj student=11 dodaj=Upiši', 1),
(481, '2011-08-23 19:19:18', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=dodjela_studenata', 1),
(482, '2011-08-23 19:19:22', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=param', 1),
(483, '2011-08-23 19:19:25', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=param subakcija=potvrda min_timova=1 max_timova=11 min_clanova_tima=2 max_clanova_tima=5', 1),
(484, '2011-08-23 19:19:25', 1, 'izmijenio parametre projekata na predmetu pp2', 2),
(485, '2011-08-23 19:19:29', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=dodaj_projekat', 1),
(486, '2011-08-23 19:19:39', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=dodaj_projekat subakcija=potvrda naziv=Nesto opis=Cccccccccccccccccccccccc', 1),
(487, '2011-08-23 19:19:39', 1, 'dodao novi projekat na predmetu pp2', 2),
(488, '2011-08-23 19:19:41', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=dodjela_studenata', 1),
(489, '2011-08-23 19:19:44', 1, 'nastavnik/projekti predmet=2 ag=1 akcija=dodjela_studenata subakcija=dodaj student=11 projekat=1 dodaj=Upiši', 1),
(490, '2011-08-23 19:19:44', 1, 'student u11 prijavljen na projekat 1 (predmet pp2', 2),
(491, '2011-08-23 19:19:52', 1, 'nastavnik/projekti predmet=2 ag=1', 1),
(492, '2011-08-23 19:20:22', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(493, '2011-08-23 19:20:24', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(494, '2011-08-23 19:20:32', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda lock=on min_timova=1 max_timova=11', 1),
(495, '2011-08-23 19:20:35', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(496, '2011-08-23 19:20:41', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_timova=1 max_timova=11', 1),
(497, '2011-08-23 19:20:51', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(498, '2011-08-24 06:02:31', 1, 'logout', 1),
(499, '2011-08-24 06:02:43', 1, 'login', 1),
(500, '2011-08-24 06:02:43', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(501, '2011-08-24 06:02:43', 1, 'common/savjet_dana', 1),
(502, '2011-08-24 06:02:51', 1, 'student/intro', 1),
(503, '2011-08-24 06:02:53', 1, 'studentska/intro', 1),
(504, '2011-08-24 06:02:54', 1, 'studentska/predmeti', 1),
(505, '2011-08-24 06:02:56', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(506, '2011-08-24 06:03:03', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(507, '2011-08-24 06:03:03', 1, 'SQL greska (astavnik\\zavrsni.php : 34):Unknown column ''zakljucni_zavrsni'' in ''field list''', 3),
(508, '2011-08-24 06:03:51', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(509, '2011-08-24 06:03:54', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(510, '2011-08-24 06:03:55', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(511, '2011-08-24 06:03:57', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(512, '2011-08-24 06:03:59', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodjela_studenata', 1),
(513, '2011-08-24 06:03:59', 1, 'SQL greska (astavnik\\zavrsni.php : 529):Unknown column ''sp.predmet'' in ''where clause''', 3),
(514, '2011-08-29 03:10:35', 1, 'login', 1),
(515, '2011-08-29 03:10:35', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(516, '2011-08-29 03:10:36', 1, 'common/savjet_dana', 1),
(517, '2011-08-29 03:10:41', 1, 'studentska/intro', 1),
(518, '2011-08-29 03:10:45', 1, 'studentska/predmeti', 1),
(519, '2011-08-29 03:10:47', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(520, '2011-08-29 03:10:52', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(521, '2011-08-29 03:10:54', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(522, '2011-08-29 03:11:04', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=0 max_tema=6 min_clanova=0 max_clanova=1', 1),
(523, '2011-08-29 03:11:07', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(524, '2011-08-29 03:11:15', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda lock=on min_tema=1 max_tema=11 min_clanova=1 max_clanova=3', 1),
(525, '2011-08-29 03:11:15', 1, 'izmijenio parametre zavrsnog na predmetu pp2', 2),
(526, '2011-08-29 03:11:19', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(527, '2011-08-29 03:11:20', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(528, '2011-08-29 03:11:23', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(529, '2011-08-29 03:11:26', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=11 min_clanova=1 max_clanova=3', 1),
(530, '2011-08-29 03:11:26', 1, 'izmijenio parametre zavrsnog na predmetu pp2', 2),
(531, '2011-08-29 03:11:28', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(532, '2011-08-29 03:11:34', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodjela_studenata', 1),
(533, '2011-08-29 03:11:34', 1, 'SQL greska (astavnik\\zavrsni.php : 542):Unknown column ''sp.predmet'' in ''where clause''', 3),
(534, '2011-08-29 03:12:12', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(535, '2011-08-29 03:12:13', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(536, '2011-08-29 03:12:17', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=11 min_clanova=1 max_clanova=3', 1),
(537, '2011-08-29 03:12:18', 1, 'izmijenio parametre zavrsnog na predmetu pp2', 2),
(538, '2011-08-29 03:12:20', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(539, '2011-08-29 03:12:23', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(540, '2011-08-29 03:12:24', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(541, '2011-08-29 03:12:29', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=2 max_tema=11 min_clanova=1 max_clanova=3', 1),
(542, '2011-08-29 03:12:29', 1, 'izmijenio parametre zavrsnog na predmetu pp2', 2),
(543, '2011-08-29 03:12:31', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(544, '2011-08-29 03:12:34', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(545, '2011-08-29 16:27:29', 1, 'login', 1),
(546, '2011-08-29 16:27:29', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(547, '2011-08-29 16:27:29', 1, 'common/savjet_dana', 1),
(548, '2011-08-29 16:27:35', 1, 'saradnik/intro', 1),
(549, '2011-08-29 16:27:38', 1, 'studentska/intro', 1),
(550, '2011-08-29 16:27:41', 1, 'studentska/predmeti', 1),
(551, '2011-08-29 16:27:44', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(552, '2011-08-29 16:27:48', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(553, '2011-08-29 16:27:51', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(554, '2011-08-29 16:28:06', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=2 max_tema=8 min_clanova=1 max_clanova=5', 1),
(555, '2011-08-29 16:28:06', 1, 'izmijenio parametre završnih radova na predmetu pp2', 2),
(556, '2011-08-29 16:28:09', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(557, '2011-08-29 16:28:11', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(558, '2011-08-29 16:28:17', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(559, '2011-08-29 16:28:19', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodjela_studenata', 1),
(560, '2011-08-29 16:28:19', 1, 'SQL greska (astavnik\\zavrsni.php : 532):Unknown column ''sz.predmet'' in ''where clause''', 3),
(561, '2011-08-29 16:28:37', 1, 'studentska/intro', 1),
(562, '2011-08-29 16:28:38', 1, 'studentska/predmeti', 1),
(563, '2011-08-29 16:28:54', 1, 'studentska/osobe', 1),
(564, '2011-08-29 16:29:10', 1, 'studentska/osobe akcija=novi ime=Profesor prezime=Zavrsni', 1),
(565, '2011-08-29 16:29:10', 1, 'dodan novi korisnik u12 (ID: 12)', 4),
(566, '2011-08-29 16:29:12', 1, 'studentska/osobe akcija=edit osoba=12', 1),
(567, '2011-08-29 16:29:23', 1, 'studentska/osobe akcija=edit osoba=12 subakcija=auth stari_login= login=zavrsni password=1 aktivan=1', 1),
(568, '2011-08-29 16:29:23', 1, 'dodan novi login ''zavrsni'' za korisnika u12', 4),
(569, '2011-08-29 16:29:36', 1, 'studentska/osobe akcija=edit osoba=12 subakcija=uloga stari_login= login=zavrsni password=1 aktivan=1 nastavnik=1', 1),
(570, '2011-08-29 16:29:37', 1, 'osobi u12 data privilegija nastavnik', 4),
(571, '2011-08-29 16:29:37', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(572, '2011-08-29 16:29:46', 1, 'studentska/osobe osoba=12 akcija=izbori', 1),
(573, '2011-08-29 16:30:07', 1, 'studentska/osobe osoba=12 akcija=izbori subakcija=novi izborday=1 izbormonth=1 izboryear=1990 neodredjeno=on istekday=1 istekmonth=1 istekyear=1990', 1),
(574, '2011-08-29 16:30:07', 1, 'dodani podaci o izboru za u12', 2),
(575, '2011-08-29 16:30:09', 1, 'studentska/osobe osoba=12 akcija=edit', 1),
(576, '2011-08-29 16:30:09', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(577, '2011-08-29 16:31:08', 1, 'studentska/osobe osoba=12 akcija=edit subakcija=angazuj predmet=4', 1),
(578, '2011-08-29 16:31:08', 1, 'nastavnik u12 angazovan na predmetu pp4 (status: 1, akademska godina: 1)', 4),
(579, '2011-08-29 16:31:08', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(580, '2011-08-29 16:31:17', 1, 'studentska/osobe osoba=12 akcija=edit subakcija=angazuj predmet=2', 1),
(581, '2011-08-29 16:31:17', 1, 'nastavnik u12 angazovan na predmetu pp2 (status: 2, akademska godina: 1)', 4),
(582, '2011-08-29 16:31:17', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(583, '2011-08-29 16:31:30', 1, 'studentska/osobe osoba=12 akcija=edit subakcija=angazuj predmet=3', 1),
(584, '2011-08-29 16:31:30', 1, 'nastavnik u12 angazovan na predmetu pp3 (status: 1, akademska godina: 1)', 4);
INSERT INTO `log` (`id`, `vrijeme`, `userid`, `dogadjaj`, `nivo`) VALUES
(585, '2011-08-29 16:31:30', 1, 'SQL greska (studentska\\osobe.php : 2516):Unknown column ''np.admin'' in ''order clause''', 3),
(586, '2011-08-29 16:31:47', 1, 'logout', 1),
(587, '2011-08-29 16:31:54', 12, 'login', 1),
(588, '2011-08-29 16:31:54', 12, '/zamger/index.php?loginforma=1 login=zavrsni', 1),
(589, '2011-08-29 16:31:54', 12, 'common/savjet_dana', 1),
(590, '2011-08-29 16:32:05', 12, '/zamger/index.php?loginforma=1 login=zavrsni sve=1', 1),
(591, '2011-08-29 16:32:12', 12, 'logout', 1),
(592, '2011-08-29 16:33:08', 8, 'login', 1),
(593, '2011-08-29 16:33:08', 8, '/zamger/index.php?loginforma=1 login=administrator', 1),
(594, '2011-08-29 16:33:14', 8, 'admin/studij', 1),
(595, '2011-08-29 16:33:18', 8, 'admin/novagodina', 1),
(596, '2011-08-29 16:33:21', 8, 'logout', 1),
(597, '2011-08-29 16:33:28', 6, 'login', 1),
(598, '2011-08-29 16:33:28', 6, '/zamger/index.php?loginforma=1 login=studentska', 1),
(599, '2011-08-29 16:33:32', 6, 'studentska/predmeti', 1),
(600, '2011-08-29 16:33:35', 6, 'studentska/predmeti akcija=edit predmet=2 ag=1', 1),
(601, '2011-08-29 16:33:42', 6, 'studentska/predmeti predmet=2 ag=1 akcija=dodaj_pk', 1),
(602, '2011-08-29 16:33:54', 6, 'studentska/predmeti predmet=2 ag=1 akcija=dodaj_pk', 1),
(603, '2011-08-29 16:33:54', 6, 'dodana ponuda kursa na predmet pp2', 4),
(604, '2011-08-29 16:33:57', 6, 'studentska/predmeti akcija=edit predmet=2 ag=1', 1),
(605, '2011-08-29 16:34:15', 6, 'studentska/prijemni', 1),
(606, '2011-08-29 16:34:16', 6, 'studentska/raspored1', 1),
(607, '2011-08-29 16:34:17', 6, 'studentska/izvjestaji', 1),
(608, '2011-08-29 16:34:18', 6, 'studentska/obavijest', 1),
(609, '2011-08-29 16:34:19', 6, 'studentska/prodsjeka', 1),
(610, '2011-08-29 16:34:20', 6, 'studentska/osobe', 1),
(611, '2011-08-29 16:34:22', 6, 'studentska/osobe offset=0 search=', 1),
(612, '2011-08-29 16:34:24', 6, 'studentska/osobe offset=0 search=', 1),
(613, '2011-08-29 16:34:27', 6, 'studentska/osobe offset=0 search=sve', 1),
(614, '2011-08-29 16:34:37', 6, 'studentska/osobe offset=0 search=sve akcija=edit osoba=11', 1),
(615, '2011-08-29 16:34:43', 6, 'studentska/osobe osoba=11 akcija=upis studij= semestar=1 godina=1', 1),
(616, '2011-08-29 16:34:52', 6, 'studentska/osobe osoba=11 akcija=upis studij= semestar=1 godina=1 subakcija=upis_potvrda novi_studij=2 nacin_studiranja=1 novi_brindexa=15000', 1),
(617, '2011-08-29 16:34:53', 6, 'studentska/osobe osoba=11 akcija=upis studij=2 semestar=1 godina=1 subakcija=upis_potvrda novi_studij=0 nacin_studiranja=1 novi_brindexa=15000', 1),
(618, '2011-08-29 16:34:53', 6, 'Student u11 upisan na studij s2, semestar 1, godina ag1', 4),
(619, '2011-08-29 16:35:02', 6, 'studentska/raspored1', 1),
(620, '2011-08-29 16:35:03', 6, 'studentska/izvjestaji', 1),
(621, '2011-08-29 16:35:04', 6, 'studentska/obavijest', 1),
(622, '2011-08-29 16:35:06', 6, 'studentska/raspored1', 1),
(623, '2011-08-29 16:35:10', 6, 'studentska/raspored1 raspored_za_edit=1', 1),
(624, '2011-08-29 16:35:26', 6, 'studentska/predmeti', 1),
(625, '2011-08-29 16:35:33', 6, 'logout', 1),
(626, '2011-08-29 16:35:39', 5, 'login', 1),
(627, '2011-08-29 16:35:39', 5, '/zamger/index.php?loginforma=1 login=nastavnik', 1),
(628, '2011-08-29 16:35:39', 5, 'common/savjet_dana', 1),
(629, '2011-08-29 16:35:44', 5, 'saradnik/grupa id=2', 1),
(630, '2011-08-29 16:35:50', 5, 'saradnik/grupa id=2 kreiranje=1', 1),
(631, '2011-08-29 16:35:53', 5, 'saradnik/grupa id=2 kreiranje=1 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:35 prisustvo=1', 1),
(632, '2011-08-29 16:35:53', 5, 'registrovan cas c1', 2),
(633, '2011-08-29 16:35:55', 5, 'saradnik/grupa id=2 kreiranje=1 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:35 prisustvo=1', 1),
(634, '2011-08-29 16:35:55', 5, 'registrovan cas c2', 2),
(635, '2011-08-29 16:35:55', 5, 'saradnik/grupa id=2 kreiranje=1 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:35 prisustvo=1', 1),
(636, '2011-08-29 16:35:55', 5, 'registrovan cas c3', 2),
(637, '2011-08-29 16:35:56', 5, 'saradnik/grupa id=2 kreiranje=1 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:35 prisustvo=1', 1),
(638, '2011-08-29 16:35:56', 5, 'registrovan cas c4', 2),
(639, '2011-08-29 16:35:56', 5, 'saradnik/grupa id=2 kreiranje=1 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:35 prisustvo=1', 1),
(640, '2011-08-29 16:35:56', 5, 'registrovan cas c5', 2),
(641, '2011-08-29 16:36:02', 5, 'saradnik/intro', 1),
(642, '2011-08-29 16:36:05', 5, 'saradnik/grupa id=4', 1),
(643, '2011-08-29 16:36:09', 5, 'saradnik/grupa id=4 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:36 prisustvo=1', 1),
(644, '2011-08-29 16:36:09', 5, 'registrovan cas c6', 2),
(645, '2011-08-29 16:36:10', 5, 'saradnik/grupa id=4 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:36 prisustvo=1', 1),
(646, '2011-08-29 16:36:10', 5, 'registrovan cas c7', 2),
(647, '2011-08-29 16:36:10', 5, 'saradnik/grupa id=4 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:36 prisustvo=1', 1),
(648, '2011-08-29 16:36:10', 5, 'registrovan cas c8', 2),
(649, '2011-08-29 16:36:13', 5, 'saradnik/intro', 1),
(650, '2011-08-29 16:36:15', 5, 'saradnik/grupa id=5', 1),
(651, '2011-08-29 16:36:16', 5, 'saradnik/grupa id=5 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:36 prisustvo=1', 1),
(652, '2011-08-29 16:36:16', 5, 'registrovan cas c9', 2),
(653, '2011-08-29 16:36:17', 5, 'saradnik/grupa id=5 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:36 prisustvo=1', 1),
(654, '2011-08-29 16:36:17', 5, 'registrovan cas c10', 2),
(655, '2011-08-29 16:36:17', 5, 'saradnik/grupa id=5 akcija=dodajcas dan=29 mjesec=8 godina=2011 vrijeme=23:36 prisustvo=1', 1),
(656, '2011-08-29 16:36:17', 5, 'registrovan cas c11', 2),
(657, '2011-08-29 16:36:18', 5, 'saradnik/intro', 1),
(658, '2011-08-29 16:36:20', 5, 'saradnik/intro sve=1', 1),
(659, '2011-08-29 16:36:23', 5, 'nastavnik/predmet predmet=2 ag=1', 1),
(660, '2011-08-29 16:36:28', 5, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(661, '2011-08-29 16:36:30', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(662, '2011-08-29 16:36:35', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=11 min_clanova=1 max_clanova=3', 1),
(663, '2011-08-29 16:36:35', 5, 'izmijenio parametre završnih radova na predmetu pp2', 2),
(664, '2011-08-29 16:36:36', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(665, '2011-08-29 16:36:42', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=11 min_clanova=1 max_clanova=3', 1),
(666, '2011-08-29 16:36:42', 5, 'izmijenio parametre završnih radova na predmetu pp2', 2),
(667, '2011-08-29 16:36:44', 5, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(668, '2011-08-29 16:36:46', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(669, '2011-08-29 16:36:49', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(670, '2011-08-29 16:37:03', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=0 max_tema=5 min_clanova=0 max_clanova=1', 1),
(671, '2011-08-29 16:37:06', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(672, '2011-08-29 16:37:16', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=5 min_clanova=1 max_clanova=2', 1),
(673, '2011-08-29 16:37:16', 5, 'izmijenio parametre završnih radova na predmetu pp2', 2),
(674, '2011-08-29 16:37:18', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(675, '2011-08-29 16:37:24', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodjela_studenata', 1),
(676, '2011-08-29 16:37:24', 5, 'SQL greska (astavnik\\zavrsni.php : 532):Unknown column ''sz.predmet'' in ''where clause''', 3),
(677, '2011-08-29 16:37:35', 5, 'nastavnik/projekti predmet=2 ag=1', 1),
(678, '2011-08-29 16:37:39', 5, 'nastavnik/projekti predmet=2 ag=1 akcija=param', 1),
(679, '2011-08-29 16:37:52', 5, 'nastavnik/projekti predmet=2 ag=1 akcija=param subakcija=potvrda min_timova=0 max_timova=6 min_clanova_tima=0 max_clanova_tima=1', 1),
(680, '2011-08-29 16:37:54', 5, 'nastavnik/projekti predmet=2 ag=1 akcija=param', 1),
(681, '2011-08-29 16:37:57', 5, 'nastavnik/projekti predmet=2 ag=1 akcija=param subakcija=potvrda min_timova=1 max_timova=11 min_clanova_tima=2 max_clanova_tima=5', 1),
(682, '2011-08-29 16:37:57', 5, 'izmijenio parametre projekata na predmetu pp2', 2),
(683, '2011-08-29 16:37:58', 5, 'nastavnik/projekti predmet=2 ag=1', 1),
(684, '2011-08-29 16:37:59', 5, 'nastavnik/projekti predmet=2 ag=1 akcija=dodjela_studenata', 1),
(685, '2011-08-29 16:38:00', 5, 'nastavnik/projekti predmet=2 ag=1 akcija=dodaj_projekat', 1),
(686, '2011-08-29 16:38:20', 5, 'nastavnik/projekti predmet=2 ag=1 akcija=dodaj_projekat subakcija=potvrda naziv=Test1 opis=CCCCCCCCCCCCCCCCCCCCCCCCCCCCCcccccccccccccccccccccccCCCCCCCCCCCCCCC', 1),
(687, '2011-08-29 16:38:20', 5, 'dodao novi projekat na predmetu pp2', 2),
(688, '2011-08-29 16:38:22', 5, 'nastavnik/projekti predmet=2 ag=1 akcija=dodjela_studenata', 1),
(689, '2011-08-29 16:38:29', 5, 'nastavnik/projekti predmet=2 ag=1 akcija=dodjela_studenata subakcija=dodaj student=11 projekat=2 dodaj=Upiši', 1),
(690, '2011-08-29 16:38:29', 5, 'student u11 prebacen sa projekta 1 na 2 (predmet pp2', 2),
(691, '2011-08-29 16:38:31', 5, 'nastavnik/projekti predmet=2 ag=1', 1),
(692, '2011-08-29 16:38:43', 5, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(693, '2011-08-29 16:38:44', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(694, '2011-08-29 16:38:49', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=11 min_clanova=1 max_clanova=3', 1),
(695, '2011-08-29 16:38:49', 5, 'izmijenio parametre završnih radova na predmetu pp2', 2),
(696, '2011-08-29 16:38:51', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(697, '2011-08-29 16:38:59', 5, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodjela_studenata', 1),
(698, '2011-08-29 16:38:59', 5, 'SQL greska (astavnik\\zavrsni.php : 532):Unknown column ''sz.predmet'' in ''where clause''', 3),
(699, '2011-08-29 17:23:59', 1, 'login', 1),
(700, '2011-08-29 17:23:59', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(701, '2011-08-29 17:24:00', 1, 'common/savjet_dana', 1),
(702, '2011-08-29 17:24:04', 1, 'studentska/intro', 1),
(703, '2011-08-29 17:24:12', 1, 'studentska/predmeti', 1),
(704, '2011-08-29 17:24:14', 1, 'nastavnik/predmet predmet=3 ag=1', 1),
(705, '2011-08-29 17:24:19', 1, 'nastavnik/predmet predmet=3 ag=1 akcija=set_smodul smodul=5 aktivan=0', 1),
(706, '2011-08-29 17:24:19', 1, 'aktiviran studentski modul 5 (predmet pp3)', 2),
(707, '2011-08-29 17:24:24', 1, 'nastavnik/zavrsni predmet=3 ag=1', 1),
(708, '2011-08-29 17:24:26', 1, 'nastavnik/zavrsni predmet=3 ag=1 akcija=param', 1),
(709, '2011-08-29 17:24:37', 1, 'nastavnik/zavrsni predmet=3 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=6 min_clanova=1 max_clanova=3', 1),
(710, '2011-08-29 17:24:37', 1, 'izmijenio parametre završnih radova na predmetu pp3', 2),
(711, '2011-08-29 17:24:38', 1, 'nastavnik/zavrsni predmet=3 ag=1 akcija=dodaj_zavrsni', 1),
(712, '2011-08-29 17:24:51', 1, 'nastavnik/zavrsni predmet=3 ag=1 akcija=dodaj_zavrsni subakcija=potvrda naziv=Pokusaj opis=neki pokusaj', 1),
(713, '2011-08-29 17:24:51', 1, 'dodana nova tema završnog rada na predmetu pp3', 2),
(714, '2011-08-29 17:24:54', 1, 'nastavnik/zavrsni predmet=3 ag=1', 1),
(715, '2011-08-29 17:24:54', 1, 'SQL greska (astavnik\\zavrsni.php : 116):SQL syntax error  to your MySQL server version for the right syntax to use near ''sp where o.id=sz.student and sz.zavrsni=1'' at line 1', 3),
(716, '2011-08-29 17:25:07', 1, 'nastavnik/zavrsni predmet=3 ag=1 akcija=dodjela_studenata', 1),
(717, '2011-08-29 17:25:07', 1, 'SQL greska (astavnik\\zavrsni.php : 532):Unknown column ''sz.predmet'' in ''where clause''', 3),
(718, '2011-08-29 18:18:26', 1, 'nastavnik/zavrsni predmet=3 ag=1', 1),
(719, '2011-08-29 18:18:26', 1, 'SQL greska (astavnik\\zavrsni.php : 116):SQL syntax error  to your MySQL server version for the right syntax to use near ''sp where o.id=sz.student and sz.zavrsni=1'' at line 1', 3),
(720, '2011-08-29 18:21:43', 1, 'nastavnik/zavrsni predmet=3 ag=1 akcija=dodjela_studenata', 1),
(721, '2011-08-29 18:21:43', 1, 'SQL greska (astavnik\\zavrsni.php : 532):Unknown column ''sz.predmet'' in ''where clause''', 3),
(722, '2011-08-29 18:31:01', 1, 'login', 1),
(723, '2011-08-29 18:31:01', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(724, '2011-08-29 18:31:01', 1, 'common/savjet_dana', 1),
(725, '2011-08-29 18:31:07', 1, 'studentska/intro', 1),
(726, '2011-08-29 18:31:09', 1, 'studentska/predmeti', 1),
(727, '2011-08-29 18:31:13', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(728, '2011-08-29 18:31:20', 1, 'nastavnik/predmet predmet=1 ag=1 akcija=set_smodul smodul=5 aktivan=0', 1),
(729, '2011-08-29 18:31:20', 1, 'aktiviran studentski modul 5 (predmet pp1)', 2),
(730, '2011-08-29 18:31:24', 1, 'nastavnik/zavrsni predmet=1 ag=1', 1),
(731, '2011-08-29 18:31:26', 1, 'nastavnik/zavrsni predmet=1 ag=1 akcija=param', 1),
(732, '2011-08-29 18:31:37', 1, 'nastavnik/zavrsni predmet=1 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=6 min_clanova=1 max_clanova=2', 1),
(733, '2011-08-29 18:31:37', 1, 'izmijenio parametre završnih radova na predmetu pp1', 2),
(734, '2011-08-29 18:31:39', 1, 'nastavnik/zavrsni predmet=1 ag=1 akcija=dodaj_zavrsni', 1),
(735, '2011-08-29 18:31:50', 1, 'nastavnik/zavrsni predmet=1 ag=1 akcija=dodaj_zavrsni subakcija=potvrda naziv=testirana opis=nesto', 1),
(736, '2011-08-29 18:31:50', 1, 'dodana nova tema završnog rada na predmetu pp1', 2),
(737, '2011-08-29 18:31:51', 1, 'nastavnik/zavrsni predmet=1 ag=1', 1),
(738, '2011-08-29 18:31:51', 1, 'SQL greska (astavnik\\zavrsni.php : 138):SQL syntax error  to your MySQL server version for the right syntax to use near ''sp WHERE sz.student=o.id and sz.zavrsni=2 ORDER BY o.prezime, o.ime'' at line 1', 3),
(739, '2011-08-29 18:34:56', 1, 'nastavnik/zavrsni predmet=1 ag=1', 1),
(740, '2011-08-29 18:34:56', 1, 'SQL greska (astavnik\\zavrsni.php : 138):SQL syntax error  to your MySQL server version for the right syntax to use near ''sp WHERE sz.student=o.id and sz.zavrsni=2 ORDER BY o.prezime, o.ime'' at line 1', 3),
(741, '2011-08-29 18:34:57', 1, 'nastavnik/zavrsni predmet=1 ag=1', 1),
(742, '2011-08-29 18:34:57', 1, 'SQL greska (astavnik\\zavrsni.php : 138):SQL syntax error  to your MySQL server version for the right syntax to use near ''sp WHERE sz.student=o.id and sz.zavrsni=2 ORDER BY o.prezime, o.ime'' at line 1', 3),
(743, '2011-08-29 18:34:58', 1, 'nastavnik/zavrsni predmet=1 ag=1', 1),
(744, '2011-08-29 18:34:58', 1, 'SQL greska (astavnik\\zavrsni.php : 138):SQL syntax error  to your MySQL server version for the right syntax to use near ''sp WHERE sz.student=o.id and sz.zavrsni=2 ORDER BY o.prezime, o.ime'' at line 1', 3),
(745, '2011-08-29 18:51:19', 0, 'index.php greska: Pogrešna šifra nastavnik ', 3),
(746, '2011-08-29 18:51:28', 0, 'index.php greska: Nepoznat korisnik mujo ', 3),
(747, '2011-08-29 18:51:37', 5, 'login', 1),
(748, '2011-08-29 18:51:37', 5, '/zamger/index.php?loginforma=1 login=nastavnik', 1),
(749, '2011-08-29 18:51:37', 5, 'common/savjet_dana', 1),
(750, '2011-08-29 18:51:53', 5, 'nastavnik/predmet predmet=5 ag=1', 1),
(751, '2011-08-29 18:51:58', 5, 'nastavnik/predmet predmet=5 ag=1 akcija=set_smodul smodul=5 aktivan=0', 1),
(752, '2011-08-29 18:51:58', 5, 'aktiviran studentski modul 5 (predmet pp5)', 2),
(753, '2011-08-29 18:52:00', 5, 'nastavnik/zavrsni predmet=5 ag=1', 1),
(754, '2011-08-29 18:52:02', 5, 'nastavnik/zavrsni predmet=5 ag=1 akcija=param', 1),
(755, '2011-08-29 18:52:17', 5, 'nastavnik/zavrsni predmet=5 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=5 min_clanova=1 max_clanova=2', 1),
(756, '2011-08-29 18:52:17', 5, 'izmijenio parametre završnih radova na predmetu pp5', 2),
(757, '2011-08-29 18:52:19', 5, 'nastavnik/zavrsni predmet=5 ag=1 akcija=dodaj_zavrsni', 1),
(758, '2011-08-29 18:52:28', 5, 'nastavnik/zavrsni predmet=5 ag=1 akcija=dodaj_zavrsni subakcija=potvrda naziv=ttttttttttttttt opis=ccccccccccc', 1),
(759, '2011-08-29 18:52:28', 5, 'dodana nova tema završnog rada na predmetu pp5', 2),
(760, '2011-08-29 18:52:30', 5, 'nastavnik/zavrsni predmet=5 ag=1', 1),
(761, '2011-08-29 18:52:30', 5, 'SQL greska (astavnik\\zavrsni.php : 138):SQL syntax error  to your MySQL server version for the right syntax to use near ''sp WHERE sz.student=o.id and sz.zavrsni=3 ORDER BY o.prezime, o.ime'' at line 1', 3),
(762, '2011-08-29 18:52:51', 5, 'nastavnik/zavrsni predmet=5 ag=1 akcija=dodjela_studenata', 1),
(763, '2011-08-29 18:52:51', 5, 'SQL greska (astavnik\\zavrsni.php : 532):Unknown column ''sz.predmet'' in ''where clause''', 3),
(764, '2011-08-29 18:53:29', 5, 'nastavnik/raspored predmet=5 ag=1', 1),
(765, '2011-08-29 18:53:31', 5, 'nastavnik/projekti predmet=5 ag=1', 1),
(766, '2011-08-29 18:53:33', 5, 'nastavnik/projekti predmet=5 ag=1 akcija=param', 1),
(767, '2011-08-29 18:53:44', 5, 'nastavnik/projekti predmet=5 ag=1 akcija=param subakcija=potvrda min_timova=2 max_timova=3 min_clanova_tima=1 max_clanova_tima=5', 1),
(768, '2011-08-29 18:53:44', 5, 'izmijenio parametre projekata na predmetu pp5', 2),
(769, '2011-08-29 18:53:46', 5, 'nastavnik/projekti predmet=5 ag=1 akcija=dodaj_projekat', 1),
(770, '2011-08-29 18:53:58', 5, 'nastavnik/projekti predmet=5 ag=1 akcija=dodaj_projekat subakcija=potvrda naziv=projekat opis=project', 1),
(771, '2011-08-29 18:53:58', 5, 'dodao novi projekat na predmetu pp5', 2),
(772, '2011-08-29 18:54:01', 5, 'nastavnik/projekti predmet=5 ag=1', 1),
(773, '2011-08-29 18:54:30', 5, 'nastavnik/projekti predmet=5 ag=1 akcija=dodjela_studenata', 1),
(774, '2011-08-29 18:55:06', 5, 'nastavnik/zavrsni predmet=5 ag=1', 1),
(775, '2011-08-29 18:55:06', 5, 'SQL greska (astavnik\\zavrsni.php : 138):SQL syntax error  to your MySQL server version for the right syntax to use near ''sp WHERE sz.student=o.id and sz.zavrsni=3 ORDER BY o.prezime, o.ime'' at line 1', 3),
(776, '2011-08-29 18:55:11', 5, 'nastavnik/zavrsni predmet=5 ag=1 akcija=dodjela_studenata', 1),
(777, '2011-08-29 18:55:11', 5, 'SQL greska (astavnik\\zavrsni.php : 532):Unknown column ''sz.predmet'' in ''where clause''', 3),
(778, '2011-08-31 18:09:40', 1, 'login', 1),
(779, '2011-08-31 18:09:40', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(780, '2011-08-31 18:09:40', 1, 'common/savjet_dana', 1),
(781, '2011-08-31 18:09:44', 1, 'studentska/intro', 1),
(782, '2011-08-31 18:09:46', 1, 'studentska/predmeti', 1),
(783, '2011-08-31 18:09:55', 1, 'studentska/predmeti akcija=novi naziv=Pokusaj predmeta', 1),
(784, '2011-08-31 18:09:55', 1, 'potpuno novi predmet pp6, akademska godina ag1', 4),
(785, '2011-08-31 18:09:57', 1, 'studentska/predmeti akcija=edit predmet=6 ag=1', 1),
(786, '2011-08-31 18:10:00', 1, 'studentska/predmeti predmet=6 ag=1 akcija=realedit', 1),
(787, '2011-08-31 18:10:13', 1, 'studentska/predmeti predmet=6 ag=1 akcija=realedit', 1),
(788, '2011-08-31 18:10:13', 1, 'izmijenjeni podaci o predmetu pp6', 4),
(789, '2011-08-31 18:10:16', 1, 'studentska/predmeti akcija=edit predmet=6 ag=1', 1),
(790, '2011-08-31 18:10:29', 1, 'studentska/predmeti predmet=6 ag=1 akcija=edit subakcija=dodaj_nastavnika nastavnik=7', 1),
(791, '2011-08-31 18:10:29', 1, 'nastavnik u7 dodan na predmet pp6', 4),
(792, '2011-08-31 18:10:33', 1, 'studentska/predmeti predmet=6 ag=1 subakcija=postavi_nivo_pristupa nastavnik=7 akcija=edit nivo_pristupa=asistent', 1),
(793, '2011-08-31 18:10:33', 1, 'nastavnik u7 dat nivo ''asistent'' na predmetu pp6', 4),
(794, '2011-08-31 18:10:40', 1, 'studentska/predmeti predmet=6 ag=1 subakcija=dodaj_nastavnika nastavnik=12 nivo_pristupa=asistent akcija=edit', 1),
(795, '2011-08-31 18:10:40', 1, 'nastavnik u12 dodan na predmet pp6', 4),
(796, '2011-08-31 18:10:43', 1, 'studentska/predmeti predmet=6 ag=1 subakcija=postavi_nivo_pristupa nastavnik=12 nivo_pristupa=asistent akcija=edit', 1),
(797, '2011-08-31 18:10:43', 1, 'nastavnik u12 dat nivo ''asistent'' na predmetu pp6', 4),
(798, '2011-08-31 18:10:47', 1, 'studentska/predmeti predmet=6 ag=1 subakcija=postavi_nivo_pristupa nastavnik=12 nivo_pristupa=nastavnik akcija=edit', 1),
(799, '2011-08-31 18:10:47', 1, 'nastavnik u12 dat nivo ''nastavnik'' na predmetu pp6', 4),
(800, '2011-08-31 18:10:52', 1, 'studentska/predmeti predmet=6 ag=1 akcija=dodaj_pk', 1),
(801, '2011-08-31 18:11:00', 1, 'studentska/predmeti predmet=6 ag=1 akcija=dodaj_pk', 1),
(802, '2011-08-31 18:11:00', 1, 'dodana ponuda kursa na predmet pp6', 4),
(803, '2011-08-31 18:11:02', 1, 'studentska/predmeti akcija=edit predmet=6 ag=1', 1),
(804, '2011-08-31 18:11:14', 1, 'izvjestaj/grupe predmet=6 ag=1', 1),
(805, '2011-08-31 18:11:18', 1, 'studentska/predmeti akcija=edit predmet=6 ag=1', 1),
(806, '2011-08-31 18:11:20', 1, 'studentska/osobe', 1),
(807, '2011-08-31 18:11:23', 1, 'studentska/predmeti', 1),
(808, '2011-08-31 18:11:26', 1, 'nastavnik/predmet predmet=6 ag=1', 1),
(809, '2011-08-31 18:11:28', 1, 'nastavnik/predmet predmet=6 ag=1 akcija=set_smodul smodul=1 aktivan=0', 1),
(810, '2011-08-31 18:11:28', 1, 'aktiviran studentski modul 1 (predmet pp6)', 2),
(811, '2011-08-31 18:11:30', 1, 'nastavnik/predmet predmet=6 ag=1 akcija=set_smodul smodul=3 aktivan=0', 1),
(812, '2011-08-31 18:11:30', 1, 'aktiviran studentski modul 3 (predmet pp6)', 2),
(813, '2011-08-31 18:11:31', 1, 'nastavnik/predmet predmet=6 ag=1 akcija=set_smodul smodul=4 aktivan=0', 1),
(814, '2011-08-31 18:11:31', 1, 'aktiviran studentski modul 4 (predmet pp6)', 2),
(815, '2011-08-31 18:11:35', 1, 'nastavnik/predmet predmet=6 ag=1 akcija=set_smodul smodul=5 aktivan=0', 1),
(816, '2011-08-31 18:11:35', 1, 'aktiviran studentski modul 5 (predmet pp6)', 2),
(817, '2011-08-31 18:11:38', 1, 'nastavnik/predmet predmet=6 ag=1 akcija=set_smodul smodul=2 aktivan=0', 1),
(818, '2011-08-31 18:11:38', 1, 'aktiviran studentski modul 2 (predmet pp6)', 2),
(819, '2011-08-31 18:11:42', 1, 'nastavnik/zavrsni predmet=6 ag=1', 1),
(820, '2011-08-31 18:11:44', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=param', 1),
(821, '2011-08-31 18:11:55', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=6 min_clanova=1 max_clanova=3', 1),
(822, '2011-08-31 18:11:55', 1, 'izmijenio parametre završnih radova na predmetu pp6', 2),
(823, '2011-08-31 18:11:57', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodaj_zavrsni', 1),
(824, '2011-08-31 18:12:09', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodaj_zavrsni subakcija=potvrda naziv=zavrsni opis=zavrsni', 1),
(825, '2011-08-31 18:12:09', 1, 'dodana nova tema završnog rada na predmetu pp6', 2),
(826, '2011-08-31 18:12:11', 1, 'nastavnik/zavrsni predmet=6 ag=1', 1),
(827, '2011-08-31 18:12:11', 1, 'SQL greska (astavnik\\zavrsni.php : 138):SQL syntax error  to your MySQL server version for the right syntax to use near ''sp WHERE sz.student=o.id and sz.zavrsni=4 ORDER BY o.prezime, o.ime'' at line 1', 3),
(828, '2011-08-31 18:12:41', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodjela_studenata', 1),
(829, '2011-08-31 18:12:41', 1, 'SQL greska (astavnik\\zavrsni.php : 532):Unknown column ''sz.predmet'' in ''where clause''', 3),
(830, '2011-08-31 18:26:22', 1, 'login', 1),
(831, '2011-08-31 18:26:22', 1, '/zamger/index.php?loginforma=1 login=admin', 1),
(832, '2011-08-31 18:26:23', 1, 'common/savjet_dana', 1),
(833, '2011-08-31 18:26:26', 1, 'studentska/intro', 1),
(834, '2011-08-31 18:26:28', 1, 'studentska/predmeti', 1),
(835, '2011-08-31 18:26:30', 1, 'nastavnik/predmet predmet=6 ag=1', 1),
(836, '2011-08-31 18:26:33', 1, 'nastavnik/zavrsni predmet=6 ag=1', 1),
(837, '2011-08-31 18:26:43', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodjela_studenata', 1),
(838, '2011-08-31 18:27:06', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=param', 1),
(839, '2011-08-31 18:27:12', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=8 min_clanova=1 max_clanova=3', 1),
(840, '2011-08-31 18:27:12', 1, 'izmijenio parametre završnih radova na predmetu pp6', 2),
(841, '2011-08-31 18:27:14', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodaj_zavrsni', 1),
(842, '2011-08-31 18:27:22', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodaj_zavrsni subakcija=potvrda naziv=sdfghj opis=dsftgzthij', 1),
(843, '2011-08-31 18:27:22', 1, 'dodana nova tema završnog rada na predmetu pp6', 2),
(844, '2011-08-31 18:27:24', 1, 'nastavnik/zavrsni predmet=6 ag=1', 1),
(845, '2011-08-31 18:27:30', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodjela_studenata', 1),
(846, '2011-08-31 18:37:15', 1, 'studentska/intro', 1),
(847, '2011-08-31 18:37:16', 1, 'studentska/predmeti', 1),
(848, '2011-08-31 18:37:18', 1, 'nastavnik/predmet predmet=2 ag=1', 1),
(849, '2011-08-31 18:37:20', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(850, '2011-08-31 18:37:22', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(851, '2011-08-31 18:37:23', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(852, '2011-08-31 18:37:24', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param', 1),
(853, '2011-08-31 18:37:29', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=11 min_clanova=1 max_clanova=3', 1),
(854, '2011-08-31 18:37:29', 1, 'izmijenio parametre završnih radova na predmetu pp2', 2),
(855, '2011-08-31 18:37:30', 1, 'nastavnik/zavrsni predmet=2 ag=1 akcija=dodaj_zavrsni', 1),
(856, '2011-08-31 18:37:36', 1, 'nastavnik/zavrsni predmet=2 ag=1', 1),
(857, '2011-08-31 18:37:41', 1, 'studentska/intro', 1),
(858, '2011-08-31 18:37:42', 1, 'studentska/predmeti', 1),
(859, '2011-08-31 18:37:49', 1, 'nastavnik/predmet predmet=4 ag=1', 1),
(860, '2011-08-31 18:37:51', 1, 'nastavnik/zavrsni predmet=4 ag=1', 1),
(861, '2011-08-31 18:37:53', 1, 'nastavnik/zavrsni predmet=4 ag=1 akcija=param', 1),
(862, '2011-08-31 18:37:59', 1, 'nastavnik/zavrsni predmet=4 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=2 min_clanova=3 max_clanova=4', 1),
(863, '2011-08-31 18:37:59', 1, 'izmijenio parametre završnih radova na predmetu pp4', 2),
(864, '2011-08-31 18:38:01', 1, 'nastavnik/zavrsni predmet=4 ag=1 akcija=dodaj_zavrsni', 1),
(865, '2011-08-31 18:38:07', 1, 'nastavnik/zavrsni predmet=4 ag=1 akcija=dodaj_zavrsni subakcija=potvrda naziv=test opis=addfghhhh', 1),
(866, '2011-08-31 18:38:07', 1, 'dodana nova tema završnog rada na predmetu pp4', 2),
(867, '2011-08-31 18:38:09', 1, 'nastavnik/zavrsni predmet=4 ag=1 akcija=dodjela_studenata', 1),
(868, '2011-08-31 18:38:25', 1, 'studentska/intro', 1),
(869, '2011-08-31 18:38:26', 1, 'studentska/osobe', 1),
(870, '2011-08-31 18:38:28', 1, 'studentska/osobe offset=0 search=', 1),
(871, '2011-08-31 18:38:30', 1, 'studentska/osobe offset=0 search=sve', 1),
(872, '2011-08-31 18:38:36', 1, 'studentska/osobe offset=0 search=sve akcija=edit osoba=11', 1),
(873, '2011-08-31 18:38:40', 1, 'studentska/osobe osoba=11 akcija=predmeti', 1),
(874, '2011-08-31 18:38:45', 1, 'studentska/osobe osoba=11 akcija=predmeti spisak=0', 1),
(875, '2011-08-31 18:38:48', 1, 'studentska/osobe osoba=11 akcija=predmeti spisak=1', 1),
(876, '2011-08-31 18:38:51', 1, 'studentska/predmeti', 1),
(877, '2011-08-31 18:38:54', 1, 'studentska/predmeti akcija=edit predmet=2 ag=1', 1),
(878, '2011-08-31 18:38:56', 1, 'studentska/predmeti', 1),
(879, '2011-08-31 18:38:58', 1, 'studentska/predmeti akcija=edit predmet=6 ag=1', 1),
(880, '2011-08-31 18:39:07', 1, 'studentska/predmeti predmet=6 ag=1 akcija=dodaj_pk', 1),
(881, '2011-08-31 18:39:12', 1, 'studentska/osobe', 1),
(882, '2011-08-31 18:39:24', 1, 'studentska/osobe akcija=novi ime=Testirani prezime=Student', 1),
(883, '2011-08-31 18:39:24', 1, 'dodan novi korisnik u13 (ID: 13)', 4),
(884, '2011-08-31 18:39:26', 1, 'studentska/osobe akcija=edit osoba=13', 1),
(885, '2011-08-31 18:39:28', 1, 'studentska/osobe akcija=podaci osoba=13', 1),
(886, '2011-08-31 18:39:32', 1, 'studentska/osobe akcija=edit osoba=13', 1),
(887, '2011-08-31 18:39:59', 1, 'studentska/osobe akcija=edit osoba=13 subakcija=auth stari_login= login=maja password=123 aktivan=1', 1),
(888, '2011-08-31 18:39:59', 1, 'dodan novi login ''maja'' za korisnika u13', 4),
(889, '2011-08-31 18:40:02', 1, 'studentska/osobe akcija=edit osoba=13 subakcija=uloga stari_login= login=maja password=123 aktivan=1 student=1', 1),
(890, '2011-08-31 18:40:02', 1, 'osobi u13 data privilegija student', 4),
(891, '2011-08-31 18:40:05', 1, 'studentska/osobe osoba=13 akcija=upis studij= semestar=1 godina=1', 1),
(892, '2011-08-31 18:40:15', 1, 'studentska/osobe osoba=13 akcija=upis studij= semestar=1 godina=1 subakcija=upis_potvrda novi_studij=3 nacin_studiranja=1 novi_brindexa=1234156', 1),
(893, '2011-08-31 18:40:21', 1, 'studentska/osobe osoba=13 akcija=upis studij=3 semestar=1 godina=1 subakcija=upis_potvrda novi_studij=0 nacin_studiranja=1 novi_brindexa=123456', 1),
(894, '2011-08-31 18:40:21', 1, 'Student u13 upisan na studij s3, semestar 1, godina ag1', 4),
(895, '2011-08-31 18:40:27', 1, 'studentska/osobe', 1),
(896, '2011-08-31 18:40:33', 1, 'studentska/osobe osoba=13 akcija=upis studij=3 semestar=1 godina=1 subakcija=upis_potvrda novi_studij=0 nacin_studiranja=1 novi_brindexa=123456', 1),
(897, '2011-08-31 18:40:33', 1, 'SQL greska (studentska\\osobe.php : 1043):Duplicate entry ''13-3-1-1'' for key ''PRIMARY''', 3),
(898, '2011-08-31 18:40:35', 1, 'studentska/osobe akcija=edit osoba=13', 1),
(899, '2011-08-31 18:40:39', 1, 'studentska/osobe osoba=13 akcija=predmeti', 1),
(900, '2011-08-31 18:40:53', 1, 'studentska/predmeti', 1),
(901, '2011-08-31 18:40:55', 1, 'nastavnik/predmet predmet=6 ag=1', 1),
(902, '2011-08-31 18:40:58', 1, 'studentska/predmeti', 1),
(903, '2011-08-31 18:41:00', 1, 'studentska/predmeti akcija=edit predmet=6 ag=1', 1),
(904, '2011-08-31 18:41:02', 1, 'studentska/predmeti predmet=6 ag=1 akcija=dodaj_pk', 1),
(905, '2011-08-31 18:41:06', 1, 'studentska/predmeti predmet=6 ag=1 akcija=dodaj_pk', 1),
(906, '2011-08-31 18:41:06', 1, 'dodana ponuda kursa na predmet pp6', 4),
(907, '2011-08-31 18:41:08', 1, 'studentska/osobe', 1),
(908, '2011-08-31 18:41:10', 1, 'studentska/osobe search=sve', 1),
(909, '2011-08-31 18:41:19', 1, 'studentska/osobe search=sve akcija=edit osoba=13', 1),
(910, '2011-08-31 18:41:25', 1, 'studentska/osobe osoba=13 akcija=predmeti', 1),
(911, '2011-08-31 18:41:28', 1, 'studentska/osobe akcija=predmeti osoba=13 subakcija=upisi ponudakursa=8 spisak=0', 1),
(912, '2011-08-31 18:41:28', 1, 'student u13 manuelno upisan na predmet p8', 4),
(913, '2011-08-31 18:41:33', 1, 'studentska/predmeti', 1),
(914, '2011-08-31 18:41:36', 1, 'nastavnik/predmet predmet=6 ag=1', 1),
(915, '2011-08-31 18:41:37', 1, 'nastavnik/zavrsni predmet=6 ag=1', 1),
(916, '2011-08-31 18:41:39', 1, 'nastavnik/zavrsni predmet=6 ag=1', 1),
(917, '2011-08-31 18:41:41', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodjela_studenata', 1),
(918, '2011-08-31 18:41:45', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodjela_studenata subakcija=dodaj zavrsni=5 dodaj=Upiši', 1),
(919, '2011-08-31 18:41:45', 1, 'student u0 prijavljen na temu završnog rada 5 (predmet pp6', 2),
(920, '2011-08-31 18:41:51', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodjela_studenata subakcija=dodaj zavrsni=4 dodaj=Upiši', 1),
(921, '2011-08-31 18:41:51', 1, 'student u0 prebacen sa teme završnog rada 5 na 4 (predmet pp6', 2),
(922, '2011-08-31 18:41:54', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=param', 1),
(923, '2011-08-31 18:41:56', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=param subakcija=potvrda min_tema=1 max_tema=6 min_clanova=1 max_clanova=3', 1),
(924, '2011-08-31 18:41:56', 1, 'izmijenio parametre završnih radova na predmetu pp6', 2),
(925, '2011-08-31 18:41:57', 1, 'nastavnik/zavrsni predmet=6 ag=1', 1),
(926, '2011-08-31 18:42:00', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodaj_zavrsni', 1),
(927, '2011-08-31 18:42:10', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodaj_zavrsni subakcija=potvrda naziv=tema opis=dghsjjjjjjjhjd&lt;jdsbdjb&lt;c,bjcjcv,ljbnvfs', 1),
(928, '2011-08-31 18:42:10', 1, 'dodana nova tema završnog rada na predmetu pp6', 2),
(929, '2011-08-31 18:42:12', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodjela_studenata', 1),
(930, '2011-08-31 18:42:18', 1, 'nastavnik/zavrsni predmet=6 ag=1 akcija=dodjela_studenata subakcija=dodaj zavrsni=7 dodaj=Upiši', 1),
(931, '2011-08-31 18:42:18', 1, 'student u0 prebacen sa teme završnog rada 4 na 7 (predmet pp6', 2),
(932, '2011-08-31 18:42:23', 1, 'student/intro', 1),
(933, '2011-08-31 18:42:33', 1, 'student/zavrsni', 1),
(934, '2011-08-31 18:42:33', 1, 'student ne slusa predmet pp0', 3),
(935, '2011-08-31 18:42:39', 1, 'logout', 1),
(936, '2011-08-31 18:42:44', 13, 'login', 1),
(937, '2011-08-31 18:42:44', 13, '/zamger/index.php?loginforma=1 login=maja', 1),
(938, '2011-08-31 18:42:50', 13, 'student/predmet predmet=2 ag=1 sm_arhiva=0', 1),
(939, '2011-08-31 18:42:57', 13, 'student/predmet predmet=6 ag=1 sm_arhiva=0', 1),
(940, '2011-08-31 18:43:01', 13, 'student/zavrsni', 1),
(941, '2011-08-31 18:43:01', 13, 'student ne slusa predmet pp0', 3),
(942, '2011-08-31 18:43:07', 13, 'student/predmet predmet=6 ag=1 sm_arhiva=0', 1),
(943, '2011-08-31 18:43:09', 13, 'student/predmet predmet=2 ag=1 sm_arhiva=0', 1),
(944, '2011-08-31 18:47:00', 0, 'index.php greska: Pogrešna šifra studentska ', 3),
(945, '2011-08-31 18:47:15', 6, 'login', 1),
(946, '2011-08-31 18:47:15', 6, '/zamger/index.php?loginforma=1 login=studentska', 1),
(947, '2011-08-31 18:47:17', 6, 'studentska/osobe', 1),
(948, '2011-08-31 18:47:20', 6, 'studentska/predmeti', 1),
(949, '2011-08-31 18:47:29', 6, 'studentska/predmeti akcija=novi naziv=Jedan predmet', 1),
(950, '2011-08-31 18:47:29', 6, 'potpuno novi predmet pp7, akademska godina ag1', 4),
(951, '2011-08-31 18:47:31', 6, 'studentska/predmeti akcija=edit predmet=7 ag=1', 1),
(952, '2011-08-31 18:47:32', 6, 'studentska/predmeti predmet=7 ag=1 akcija=realedit', 1),
(953, '2011-08-31 18:47:40', 6, 'studentska/predmeti predmet=7 ag=1 akcija=realedit', 1),
(954, '2011-08-31 18:47:40', 6, 'izmijenjeni podaci o predmetu pp7', 4),
(955, '2011-08-31 18:47:42', 6, 'studentska/predmeti akcija=edit predmet=7 ag=1', 1),
(956, '2011-08-31 18:47:48', 6, 'studentska/predmeti predmet=7 ag=1 akcija=dodaj_pk', 1),
(957, '2011-08-31 18:47:52', 6, 'studentska/predmeti predmet=7 ag=1 akcija=dodaj_pk', 1),
(958, '2011-08-31 18:47:53', 6, 'dodana ponuda kursa na predmet pp7', 4),
(959, '2011-08-31 18:47:54', 6, 'studentska/predmeti akcija=edit predmet=7 ag=1', 1),
(960, '2011-08-31 18:47:57', 6, 'studentska/predmeti predmet=7 ag=1 akcija=dodaj_pk', 1),
(961, '2011-08-31 18:48:01', 6, 'studentska/predmeti predmet=7 ag=1 akcija=dodaj_pk', 1),
(962, '2011-08-31 18:48:01', 6, 'dodana ponuda kursa na predmet pp7', 4),
(963, '2011-08-31 18:48:03', 6, 'studentska/predmeti akcija=edit predmet=7 ag=1', 1),
(964, '2011-08-31 18:48:06', 6, 'studentska/predmeti predmet=7 ag=1 akcija=dodaj_pk', 1),
(965, '2011-08-31 18:48:10', 6, 'studentska/predmeti akcija=edit predmet=7 ag=1', 1),
(966, '2011-08-31 18:48:17', 6, 'studentska/predmeti predmet=7 ag=1 akcija=edit subakcija=dodaj_nastavnika nastavnik=5', 1),
(967, '2011-08-31 18:48:17', 6, 'nastavnik u5 dodan na predmet pp7', 4),
(968, '2011-08-31 18:48:20', 6, 'studentska/predmeti predmet=7 ag=1 subakcija=postavi_nivo_pristupa nastavnik=5 akcija=edit nivo_pristupa=nastavnik', 1),
(969, '2011-08-31 18:48:20', 6, 'nastavnik u5 dat nivo ''nastavnik'' na predmetu pp7', 4),
(970, '2011-08-31 18:48:27', 6, 'studentska/predmeti predmet=7 ag=1 subakcija=dodaj_nastavnika nastavnik=7 nivo_pristupa=nastavnik akcija=edit', 1),
(971, '2011-08-31 18:48:28', 6, 'nastavnik u7 dodan na predmet pp7', 4),
(972, '2011-08-31 18:48:30', 6, 'studentska/predmeti predmet=7 ag=1 subakcija=postavi_nivo_pristupa nastavnik=7 nivo_pristupa=asistent akcija=edit', 1),
(973, '2011-08-31 18:48:30', 6, 'nastavnik u7 dat nivo ''asistent'' na predmetu pp7', 4),
(974, '2011-08-31 18:48:34', 6, 'izvjestaj/grupe predmet=7 ag=1', 1),
(975, '2011-08-31 18:48:42', 6, 'studentska/predmeti predmet=7 ag=1 subakcija=postavi_nivo_pristupa nastavnik=7 nivo_pristupa=asistent akcija=edit', 1),
(976, '2011-08-31 18:48:42', 6, 'nastavnik u7 dat nivo ''asistent'' na predmetu pp7', 4),
(977, '2011-08-31 18:48:46', 6, 'studentska/osobe', 1),
(978, '2011-08-31 18:48:58', 6, 'studentska/osobe akcija=novi ime=studentic prezime=studentski', 1),
(979, '2011-08-31 18:48:59', 6, 'dodan novi korisnik u14 (ID: 14)', 4),
(980, '2011-08-31 18:49:01', 6, 'studentska/osobe akcija=edit osoba=14', 1),
(981, '2011-08-31 18:49:14', 6, 'studentska/osobe akcija=edit osoba=14 subakcija=auth stari_login= login=123 password=123 aktivan=1', 1),
(982, '2011-08-31 18:49:14', 6, 'dodan novi login ''123'' za korisnika u14', 4),
(983, '2011-08-31 18:49:26', 6, 'studentska/osobe akcija=podaci osoba=14 subakcija=auth stari_login= login=123 password=123 aktivan=1', 1),
(984, '2011-08-31 18:50:45', 6, 'studentska/osobe akcija=podaci osoba=14 subakcija=potvrda stari_login= login=123 password=123 aktivan=1 ime=studentic prezime=studentski spol=M jmbg=1203214568745 nacionalnost=Bošnjak/Bošnjakinja brindexa=123 imeoca=tata prezimeoca=prezimic imemajke=mama ', 1),
(985, '2011-08-31 18:50:45', 6, 'promijenjeni licni podaci korisnika u14', 4),
(986, '2011-08-31 18:50:46', 6, 'studentska/osobe osoba=14 akcija=edit', 1),
(987, '2011-08-31 18:50:51', 6, 'studentska/osobe', 1),
(988, '2011-08-31 18:50:56', 6, 'studentska/osobe search=sve', 1),
(989, '2011-08-31 18:51:03', 6, 'studentska/osobe search=sve akcija=edit osoba=14', 1),
(990, '2011-08-31 18:51:10', 6, 'studentska/osobe search=sve akcija=edit osoba=14', 1),
(991, '2011-08-31 18:51:17', 6, 'studentska/predmeti', 1),
(992, '2011-08-31 18:51:22', 6, 'logout', 1),
(993, '2011-08-31 18:51:26', 5, 'login', 1),
(994, '2011-08-31 18:51:26', 5, '/zamger/index.php?loginforma=1 login=nastavnik', 1),
(995, '2011-08-31 18:51:26', 5, 'common/savjet_dana', 1),
(996, '2011-08-31 18:51:33', 5, 'nastavnik/predmet predmet=7 ag=1', 1),
(997, '2011-08-31 18:51:35', 5, 'nastavnik/zavrsni predmet=7 ag=1', 1),
(998, '2011-08-31 18:51:36', 5, 'nastavnik/zavrsni predmet=7 ag=1 akcija=dodjela_studenata', 1),
(999, '2011-08-31 18:51:44', 5, 'nastavnik/predmet predmet=7 ag=1', 1),
(1000, '2011-08-31 18:51:47', 5, 'logout', 1),
(1001, '2011-08-31 18:51:52', 14, 'login', 1),
(1002, '2011-08-31 18:51:52', 14, 'index.php greska: Vaše korisničko ime je ispravno, ali nemate nikakve privilegije na sistemu! Kontaktirajte administratora. 123 ', 3),
(1003, '2011-08-31 18:51:52', 14, '/zamger/index.php?loginforma=1 login=123', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mjesto`
--

CREATE TABLE IF NOT EXISTS `mjesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(40) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `drzava` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=80 ;

--
-- Dumping data for table `mjesto`
--

INSERT INTO `mjesto` (`id`, `naziv`, `opcina`, `drzava`) VALUES
(1, 'Sarajevo', 0, 1),
(2, 'Sarajevo', 13, 1),
(3, 'Zenica', 77, 1),
(4, 'Mostar', 46, 1),
(5, 'Banja Luka', 93, 1),
(6, 'Bihać', 2, 1),
(7, 'Tuzla', 69, 1),
(79, 'Mostar', 16, 5);

-- --------------------------------------------------------

--
-- Table structure for table `moodle_predmet_id`
--

CREATE TABLE IF NOT EXISTS `moodle_predmet_id` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `moodle_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `moodle_predmet_rss`
--


-- --------------------------------------------------------

--
-- Table structure for table `nacin_studiranja`
--

CREATE TABLE IF NOT EXISTS `nacin_studiranja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `nacin_studiranja`
--

INSERT INTO `nacin_studiranja` (`id`, `naziv`) VALUES
(1, 'Redovan'),
(2, 'Paralelan'),
(3, 'Redovan samofinansirajući'),
(0, 'Nepoznat status');

-- --------------------------------------------------------

--
-- Table structure for table `nacionalnost`
--

CREATE TABLE IF NOT EXISTS `nacionalnost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

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
  PRIMARY KEY (`nastavnik`,`akademska_godina`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `nastavnik_predmet`
--

INSERT INTO `nastavnik_predmet` (`nastavnik`, `akademska_godina`, `predmet`, `nivo_pristupa`) VALUES
(3, 1, 3, 'nastavnik'),
(7, 1, 2, 'super_asistent'),
(5, 1, 2, 'nastavnik'),
(7, 1, 3, 'asistent'),
(3, 1, 4, 'super_asistent'),
(5, 1, 4, 'nastavnik'),
(7, 1, 1, 'asistent'),
(3, 1, 1, 'nastavnik'),
(3, 1, 5, 'asistent'),
(5, 1, 5, 'nastavnik'),
(7, 1, 6, 'asistent'),
(12, 1, 6, 'nastavnik'),
(5, 1, 7, 'nastavnik'),
(7, 1, 7, 'asistent');

-- --------------------------------------------------------

--
-- Table structure for table `naucni_stepen`
--

CREATE TABLE IF NOT EXISTS `naucni_stepen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

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

CREATE TABLE IF NOT EXISTS `oblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institucija` int(11) NOT NULL,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `oblast`
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `odluka`
--


-- --------------------------------------------------------

--
-- Table structure for table `ogranicenje`
--

CREATE TABLE IF NOT EXISTS `ogranicenje` (
  `nastavnik` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=145 ;

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
  `email` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
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
  `strucni_stepen` int(11) NOT NULL,
  `naucni_stepen` int(11) NOT NULL,
  `slika` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `osoba`
--

INSERT INTO `osoba` (`id`, `ime`, `prezime`, `imeoca`, `prezimeoca`, `imemajke`, `prezimemajke`, `spol`, `email`, `brindexa`, `datum_rodjenja`, `mjesto_rodjenja`, `nacionalnost`, `drzavljanstvo`, `boracke_kategorije`, `jmbg`, `adresa`, `adresa_mjesto`, `telefon`, `kanton`, `treba_brisati`, `strucni_stepen`, `naucni_stepen`, `slika`) VALUES
(1, 'Site', 'Admin', '', '', '', '', 'M', 'site@admin.com', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(2, 'Niko', 'Neznanovic', '', '', '', '', 'M', '', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(3, 'Mujo', 'Mujic', '', '', '', '', 'M', '', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(4, 'Student', 'Student', '', '', '', '', 'M', '', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(5, 'Nastavnik', 'Nastavnik', '', '', '', '', 'M', '', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(6, 'Studentska', 'Sluzba', '', '', '', '', 'M', '', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(7, 'Asistent', 'Asistent', '', '', '', '', 'M', '', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(8, 'Administrator', 'Stranice', '', '', '', '', 'M', '', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(9, 'Prva', 'Godina', 'Otac', 'Nesto', 'Majka', 'Nesto', 'M', 'arnela.kozar@gmail.com', '15865', '1992-02-03', 0, 6, 1, 0, '1506988197856', 'Fatmic L1', 0, '062 187 377', 6, 0, 2, 6, ''),
(10, 'Arnela', 'Kozar', 'Senad', 'Kozar', 'Borka', 'Kozar', 'Z', 'arnela.kozar@gmail.com', '15856', '1992-02-03', 0, 1, 1, 0, '0602992198067', 'Fatmic L1', 0, '062 187 377', 6, 0, 2, 6, ''),
(11, 'Maja ', 'Kozar', 'Senad', 'Kozar', 'Borka', 'Kozar', 'Z', 'maja.kozar88@gmail.com', '15000', '1988-06-15', 0, 1, 1, 0, '1506988198069', 'Fatmic 1', 0, '061 452 128', 6, 0, 2, 6, ''),
(12, 'Profesor', 'Zavrsni', '', '', '', '', 'M', '', '', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(13, 'Testirani', 'Student', '', '', '', '', 'M', '', '123456', '0000-00-00', 0, 0, 0, 0, '', '', 0, '', 0, 0, 0, 0, ''),
(14, 'studentic', 'studentski', 'tata', 'prezimic', 'mama', 'prezimic', 'M', 'mail@gmail.com', '123', '1214-03-12', 79, 1, 7, 0, '1203214568745', 'jjfjfj jfjfj ', 5, '123 456 789', 10, 0, 2, 6, '');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `plan_studija`
--

INSERT INTO `plan_studija` (`godina_vazenja`, `studij`, `semestar`, `predmet`, `obavezan`) VALUES
(1, 1, 1, 1, 1),
(1, 2, 3, 2, 1),
(1, 4, 3, 3, 1),
(1, 3, 4, 4, 1),
(1, 5, 6, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `podoblast`
--

CREATE TABLE IF NOT EXISTS `podoblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oblast` int(11) NOT NULL,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `podoblast`
--


-- --------------------------------------------------------

--
-- Table structure for table `ponudakursa`
--

CREATE TABLE IF NOT EXISTS `ponudakursa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `predmet` int(11) NOT NULL DEFAULT '0',
  `studij` int(11) NOT NULL DEFAULT '0',
  `semestar` int(11) NOT NULL DEFAULT '0',
  `obavezan` tinyint(1) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=11 ;

--
-- Dumping data for table `ponudakursa`
--

INSERT INTO `ponudakursa` (`id`, `predmet`, `studij`, `semestar`, `obavezan`, `akademska_godina`) VALUES
(1, 1, 1, 1, 1, 1),
(2, 2, 2, 3, 1, 1),
(3, 3, 4, 3, 1, 1),
(4, 4, 3, 4, 0, 1),
(5, 5, 5, 6, 1, 1),
(6, 2, 3, 1, 1, 1),
(7, 6, 3, 4, 1, 1),
(8, 6, 3, 1, 1, 1),
(9, 7, 3, 1, 1, 1),
(10, 7, 3, 2, 1, 1);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `poruka`
--


-- --------------------------------------------------------

--
-- Table structure for table `predmet`
--

CREATE TABLE IF NOT EXISTS `predmet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sifra` varchar(20) COLLATE utf8_slovenian_ci NOT NULL,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `institucija` int(11) NOT NULL DEFAULT '0',
  `kratki_naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  `ects` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=8 ;

--
-- Dumping data for table `predmet`
--

INSERT INTO `predmet` (`id`, `sifra`, `naziv`, `institucija`, `kratki_naziv`, `tippredmeta`, `ects`) VALUES
(1, '1', 'Inženjerska matematika 1', 1, 'IM1', 1, 7),
(2, '2', 'Diskretna matematika', 1, 'DM', 1, 7),
(3, '3', 'Elektricčni krugovi II', 1, 'EKII', 1, 7),
(4, '5', 'Elektronika', 1, 'E', 1, 5),
(5, '6', 'Osnove telekomunikacija', 1, 'OT', 1, 3),
(6, '123456789', 'Pokusaj predmeta', 1, 'PP', 1, 12),
(7, '14568', 'Jedan predmet', 1, 'JP', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `predmet_parametri_zavrsni`
--

CREATE TABLE IF NOT EXISTS `predmet_parametri_zavrsni` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `min_tema` tinyint(3) NOT NULL,
  `max_tema` tinyint(3) NOT NULL,
  `min_clanova` tinyint(3) NOT NULL,
  `max_clanova` tinyint(3) NOT NULL,
  `zakljucani_zavrsni` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `predmet_parametri_zavrsni`
--

INSERT INTO `predmet_parametri_zavrsni` (`predmet`, `akademska_godina`, `min_tema`, `max_tema`, `min_clanova`, `max_clanova`, `zakljucani_zavrsni`) VALUES
(2, 1, 1, 11, 1, 3, 1),
(2, 1, 1, 11, 1, 3, 0),
(2, 1, 1, 11, 1, 3, 0),
(2, 1, 2, 11, 1, 3, 0),
(2, 1, 2, 8, 1, 5, 0),
(2, 1, 1, 11, 1, 3, 0),
(2, 1, 1, 11, 1, 3, 0),
(2, 1, 1, 5, 1, 2, 0),
(2, 1, 1, 11, 1, 3, 0),
(3, 1, 1, 6, 1, 3, 0),
(1, 1, 1, 6, 1, 2, 0),
(5, 1, 1, 5, 1, 2, 0),
(6, 1, 1, 6, 1, 3, 0),
(6, 1, 1, 8, 1, 3, 0),
(2, 1, 1, 11, 1, 3, 0),
(4, 1, 1, 2, 3, 4, 0),
(6, 1, 1, 6, 1, 3, 0);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `predmet_projektni_parametri`
--

INSERT INTO `predmet_projektni_parametri` (`predmet`, `akademska_godina`, `min_timova`, `max_timova`, `min_clanova_tima`, `max_clanova_tima`, `zakljucani_projekti`) VALUES
(2, 1, 1, 11, 2, 5, 0),
(5, 1, 2, 3, 1, 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `preference`
--

CREATE TABLE IF NOT EXISTS `preference` (
  `korisnik` int(11) NOT NULL,
  `preferenca` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijednost` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`korisnik`,`preferenca`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `preference`
--


-- --------------------------------------------------------

--
-- Table structure for table `prijemni_prijava`
--

CREATE TABLE IF NOT EXISTS `prijemni_prijava` (
  `prijemni_termin` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `broj_dosjea` int(11) NOT NULL,
  `redovan` tinyint(1) NOT NULL DEFAULT '1',
  `studij_prvi` int(11) NOT NULL,
  `studij_drugi` int(11) NOT NULL,
  `studij_treci` int(11) NOT NULL,
  `studij_cetvrti` int(11) NOT NULL,
  `izasao` tinyint(1) NOT NULL,
  `rezultat` double NOT NULL,
  PRIMARY KEY (`prijemni_termin`,`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `prisustvo`
--

INSERT INTO `prisustvo` (`student`, `cas`, `prisutan`, `plus_minus`) VALUES
(11, 1, 1, 0),
(11, 2, 1, 0),
(11, 3, 1, 0),
(11, 4, 1, 0),
(11, 5, 1, 0),
(11, 6, 1, 0),
(11, 7, 1, 0),
(11, 8, 1, 0),
(11, 9, 1, 0),
(11, 10, 1, 0),
(11, 11, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `privilegije`
--

CREATE TABLE IF NOT EXISTS `privilegije` (
  `osoba` int(11) NOT NULL,
  `privilegija` varchar(30) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `privilegije`
--

INSERT INTO `privilegije` (`osoba`, `privilegija`) VALUES
(1, 'siteadmin'),
(1, 'studentska'),
(1, 'student'),
(1, 'nastavnik'),
(3, 'siteadmin'),
(3, 'studentska'),
(3, 'nastavnik'),
(3, 'student'),
(7, 'nastavnik'),
(5, 'nastavnik'),
(6, 'studentska'),
(8, 'siteadmin'),
(4, 'student'),
(9, 'student'),
(10, 'student'),
(11, 'student'),
(12, 'nastavnik'),
(13, 'student');

-- --------------------------------------------------------

--
-- Table structure for table `programskijezik`
--

CREATE TABLE IF NOT EXISTS `programskijezik` (
  `id` int(10) NOT NULL DEFAULT '0',
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `geshi` varchar(20) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `ekstenzija` varchar(10) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
-- Dumping data for table `projekat`
--

INSERT INTO `projekat` (`id`, `naziv`, `predmet`, `akademska_godina`, `opis`, `biljeska`, `vrijeme`) VALUES
(1, 'Nesto', 2, 1, 'Cccccccccccccccccccccccc', NULL, '2011-08-23 19:19:39'),
(2, 'Test1', 2, 1, 'CCCCCCCCCCCCCCCCCCCCCCCCCCCCCcccccccccccccccccccccccCCCCCCCCCCCCCCC', NULL, '2011-08-29 16:38:20'),
(3, 'projekat', 5, 1, 'project', NULL, '2011-08-29 18:53:58');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
  `email` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `brindexa` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `datum_rodjenja` date NOT NULL,
  `mjesto_rodjenja` int(11) NOT NULL,
  `drzavljanstvo` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `jmbg` varchar(14) COLLATE utf8_slovenian_ci NOT NULL,
  `adresa` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `adresa_mjesto` int(11) NOT NULL,
  `telefon` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `kanton` int(11) NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `dodatni_bodovi` double NOT NULL,
  PRIMARY KEY (`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `raspored`
--

INSERT INTO `raspored` (`id`, `studij`, `akademska_godina`, `semestar`) VALUES
(1, 1, 1, 1);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `rss`
--

INSERT INTO `rss` (`id`, `auth`, `access`) VALUES
('GFAdS7kXeh', 1, '0000-00-00 00:00:00'),
('HytArQBALc', 4, '0000-00-00 00:00:00'),
('pg5Gz8BMp8', 13, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `savjet_dana`
--

CREATE TABLE IF NOT EXISTS `savjet_dana` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  `vrsta_korisnika` enum('nastavnik','student','studentska','siteadmin') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'nastavnik',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=21 ;

--
-- Dumping data for table `savjet_dana`
--

INSERT INTO `savjet_dana` (`id`, `tekst`, `vrsta_korisnika`) VALUES
(1, '<p>...da je Charles Babbage, matematičar i filozof iz 19. vijeka za kojeg se smatra da je otac ideje prvog programabilnog računara, u svojoj biografiji napisao:</p>\r\n\r\n<p><i>U dva navrata su me pitali</i></p>\r\n\r\n<p><i>"Molim Vas gospodine Babbage, ako u Vašu mašinu stavite pogrešne brojeve, da li će izaći tačni odgovori?"</i></p>\r\n\r\n<p><i>Jednom je to bio pripadnik Gornjeg, a jednom Donjeg doma. Ne mogu da potpuno shvatim tu vrstu konfuzije ideja koja bi rezultirala takvim pitanjem.</i></p>', 'nastavnik'),
(2, '<p>...da sada možete podesiti sistem bodovanja na vašem predmetu (broj bodova koje studenti dobijaju za ispite, prisustvo, zadaće, seminarski rad, projekte...)?</p>\r\n<ul><li>Kliknite na dugme [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite opciju <i>Sistem bodovanja</i>.</li>\r\n<li>Slijedite uputstva.</li></ul>\r\n<p><b>Važna napomena:</b> Promjena sistema bodovanja može dovesti do gubitka do sada upisanih bodova na predmetu!</p>', 'nastavnik'),
(3, '<p>...da možete pristupiti Dosjeu studenta sa svim podacima koji se tiču uspjeha studenta na datom predmetu? Dosje studenta sadrži, između ostalog:</p>\r\n<ul><li>Fotografiju studenta;</li>\r\n<li>Koliko puta je student ponavljao predmet, da li je u koliziji, da li je prenio predmet na višu godinu;</li>\r\n<li>Sve podatke sa pogleda grupe (prisustvo, zadaće, rezultati ispita, konačna ocjena) sa mogućnošću izmjene svakog podatka;</li>\r\n<li>Za ispite i konačnu ocjenu možete vidjeti dnevnik izmjena sa informacijom ko je i kada izmijenio podatak.</li>\r\n<li>Brze linkove na dosjee istog studenta sa ranijih akademskih godina (ako je ponavljao/la predmet).</li></ul>\r\n\r\n<p>Dosjeu studenta možete pristupiti tako što kliknete na ime studenta u pregledu grupe. Na vašem početnom ekranu kliknite na ime grupe ili link <i>(Svi studenti)</i>, a zatim na ime i prezime studenta.</p>\r\n	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik'),
(4, '<p>...da možete ostavljati kratke tekstualne komentare na rad studenata?</p>\r\n<p>Na vašem početnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>. Zatim kliknite na ikonu sa oblačićem pored imena studenta:<br>\r\n<img src="/images/16x16/komentar-plavi.png" width="16" height="16"></p>\r\n<p>Možete dobiti pregled studenata sa komentarima na sljedeći način:<br>\r\n<ul><li>Pored naziva predmeta kliknite na link [EDIT].</li>\r\n<li>Zatim s lijeve strane kliknite na link <i>Izvještaji</i>.</li>\r\n<li>Konačno, kliknite na opciju <i>Spisak studenata</i> - <i>Sa komentarima na rad</i>.</li></ul>\r\n<p>Na istog studenta možete ostaviti više komentara pri čemu je svaki komentar datiran i označeno je ko ga je ostavio.</p>	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 7-8.</i></p>', 'nastavnik'),
(5, '<p>...da možete brzo i lako pomoću nekog spreadsheet programa (npr. MS Excel) kreirati grupe na predmetu?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"><br>\r\nDobićete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte naziv grupe npr. "Grupa 1" (bez navodnika). Koristite Copy i Paste opcije Excela da biste brzo definisali grupu za sve studente.</li>\r\n<li>Kada završite definisanje grupa, koristeći tipku Shift i tipke sa strelicama označite imena studenata i imena grupa. Nemojte označiti naslov niti redni broj. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja. Sada s lijeve strane izaberite opciju <i>Grupe za predavanja i vježbe</i>.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos studenata u grupe</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li></ul>\r\n<p>Ovim su grupe kreirane!</p>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 16.</i></p>', 'nastavnik'),
(6, '<p>...da možete brzo i lako ocijeniti zadaću svim studentima na predmetu ili u grupi, koristeći neki spreadsheet program (npr. MS Excel)?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, a s desne izaberite izvještaj <i>Spisak studenata</i> - <i>Bez grupa</i>. Alternativno, ako želite unositi ocjene samo za jednu grupu, možete koristiti izvještaj <i>Jedna kolona po grupama</i> pa u Excelu pobrisati sve grupe osim one koja vas interesuje.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"></li>\r\n<li>Pored imena svakog studenta nalazi se broj indeksa. <b>Umjesto broja indeksa</b> upišite broj bodova ostvarenih na određenom zadatku određene zadaće.</li>\r\n<li>Korištenjem tipke Shift i tipki sa strelicama izaberite samo imena studenata i bodove. Nemojte selektovati naslov ili redne brojeve. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja. Sada s lijeve strane izaberite opciju <i>Kreiranje i unos zadaća</i>.</li>\r\n<li>Uvjerite se da je na spisku <i>Postojeće zadaće</i> definisana zadaća koju želite unijeti. Ako nije, popunite formular ispod naslova <i>Kreiranje zadaće</i> sa odgovarajućim podacima.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos zadaća</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>U polju <i>Izaberite zadaću</i> odaberite upravo kreiranu zadaću. Ako zadaća ima više zadataka, u polju <i>Izaberite zadatak</i> odaberite koji zadatak masovno unosite.\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li>\r\n<li>Ovu proceduru sada vrlo lako možete ponoviti za sve zadatke i sve zadaće zato što već imate u Excelu sve podatke osim broja bodova.</li></ul>\r\n<p>Ovim su rezultati zadaće uneseni za sve studente!</p>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 27-28.</i></p>', 'nastavnik'),
(12, '<p>...da možete ograničiti format datoteke u kojem studenti šalju zadaću?</p>\r\n<p>Prilikom kreiranja nove zadaće, označite opciju pod nazivom <i>Slanje zadatka u formi attachmenta</i>. Pojaviće se spisak tipova datoteka koje studenti mogu koristiti prilikom slanja zadaće u formi attachmenta.</p>\r\n<p>Izaberite jedan ili više formata kako bi studenti dobili grešku u slučaju da pokušaju poslati zadaću u nekom od formata koje niste izabrali. Ako ne izaberete nijednu od ponuđenih opcija, biće dozvoljeni svi formati datoteka, uključujući i one koji nisu navedeni na spisku.</p>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 26-27.</i></p>', 'nastavnik'),
(7, '<p>...da možete preuzeti odjednom sve zadaće koje su poslali studenti u grupi u formi ZIP fajla, pri čemu su zadaće imenovane po sistemu Prezime_Ime_BrojIndeksa?</p>\r\n<ul><li>Na vašem početnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>.</li>\r\n<li>U zaglavlju tabele sa spiskom studenata možete vidjeti navedene zadaće: npr. Zadaća 1, Zadaća 2 itd.</li>\r\n<li>Ispod naziva svake zadaće nalazi se riječ <i>Download</i> koja predstavlja link - kliknite na njega.</li></ul>	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 11-12.</i></p>', 'nastavnik'),
(8, '<p>...da možete imati više termina jednog ispita? Pri tome se datum termina ne mora poklapati sa datumom ispita.</p>\r\n<p>Datum ispita se daje samo okvirno, kako bi se po nečemu razlikovali npr. junski rok i septembarski rok. Datum koji studentu piše na prijavi je datum koji pridružite terminu za prijavu ispita.</p>\r\n<p>Da biste definisali termine ispita:</p>\r\n<ul><li>Najprije kreirajte ispit, tako što ćete kliknuti na link [EDIT] a zatim izabrati opciju Ispiti s lijeve strane. Zatim popunite formular ispod naslova <i>Kreiranje novog ispita</i>.</li>\r\n<li>U tabeli ispita možete vidjeti novi ispit. Desno od ispita možete vidjeti link <i>Termini</i>. Kliknite na njega.</li>\r\n<li>Zatim kreirajte proizvoljan broj termina popunjavajući formular ispod naslova <i>Registrovanje novog termina</i>.</li></ul>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, poglavlje "Prijavljivanje za ispit" (str. 21-26).</i></p>', 'nastavnik'),
(9, '<p>...da, u slučaju da se neki student nije prijavio/la za vaš ispit, možete ih manuelno prijaviti na termin kako bi imao/la korektan datum na prijavi?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta. S lijeve strane izaberite link <i>Ispiti</i>.</li>\r\n<li>U tabeli ispita locirajte ispit koji želite i kliknite na link <i>Termini</i> desno od željenog ispita.</li>\r\n<li>Ispod naslova <i>Objavljeni termini</i> izaberite željeni termin i kliknite na link <i>Studenti</i> desno od željenog termina.</li>\r\n<li>Sada možete vidjeti sve studente koji su se prijavili za termin. Pored imena i prezimena studenta možete vidjeti dugme <i>Izbaci</i> kako student više ne bi bio prijavljen za taj termin.</li>\r\n<li>Ispod tabele studenata možete vidjeti padajući spisak svih studenata upisanih na vaš predmet. Izaberite na padajućem spisku studenta kojeg želite prijaviti za termin i kliknite na dugme <i>Dodaj</i>.</li></ul>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 26.</i></p>', 'nastavnik'),
(10, '<p>...da upisom studenata na predmete u Zamgeru sada u potpunosti rukuje Studentska služba?</p>\r\n<p>Ako vam se pojavi student kojeg nemate na spiskovima u Zamgeru, recite mu da se <b>obavezno</b> javi u Studentsku službu, ne samo radi vašeg predmeta nego generalno radi regulisanja statusa (npr. neplaćenih školarina, taksi i slično).</p>', 'nastavnik'),
(11, '<p>...da svaki korisnik može imati jedan od tri nivoa pristupa bilo kojem predmetu:</p><ul><li><i>asistent</i> - može unositi prisustvo časovima i ocjenjivati zadaće</li><li><i>super-asistent</i> - može unositi sve podatke osim konačne ocjene</li><li><i>nastavnik</i> - može unositi i konačnu ocjenu.</li></ul><p>Početni nivoi pristupa se određuju na osnovu zvanično usvojenog nastavnog ansambla, a u slučaju da želite promijeniti nivo pristupa bez izmjena u ansamblu (npr. kako biste asistentu dali privilegije unosa rezultata ispita), kontaktirajte Studentsku službu.</p>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 3-4.</i></p>', 'nastavnik'),
(13, '<p>...da možete utjecati na format u kojem se izvještaj prosljeđuje Excelu kada kliknete na Excel ikonu u gornjem desnom uglu izvještaja?<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"></p>\r\n<p>Može se desiti da izvještaj ne izgleda potpuno kako treba u vašem spreadsheet programu. Podaci se šalju u CSV formatu pod pretpostavkom da koristite regionalne postavke za BiH (ili Hrvatsku ili Srbiju). Ako izvještaj u vašem programu ne izgleda kako treba, slijedi nekoliko savjeta kako možete utjecati na to.</p>\r\n<ul><li>Ako se svi podaci nalaze u jednoj koloni, vjerovatno je da koristite sistem sa Američkim regionalnim postavkama. U vašem Profilu možete pod Zamger opcije izabrati CSV separator "zarez" umjesto "tačka-zarez", ali vjerovatno je da vam naša slova i dalje neće izgledati kako treba.</li>\r\n<li>Moguće je da će dokument izgledati ispravno, osim slova sa afrikatima koja će biti zamijenjena nekim drugim. Na žalost, ne postoji način da se ovo riješi. Excel može učitati CSV datoteke isključivo u formatu koji ne podržava prikaz naših slova. Možete uraditi zamjenu koristeći Replace opciju vašeg programa. Nešto složenija varijanta je da koristite "Save Link As" opciju vašeg web preglednika, promijenite naziv dokumenta iz izvjestaj.csv u izvjestaj.txt, a zatim koristite <a href="http://office.microsoft.com/en-us/excel-help/text-import-wizard-HP010102244.aspx">Excel Text Import Wizard</a>.</li>\r\n<li>Ako koristite OpenOffice.org uredski paket, prilikom otvaranja dokumenta izaberite Text encoding "Eastern European (Windows-1250)", a kao razdjelnik (Delimiter) izaberite tačka-zarez (Semicolon). Ostale opcije obavezno isključite. Takođe isključite opciju spajanja razdjelnika (Merge delimiters).</li>\r\n<li>Može se desiti da vaš program prepozna određene stavke (npr. redne brojeve ili ostvarene bodove) kao datum, pogotovo ako ste poslušali savjet iz prve tačke - odnosno, ako ste kao CSV separator podesili "zarez".</li>\r\n<li>U velikoj većini slučajeva možete dobiti potpuno zadovoljavajuće rezultate ako otvorite prazan dokument u vašem spreadsheet programu (npr. Excel) i zatim napravite copy-paste kompletnog sadržaja web stranice.</li></ul>\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, strana 32-33.</i></p>', 'nastavnik'),
(14, '<p>...da možete brzo i lako pomoću nekog spreadsheet programa (npr. MS Excel) unijeti rezultate ispita ili konačne ocjene?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>. Ili, ako vam je lakše unositi podatke po grupama, izaberite izvještaj <i>Jedna kolona po grupama</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"><br>\r\nDobićete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte broj bodova koje je student ostvario na ispitu ili konačnu ocjenu.</li>\r\n<li>Kada završite unos rezultata/ocjena, koristeći tipku Shift i tipke sa strelicama označite imena studenata i ocjene. Nemojte označiti naslov niti redni broj studenta. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja.</li>\r\n<li>Ako unosite konačne ocjene, s lijeve strane izaberite opciju <i>Konačna ocjena</i>.</li>\r\n<li>Ako unosite rezultate ispita, s lijeve strane izaberite opciju <i>Ispiti</i>, kreirajte novi ispit, a zatim kliknite na link <i>Masovni unos rezultata</i> pored novokreiranog ispita.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos ocjena</i> i pritisnite Ctrl+V. Trebalo bi da ugledate rezultate ispita odnosno ocjene.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> (a ne Prezime[TAB]Ime), te da pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li></ul>\r\n<p>Ovim su unesene ocjene / rezultati ispita!</p>\r\n\r\n\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 18-20 (masovni unos ispita) i str. 28-29 (masovni unos konačne ocjene).</i></p>', 'nastavnik'),
(15, '<p>...da kod evidencije prisustva, pored stanja "prisutan" (zelena boja) i stanja "odsutan" (crvena boja) postoji i nedefinisano stanje (žuta boja). Ovo stanje se dodjeljuje ako je student upisan u grupu nakon što su održani određeni časovi.</p>\r\n<p>Drečavo žuta boja je odabrana kako bi se predmetni nastavnik odnosno asistent podsjetio da se mora odlučiti da li će studentu priznati časove kao prisustva ili ne. U međuvremenu, nedefinisano stanje će se tumačiti u korist studenta, odnosno neće ulaziti u broj izostanaka prilikom određivanja da li je student izgubio bodove za prisustvo.</p>\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik'),
(16, '<p>...da ne morate voditi evidenciju o prisustvu kroz Zamger ako ne želite, a i dalje možete imati ažuran broj bodova ostvarenih na prisustvo?</p>\r\n<p>Sistem bodovanja je takav da student dobija 10 bodova ako je odsustvovao manje od 4 puta, a 0 bodova ako je odsustvovao 4 ili više puta. Podaci o konkretnim održanim časovima u Zamgeru se ne koriste nigdje osim za internu evidenciju na predmetu.</p>\r\n<p>Dakle, u slučaju da imate vlastitu evidenciju, samo kreirajte četiri časa (datum je nebitan) i unesite četiri izostanka studentima koji nisu zadovoljili prisustvo.</p>	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 4-5.</i></p>', 'nastavnik'),
(17, '<p>...da možete podesiti drugačiji sistem bodovanja za prisustvo od ponuđenog?</p>\r\n<p>Možete podesiti ukupan broj bodova za prisustvo (različit od 10). Možete promijeniti maksimalan broj dozvoljenih izostanaka (različit od 3) ili pak podesiti linearno bodovanje u odnosu na broj izostanaka (npr. ako je student od 14 časova izostao 2 puta, dobiće (12/14)*10 = 8,6 bodova). Konačno, umjesto evidencije pojedinačnih časova, možete odabrati da direktno unosite broj bodova za prisustvo po uzoru na rezultate ispita.</p>\r\n<p>Da biste aktivirali ovu mogućnost, trebate promijeniti sistem bodovanja samog predmeta.</p>', 'nastavnik'),
(18, '<p>...da možete unijeti bodove za zadaću čak i ako je student nije poslao kroz Zamger?</p>\r\n<p>Da biste to uradili, potrebno je da kliknete na link <i>Prikaži dugmad za kreiranje zadataka</i> koji se nalazi u dnu stranice sa prikazom grupe (vidi sliku). Nakon što ovo uradite, ćelije tabele koje odgovaraju neposlanim zadaćama će se popuniti ikonama za kreiranje zadaće koje imaju oblik sijalice.</p>\r\n<p><a href="doc/savjet_sijalice.png" target="_new">Slika</a> - ukoliko ne vidite detalje, raširite prozor!</p>	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 10-11.</i></p>\r\n<p>U slučaju da se na vašem predmetu zadaće generalno ne šalju kroz Zamger, vjerovatno će brži način rada za vas biti da koristite masovni unos. Više informacija na str. 27-28. Uputstava.</p>', 'nastavnik'),
(19, '<p>...da pomoću Zamgera možete poslati cirkularni mail svim studentima na vašem predmetu ili u pojedinim grupama?</p>\r\n<p>Da biste pristupili ovoj opciji:</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta</li>\r\n<li>U meniju sa lijeve strane odaberite opciju <i>Obavještenja za studente</i>.</li>\r\n<li>Pod menijem <i>Obavještenje za:</i> odaberite da li obavještenje šaljete svim studentima na predmetu ili samo studentima koji su članovi određene grupe.</li>\r\n<li>Aktivirajte opciju <i>Slanje e-maila</i>. Ako ova opcija nije aktivna, studenti će i dalje vidjeti vaše obavještenje na svojoj Zamger početnoj stranici (sekcija Obavještenja) kao i putem RSSa.</li>\r\n<li>U dio pod naslovom <i>Kraći tekst</i> unesite udarnu liniju vaše informacije.</li>\r\n<li>U dio pod naslovom <i>Detaljan tekst</i> možete napisati dodatna pojašnjenja, a možete ga i ostaviti praznim.</li>\r\n<li>Kliknite na dugme <i>Pošalji</i>. Vidjećete jedno po jedno ime studenta kojem je poslan mail kao i e-mail adresu na koju je mail poslan. Slanje veće količine mailova može potrajati nekoliko minuta.</li></ul>\r\n<p>Mailovi će biti poslani na adrese koje su studenti podesili koristeći svoj profil, ali i na zvanične fakultetske adrese.</p>\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 12-14.</i></p>', 'nastavnik'),
(20, '<p>...da je promjena grupe studenta destruktivna operacija kojom se nepovratno gube podaci o prisustvu studenta na časovima registrovanim za tu grupu?</p>\r\n<p>Studenta možete prebaciti u drugu grupu putem ekrana Dosje studenta: na pogledu grupe (npr. <i>Svi studenti</i>) kliknite na ime i prezime studenta da biste ušli u njegov ili njen dosje.</p>\r\n<p>Promjenom grupe nepovratno se gubi evidencija prisustva studenta na časovima registrovanim za prethodnu grupu. Naime, između časova registrovanih za dvije različite grupe ne postoji jednoznačno mapiranje. U nekom datom trenutku vremena u jednoj grupi može biti registrovano 10 časova a u drugoj 8. Kako znati koji od tih 10 časova odgovara kojem od onih 8? I šta raditi sa suvišnim časovima? Dakle, kada premjestite studenta u grupu u kojoj već postoje registrovani časovi, prisustvo studenta tim časovima će biti označeno kao nedefinisano (žuta boja). Prepušta se nastavnom ansamblu da odluči koje od tih časova će priznati kao prisutne, a koje markirati kao odsutne. Vjerovatno ćete se pitati šta ako se student ponovo vrati u polaznu grupu. Odgovor je da će podaci ponovo biti izgubljeni, jer šta raditi sa časovima registrovanim u međuvremenu?</p>\r\n<p>Preporučujemo da ne vršite promjene grupe nakon što počne akademska godina.</p>\r\n	\r\n<p><i>Više informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik');

-- --------------------------------------------------------

--
-- Table structure for table `septembar`
--

CREATE TABLE IF NOT EXISTS `septembar` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `septembar`
--


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stdin`
--


-- --------------------------------------------------------

--
-- Table structure for table `strucni_stepen`
--

CREATE TABLE IF NOT EXISTS `strucni_stepen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

--
-- Dumping data for table `strucni_stepen`
--

INSERT INTO `strucni_stepen` (`id`, `naziv`, `titula`) VALUES
(1, 'Magistar elektrotehnike - Diplomirani inženjer elektrotehnike', 'M.E.'),
(2, 'Bakalaureat elektrotehnike - Inženjer elektrotehnike', 'B.E.'),
(3, 'Diplomirani inženjer elektrotehnike', 'dipl.ing.el.'),
(4, 'Diplomirani matematičar', 'dipl.mat.'),
(5, 'Srednja stručna sprema', ''),
(6, 'Diplomirani inženjer mašinstva', 'dipl.ing.'),
(7, 'Diplomirani inženjer građevinarstva', 'dipl.ing.'),
(8, 'Diplomirani ekonomista', 'dipl.ecc.');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `studentski_modul`
--

INSERT INTO `studentski_modul` (`id`, `modul`, `gui_naziv`, `novi_prozor`) VALUES
(1, 'student/moodle', 'Materijali (Moodle)', 1),
(2, 'student/zadace', 'Slanje zadaća', 0),
(3, 'izvjestaj/predmet', 'Dnevnik', 1),
(4, 'student/projekti', 'Projekti', 0),
(5, 'student/zavrsni', 'Završni radovi', 1);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `studentski_modul_predmet`
--

INSERT INTO `studentski_modul_predmet` (`predmet`, `akademska_godina`, `studentski_modul`, `aktivan`) VALUES
(2, 1, 1, 1),
(2, 1, 2, 1),
(2, 1, 3, 1),
(2, 1, 4, 1),
(2, 1, 5, 1),
(3, 1, 5, 1),
(1, 1, 5, 1),
(5, 1, 5, 1),
(6, 1, 1, 1),
(6, 1, 3, 1),
(6, 1, 4, 1),
(6, 1, 5, 1),
(6, 1, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_ispit_termin`
--

CREATE TABLE IF NOT EXISTS `student_ispit_termin` (
  `student` int(11) NOT NULL,
  `ispit_termin` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `student_ispit_termin`
--


-- --------------------------------------------------------

--
-- Table structure for table `student_labgrupa`
--

CREATE TABLE IF NOT EXISTS `student_labgrupa` (
  `student` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_labgrupa`
--

INSERT INTO `student_labgrupa` (`student`, `labgrupa`) VALUES
(10, 3),
(11, 4),
(11, 3),
(11, 2),
(11, 5),
(13, 2),
(13, 6);

-- --------------------------------------------------------

--
-- Table structure for table `student_predmet`
--

CREATE TABLE IF NOT EXISTS `student_predmet` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY (`student`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_predmet`
--

INSERT INTO `student_predmet` (`student`, `predmet`) VALUES
(10, 3),
(11, 2),
(11, 3),
(11, 4),
(11, 5),
(13, 6),
(13, 8);

-- --------------------------------------------------------

--
-- Table structure for table `student_projekat`
--

CREATE TABLE IF NOT EXISTS `student_projekat` (
  `student` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY (`student`,`projekat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_projekat`
--

INSERT INTO `student_projekat` (`student`, `projekat`) VALUES
(11, 2);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_studij`
--

INSERT INTO `student_studij` (`student`, `studij`, `semestar`, `akademska_godina`, `nacin_studiranja`, `ponovac`, `odluka`, `plan_studija`) VALUES
(9, 3, 1, 1, 1, 0, 0, 1),
(10, 4, 1, 1, 1, 0, 0, 1),
(11, 2, 1, 1, 1, 0, 0, 1),
(13, 3, 1, 1, 1, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_zavrsni`
--

CREATE TABLE IF NOT EXISTS `student_zavrsni` (
  `student` int(11) NOT NULL,
  `zavrsni` int(11) NOT NULL,
  PRIMARY KEY (`student`,`zavrsni`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_zavrsni`
--

INSERT INTO `student_zavrsni` (`student`, `zavrsni`) VALUES
(0, 7);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `studij`
--

INSERT INTO `studij` (`id`, `naziv`, `zavrsni_semestar`, `institucija`, `kratkinaziv`, `moguc_upis`, `tipstudija`, `preduslov`) VALUES
(1, 'Prva godina studija', 2, 1, 'PGS', 0, 1, 0),
(2, 'Računarstvo i informatika (BSc)', 6, 2, 'RI', 1, 2, 1),
(3, 'Automatika i elektronika (BSc)', 6, 3, 'AE', 1, 2, 1),
(4, 'Elektroenergetika (BSc)', 6, 4, 'EE', 1, 2, 1),
(5, 'Telekomunikacije (BSc)', 6, 5, 'TK', 1, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tipkomponente`
--

CREATE TABLE IF NOT EXISTS `tipkomponente` (
  `id` int(11) NOT NULL,
  `naziv` varchar(20) COLLATE utf8_slovenian_ci NOT NULL,
  `opis_opcija` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `tipstudija`
--

INSERT INTO `tipstudija` (`id`, `naziv`, `ciklus`, `trajanje`, `moguc_upis`) VALUES
(1, 'Virtualni studij PGS', 1, 2, 0),
(2, 'Bakalaureat', 1, 6, 1),
(3, 'Master', 2, 4, 1),
(4, 'Diplomski studij - Ante-Bologna', 1, 9, 0);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `ugovoroucenju_izborni`
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
  `prijemni_max` int(5) NOT NULL,
  `studij` int(11) NOT NULL,
  PRIMARY KEY (`prijemni_termin`,`studij`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci COMMENT='Tabela za pohranu kriterija za upis' AUTO_INCREMENT=5 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `uspjeh_u_srednjoj`
--


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
  `attachment` tinyint(1) NOT NULL DEFAULT '0',
  `dozvoljene_ekstenzije` varchar(255) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `postavka_zadace` varchar(255) COLLATE utf8_slovenian_ci DEFAULT NULL,
  `komponenta` int(11) NOT NULL,
  `vrijemeobjave` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
  KEY `pomocni` (`zadaca`,`redni_broj`,`student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `zadatak`
--


-- --------------------------------------------------------

--
-- Table structure for table `zadatakdiff`
--

CREATE TABLE IF NOT EXISTS `zadatakdiff` (
  `zadatak` bigint(11) NOT NULL DEFAULT '0',
  `diff` text COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zadatakdiff`
--


-- --------------------------------------------------------

--
-- Table structure for table `zavrsni`
--

CREATE TABLE IF NOT EXISTS `zavrsni` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `opis` text CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `biljeska` text CHARACTER SET utf8 COLLATE utf8_slovenian_ci,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `zavrsni`
--

INSERT INTO `zavrsni` (`id`, `naziv`, `predmet`, `akademska_godina`, `opis`, `biljeska`, `vrijeme`) VALUES
(1, 'Pokusaj', 3, 1, 'neki pokusaj', NULL, '2011-08-29 17:24:51'),
(2, 'testirana', 1, 1, 'nesto', NULL, '2011-08-29 18:31:50'),
(3, 'ttttttttttttttt', 5, 1, 'ccccccccccc', NULL, '2011-08-29 18:52:28'),
(4, 'zavrsni', 6, 1, 'zavrsni', NULL, '2011-08-31 18:12:09'),
(5, 'sdfghj', 6, 1, 'dsftgzthij', NULL, '2011-08-31 18:27:22'),
(6, 'test', 4, 1, 'addfghhhh', NULL, '2011-08-31 18:38:07'),
(7, 'tema', 6, 1, 'dghsjjjjjjjhjd&lt;jdsbdjb&lt;c,bjcjcv,ljbnvfs', NULL, '2011-08-31 18:42:10');

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_file`
--

CREATE TABLE IF NOT EXISTS `zavrsni_file` (
  `id` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revizija` tinyint(4) NOT NULL,
  `osoba` int(11) NOT NULL,
  `zavrsni` int(11) NOT NULL,
  `file` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `zavrsni_file`
--


-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_file_diff`
--

CREATE TABLE IF NOT EXISTS `zavrsni_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`file`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `zavrsni_file_diff`
--


-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_link`
--

CREATE TABLE IF NOT EXISTS `zavrsni_link` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `url` varchar(200) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `opis` text CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `zavrsni` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `zavrsni_link`
--


-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_rss`
--

CREATE TABLE IF NOT EXISTS `zavrsni_rss` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `url` varchar(200) CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `opis` text CHARACTER SET utf8 COLLATE utf8_slovenian_ci NOT NULL,
  `zavrsni` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `zavrsni_rss`
--


-- --------------------------------------------------------

--
-- Table structure for table `zvanje`
--

CREATE TABLE IF NOT EXISTS `zvanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `titula` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `zvanje`
--

INSERT INTO `zvanje` (`id`, `naziv`, `titula`) VALUES
(1, 'Redovni profesor', 'R. prof.'),
(2, 'Vanredni profesor', 'V. prof.'),
(3, 'Docent', 'Doc.'),
(4, 'Viši asistent', 'V. asis.'),
(5, 'Asistent', 'Asis.'),
(6, 'Profesor emeritus', '');
