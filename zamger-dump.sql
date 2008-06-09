-- phpMyAdmin SQL Dump
-- version 2.11.1.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 28, 2008 at 10:44 AM
-- Server version: 5.0.22
-- PHP Version: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `zamger-pkks`
--

-- --------------------------------------------------------

--
-- Table structure for table `akademska_godina`
--

CREATE TABLE IF NOT EXISTS `akademska_godina` (
  `id` int(11) NOT NULL,
  `naziv` varchar(20) collate utf8_slovenian_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `akademska_godina`
--

INSERT INTO `akademska_godina` (`id`, `naziv`) VALUES
(1, '2007/2008');

-- --------------------------------------------------------

--
-- Table structure for table `auth`
--

CREATE TABLE IF NOT EXISTS `auth` (
  `id` int(11) NOT NULL default '0',
  `login` varchar(50) collate utf8_slovenian_ci NOT NULL default '',
  `password` varchar(20) collate utf8_slovenian_ci NOT NULL default '',
  `admin` tinyint(1) NOT NULL default '0',
  `external_id` varchar(50) collate utf8_slovenian_ci NOT NULL default '',
  `ime` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `prezime` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `email` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `brindexa` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `student` tinyint(1) NOT NULL default '0',
  `nastavnik` tinyint(1) NOT NULL default '0',
  `studentska` tinyint(1) NOT NULL default '0',
  `siteadmin` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`,`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `auth`
--

INSERT INTO `auth` (`id`, `login`, `password`, `admin`, `external_id`, `ime`, `prezime`, `email`, `brindexa`, `student`, `nastavnik`, `studentska`, `siteadmin`) VALUES
(1, 'admin', 'admin', 0, '', 'Site', 'Admin', 'site@admin.com', '', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cas`
--

CREATE TABLE IF NOT EXISTS `cas` (
  `id` int(11) NOT NULL auto_increment,
  `datum` date NOT NULL default '0000-00-00',
  `vrijeme` time NOT NULL default '00:00:00',
  `labgrupa` int(11) NOT NULL default '0',
  `nastavnik` int(11) NOT NULL default '0',
  `predmet` int(11) NOT NULL,
  `komponenta` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2629 ;

--
-- Dumping data for table `cas`
--


-- --------------------------------------------------------

--
-- Table structure for table `institucija`
--

CREATE TABLE IF NOT EXISTS `institucija` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL default '',
  `roditelj` int(11) NOT NULL default '0',
  `kratki_naziv` varchar(10) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `institucija`
--


-- --------------------------------------------------------

--
-- Table structure for table `ispit`
--

CREATE TABLE IF NOT EXISTS `ispit` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL default '',
  `predmet` int(11) NOT NULL default '0',
  `datum` date NOT NULL default '0000-00-00',
  `komponenta` int(2) NOT NULL default '0',
  `vrijemeobjave` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=603 ;

--
-- Dumping data for table `ispit`
--


-- --------------------------------------------------------

--
-- Table structure for table `ispitocjene`
--

CREATE TABLE IF NOT EXISTS `ispitocjene` (
  `ispit` int(11) NOT NULL default '0',
  `student` int(11) NOT NULL default '0',
  `ocjena` float NOT NULL default '0',
  PRIMARY KEY  (`ispit`,`student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `ispitocjene`
--


-- --------------------------------------------------------

--
-- Table structure for table `kanton`
--

CREATE TABLE IF NOT EXISTS `kanton` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `kratki_naziv` varchar(5) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

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
-- Table structure for table `komentar`
--

CREATE TABLE IF NOT EXISTS `komentar` (
  `id` int(11) NOT NULL auto_increment,
  `student` int(11) NOT NULL default '0',
  `nastavnik` int(11) NOT NULL default '0',
  `labgrupa` int(11) NOT NULL default '0',
  `predmet` int(11) NOT NULL,
  `datum` datetime NOT NULL default '0000-00-00 00:00:00',
  `komentar` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=284 ;

--
-- Dumping data for table `komentar`
--


-- --------------------------------------------------------

--
-- Table structure for table `komponenta`
--

CREATE TABLE IF NOT EXISTS `komponenta` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(40) collate utf8_slovenian_ci NOT NULL,
  `gui_naziv` varchar(20) collate utf8_slovenian_ci NOT NULL,
  `kratki_gui_naziv` varchar(20) collate utf8_slovenian_ci NOT NULL,
  `tipkomponente` int(11) NOT NULL,
  `maxbodova` double NOT NULL,
  `prolaz` double NOT NULL,
  `opcija` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `komponenta`
--

INSERT INTO `komponenta` (`id`, `naziv`, `gui_naziv`, `kratki_gui_naziv`, `tipkomponente`, `maxbodova`, `prolaz`, `opcija`) VALUES
(1, 'I parcijalni (ETF BSc)', 'I parcijalni', 'I parc', 1, 20, 10, ''),
(2, 'II parcijalni (ETF BSc)', 'II parcijalni', 'II parc', 1, 20, 10, ''),
(3, 'Integralni (ETF BSc)', 'Integralni', 'Int', 2, 40, 20, '1+2'),
(4, 'Usmeni (ETF BSc)', 'Usmeni', 'Usmeni', 1, 40, 0, ''),
(5, 'Prisustvo (ETF BSc)', 'Prisustvo', 'Prisustvo', 3, 10, 0, '3'),
(6, 'Zadace (ETF BSc)', 'Zadace', 'Zadace', 4, 10, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `komponentebodovi`
--

CREATE TABLE IF NOT EXISTS `komponentebodovi` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `komponenta` int(11) NOT NULL,
  `bodovi` double NOT NULL,
  PRIMARY KEY  (`student`,`predmet`,`komponenta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `komponentebodovi`
--


-- --------------------------------------------------------

--
-- Table structure for table `konacna_ocjena`
--

CREATE TABLE IF NOT EXISTS `konacna_ocjena` (
  `student` int(11) NOT NULL default '0',
  `predmet` int(11) NOT NULL default '0',
  `ocjena` int(3) NOT NULL default '0',
  `datum` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `konacna_ocjena`
--


-- --------------------------------------------------------

--
-- Table structure for table `labgrupa`
--

CREATE TABLE IF NOT EXISTS `labgrupa` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL default '',
  `predmet` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=527 ;

--
-- Dumping data for table `labgrupa`
--


-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL auto_increment,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `userid` int(11) NOT NULL default '0',
  `dogadjaj` varchar(255) collate utf8_slovenian_ci NOT NULL,
  `nivo` tinyint(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=119652 ;


--
-- Table structure for table `nastavnik_old`
--

CREATE TABLE IF NOT EXISTS `nastavnik_old` (
  `id` int(11) NOT NULL,
  `ime` varchar(100) collate utf8_slovenian_ci NOT NULL default '',
  `prezime` varchar(100) collate utf8_slovenian_ci NOT NULL default '',
  `email` varchar(100) collate utf8_slovenian_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `nastavnik_old`
--


-- --------------------------------------------------------

--
-- Table structure for table `nastavnik_predmet`
--

CREATE TABLE IF NOT EXISTS `nastavnik_predmet` (
  `nastavnik` int(11) NOT NULL default '0',
  `predmet` int(11) NOT NULL default '0',
  `admin` tinyint(1) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `nastavnik_predmet`
--


-- --------------------------------------------------------

--
-- Table structure for table `ogranicenje`
--

CREATE TABLE IF NOT EXISTS `ogranicenje` (
  `nastavnik` int(11) NOT NULL default '0',
  `labgrupa` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `ogranicenje`
--


-- --------------------------------------------------------

--
-- Table structure for table `ponudakursa`
--

CREATE TABLE IF NOT EXISTS `ponudakursa` (
  `id` int(11) NOT NULL auto_increment,
  `predmet` int(11) NOT NULL default '0',
  `studij` int(11) NOT NULL default '0',
  `semestar` int(11) NOT NULL default '0',
  `obavezan` tinyint(1) NOT NULL default '0',
  `akademska_godina` int(11) NOT NULL default '0',
  `tippredmeta` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `poruka`
--

CREATE TABLE IF NOT EXISTS `poruka` (
  `id` int(11) NOT NULL auto_increment,
  `tip` tinyint(4) NOT NULL,
  `opseg` tinyint(4) NOT NULL,
  `primalac` int(11) NOT NULL,
  `posiljalac` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `ref` int(11) NOT NULL default '0',
  `naslov` text collate utf8_slovenian_ci NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=117 ;

--
-- Dumping data for table `poruka`
--


-- --------------------------------------------------------

--
-- Table structure for table `predmet`
--

CREATE TABLE IF NOT EXISTS `predmet` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `institucija` int(11) NOT NULL default '0',
  `kratki_naziv` varchar(10) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `predmet`
--


-- --------------------------------------------------------

--
-- Table structure for table `preference`
--

CREATE TABLE IF NOT EXISTS `preference` (
  `korisnik` int(11) NOT NULL,
  `preferenca` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `vrijednost` varchar(100) collate utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `preference`
--


-- --------------------------------------------------------

--
-- Table structure for table `prijemni`
--

CREATE TABLE IF NOT EXISTS `prijemni` (
  `id` int(11) NOT NULL auto_increment,
  `ime` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `prezime` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `datum_rodjenja` date NOT NULL,
  `mjesto_rodjenja` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `drzavljanstvo` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `zavrsena_skola` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `jmbg` varchar(14) collate utf8_slovenian_ci NOT NULL,
  `adresa` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `telefon` varchar(15) collate utf8_slovenian_ci NOT NULL,
  `kanton` int(11) NOT NULL,
  `redovni` tinyint(1) NOT NULL,
  `odsjek_prvi` varchar(3) collate utf8_slovenian_ci NOT NULL,
  `odsjek_drugi` varchar(3) collate utf8_slovenian_ci NOT NULL,
  `odsjek_treci` varchar(3) collate utf8_slovenian_ci NOT NULL,
  `odsjek_cetvrti` varchar(3) collate utf8_slovenian_ci NOT NULL,
  `opci_uspjeh` double NOT NULL,
  `kljucni_predmeti` double NOT NULL,
  `dodatni_bodovi` double NOT NULL,
  `prijemni_ispit` double NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=17 ;

--
-- Dumping data for table `prijemni`
--


-- --------------------------------------------------------

--
-- Table structure for table `prisustvo`
--

CREATE TABLE IF NOT EXISTS `prisustvo` (
  `student` int(11) NOT NULL default '0',
  `cas` int(11) NOT NULL default '0',
  `prisutan` tinyint(1) NOT NULL default '0',
  `plus_minus` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`student`,`cas`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `prisustvo`
--


-- --------------------------------------------------------

--
-- Table structure for table `programskijezik`
--

CREATE TABLE IF NOT EXISTS `programskijezik` (
  `id` int(10) NOT NULL default '0',
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL default '',
  `geshi` varchar(20) collate utf8_slovenian_ci NOT NULL default '',
  `ekstenzija` varchar(10) collate utf8_slovenian_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `programskijezik`
--



INSERT INTO `programskijezik` (`id`, `naziv`, `geshi`, `ekstenzija`) VALUES
(0, '--Nije odredjen--', '', '');



-- --------------------------------------------------------

--
-- Table structure for table `rss`
--

CREATE TABLE IF NOT EXISTS `rss` (
  `id` varchar(15) collate utf8_slovenian_ci NOT NULL,
  `auth` int(11) NOT NULL,
  `access` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `rss`
--


-- --------------------------------------------------------

--
-- Table structure for table `stdin`
--

CREATE TABLE IF NOT EXISTS `stdin` (
  `id` bigint(20) NOT NULL auto_increment,
  `zadaca` bigint(20) NOT NULL default '0',
  `redni_broj` int(11) NOT NULL default '0',
  `ulaz` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=230 ;

--
-- Dumping data for table `stdin`
--


-- --------------------------------------------------------

--
-- Table structure for table `studentski_moduli`
--

CREATE TABLE IF NOT EXISTS `studentski_moduli` (
  `id` int(11) NOT NULL auto_increment,
  `predmet` int(11) NOT NULL,
  `gui_naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `url` varchar(255) collate utf8_slovenian_ci NOT NULL,
  `aktivan` tinyint(1) NOT NULL,
  `novi_prozor` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=423 ;

--
-- Dumping data for table `studentski_moduli`
--


-- --------------------------------------------------------

--
-- Table structure for table `student_labgrupa`
--

CREATE TABLE IF NOT EXISTS `student_labgrupa` (
  `student` int(11) NOT NULL default '0',
  `labgrupa` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_labgrupa`
--


-- --------------------------------------------------------

--
-- Table structure for table `student_old`
--

CREATE TABLE IF NOT EXISTS `student_old` (
  `id` bigint(11) NOT NULL,
  `ime` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `prezime` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `email` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `brindexa` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_old`
--


-- --------------------------------------------------------

--
-- Table structure for table `student_predmet`
--

CREATE TABLE IF NOT EXISTS `student_predmet` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_predmet`
--


-- --------------------------------------------------------

--
-- Table structure for table `student_studij`
--

CREATE TABLE IF NOT EXISTS `student_studij` (
  `student` int(11) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(3) NOT NULL,
  `akademska_godina` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `student_studij`
--


-- --------------------------------------------------------

--
-- Table structure for table `studij`
--

CREATE TABLE IF NOT EXISTS `studij` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL default '',
  `trajanje` int(11) NOT NULL default '0',
  `institucija` int(11) NOT NULL default '0',
  `kratkinaziv` varchar(10) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `studij`
--

INSERT INTO `studij` (`id`, `naziv`, `trajanje`, `institucija`, `kratkinaziv`) VALUES
(1, 'Prva godina studija', 2, 1, 'PGS'),
(2, 'Računarstvo i informatika (BSc)', 6, 2, 'RI'),
(3, 'Automatika i elektronika (BSc)', 6, 3, 'AE'),
(4, 'Elektroenergetika (BSc)', 6, 4, 'EE'),
(5, 'Telekomunikacije (BSc)', 6, 5, 'TK'),
(6, 'Računarstvo i informatika (AB)', 9, 2, 'RI');

-- --------------------------------------------------------

--
-- Table structure for table `tipkomponente`
--

CREATE TABLE IF NOT EXISTS `tipkomponente` (
  `id` int(11) NOT NULL,
  `naziv` varchar(20) collate utf8_slovenian_ci NOT NULL,
  `opis_opcija` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
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
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

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
-- Table structure for table `zadaca`
--

CREATE TABLE IF NOT EXISTS `zadaca` (
  `id` bigint(4) NOT NULL auto_increment,
  `predmet` int(11) NOT NULL default '0',
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `zadataka` tinyint(4) NOT NULL default '0',
  `bodova` float NOT NULL default '0',
  `rok` datetime default NULL,
  `aktivna` tinyint(1) NOT NULL default '0',
  `programskijezik` int(10) NOT NULL default '0',
  `attachment` tinyint(1) NOT NULL default '0',
  `komponenta` int(11) NOT NULL,
  `vrijemeobjave` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=194 ;

--
-- Dumping data for table `zadaca`
--


-- --------------------------------------------------------

--
-- Table structure for table `zadatak`
--

CREATE TABLE IF NOT EXISTS `zadatak` (
  `id` bigint(11) NOT NULL auto_increment,
  `zadaca` int(11) NOT NULL default '0',
  `redni_broj` int(11) NOT NULL default '0',
  `student` int(11) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '0',
  `bodova` float NOT NULL default '0',
  `izvjestaj_skripte` text collate utf8_slovenian_ci NOT NULL,
  `vrijeme` datetime default NULL,
  `komentar` text collate utf8_slovenian_ci NOT NULL,
  `filename` varchar(200) collate utf8_slovenian_ci NOT NULL default '',
  `userid` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=60466 ;

--
-- Dumping data for table `zadatak`
--


-- --------------------------------------------------------

--
-- Table structure for table `zadatakdiff`
--

CREATE TABLE IF NOT EXISTS `zadatakdiff` (
  `zadatak` bigint(11) NOT NULL default '0',
  `diff` text collate utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zadatakdiff`
--

