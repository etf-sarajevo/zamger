-- phpMyAdmin SQL Dump
-- version 2.11.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 10, 2009 at 08:46 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `zamgerdemo`
--
-- --------------------------------------------------------
--
-- Table structure for table `akademska_godina`
--

CREATE TABLE IF NOT EXISTS `akademska_godina` (
  `id` int(11) NOT NULL,
  `naziv` varchar(20) collate utf8_slovenian_ci NOT NULL default '',
  `aktuelna` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
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


-- --------------------------------------------------------

--
-- Table structure for table `angazman`
--

CREATE TABLE IF NOT EXISTS `angazman` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `angazman_status` int(11) NOT NULL,
  PRIMARY KEY  (`predmet`,`akademska_godina`,`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `angazman_status`
--

CREATE TABLE IF NOT EXISTS `angazman_status` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `angazman_status`
--

INSERT INTO `angazman_status` (`id`, `naziv`) VALUES
(1, 'odgovorni nastavnik'),
(2, 'asistent'),
(3, 'demonstrator'),
(4, 'predavaÄ� - istaknuti struÄ�njak iz prakse'),
(5, 'asistent - istaknuti struÄ�njak iz prakse'),
(6, 'profesor emeritus');


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
  `aktivan` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`,`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `auth`
--

INSERT INTO `auth` (`id`, `login`, `password`, `admin`, `external_id`, `aktivan`) VALUES
(1, 'admin', 'admin', 0, '', 1);


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
  `komponenta` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `drzava`
--

CREATE TABLE IF NOT EXISTS `drzava` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(30) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
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
(8, 'NjemaÄ�ka'),
(9, 'Makedonija'),
(10, 'Iran');


-- --------------------------------------------------------

--
-- Table structure for table `moodle_predmet_id`
--

CREATE TABLE IF NOT EXISTS `moodle_predmet_id` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `moodle_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `institucija`
--

INSERT INTO `institucija` (`id`, `naziv`, `roditelj`, `kratki_naziv`) VALUES
(2, 'Odsjek za raÄ�unarstvo i informatiku', 1, 'RI'),
(3, 'Odsjek za automatiku i elektroniku', 1, 'AE'),
(4, 'Odsjek za elektroenergetiku', 1, 'EE'),
(5, 'Odsjek za telekomunikacije', 1, 'TK'),
(1, 'ElektrotehniÄ�ki fakultet Sarajevo', 0, 'ETF');

-- --------------------------------------------------------

--
-- Table structure for table `ispit`
--

CREATE TABLE IF NOT EXISTS `ispit` (
  `id` int(11) NOT NULL auto_increment,
  `predmet` int(11) NOT NULL default '0',
  `akademska_godina` int(11) NOT NULL default '0',
  `datum` date NOT NULL default '0000-00-00',
  `komponenta` int(2) NOT NULL default '0',
  `vrijemeobjave` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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


-- --------------------------------------------------------

--
-- Table structure for table `izborni_slot`
--

CREATE TABLE IF NOT EXISTS `izborni_slot` (
  `id` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY  (`id`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `kanton`
--

CREATE TABLE IF NOT EXISTS `kanton` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `kratki_naziv` varchar(5) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

--
-- Dumping data for table `kanton`
--

INSERT INTO `kanton` (`id`, `naziv`, `kratki_naziv`) VALUES
(1, 'Bosansko-Podrinjski kanton', 'BPK'),
(2, 'HercegovaÄ�ko-Neretvanski kanton', 'HNK'),
(3, 'Livanjski kanton', 'LK'),
(4, 'Posavski kanton', 'PK'),
(5, 'Sarajevski kanton', 'SK'),
(6, 'Srednjobosanski kanton', 'SBK'),
(7, 'Tuzlanski kanton', 'TK'),
(8, 'Unsko-Sanski kanton', 'USK'),
(9, 'Zapadno-HercegovaÄ�ki kanton', 'ZHK'),
(10, 'ZeniÄ�ko-Dobojski kanton', 'ZDK'),
(11, 'Republika Srpska', 'RS'),
(12, 'Distrikt BrÄ�ko', 'DB'),
(13, 'Strani drÅ¾avljanin', 'SD');

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
  `id` int(11) NOT NULL auto_increment,
  `student` int(11) NOT NULL default '0',
  `nastavnik` int(11) NOT NULL default '0',
  `labgrupa` int(11) NOT NULL default '0',
  `datum` datetime NOT NULL default '0000-00-00 00:00:00',
  `komentar` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
  `uslov` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

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
  `akademska_godina` int(11) NOT NULL default '0',
  `ocjena` int(3) NOT NULL default '0',
  `datum` datetime NOT NULL,
  `odluka` int(11) NOT NULL default '0',
  PRIMARY KEY  (`student`,`predmet`)
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
  `akademska_godina` int(11) NOT NULL default '0',
  `virtualna` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `log`
--


-- --------------------------------------------------------

--
-- Table structure for table `mjesto`
--

CREATE TABLE IF NOT EXISTS `mjesto` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(40) collate utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `drzava` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=79 ;

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
(6, 'BihaÄ‡', 2, 1),
(7, 'Tuzla', 69, 1);


-- --------------------------------------------------------

--
-- Table structure for table `nacin_studiranja`
--

CREATE TABLE IF NOT EXISTS `nacin_studiranja` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(30) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `nacin_studiranja`
--

INSERT INTO `nacin_studiranja` (`id`, `naziv`) VALUES
(1, 'Redovan'),
(2, 'Paralelan'),
(3, 'Redovan samofinansirajuÄ‡i'),
(0, 'Nepoznat status');


-- --------------------------------------------------------

--
-- Table structure for table `nacionalnost`
--

CREATE TABLE IF NOT EXISTS `nacionalnost` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

--
-- Dumping data for table `nacionalnost`
--

INSERT INTO `nacionalnost` (`id`, `naziv`) VALUES
(1, 'BoÅ¡njak/BoÅ¡njakinja'),
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
  `nivo_pristupa` enum ('nastavnik', 'super_asistent', 'asistent') NOT NULL default 'asistent',
  PRIMARY KEY  (`nastavnik`,`akademska_godina`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `nastavnik_predmet`
--


-- --------------------------------------------------------

--
-- Table structure for table `naucni_stepen`
--

CREATE TABLE IF NOT EXISTS `naucni_stepen` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `naucni_stepen`
--

INSERT INTO `naucni_stepen` (`id`, `naziv`, `titula`) VALUES
(1, 'Doktor nauka', 'dr'),
(2, 'Magistar nauka', 'mr'),
(6, 'Bez nauÄ�nog stepena', '');

-- --------------------------------------------------------

--
-- Table structure for table `notifikacija`
--

CREATE TABLE IF NOT EXISTS `notifikacija` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`tekst` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
`link` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
`tip` int(1) NOT NULL,
`procitana` int(1) NOT NULL,
`vrijeme` timestamp NOT NULL,
`student` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `oblast`
--

CREATE TABLE IF NOT EXISTS `oblast` (
  `id` int(11) NOT NULL auto_increment,
  `institucija` int(11) NOT NULL,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `oblast`
--


-- --------------------------------------------------------

--
-- Table structure for table `odluka`
--

CREATE TABLE IF NOT EXISTS `odluka` (
  `id` int(11) NOT NULL auto_increment,
  `datum` date NOT NULL,
  `broj_protokola` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `student` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `odluka`
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
-- Table structure for table `opcina`
--

CREATE TABLE IF NOT EXISTS `opcina` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=145 ;

--
-- Dumping data for table `opcina`
--

INSERT INTO `opcina` (`id`, `naziv`) VALUES
(1, 'BanoviÄ‡i'),
(2, 'BihaÄ‡'),
(3, 'Bosanska Krupa'),
(4, 'Bosanski Petrovac'),
(5, 'Bosansko Grahovo'),
(6, 'Breza'),
(7, 'Bugojno'),
(8, 'BusovaÄ�a'),
(9, 'BuÅ¾im'),
(10, 'ÄŒapljina'),
(11, 'Cazin'),
(12, 'ÄŒeliÄ‡'),
(13, 'Centar, Sarajevo'),
(14, 'ÄŒitluk'),
(15, 'Drvar'),
(16, 'Doboj Istok'),
(17, 'Doboj Jug'),
(18, 'DobretiÄ‡i'),
(19, 'Domaljevac-Å amac'),
(20, 'Donji Vakuf'),
(21, 'FoÄ�a-Ustikolina'),
(22, 'Fojnica'),
(23, 'GlamoÄ�'),
(24, 'GoraÅ¾de'),
(25, 'Gornji Vakuf-Uskoplje'),
(26, 'GraÄ�anica'),
(27, 'GradaÄ�ac'),
(28, 'Grude'),
(29, 'HadÅ¾iÄ‡i'),
(30, 'IlidÅ¾a'),
(31, 'IlijaÅ¡'),
(32, 'Jablanica'),
(33, 'Jajce'),
(34, 'Kakanj'),
(35, 'Kalesija'),
(36, 'Kiseljak'),
(37, 'Kladanj'),
(38, 'KljuÄ�'),
(39, 'Konjic'),
(40, 'KreÅ¡evo'),
(41, 'Kupres'),
(42, 'Livno'),
(43, 'LjubuÅ¡ki'),
(44, 'Lukavac'),
(45, 'Maglaj'),
(46, 'Mostar'),
(47, 'Neum'),
(48, 'Novi Grad, Sarajevo'),
(49, 'Novo Sarajevo'),
(50, 'Novi Travnik'),
(51, 'OdÅ¾ak'),
(52, 'Olovo'),
(53, 'OraÅ¡je'),
(54, 'Pale-PraÄ�a'),
(55, 'PosuÅ¡je'),
(56, 'Prozor-Rama'),
(57, 'Ravno'),
(58, 'Sanski Most'),
(59, 'Sapna'),
(60, 'Å iroki Brijeg'),
(61, 'Srebrenik'),
(62, 'Stari Grad, Sarajevo'),
(63, 'Stolac'),
(64, 'TeoÄ�ak'),
(65, 'TeÅ¡anj'),
(66, 'Tomislavgrad'),
(67, 'Travnik'),
(68, 'Trnovo (FBiH)'),
(69, 'Tuzla'),
(70, 'Usora'),
(71, 'VareÅ¡'),
(72, 'Velika KladuÅ¡a'),
(73, 'Visoko'),
(74, 'Vitez'),
(75, 'VogoÅ¡Ä‡a'),
(76, 'ZavidoviÄ‡i'),
(77, 'Zenica'),
(78, 'Å½epÄ�e'),
(79, 'Å½ivinice'),
(80, 'BerkoviÄ‡i'),
(81, 'Bijeljina'),
(82, 'BileÄ‡a'),
(83, 'Bosanska Kostajnica'),
(84, 'Bosanski Brod'),
(85, 'Bratunac'),
(86, 'ÄŒajniÄ�e'),
(87, 'ÄŒelinac'),
(88, 'Derventa'),
(89, 'Doboj'),
(90, 'Donji Å½abar'),
(91, 'FoÄ�a'),
(92, 'Gacko'),
(93, 'Banja Luka'),
(94, 'GradiÅ¡ka'),
(95, 'Han Pijesak'),
(96, 'IstoÄ�ni Drvar'),
(97, 'IstoÄ�na IlidÅ¾a'),
(98, 'IstoÄ�ni Mostar'),
(99, 'IstoÄ�ni Stari Grad'),
(100, 'IstoÄ�no Novo Sarajevo'),
(101, 'Jezero'),
(102, 'Kalinovik'),
(103, 'KneÅ¾evo'),
(104, 'Kozarska Dubica'),
(105, 'Kotor VaroÅ¡'),
(106, 'Krupa na Uni'),
(107, 'Kupres (RS)'),
(108, 'LaktaÅ¡i'),
(109, 'Ljubinje'),
(110, 'Lopare'),
(111, 'MiliÄ‡i'),
(112, 'ModriÄ�a'),
(113, 'MrkonjiÄ‡ Grad'),
(114, 'Nevesinje'),
(115, 'Novi Grad (RS)'),
(116, 'Novo GoraÅ¾de'),
(117, 'Osmaci'),
(118, 'OÅ¡tra Luka'),
(119, 'Pale'),
(120, 'PelagiÄ‡evo'),
(121, 'Petrovac'),
(122, 'Petrovo'),
(123, 'Prijedor'),
(124, 'Prnjavor'),
(125, 'Ribnik'),
(126, 'Rogatica'),
(127, 'Rudo'),
(128, 'Å amac'),
(129, 'Å ekoviÄ‡i'),
(130, 'Å ipovo'),
(131, 'Sokolac'),
(132, 'Srbac'),
(133, 'Srebrenica'),
(134, 'TesliÄ‡'),
(135, 'Trebinje'),
(136, 'Trnovo (RS)'),
(137, 'Ugljevik'),
(138, 'ViÅ¡egrad'),
(139, 'Vlasenica'),
(140, 'Vukosavlje'),
(141, 'Zvornik'),
(142, 'BrÄ�ko'),
(143, '(nije u BiH)');


-- --------------------------------------------------------

--
-- Table structure for table `osoba`
--

CREATE TABLE IF NOT EXISTS `osoba` (
  `id` int(11) NOT NULL,
  `ime` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `prezime` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `imeoca` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `prezimeoca` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `imemajke` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `prezimemajke` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `spol` enum('M','Z','') collate utf8_slovenian_ci NOT NULL,
  `email` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `brindexa` varchar(10) collate utf8_slovenian_ci NOT NULL,
  `datum_rodjenja` date NOT NULL,
  `mjesto_rodjenja` int(11) NOT NULL,
  `nacionalnost` int(11) NOT NULL,
  `drzavljanstvo` int(11) NOT NULL,
  `boracke_kategorije` tinyint(1) NOT NULL,
  `jmbg` varchar(14) collate utf8_slovenian_ci NOT NULL,
  `adresa` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `adresa_mjesto` int(11) NOT NULL,
  `telefon` varchar(15) collate utf8_slovenian_ci NOT NULL,
  `kanton` int(11) NOT NULL,
  `treba_brisati` tinyint(1) NOT NULL default '0',
  `strucni_stepen` int(11) NOT NULL,
  `naucni_stepen` int(11) NOT NULL,
  `slika` varchar(50) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `osoba`
--

INSERT INTO `osoba` (`id`, `ime`, `prezime`, `email`, `brindexa`, `datum_rodjenja`, `mjesto_rodjenja`, `drzavljanstvo`, `jmbg`, `adresa`, `adresa_mjesto`, `telefon`, `kanton`, `treba_brisati`) VALUES
(1, 'Site', 'Admin', 'site@admin.com', '', '0000-00-00', 0, '', '', '', 0, '', 0, 0);

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


-- --------------------------------------------------------

--
-- Table structure for table `podoblast`
--

CREATE TABLE IF NOT EXISTS `podoblast` (
  `id` int(11) NOT NULL auto_increment,
  `oblast` int(11) NOT NULL,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `podoblast`
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ponudakursa`
--


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
  `vrijeme` datetime NOT NULL,
  `ref` int(11) NOT NULL default '0',
  `naslov` text collate utf8_slovenian_ci NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `poruka`
--


-- --------------------------------------------------------

--
-- Table structure for table `predmet`
--

CREATE TABLE IF NOT EXISTS `predmet` (
  `id` int(11) NOT NULL auto_increment,
  `sifra` varchar(20) collate utf8_slovenian_ci NOT NULL,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `institucija` int(11) NOT NULL default '0',
  `kratki_naziv` varchar(10) collate utf8_slovenian_ci NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  `ects` float NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
  `vrijednost` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`korisnik`,`preferenca`)
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
  `redovan` tinyint(1) NOT NULL default '1',
  `studij_prvi` int(11) NOT NULL,
  `studij_drugi` int(11) NOT NULL,
  `studij_treci` int(11) NOT NULL,
  `studij_cetvrti` int(11) NOT NULL,
  `izasao` tinyint(1) NOT NULL,
  `rezultat` double NOT NULL,
  PRIMARY KEY  (`prijemni_termin`,`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `prijemni_prijava`
--



-- --------------------------------------------------------

--
-- Table structure for table `prijemni_termin`
--

CREATE TABLE IF NOT EXISTS `prijemni_termin` (
  `id` int(11) NOT NULL auto_increment,
  `akademska_godina` int(11) NOT NULL,
  `datum` date NOT NULL,
  `ciklus_studija` tinyint(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;


--
-- Dumping data for table `prijemni_termin`
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
-- Table structure for table `privilegije`
--

CREATE TABLE IF NOT EXISTS `privilegije` (
  `osoba` int(11) NOT NULL,
  `privilegija` varchar(30) collate utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
(0, '--Nije odredjen--', '', ''),
(1, 'C', 'C', '.c'),
(2, 'C++', 'C++', '.cpp');

-- --------------------------------------------------------

--
-- Table structure for table `promjena_odsjeka`
--

CREATE TABLE IF NOT EXISTS `promjena_odsjeka` (
  `id` int(11) NOT NULL auto_increment,
  `osoba` int(11) NOT NULL,
  `iz_odsjeka` int(11) NOT NULL,
  `u_odsjek` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `promjena_odsjeka`
--


-- --------------------------------------------------------

--
-- Table structure for table `promjena_podataka`
--

CREATE TABLE IF NOT EXISTS `promjena_podataka` (
  `id` int(11) NOT NULL auto_increment,
  `osoba` int(11) NOT NULL,
  `ime` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `prezime` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `email` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `brindexa` varchar(10) collate utf8_slovenian_ci NOT NULL,
  `datum_rodjenja` date NOT NULL,
  `mjesto_rodjenja` int(11) NOT NULL,
  `drzavljanstvo` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `jmbg` varchar(14) collate utf8_slovenian_ci NOT NULL,
  `adresa` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `adresa_mjesto` int(11) NOT NULL,
  `telefon` varchar(15) collate utf8_slovenian_ci NOT NULL,
  `kanton` int(11) NOT NULL,
  `vrijeme_zahtjeva` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
  PRIMARY KEY  (`osoba`,`redni_broj`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


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
  PRIMARY KEY  (`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- --------------------------------------------------------

--
-- Table structure for table `raspored`
--

CREATE TABLE IF NOT EXISTS `raspored` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `datum_kreiranja` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `aktivan` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `raspored`
--


-- --------------------------------------------------------

--
-- Table structure for table `raspored_stavka`
--

CREATE TABLE IF NOT EXISTS `raspored_stavka` (
  `id` int(11) NOT NULL auto_increment,
  `raspored` int(11) NOT NULL,
  `dan_u_sedmici` tinyint(1) NOT NULL,
  `predmet` int(11) NOT NULL,
  `labgrupa` int(11) NOT NULL,
  `vrijeme_pocetak` int(11) NOT NULL,
  `vrijeme_kraj` int(11) NOT NULL,
  `sala` int(11) NOT NULL,
  `tip` varchar(1) character set latin1 NOT NULL default 'P',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `raspored_stavka`
--


-- --------------------------------------------------------

--
-- Table structure for table `raspored_sala`
--

CREATE TABLE IF NOT EXISTS `raspored_sala` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `kapacitet` int(5) default NULL,
  `tip` varchar(255) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `raspored_sala`
--


-- --------------------------------------------------------

--
-- Table structure for table `ras_sati`
--

CREATE TABLE IF NOT EXISTS `ras_sati` (
  `idS` tinyint(1) NOT NULL auto_increment,
  `satS` varchar(13) NOT NULL,
  PRIMARY KEY  (`idS`)
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
-- Table structure for table `savjet_dana`
--

CREATE TABLE IF NOT EXISTS `savjet_dana` (
  `id` int(11) NOT NULL auto_increment,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  `vrsta_korisnika` enum('nastavnik','student','studentska','siteadmin') collate utf8_slovenian_ci NOT NULL default 'nastavnik',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=21 ;

--
-- Dumping data for table `savjet_dana`
--

INSERT INTO `savjet_dana` (`id`, `tekst`, `vrsta_korisnika`) VALUES
(1, '<p>...da je Charles Babbage, matematiÄ�ar i filozof iz 19. vijeka za kojeg se smatra da je otac ideje prvog programabilnog raÄ�unara, u svojoj biografiji napisao:</p>\r\n\r\n<p><i>U dva navrata su me pitali</i></p>\r\n\r\n<p><i>"Molim Vas gospodine Babbage, ako u VaÅ¡u maÅ¡inu stavite pogreÅ¡ne brojeve, da li Ä‡e izaÄ‡i taÄ�ni odgovori?"</i></p>\r\n\r\n<p><i>Jednom je to bio pripadnik Gornjeg, a jednom Donjeg doma. Ne mogu da potpuno shvatim tu vrstu konfuzije ideja koja bi rezultirala takvim pitanjem.</i></p>', 'nastavnik'),
(2, '<p>...da sada moÅ¾ete podesiti sistem bodovanja na vaÅ¡em predmetu (broj bodova koje studenti dobijaju za ispite, prisustvo, zadaÄ‡e, seminarski rad, projekte...)?</p>\r\n<ul><li>Kliknite na dugme [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite opciju <i>Sistem bodovanja</i>.</li>\r\n<li>Slijedite uputstva.</li></ul>\r\n<p><b>VaÅ¾na napomena:</b> Promjena sistema bodovanja moÅ¾e dovesti do gubitka do sada upisanih bodova na predmetu!</p>', 'nastavnik'),
(3, '<p>...da moÅ¾ete pristupiti Dosjeu studenta sa svim podacima koji se tiÄ�u uspjeha studenta na datom predmetu? Dosje studenta sadrÅ¾i, izmeÄ‘u ostalog:</p>\r\n<ul><li>Fotografiju studenta;</li>\r\n<li>Koliko puta je student ponavljao predmet, da li je u koliziji, da li je prenio predmet na viÅ¡u godinu;</li>\r\n<li>Sve podatke sa pogleda grupe (prisustvo, zadaÄ‡e, rezultati ispita, konaÄ�na ocjena) sa moguÄ‡noÅ¡Ä‡u izmjene svakog podatka;</li>\r\n<li>Za ispite i konaÄ�nu ocjenu moÅ¾ete vidjeti dnevnik izmjena sa informacijom ko je i kada izmijenio podatak.</li>\r\n<li>Brze linkove na dosjee istog studenta sa ranijih akademskih godina (ako je ponavljao/la predmet).</li></ul>\r\n\r\n<p>Dosjeu studenta moÅ¾ete pristupiti tako Å¡to kliknete na ime studenta u pregledu grupe. Na vaÅ¡em poÄ�etnom ekranu kliknite na ime grupe ili link <i>(Svi studenti)</i>, a zatim na ime i prezime studenta.</p>\r\n	\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik'),
(4, '<p>...da moÅ¾ete ostavljati kratke tekstualne komentare na rad studenata?</p>\r\n<p>Na vaÅ¡em poÄ�etnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>. Zatim kliknite na ikonu sa oblaÄ�iÄ‡em pored imena studenta:<br>\r\n<img src="images/16x16/komentar-plavi.png" width="16" height="16"></p>\r\n<p>MoÅ¾ete dobiti pregled studenata sa komentarima na sljedeÄ‡i naÄ�in:<br>\r\n<ul><li>Pored naziva predmeta kliknite na link [EDIT].</li>\r\n<li>Zatim s lijeve strane kliknite na link <i>IzvjeÅ¡taji</i>.</li>\r\n<li>KonaÄ�no, kliknite na opciju <i>Spisak studenata</i> - <i>Sa komentarima na rad</i>.</li></ul>\r\n<p>Na istog studenta moÅ¾ete ostaviti viÅ¡e komentara pri Ä�emu je svaki komentar datiran i oznaÄ�eno je ko ga je ostavio.</p>	\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 7-8.</i></p>', 'nastavnik'),
(5, '<p>...da moÅ¾ete brzo i lako pomoÄ‡u nekog spreadsheet programa (npr. MS Excel) kreirati grupe na predmetu?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>IzvjeÅ¡taji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvjeÅ¡taja:<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"><br>\r\nDobiÄ‡ete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte naziv grupe npr. "Grupa 1" (bez navodnika). Koristite Copy i Paste opcije Excela da biste brzo definisali grupu za sve studente.</li>\r\n<li>Kada zavrÅ¡ite definisanje grupa, koristeÄ‡i tipku Shift i tipke sa strelicama oznaÄ�ite imena studenata i imena grupa. Nemojte oznaÄ�iti naslov niti redni broj. DrÅ¾eÄ‡i tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vaÅ¡eg web preglednika da se vratite na spisak izvjeÅ¡taja. Sada s lijeve strane izaberite opciju <i>Grupe za predavanja i vjeÅ¾be</i>.</li>\r\n<li>Pozicionirajte kursor miÅ¡a u polje ispod naslova <i>Masovni unos studenata u grupe</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger Ä‡e vam ponuditi joÅ¡ jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li></ul>\r\n<p>Ovim su grupe kreirane!</p>\r\n\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 16.</i></p>', 'nastavnik'),
(6, '<p>...da moÅ¾ete brzo i lako ocijeniti zadaÄ‡u svim studentima na predmetu ili u grupi, koristeÄ‡i neki spreadsheet program (npr. MS Excel)?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>IzvjeÅ¡taji</i>, a s desne izaberite izvjeÅ¡taj <i>Spisak studenata</i> - <i>Bez grupa</i>. Alternativno, ako Å¾elite unositi ocjene samo za jednu grupu, moÅ¾ete koristiti izvjeÅ¡taj <i>Jedna kolona po grupama</i> pa u Excelu pobrisati sve grupe osim one koja vas interesuje.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvjeÅ¡taja:<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"></li>\r\n<li>Pored imena svakog studenta nalazi se broj indeksa. <b>Umjesto broja indeksa</b> upiÅ¡ite broj bodova ostvarenih na odreÄ‘enom zadatku odreÄ‘ene zadaÄ‡e.</li>\r\n<li>KoriÅ¡tenjem tipke Shift i tipki sa strelicama izaberite samo imena studenata i bodove. Nemojte selektovati naslov ili redne brojeve. DrÅ¾eÄ‡i tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vaÅ¡eg web preglednika da se vratite na spisak izvjeÅ¡taja. Sada s lijeve strane izaberite opciju <i>Kreiranje i unos zadaÄ‡a</i>.</li>\r\n<li>Uvjerite se da je na spisku <i>PostojeÄ‡e zadaÄ‡e</i> definisana zadaÄ‡a koju Å¾elite unijeti. Ako nije, popunite formular ispod naslova <i>Kreiranje zadaÄ‡e</i> sa odgovarajuÄ‡im podacima.</li>\r\n<li>Pozicionirajte kursor miÅ¡a u polje ispod naslova <i>Masovni unos zadaÄ‡a</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>U polju <i>Izaberite zadaÄ‡u</i> odaberite upravo kreiranu zadaÄ‡u. Ako zadaÄ‡a ima viÅ¡e zadataka, u polju <i>Izaberite zadatak</i> odaberite koji zadatak masovno unosite.\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger Ä‡e vam ponuditi joÅ¡ jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li>\r\n<li>Ovu proceduru sada vrlo lako moÅ¾ete ponoviti za sve zadatke i sve zadaÄ‡e zato Å¡to veÄ‡ imate u Excelu sve podatke osim broja bodova.</li></ul>\r\n<p>Ovim su rezultati zadaÄ‡e uneseni za sve studente!</p>\r\n\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 27-28.</i></p>', 'nastavnik'),
(12, '<p>...da moÅ¾ete ograniÄ�iti format datoteke u kojem studenti Å¡alju zadaÄ‡u?</p>\r\n<p>Prilikom kreiranja nove zadaÄ‡e, oznaÄ�ite opciju pod nazivom <i>Slanje zadatka u formi attachmenta</i>. PojaviÄ‡e se spisak tipova datoteka koje studenti mogu koristiti prilikom slanja zadaÄ‡e u formi attachmenta.</p>\r\n<p>Izaberite jedan ili viÅ¡e formata kako bi studenti dobili greÅ¡ku u sluÄ�aju da pokuÅ¡aju poslati zadaÄ‡u u nekom od formata koje niste izabrali. Ako ne izaberete nijednu od ponuÄ‘enih opcija, biÄ‡e dozvoljeni svi formati datoteka, ukljuÄ�ujuÄ‡i i one koji nisu navedeni na spisku.</p>\r\n\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 26-27.</i></p>', 'nastavnik'),
(7, '<p>...da moÅ¾ete preuzeti odjednom sve zadaÄ‡e koje su poslali studenti u grupi u formi ZIP fajla, pri Ä�emu su zadaÄ‡e imenovane po sistemu Prezime_Ime_BrojIndeksa?</p>\r\n<ul><li>Na vaÅ¡em poÄ�etnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>.</li>\r\n<li>U zaglavlju tabele sa spiskom studenata moÅ¾ete vidjeti navedene zadaÄ‡e: npr. ZadaÄ‡a 1, ZadaÄ‡a 2 itd.</li>\r\n<li>Ispod naziva svake zadaÄ‡e nalazi se rijeÄ� <i>Download</i> koja predstavlja link - kliknite na njega.</li></ul>	\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 11-12.</i></p>', 'nastavnik'),
(8, '<p>...da moÅ¾ete imati viÅ¡e termina jednog ispita? Pri tome se datum termina ne mora poklapati sa datumom ispita.</p>\r\n<p>Datum ispita se daje samo okvirno, kako bi se po neÄ�emu razlikovali npr. junski rok i septembarski rok. Datum koji studentu piÅ¡e na prijavi je datum koji pridruÅ¾ite terminu za prijavu ispita.</p>\r\n<p>Da biste definisali termine ispita:</p>\r\n<ul><li>Najprije kreirajte ispit, tako Å¡to Ä‡ete kliknuti na link [EDIT] a zatim izabrati opciju Ispiti s lijeve strane. Zatim popunite formular ispod naslova <i>Kreiranje novog ispita</i>.</li>\r\n<li>U tabeli ispita moÅ¾ete vidjeti novi ispit. Desno od ispita moÅ¾ete vidjeti link <i>Termini</i>. Kliknite na njega.</li>\r\n<li>Zatim kreirajte proizvoljan broj termina popunjavajuÄ‡i formular ispod naslova <i>Registrovanje novog termina</i>.</li></ul>\r\n\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, poglavlje "Prijavljivanje za ispit" (str. 21-26).</i></p>', 'nastavnik'),
(9, '<p>...da, u sluÄ�aju da se neki student nije prijavio/la za vaÅ¡ ispit, moÅ¾ete ih manuelno prijaviti na termin kako bi imao/la korektan datum na prijavi?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta. S lijeve strane izaberite link <i>Ispiti</i>.</li>\r\n<li>U tabeli ispita locirajte ispit koji Å¾elite i kliknite na link <i>Termini</i> desno od Å¾eljenog ispita.</li>\r\n<li>Ispod naslova <i>Objavljeni termini</i> izaberite Å¾eljeni termin i kliknite na link <i>Studenti</i> desno od Å¾eljenog termina.</li>\r\n<li>Sada moÅ¾ete vidjeti sve studente koji su se prijavili za termin. Pored imena i prezimena studenta moÅ¾ete vidjeti dugme <i>Izbaci</i> kako student viÅ¡e ne bi bio prijavljen za taj termin.</li>\r\n<li>Ispod tabele studenata moÅ¾ete vidjeti padajuÄ‡i spisak svih studenata upisanih na vaÅ¡ predmet. Izaberite na padajuÄ‡em spisku studenta kojeg Å¾elite prijaviti za termin i kliknite na dugme <i>Dodaj</i>.</li></ul>\r\n\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 26.</i></p>', 'nastavnik'),
(10, '<p>...da upisom studenata na predmete u Zamgeru sada u potpunosti rukuje Studentska sluÅ¾ba?</p>\r\n<p>Ako vam se pojavi student kojeg nemate na spiskovima u Zamgeru, recite mu da se <b>obavezno</b> javi u Studentsku sluÅ¾bu, ne samo radi vaÅ¡eg predmeta nego generalno radi regulisanja statusa (npr. neplaÄ‡enih Å¡kolarina, taksi i sliÄ�no).</p>', 'nastavnik'),
(11, '<p>...da svaki korisnik moÅ¾e imati jedan od tri nivoa pristupa bilo kojem predmetu:</p><ul><li><i>asistent</i> - moÅ¾e unositi prisustvo Ä�asovima i ocjenjivati zadaÄ‡e</li><li><i>super-asistent</i> - moÅ¾e unositi sve podatke osim konaÄ�ne ocjene</li><li><i>nastavnik</i> - moÅ¾e unositi i konaÄ�nu ocjenu.</li></ul><p>PoÄ�etni nivoi pristupa se odreÄ‘uju na osnovu zvaniÄ�no usvojenog nastavnog ansambla, a u sluÄ�aju da Å¾elite promijeniti nivo pristupa bez izmjena u ansamblu (npr. kako biste asistentu dali privilegije unosa rezultata ispita), kontaktirajte Studentsku sluÅ¾bu.</p>\r\n\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 3-4.</i></p>', 'nastavnik'),
(13, '<p>...da moÅ¾ete utjecati na format u kojem se izvjeÅ¡taj prosljeÄ‘uje Excelu kada kliknete na Excel ikonu u gornjem desnom uglu izvjeÅ¡taja?<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"></p>\r\n<p>MoÅ¾e se desiti da izvjeÅ¡taj ne izgleda potpuno kako treba u vaÅ¡em spreadsheet programu. Podaci se Å¡alju u CSV formatu pod pretpostavkom da koristite regionalne postavke za BiH (ili Hrvatsku ili Srbiju). Ako izvjeÅ¡taj u vaÅ¡em programu ne izgleda kako treba, slijedi nekoliko savjeta kako moÅ¾ete utjecati na to.</p>\r\n<ul><li>Ako se svi podaci nalaze u jednoj koloni, vjerovatno je da koristite sistem sa AmeriÄ�kim regionalnim postavkama. U vaÅ¡em Profilu moÅ¾ete pod Zamger opcije izabrati CSV separator "zarez" umjesto "taÄ�ka-zarez", ali vjerovatno je da vam naÅ¡a slova i dalje neÄ‡e izgledati kako treba.</li>\r\n<li>MoguÄ‡e je da Ä‡e dokument izgledati ispravno, osim slova sa afrikatima koja Ä‡e biti zamijenjena nekim drugim. Na Å¾alost, ne postoji naÄ�in da se ovo rijeÅ¡i. Excel moÅ¾e uÄ�itati CSV datoteke iskljuÄ�ivo u formatu koji ne podrÅ¾ava prikaz naÅ¡ih slova. MoÅ¾ete uraditi zamjenu koristeÄ‡i Replace opciju vaÅ¡eg programa. NeÅ¡to sloÅ¾enija varijanta je da koristite "Save Link As" opciju vaÅ¡eg web preglednika, promijenite naziv dokumenta iz izvjestaj.csv u izvjestaj.txt, a zatim koristite <a href="http://office.microsoft.com/en-us/excel-help/text-import-wizard-HP010102244.aspx">Excel Text Import Wizard</a>.</li>\r\n<li>Ako koristite OpenOffice.org uredski paket, prilikom otvaranja dokumenta izaberite Text encoding "Eastern European (Windows-1250)", a kao razdjelnik (Delimiter) izaberite taÄ�ka-zarez (Semicolon). Ostale opcije obavezno iskljuÄ�ite. TakoÄ‘e iskljuÄ�ite opciju spajanja razdjelnika (Merge delimiters).</li>\r\n<li>MoÅ¾e se desiti da vaÅ¡ program prepozna odreÄ‘ene stavke (npr. redne brojeve ili ostvarene bodove) kao datum, pogotovo ako ste posluÅ¡ali savjet iz prve taÄ�ke - odnosno, ako ste kao CSV separator podesili "zarez".</li>\r\n<li>U velikoj veÄ‡ini sluÄ�ajeva moÅ¾ete dobiti potpuno zadovoljavajuÄ‡e rezultate ako otvorite prazan dokument u vaÅ¡em spreadsheet programu (npr. Excel) i zatim napravite copy-paste kompletnog sadrÅ¾aja web stranice.</li></ul>\r\n\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, strana 32-33.</i></p>', 'nastavnik'),
(14, '<p>...da moÅ¾ete brzo i lako pomoÄ‡u nekog spreadsheet programa (npr. MS Excel) unijeti rezultate ispita ili konaÄ�ne ocjene?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>IzvjeÅ¡taji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>. Ili, ako vam je lakÅ¡e unositi podatke po grupama, izaberite izvjeÅ¡taj <i>Jedna kolona po grupama</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvjeÅ¡taja:<br>\r\n<img src="images/32x32/excel.png" width="32" height="32"><br>\r\nDobiÄ‡ete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte broj bodova koje je student ostvario na ispitu ili konaÄ�nu ocjenu.</li>\r\n<li>Kada zavrÅ¡ite unos rezultata/ocjena, koristeÄ‡i tipku Shift i tipke sa strelicama oznaÄ�ite imena studenata i ocjene. Nemojte oznaÄ�iti naslov niti redni broj studenta. DrÅ¾eÄ‡i tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vaÅ¡eg web preglednika da se vratite na spisak izvjeÅ¡taja.</li>\r\n<li>Ako unosite konaÄ�ne ocjene, s lijeve strane izaberite opciju <i>KonaÄ�na ocjena</i>.</li>\r\n<li>Ako unosite rezultate ispita, s lijeve strane izaberite opciju <i>Ispiti</i>, kreirajte novi ispit, a zatim kliknite na link <i>Masovni unos rezultata</i> pored novokreiranog ispita.</li>\r\n<li>Pozicionirajte kursor miÅ¡a u polje ispod naslova <i>Masovni unos ocjena</i> i pritisnite Ctrl+V. Trebalo bi da ugledate rezultate ispita odnosno ocjene.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> (a ne Prezime[TAB]Ime), te da pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger Ä‡e vam ponuditi joÅ¡ jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li></ul>\r\n<p>Ovim su unesene ocjene / rezultati ispita!</p>\r\n\r\n\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 18-20 (masovni unos ispita) i str. 28-29 (masovni unos konaÄ�ne ocjene).</i></p>', 'nastavnik'),
(15, '<p>...da kod evidencije prisustva, pored stanja "prisutan" (zelena boja) i stanja "odsutan" (crvena boja) postoji i nedefinisano stanje (Å¾uta boja). Ovo stanje se dodjeljuje ako je student upisan u grupu nakon Å¡to su odrÅ¾ani odreÄ‘eni Ä�asovi.</p>\r\n<p>DreÄ�avo Å¾uta boja je odabrana kako bi se predmetni nastavnik odnosno asistent podsjetio da se mora odluÄ�iti da li Ä‡e studentu priznati Ä�asove kao prisustva ili ne. U meÄ‘uvremenu, nedefinisano stanje Ä‡e se tumaÄ�iti u korist studenta, odnosno neÄ‡e ulaziti u broj izostanaka prilikom odreÄ‘ivanja da li je student izgubio bodove za prisustvo.</p>\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik'),
(16, '<p>...da ne morate voditi evidenciju o prisustvu kroz Zamger ako ne Å¾elite, a i dalje moÅ¾ete imati aÅ¾uran broj bodova ostvarenih na prisustvo?</p>\r\n<p>Sistem bodovanja je takav da student dobija 10 bodova ako je odsustvovao manje od 4 puta, a 0 bodova ako je odsustvovao 4 ili viÅ¡e puta. Podaci o konkretnim odrÅ¾anim Ä�asovima u Zamgeru se ne koriste nigdje osim za internu evidenciju na predmetu.</p>\r\n<p>Dakle, u sluÄ�aju da imate vlastitu evidenciju, samo kreirajte Ä�etiri Ä�asa (datum je nebitan) i unesite Ä�etiri izostanka studentima koji nisu zadovoljili prisustvo.</p>	\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 4-5.</i></p>', 'nastavnik'),
(17, '<p>...da moÅ¾ete podesiti drugaÄ�iji sistem bodovanja za prisustvo od ponuÄ‘enog?</p>\r\n<p>MoÅ¾ete podesiti ukupan broj bodova za prisustvo (razliÄ�it od 10). MoÅ¾ete promijeniti maksimalan broj dozvoljenih izostanaka (razliÄ�it od 3) ili pak podesiti linearno bodovanje u odnosu na broj izostanaka (npr. ako je student od 14 Ä�asova izostao 2 puta, dobiÄ‡e (12/14)*10 = 8,6 bodova). KonaÄ�no, umjesto evidencije pojedinaÄ�nih Ä�asova, moÅ¾ete odabrati da direktno unosite broj bodova za prisustvo po uzoru na rezultate ispita.</p>\r\n<p>Da biste aktivirali ovu moguÄ‡nost, trebate promijeniti sistem bodovanja samog predmeta.</p>', 'nastavnik'),
(18, '<p>...da moÅ¾ete unijeti bodove za zadaÄ‡u Ä�ak i ako je student nije poslao kroz Zamger?</p>\r\n<p>Da biste to uradili, potrebno je da kliknete na link <i>PrikaÅ¾i dugmad za kreiranje zadataka</i> koji se nalazi u dnu stranice sa prikazom grupe (vidi sliku). Nakon Å¡to ovo uradite, Ä‡elije tabele koje odgovaraju neposlanim zadaÄ‡ama Ä‡e se popuniti ikonama za kreiranje zadaÄ‡e koje imaju oblik sijalice.</p>\r\n<p><a href="doc/savjet_sijalice.png" target="_new">Slika</a> - ukoliko ne vidite detalje, raÅ¡irite prozor!</p>	\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 10-11.</i></p>\r\n<p>U sluÄ�aju da se na vaÅ¡em predmetu zadaÄ‡e generalno ne Å¡alju kroz Zamger, vjerovatno Ä‡e brÅ¾i naÄ�in rada za vas biti da koristite masovni unos. ViÅ¡e informacija na str. 27-28. Uputstava.</p>', 'nastavnik'),
(19, '<p>...da pomoÄ‡u Zamgera moÅ¾ete poslati cirkularni mail svim studentima na vaÅ¡em predmetu ili u pojedinim grupama?</p>\r\n<p>Da biste pristupili ovoj opciji:</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta</li>\r\n<li>U meniju sa lijeve strane odaberite opciju <i>ObavjeÅ¡tenja za studente</i>.</li>\r\n<li>Pod menijem <i>ObavjeÅ¡tenje za:</i> odaberite da li obavjeÅ¡tenje Å¡aljete svim studentima na predmetu ili samo studentima koji su Ä�lanovi odreÄ‘ene grupe.</li>\r\n<li>Aktivirajte opciju <i>Slanje e-maila</i>. Ako ova opcija nije aktivna, studenti Ä‡e i dalje vidjeti vaÅ¡e obavjeÅ¡tenje na svojoj Zamger poÄ�etnoj stranici (sekcija ObavjeÅ¡tenja) kao i putem RSSa.</li>\r\n<li>U dio pod naslovom <i>KraÄ‡i tekst</i> unesite udarnu liniju vaÅ¡e informacije.</li>\r\n<li>U dio pod naslovom <i>Detaljan tekst</i> moÅ¾ete napisati dodatna pojaÅ¡njenja, a moÅ¾ete ga i ostaviti praznim.</li>\r\n<li>Kliknite na dugme <i>PoÅ¡alji</i>. VidjeÄ‡ete jedno po jedno ime studenta kojem je poslan mail kao i e-mail adresu na koju je mail poslan. Slanje veÄ‡e koliÄ�ine mailova moÅ¾e potrajati nekoliko minuta.</li></ul>\r\n<p>Mailovi Ä‡e biti poslani na adrese koje su studenti podesili koristeÄ‡i svoj profil, ali i na zvaniÄ�ne fakultetske adrese.</p>\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 12-14.</i></p>', 'nastavnik'),
(20, '<p>...da je promjena grupe studenta destruktivna operacija kojom se nepovratno gube podaci o prisustvu studenta na Ä�asovima registrovanim za tu grupu?</p>\r\n<p>Studenta moÅ¾ete prebaciti u drugu grupu putem ekrana Dosje studenta: na pogledu grupe (npr. <i>Svi studenti</i>) kliknite na ime i prezime studenta da biste uÅ¡li u njegov ili njen dosje.</p>\r\n<p>Promjenom grupe nepovratno se gubi evidencija prisustva studenta na Ä�asovima registrovanim za prethodnu grupu. Naime, izmeÄ‘u Ä�asova registrovanih za dvije razliÄ�ite grupe ne postoji jednoznaÄ�no mapiranje. U nekom datom trenutku vremena u jednoj grupi moÅ¾e biti registrovano 10 Ä�asova a u drugoj 8. Kako znati koji od tih 10 Ä�asova odgovara kojem od onih 8? I Å¡ta raditi sa suviÅ¡nim Ä�asovima? Dakle, kada premjestite studenta u grupu u kojoj veÄ‡ postoje registrovani Ä�asovi, prisustvo studenta tim Ä�asovima Ä‡e biti oznaÄ�eno kao nedefinisano (Å¾uta boja). PrepuÅ¡ta se nastavnom ansamblu da odluÄ�i koje od tih Ä�asova Ä‡e priznati kao prisutne, a koje markirati kao odsutne. Vjerovatno Ä‡ete se pitati Å¡ta ako se student ponovo vrati u polaznu grupu. Odgovor je da Ä‡e podaci ponovo biti izgubljeni, jer Å¡ta raditi sa Ä�asovima registrovanim u meÄ‘uvremenu?</p>\r\n<p>PreporuÄ�ujemo da ne vrÅ¡ite promjene grupe nakon Å¡to poÄ�ne akademska godina.</p>\r\n	\r\n<p><i>ViÅ¡e informacija u <a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik');

-- --------------------------------------------------------

--
-- Table structure for table `septembar`
--

CREATE TABLE IF NOT EXISTS `septembar` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


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
  PRIMARY KEY  (`osoba`,`razred`,`redni_broj`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `srednja_ocjene`
--



-- --------------------------------------------------------

--
-- Table structure for table `srednja_skola`
--

CREATE TABLE IF NOT EXISTS `srednja_skola` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `domaca` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

--
-- Dumping data for table `srednja_skola`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stdin`
--


-- --------------------------------------------------------

--
-- Table structure for table `strucni_stepen`
--

CREATE TABLE IF NOT EXISTS `strucni_stepen` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

--
-- Dumping data for table `strucni_stepen`
--

INSERT INTO `strucni_stepen` (`id`, `naziv`, `titula`) VALUES
(1, 'Magistar elektrotehnike - Diplomirani inÅ¾enjer elektrotehnike', 'M.E.'),
(2, 'Bakalaureat elektrotehnike - InÅ¾enjer elektrotehnike', 'B.E.'),
(3, 'Diplomirani inÅ¾enjer elektrotehnike', 'dipl.ing.el.'),
(4, 'Diplomirani matematiÄ�ar', 'dipl.mat.'),
(5, 'Srednja struÄ�na sprema', ''),
(6, 'Diplomirani inÅ¾enjer maÅ¡instva', 'dipl.ing.'),
(7, 'Diplomirani inÅ¾enjer graÄ‘evinarstva', 'dipl.ing.'),
(8, 'Diplomirani ekonomista', 'dipl.ecc.');


-- --------------------------------------------------------

--
-- Table structure for table `studentski_modul`
--

CREATE TABLE IF NOT EXISTS `studentski_modul` (
  `id` int(11) NOT NULL,
  `modul` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `gui_naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `novi_prozor` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `studentski_modul`
--

INSERT INTO `studentski_modul` (`id`, `modul`, `gui_naziv`, `novi_prozor`) VALUES
(1, 'student/moodle', 'Materijali (Moodle)', 1),
(2, 'student/zadaca', 'Slanje zadaÄ‡e', 0),
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
  PRIMARY KEY  (`predmet`,`akademska_godina`,`studentski_modul`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `akademska_godina` int(11) NOT NULL,
  `nacin_studiranja` int(11) NOT NULL,
  `ponovac` tinyint(4) NOT NULL default '0',
  `odluka` int(11) NOT NULL default '0',
  `plan_studija` int(11) NOT NULL default '0',
  PRIMARY KEY  (`student`,`studij`,`semestar`,`akademska_godina`)
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
  `zavrsni_semestar` int(11) NOT NULL default '0',
  `institucija` int(11) NOT NULL default '0',
  `kratkinaziv` varchar(10) collate utf8_slovenian_ci NOT NULL,
  `moguc_upis` tinyint(1) NOT NULL,
  `tipstudija` int(11) NOT NULL,
  `preduslov` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `studij`
--

INSERT INTO `studij` (`id`, `naziv`, `zavrsni_semestar`, `institucija`, `kratkinaziv`, `moguc_upis`, `tipstudija`, `preduslov`) VALUES
(1, 'Prva godina studija', 2, 1, 'PGS', 0, 1, 0),
(2, 'RaÄ�unarstvo i informatika (BSc)', 6, 2, 'RI', 1, 2, 1),
(3, 'Automatika i elektronika (BSc)', 6, 3, 'AE', 1, 2, 1),
(4, 'Elektroenergetika (BSc)', 6, 4, 'EE', 1, 2, 1),
(5, 'Telekomunikacije (BSc)', 6, 5, 'TK', 1, 2, 1);

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
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
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
  `id` int(11) NOT NULL auto_increment,
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(5) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
  `prijemni_termin` int(11) NOT NULL auto_increment,
  `donja_granica` float NOT NULL,
  `gornja_granica` float NOT NULL,
  `kandidati_strani` int(5) NOT NULL,
  `kandidati_sami_placaju` int(5) NOT NULL,
  `kandidati_kanton_placa` int(5) NOT NULL,
  `prijemni_max` int(5) NOT NULL,
  `studij` int(11) NOT NULL,
  PRIMARY KEY  (`prijemni_termin`,`studij`)
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
  PRIMARY KEY  (`osoba`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `uspjeh_u_srednjoj`
--


-- --------------------------------------------------------

--
-- Table structure for table `zadaca`
--

CREATE TABLE IF NOT EXISTS `zadaca` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL default '0',
  `akademska_godina` int(11) NOT NULL default '0',
  `zadataka` tinyint(4) NOT NULL default '0',
  `bodova` float NOT NULL default '0',
  `rok` datetime default NULL,
  `aktivna` tinyint(1) NOT NULL default '0',
  `programskijezik` int(10) NOT NULL default '0',
  `attachment` tinyint(1) NOT NULL default '0',
  `dozvoljene_ekstenzije` varchar(255) collate utf8_slovenian_ci default NULL,
  `postavka_zadace` varchar(255) collate utf8_slovenian_ci default NULL,
  `komponenta` int(11) NOT NULL,
  `vrijemeobjave` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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
  PRIMARY KEY  (`id`),
  KEY `pomocni` (`zadaca`,`redni_broj`,`student`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

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


-- --------------------------------------------------------

--
-- Table structure for table `zvanje`
--

CREATE TABLE IF NOT EXISTS `zvanje` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `titula` varchar(10) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `zvanje`
--

INSERT INTO `zvanje` (`id`, `naziv`, `titula`) VALUES
(1, 'Redovni profesor', 'R. prof.'),
(2, 'Vanredni profesor', 'V. prof.'),
(3, 'Docent', 'Doc.'),
(4, 'ViÅ¡i asistent', 'V. asis.'),
(5, 'Asistent', 'Asis.'),
(6, 'Profesor emeritus', '');


-- --------------------------------------------------------

-- HARIS AGIC START
--
-- Table structure for table `bb_post`
--


CREATE TABLE IF NOT EXISTS `bb_post` (
  `id` int(11) NOT NULL,
  `naslov` varchar(300) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  `tema` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bb_post_text`
--


CREATE TABLE IF NOT EXISTS `bb_post_text` (
  `post` int(11) NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`post`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bb_tema`
--


CREATE TABLE  IF NOT EXISTS `bb_tema` (
  `id` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `prvi_post` int(11) NOT NULL default '0',
  `zadnji_post` int(11) NOT NULL default '0',
  `pregleda` int(11) unsigned NOT NULL default '0',
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bl_clanak`
--


CREATE TABLE  IF NOT EXISTS `bl_clanak` (
  `id` int(11) NOT NULL,
  `naslov` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  `slika` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `predmet_projektni_parametri`
--


CREATE TABLE  IF NOT EXISTS `predmet_projektni_parametri` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL default '0',
  `min_timova` tinyint(3) NOT NULL,
  `max_timova` tinyint(3) NOT NULL,
  `min_clanova_tima` tinyint(3) NOT NULL,
  `max_clanova_tima` tinyint(3) NOT NULL,
  `zakljucani_projekti` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`predmet`,`akademska_godina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projekat`
--


CREATE TABLE  IF NOT EXISTS `projekat` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL default '0',
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `biljeska` text collate utf8_slovenian_ci,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projekat_file`
--


CREATE TABLE  IF NOT EXISTS `projekat_file` (
  `id` int(11) NOT NULL,
  `filename` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `revizija` tinyint(4) NOT NULL,
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  `file` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projekat_file_diff`
--


CREATE TABLE  IF NOT EXISTS `projekat_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projekat_link`
--


CREATE TABLE  IF NOT EXISTS `projekat_link` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `url` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projekat_rss`
--


CREATE TABLE  IF NOT EXISTS `projekat_rss` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `url` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_projekat`
--


CREATE TABLE  IF NOT EXISTS `student_projekat` (
  `student` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`projekat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- HARIS AGIC END



-- ADMIR HERIC START
-- --------------------------------------------------------
--
-- Table structure for table `ispit_termin`
--
DROP TABLE IF EXISTS `ispit_termin`;
CREATE TABLE IF NOT EXISTS `ispit_termin` (
  `id` int(11) NOT NULL auto_increment,
  `datumvrijeme` datetime NOT NULL default '0000-00-00 00:00:00',
  `maxstudenata` int(11) NOT NULL,
  `deadline` datetime NOT NULL default '0000-00-00 00:00:00',
  `ispit` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;
-- --------------------------------------------------------
--
-- Table structure for table `student_ispit_termin`
--
DROP TABLE IF EXISTS `student_ispit_termin`;
CREATE TABLE IF NOT EXISTS `student_ispit_termin` (
  `student` int(11) NOT NULL,
  `ispit_termin` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ADMIR HERIC END


-- SOFTIC NERMIN START
-- -------------------------------------------------------

--
-- Table structure for table `anketa`
--

CREATE TABLE IF NOT EXISTS `anketa_anketa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datum_otvaranja` datetime DEFAULT NULL,
  `datum_zatvaranja` datetime DEFAULT NULL,
  `naziv` char(255) NOT NULL,
  `opis` text,
  `aktivna` tinyint(1) DEFAULT '0',
  `editable` tinyint(1) DEFAULT '1',
  `akademska_godina` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=16 ;



--
-- Table structure for table `izbori_pitanja`
--

CREATE TABLE IF NOT EXISTS `anketa_izbori_pitanja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pitanje` int(10) unsigned NOT NULL,
  `izbor` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=26 ;



--
-- Table structure for table `odgovor_rank`
--

CREATE TABLE IF NOT EXISTS `anketa_odgovor_rank` (
  `rezultat` int(10) unsigned NOT NULL,
  `pitanje` int(10) unsigned NOT NULL,
  `izbor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`rezultat`,`pitanje`,`izbor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;


-- 
-- Table structure for table `ekstenzije`
-- 

CREATE TABLE `ekstenzije` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `naziv` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=26 ;



--
-- Table structure for table `odgovor_text`
--

CREATE TABLE IF NOT EXISTS `anketa_odgovor_text` (
  `rezultat` int(10) unsigned NOT NULL,
  `pitanje` int(10) unsigned NOT NULL,
  `odgovor` text,
  PRIMARY KEY (`rezultat`,`pitanje`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;



--
-- Table structure for table `pitanje`
--

CREATE TABLE IF NOT EXISTS `anketa_pitanje` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa` int(10) unsigned NOT NULL DEFAULT '0',
  `tip_pitanja` int(10) unsigned NOT NULL,
  `tekst` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=92 ;



-- --------------------------------------------------------

--
-- Table structure for table `rezultat`
--

CREATE TABLE IF NOT EXISTS `anketa_rezultat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anketa` int(10) unsigned NOT NULL,
  `vrijeme` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `zavrsena` enum('Y','N') DEFAULT 'N',
  `predmet` int(11) DEFAULT NULL,
  `unique_id` varchar(50) DEFAULT NULL,
  `akademska_godina` int(10) NOT NULL,
  `studij` int(10) NOT NULL,
  `semestar` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unique_id` (`unique_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=27 ;



--
-- Table structure for table `tip_pitanja`
--

CREATE TABLE IF NOT EXISTS `anketa_tip_pitanja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tip` char(32) NOT NULL,
  `postoji_izbor` enum('Y','N') NOT NULL,
  `tabela_odgovora` char(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `tip_pitanja`
--

INSERT INTO `anketa_tip_pitanja` (`id`, `tip`, `postoji_izbor`, `tabela_odgovora`) VALUES
(1, 'Ocjena (skala 1..5)', 'Y', 'odgovor_rank'),
(2, 'Komentar', 'N', 'odgovor_text');

-- SOFTIC NERMIN END
-- -------------------------------------------------------
--
-- Table structure for table `moodle_predmet_rss`
--

CREATE TABLE IF NOT EXISTS `moodle_predmet_rss` (
  `id` int(11) NOT NULL auto_increment,
  `vrstanovosti` int(2) NOT NULL,
  `moodle_id` int(11) NOT NULL,
  `sadrzaj` text collate utf8_slovenian_ci NOT NULL,
  `vrijeme_promjene` bigint(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `moodle_predmet_rss`
--
-- -------------------------------------------------------

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `raspored`
--


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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `raspored_sala`
--


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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `raspored_stavka`
--

DROP TABLE IF EXISTS `anketa_predmet`;
CREATE TABLE IF NOT EXISTS `anketa_predmet` (   
  `anketa` int(11) NOT NULL,   
  `predmet` int(11) NOT NULL,   
  `akademska_godina` int(11) NOT NULL,   
  `aktivna` tinyint(1) NOT NULL,   PRIMARY KEY  (`anketa`,`predmet`,`akademska_godina`) 
 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;
