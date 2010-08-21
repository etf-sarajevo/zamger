-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 22, 2010 at 12:06 AM
-- Server version: 5.0.27
-- PHP Version: 5.2.1
-- 
-- Database: `zamger`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `akademska_godina`
-- 

CREATE TABLE `akademska_godina` (
  `id` int(11) NOT NULL,
  `naziv` varchar(20) collate utf8_slovenian_ci NOT NULL default '',
  `aktuelna` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `akademska_godina`
-- 

INSERT INTO `akademska_godina` (`id`, `naziv`, `aktuelna`) VALUES 
(1, '2008/2009', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `anketa_anketa`
-- 

CREATE TABLE `anketa_anketa` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `datum_otvaranja` datetime default NULL,
  `datum_zatvaranja` datetime default NULL,
  `naziv` char(255) collate utf8_slovenian_ci NOT NULL,
  `opis` text collate utf8_slovenian_ci,
  `aktivna` tinyint(1) default '0',
  `editable` tinyint(1) default '1',
  `akademska_godina` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=16 ;

-- 
-- Dumping data for table `anketa_anketa`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `anketa_izbori_pitanja`
-- 

CREATE TABLE `anketa_izbori_pitanja` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pitanje` int(10) unsigned NOT NULL,
  `izbor` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=26 ;

-- 
-- Dumping data for table `anketa_izbori_pitanja`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `anketa_odgovor_rank`
-- 

CREATE TABLE `anketa_odgovor_rank` (
  `rezultat` int(10) unsigned NOT NULL,
  `pitanje` int(10) unsigned NOT NULL,
  `izbor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`rezultat`,`pitanje`,`izbor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `anketa_odgovor_rank`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `anketa_odgovor_text`
-- 

CREATE TABLE `anketa_odgovor_text` (
  `rezultat` int(10) unsigned NOT NULL,
  `pitanje` int(10) unsigned NOT NULL,
  `odgovor` text collate utf8_slovenian_ci,
  PRIMARY KEY  (`rezultat`,`pitanje`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `anketa_odgovor_text`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `anketa_pitanje`
-- 

CREATE TABLE `anketa_pitanje` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `anketa` int(10) unsigned NOT NULL default '0',
  `tip_pitanja` int(10) unsigned NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=92 ;

-- 
-- Dumping data for table `anketa_pitanje`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `anketa_rezultat`
-- 

CREATE TABLE `anketa_rezultat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `anketa` int(10) unsigned NOT NULL,
  `vrijeme` timestamp NULL default '0000-00-00 00:00:00',
  `zavrsena` enum('Y','N') collate utf8_slovenian_ci default 'N',
  `predmet` int(11) default NULL,
  `unique_id` varchar(50) collate utf8_slovenian_ci default NULL,
  `akademska_godina` int(10) NOT NULL,
  `studij` int(10) NOT NULL,
  `semestar` int(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=27 ;

-- 
-- Dumping data for table `anketa_rezultat`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `anketa_tip_pitanja`
-- 

CREATE TABLE `anketa_tip_pitanja` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tip` char(32) collate utf8_slovenian_ci NOT NULL,
  `postoji_izbor` enum('Y','N') collate utf8_slovenian_ci NOT NULL,
  `tabela_odgovora` char(32) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
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

CREATE TABLE `auth` (
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
(1, 'admin', 'admin', 0, '', 1),
(2, 'jasmin', 'krcalo', 0, '', 1),
(3, 'fahrudin', 'halilovic', 0, '', 1),
(4, 'muris', 'agic', 0, '', 1),
(5, 'huse', 'fatkic', 0, '', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `bb_post`
-- 

CREATE TABLE `bb_post` (
  `id` int(11) NOT NULL,
  `naslov` varchar(300) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  `tema` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `bb_post`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `bb_post_text`
-- 

CREATE TABLE `bb_post_text` (
  `post` int(11) NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`post`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `bb_post_text`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `bb_tema`
-- 

CREATE TABLE `bb_tema` (
  `id` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `prvi_post` int(11) NOT NULL default '0',
  `zadnji_post` int(11) NOT NULL default '0',
  `pregleda` int(11) unsigned NOT NULL default '0',
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `bb_tema`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `bl_clanak`
-- 

CREATE TABLE `bl_clanak` (
  `id` int(11) NOT NULL,
  `naslov` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  `slika` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `bl_clanak`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `cas`
-- 

CREATE TABLE `cas` (
  `id` int(11) NOT NULL auto_increment,
  `datum` date NOT NULL default '0000-00-00',
  `vrijeme` time NOT NULL default '00:00:00',
  `labgrupa` int(11) NOT NULL default '0',
  `nastavnik` int(11) NOT NULL default '0',
  `komponenta` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10 ;

-- 
-- Dumping data for table `cas`
-- 

INSERT INTO `cas` (`id`, `datum`, `vrijeme`, `labgrupa`, `nastavnik`, `komponenta`) VALUES 
(1, '2010-08-04', '12:15:00', 1, 5, 5),
(2, '2010-08-04', '12:16:00', 1, 5, 5),
(3, '2010-08-04', '12:16:00', 1, 5, 5),
(4, '2010-08-04', '12:16:00', 1, 5, 5),
(5, '2010-08-04', '12:16:00', 1, 5, 5),
(6, '2010-08-04', '12:16:00', 1, 5, 5),
(7, '2010-08-04', '12:16:00', 1, 5, 5);

-- --------------------------------------------------------

-- 
-- Table structure for table `etf_moodle`
-- 

CREATE TABLE `etf_moodle` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `moodle_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `etf_moodle`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `institucija`
-- 

CREATE TABLE `institucija` (
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
(2, 'Odsjek za računarstvo i informatiku', 1, 'RI'),
(3, 'Odsjek za automatiku i elektroniku', 1, 'AE'),
(4, 'Odsjek za elektroenergetiku', 1, 'EE'),
(5, 'Odsjek za telekomunikacije', 1, 'TK'),
(1, 'Elektrotehnički fakultet Sarajevo', 0, 'ETF');

-- --------------------------------------------------------

-- 
-- Table structure for table `ispit`
-- 

CREATE TABLE `ispit` (
  `id` int(11) NOT NULL auto_increment,
  `predmet` int(11) NOT NULL default '0',
  `akademska_godina` int(11) NOT NULL default '0',
  `datum` date NOT NULL default '0000-00-00',
  `komponenta` int(2) NOT NULL default '0',
  `vrijemeobjave` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `ispit`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `ispit_termin`
-- 

CREATE TABLE `ispit_termin` (
  `id` int(11) NOT NULL auto_increment,
  `datumvrijeme` datetime NOT NULL default '0000-00-00 00:00:00',
  `maxstudenata` int(11) NOT NULL,
  `deadline` datetime NOT NULL default '0000-00-00 00:00:00',
  `ispit` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;

-- 
-- Dumping data for table `ispit_termin`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `ispitocjene`
-- 

CREATE TABLE `ispitocjene` (
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

CREATE TABLE `kanton` (
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

CREATE TABLE `kolizija` (
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

CREATE TABLE `komentar` (
  `id` int(11) NOT NULL auto_increment,
  `student` int(11) NOT NULL default '0',
  `nastavnik` int(11) NOT NULL default '0',
  `labgrupa` int(11) NOT NULL default '0',
  `datum` datetime NOT NULL default '0000-00-00 00:00:00',
  `komentar` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `komentar`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `komponenta`
-- 

CREATE TABLE `komponenta` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(40) collate utf8_slovenian_ci NOT NULL,
  `gui_naziv` varchar(20) collate utf8_slovenian_ci NOT NULL,
  `kratki_gui_naziv` varchar(20) collate utf8_slovenian_ci NOT NULL,
  `tipkomponente` int(11) NOT NULL,
  `maxbodova` double NOT NULL,
  `prolaz` double NOT NULL,
  `opcija` varchar(100) collate utf8_slovenian_ci NOT NULL,
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

CREATE TABLE `komponentebodovi` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `komponenta` int(11) NOT NULL,
  `bodovi` double NOT NULL,
  PRIMARY KEY  (`student`,`predmet`,`komponenta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `komponentebodovi`
-- 

INSERT INTO `komponentebodovi` (`student`, `predmet`, `komponenta`, `bodovi`) VALUES 
(2, 1, 5, 10),
(4, 1, 5, 10),
(3, 1, 5, 10),
(2, 1, 6, 6),
(4, 1, 6, 2),
(3, 1, 6, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `konacna_ocjena`
-- 

CREATE TABLE `konacna_ocjena` (
  `student` int(11) NOT NULL default '0',
  `predmet` int(11) NOT NULL default '0',
  `akademska_godina` int(11) NOT NULL default '0',
  `ocjena` int(3) NOT NULL default '0',
  `datum` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
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

CREATE TABLE `labgrupa` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL default '',
  `predmet` int(11) NOT NULL default '0',
  `akademska_godina` int(11) NOT NULL default '0',
  `virtualna` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `labgrupa`
-- 

INSERT INTO `labgrupa` (`id`, `naziv`, `predmet`, `akademska_godina`, `virtualna`) VALUES 
(1, '(Svi studenti)', 1, 1, 1),
(2, 'Grupa 1', 1, 1, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `log`
-- 

CREATE TABLE `log` (
  `id` int(11) NOT NULL auto_increment,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `userid` int(11) NOT NULL default '0',
  `dogadjaj` varchar(255) collate utf8_slovenian_ci NOT NULL,
  `nivo` tinyint(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=3065 ;

-- 
-- Dumping data for table `log`
-- 

INSERT INTO `log` (`id`, `vrijeme`, `userid`, `dogadjaj`, `nivo`) VALUES 
(1, '2010-08-03 21:52:51', 1, 'logout', 1),
(2, '2010-08-03 21:53:01', 1, 'login', 1),
(3, '2010-08-03 21:53:01', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(4, '2010-08-03 21:53:04', 1, 'studentska/intro', 1),
(5, '2010-08-03 21:53:12', 1, 'logout', 1),
(6, '2010-08-03 21:53:38', 1, 'login', 1),
(7, '2010-08-03 21:53:38', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(8, '2010-08-03 22:01:34', 1, '/zamger41/index.php?', 1),
(9, '2010-08-03 22:01:41', 1, 'logout', 1),
(10, '2010-08-03 22:01:56', 0, 'index.php greska: Pogrešna šifra admin ', 3),
(11, '2010-08-03 22:08:26', 1, 'login', 1),
(12, '2010-08-03 22:08:26', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(13, '2010-08-03 22:13:33', 1, '/zamger41/index.php?', 1),
(14, '2010-08-03 22:13:41', 1, 'logout', 1),
(15, '2010-08-03 22:14:49', 1, 'login', 1),
(16, '2010-08-03 22:14:49', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(17, '2010-08-03 23:03:13', 1, '/zamger41/index.php?', 1),
(18, '2010-08-03 23:03:15', 1, 'logout', 1),
(19, '2010-08-03 23:03:31', 1, 'login', 1),
(20, '2010-08-03 23:03:31', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(21, '2010-08-03 23:03:33', 1, 'studentska/intro', 1),
(22, '2010-08-03 23:03:34', 1, 'studentska/osobe', 1),
(23, '2010-08-03 23:03:48', 1, 'studentska/osobe akcija=novi ime=Jasmin prezime=Krčalo', 1),
(24, '2010-08-03 23:03:48', 1, 'dodan novi korisnik u2 (ID: 2)', 4),
(25, '2010-08-03 23:03:51', 1, 'studentska/osobe akcija=edit osoba=2', 1),
(26, '2010-08-03 23:04:29', 1, 'studentska/osobe akcija=edit osoba=2 subakcija=uloga student=1', 1),
(27, '2010-08-03 23:04:29', 1, 'osobi u2 data privilegija student', 4),
(28, '2010-08-03 23:06:34', 1, 'studentska/osobe akcija=podaci osoba=2 subakcija=uloga student=1', 1),
(29, '2010-08-03 23:06:46', 1, 'studentska/osobe akcija=edit osoba=2', 1),
(30, '2010-08-03 23:07:07', 1, 'studentska/osobe akcija=edit osoba=2 subakcija=auth stari_login= login=jasmin password=krcalo aktivan=1', 1),
(31, '2010-08-03 23:07:07', 1, 'dodan novi login ''jasmin'' za korisnika u2', 4),
(32, '2010-08-03 23:07:18', 1, 'studentska/osobe akcija=edit osoba=2 subakcija=uloga stari_login= login=jasmin password=krcalo aktivan=1 student=1', 1),
(33, '2010-08-03 23:07:27', 1, 'studentska/osobe search= offset=', 1),
(34, '2010-08-03 23:07:28', 1, 'studentska/osobe search=sve offset=', 1),
(35, '2010-08-03 23:08:09', 1, 'studentska/osobe search=sve offset= akcija=novi ime=Fahrudin prezime=Halilović', 1),
(36, '2010-08-03 23:08:09', 1, 'dodan novi korisnik u3 (ID: 3)', 4),
(37, '2010-08-03 23:08:10', 1, 'studentska/osobe akcija=edit osoba=3', 1),
(38, '2010-08-03 23:08:15', 1, 'studentska/osobe akcija=edit osoba=3 subakcija=auth stari_login= login= password=', 1),
(39, '2010-08-03 23:08:15', 1, 'prazan login za u3', 3),
(40, '2010-08-03 23:08:30', 1, 'studentska/osobe akcija=edit osoba=3 subakcija=auth stari_login= login=fahrudin password=halilovic aktivan=1', 1),
(41, '2010-08-03 23:08:31', 1, 'dodan novi login ''fahrudin'' za korisnika u3', 4),
(42, '2010-08-03 23:08:45', 1, 'studentska/osobe akcija=edit osoba=3 subakcija=uloga stari_login= login=fahrudin password=halilovic aktivan=1 student=1', 1),
(43, '2010-08-03 23:08:45', 1, 'osobi u3 data privilegija student', 4),
(44, '2010-08-03 23:08:52', 1, 'studentska/osobe search= offset=', 1),
(45, '2010-08-03 23:09:06', 1, 'studentska/osobe search= offset= akcija=novi ime=Muris prezime=Agić', 1),
(46, '2010-08-03 23:09:06', 1, 'dodan novi korisnik u4 (ID: 4)', 4),
(47, '2010-08-03 23:09:08', 1, 'studentska/osobe akcija=edit osoba=4', 1),
(48, '2010-08-03 23:09:18', 1, 'studentska/osobe akcija=edit osoba=4 subakcija=auth stari_login= login=muris password=agic aktivan=1', 1),
(49, '2010-08-03 23:09:18', 1, 'dodan novi login ''muris'' za korisnika u4', 4),
(50, '2010-08-03 23:09:22', 1, 'studentska/osobe akcija=edit osoba=4 subakcija=uloga stari_login= login=muris password=agic aktivan=1 student=1', 1),
(51, '2010-08-03 23:09:22', 1, 'osobi u4 data privilegija student', 4),
(52, '2010-08-03 23:09:25', 1, 'studentska/osobe search= offset=', 1),
(53, '2010-08-03 23:09:48', 1, 'studentska/predmeti', 1),
(54, '2010-08-03 23:10:39', 1, 'studentska/predmeti akcija=novi naziv=Inženjerska  Matematika 1', 1),
(55, '2010-08-03 23:10:39', 1, 'potpuno novi predmet pp1, akademska godina ag1', 4),
(56, '2010-08-03 23:10:47', 1, 'studentska/predmeti akcija=edit predmet=1 ag=1', 1),
(57, '2010-08-03 23:10:56', 1, 'studentska/predmeti predmet=1 ag=1 akcija=realedit', 1),
(58, '2010-08-03 23:11:11', 1, 'studentska/predmeti predmet=1 ag=1 akcija=realedit', 1),
(59, '2010-08-03 23:11:11', 1, 'izmijenjeni podaci o predmetu pp1', 4),
(60, '2010-08-03 23:11:15', 1, 'studentska/predmeti akcija=edit predmet=1 ag=1', 1),
(61, '2010-08-03 23:11:42', 1, 'studentska/predmeti predmet=1 ag=1 akcija=dodaj_pk', 1),
(62, '2010-08-03 23:12:10', 1, 'studentska/predmeti predmet=1 ag=1 akcija=dodaj_pk', 1),
(63, '2010-08-03 23:12:10', 1, 'dodana ponuda kursa na predmet pp1', 4),
(64, '2010-08-03 23:12:14', 1, 'studentska/predmeti akcija=edit predmet=1 ag=1', 1),
(65, '2010-08-03 23:12:18', 1, 'studentska/predmeti ag=1 search= offset=0', 1),
(66, '2010-08-03 23:12:35', 1, 'studentska/osobe', 1),
(67, '2010-08-03 23:12:51', 1, 'studentska/osobe akcija=novi ime=Huse prezime=Fatkić', 1),
(68, '2010-08-03 23:12:51', 1, 'dodan novi korisnik u5 (ID: 5)', 4),
(69, '2010-08-03 23:12:53', 1, 'studentska/osobe akcija=edit osoba=5', 1),
(70, '2010-08-03 23:13:10', 1, 'studentska/osobe akcija=edit osoba=5 subakcija=auth stari_login= login=huse password=fatkic aktivan=1', 1),
(71, '2010-08-03 23:13:10', 1, 'dodan novi login ''huse'' za korisnika u5', 4),
(72, '2010-08-03 23:13:17', 1, 'studentska/osobe akcija=edit osoba=5 subakcija=uloga stari_login= login=huse password=fatkic aktivan=1 nastavnik=1', 1),
(73, '2010-08-03 23:13:17', 1, 'osobi u5 data privilegija nastavnik', 4),
(74, '2010-08-03 23:13:44', 1, 'studentska/osobe akcija=edit osoba=5 subakcija=angazuj stari_login= login=huse password=fatkic aktivan=1 nastavnik=1 predmet=1', 1),
(75, '2010-08-03 23:13:44', 1, 'nastavnik u5 prijavljen na predmet p1 (admin: 0, akademska godina: 1)', 4),
(76, '2010-08-03 23:13:58', 1, 'studentska/osobe search= offset=', 1),
(77, '2010-08-03 23:14:00', 1, 'studentska/osobe search=sve offset=', 1),
(78, '2010-08-03 23:14:42', 1, 'studentska/osobe search=sve offset= akcija=edit osoba=2', 1),
(79, '2010-08-03 23:15:03', 1, 'studentska/osobe search=sve offset=', 1),
(80, '2010-08-03 23:15:06', 1, 'studentska/osobe search=sve offset= akcija=edit osoba=2', 1),
(81, '2010-08-03 23:15:28', 1, 'studentska/osobe osoba=2 akcija=upis studij= semestar=1 godina=1', 1),
(82, '2010-08-03 23:15:53', 1, 'studentska/osobe osoba=2 akcija=upis studij= semestar=1 godina=1 subakcija=upis_potvrda novi_studij=2 nacin_studiranja=1 novi_brindexa=14888', 1),
(83, '2010-08-03 23:16:03', 1, 'studentska/osobe osoba=2 akcija=upis studij=2 semestar=1 godina=1 subakcija=upis_potvrda novi_studij=0 nacin_studiranja=1 novi_brindexa=14888', 1),
(84, '2010-08-03 23:16:03', 1, 'Student u2 upisan na studij s2, semestar 1, godina ag1', 4),
(85, '2010-08-03 23:18:12', 1, 'studentska/osobe akcija=edit osoba=2', 1),
(86, '2010-08-03 23:18:16', 1, 'studentska/osobe search= offset=', 1),
(87, '2010-08-03 23:18:19', 1, 'studentska/osobe search=sve offset=', 1),
(88, '2010-08-03 23:18:25', 1, 'studentska/osobe search=sve offset= akcija=edit osoba=4', 1),
(89, '2010-08-03 23:18:29', 1, 'studentska/osobe osoba=4 akcija=upis studij= semestar=1 godina=1', 1),
(90, '2010-08-03 23:18:55', 1, 'studentska/osobe osoba=4 akcija=upis studij= semestar=1 godina=1 subakcija=upis_potvrda novi_studij=2 nacin_studiranja=1 novi_brindexa=14887', 1),
(91, '2010-08-03 23:19:23', 1, 'studentska/osobe osoba=4 akcija=upis studij=2 semestar=1 godina=1 subakcija=upis_potvrda novi_studij=0 nacin_studiranja=1 novi_brindexa=14888', 1),
(92, '2010-08-03 23:19:23', 1, 'Student u4 upisan na studij s2, semestar 1, godina ag1', 4),
(93, '2010-08-03 23:19:38', 1, 'studentska/osobe akcija=edit osoba=4', 1),
(94, '2010-08-03 23:19:41', 1, 'studentska/osobe osoba=4 akcija=predmeti', 1),
(95, '2010-08-03 23:19:48', 1, 'studentska/osobe akcija=predmeti osoba=4 subakcija=ispisi ponudakursa=1 spisak=0', 1),
(96, '2010-08-03 23:19:48', 1, 'student u4 manuelno ispisan sa predmeta p1', 4),
(97, '2010-08-03 23:20:02', 1, 'studentska/osobe akcija=predmeti osoba=4 subakcija=upisi ponudakursa=1 spisak=0', 1),
(98, '2010-08-03 23:20:02', 1, 'student u4 manuelno upisan na predmet p1', 4),
(99, '2010-08-03 23:20:07', 1, 'studentska/osobe akcija=predmeti osoba=4 ponudakursa=1 spisak=1', 1),
(100, '2010-08-03 23:20:43', 1, 'studentska/osobe osoba=4 akcija=edit', 1),
(101, '2010-08-03 23:20:55', 1, 'studentska/osobe osoba=4 akcija=podaci', 1),
(102, '2010-08-03 23:21:03', 1, 'studentska/osobe osoba=4 akcija=podaci subakcija=potvrda ime=Muris prezime=Agić brindexa=14887 jmbg= datum_rodjenja= mjesto_rodjenja= drzavljanstvo= adresa= adresa_mjesto= telefon= email=', 1),
(103, '2010-08-03 23:21:03', 1, 'promijenjeni licni podaci korisnika u4', 4),
(104, '2010-08-03 23:21:03', 1, 'studentska/osobe osoba=4 akcija=edit', 1),
(105, '2010-08-03 23:21:20', 1, 'studentska/osobe search= offset=', 1),
(106, '2010-08-03 23:21:23', 1, 'studentska/osobe search=sve offset=', 1),
(107, '2010-08-03 23:21:27', 1, 'studentska/osobe search=sve offset= akcija=edit osoba=3', 1),
(108, '2010-08-03 23:21:35', 1, 'studentska/osobe osoba=3 akcija=upis studij= semestar=1 godina=1', 1),
(109, '2010-08-03 23:21:50', 1, 'studentska/osobe osoba=3 akcija=upis studij= semestar=1 godina=1 subakcija=upis_potvrda novi_studij=2 nacin_studiranja=1 novi_brindexa=15888', 1),
(110, '2010-08-03 23:21:57', 1, 'studentska/osobe osoba=3 akcija=upis studij=2 semestar=1 godina=1 subakcija=upis_potvrda novi_studij=0 nacin_studiranja=1 novi_brindexa=15888', 1),
(111, '2010-08-03 23:21:57', 1, 'Student u3 upisan na studij s2, semestar 1, godina ag1', 4),
(112, '2010-08-03 23:22:05', 1, 'studentska/osobe akcija=edit osoba=3', 1),
(113, '2010-08-03 23:22:11', 1, 'studentska/osobe search= offset=', 1),
(114, '2010-08-03 23:22:13', 1, 'studentska/osobe search=sve offset=', 1),
(115, '2010-08-03 23:22:19', 1, 'studentska/osobe search=sve offset= akcija=edit osoba=5', 1),
(116, '2010-08-03 23:22:27', 1, 'studentska/osobe search=sve offset=', 1),
(117, '2010-08-03 23:23:15', 1, 'saradnik/intro', 1),
(118, '2010-08-03 23:23:20', 1, 'saradnik/grupa id=1', 1),
(119, '2010-08-03 23:23:36', 1, 'saradnik/grupa id=1 kreiranje=1', 1),
(120, '2010-08-03 23:23:40', 1, 'saradnik/grupa id=1', 1),
(121, '2010-08-03 23:23:47', 1, 'admin/intro', 1),
(122, '2010-08-03 23:23:51', 1, 'saradnik/intro', 1),
(123, '2010-08-03 23:24:02', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(124, '2010-08-03 23:24:19', 1, 'nastavnik/predmet predmet=1 ag=1 akcija=set_smodul smodul=1 aktivan=0', 1),
(125, '2010-08-03 23:24:19', 1, 'aktiviran studentski modul 1 (predmet pp1)', 2),
(126, '2010-08-03 23:24:19', 1, 'nastavnik/predmet predmet=1 ag=1 akcija=set_smodul smodul=2 aktivan=0', 1),
(127, '2010-08-03 23:24:19', 1, 'aktiviran studentski modul 2 (predmet pp1)', 2),
(128, '2010-08-03 23:24:20', 1, 'nastavnik/predmet predmet=1 ag=1 akcija=set_smodul smodul=3 aktivan=0', 1),
(129, '2010-08-03 23:24:20', 1, 'aktiviran studentski modul 3 (predmet pp1)', 2),
(130, '2010-08-03 23:24:21', 1, 'nastavnik/predmet predmet=1 ag=1 akcija=set_smodul smodul=4 aktivan=0', 1),
(131, '2010-08-03 23:24:21', 1, 'aktiviran studentski modul 4 (predmet pp1)', 2),
(132, '2010-08-03 23:24:27', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(133, '2010-08-03 23:24:29', 1, 'nastavnik/obavjestenja predmet=1 ag=1', 1),
(134, '2010-08-03 23:24:32', 1, 'nastavnik/grupe predmet=1 ag=1', 1),
(135, '2010-08-03 23:24:37', 1, 'nastavnik/ispiti predmet=1 ag=1', 1),
(136, '2010-08-03 23:24:41', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(137, '2010-08-03 23:24:51', 1, 'nastavnik/ocjena predmet=1 ag=1', 1),
(138, '2010-08-03 23:24:53', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(139, '2010-08-03 23:26:53', 1, 'izvjestaj/anketa predmet=1 ag=1 rank=da', 1),
(140, '2010-08-03 23:27:02', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(141, '2010-08-03 23:27:04', 1, 'izvjestaj/anketa predmet=1 ag=1 rank=da', 1),
(142, '2010-08-03 23:27:05', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(143, '2010-08-03 23:27:11', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(144, '2010-08-03 23:27:39', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(145, '2010-08-03 23:27:40', 1, 'izvjestaj/grupe predmet=1 ag=1', 1),
(146, '2010-08-03 23:27:44', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(147, '2010-08-03 23:27:47', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=', 1),
(148, '2010-08-03 23:27:49', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(149, '2010-08-03 23:27:51', 1, 'izvjestaj/grupe predmet=1 ag=1', 1),
(150, '2010-08-03 23:27:53', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(151, '2010-08-03 23:27:54', 1, 'izvjestaj/grupe predmet=1 ag=1 double=1', 1),
(152, '2010-08-03 23:27:56', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(153, '2010-08-03 23:27:57', 1, 'izvjestaj/grupe predmet=1 ag=1 komentari=1', 1),
(154, '2010-08-03 23:27:59', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(155, '2010-08-03 23:28:00', 1, 'izvjestaj/grupe predmet=1 ag=1 prisustvo=1 komentari=1', 1),
(156, '2010-08-03 23:28:02', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(157, '2010-08-03 23:28:05', 1, 'izvjestaj/grupe predmet=1 ag=1 prisustvo=1 komentari=1', 1),
(158, '2010-08-03 23:28:47', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(159, '2010-08-03 23:29:35', 1, 'studentska/intro', 1),
(160, '2010-08-03 23:29:37', 1, 'admin/intro', 1),
(161, '2010-08-03 23:29:39', 1, 'saradnik/intro', 1),
(162, '2010-08-03 23:29:41', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(163, '2010-08-03 23:29:47', 1, 'nastavnik/obavjestenja predmet=1 ag=1', 1),
(164, '2010-08-03 23:29:49', 1, 'nastavnik/grupe predmet=1 ag=1', 1),
(165, '2010-08-03 23:29:51', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(166, '2010-08-03 23:29:53', 1, 'nastavnik/ispiti predmet=1 ag=1', 1),
(167, '2010-08-03 23:29:54', 1, 'nastavnik/ocjena predmet=1 ag=1', 1),
(168, '2010-08-03 23:29:55', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(169, '2010-08-03 23:30:04', 1, 'studentska/intro', 1),
(170, '2010-08-03 23:30:13', 1, 'studentska/intro', 1),
(171, '2010-08-03 23:30:16', 1, 'studentska/osobe', 1),
(172, '2010-08-03 23:30:18', 1, 'studentska/predmeti', 1),
(173, '2010-08-03 23:30:22', 1, 'logout', 1),
(174, '2010-08-03 23:30:33', 2, 'login', 1),
(175, '2010-08-03 23:30:33', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(176, '2010-08-03 23:30:39', 2, 'student/kolizija', 1),
(177, '2010-08-03 23:30:39', 2, 'SQL greska (studentkolizija.php : 65):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''and ss.studij=s.id order by semestar desc limit 1'' at line 1', 3),
(178, '2010-08-03 23:30:54', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(179, '2010-08-03 23:31:19', 2, 'student/zadaca predmet=1 ag=1', 1),
(180, '2010-08-03 23:31:23', 2, 'izvjestaj/predmet predmet=1 ag=1', 1),
(181, '2010-08-03 23:32:31', 2, 'student/ugovoroucenju', 1),
(182, '2010-08-03 23:32:31', 2, 'SQL greska (ntugovoroucenju.php : 171):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''and ss.studij=s.id order by semestar desc limit 1'' at line 1', 3),
(183, '2010-08-03 23:33:23', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(184, '2010-08-03 23:33:51', 2, 'logout', 1),
(185, '2010-08-03 23:34:01', 5, 'login', 1),
(186, '2010-08-03 23:34:01', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(187, '2010-08-03 23:34:05', 5, 'saradnik/grupa id=1', 1),
(188, '2010-08-03 23:34:10', 5, 'saradnik/grupa id=1 kreiranje=1', 1),
(189, '2010-08-03 23:34:12', 5, 'saradnik/student student=2 predmet=1 ag=1', 1),
(190, '2010-08-03 23:34:40', 5, 'common/inbox akcija=compose primalac=', 1),
(191, '2010-08-03 23:35:17', 5, 'common/ajah akcija=pretraga ime=jasmin', 1),
(192, '2010-08-03 23:35:28', 5, 'common/ajah akcija=pretraga ime=muris', 1),
(193, '2010-08-03 23:35:34', 5, 'common/ajah akcija=pretraga ime=agic', 1),
(194, '2010-08-03 23:35:41', 5, 'common/ajah akcija=pretraga ime=jas', 1),
(195, '2010-08-03 23:36:33', 5, 'common/inbox akcija=send primalac=jasmin (Jasmin Krčalo) metoda=2 naslov=Usmeni Ispit ', 1),
(196, '2010-08-03 23:36:33', 5, 'poslana poruka za u2', 2),
(197, '2010-08-03 23:36:42', 5, 'saradnik/intro', 1),
(198, '2010-08-03 23:36:45', 5, 'saradnik/grupa id=1', 1),
(199, '2010-08-03 23:36:49', 5, 'saradnik/student student=3 predmet=1 ag=1', 1),
(200, '2010-08-03 23:36:57', 5, 'logout', 1),
(201, '2010-08-03 23:37:08', 2, 'login', 1),
(202, '2010-08-03 23:37:08', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(203, '2010-08-03 23:37:19', 2, 'common/inbox poruka=1', 1),
(204, '2010-08-03 23:37:33', 2, 'common/inbox poruka=1', 1),
(205, '2010-08-03 23:37:35', 2, 'common/inbox poruka=1', 1),
(206, '2010-08-03 23:37:43', 2, 'common/inbox akcija=odgovor poruka=1', 1),
(207, '2010-08-03 23:38:25', 2, 'common/inbox akcija=send poruka=1 ref=1 primalac=huse (Huse Fatkić) metoda=2 naslov=Re: Usmeni Ispit ', 1),
(208, '2010-08-03 23:38:25', 2, 'poslana poruka za u5', 2),
(209, '2010-08-03 23:38:32', 2, 'logout', 1),
(210, '2010-08-03 23:38:44', 5, 'login', 1),
(211, '2010-08-03 23:38:44', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(212, '2010-08-03 23:38:48', 5, 'logout', 1),
(213, '2010-08-03 23:39:36', 5, 'login', 1),
(214, '2010-08-03 23:39:36', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(215, '2010-08-03 23:39:49', 5, '/zamger41/index.php?loginforma=1 login=huse sve=1', 1),
(216, '2010-08-03 23:39:51', 5, 'saradnik/grupa id=1', 1),
(217, '2010-08-03 23:39:55', 5, 'saradnik/intro', 1),
(218, '2010-08-03 23:40:02', 5, 'saradnik/grupa id=1', 1),
(219, '2010-08-03 23:40:05', 5, 'saradnik/grupa id=1 kreiranje=1', 1),
(220, '2010-08-03 23:40:08', 5, 'saradnik/grupa id=1', 1),
(221, '2010-08-03 23:40:29', 5, 'saradnik/grupa id=1 kreiranje=1', 1),
(222, '2010-08-03 23:40:31', 5, 'saradnik/intro', 1),
(223, '2010-08-03 23:40:38', 5, 'saradnik/intro sve=1', 1),
(224, '2010-08-03 23:40:40', 5, 'saradnik/grupa id=1', 1),
(225, '2010-08-03 23:40:49', 5, 'logout', 1),
(226, '2010-08-03 23:40:58', 1, 'login', 1),
(227, '2010-08-03 23:40:58', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(228, '2010-08-03 23:41:00', 1, 'admin/intro grupe=1', 1),
(229, '2010-08-03 23:41:01', 1, 'admin/intro', 1),
(230, '2010-08-03 23:41:06', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(231, '2010-08-03 23:41:19', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(232, '2010-08-03 23:42:19', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(233, '2010-08-03 23:42:20', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(234, '2010-08-03 23:43:02', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaća1 zadataka=3 bodova=2 day=4 month=8 year=2010 sat=16 minuta=42 sekunda=20 aktivna=on attachment=on', 1),
(235, '2010-08-03 23:43:02', 1, 'kreirana nova zadaca z1', 2),
(236, '2010-08-03 23:43:31', 1, 'studentska/intro', 1),
(237, '2010-08-03 23:43:34', 1, 'logout', 1),
(238, '2010-08-03 23:43:43', 5, 'login', 1),
(239, '2010-08-03 23:43:43', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(240, '2010-08-03 23:43:47', 5, 'saradnik/grupa id=1', 1),
(241, '2010-08-03 23:43:56', 5, 'saradnik/svezadace grupa=1 zadaca=1', 1),
(242, '2010-08-03 23:43:56', 5, 'saradnik/svezadace grupa=1 zadaca=1 potvrda=ok', 1),
(243, '2010-08-03 23:43:56', 5, 'niko nije poslao zadacu (z1, pp1, g1)', 3),
(244, '2010-08-03 23:44:20', 5, 'saradnik/grupa id=1', 1),
(245, '2010-08-03 23:44:26', 5, 'logout', 1),
(246, '2010-08-03 23:44:36', 1, 'login', 1),
(247, '2010-08-03 23:44:36', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(248, '2010-08-03 23:44:39', 1, 'studentska/intro', 1),
(249, '2010-08-03 23:44:41', 1, 'saradnik/intro', 1),
(250, '2010-08-03 23:44:43', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(251, '2010-08-03 23:44:45', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(252, '2010-08-03 23:44:47', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(253, '2010-08-03 23:45:12', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaća2 zadataka=4 bodova=3 day=4 month=8 year=2010 sat=23 minuta=44 sekunda=47 aktivna=on', 1),
(254, '2010-08-03 23:45:12', 1, 'kreirana nova zadaca z2', 2),
(255, '2010-08-03 23:45:21', 1, 'logout', 1),
(256, '2010-08-03 23:45:32', 5, 'login', 1),
(257, '2010-08-03 23:45:32', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(258, '2010-08-03 23:45:35', 5, 'saradnik/grupa id=1', 1),
(259, '2010-08-03 23:45:42', 5, 'logout', 1),
(260, '2010-08-03 23:45:56', 2, 'login', 1),
(261, '2010-08-03 23:45:56', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(262, '2010-08-03 23:46:10', 2, 'student/zadaca zadaca=1 predmet=1 ag=1', 1),
(263, '2010-08-03 23:46:22', 2, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(264, '2010-08-03 23:46:24', 2, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(265, '2010-08-03 23:46:39', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=1 zadatak=1 labgrupa=', 1),
(266, '2010-08-03 23:46:39', 2, 'poslana zadaca z1 zadatak 1 (attachment)', 2),
(267, '2010-08-03 23:47:11', 2, 'common/attachment zadaca=1 zadatak=1', 1),
(268, '2010-08-03 23:47:30', 2, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(269, '2010-08-03 23:47:47', 2, 'logout', 1),
(270, '2010-08-04 11:22:44', 0, 'logout', 1),
(271, '2010-08-04 11:22:48', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  admin/intro', 3),
(272, '2010-08-04 11:23:42', 1, 'login', 1),
(273, '2010-08-04 11:23:42', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(274, '2010-08-04 11:23:46', 1, 'saradnik/intro', 1),
(275, '2010-08-04 11:23:47', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(276, '2010-08-04 11:23:50', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(277, '2010-08-04 11:24:03', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(278, '2010-08-04 11:24:04', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(279, '2010-08-04 11:24:06', 1, 'izvjestaj/grupe predmet=1 ag=1', 1),
(280, '2010-08-04 11:24:08', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(281, '2010-08-04 11:29:38', 1, 'logout', 1),
(282, '2010-08-04 11:29:43', 0, 'logout', 1),
(283, '2010-08-04 11:29:57', 1, 'login', 1),
(284, '2010-08-04 11:29:57', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(285, '2010-08-04 11:29:59', 1, 'studentska/intro', 1),
(286, '2010-08-04 11:29:59', 1, 'saradnik/intro', 1),
(287, '2010-08-04 11:30:01', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(288, '2010-08-04 11:30:05', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(289, '2010-08-04 11:36:00', 1, 'logout', 1),
(290, '2010-08-04 11:36:26', 0, 'index.php greska: Pogrešna šifra jasmin ', 3),
(291, '2010-08-04 11:36:35', 2, 'login', 1),
(292, '2010-08-04 11:36:35', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(293, '2010-08-04 11:36:42', 2, 'student/zadaca zadaca=2 predmet=1 ag=1', 1),
(294, '2010-08-04 11:36:45', 2, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(295, '2010-08-04 11:37:02', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=1 zadatak=1 labgrupa=', 1),
(296, '2010-08-04 11:37:02', 2, 'poslana zadaca z1 zadatak 1 (attachment)', 2),
(297, '2010-08-04 11:37:40', 2, 'common/attachment zadaca=1 zadatak=1', 1),
(298, '2010-08-04 11:38:42', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=1 zadatak=1 labgrupa=', 1),
(299, '2010-08-04 11:38:42', 2, 'poslana zadaca z1 zadatak 1 (attachment)', 2),
(300, '2010-08-04 11:46:14', 1, '/zamger/index.php?', 1),
(301, '2010-08-04 11:46:20', 1, 'logout', 1),
(302, '2010-08-04 11:46:24', 4, 'login', 1),
(303, '2010-08-04 11:46:24', 4, '/zamger/index.php?loginforma=1 login=muris', 1),
(304, '2010-08-04 11:46:28', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(305, '2010-08-04 11:46:34', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(306, '2010-08-04 11:46:50', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=1 zadatak=1 labgrupa=', 1),
(307, '2010-08-04 11:46:50', 4, 'poslana zadaca z1 zadatak 1 (attachment)', 2),
(308, '2010-08-04 11:48:05', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(309, '2010-08-04 11:48:10', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(310, '2010-08-04 11:48:11', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(311, '2010-08-04 11:48:11', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(312, '2010-08-04 11:48:11', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(313, '2010-08-04 11:48:12', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(314, '2010-08-04 11:49:58', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(315, '2010-08-04 11:50:00', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=2', 1),
(316, '2010-08-04 11:50:10', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=1 zadatak=2 labgrupa=', 1),
(317, '2010-08-04 11:50:10', 4, 'poslana zadaca z1 zadatak 2 (attachment)', 2),
(318, '2010-08-04 11:51:01', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(319, '2010-08-04 11:53:15', 4, '/zamger41/index.php?', 1),
(320, '2010-08-04 11:53:19', 4, 'logout', 1),
(321, '2010-08-04 11:53:24', 4, 'login', 1),
(322, '2010-08-04 11:53:24', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(323, '2010-08-04 11:53:29', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(324, '2010-08-04 11:53:32', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(325, '2010-08-04 11:53:41', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=1 zadatak=1 labgrupa=', 1),
(326, '2010-08-04 11:53:41', 4, 'poslana zadaca z1 zadatak 1 (attachment)', 2),
(327, '2010-08-04 11:53:47', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=2', 1),
(328, '2010-08-04 11:53:58', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=1 zadatak=2 labgrupa=', 1),
(329, '2010-08-04 11:53:58', 4, 'poslana zadaca z1 zadatak 2 (attachment)', 2),
(330, '2010-08-04 11:54:01', 4, 'common/attachment zadaca=1 zadatak=2', 1),
(331, '2010-08-04 11:58:16', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=1 zadatak=2 labgrupa=', 1),
(332, '2010-08-04 11:58:16', 4, 'poslana zadaca z1 zadatak 2 (attachment)', 2),
(333, '2010-08-04 11:58:19', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(334, '2010-08-04 11:58:23', 4, 'student/projekti predmet=1 ag=1', 1),
(335, '2010-08-04 11:58:25', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(336, '2010-08-04 11:59:20', 4, 'student/intro', 1),
(337, '2010-08-04 11:59:37', 4, 'student/ugovoroucenju', 1),
(338, '2010-08-04 11:59:37', 4, 'SQL greska (ntugovoroucenju.php : 171):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''and ss.studij=s.id order by semestar desc limit 1'' at line 1', 3),
(339, '2010-08-04 11:59:58', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(340, '2010-08-04 12:00:22', 4, 'student/kolizija', 1),
(341, '2010-08-04 12:00:22', 4, 'SQL greska (studentkolizija.php : 65):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''and ss.studij=s.id order by semestar desc limit 1'' at line 1', 3),
(342, '2010-08-04 12:00:28', 4, 'student/prosjeci', 1),
(343, '2010-08-04 12:01:41', 4, 'student/prijava_ispita', 1),
(344, '2010-08-04 12:01:45', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(345, '2010-08-04 12:01:47', 4, 'student/projekti predmet=1 ag=1', 1),
(346, '2010-08-04 12:01:49', 4, 'student/zadaca predmet=1 ag=1', 1),
(347, '2010-08-04 12:01:50', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(348, '2010-08-04 12:02:25', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(349, '2010-08-04 12:02:30', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(350, '2010-08-04 12:02:39', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(351, '2010-08-04 12:02:43', 4, 'student/intro', 1),
(352, '2010-08-04 12:12:04', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(353, '2010-08-04 12:12:58', 4, 'student/pdf zadaca=2', 1),
(354, '2010-08-04 12:13:53', 4, 'login', 1),
(355, '2010-08-04 12:13:53', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(356, '2010-08-04 12:13:58', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(357, '2010-08-04 12:14:31', 4, 'student/pdf zadaca=2', 1),
(358, '2010-08-04 12:14:52', 4, 'logout', 1),
(359, '2010-08-04 12:15:01', 5, 'login', 1),
(360, '2010-08-04 12:15:01', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(361, '2010-08-04 12:15:04', 5, 'saradnik/grupa id=1', 1),
(362, '2010-08-04 12:15:12', 5, 'saradnik/zadaca student=4 zadaca=1 zadatak=1', 1),
(363, '2010-08-04 12:15:18', 5, 'common/attachment student=4 zadaca=1 zadatak=1', 1),
(364, '2010-08-04 12:15:43', 5, 'saradnik/zadaca student=4 zadaca=1 zadatak=1', 1),
(365, '2010-08-04 12:16:10', 5, 'saradnik/komentar student=4 labgrupa=1', 1),
(366, '2010-08-04 12:16:26', 5, 'saradnik/grupa id=1 akcija=dodajcas dan=4 mjesec=8 godina=2010 vrijeme=12:15 prisustvo=0', 1),
(367, '2010-08-04 12:16:26', 5, 'registrovan cas c1', 2),
(368, '2010-08-04 12:16:31', 5, 'saradnik/grupa id=1 akcija=dodajcas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(369, '2010-08-04 12:16:31', 5, 'registrovan cas c2', 2),
(370, '2010-08-04 12:16:34', 5, 'saradnik/grupa id=1 akcija=dodajcas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(371, '2010-08-04 12:16:34', 5, 'registrovan cas c3', 2),
(372, '2010-08-04 12:16:34', 5, 'saradnik/grupa id=1 akcija=dodajcas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(373, '2010-08-04 12:16:34', 5, 'registrovan cas c4', 2),
(374, '2010-08-04 12:16:34', 5, 'saradnik/grupa id=1 akcija=dodajcas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(375, '2010-08-04 12:16:34', 5, 'registrovan cas c5', 2),
(376, '2010-08-04 12:16:34', 5, 'saradnik/grupa id=1 akcija=dodajcas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(377, '2010-08-04 12:16:34', 5, 'registrovan cas c6', 2),
(378, '2010-08-04 12:16:34', 5, 'saradnik/grupa id=1 akcija=dodajcas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(379, '2010-08-04 12:16:34', 5, 'registrovan cas c7', 2),
(380, '2010-08-04 12:16:34', 5, 'saradnik/grupa id=1 akcija=dodajcas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(381, '2010-08-04 12:16:34', 5, 'registrovan cas c8', 2),
(382, '2010-08-04 12:16:35', 5, 'saradnik/grupa id=1 akcija=dodajcas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(383, '2010-08-04 12:16:35', 5, 'registrovan cas c9', 2),
(384, '2010-08-04 12:16:44', 5, 'saradnik/grupa id=1 akcija=brisi_cas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(385, '2010-08-04 12:16:44', 5, 'obrisan cas 9', 2),
(386, '2010-08-04 12:16:45', 5, 'saradnik/grupa id=1 akcija=brisi_cas dan=4 mjesec=8 godina=2010 vrijeme=12:16 prisustvo=1', 1),
(387, '2010-08-04 12:16:45', 5, 'obrisan cas 8', 2),
(388, '2010-08-04 12:16:46', 5, 'saradnik/zadaca student=4 zadaca=1 zadatak=2', 1),
(389, '2010-08-04 12:16:54', 5, 'common/attachment student=4 zadaca=1 zadatak=2', 1),
(390, '2010-08-04 12:17:03', 5, 'saradnik/svezadace grupa=1 zadaca=1', 1),
(391, '2010-08-04 12:17:03', 5, 'saradnik/svezadace grupa=1 zadaca=1 potvrda=ok', 1),
(392, '2010-08-04 12:17:04', 5, 'kreiranje arhive zadaca nije uspjelo (z1, pp1, g1)', 3),
(393, '2010-08-04 12:17:41', 5, '/zamger41/index.php?', 1),
(394, '2010-08-04 12:17:43', 5, 'saradnik/grupa id=1', 1),
(395, '2010-08-04 12:17:49', 5, 'saradnik/svezadace grupa=1 zadaca=1', 1),
(396, '2010-08-04 12:17:49', 5, 'saradnik/svezadace grupa=1 zadaca=1 potvrda=ok', 1),
(397, '2010-08-04 12:17:49', 5, 'kreiranje arhive zadaca nije uspjelo (z1, pp1, g1)', 3),
(398, '2010-08-04 12:18:25', 5, '/zamger41/index.php?', 1),
(399, '2010-08-04 12:18:30', 5, 'common/profil', 1),
(400, '2010-08-04 12:18:34', 5, 'common/inbox', 1),
(401, '2010-08-04 12:18:38', 5, 'common/inbox poruka=2', 1),
(402, '2010-08-04 12:18:47', 5, 'saradnik/intro', 1),
(403, '2010-08-04 12:19:06', 5, 'saradnik/grupa id=1', 1),
(404, '2010-08-04 12:19:23', 5, 'saradnik/zadaca student=4 zadaca=1 zadatak=2', 1),
(405, '2010-08-04 12:19:37', 5, 'saradnik/zadaca student=4 zadaca=1 zadatak=2 akcija=slanje status=2 bodova=0 komentar=Prepisao si \r\n', 1),
(406, '2010-08-04 12:19:37', 5, 'izmjena zadace (student u4 zadaca z1 zadatak 2)', 2),
(407, '2010-08-04 12:19:43', 5, 'saradnik/grupa id=1', 1),
(408, '2010-08-04 12:19:50', 5, 'saradnik/zadaca student=4 zadaca=1 zadatak=2', 1),
(409, '2010-08-04 12:19:59', 5, 'saradnik/zadaca student=4 zadaca=1 zadatak=1', 1),
(410, '2010-08-04 12:20:08', 5, 'saradnik/zadaca student=4 zadaca=1 zadatak=1 akcija=slanje status=5 bodova=2 komentar=', 1),
(411, '2010-08-04 12:20:08', 5, 'izmjena zadace (student u4 zadaca z1 zadatak 1)', 2),
(412, '2010-08-04 12:20:14', 5, 'saradnik/zadaca student=4 zadaca=1 zadatak=1', 1),
(413, '2010-08-04 12:20:27', 5, 'saradnik/grupa id=1', 1),
(414, '2010-08-04 12:20:49', 5, 'logout', 1),
(415, '2010-08-04 12:26:10', 4, 'login', 1),
(416, '2010-08-04 12:26:10', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(417, '2010-08-04 12:26:12', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(418, '2010-08-04 12:33:19', 4, 'logout', 1),
(419, '2010-08-04 12:33:37', 0, 'index.php greska: Pogrešna šifra admin ', 3),
(420, '2010-08-04 12:33:42', 1, 'login', 1),
(421, '2010-08-04 12:33:42', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(422, '2010-08-04 12:33:47', 1, 'studentska/intro', 1),
(423, '2010-08-04 12:34:01', 1, '/zamger41/index.php?', 1),
(424, '2010-08-04 12:34:09', 1, 'saradnik/intro', 1),
(425, '2010-08-04 12:34:14', 1, 'saradnik/grupa id=1', 1),
(426, '2010-08-04 12:34:16', 1, 'saradnik/intro', 1),
(427, '2010-08-04 12:34:19', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(428, '2010-08-04 12:34:23', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(429, '2010-08-04 12:35:06', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaca 3 zadataka=3 bodova=2 day=4 month=8 year=2010 sat=12 minuta=34 sekunda=23', 1),
(430, '2010-08-04 12:35:06', 1, 'kreirana nova zadaca z3', 2),
(431, '2010-08-04 12:35:14', 1, 'logout', 1),
(432, '2010-08-04 12:35:26', 4, 'login', 1),
(433, '2010-08-04 12:35:26', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(434, '2010-08-04 12:35:28', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(435, '2010-08-04 12:35:30', 4, 'student/zadaca predmet=1 ag=1', 1),
(436, '2010-08-04 12:35:32', 4, 'student/zadaca predmet=1 ag=1 zadaca=3 zadatak=1', 1),
(437, '2010-08-04 12:36:01', 4, 'student/zadaca predmet=1 ag=1 zadaca=3 zadatak=1 akcija=slanje labgrupa= program=#include &lt;iostream&gt;\r\n#include &lt;fstream&gt;\r\n\r\nusing namespace std;\r\n\r\nstruct Student{\r\n    char ime_studenta[20], prezime_studenta[20];\r\n    int broj_indeksa, broj_o', 1),
(438, '2010-08-04 12:36:01', 4, 'isteklo vrijeme za slanje zadaće z3', 3),
(439, '2010-08-04 12:36:13', 4, 'logout', 1),
(440, '2010-08-04 12:36:15', 1, 'login', 1),
(441, '2010-08-04 12:36:15', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(442, '2010-08-04 12:36:19', 1, 'saradnik/intro', 1),
(443, '2010-08-04 12:36:20', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(444, '2010-08-04 12:36:22', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(445, '2010-08-04 12:36:24', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(446, '2010-08-04 12:36:41', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=3 naziv=Zadaca 3 zadataka=3 bodova=2 day=4 month=10 year=2010 sat=12 minuta=34 sekunda=23', 1),
(447, '2010-08-04 12:36:42', 1, 'azurirana zadaca z3', 2),
(448, '2010-08-04 12:36:49', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=3 naziv=Zadaca3 zadataka=3 bodova=2 day=4 month=10 year=2010 sat=12 minuta=34 sekunda=23', 1),
(449, '2010-08-04 12:36:49', 1, 'azurirana zadaca z3', 2),
(450, '2010-08-04 12:36:53', 1, 'logout', 1),
(451, '2010-08-04 12:37:02', 4, 'login', 1),
(452, '2010-08-04 12:37:02', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(453, '2010-08-04 12:37:05', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(454, '2010-08-04 12:37:07', 4, 'student/zadaca predmet=1 ag=1 zadaca=3 zadatak=1', 1),
(455, '2010-08-04 12:37:17', 4, 'student/zadaca predmet=1 ag=1 zadaca=3 zadatak=1 akcija=slanje labgrupa= program=#include &lt;iostream&gt;\r\n#include &lt;fstream&gt;\r\n\r\nusing namespace std;\r\n\r\nstruct Student{\r\n    char ime_studenta[20], prezime_studenta[20];\r\n    int broj_indeksa, broj_o', 1),
(456, '2010-08-04 12:37:17', 4, 'poslana zadaca z3 zadatak 1', 2),
(457, '2010-08-04 12:38:14', 4, 'student/intro', 1),
(458, '2010-08-04 12:38:15', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(459, '2010-08-04 12:38:19', 4, 'student/pdf zadaca=3', 1),
(460, '2010-08-04 12:41:35', 4, 'student/pdf zadaca=3', 1),
(461, '2010-08-04 12:52:41', 4, 'student/pdf zadaca=3', 1),
(462, '2010-08-04 12:53:46', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(463, '2010-08-04 12:53:51', 4, 'student/pdf zadaca=3', 1),
(464, '2010-08-04 12:53:56', 4, 'student/pdf zadaca=3', 1),
(465, '2010-08-04 12:54:00', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(466, '2010-08-04 12:54:06', 4, 'student/pdf zadaca=3', 1),
(467, '2010-08-04 12:55:25', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(468, '2010-08-04 12:55:27', 4, 'student/pdf zadaca=3', 1),
(469, '2010-08-04 12:56:05', 4, 'logout', 1),
(470, '2010-08-04 12:57:42', 1, 'login', 1),
(471, '2010-08-04 12:57:42', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(472, '2010-08-04 12:57:45', 1, 'studentska/intro', 1),
(473, '2010-08-04 12:57:51', 1, 'studentska/predmeti', 1),
(474, '2010-08-04 12:58:08', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(475, '2010-08-04 12:58:11', 1, 'nastavnik/grupe predmet=1 ag=1', 1),
(476, '2010-08-04 12:58:25', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=nova_grupa ime=Grupa 1', 1),
(477, '2010-08-04 12:58:25', 1, 'dodana nova labgrupa ''Grupa 1'' (predmet pp1 godina ag1)', 4),
(478, '2010-08-04 12:58:27', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=studenti_grupa grupaid=2', 1),
(479, '2010-08-04 12:59:20', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput=Krčalo Jasmin\r\nAgić Muris\r\n format=2 separator=0', 1),
(480, '2010-08-04 12:59:32', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput=Krčalo Jasmin\r\nAgić Muris format=2 separator=0', 1),
(481, '2010-08-04 12:59:50', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=studenti_grupa grupaid=2', 1),
(482, '2010-08-04 13:00:17', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=nova_grupa grupaid=2 ime=', 1),
(483, '2010-08-04 13:00:17', 1, 'dodana nova labgrupa '''' (predmet pp1 godina ag1)', 4),
(484, '2010-08-04 13:00:22', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=obrisi_grupu grupaid=3 ime=', 1),
(485, '2010-08-04 13:00:22', 1, 'obrisana labgrupa 3 (predmet pp1)', 4),
(486, '2010-08-04 13:00:24', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=studenti_grupa grupaid=2', 1),
(487, '2010-08-04 13:00:42', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput=Agić,Muris format=2 separator=1', 1),
(488, '2010-08-04 13:01:34', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput=Agić,Muris format=2 separator=1', 1),
(489, '2010-08-04 13:01:48', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput=Agić Muris format=0 separator=0', 1),
(490, '2010-08-04 13:01:55', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput=Agić Muris format=0 separator=0', 1),
(491, '2010-08-04 13:02:01', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput=AgićMuris format=0 separator=0', 1),
(492, '2010-08-04 13:02:06', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput=AgićMuris format=0 separator=0', 1),
(493, '2010-08-04 13:02:10', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=studenti_grupa grupaid=2', 1),
(494, '2010-08-04 13:02:23', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput=Agić Muris format=0 separator=0', 1),
(495, '2010-08-04 13:02:38', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput=Agić Muris format=0 separator=0', 1),
(496, '2010-08-04 13:03:02', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=studenti_grupa grupaid=2', 1),
(497, '2010-08-04 13:03:08', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput=AgićMuris format=0 separator=0', 1),
(498, '2010-08-04 13:03:10', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput=AgićMuris format=0 separator=0', 1),
(499, '2010-08-04 13:03:22', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=studenti_grupa grupaid=2', 1),
(500, '2010-08-04 13:03:29', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput=Agić Muris format=0 separator=0', 1),
(501, '2010-08-04 13:04:46', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput=Agić Muris format=0 separator=0', 1),
(502, '2010-08-04 13:05:02', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput=Agić\r\nMuris format=0 separator=0', 1),
(503, '2010-08-04 13:05:05', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput=Agić\r\nMuris format=0 separator=0', 1),
(504, '2010-08-04 13:06:19', 1, 'studentska/intro', 1),
(505, '2010-08-04 13:06:23', 1, 'studentska/predmeti', 1),
(506, '2010-08-04 13:06:27', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(507, '2010-08-04 13:06:32', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(508, '2010-08-04 13:06:41', 1, 'izvjestaj/statistika_predmeta predmet=1 ag=1', 1),
(509, '2010-08-04 13:06:47', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(510, '2010-08-04 13:06:49', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(511, '2010-08-04 13:06:51', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(512, '2010-08-04 13:07:33', 1, 'nastavnik/grupe predmet=1 ag=1', 1),
(513, '2010-08-04 13:07:38', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=studenti_grupa grupaid=2', 1),
(514, '2010-08-04 13:09:52', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput= format=0 separator=0', 1),
(515, '2010-08-04 13:09:56', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput= format=0 separator=0', 1),
(516, '2010-08-04 13:10:00', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput= Muris format=0 separator=0', 1),
(517, '2010-08-04 13:10:05', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput= Muris format=0 separator=0', 1),
(518, '2010-08-04 13:16:02', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=0 nazad= visestruki=1 duplikati=1 brpodataka=1 massinput=Agić Muris format=2 separator=0', 1),
(519, '2010-08-04 13:16:15', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=massinput grupaid=2 fakatradi=1 nazad= Nazad  visestruki=1 duplikati=1 brpodataka=1 massinput=Agić Muris format=2 separator=0', 1),
(520, '2010-08-04 13:16:27', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=studenti_grupa grupaid=2', 1),
(521, '2010-08-04 13:16:52', 1, 'nastavnik/grupe predmet=1 ag=1 akcija=kopiraj_grupe grupaid=2 kopiraj=1', 1),
(522, '2010-08-04 13:16:52', 1, 'kopiranje sa istog predmeta pp1', 3),
(523, '2010-08-04 13:16:55', 1, 'nastavnik/grupe predmet=1 ag=1', 1),
(524, '2010-08-04 13:20:08', 1, 'logout', 1),
(525, '2010-08-04 13:23:12', 4, 'login', 1),
(526, '2010-08-04 13:23:12', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(527, '2010-08-04 13:23:14', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(528, '2010-08-04 13:23:17', 4, 'student/pdf zadaca=3', 1),
(529, '2010-08-04 13:23:21', 4, 'student/pdf zadaca=3', 1),
(530, '2010-08-04 13:23:22', 4, 'student/pdf zadaca=3', 1),
(531, '2010-08-04 13:23:22', 4, 'student/pdf zadaca=3', 1),
(532, '2010-08-04 13:23:23', 4, 'student/pdf zadaca=3', 1),
(533, '2010-08-04 13:23:26', 4, 'student/pdf zadaca=3', 1),
(534, '2010-08-04 13:24:01', 4, 'student/pdf zadaca=3', 1),
(535, '2010-08-04 13:24:01', 4, 'student/zadaca predmet=1 ag=1', 1),
(536, '2010-08-04 13:24:06', 4, 'student/intro', 1),
(537, '2010-08-04 13:24:08', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(538, '2010-08-04 13:24:10', 4, 'student/pdf zadaca=3', 1),
(539, '2010-08-04 13:27:20', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(540, '2010-08-04 13:27:22', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(541, '2010-08-04 13:27:24', 4, 'student/pdf zadaca=3', 1),
(542, '2010-08-04 13:28:32', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(543, '2010-08-04 13:28:34', 4, 'student/pdf zadaca=3', 1),
(544, '2010-08-04 13:28:36', 4, 'student/pdf zadaca=3', 1),
(545, '2010-08-04 13:28:36', 4, 'student/pdf zadaca=3', 1),
(546, '2010-08-04 13:28:42', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(547, '2010-08-04 13:28:44', 4, 'student/pdf zadaca=3', 1),
(548, '2010-08-04 13:30:24', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(549, '2010-08-04 13:30:26', 4, 'student/pdf zadaca=3', 1),
(550, '2010-08-04 13:30:28', 4, 'student/pdf zadaca=3', 1),
(551, '2010-08-04 13:30:29', 4, 'student/pdf zadaca=3', 1),
(552, '2010-08-04 13:30:29', 4, 'student/pdf zadaca=3', 1),
(553, '2010-08-04 13:30:31', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(554, '2010-08-04 13:30:33', 4, 'student/intro', 1),
(555, '2010-08-04 13:30:34', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(556, '2010-08-04 13:30:36', 4, 'student/pdf zadaca=3', 1),
(557, '2010-08-04 13:30:39', 4, 'student/pdf zadaca=3', 1),
(558, '2010-08-04 13:30:53', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(559, '2010-08-04 13:30:56', 4, 'student/pdf zadaca=3', 1),
(560, '2010-08-04 13:32:01', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(561, '2010-08-04 13:32:03', 4, 'student/pdf zadaca=3', 1),
(562, '2010-08-04 13:32:05', 4, 'student/pdf zadaca=3', 1),
(563, '2010-08-04 13:32:05', 4, 'student/pdf zadaca=3', 1),
(564, '2010-08-04 13:32:06', 4, 'student/pdf zadaca=3', 1),
(565, '2010-08-04 13:32:06', 4, 'student/pdf zadaca=3', 1),
(566, '2010-08-04 13:32:06', 4, 'student/pdf zadaca=3', 1),
(567, '2010-08-04 13:32:12', 4, 'student/pdf zadaca=3', 1),
(568, '2010-08-04 13:36:51', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(569, '2010-08-04 13:36:53', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(570, '2010-08-04 13:36:55', 4, 'student/pdf zadaca=3', 1),
(571, '2010-08-04 13:46:55', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(572, '2010-08-04 13:46:57', 4, 'student/pdf zadaca=3', 1),
(573, '2010-08-04 13:48:17', 4, 'student/pdf zadaca=3', 1),
(574, '2010-08-04 13:48:24', 4, 'student/pdf zadaca=3', 1),
(575, '2010-08-04 13:50:04', 4, 'student/pdf zadaca=3', 1),
(576, '2010-08-04 13:50:08', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(577, '2010-08-04 13:50:10', 4, 'student/pdf zadaca=3', 1),
(578, '2010-08-04 13:50:12', 4, 'student/pdf zadaca=3', 1),
(579, '2010-08-04 13:50:13', 4, 'student/pdf zadaca=3', 1),
(580, '2010-08-04 13:50:13', 4, 'student/pdf zadaca=3', 1),
(581, '2010-08-04 13:50:13', 4, 'student/pdf zadaca=3', 1),
(582, '2010-08-04 13:50:13', 4, 'student/pdf zadaca=3', 1),
(583, '2010-08-04 13:50:16', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(584, '2010-08-04 13:50:18', 4, 'student/pdf zadaca=3', 1),
(585, '2010-08-04 13:50:22', 4, 'student/intro', 1),
(586, '2010-08-04 13:50:23', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(587, '2010-08-04 13:50:25', 4, 'student/pdf zadaca=3', 1),
(588, '2010-08-04 13:50:26', 4, 'student/pdf zadaca=3', 1),
(589, '2010-08-04 13:50:26', 4, 'student/pdf zadaca=3', 1),
(590, '2010-08-04 13:50:26', 4, 'student/pdf zadaca=3', 1),
(591, '2010-08-04 13:50:27', 4, 'student/pdf zadaca=3', 1),
(592, '2010-08-04 13:50:27', 4, 'student/pdf zadaca=3', 1),
(593, '2010-08-04 13:50:27', 4, 'student/pdf zadaca=3', 1),
(594, '2010-08-04 13:50:39', 4, 'student/pdf zadaca=3', 1),
(595, '2010-08-04 13:50:39', 4, 'student/pdf zadaca=3', 1),
(596, '2010-08-04 13:50:41', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(597, '2010-08-04 13:50:43', 4, 'student/pdf zadaca=3', 1),
(598, '2010-08-04 13:50:43', 4, 'student/pdf zadaca=3', 1),
(599, '2010-08-04 13:50:43', 4, 'student/pdf zadaca=3', 1),
(600, '2010-08-04 13:50:43', 4, 'student/pdf zadaca=3', 1),
(601, '2010-08-04 13:50:43', 4, 'student/pdf zadaca=3', 1),
(602, '2010-08-04 13:52:25', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(603, '2010-08-04 13:52:26', 4, 'student/pdf zadaca=3', 1),
(604, '2010-08-04 13:52:28', 4, 'student/pdf zadaca=3', 1),
(605, '2010-08-04 13:52:29', 4, 'student/pdf zadaca=3', 1),
(606, '2010-08-04 13:52:29', 4, 'student/pdf zadaca=3', 1),
(607, '2010-08-04 13:52:30', 4, 'student/pdf zadaca=3', 1),
(608, '2010-08-04 13:52:30', 4, 'student/pdf zadaca=3', 1),
(609, '2010-08-04 13:52:30', 4, 'student/pdf zadaca=3', 1),
(610, '2010-08-04 13:52:30', 4, 'student/pdf zadaca=3', 1),
(611, '2010-08-04 13:52:31', 4, 'student/pdf zadaca=3', 1),
(612, '2010-08-04 13:52:31', 4, 'student/pdf zadaca=3', 1),
(613, '2010-08-04 13:52:31', 4, 'student/pdf zadaca=3', 1),
(614, '2010-08-04 13:52:31', 4, 'student/pdf zadaca=3', 1),
(615, '2010-08-04 13:52:31', 4, 'student/pdf zadaca=3', 1),
(616, '2010-08-04 13:52:31', 4, 'student/pdf zadaca=3', 1),
(617, '2010-08-04 13:52:31', 4, 'student/pdf zadaca=3', 1),
(618, '2010-08-04 13:52:32', 4, 'student/pdf zadaca=3', 1),
(619, '2010-08-04 13:52:32', 4, 'student/pdf zadaca=3', 1),
(620, '2010-08-04 13:52:32', 4, 'student/pdf zadaca=3', 1);
INSERT INTO `log` (`id`, `vrijeme`, `userid`, `dogadjaj`, `nivo`) VALUES 
(621, '2010-08-04 13:52:32', 4, 'student/pdf zadaca=3', 1),
(622, '2010-08-04 13:52:32', 4, 'student/pdf zadaca=3', 1),
(623, '2010-08-04 13:52:32', 4, 'student/pdf zadaca=3', 1),
(624, '2010-08-04 13:52:32', 4, 'student/pdf zadaca=3', 1),
(625, '2010-08-04 13:52:33', 4, 'student/pdf zadaca=3', 1),
(626, '2010-08-04 13:52:34', 4, 'student/pdf zadaca=3', 1),
(627, '2010-08-04 13:52:34', 4, 'student/pdf zadaca=3', 1),
(628, '2010-08-04 13:52:35', 4, 'student/pdf zadaca=3', 1),
(629, '2010-08-04 13:52:35', 4, 'student/pdf zadaca=3', 1),
(630, '2010-08-04 13:52:35', 4, 'student/pdf zadaca=3', 1),
(631, '2010-08-04 13:52:35', 4, 'student/pdf zadaca=3', 1),
(632, '2010-08-04 13:53:19', 4, 'student/pdf zadaca=3', 1),
(633, '2010-08-04 13:53:25', 4, 'student/pdf zadaca=3', 1),
(634, '2010-08-04 13:54:50', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(635, '2010-08-04 13:54:52', 4, 'student/pdf zadaca=3', 1),
(636, '2010-08-04 13:54:54', 4, 'student/pdf zadaca=3', 1),
(637, '2010-08-04 13:54:56', 4, 'student/pdf zadaca=3', 1),
(638, '2010-08-04 13:56:02', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(639, '2010-08-04 13:56:03', 4, 'student/pdf zadaca=3', 1),
(640, '2010-08-04 13:56:08', 4, 'student/pdf zadaca=3', 1),
(641, '2010-08-04 13:57:08', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(642, '2010-08-04 13:57:15', 4, 'student/pdf zadaca=3', 1),
(643, '2010-08-04 14:01:34', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(644, '2010-08-04 14:01:38', 4, 'student/pdf zadaca=3', 1),
(645, '2010-08-04 14:02:37', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(646, '2010-08-04 14:02:41', 4, 'student/pdf zadaca=3', 1),
(647, '2010-08-04 14:03:59', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(648, '2010-08-04 14:04:01', 4, 'student/pdf zadaca=3', 1),
(649, '2010-08-04 14:04:24', 4, 'student/pdf zadaca=3', 1),
(650, '2010-08-04 14:07:08', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(651, '2010-08-04 14:07:12', 4, 'student/pdf zadaca=3', 1),
(652, '2010-08-04 14:07:54', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(653, '2010-08-04 14:07:55', 4, 'student/pdf zadaca=3', 1),
(654, '2010-08-04 14:09:01', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(655, '2010-08-04 14:09:03', 4, 'student/pdf zadaca=3', 1),
(656, '2010-08-04 14:09:49', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(657, '2010-08-04 14:09:51', 4, 'student/pdf zadaca=3', 1),
(658, '2010-08-04 14:11:21', 4, 'student/intro', 1),
(659, '2010-08-04 14:11:24', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(660, '2010-08-04 14:11:26', 4, 'student/pdf zadaca=3', 1),
(661, '2010-08-04 14:11:33', 4, 'student/pdf zadaca=3', 1),
(662, '2010-08-04 14:12:14', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(663, '2010-08-04 14:12:17', 4, 'student/pdf zadaca=3', 1),
(664, '2010-08-04 14:13:20', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(665, '2010-08-04 14:13:22', 4, 'student/pdf zadaca=3', 1),
(666, '2010-08-04 14:20:57', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(667, '2010-08-04 14:21:06', 4, 'student/pdf zadaca=3', 1),
(668, '2010-08-04 14:23:05', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(669, '2010-08-04 14:23:07', 4, 'student/pdf zadaca=3', 1),
(670, '2010-08-04 14:23:19', 4, 'student/pdf zadaca=3', 1),
(671, '2010-08-04 14:26:38', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(672, '2010-08-04 14:26:40', 4, 'student/pdf zadaca=3', 1),
(673, '2010-08-04 14:27:00', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(674, '2010-08-04 14:28:06', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(675, '2010-08-04 14:28:19', 4, 'student/intro', 1),
(676, '2010-08-04 14:28:21', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(677, '2010-08-04 14:28:23', 4, 'student/pdf zadaca=3', 1),
(678, '2010-08-04 14:31:07', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(679, '2010-08-04 14:31:08', 4, 'student/pdf zadaca=3', 1),
(680, '2010-08-04 14:32:32', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(681, '2010-08-04 14:32:35', 4, 'student/pdf zadaca=3', 1),
(682, '2010-08-04 14:33:45', 4, 'student/pdf zadaca=3', 1),
(683, '2010-08-04 14:36:00', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(684, '2010-08-04 14:36:02', 4, 'student/pdf zadaca=3', 1),
(685, '2010-08-04 14:40:41', 4, 'student/pdf zadaca=3', 1),
(686, '2010-08-04 14:41:22', 4, 'student/pdf zadaca=3', 1),
(687, '2010-08-04 14:59:28', 4, 'login', 1),
(688, '2010-08-04 14:59:28', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(689, '2010-08-04 14:59:30', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(690, '2010-08-04 14:59:35', 4, 'student/pdf zadaca=3', 1),
(691, '2010-08-04 15:05:41', 4, 'student/pdf zadaca=3', 1),
(692, '2010-08-04 15:07:13', 4, 'student/pdf zadaca=3', 1),
(693, '2010-08-04 15:08:25', 4, 'student/pdf zadaca=3', 1),
(694, '2010-08-04 15:09:12', 4, 'student/pdf zadaca=3', 1),
(695, '2010-08-04 15:54:20', 4, '/zamger41/index.php?', 1),
(696, '2010-08-04 16:35:20', 4, '/zamger41/index.php?', 1),
(697, '2010-08-04 16:35:45', 4, '/zamger41/index.php?', 1),
(698, '2010-08-04 16:36:09', 4, '/zamger41/index.php?', 1),
(699, '2010-08-04 16:41:26', 4, '/zamger41/index.php?', 1),
(700, '2010-08-04 16:42:50', 4, '/zamger41/index.php?', 1),
(701, '2010-08-04 16:46:06', 4, '/zamger41/index.php?', 1),
(702, '2010-08-04 16:47:14', 4, '/zamger41/index.php?', 1),
(703, '2010-08-05 14:09:47', 4, 'login', 1),
(704, '2010-08-05 14:09:48', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(705, '2010-08-05 14:09:51', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(706, '2010-08-05 14:09:55', 4, 'student/pdf zadaca=3', 1),
(707, '2010-08-05 14:14:08', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(708, '2010-08-05 14:14:11', 4, 'common/attachment zadaca=1 zadatak=1', 1),
(709, '2010-08-05 14:18:23', 4, 'common/attachment zadaca=1 zadatak=1', 1),
(710, '2010-08-05 14:24:34', 4, 'common/attachment zadaca=1 zadatak=1', 1),
(711, '2010-08-05 14:29:56', 4, 'login', 1),
(712, '2010-08-05 14:29:56', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(713, '2010-08-05 14:29:59', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(714, '2010-08-05 14:30:08', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(715, '2010-08-05 14:30:46', 4, 'common/attachment zadaca=1 zadatak=1', 1),
(716, '2010-08-05 14:36:19', 4, 'login', 1),
(717, '2010-08-05 14:36:19', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(718, '2010-08-05 14:36:21', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(719, '2010-08-05 14:36:23', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(720, '2010-08-05 14:36:27', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(721, '2010-08-05 14:36:36', 4, 'student/pdf zadaca=3', 1),
(722, '2010-08-05 14:38:05', 4, 'student/pdf zadaca=3', 1),
(723, '2010-08-05 14:39:39', 4, 'student/pdf zadaca=3', 1),
(724, '2010-08-05 14:39:50', 4, 'student/pdf zadaca=3', 1),
(725, '2010-08-05 14:40:46', 4, 'student/pdf zadaca=3', 1),
(726, '2010-08-05 14:42:32', 4, 'student/pdf zadaca=3', 1),
(727, '2010-08-05 14:43:03', 4, 'student/pdf zadaca=3', 1),
(728, '2010-08-05 14:43:11', 4, 'student/pdf zadaca=3', 1),
(729, '2010-08-05 14:43:26', 4, 'student/pdf zadaca=3', 1),
(730, '2010-08-05 14:44:27', 4, 'student/pdf zadaca=3', 1),
(731, '2010-08-05 14:44:32', 4, 'student/pdf zadaca=3', 1),
(732, '2010-08-05 14:46:01', 4, 'student/pdf zadaca=3', 1),
(733, '2010-08-05 14:47:27', 4, 'student/pdf zadaca=3', 1),
(734, '2010-08-05 14:47:35', 4, 'student/pdf zadaca=3', 1),
(735, '2010-08-05 14:48:26', 4, 'student/pdf zadaca=3', 1),
(736, '2010-08-05 14:49:15', 4, 'student/pdf zadaca=3', 1),
(737, '2010-08-05 14:49:27', 4, 'student/pdf zadaca=3', 1),
(738, '2010-08-05 14:52:46', 4, 'student/pdf zadaca=3', 1),
(739, '2010-08-05 14:53:10', 4, 'student/pdf zadaca=3', 1),
(740, '2010-08-05 14:56:43', 4, 'student/pdf zadaca=3', 1),
(741, '2010-08-05 14:57:22', 4, 'student/pdf zadaca=3', 1),
(742, '2010-08-05 14:58:21', 4, 'student/pdf zadaca=3', 1),
(743, '2010-08-05 14:58:55', 4, 'student/pdf zadaca=3', 1),
(744, '2010-08-05 14:59:33', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(745, '2010-08-05 14:59:36', 4, 'student/pdf zadaca=3', 1),
(746, '2010-08-05 15:00:30', 4, 'student/pdf zadaca=3', 1),
(747, '2010-08-05 15:01:25', 4, 'student/pdf zadaca=3', 1),
(748, '2010-08-05 15:02:01', 4, 'student/pdf zadaca=3', 1),
(749, '2010-08-05 15:02:26', 4, 'student/pdf zadaca=3', 1),
(750, '2010-08-05 15:03:33', 4, 'student/pdf zadaca=3', 1),
(751, '2010-08-05 15:04:05', 4, 'student/pdf zadaca=3', 1),
(752, '2010-08-05 15:04:37', 4, 'student/pdf zadaca=3', 1),
(753, '2010-08-05 15:05:09', 4, 'student/pdf zadaca=3', 1),
(754, '2010-08-05 15:06:16', 4, 'student/pdf zadaca=3', 1),
(755, '2010-08-05 15:07:37', 4, 'student/pdf zadaca=3', 1),
(756, '2010-08-05 15:08:44', 4, 'student/pdf zadaca=3', 1),
(757, '2010-08-05 15:09:12', 4, 'student/pdf zadaca=3', 1),
(758, '2010-08-05 15:11:33', 4, 'student/pdf zadaca=3', 1),
(759, '2010-08-05 15:13:14', 4, 'student/pdf zadaca=3', 1),
(760, '2010-08-05 15:15:44', 4, 'student/pdf zadaca=3', 1),
(761, '2010-08-05 15:17:38', 4, 'student/pdf zadaca=3', 1),
(762, '2010-08-05 15:18:08', 4, 'student/pdf zadaca=3', 1),
(763, '2010-08-05 15:18:44', 4, 'student/pdf zadaca=3', 1),
(764, '2010-08-05 15:19:17', 4, 'student/pdf zadaca=3', 1),
(765, '2010-08-05 15:19:57', 4, 'student/pdf zadaca=3', 1),
(766, '2010-08-05 15:21:10', 4, 'student/pdf zadaca=3', 1),
(767, '2010-08-05 15:21:34', 4, 'student/pdf zadaca=3', 1),
(768, '2010-08-05 15:22:15', 4, 'student/pdf zadaca=3', 1),
(769, '2010-08-05 15:22:45', 4, 'student/pdf zadaca=3', 1),
(770, '2010-08-05 15:22:57', 4, 'student/pdf zadaca=3', 1),
(771, '2010-08-05 15:23:16', 4, 'student/pdf zadaca=3', 1),
(772, '2010-08-05 15:23:35', 4, 'student/pdf zadaca=3', 1),
(773, '2010-08-05 15:23:58', 4, 'student/pdf zadaca=3', 1),
(774, '2010-08-05 15:24:41', 4, 'student/pdf zadaca=3', 1),
(775, '2010-08-05 15:25:22', 4, 'student/pdf zadaca=3', 1),
(776, '2010-08-05 15:26:30', 4, 'student/pdf zadaca=3', 1),
(777, '2010-08-05 15:27:09', 4, 'student/pdf zadaca=3', 1),
(778, '2010-08-05 15:28:14', 4, 'student/pdf zadaca=3', 1),
(779, '2010-08-05 15:28:45', 4, 'student/pdf zadaca=3', 1),
(780, '2010-08-05 15:29:35', 4, 'student/pdf zadaca=3', 1),
(781, '2010-08-05 15:29:52', 4, 'student/pdf zadaca=3', 1),
(782, '2010-08-05 15:30:27', 4, 'student/pdf zadaca=3', 1),
(783, '2010-08-05 15:31:24', 4, 'student/pdf zadaca=3', 1),
(784, '2010-08-05 15:31:48', 4, 'student/pdf zadaca=3', 1),
(785, '2010-08-05 15:32:05', 4, 'student/pdf zadaca=3', 1),
(786, '2010-08-05 15:32:10', 4, 'student/pdf zadaca=3', 1),
(787, '2010-08-05 15:32:26', 4, 'student/pdf zadaca=3', 1),
(788, '2010-08-05 15:33:22', 4, 'student/pdf zadaca=3', 1),
(789, '2010-08-05 15:34:07', 4, 'student/pdf zadaca=3', 1),
(790, '2010-08-05 15:34:26', 4, 'student/pdf zadaca=3', 1),
(791, '2010-08-05 15:36:03', 4, 'student/pdf zadaca=3', 1),
(792, '2010-08-05 15:36:49', 4, 'student/pdf zadaca=3', 1),
(793, '2010-08-05 15:37:28', 4, 'student/pdf zadaca=3', 1),
(794, '2010-08-05 15:39:02', 4, 'student/pdf zadaca=3', 1),
(795, '2010-08-05 15:39:35', 4, 'student/pdf zadaca=3', 1),
(796, '2010-08-05 15:43:05', 4, 'student/pdf zadaca=3', 1),
(797, '2010-08-05 15:43:49', 4, 'student/pdf zadaca=3', 1),
(798, '2010-08-05 16:18:55', 4, 'student/pdf zadaca=3', 1),
(799, '2010-08-05 16:21:25', 4, 'student/pdf zadaca=3', 1),
(800, '2010-08-05 16:23:24', 4, 'student/pdf zadaca=3', 1),
(801, '2010-08-05 16:27:00', 4, 'student/pdf zadaca=3', 1),
(802, '2010-08-05 16:27:17', 4, 'student/pdf zadaca=3', 1),
(803, '2010-08-05 16:27:56', 4, 'student/pdf zadaca=3', 1),
(804, '2010-08-05 16:33:41', 4, 'student/pdf zadaca=3', 1),
(805, '2010-08-05 16:35:00', 4, 'student/pdf zadaca=3', 1),
(806, '2010-08-05 16:37:10', 4, 'student/pdf zadaca=3', 1),
(807, '2010-08-05 16:39:28', 4, 'student/pdf zadaca=3', 1),
(808, '2010-08-05 16:40:37', 4, 'student/pdf zadaca=3', 1),
(809, '2010-08-05 16:45:59', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(810, '2010-08-05 16:46:02', 4, 'student/pdf zadaca=3', 1),
(811, '2010-08-05 16:49:47', 4, 'student/pdf zadaca=3', 1),
(812, '2010-08-05 16:52:10', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(813, '2010-08-05 16:52:12', 4, 'student/pdf zadaca=3', 1),
(814, '2010-08-05 16:53:19', 4, 'student/pdf zadaca=3', 1),
(815, '2010-08-05 16:54:04', 4, 'student/pdf zadaca=3', 1),
(816, '2010-08-05 16:58:21', 4, 'student/pdf zadaca=3', 1),
(817, '2010-08-05 16:59:27', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(818, '2010-08-05 16:59:29', 4, 'student/pdf zadaca=3', 1),
(819, '2010-08-05 17:00:32', 4, 'student/pdf zadaca=3', 1),
(820, '2010-08-05 17:01:42', 4, 'student/pdf zadaca=3', 1),
(821, '2010-08-05 17:02:37', 4, 'student/pdf zadaca=3', 1),
(822, '2010-08-05 17:05:20', 4, 'student/pdf zadaca=3', 1),
(823, '2010-08-05 17:05:33', 4, 'student/pdf zadaca=3', 1),
(824, '2010-08-05 17:07:24', 4, 'student/pdf zadaca=3', 1),
(825, '2010-08-05 17:09:21', 4, 'student/pdf zadaca=3', 1),
(826, '2010-08-05 17:10:21', 4, 'student/pdf zadaca=3', 1),
(827, '2010-08-05 17:11:19', 4, 'student/pdf zadaca=3', 1),
(828, '2010-08-05 17:12:31', 4, 'student/pdf zadaca=3', 1),
(829, '2010-08-05 17:13:46', 4, 'student/pdf zadaca=3', 1),
(830, '2010-08-05 17:14:43', 4, 'student/pdf zadaca=3', 1),
(831, '2010-08-05 17:16:17', 4, 'student/pdf zadaca=3', 1),
(832, '2010-08-05 17:17:11', 4, 'student/pdf zadaca=3', 1),
(833, '2010-08-05 17:18:51', 4, 'student/pdf zadaca=3', 1),
(834, '2010-08-05 17:21:12', 4, 'student/pdf zadaca=3', 1),
(835, '2010-08-05 17:22:37', 4, 'student/pdf zadaca=3', 1),
(836, '2010-08-05 17:28:17', 4, 'student/pdf zadaca=3', 1),
(837, '2010-08-05 17:29:40', 4, 'student/pdf zadaca=3', 1),
(838, '2010-08-05 17:30:54', 4, 'student/pdf zadaca=3', 1),
(839, '2010-08-05 17:34:08', 4, 'student/pdf zadaca=3', 1),
(840, '2010-08-05 17:36:15', 4, 'student/pdf zadaca=3', 1),
(841, '2010-08-05 17:37:54', 4, 'student/pdf zadaca=3', 1),
(842, '2010-08-05 17:40:37', 4, 'student/pdf zadaca=3', 1),
(843, '2010-08-05 17:40:59', 4, 'student/pdf zadaca=3', 1),
(844, '2010-08-05 17:42:04', 4, 'student/pdf zadaca=3', 1),
(845, '2010-08-05 17:43:34', 4, 'student/pdf zadaca=3', 1),
(846, '2010-08-05 17:44:27', 4, 'student/pdf zadaca=3', 1),
(847, '2010-08-05 17:45:07', 4, 'student/pdf zadaca=3', 1),
(848, '2010-08-05 17:46:41', 4, 'student/pdf zadaca=3', 1),
(849, '2010-08-05 17:47:24', 4, 'student/pdf zadaca=3', 1),
(850, '2010-08-05 17:48:30', 4, 'student/pdf zadaca=3', 1),
(851, '2010-08-05 17:49:59', 4, 'student/pdf zadaca=3', 1),
(852, '2010-08-05 17:51:25', 4, 'student/pdf zadaca=3', 1),
(853, '2010-08-05 17:52:57', 4, 'student/pdf zadaca=3', 1),
(854, '2010-08-05 17:53:46', 4, 'student/pdf zadaca=3', 1),
(855, '2010-08-05 17:57:47', 4, 'student/pdf zadaca=3', 1),
(856, '2010-08-05 17:58:39', 4, 'student/pdf zadaca=3', 1),
(857, '2010-08-05 18:05:03', 4, 'logout', 1),
(858, '2010-08-05 18:05:08', 1, 'login', 1),
(859, '2010-08-05 18:05:08', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(860, '2010-08-05 18:05:12', 1, 'studentska/intro', 1),
(861, '2010-08-05 18:05:15', 1, 'studentska/izvjestaji', 1),
(862, '2010-08-05 18:05:18', 1, 'izvjestaj/granicni parcijalni=0 predmet=1 akademska_godina=4', 1),
(863, '2010-08-05 18:06:06', 1, 'izvjestaj/granicni parcijalni=0 predmet=1 akademska_godina=4', 1),
(864, '2010-08-05 18:06:09', 1, 'izvjestaj/granicni parcijalni=0 predmet=1 akademska_godina=4', 1),
(865, '2010-08-05 18:08:49', 1, 'izvjestaj/granicni parcijalni=0 predmet=1 akademska_godina=4', 1),
(866, '2010-08-05 18:08:51', 1, 'izvjestaj/granicni parcijalni=0 predmet=1 akademska_godina=4', 1),
(867, '2010-08-05 18:09:20', 1, 'izvjestaj/granicni parcijalni=0 predmet=1 akademska_godina=4', 1),
(868, '2010-08-05 18:09:23', 1, 'izvjestaj/granicni parcijalni=0 predmet=1 akademska_godina=4', 1),
(869, '2010-08-05 18:09:24', 1, 'studentska/izvjestaji', 1),
(870, '2010-08-05 18:09:29', 1, 'izvjestaj/genijalci akademska_godina=4 limit_prosjek=8 studij=0 godina_studija=1 limit_ects=22', 1),
(871, '2010-08-05 18:09:34', 1, 'studentska/izvjestaji', 1),
(872, '2010-08-05 18:09:37', 1, 'izvjestaj/genijalci akademska_godina=4 limit_prosjek=8 studij=0 godina_studija=2 limit_ects=22', 1),
(873, '2010-08-05 18:09:38', 1, 'studentska/izvjestaji', 1),
(874, '2010-08-05 18:09:40', 1, 'izvjestaj/genijalci akademska_godina=4 limit_prosjek=8 studij=0 godina_studija=3 limit_ects=22', 1),
(875, '2010-08-05 18:09:41', 1, 'studentska/izvjestaji', 1),
(876, '2010-08-05 18:09:45', 1, 'pristup nepostojecom modulu izvjestaj/prolaznosttab', 3),
(877, '2010-08-05 18:09:45', 1, 'izvjestaj/prolaznosttab', 1),
(878, '2010-08-05 18:09:47', 1, 'studentska/izvjestaji', 1),
(879, '2010-08-05 18:09:52', 1, 'izvjestaj/pregled', 1),
(880, '2010-08-05 18:09:57', 1, 'studentska/izvjestaji', 1),
(881, '2010-08-05 18:10:02', 1, 'studentska/raspored', 1),
(882, '2010-08-05 18:10:05', 1, 'studentska/obavijest', 1),
(883, '2010-08-05 18:10:07', 1, 'studentska/anketa', 1),
(884, '2010-08-05 18:10:15', 1, 'studentska/plan', 1),
(885, '2010-08-05 18:10:18', 1, 'studentska/anketa', 1),
(886, '2010-08-05 18:10:19', 1, 'studentska/obavijest', 1),
(887, '2010-08-05 18:10:20', 1, 'studentska/raspored', 1),
(888, '2010-08-05 18:10:22', 1, 'logout', 1),
(889, '2010-08-05 18:12:03', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/pdf', 3),
(890, '2010-08-05 18:12:11', 4, 'login', 1),
(891, '2010-08-05 18:12:11', 4, 'student/pdf zadaca=3 loginforma=1 login=muris', 1),
(892, '2010-08-05 18:13:37', 4, 'login', 1),
(893, '2010-08-05 18:13:37', 4, 'student/pdf zadaca=3 loginforma=1 login=muris', 1),
(894, '2010-08-05 21:09:35', 4, 'login', 1),
(895, '2010-08-05 21:09:35', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(896, '2010-08-05 21:09:43', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(897, '2010-08-05 21:09:46', 4, 'student/zadaca predmet=1 ag=1', 1),
(898, '2010-08-05 21:09:49', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(899, '2010-08-05 21:10:00', 4, 'student/zadaca predmet=1 ag=1 zadaca=3 zadatak=1', 1),
(900, '2010-08-05 21:10:42', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(901, '2010-08-05 21:10:46', 4, 'student/pdf zadaca=3', 1),
(902, '2010-08-05 21:11:50', 4, 'logout', 1),
(903, '2010-08-05 21:11:54', 1, 'login', 1),
(904, '2010-08-05 21:11:54', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(905, '2010-08-05 21:11:57', 1, 'studentska/intro', 1),
(906, '2010-08-05 21:12:00', 1, 'studentska/osobe', 1),
(907, '2010-08-05 21:12:02', 1, 'studentska/osobe search=sve', 1),
(908, '2010-08-05 21:12:11', 1, 'studentska/osobe search=sve akcija=edit osoba=4', 1),
(909, '2010-08-05 21:12:19', 1, 'studentska/obavijest', 1),
(910, '2010-08-05 21:12:21', 1, 'studentska/obavijest akcija=compose', 1),
(911, '2010-08-05 21:12:36', 1, 'studentska/obavijest akcija=send opseg=0 naslov=dsjkdjksjdksjkdsjk ', 1),
(912, '2010-08-05 21:12:36', 1, 'poslana obavijest, opseg 0 primalac 0', 2),
(913, '2010-08-05 21:12:43', 1, 'studentska/intro', 1),
(914, '2010-08-05 21:12:47', 1, 'logout', 1),
(915, '2010-08-05 21:12:57', 4, 'login', 1),
(916, '2010-08-05 21:12:57', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(917, '2010-08-05 21:13:04', 4, 'common/inbox poruka=3', 1),
(918, '2010-08-05 21:13:16', 4, 'login', 1),
(919, '2010-08-05 21:13:16', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(920, '2010-08-05 21:13:19', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(921, '2010-08-05 21:13:22', 4, 'logout', 1),
(922, '2010-08-06 18:24:53', 4, 'login', 1),
(923, '2010-08-06 18:24:53', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(924, '2010-08-06 18:24:55', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(925, '2010-08-06 18:25:00', 4, 'student/zadaca predmet=1 ag=1 zadaca=3 zadatak=1', 1),
(926, '2010-08-06 18:25:09', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(927, '2010-08-06 18:25:18', 4, 'student/zadaca predmet=1 ag=1 zadaca=2 zadatak=1', 1),
(928, '2010-08-06 18:25:20', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(929, '2010-08-06 18:25:29', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(930, '2010-08-06 18:25:31', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(931, '2010-08-06 18:25:36', 4, 'student/pdf zadaca=2', 1),
(932, '2010-08-06 18:30:13', 4, 'login', 1),
(933, '2010-08-06 18:30:13', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(934, '2010-08-06 18:30:16', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(935, '2010-08-06 18:30:18', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(936, '2010-08-06 18:32:06', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(937, '2010-08-06 18:32:09', 4, 'student/pdf zadaca=2', 1),
(938, '2010-08-06 18:32:56', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(939, '2010-08-06 18:36:30', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(940, '2010-08-06 18:37:14', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(941, '2010-08-06 18:37:37', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(942, '2010-08-06 18:42:56', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(943, '2010-08-06 18:44:12', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(944, '2010-08-06 18:45:10', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(945, '2010-08-06 18:46:01', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(946, '2010-08-06 18:46:07', 4, 'student/pdf zadaca=2', 1),
(947, '2010-08-06 18:46:39', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(948, '2010-08-06 18:47:40', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(949, '2010-08-06 18:49:06', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(950, '2010-08-06 18:50:22', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(951, '2010-08-06 18:50:25', 4, 'student/pdf zadaca=2', 1),
(952, '2010-08-06 18:53:53', 4, 'login', 1),
(953, '2010-08-06 18:53:53', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(954, '2010-08-06 18:53:55', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(955, '2010-08-06 18:53:59', 4, 'student/pdf zadaca=3', 1),
(956, '2010-08-06 18:54:52', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(957, '2010-08-06 18:54:54', 4, 'student/pdf zadaca=3', 1),
(958, '2010-08-06 18:57:09', 4, 'student/pdf zadaca=3', 1),
(959, '2010-08-06 18:58:18', 4, 'student/pdf zadaca=3', 1),
(960, '2010-08-06 18:58:49', 4, 'student/pdf zadaca=3', 1),
(961, '2010-08-06 18:59:33', 4, 'student/pdf zadaca=3', 1),
(962, '2010-08-06 19:02:41', 4, 'student/pdf zadaca=3', 1),
(963, '2010-08-06 19:04:19', 4, 'student/pdf zadaca=3', 1),
(964, '2010-08-06 19:04:19', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/predmet', 3),
(965, '2010-08-06 19:05:30', 4, 'student/pdf zadaca=3', 1),
(966, '2010-08-06 19:05:30', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/predmet', 3),
(967, '2010-08-06 19:07:45', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(968, '2010-08-06 19:08:32', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(969, '2010-08-06 19:09:04', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(970, '2010-08-06 19:09:06', 4, 'student/pdf zadaca=3', 1),
(971, '2010-08-06 19:12:42', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(972, '2010-08-06 19:12:44', 4, 'student/pdf zadaca=2', 1),
(973, '2010-08-08 15:07:02', 4, 'login', 1),
(974, '2010-08-08 15:07:02', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(975, '2010-08-08 15:07:06', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(976, '2010-08-08 15:07:09', 4, 'student/pdf zadaca=3', 1),
(977, '2010-08-13 11:20:46', 4, 'login', 1),
(978, '2010-08-13 11:20:46', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(979, '2010-08-13 11:20:53', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(980, '2010-08-13 11:20:58', 4, 'student/pdf zadaca=3', 1),
(981, '2010-08-13 11:25:33', 4, '/zamger41/index.php?', 1),
(982, '2010-08-13 11:25:39', 4, 'logout', 1),
(983, '2010-08-13 11:25:47', 1, 'login', 1),
(984, '2010-08-13 11:25:48', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(985, '2010-08-13 11:25:54', 1, 'saradnik/intro', 1),
(986, '2010-08-13 11:26:03', 1, 'studentska/intro', 1),
(987, '2010-08-13 11:26:12', 1, 'student/intro', 1),
(988, '2010-08-13 11:26:15', 1, 'studentska/intro', 1),
(989, '2010-08-13 11:26:17', 1, 'studentska/predmeti', 1),
(990, '2010-08-13 11:26:26', 1, 'studentska/izvjestaji', 1),
(991, '2010-08-13 11:26:30', 1, 'saradnik/intro', 1),
(992, '2010-08-13 11:26:34', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(993, '2010-08-13 11:26:38', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(994, '2010-08-13 11:28:44', 1, 'logout', 1),
(995, '2010-08-13 11:28:54', 5, 'login', 1),
(996, '2010-08-13 11:28:54', 5, '/zamger41/index.php?loginforma=1 login=huse ', 1),
(997, '2010-08-13 11:29:02', 5, 'saradnik/grupa id=2', 1),
(998, '2010-08-13 11:30:10', 5, 'logout', 1),
(999, '2010-08-13 11:30:16', 1, 'login', 1),
(1000, '2010-08-13 11:30:16', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1001, '2010-08-13 11:30:21', 1, 'saradnik/intro', 1),
(1002, '2010-08-13 11:30:23', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1003, '2010-08-13 11:30:30', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1004, '2010-08-13 11:30:33', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1005, '2010-08-13 11:31:55', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1006, '2010-08-13 11:31:58', 1, 'izvjestaj/statistika_predmeta predmet=1 ag=1', 1),
(1007, '2010-08-13 11:34:16', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1008, '2010-08-13 11:34:17', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1009, '2010-08-13 11:34:18', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1010, '2010-08-13 11:34:20', 1, 'logout', 1),
(1011, '2010-08-13 11:34:26', 4, 'login', 1),
(1012, '2010-08-13 11:34:26', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(1013, '2010-08-13 11:34:28', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1014, '2010-08-13 11:34:31', 4, 'student/pdf zadaca=3', 1),
(1015, '2010-08-13 11:35:24', 4, 'student/pdf zadaca=3', 1),
(1016, '2010-08-13 11:35:26', 4, 'student/pdf zadaca=3', 1),
(1017, '2010-08-13 11:35:44', 4, 'student/pdf zadaca=3', 1),
(1018, '2010-08-13 11:35:45', 4, 'student/pdf zadaca=3', 1),
(1019, '2010-08-13 11:35:45', 4, 'student/pdf zadaca=3', 1),
(1020, '2010-08-13 11:35:46', 4, 'student/pdf zadaca=3', 1),
(1021, '2010-08-13 11:35:46', 4, 'student/pdf zadaca=3', 1),
(1022, '2010-08-13 11:35:46', 4, 'student/pdf zadaca=3', 1),
(1023, '2010-08-13 11:35:46', 4, 'student/pdf zadaca=3', 1),
(1024, '2010-08-13 11:36:21', 4, 'student/pdf zadaca=3', 1),
(1025, '2010-08-13 11:36:22', 4, 'student/pdf zadaca=3', 1),
(1026, '2010-08-13 11:36:23', 4, 'student/pdf zadaca=3', 1),
(1027, '2010-08-13 11:36:23', 4, 'student/pdf zadaca=3', 1),
(1028, '2010-08-13 11:36:23', 4, 'student/pdf zadaca=3', 1),
(1029, '2010-08-13 11:36:24', 4, 'student/pdf zadaca=3', 1),
(1030, '2010-08-13 11:36:24', 4, 'student/pdf zadaca=3', 1),
(1031, '2010-08-13 11:36:24', 4, 'student/pdf zadaca=3', 1),
(1032, '2010-08-13 11:36:24', 4, 'student/pdf zadaca=3', 1),
(1033, '2010-08-13 11:36:24', 4, 'student/pdf zadaca=3', 1),
(1034, '2010-08-13 11:36:25', 4, 'student/pdf zadaca=3', 1),
(1035, '2010-08-13 11:36:25', 4, 'student/pdf zadaca=3', 1),
(1036, '2010-08-13 11:36:25', 4, 'student/pdf zadaca=3', 1),
(1037, '2010-08-13 11:36:25', 4, 'student/pdf zadaca=3', 1),
(1038, '2010-08-13 11:36:25', 4, 'student/pdf zadaca=3', 1),
(1039, '2010-08-13 11:36:25', 4, 'student/pdf zadaca=3', 1),
(1040, '2010-08-13 11:36:47', 4, 'student/pdf zadaca=3', 1),
(1041, '2010-08-13 11:37:06', 4, 'student/pdf zadaca=3', 1),
(1042, '2010-08-13 11:47:27', 4, '/zamger41/index.php?', 1),
(1043, '2010-08-13 11:47:33', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1044, '2010-08-13 11:47:45', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(1045, '2010-08-13 11:48:04', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1046, '2010-08-13 11:48:14', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(1047, '2010-08-13 11:48:35', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1048, '2010-08-13 11:48:38', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(1049, '2010-08-13 11:49:06', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=1 zadatak=1 labgrupa=', 1),
(1050, '2010-08-13 11:49:06', 4, 'isteklo vrijeme za slanje zadaće z1', 3),
(1051, '2010-08-13 11:49:11', 4, 'logout', 1),
(1052, '2010-08-13 11:49:18', 1, 'login', 1),
(1053, '2010-08-13 11:49:18', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1054, '2010-08-13 11:49:22', 1, 'saradnik/intro', 1),
(1055, '2010-08-13 11:49:24', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1056, '2010-08-13 11:49:26', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1057, '2010-08-13 11:50:01', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaca 4 zadataka=2 bodova=2 day=13 month=10 year=2010 sat=11 minuta=49 sekunda=26 aktivna=on attachment=on', 1),
(1058, '2010-08-13 11:50:01', 1, 'kreirana nova zadaca z4', 2),
(1059, '2010-08-13 11:50:04', 1, 'logout', 1),
(1060, '2010-08-13 11:50:12', 4, 'login', 1),
(1061, '2010-08-13 11:50:12', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(1062, '2010-08-13 11:50:18', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1063, '2010-08-13 11:50:20', 4, 'student/zadaca predmet=1 ag=1 zadaca=4 zadatak=1', 1),
(1064, '2010-08-13 11:50:33', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=4 zadatak=1 labgrupa=', 1),
(1065, '2010-08-13 11:50:33', 4, 'poslana zadaca z4 zadatak 1 (attachment)', 2),
(1066, '2010-08-13 11:52:41', 4, 'student/intro', 1),
(1067, '2010-08-13 11:52:43', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1068, '2010-08-13 11:52:45', 4, 'student/pdf zadaca=3', 1),
(1069, '2010-08-13 11:58:28', 4, 'izvjestaj/predmet predmet=1 ag=1', 1),
(1070, '2010-08-13 11:58:43', 4, 'student/pdf zadaca=2', 1),
(1071, '2010-08-13 12:00:00', 4, 'logout', 1),
(1072, '2010-08-13 12:00:05', 1, 'login', 1),
(1073, '2010-08-13 12:00:05', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1074, '2010-08-13 12:00:07', 1, 'saradnik/intro', 1),
(1075, '2010-08-13 12:00:12', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1076, '2010-08-13 12:00:16', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1077, '2010-08-13 12:02:27', 1, 'logout', 1),
(1078, '2010-08-13 12:02:34', 4, 'login', 1),
(1079, '2010-08-13 12:02:34', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(1080, '2010-08-13 12:02:36', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1081, '2010-08-13 12:05:52', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1082, '2010-08-13 12:08:00', 4, 'student/pdf zadaca=1', 1),
(1083, '2010-08-13 12:08:47', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=2', 1),
(1084, '2010-08-13 12:08:50', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1085, '2010-08-13 12:08:53', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(1086, '2010-08-13 12:08:58', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1087, '2010-08-13 12:09:02', 4, 'student/pdf zadaca=2', 1),
(1088, '2010-08-13 12:09:25', 4, 'student/pdf zadaca=4', 1),
(1089, '2010-08-13 12:18:15', 4, 'student/pdf zadaca=4', 1),
(1090, '2010-08-13 14:57:10', 1, 'login', 1),
(1091, '2010-08-13 14:57:10', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1092, '2010-08-13 14:57:12', 1, 'saradnik/intro', 1),
(1093, '2010-08-13 14:57:15', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1094, '2010-08-13 14:57:30', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1095, '2010-08-13 15:07:39', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1096, '2010-08-13 15:08:36', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1097, '2010-08-13 15:09:31', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1098, '2010-08-13 15:10:04', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1099, '2010-08-13 15:10:28', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1100, '2010-08-13 15:11:04', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Test zadataka=1 bodova=2 day=18 month=8 year=2010 sat=15 minuta=10 sekunda=28 aktivna=on attachment=on', 1),
(1101, '2010-08-13 15:11:04', 1, 'kreirana nova zadaca z5', 2),
(1102, '2010-08-13 15:19:05', 1, '/zamger41/index.php?', 1),
(1103, '2010-08-13 15:19:10', 1, 'saradnik/intro', 1),
(1104, '2010-08-13 15:19:14', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1105, '2010-08-13 15:19:18', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1106, '2010-08-13 15:23:14', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1107, '2010-08-13 15:24:29', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1108, '2010-08-13 15:26:44', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1109, '2010-08-13 15:26:56', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1110, '2010-08-13 15:27:21', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1111, '2010-08-13 15:30:16', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1112, '2010-08-13 15:31:25', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1113, '2010-08-13 15:31:56', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1114, '2010-08-13 15:32:18', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1115, '2010-08-13 15:33:49', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1116, '2010-08-13 15:34:20', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1117, '2010-08-13 15:37:32', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1118, '2010-08-13 15:38:32', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1119, '2010-08-13 15:40:02', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1120, '2010-08-13 15:40:52', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1121, '2010-08-13 15:41:20', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1122, '2010-08-13 15:46:07', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1123, '2010-08-13 15:46:17', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1124, '2010-08-13 15:46:55', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1125, '2010-08-13 15:47:04', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1126, '2010-08-13 15:47:53', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1127, '2010-08-13 15:49:08', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1128, '2010-08-13 15:49:52', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1129, '2010-08-13 15:52:39', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1130, '2010-08-13 15:53:01', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1131, '2010-08-13 15:53:58', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1132, '2010-08-13 15:54:21', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1133, '2010-08-13 15:56:32', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1134, '2010-08-13 15:57:04', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1135, '2010-08-13 15:58:43', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1136, '2010-08-13 16:03:16', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1137, '2010-08-13 16:03:34', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=fdsfdsfds zadataka=2 bodova=2 day=13 month=8 year=2010 sat=16 minuta=03 sekunda=16 attachment=1 dozvoljene_eks=', 1),
(1138, '2010-08-13 16:04:01', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=fdsfdsfds zadataka=2 bodova=2 day=13 month=8 year=2010 sat=16 minuta=03 sekunda=16 attachment=1 dozvoljene_eks=', 1),
(1139, '2010-08-13 16:04:08', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1140, '2010-08-13 16:04:17', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=safad zadataka=2 bodova=2 day=13 month=8 year=2010 sat=16 minuta=04 sekunda=08 attachment=1 dozvoljene_eks=', 1),
(1141, '2010-08-13 16:04:29', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=safad zadataka=2 bodova=2 day=13 month=8 year=2010 sat=16 minuta=04 sekunda=08 attachment=1 dozvoljene_eks=', 1),
(1142, '2010-08-13 16:05:32', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1143, '2010-08-13 16:05:39', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=ewrewr zadataka=2 bodova=4 day=13 month=8 year=2010 sat=16 minuta=05 sekunda=32', 1),
(1144, '2010-08-13 16:06:33', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1145, '2010-08-13 16:07:20', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Test chk zadataka=3 bodova=2 day=18 month=8 year=2010 sat=16 minuta=06 sekunda=33 attachment=1 dozvoljene_eks=', 1),
(1146, '2010-08-13 16:07:20', 1, 'SQL greska (nastavnikzadace.php : 313):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''pdf predmet=1, akademska_godina=1, naziv=''Test chk'', zadataka=3, bodova=2, rok=''''', 3),
(1147, '2010-08-13 16:07:41', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Test chk zadataka=3 bodova=2 day=18 month=8 year=2010 sat=16 minuta=06 sekunda=33 attachment=1 dozvoljene_eks=', 1),
(1148, '2010-08-13 16:07:41', 1, 'SQL greska (nastavnikzadace.php : 313):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''pdf, predmet=1, akademska_godina=1, naziv=''Test chk'', zadataka=3, bodova=2, rok=''', 3),
(1149, '2010-08-13 16:07:55', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Test chk zadataka=3 bodova=2 day=18 month=8 year=2010 sat=16 minuta=06 sekunda=33 attachment=1 dozvoljene_eks=', 1),
(1150, '2010-08-13 16:07:55', 1, 'kreirana nova zadaca z6', 2),
(1151, '2010-08-13 16:09:45', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Test chk zadataka=3 bodova=2 day=18 month=8 year=2010 sat=16 minuta=06 sekunda=33 attachment=1 dozvoljene_eks=', 1),
(1152, '2010-08-13 16:09:45', 1, 'zadaca sa nazivom ''Test chk'' vec postoji', 3),
(1153, '2010-08-13 16:09:48', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1154, '2010-08-13 16:10:00', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=rerwer zadataka=3 bodova=5 day=13 month=8 year=2010 sat=16 minuta=09 sekunda=48 attachment=1 dozvoljene_eks=', 1),
(1155, '2010-08-13 16:10:00', 1, 'kreirana nova zadaca z7', 2),
(1156, '2010-08-13 16:10:28', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=rerwer zadataka=3 bodova=5 day=13 month=8 year=2010 sat=16 minuta=09 sekunda=48 attachment=1 dozvoljene_eks=', 1),
(1157, '2010-08-13 16:10:28', 1, 'zadaca sa nazivom ''rerwer'' vec postoji', 3),
(1158, '2010-08-13 16:10:30', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1159, '2010-08-13 16:10:36', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=dfsdsf zadataka=2 bodova=3 day=13 month=8 year=2010 sat=16 minuta=10 sekunda=30', 1),
(1160, '2010-08-13 16:10:36', 1, 'kreirana nova zadaca z8', 2),
(1161, '2010-08-13 16:15:15', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1162, '2010-08-13 16:15:32', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=dfsdsfds zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=15 sekunda=15 attachment=1 dozvoljene_eks=', 1),
(1163, '2010-08-13 16:15:32', 1, 'kreirana nova zadaca z9', 2),
(1164, '2010-08-13 16:17:12', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1165, '2010-08-13 16:18:08', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=ddasd zadataka=2 bodova=2 day=13 month=8 year=2010 sat=16 minuta=17 sekunda=12 attachment=1 dozvoljene_eks=', 1),
(1166, '2010-08-13 16:18:08', 1, 'kreirana nova zadaca z10', 2),
(1167, '2010-08-13 16:18:45', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1168, '2010-08-13 16:19:01', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=sfsdfsdf2 zadataka=3 bodova=3 day=13 month=8 year=2010 sat=16 minuta=18 sekunda=45 attachment=1 dozvoljene_eks=', 1),
(1169, '2010-08-13 16:19:01', 1, 'kreirana nova zadaca z11', 2),
(1170, '2010-08-13 16:19:59', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1171, '2010-08-13 16:20:12', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=eeeee zadataka=2 bodova=3 day=13 month=8 year=2010 sat=16 minuta=19 sekunda=59 attachment=1 dozvoljene_eks=', 1),
(1172, '2010-08-13 16:20:12', 1, 'kreirana nova zadaca z12', 2),
(1173, '2010-08-13 16:22:38', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1174, '2010-08-13 16:22:54', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Muris zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=22 sekunda=38 attachment=1 dozvoljene_eks=ppt,pptx', 1),
(1175, '2010-08-13 16:22:54', 1, 'kreirana nova zadaca z13', 2),
(1176, '2010-08-13 16:24:30', 1, '/zamger41/index.php?', 1),
(1177, '2010-08-13 16:24:38', 1, 'admin/kompakt', 1),
(1178, '2010-08-13 16:25:17', 1, 'saradnik/intro', 1),
(1179, '2010-08-13 16:25:19', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1180, '2010-08-13 16:25:22', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1181, '2010-08-13 16:26:40', 1, 'logout', 1),
(1182, '2010-08-13 16:26:46', 4, 'login', 1),
(1183, '2010-08-13 16:26:46', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(1184, '2010-08-13 16:26:51', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1185, '2010-08-13 16:26:59', 4, 'student/zadaca predmet=1 ag=1 zadaca=12 zadatak=1', 1),
(1186, '2010-08-13 16:31:32', 4, 'student/zadaca predmet=1 ag=1 zadaca=12 zadatak=1', 1),
(1187, '2010-08-13 16:31:42', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1188, '2010-08-13 16:31:45', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1189, '2010-08-13 16:32:30', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1190, '2010-08-13 16:33:28', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1191, '2010-08-13 16:33:33', 4, 'student/zadaca', 1),
(1192, '2010-08-13 16:33:33', 4, 'nepoznat predmet 0', 3),
(1193, '2010-08-13 16:33:40', 4, 'pristup nepostojecom modulu student/zadaca.php', 3),
(1194, '2010-08-13 16:33:40', 4, 'student/zadaca.php', 1),
(1195, '2010-08-13 16:33:53', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1196, '2010-08-13 16:33:58', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1197, '2010-08-13 16:34:57', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1198, '2010-08-13 16:38:32', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1199, '2010-08-13 16:38:33', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1200, '2010-08-13 16:38:34', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1201, '2010-08-13 16:38:34', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1202, '2010-08-13 16:38:34', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1203, '2010-08-13 16:40:00', 4, 'student/zadaca predmet=1 ag=1 zadaca=2 zadatak=1', 1),
(1204, '2010-08-13 16:40:27', 4, 'student/zadaca predmet=1 ag=1 zadaca=3 zadatak=1', 1),
(1205, '2010-08-13 16:42:26', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1206, '2010-08-13 16:44:16', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1207, '2010-08-13 16:44:38', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(1208, '2010-08-13 16:44:43', 4, 'student/zadaca predmet=1 ag=1 zadaca=3 zadatak=1', 1),
(1209, '2010-08-13 16:49:41', 4, 'student/zadaca predmet=1 ag=1 zadaca=3 zadatak=2', 1),
(1210, '2010-08-13 16:50:21', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1211, '2010-08-13 16:50:25', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1212, '2010-08-13 16:50:38', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=13 zadatak=1 labgrupa=', 1),
(1213, '2010-08-13 16:50:38', 4, 'isteklo vrijeme za slanje zadaće z13', 3),
(1214, '2010-08-13 16:51:08', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=1', 1),
(1215, '2010-08-13 16:51:18', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=1 labgrupa=', 1),
(1216, '2010-08-13 16:51:18', 4, 'poslana zadaca z6 zadatak 1 (attachment)', 2),
(1217, '2010-08-13 16:53:33', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=1 labgrupa=', 1),
(1218, '2010-08-13 16:53:33', 4, 'poslana zadaca z6 zadatak 1 (attachment)', 2),
(1219, '2010-08-13 16:54:46', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=1 labgrupa=', 1),
(1220, '2010-08-13 16:55:04', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=1 labgrupa=', 1),
(1221, '2010-08-13 16:55:04', 4, 'poslana zadaca z6 zadatak 1 (attachment)', 2),
(1222, '2010-08-13 16:55:22', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=1 labgrupa=', 1),
(1223, '2010-08-13 16:55:22', 4, 'poslana zadaca z6 zadatak 1 (attachment)', 2),
(1224, '2010-08-13 16:55:52', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=1 labgrupa=', 1),
(1225, '2010-08-13 16:56:05', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=1 labgrupa=', 1),
(1226, '2010-08-13 16:56:05', 4, 'poslana zadaca z6 zadatak 1 (attachment)', 2),
(1227, '2010-08-13 17:01:58', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=1 labgrupa=', 1),
(1228, '2010-08-13 17:01:58', 4, 'poslana zadaca z6 zadatak 1 (attachment)', 2),
(1229, '2010-08-13 17:02:18', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1230, '2010-08-13 17:02:27', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=2 labgrupa=', 1),
(1231, '2010-08-13 17:02:27', 4, 'poslana zadaca z6 zadatak 2 (attachment)', 2),
(1232, '2010-08-13 17:03:17', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=1', 1),
(1233, '2010-08-13 17:03:20', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1234, '2010-08-13 17:03:23', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=1', 1),
(1235, '2010-08-13 17:03:25', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1236, '2010-08-13 17:04:01', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=1', 1),
(1237, '2010-08-13 17:04:03', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1238, '2010-08-13 17:04:06', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1239, '2010-08-13 17:04:17', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=2 labgrupa=', 1),
(1240, '2010-08-13 17:04:21', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1241, '2010-08-13 17:04:22', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=1', 1),
(1242, '2010-08-13 17:04:24', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1243, '2010-08-13 17:05:03', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=2 labgrupa=', 1),
(1244, '2010-08-13 17:05:12', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1245, '2010-08-13 17:05:22', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=2 labgrupa=', 1),
(1246, '2010-08-13 17:05:27', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1247, '2010-08-13 17:05:32', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=1', 1),
(1248, '2010-08-13 17:05:33', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1249, '2010-08-13 17:05:46', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1250, '2010-08-13 17:05:48', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1251, '2010-08-13 17:06:23', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=2 labgrupa=', 1),
(1252, '2010-08-13 17:06:28', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=2 labgrupa=', 1),
(1253, '2010-08-13 17:06:30', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=2', 1),
(1254, '2010-08-13 17:06:39', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=2 labgrupa=', 1),
(1255, '2010-08-13 17:06:53', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=2 labgrupa=', 1),
(1256, '2010-08-13 17:06:53', 4, 'poslana zadaca z6 zadatak 2 (attachment)', 2),
(1257, '2010-08-13 17:07:05', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=2 labgrupa=', 1),
(1258, '2010-08-13 17:07:05', 4, 'poslana zadaca z6 zadatak 2 (attachment)', 2),
(1259, '2010-08-13 17:15:49', 4, 'student/zadaca predmet=1 ag=1 zadaca=6 zadatak=3', 1),
(1260, '2010-08-13 17:16:05', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=3 labgrupa=', 1),
(1261, '2010-08-13 17:16:05', 4, 'greska kod attachmenta (zadaca z6, varijabla program je: c:/wamp/tmpphp451.tmp)', 3),
(1262, '2010-08-13 17:16:24', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=3 labgrupa=', 1),
(1263, '2010-08-13 17:16:24', 4, 'greska kod attachmenta (zadaca z6, varijabla program je: c:/wamp/tmpphp453.tmp)', 3),
(1264, '2010-08-13 17:16:41', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=3 labgrupa=', 1),
(1265, '2010-08-13 17:16:41', 4, 'greska kod attachmenta (zadaca z6, varijabla program je: c:/wamp/tmpphp455.tmp)', 3),
(1266, '2010-08-13 17:17:13', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=3 labgrupa=', 1),
(1267, '2010-08-13 17:17:13', 4, 'greska kod attachmenta (zadaca z6, varijabla program je: c:/wamp/tmpphp457.tmp)', 3),
(1268, '2010-08-13 17:17:39', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=3 labgrupa=', 1),
(1269, '2010-08-13 17:17:39', 4, 'poslana zadaca z6 zadatak 3 (attachment)', 2),
(1270, '2010-08-13 17:18:01', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=3 labgrupa=', 1),
(1271, '2010-08-13 17:18:01', 4, 'greska kod attachmenta (zadaca z6, varijabla program je: c:/wamp/tmpphp45B.tmp)', 3),
(1272, '2010-08-13 17:19:48', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=3 labgrupa=', 1),
(1273, '2010-08-13 17:19:48', 4, 'greska kod attachmenta (zadaca z6, varijabla program je: c:/wamp/tmpphp45E.tmp)', 3),
(1274, '2010-08-13 17:20:35', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=6 zadatak=3 labgrupa=', 1),
(1275, '2010-08-13 17:20:35', 4, 'greska kod attachmenta (zadaca z6, varijabla program je: c:/wamp/tmpphp460.tmp)', 3),
(1276, '2010-08-13 17:20:47', 4, '/zamger41/index.php?', 1),
(1277, '2010-08-13 17:20:49', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1278, '2010-08-13 17:20:54', 4, 'student/zadaca predmet=1 ag=1', 1),
(1279, '2010-08-13 17:21:25', 4, 'logout', 1),
(1280, '2010-08-13 17:21:32', 1, 'login', 1),
(1281, '2010-08-13 17:21:32', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1282, '2010-08-13 17:21:35', 1, 'saradnik/intro', 1),
(1283, '2010-08-13 17:21:36', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1284, '2010-08-13 17:21:38', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1285, '2010-08-13 17:21:48', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1286, '2010-08-13 17:22:01', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=13 naziv=Muris zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=22 sekunda=38', 1),
(1287, '2010-08-13 17:22:01', 1, 'azurirana zadaca z13', 2),
(1288, '2010-08-13 17:23:41', 1, '/zamger41/index.php?', 1),
(1289, '2010-08-13 17:23:43', 1, 'saradnik/intro', 1),
(1290, '2010-08-13 17:23:44', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1291, '2010-08-13 17:23:47', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1292, '2010-08-13 17:23:50', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1293, '2010-08-13 17:23:59', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=13 naziv=Muris zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=22 sekunda=38', 1),
(1294, '2010-08-13 17:23:59', 1, 'azurirana zadaca z13', 2),
(1295, '2010-08-13 17:24:06', 1, 'logout', 1),
(1296, '2010-08-13 17:24:12', 4, 'login', 1);
INSERT INTO `log` (`id`, `vrijeme`, `userid`, `dogadjaj`, `nivo`) VALUES 
(1297, '2010-08-13 17:24:12', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(1298, '2010-08-13 17:24:14', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1299, '2010-08-13 17:24:19', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1300, '2010-08-13 17:24:32', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1301, '2010-08-13 17:25:50', 4, 'logout', 1),
(1302, '2010-08-13 17:25:54', 1, 'login', 1),
(1303, '2010-08-13 17:25:54', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1304, '2010-08-13 17:25:57', 1, 'saradnik/intro', 1),
(1305, '2010-08-13 17:25:58', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1306, '2010-08-13 17:26:00', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1307, '2010-08-13 17:26:06', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1308, '2010-08-13 17:32:36', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1309, '2010-08-13 17:32:38', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1310, '2010-08-13 17:42:05', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=13 naziv=Muris zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=22 sekunda=38 attachment=1 dozvoljene_eks=docx', 1),
(1311, '2010-08-13 17:42:05', 1, 'SQL greska (nastavnikzadace.php : 339):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''where id=13'' at line 1', 3),
(1312, '2010-08-13 17:47:48', 1, '/zamger41/index.php?', 1),
(1313, '2010-08-13 17:47:52', 1, 'saradnik/intro', 1),
(1314, '2010-08-13 17:47:54', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1315, '2010-08-13 17:47:56', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1316, '2010-08-13 17:47:58', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1317, '2010-08-13 17:48:09', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=13 naziv=Muris zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=22 sekunda=38 attachment=1 dozvoljene_eks=docx', 1),
(1318, '2010-08-13 17:48:09', 1, 'azurirana zadaca z13', 2),
(1319, '2010-08-13 17:48:48', 1, 'logout', 1),
(1320, '2010-08-13 17:48:53', 4, 'login', 1),
(1321, '2010-08-13 17:48:53', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(1322, '2010-08-13 17:48:54', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1323, '2010-08-13 17:48:59', 4, 'student/zadaca predmet=1 ag=1 zadaca=13 zadatak=1', 1),
(1324, '2010-08-13 17:49:03', 4, 'logout', 1),
(1325, '2010-08-13 17:58:27', 1, 'login', 1),
(1326, '2010-08-13 17:58:27', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1327, '2010-08-13 17:58:30', 1, 'saradnik/intro', 1),
(1328, '2010-08-13 17:58:31', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1329, '2010-08-13 17:58:34', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1330, '2010-08-13 17:58:36', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1331, '2010-08-13 17:58:36', 1, 'SQL greska (nastavnikzadace.php : 373):Unknown column ''dozvoljenje_ekstenzije'' in ''field list''', 3),
(1332, '2010-08-13 17:59:14', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1333, '2010-08-13 17:59:46', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1334, '2010-08-13 18:02:17', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1335, '2010-08-13 18:02:19', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1336, '2010-08-13 18:02:21', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1337, '2010-08-13 18:02:32', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1338, '2010-08-13 18:04:56', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1339, '2010-08-13 18:04:58', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1340, '2010-08-13 18:05:21', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=jfkjdkf zadataka=2 bodova=2 day=17 month=8 year=2010 sat=18 minuta=04 sekunda=58 aktivna=on attachment=1 dozvoljene_eks=xls', 1),
(1341, '2010-08-13 18:05:21', 1, 'kreirana nova zadaca z14', 2),
(1342, '2010-08-13 18:08:48', 1, '/zamger41/index.php?', 1),
(1343, '2010-08-13 18:08:50', 1, 'saradnik/intro', 1),
(1344, '2010-08-13 18:08:51', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1345, '2010-08-13 18:08:55', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1346, '2010-08-13 18:08:57', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1347, '2010-08-13 18:09:10', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1348, '2010-08-13 18:09:44', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1349, '2010-08-13 18:10:10', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1350, '2010-08-13 18:11:45', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1351, '2010-08-13 18:12:37', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1352, '2010-08-13 18:12:54', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1353, '2010-08-13 18:17:59', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1354, '2010-08-13 18:20:48', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1355, '2010-08-13 18:25:58', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1356, '2010-08-13 18:26:29', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=sdsdsd zadataka=3 bodova=2 day=13 month=8 year=2010 sat=18 minuta=25 sekunda=58', 1),
(1357, '2010-08-13 18:26:29', 1, 'kreirana nova zadaca z15', 2),
(1358, '2010-08-13 18:31:59', 1, '/zamger41/index.php?', 1),
(1359, '2010-08-13 18:32:01', 1, 'saradnik/intro', 1),
(1360, '2010-08-13 18:32:04', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1361, '2010-08-13 18:32:07', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1362, '2010-08-13 18:32:33', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=xxxx zadataka=2 bodova=2 day=13 month=8 year=2010 sat=18 minuta=32 sekunda=07 attachment=1 dozvoljene_eks=docx', 1),
(1363, '2010-08-13 18:32:33', 1, 'kreirana nova zadaca z16', 2),
(1364, '2010-08-13 18:36:30', 1, '/zamger41/index.php?', 1),
(1365, '2010-08-13 18:36:33', 1, 'saradnik/intro', 1),
(1366, '2010-08-13 18:36:35', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1367, '2010-08-13 18:36:37', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1368, '2010-08-13 18:37:04', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=yyy zadataka=2 bodova=2 day=13 month=8 year=2010 sat=18 minuta=36 sekunda=37 attachment=1 dozvoljene_eks=doc', 1),
(1369, '2010-08-13 18:37:04', 1, 'kreirana nova zadaca z17', 2),
(1370, '2010-08-13 18:38:06', 1, '/zamger41/index.php?', 1),
(1371, '2010-08-13 18:38:08', 1, 'saradnik/intro', 1),
(1372, '2010-08-13 18:38:10', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1373, '2010-08-13 18:38:12', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1374, '2010-08-13 18:38:33', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=ppp zadataka=2 bodova=1 day=13 month=8 year=2010 sat=18 minuta=38 sekunda=12 attachment=1 dozvoljene_eks=doc', 1),
(1375, '2010-08-13 18:38:33', 1, 'kreirana nova zadaca z18', 2),
(1376, '2010-08-13 18:39:34', 1, 'logout', 1),
(1377, '2010-08-13 18:39:43', 4, 'login', 1),
(1378, '2010-08-13 18:39:43', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(1379, '2010-08-13 18:39:45', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1380, '2010-08-13 18:39:53', 4, 'student/zadaca predmet=1 ag=1 zadaca=18 zadatak=1', 1),
(1381, '2010-08-13 18:40:06', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=18 zadatak=1 labgrupa=', 1),
(1382, '2010-08-13 18:40:06', 4, 'isteklo vrijeme za slanje zadaće z18', 3),
(1383, '2010-08-13 18:41:56', 4, '/zamger41/index.php?', 1),
(1384, '2010-08-13 18:42:05', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1385, '2010-08-13 18:42:09', 4, 'student/zadaca predmet=1 ag=1 zadaca=18 zadatak=1', 1),
(1386, '2010-08-13 18:42:17', 4, 'student/zadaca predmet=1 ag=1 zadaca=18 zadatak=1', 1),
(1387, '2010-08-13 18:42:41', 1, 'login', 1),
(1388, '2010-08-13 18:42:41', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1389, '2010-08-13 18:42:45', 1, 'saradnik/intro', 1),
(1390, '2010-08-13 18:42:46', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1391, '2010-08-13 18:42:48', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1392, '2010-08-13 18:42:50', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1393, '2010-08-13 18:42:58', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=18 naziv=ppp zadataka=2 bodova=1 day=13 month=8 year=2010 sat=20 minuta=38 sekunda=12 attachment=1 dozvoljene_eks=doc', 1),
(1394, '2010-08-13 18:42:58', 1, 'azurirana zadaca z18', 2),
(1395, '2010-08-13 18:43:05', 4, 'student/zadaca predmet=1 ag=1 zadaca=18 zadatak=1', 1),
(1396, '2010-08-13 18:43:50', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=18 zadatak=1 labgrupa=', 1),
(1397, '2010-08-13 18:43:50', 4, 'greska kod attachmenta (zadaca z18, varijabla program je: c:/wamp/tmpphp5A4.tmp)', 3),
(1398, '2010-08-13 18:44:07', 4, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=18 zadatak=1 labgrupa=', 1),
(1399, '2010-08-13 18:44:07', 4, 'poslana zadaca z18 zadatak 1 (attachment)', 2),
(1400, '2010-08-13 18:50:04', 4, '/zamger41/index.php?', 1),
(1401, '2010-08-13 18:50:11', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1402, '2010-08-13 18:50:24', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=jkdjks zadataka=1 bodova=1 day=13 month=8 year=2010 sat=20 minuta=50 sekunda=11', 1),
(1403, '2010-08-13 18:50:24', 1, 'kreirana nova zadaca z19', 2),
(1404, '2010-08-13 18:50:55', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=19 naziv=jkdjks111 zadataka=1 bodova=1 day=13 month=8 year=2010 sat=20 minuta=50 sekunda=11 attachment=1 dozvoljene_eks=docx', 1),
(1405, '2010-08-13 18:50:55', 1, 'azurirana zadaca z19', 2),
(1406, '2010-08-13 18:52:26', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1407, '2010-08-13 18:52:48', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=aaaa zadataka=1 bodova=1 day=13 month=8 year=2010 sat=21 minuta=52 sekunda=26 attachment=1 dozvoljene_eks=docx', 1),
(1408, '2010-08-13 18:52:48', 1, 'kreirana nova zadaca z20', 2),
(1409, '2010-08-13 18:55:01', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1410, '2010-08-13 18:55:05', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1411, '2010-08-13 18:55:33', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=vvvv zadataka=2 bodova=3 day=13 month=8 year=2010 sat=22 minuta=55 sekunda=05 attachment=1 dozvoljene_eks=docx', 1),
(1412, '2010-08-13 18:55:33', 1, 'kreirana nova zadaca z21', 2),
(1413, '2010-08-13 18:56:22', 1, '/zamger41/index.php?', 1),
(1414, '2010-08-13 18:56:25', 1, 'saradnik/intro', 1),
(1415, '2010-08-13 18:56:26', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1416, '2010-08-13 18:56:29', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1417, '2010-08-13 18:56:54', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=mmmm zadataka=2 bodova=2 day=13 month=8 year=2010 sat=21 minuta=56 sekunda=29 attachment=1 dozvoljene_eks=doc', 1),
(1418, '2010-08-13 18:56:54', 1, 'kreirana nova zadaca z22', 2),
(1419, '2010-08-13 18:59:10', 1, '/zamger41/index.php?', 1),
(1420, '2010-08-13 18:59:12', 1, 'saradnik/intro', 1),
(1421, '2010-08-13 18:59:13', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1422, '2010-08-13 18:59:15', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1423, '2010-08-13 18:59:26', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=xxxxxxxx zadataka=2 bodova=1 day=13 month=8 year=2010 sat=21 minuta=59 sekunda=15', 1),
(1424, '2010-08-13 18:59:26', 1, 'kreirana nova zadaca z23', 2),
(1425, '2010-08-13 18:59:43', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=23 naziv=xxxxxxx323 zadataka=2 bodova=1 day=13 month=8 year=2010 sat=21 minuta=59 sekunda=15 attachment=1 dozvoljene_eks=docx', 1),
(1426, '2010-08-13 18:59:43', 1, 'azurirana zadaca z23', 2),
(1427, '2010-08-13 19:01:14', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=23 naziv=xxxxxxx323 zadataka=2 bodova=1 day=13 month=8 year=2010 sat=21 minuta=59 sekunda=15 attachment=1 dozvoljene_eks=docx', 1),
(1428, '2010-08-13 19:01:34', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=mmlcm zadataka=3 bodova=3 day=13 month=8 year=2010 sat=19 minuta=01 sekunda=14 attachment=1 dozvoljene_eks=docx', 1),
(1429, '2010-08-13 19:01:34', 1, 'kreirana nova zadaca z24', 2),
(1430, '2010-08-13 19:07:50', 1, '/zamger41/index.php?', 1),
(1431, '2010-08-13 19:07:52', 1, 'saradnik/intro', 1),
(1432, '2010-08-13 19:07:54', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1433, '2010-08-13 19:07:56', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1434, '2010-08-13 19:08:02', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=klasla zadataka=2 bodova=1 day=13 month=8 year=2010 sat=19 minuta=07 sekunda=56', 1),
(1435, '2010-08-13 19:08:02', 1, 'kreirana nova zadaca z25', 2),
(1436, '2010-08-13 19:08:44', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=25 naziv=klasla67 zadataka=2 bodova=1 day=13 month=8 year=2010 sat=19 minuta=07 sekunda=56 attachment=1 dozvoljene_eks=xlsx', 1),
(1437, '2010-08-13 19:08:44', 1, 'azurirana zadaca z25', 2),
(1438, '2010-08-13 19:09:08', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=25 naziv=klasla67 zadataka=2 bodova=1 day=13 month=8 year=2010 sat=19 minuta=07 sekunda=56 attachment=1 dozvoljene_eks=xlsx', 1),
(1439, '2010-08-13 19:09:28', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=24 naziv=mmlcm zadataka=3 bodova=3 day=13 month=8 year=2010 sat=19 minuta=01 sekunda=14 attachment=1 dozvoljene_eks=docx', 1),
(1440, '2010-08-13 19:09:28', 1, 'azurirana zadaca z24', 2),
(1441, '2010-08-13 19:10:34', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1442, '2010-08-13 19:10:57', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=llll zadataka=3 bodova=3 day=13 month=8 year=2010 sat=20 minuta=10 sekunda=34 attachment=1 dozvoljene_eks=docx', 1),
(1443, '2010-08-13 19:10:57', 1, 'kreirana nova zadaca z26', 2),
(1444, '2010-08-13 19:11:11', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1445, '2010-08-13 19:11:15', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1446, '2010-08-13 19:13:53', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1447, '2010-08-13 19:14:07', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=23 naziv=xxxxxxx323 zadataka=2 bodova=1 day=13 month=8 year=2010 sat=21 minuta=59 sekunda=15 attachment=1 dozvoljene_eks=docx', 1),
(1448, '2010-08-13 19:16:15', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1449, '2010-08-13 19:16:18', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1450, '2010-08-13 19:22:11', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1451, '2010-08-13 19:23:08', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1452, '2010-08-13 19:24:19', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1453, '2010-08-13 19:24:24', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1454, '2010-08-14 10:01:40', 5, 'login', 1),
(1455, '2010-08-14 10:01:40', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(1456, '2010-08-14 10:01:45', 5, 'saradnik/grupa id=2', 1),
(1457, '2010-08-14 10:02:03', 5, 'saradnik/zadaca student=2 zadaca=1 zadatak=1', 1),
(1458, '2010-08-14 10:02:13', 5, 'saradnik/zadaca student=2 zadaca=1 zadatak=1 akcija=slanje status=5 bodova=2 komentar=', 1),
(1459, '2010-08-14 10:02:13', 5, 'izmjena zadace (student u2 zadaca z1 zadatak 1)', 2),
(1460, '2010-08-14 10:02:16', 5, 'saradnik/grupa id=2', 1),
(1461, '2010-08-14 10:02:26', 5, 'saradnik/zadaca student=4 zadaca=3 zadatak=1', 1),
(1462, '2010-08-14 10:02:32', 5, 'saradnik/zadaca student=4 zadaca=3 zadatak=1 akcija=izvrsi stdin=', 1),
(1463, '2010-08-14 10:02:32', 5, 'nije uspjelo kreiranje datoteka', 3),
(1464, '2010-08-14 10:02:36', 5, 'saradnik/zadaca student=4 zadaca=6 zadatak=2', 1),
(1465, '2010-08-14 10:03:06', 5, 'logout', 1),
(1466, '2010-08-14 10:03:14', 2, 'login', 1),
(1467, '2010-08-14 10:03:14', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1468, '2010-08-14 10:03:31', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1469, '2010-08-14 10:03:46', 2, 'student/pdf zadaca=1', 1),
(1470, '2010-08-14 10:04:06', 2, 'student/pdf zadaca=26', 1),
(1471, '2010-08-14 10:04:30', 2, 'student/pdf zadaca=2', 1),
(1472, '2010-08-14 10:04:55', 2, 'logout', 1),
(1473, '2010-08-14 10:20:54', 1, 'login', 1),
(1474, '2010-08-14 10:20:54', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1475, '2010-08-14 10:20:57', 1, 'saradnik/intro', 1),
(1476, '2010-08-14 10:20:58', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1477, '2010-08-14 10:21:06', 1, 'nastavnik/obavjestenja predmet=1 ag=1', 1),
(1478, '2010-08-14 10:21:08', 1, 'nastavnik/grupe predmet=1 ag=1', 1),
(1479, '2010-08-14 10:21:09', 1, 'nastavnik/grupe predmet=1 ag=1', 1),
(1480, '2010-08-14 10:21:20', 1, 'nastavnik/ispiti predmet=1 ag=1', 1),
(1481, '2010-08-14 10:21:20', 1, 'nastavnik/ocjena predmet=1 ag=1', 1),
(1482, '2010-08-14 10:21:24', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1483, '2010-08-14 10:21:27', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1484, '2010-08-14 10:21:31', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=26 naziv=llll zadataka=3 bodova=3 day=13 month=8 year=2010 sat=20 minuta=10 sekunda=34 attachment=1 dozvoljene_eks=docx brisanje= Obriši ', 1),
(1485, '2010-08-14 10:21:33', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=26 naziv=llll zadataka=3 bodova=3 day=13 month=8 year=2010 sat=20 minuta=10 sekunda=34 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1486, '2010-08-14 10:21:33', 1, 'obrisana zadaca 26 sa predmeta pp1', 4),
(1487, '2010-08-14 10:21:33', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1488, '2010-08-14 10:21:43', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1489, '2010-08-14 10:21:45', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1490, '2010-08-14 10:22:55', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaća 5 zadataka=02 bodova=1 day=14 month=9 year=2010 sat=10 minuta=21 sekunda=45', 1),
(1491, '2010-08-14 10:22:55', 1, 'kreirana nova zadaca z27', 2),
(1492, '2010-08-14 10:24:03', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaća 5 zadataka=02 bodova=1 day=14 month=9 year=2010 sat=10 minuta=21 sekunda=45', 1),
(1493, '2010-08-14 10:24:05', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=27 naziv=Zadaća 5 zadataka=2 bodova=1 day=14 month=9 year=2010 sat=10 minuta=21 sekunda=45 brisanje= Obriši ', 1),
(1494, '2010-08-14 10:24:06', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=27 naziv=Zadaća 5 zadataka=2 bodova=1 day=14 month=9 year=2010 sat=10 minuta=21 sekunda=45 brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1495, '2010-08-14 10:24:06', 1, 'obrisana zadaca 27 sa predmeta pp1', 4),
(1496, '2010-08-14 10:24:06', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1497, '2010-08-14 10:25:50', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaća 5 zadataka=2 bodova=2 day=14 month=9 year=2010 sat=10 minuta=24 sekunda=06 attachment=1 dozvoljene_eks=doc,docx,zip', 1),
(1498, '2010-08-14 10:25:50', 1, 'kreirana nova zadaca z28', 2),
(1499, '2010-08-14 10:26:09', 1, 'logout', 1),
(1500, '2010-08-14 10:26:19', 2, 'login', 1),
(1501, '2010-08-14 10:26:19', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1502, '2010-08-14 10:26:38', 2, 'logout', 1),
(1503, '2010-08-14 10:26:47', 1, 'login', 1),
(1504, '2010-08-14 10:26:47', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1505, '2010-08-14 10:26:49', 1, 'saradnik/intro', 1),
(1506, '2010-08-14 10:26:51', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1507, '2010-08-14 10:26:53', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1508, '2010-08-14 10:26:55', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1509, '2010-08-14 10:27:00', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=28 naziv=Zadaća 5 zadataka=2 bodova=2 day=14 month=9 year=2010 sat=10 minuta=24 sekunda=06 aktivna=on attachment=1 dozvoljene_eks=doc,docx,zip', 1),
(1510, '2010-08-14 10:27:00', 1, 'azurirana zadaca z28', 2),
(1511, '2010-08-14 10:27:05', 1, 'logout', 1),
(1512, '2010-08-14 10:27:14', 2, 'login', 1),
(1513, '2010-08-14 10:27:14', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1514, '2010-08-14 10:27:16', 2, 'student/zadaca zadaca=28 predmet=1 ag=1', 1),
(1515, '2010-08-14 10:27:32', 2, 'student/zadaca predmet=1 ag=1 zadaca=28 zadatak=1', 1),
(1516, '2010-08-14 10:27:49', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=28 zadatak=1 labgrupa=', 1),
(1517, '2010-08-14 10:27:49', 2, 'greska kod attachmenta (zadaca z28, varijabla program je: c:/wamp/tmpphp6159.tmp)', 3),
(1518, '2010-08-14 10:28:20', 2, 'student/zadaca predmet=1 ag=1 zadaca=28 zadatak=1', 1),
(1519, '2010-08-14 10:29:13', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=28 zadatak=1 labgrupa=', 1),
(1520, '2010-08-14 10:29:13', 2, 'poslana zadaca z28 zadatak 1 (attachment)', 2),
(1521, '2010-08-14 10:29:18', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1522, '2010-08-14 10:29:32', 2, 'student/pdf zadaca=28', 1),
(1523, '2010-08-14 10:59:50', 2, 'student/zadaca predmet=1 ag=1', 1),
(1524, '2010-08-14 11:29:48', 2, 'student/zadaca predmet=1 ag=1', 1),
(1525, '2010-08-14 11:34:18', 2, 'student/zadaca predmet=1 ag=1 zadaca=28 zadatak=1', 1),
(1526, '2010-08-14 11:35:37', 2, 'student/zadaca predmet=1 ag=1 zadaca=28 zadatak=1', 1),
(1527, '2010-08-14 11:36:06', 2, 'student/zadaca predmet=1 ag=1 zadaca=28 zadatak=1', 1),
(1528, '2010-08-14 11:41:31', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=28 zadatak=1 labgrupa=', 1),
(1529, '2010-08-14 11:41:44', 2, 'student/zadaca predmet=1 ag=1 zadaca=28 zadatak=1', 1),
(1530, '2010-08-14 11:42:00', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=28 zadatak=1 labgrupa=', 1),
(1531, '2010-08-14 11:42:11', 2, 'student/zadaca predmet=1 ag=1 zadaca=28 zadatak=1', 1),
(1532, '2010-08-14 12:09:16', 2, 'student/zadaca predmet=1 ag=1 zadaca=28 zadatak=1', 1),
(1533, '2010-08-14 12:09:46', 2, 'student/zadaca predmet=1 ag=1 zadaca=28 zadatak=1', 1),
(1534, '2010-08-14 12:33:39', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1535, '2010-08-14 12:33:43', 2, 'student/pdf zadaca=28', 1),
(1536, '2010-08-14 12:37:17', 2, 'student/pdf zadaca=28', 1),
(1537, '2010-08-14 12:39:15', 2, 'logout', 1),
(1538, '2010-08-14 12:39:22', 1, 'login', 1),
(1539, '2010-08-14 12:39:22', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1540, '2010-08-14 12:39:23', 1, 'saradnik/intro', 1),
(1541, '2010-08-14 12:39:26', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1542, '2010-08-14 12:39:28', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1543, '2010-08-14 12:41:15', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1544, '2010-08-14 12:52:51', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaca 6 zadataka=2 bodova=2 day=14 month=10 year=2010 sat=12 minuta=41 sekunda=15 aktivna=on attachment=1 dozvoljene_eks=php', 1),
(1545, '2010-08-14 12:52:51', 1, 'kreirana nova zadaca z29', 2),
(1546, '2010-08-14 12:52:55', 1, 'logout', 1),
(1547, '2010-08-14 12:53:06', 2, 'login', 1),
(1548, '2010-08-14 12:53:06', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1549, '2010-08-14 12:53:09', 2, 'student/zadaca zadaca=29 predmet=1 ag=1', 1),
(1550, '2010-08-14 12:53:25', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=29 zadatak=2 labgrupa=', 1),
(1551, '2010-08-14 12:53:25', 2, 'poslana zadaca z29 zadatak 2 (attachment)', 2),
(1552, '2010-08-14 12:53:29', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1553, '2010-08-14 12:53:37', 2, 'student/pdf zadaca=29', 1),
(1554, '2010-08-14 12:55:11', 2, 'student/pdf zadaca=28', 1),
(1555, '2010-08-14 12:57:50', 2, 'student/zadaca predmet=1 ag=1 zadaca=29 zadatak=2', 1),
(1556, '2010-08-14 12:57:54', 2, 'common/attachment zadaca=29 zadatak=2', 1),
(1557, '2010-08-14 12:58:27', 2, 'student/zadaca predmet=1 ag=1 zadaca=29 zadatak=2', 1),
(1558, '2010-08-14 12:59:07', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1559, '2010-08-14 12:59:10', 2, 'student/pdf zadaca=1', 1),
(1560, '2010-08-14 12:59:24', 2, 'student/pdf zadaca=28', 1),
(1561, '2010-08-14 12:59:38', 2, 'student/zadaca predmet=1 ag=1 zadaca=29 zadatak=1', 1),
(1562, '2010-08-14 12:59:47', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=29 zadatak=1 labgrupa=', 1),
(1563, '2010-08-14 12:59:47', 2, 'poslana zadaca z29 zadatak 1 (attachment)', 2),
(1564, '2010-08-14 12:59:50', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1565, '2010-08-14 12:59:53', 2, 'student/pdf zadaca=29', 1),
(1566, '2010-08-14 13:01:09', 2, 'student/pdf zadaca=29', 1),
(1567, '2010-08-14 13:03:02', 2, 'student/pdf zadaca=29', 1),
(1568, '2010-08-14 13:08:40', 2, 'student/pdf zadaca=29', 1),
(1569, '2010-08-14 13:08:50', 2, 'student/pdf zadaca=29', 1),
(1570, '2010-08-14 13:11:14', 2, 'student/pdf zadaca=29', 1),
(1571, '2010-08-14 13:11:58', 2, 'student/zadaca predmet=1 ag=1', 1),
(1572, '2010-08-14 13:12:12', 2, 'student/zadaca predmet=1 ag=1 zadaca=29 zadatak=2', 1),
(1573, '2010-08-14 13:12:23', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=29 zadatak=2 labgrupa=', 1),
(1574, '2010-08-14 13:12:23', 2, 'greska kod attachmenta (zadaca z29, varijabla program je: c:/wamp/tmpphpBB5.tmp)', 3),
(1575, '2010-08-14 13:16:53', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1576, '2010-08-14 13:16:56', 2, 'student/pdf zadaca=29', 1),
(1577, '2010-08-14 13:18:45', 2, 'student/pdf zadaca=29', 1),
(1578, '2010-08-14 13:19:54', 2, 'student/pdf zadaca=29', 1),
(1579, '2010-08-14 13:24:34', 2, 'student/pdf zadaca=29', 1),
(1580, '2010-08-14 13:27:15', 2, 'logout', 1),
(1581, '2010-08-14 13:27:21', 1, 'login', 1),
(1582, '2010-08-14 13:27:22', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1583, '2010-08-14 13:27:23', 1, 'saradnik/intro', 1),
(1584, '2010-08-14 13:27:25', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1585, '2010-08-14 13:27:27', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1586, '2010-08-14 13:28:27', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaca 7 zadataka=2 bodova=1 day=14 month=9 year=2010 sat=13 minuta=27 sekunda=27 attachment=1 dozvoljene_eks=php,c', 1),
(1587, '2010-08-14 13:28:27', 1, 'kreirana nova zadaca z30', 2),
(1588, '2010-08-14 13:28:30', 1, 'logout', 1),
(1589, '2010-08-14 13:28:38', 0, 'index.php greska: Nepoznat korisnik jasmn ', 3),
(1590, '2010-08-14 13:28:47', 2, 'login', 1),
(1591, '2010-08-14 13:28:47', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1592, '2010-08-14 13:28:55', 2, 'logout', 1),
(1593, '2010-08-14 13:29:01', 1, 'login', 1),
(1594, '2010-08-14 13:29:01', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1595, '2010-08-14 13:29:02', 1, 'saradnik/intro', 1),
(1596, '2010-08-14 13:29:04', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1597, '2010-08-14 13:29:09', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1598, '2010-08-14 13:29:12', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1599, '2010-08-14 13:29:16', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=30 naziv=Zadaca 7 zadataka=2 bodova=1 day=14 month=9 year=2010 sat=13 minuta=27 sekunda=27 aktivna=on attachment=1 dozvoljene_eks=php,c', 1),
(1600, '2010-08-14 13:29:16', 1, 'azurirana zadaca z30', 2),
(1601, '2010-08-14 13:29:37', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=30 naziv=Zadaca 7 zadataka=2 bodova=1 day=14 month=9 year=2010 sat=13 minuta=27 sekunda=27 aktivna=on attachment=1 dozvoljene_eks=php,c', 1),
(1602, '2010-08-14 13:29:37', 1, 'azurirana zadaca z30', 2),
(1603, '2010-08-14 13:29:42', 1, 'logout', 1),
(1604, '2010-08-14 13:29:54', 2, 'login', 1),
(1605, '2010-08-14 13:29:54', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1606, '2010-08-14 13:30:02', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1607, '2010-08-14 13:30:35', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=1', 1),
(1608, '2010-08-14 13:30:58', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=30 zadatak=1 labgrupa=', 1),
(1609, '2010-08-14 13:30:58', 2, 'poslana zadaca z30 zadatak 1 (attachment)', 2),
(1610, '2010-08-14 13:31:00', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1611, '2010-08-14 13:31:04', 2, 'student/pdf zadaca=30', 1),
(1612, '2010-08-14 13:31:47', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=2', 1),
(1613, '2010-08-14 13:32:12', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=30 zadatak=2 labgrupa=', 1),
(1614, '2010-08-14 13:32:12', 2, 'poslana zadaca z30 zadatak 2 (attachment)', 2),
(1615, '2010-08-14 13:32:14', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1616, '2010-08-14 13:32:17', 2, 'student/pdf zadaca=30', 1),
(1617, '2010-08-14 13:33:10', 2, 'logout', 1),
(1618, '2010-08-14 13:33:18', 0, 'index.php greska: Nepoznat korisnik huseFATKIC ', 3),
(1619, '2010-08-14 13:33:31', 5, 'login', 1),
(1620, '2010-08-14 13:33:31', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(1621, '2010-08-14 13:33:33', 5, 'saradnik/grupa id=2', 1),
(1622, '2010-08-14 13:34:08', 5, 'saradnik/zadaca student=2 zadaca=29 zadatak=1', 1),
(1623, '2010-08-14 13:34:24', 5, 'saradnik/zadaca student=2 zadaca=29 zadatak=1 akcija=slanje status=5 bodova=1 komentar=Dobro je uradjeno', 1),
(1624, '2010-08-14 13:34:24', 5, 'izmjena zadace (student u2 zadaca z29 zadatak 1)', 2),
(1625, '2010-08-14 13:34:40', 5, 'saradnik/zadaca student=2 zadaca=30 zadatak=1', 1),
(1626, '2010-08-14 13:34:44', 5, 'saradnik/zadaca student=2 zadaca=29 zadatak=2', 1),
(1627, '2010-08-14 13:34:52', 5, 'saradnik/zadaca student=2 zadaca=29 zadatak=2 akcija=slanje status=5 bodova=1 komentar=', 1),
(1628, '2010-08-14 13:34:52', 5, 'izmjena zadace (student u2 zadaca z29 zadatak 2)', 2),
(1629, '2010-08-14 13:34:54', 5, 'saradnik/zadaca student=2 zadaca=30 zadatak=1', 1),
(1630, '2010-08-14 13:34:59', 5, 'saradnik/zadaca student=2 zadaca=30 zadatak=1 akcija=slanje status=5 bodova=1 komentar=', 1),
(1631, '2010-08-14 13:34:59', 5, 'izmjena zadace (student u2 zadaca z30 zadatak 1)', 2),
(1632, '2010-08-14 13:35:02', 5, 'saradnik/zadaca student=2 zadaca=30 zadatak=2', 1),
(1633, '2010-08-14 13:35:21', 5, 'saradnik/zadaca student=2 zadaca=30 zadatak=2 akcija=slanje status=4 bodova=0.5 komentar=', 1),
(1634, '2010-08-14 13:35:21', 5, 'izmjena zadace (student u2 zadaca z30 zadatak 2)', 2),
(1635, '2010-08-14 13:35:25', 5, 'saradnik/grupa id=2', 1),
(1636, '2010-08-14 13:35:40', 5, 'logout', 1),
(1637, '2010-08-14 13:35:48', 1, 'login', 1),
(1638, '2010-08-14 13:35:48', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1639, '2010-08-14 13:35:50', 1, 'saradnik/intro', 1),
(1640, '2010-08-14 13:35:51', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1641, '2010-08-14 13:35:53', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1642, '2010-08-14 13:35:55', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1643, '2010-08-14 13:35:59', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=14 naziv=jfkjdkf zadataka=2 bodova=2 day=17 month=8 year=2010 sat=18 minuta=04 sekunda=58 aktivna=on attachment=1 dozvoljene_eks=xls brisanje= Obriši ', 1),
(1644, '2010-08-14 13:36:00', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=14 naziv=jfkjdkf zadataka=2 bodova=2 day=17 month=8 year=2010 sat=18 minuta=04 sekunda=58 aktivna=on attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1645, '2010-08-14 13:36:00', 1, 'obrisana zadaca 14 sa predmeta pp1', 4),
(1646, '2010-08-14 13:36:00', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1647, '2010-08-14 13:36:02', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1648, '2010-08-14 13:36:05', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1649, '2010-08-14 13:36:07', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=23 naziv=xxxxxxx323 zadataka=2 bodova=1 day=13 month=8 year=2010 sat=21 minuta=59 sekunda=15 attachment=1 dozvoljene_eks=docx brisanje= Obriši ', 1),
(1650, '2010-08-14 13:36:08', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=23 naziv=xxxxxxx323 zadataka=2 bodova=1 day=13 month=8 year=2010 sat=21 minuta=59 sekunda=15 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1651, '2010-08-14 13:36:08', 1, 'obrisana zadaca 23 sa predmeta pp1', 4),
(1652, '2010-08-14 13:36:08', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1653, '2010-08-14 13:36:10', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1654, '2010-08-14 13:36:13', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1655, '2010-08-14 13:36:15', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=18 naziv=ppp zadataka=2 bodova=1 day=13 month=8 year=2010 sat=20 minuta=38 sekunda=12 attachment=1 dozvoljene_eks=doc brisanje= Obriši ', 1),
(1656, '2010-08-14 13:36:17', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=18 naziv=ppp zadataka=2 bodova=1 day=13 month=8 year=2010 sat=20 minuta=38 sekunda=12 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1657, '2010-08-14 13:36:17', 1, 'obrisana zadaca 18 sa predmeta pp1', 4),
(1658, '2010-08-14 13:36:17', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1659, '2010-08-14 13:36:19', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1660, '2010-08-14 13:36:23', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=6 naziv=Test chk zadataka=3 bodova=2 day=18 month=8 year=2010 sat=16 minuta=06 sekunda=33 attachment=1 dozvoljene_eks=doc,docx,pdf brisanje= Obriši ', 1),
(1661, '2010-08-14 13:36:24', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=6 naziv=Test chk zadataka=3 bodova=2 day=18 month=8 year=2010 sat=16 minuta=06 sekunda=33 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1662, '2010-08-14 13:36:24', 1, 'obrisana zadaca 6 sa predmeta pp1', 4),
(1663, '2010-08-14 13:36:24', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1664, '2010-08-14 13:36:25', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1665, '2010-08-14 13:36:27', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=11 naziv=sfsdfsdf2 zadataka=3 bodova=3 day=13 month=8 year=2010 sat=16 minuta=18 sekunda=45 attachment=1 brisanje= Obriši ', 1),
(1666, '2010-08-14 13:36:29', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=11 naziv=sfsdfsdf2 zadataka=3 bodova=3 day=13 month=8 year=2010 sat=16 minuta=18 sekunda=45 attachment=1 brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1667, '2010-08-14 13:36:29', 1, 'obrisana zadaca 11 sa predmeta pp1', 4),
(1668, '2010-08-14 13:36:29', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1669, '2010-08-14 13:36:30', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1670, '2010-08-14 13:36:32', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=16 naziv=xxxx zadataka=2 bodova=2 day=13 month=8 year=2010 sat=18 minuta=32 sekunda=07 attachment=1 dozvoljene_eks=docx brisanje= Obriši ', 1),
(1671, '2010-08-14 13:36:33', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=16 naziv=xxxx zadataka=2 bodova=2 day=13 month=8 year=2010 sat=18 minuta=32 sekunda=07 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1672, '2010-08-14 13:36:33', 1, 'obrisana zadaca 16 sa predmeta pp1', 4),
(1673, '2010-08-14 13:36:33', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1674, '2010-08-14 13:36:35', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1675, '2010-08-14 13:36:37', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=17 naziv=yyy zadataka=2 bodova=2 day=13 month=8 year=2010 sat=18 minuta=36 sekunda=37 attachment=1 dozvoljene_eks=doc brisanje= Obriši ', 1),
(1676, '2010-08-14 13:36:38', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=17 naziv=yyy zadataka=2 bodova=2 day=13 month=8 year=2010 sat=18 minuta=36 sekunda=37 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1677, '2010-08-14 13:36:38', 1, 'obrisana zadaca 17 sa predmeta pp1', 4),
(1678, '2010-08-14 13:36:38', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1679, '2010-08-14 13:36:39', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1680, '2010-08-14 13:36:41', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=10 naziv=ddasd zadataka=2 bodova=2 day=13 month=8 year=2010 sat=16 minuta=17 sekunda=12 attachment=1 dozvoljene_eks=doc brisanje= Obriši ', 1),
(1681, '2010-08-14 13:36:43', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=10 naziv=ddasd zadataka=2 bodova=2 day=13 month=8 year=2010 sat=16 minuta=17 sekunda=12 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1682, '2010-08-14 13:36:43', 1, 'obrisana zadaca 10 sa predmeta pp1', 4),
(1683, '2010-08-14 13:36:43', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1684, '2010-08-14 13:36:44', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1685, '2010-08-14 13:36:46', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=15 naziv=sdsdsd zadataka=3 bodova=2 day=13 month=8 year=2010 sat=18 minuta=25 sekunda=58 brisanje= Obriši ', 1),
(1686, '2010-08-14 13:36:48', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=15 naziv=sdsdsd zadataka=3 bodova=2 day=13 month=8 year=2010 sat=18 minuta=25 sekunda=58 brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1687, '2010-08-14 13:36:48', 1, 'obrisana zadaca 15 sa predmeta pp1', 4),
(1688, '2010-08-14 13:36:48', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1689, '2010-08-14 13:36:49', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1690, '2010-08-14 13:36:51', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=12 naziv=eeeee zadataka=2 bodova=3 day=13 month=8 year=2010 sat=16 minuta=19 sekunda=59 attachment=1 dozvoljene_eks=doc brisanje= Obriši ', 1),
(1691, '2010-08-14 13:36:53', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=12 naziv=eeeee zadataka=2 bodova=3 day=13 month=8 year=2010 sat=16 minuta=19 sekunda=59 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1692, '2010-08-14 13:36:53', 1, 'obrisana zadaca 12 sa predmeta pp1', 4),
(1693, '2010-08-14 13:36:53', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1694, '2010-08-14 13:36:54', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1695, '2010-08-14 13:36:56', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=19 naziv=jkdjks111 zadataka=1 bodova=1 day=13 month=8 year=2010 sat=20 minuta=50 sekunda=11 attachment=1 dozvoljene_eks=docx brisanje= Obriši ', 1),
(1696, '2010-08-14 13:36:57', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=19 naziv=jkdjks111 zadataka=1 bodova=1 day=13 month=8 year=2010 sat=20 minuta=50 sekunda=11 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1697, '2010-08-14 13:36:57', 1, 'obrisana zadaca 19 sa predmeta pp1', 4),
(1698, '2010-08-14 13:36:57', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1699, '2010-08-14 13:37:01', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1700, '2010-08-14 13:37:04', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=24 naziv=mmlcm zadataka=3 bodova=3 day=13 month=8 year=2010 sat=19 minuta=01 sekunda=14 attachment=1 dozvoljene_eks=docx brisanje= Obriši ', 1),
(1701, '2010-08-14 13:37:05', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=24 naziv=mmlcm zadataka=3 bodova=3 day=13 month=8 year=2010 sat=19 minuta=01 sekunda=14 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1702, '2010-08-14 13:37:05', 1, 'obrisana zadaca 24 sa predmeta pp1', 4),
(1703, '2010-08-14 13:37:05', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1704, '2010-08-14 13:37:08', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1705, '2010-08-14 13:37:12', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=21 naziv=vvvv zadataka=2 bodova=3 day=13 month=8 year=2010 sat=22 minuta=55 sekunda=05 attachment=1 dozvoljene_eks=docx brisanje= Obriši ', 1),
(1706, '2010-08-14 13:37:13', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=21 naziv=vvvv zadataka=2 bodova=3 day=13 month=8 year=2010 sat=22 minuta=55 sekunda=05 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1707, '2010-08-14 13:37:13', 1, 'obrisana zadaca 21 sa predmeta pp1', 4),
(1708, '2010-08-14 13:37:13', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1709, '2010-08-14 13:37:20', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1710, '2010-08-14 13:37:22', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=22 naziv=mmmm zadataka=2 bodova=2 day=13 month=8 year=2010 sat=21 minuta=56 sekunda=29 attachment=1 dozvoljene_eks=doc brisanje= Obriši ', 1),
(1711, '2010-08-14 13:37:24', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=22 naziv=mmmm zadataka=2 bodova=2 day=13 month=8 year=2010 sat=21 minuta=56 sekunda=29 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1712, '2010-08-14 13:37:24', 1, 'obrisana zadaca 22 sa predmeta pp1', 4),
(1713, '2010-08-14 13:37:24', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1714, '2010-08-14 13:37:26', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1715, '2010-08-14 13:37:28', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=9 naziv=dfsdsfds zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=15 sekunda=15 attachment=1 brisanje= Obriši ', 1),
(1716, '2010-08-14 13:37:30', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=9 naziv=dfsdsfds zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=15 sekunda=15 attachment=1 brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1717, '2010-08-14 13:37:30', 1, 'obrisana zadaca 9 sa predmeta pp1', 4),
(1718, '2010-08-14 13:37:30', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1719, '2010-08-14 13:37:32', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1720, '2010-08-14 13:37:35', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=8 naziv=dfsdsf zadataka=2 bodova=3 day=13 month=8 year=2010 sat=16 minuta=10 sekunda=30 brisanje= Obriši ', 1),
(1721, '2010-08-14 13:37:36', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=8 naziv=dfsdsf zadataka=2 bodova=3 day=13 month=8 year=2010 sat=16 minuta=10 sekunda=30 brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1722, '2010-08-14 13:37:36', 1, 'obrisana zadaca 8 sa predmeta pp1', 4),
(1723, '2010-08-14 13:37:37', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1724, '2010-08-14 13:37:38', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1725, '2010-08-14 13:37:40', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=7 naziv=rerwer zadataka=3 bodova=5 day=13 month=8 year=2010 sat=16 minuta=09 sekunda=48 attachment=1 dozvoljene_eks=doc brisanje= Obriši ', 1),
(1726, '2010-08-14 13:37:42', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=7 naziv=rerwer zadataka=3 bodova=5 day=13 month=8 year=2010 sat=16 minuta=09 sekunda=48 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1727, '2010-08-14 13:37:42', 1, 'obrisana zadaca 7 sa predmeta pp1', 4),
(1728, '2010-08-14 13:37:42', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1729, '2010-08-14 13:37:43', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1730, '2010-08-14 13:37:45', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=20 naziv=aaaa zadataka=1 bodova=1 day=13 month=8 year=2010 sat=21 minuta=52 sekunda=26 attachment=1 dozvoljene_eks=docx brisanje= Obriši ', 1),
(1731, '2010-08-14 13:37:47', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=20 naziv=aaaa zadataka=1 bodova=1 day=13 month=8 year=2010 sat=21 minuta=52 sekunda=26 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1732, '2010-08-14 13:37:47', 1, 'obrisana zadaca 20 sa predmeta pp1', 4),
(1733, '2010-08-14 13:37:47', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1734, '2010-08-14 13:37:48', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1735, '2010-08-14 13:37:50', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=5 naziv=Test zadataka=1 bodova=2 day=18 month=8 year=2010 sat=15 minuta=10 sekunda=28 aktivna=on attachment=1 brisanje= Obriši ', 1),
(1736, '2010-08-14 13:37:51', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=5 naziv=Test zadataka=1 bodova=2 day=18 month=8 year=2010 sat=15 minuta=10 sekunda=28 aktivna=on attachment=1 brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1737, '2010-08-14 13:37:51', 1, 'obrisana zadaca 5 sa predmeta pp1', 4),
(1738, '2010-08-14 13:37:51', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1739, '2010-08-14 13:37:54', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1740, '2010-08-14 13:37:55', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=25 naziv=klasla67 zadataka=2 bodova=1 day=13 month=8 year=2010 sat=19 minuta=07 sekunda=56 attachment=1 dozvoljene_eks=xlsx brisanje= Obriši ', 1),
(1741, '2010-08-14 13:37:57', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=25 naziv=klasla67 zadataka=2 bodova=1 day=13 month=8 year=2010 sat=19 minuta=07 sekunda=56 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1742, '2010-08-14 13:37:57', 1, 'obrisana zadaca 25 sa predmeta pp1', 4),
(1743, '2010-08-14 13:37:57', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1744, '2010-08-14 13:38:00', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1745, '2010-08-14 13:38:01', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=13 naziv=Muris zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=22 sekunda=38 attachment=1 dozvoljene_eks=docx brisanje= Obriši ', 1),
(1746, '2010-08-14 13:38:03', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=13 naziv=Muris zadataka=3 bodova=2 day=13 month=8 year=2010 sat=16 minuta=22 sekunda=38 attachment=1 dozvoljene_eks=Array brisanje= Obriši  potvrdabrisanja= Briši ', 1),
(1747, '2010-08-14 13:38:03', 1, 'obrisana zadaca 13 sa predmeta pp1', 4),
(1748, '2010-08-14 13:38:03', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1749, '2010-08-14 13:38:30', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaca 8 zadataka=2 bodova=4 day=14 month=10 year=2010 sat=13 minuta=38 sekunda=03 aktivna=on', 1),
(1750, '2010-08-14 13:38:31', 1, 'kreirana nova zadaca z31', 2),
(1751, '2010-08-14 13:38:32', 1, 'logout', 1),
(1752, '2010-08-14 13:38:51', 2, 'login', 1),
(1753, '2010-08-14 13:38:51', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1754, '2010-08-14 13:38:55', 2, 'student/zadaca zadaca=31 predmet=1 ag=1', 1),
(1755, '2010-08-14 13:38:58', 2, 'student/zadaca predmet=1 ag=1 zadaca=31 zadatak=1', 1),
(1756, '2010-08-14 13:39:47', 2, 'student/zadaca predmet=1 ag=1 zadaca=31 zadatak=1 akcija=slanje labgrupa= program=#include &lt;iostream&gt;\r\n#include &lt;fstream&gt;\r\n\r\nusing namespace std;\r\n\r\nstruct Student{\r\n    char ime_studenta[20], prezime_studenta[20];\r\n    int broj_indeksa, broj_', 1),
(1757, '2010-08-14 13:39:47', 2, 'poslana zadaca z31 zadatak 1', 2),
(1758, '2010-08-14 13:39:50', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1759, '2010-08-14 13:39:53', 2, 'student/pdf zadaca=31', 1),
(1760, '2010-08-14 13:42:24', 2, 'logout', 1),
(1761, '2010-08-14 13:42:40', 1, 'login', 1),
(1762, '2010-08-14 13:42:40', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1763, '2010-08-14 13:42:43', 1, 'saradnik/intro', 1),
(1764, '2010-08-14 13:42:44', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1765, '2010-08-14 13:42:46', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1766, '2010-08-14 13:43:30', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaca 9 zadataka=2 bodova=2 day=25 month=8 year=2010 sat=13 minuta=42 sekunda=46 aktivna=on attachment=1 dozvoljene_eks=doc', 1),
(1767, '2010-08-14 13:43:30', 1, 'kreirana nova zadaca z32', 2),
(1768, '2010-08-14 13:44:13', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=32 naziv=Zadaca 9 zadataka=2 bodova=2 day=25 month=8 year=2010 sat=13 minuta=42 sekunda=46 aktivna=on attachment=1 dozvoljene_eks=doc,docx,zip,php', 1),
(1769, '2010-08-14 13:44:13', 1, 'azurirana zadaca z32', 2),
(1770, '2010-08-14 13:44:15', 1, 'logout', 1),
(1771, '2010-08-14 13:44:23', 2, 'login', 1),
(1772, '2010-08-14 13:44:23', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1773, '2010-08-14 13:45:59', 2, 'student/zadaca zadaca=32 predmet=1 ag=1', 1),
(1774, '2010-08-14 13:46:05', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(1775, '2010-08-14 13:46:09', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(1776, '2010-08-14 13:46:13', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(1777, '2010-08-14 13:46:19', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(1778, '2010-08-14 13:47:01', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(1779, '2010-08-14 13:47:01', 2, 'poslana zadaca z32 zadatak 1 (attachment)', 2),
(1780, '2010-08-14 13:47:05', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1781, '2010-08-14 13:47:08', 2, 'student/pdf zadaca=32', 1),
(1782, '2010-08-14 13:47:32', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=2', 1),
(1783, '2010-08-14 13:47:39', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=2', 1),
(1784, '2010-08-14 13:47:45', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=2', 1),
(1785, '2010-08-14 13:48:13', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=2 labgrupa=', 1),
(1786, '2010-08-14 13:48:13', 2, 'poslana zadaca z32 zadatak 2 (attachment)', 2),
(1787, '2010-08-14 13:48:16', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1788, '2010-08-14 13:48:19', 2, 'student/pdf zadaca=32', 1),
(1789, '2010-08-14 13:52:16', 2, 'student/pdf zadaca=32', 1),
(1790, '2010-08-14 14:02:03', 2, 'student/zadaca predmet=1 ag=1 zadaca=31 zadatak=1', 1),
(1791, '2010-08-14 14:02:08', 2, 'student/zadaca predmet=1 ag=1 zadaca=31 zadatak=2', 1),
(1792, '2010-08-14 14:02:11', 2, 'student/zadaca predmet=1 ag=1 zadaca=31 zadatak=1', 1),
(1793, '2010-08-14 14:02:21', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1794, '2010-08-14 14:02:24', 2, 'student/pdf zadaca=31', 1),
(1795, '2010-08-14 14:09:33', 2, 'student/pdf zadaca=31', 1),
(1796, '2010-08-14 14:10:18', 2, 'student/pdf zadaca=31', 1),
(1797, '2010-08-14 14:11:04', 2, 'student/pdf zadaca=31', 1),
(1798, '2010-08-14 14:11:26', 2, 'student/pdf zadaca=31', 1),
(1799, '2010-08-14 14:11:51', 2, 'student/pdf zadaca=31', 1),
(1800, '2010-08-14 14:12:52', 2, 'student/pdf zadaca=31', 1),
(1801, '2010-08-14 14:15:11', 2, 'student/pdf zadaca=31', 1),
(1802, '2010-08-14 14:15:51', 2, 'student/pdf zadaca=31', 1),
(1803, '2010-08-14 14:16:27', 2, 'student/pdf zadaca=31', 1),
(1804, '2010-08-14 14:17:01', 2, 'student/pdf zadaca=31', 1),
(1805, '2010-08-14 14:18:29', 2, 'logout', 1),
(1806, '2010-08-14 14:18:36', 1, 'login', 1),
(1807, '2010-08-14 14:18:36', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1808, '2010-08-14 14:18:38', 1, 'saradnik/intro', 1),
(1809, '2010-08-14 14:18:40', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1810, '2010-08-14 14:18:52', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1811, '2010-08-14 14:18:56', 1, 'izvjestaj/statistika_predmeta predmet=1 ag=1', 1),
(1812, '2010-08-14 14:18:59', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1813, '2010-08-14 14:19:01', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(1814, '2010-08-14 14:19:05', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1815, '2010-08-14 14:19:07', 1, 'izvjestaj/grupe predmet=1 ag=1', 1),
(1816, '2010-08-14 14:19:09', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1817, '2010-08-14 14:19:57', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(1818, '2010-08-14 14:21:58', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1819, '2010-08-14 14:22:01', 1, 'izvjestaj/predmet predmet=1 ag=1', 1);
INSERT INTO `log` (`id`, `vrijeme`, `userid`, `dogadjaj`, `nivo`) VALUES 
(1820, '2010-08-14 14:22:03', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1821, '2010-08-14 14:22:17', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(1822, '2010-08-14 14:22:55', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1823, '2010-08-14 14:22:56', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(1824, '2010-08-14 14:22:58', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1825, '2010-08-14 14:23:00', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(1826, '2010-08-14 14:23:02', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1827, '2010-08-14 14:23:54', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1828, '2010-08-14 14:23:59', 1, 'student/pdf zadaca=2', 1),
(1829, '2010-08-14 14:25:10', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1830, '2010-08-14 14:25:53', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1831, '2010-08-14 14:25:55', 1, 'student/pdf zadaca=1', 1),
(1832, '2010-08-14 14:26:20', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(1833, '2010-08-14 14:27:18', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(1834, '2010-08-14 14:35:19', 1, 'student/pdf zadaca=1', 1),
(1835, '2010-08-14 14:41:15', 1, 'logout', 1),
(1836, '2010-08-14 14:41:29', 2, 'login', 1),
(1837, '2010-08-14 14:41:29', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1838, '2010-08-14 14:42:06', 2, 'student/zadaca zadaca=32 predmet=1 ag=1', 1),
(1839, '2010-08-14 14:42:11', 2, 'student/zadaca predmet=1 ag=1 zadaca=31 zadatak=1', 1),
(1840, '2010-08-14 14:42:13', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(1841, '2010-08-14 14:42:15', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=2', 1),
(1842, '2010-08-14 14:42:27', 2, 'student/zadaca predmet=1 ag=1 zadaca=31 zadatak=1', 1),
(1843, '2010-08-14 14:42:29', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(1844, '2010-08-14 14:42:37', 2, 'logout', 1),
(1845, '2010-08-14 14:42:44', 1, 'login', 1),
(1846, '2010-08-14 14:42:44', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1847, '2010-08-14 14:42:47', 1, 'saradnik/intro', 1),
(1848, '2010-08-14 14:42:49', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1849, '2010-08-14 14:42:52', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1850, '2010-08-14 14:42:56', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1851, '2010-08-14 14:43:09', 1, 'logout', 1),
(1852, '2010-08-14 14:43:17', 2, 'login', 1),
(1853, '2010-08-14 14:43:17', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1854, '2010-08-14 14:43:19', 2, 'student/zadaca zadaca=32 predmet=1 ag=1', 1),
(1855, '2010-08-14 14:43:34', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(1856, '2010-08-14 14:43:39', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=2', 1),
(1857, '2010-08-14 14:44:03', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=2 labgrupa=', 1),
(1858, '2010-08-14 14:44:03', 2, 'poslana zadaca z32 zadatak 2 (attachment)', 2),
(1859, '2010-08-14 14:44:06', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(1860, '2010-08-14 17:02:31', 2, 'login', 1),
(1861, '2010-08-14 17:02:31', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1862, '2010-08-14 17:02:42', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1863, '2010-08-14 17:02:46', 2, 'student/pdf zadaca=32', 1),
(1864, '2010-08-14 17:03:00', 2, 'student/pdf zadaca=31', 1),
(1865, '2010-08-14 22:26:31', 2, 'student/pdf zadaca=31', 1),
(1866, '2010-08-14 22:26:56', 2, 'student/pdf zadaca=31', 1),
(1867, '2010-08-14 22:27:07', 2, 'student/pdf zadaca=32', 1),
(1868, '2010-08-14 22:27:21', 2, 'logout', 1),
(1869, '2010-08-14 22:27:37', 2, 'login', 1),
(1870, '2010-08-14 22:27:37', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1871, '2010-08-14 22:27:41', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1872, '2010-08-14 22:27:46', 2, 'student/pdf zadaca=31', 1),
(1873, '2010-08-14 22:28:41', 2, 'student/pdf zadaca=1', 1),
(1874, '2010-08-14 22:28:51', 2, 'student/pdf zadaca=31', 1),
(1875, '2010-08-14 22:29:11', 2, 'logout', 1),
(1876, '2010-08-14 22:30:14', 2, 'login', 1),
(1877, '2010-08-14 22:30:14', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1878, '2010-08-14 22:30:16', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1879, '2010-08-14 22:30:19', 2, 'student/pdf zadaca=31', 1),
(1880, '2010-08-14 22:30:44', 2, 'logout', 1),
(1881, '2010-08-14 22:30:51', 0, 'logout', 1),
(1882, '2010-08-14 22:30:51', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/pdf', 3),
(1883, '2010-08-14 22:31:00', 2, 'login', 1),
(1884, '2010-08-14 22:31:00', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1885, '2010-08-14 22:31:02', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1886, '2010-08-14 22:31:03', 2, 'student/pdf zadaca=31', 1),
(1887, '2010-08-15 15:01:28', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1888, '2010-08-15 15:01:28', 2, 'student/pdf zadaca=31', 1),
(1889, '2010-08-15 16:12:11', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1890, '2010-08-15 16:12:11', 2, 'student/pdf zadaca=31', 1),
(1891, '2010-08-15 19:42:19', 2, 'login', 1),
(1892, '2010-08-15 19:42:19', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1893, '2010-08-15 19:42:23', 2, 'logout', 1),
(1894, '2010-08-16 11:18:36', 2, 'login', 1),
(1895, '2010-08-16 11:18:36', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1896, '2010-08-16 11:18:41', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1897, '2010-08-16 11:18:44', 2, 'student/pdf zadaca=31', 1),
(1898, '2010-08-16 11:18:59', 2, 'logout', 1),
(1899, '2010-08-16 11:49:22', 0, 'logout', 1),
(1900, '2010-08-16 11:49:29', 2, 'login', 1),
(1901, '2010-08-16 11:49:29', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1902, '2010-08-16 11:49:31', 2, 'logout', 1),
(1903, '2010-08-16 12:32:28', 2, 'login', 1),
(1904, '2010-08-16 12:32:28', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1905, '2010-08-16 12:32:31', 2, 'logout', 1),
(1906, '2010-08-16 12:33:00', 0, 'logout', 1),
(1907, '2010-08-16 12:33:02', 0, 'logout', 1),
(1908, '2010-08-16 12:34:48', 2, 'login', 1),
(1909, '2010-08-16 12:34:48', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1910, '2010-08-16 12:34:53', 2, 'logout', 1),
(1911, '2010-08-16 15:22:24', 0, 'index.php greska: Nepoznat korisnik jasmn ', 3),
(1912, '2010-08-16 15:22:32', 2, 'login', 1),
(1913, '2010-08-16 15:22:32', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1914, '2010-08-16 15:22:35', 2, 'logout', 1),
(1915, '2010-08-16 18:19:17', 0, 'index.php greska: Pogrešna šifra jasmin ', 3),
(1916, '2010-08-16 18:19:29', 2, 'login', 1),
(1917, '2010-08-16 18:19:29', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1918, '2010-08-16 18:19:31', 2, '/zamger41/index.php?loginforma=1 login=jasmin sm_arhiva=1', 1),
(1919, '2010-08-16 18:19:32', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=1', 1),
(1920, '2010-08-16 18:19:34', 2, 'student/pdf zadaca=31', 1),
(1921, '2010-08-16 18:21:18', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(1922, '2010-08-16 18:21:21', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=1', 1),
(1923, '2010-08-16 18:21:23', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=2', 1),
(1924, '2010-08-16 18:21:26', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=1', 1),
(1925, '2010-08-16 18:21:27', 2, 'student/zadaca predmet=1 ag=1 zadaca=31 zadatak=1', 1),
(1926, '2010-08-16 18:21:34', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=1', 1),
(1927, '2010-08-16 18:21:37', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=2', 1),
(1928, '2010-08-16 18:21:39', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=1', 1),
(1929, '2010-08-16 18:21:41', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=2', 1),
(1930, '2010-08-16 18:21:43', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=1', 1),
(1931, '2010-08-16 18:21:44', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1932, '2010-08-16 18:21:47', 2, 'student/pdf zadaca=30', 1),
(1933, '2010-08-16 18:22:05', 2, 'student/pdf zadaca=30', 1),
(1934, '2010-08-16 18:22:20', 2, 'student/pdf zadaca=30', 1),
(1935, '2010-08-16 18:22:31', 2, 'student/pdf zadaca=29', 1),
(1936, '2010-08-16 18:22:48', 2, 'student/pdf zadaca=32', 1),
(1937, '2010-08-16 18:23:03', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(1938, '2010-08-16 18:23:19', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(1939, '2010-08-16 18:23:20', 2, 'greska kod attachmenta (zadaca z32, varijabla program je: c:/wamp/tmpphp925D.tmp)', 3),
(1940, '2010-08-16 18:23:41', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(1941, '2010-08-16 18:23:41', 2, 'greska kod attachmenta (zadaca z32, varijabla program je: c:/wamp/tmpphpE5AB.tmp)', 3),
(1942, '2010-08-16 18:24:02', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(1943, '2010-08-16 18:24:02', 2, 'poslana zadaca z32 zadatak 1 (attachment)', 2),
(1944, '2010-08-16 18:24:08', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1945, '2010-08-16 18:24:10', 2, 'student/pdf zadaca=32', 1),
(1946, '2010-08-16 18:27:08', 2, 'logout', 1),
(1947, '2010-08-16 18:27:17', 0, 'index.php greska: Pogrešna šifra admin ', 3),
(1948, '2010-08-16 18:27:36', 1, 'login', 1),
(1949, '2010-08-16 18:27:36', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(1950, '2010-08-16 18:27:39', 1, 'saradnik/intro', 1),
(1951, '2010-08-16 18:27:40', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(1952, '2010-08-16 18:27:43', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(1953, '2010-08-16 18:28:24', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaca 10 zadataka=1 bodova=1 day=20 month=8 year=2010 sat=18 minuta=27 sekunda=43 aktivna=on attachment=1 dozvoljene_eks=zip,rar', 1),
(1954, '2010-08-16 18:28:24', 1, 'kreirana nova zadaca z33', 2),
(1955, '2010-08-16 18:28:26', 1, 'logout', 1),
(1956, '2010-08-16 18:28:34', 2, 'login', 1),
(1957, '2010-08-16 18:28:34', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1958, '2010-08-16 18:28:37', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1959, '2010-08-16 18:28:39', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(1960, '2010-08-16 18:28:53', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(1961, '2010-08-16 18:28:53', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(1962, '2010-08-16 18:28:56', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1963, '2010-08-16 18:28:58', 2, 'student/pdf zadaca=33', 1),
(1964, '2010-08-16 18:43:01', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1965, '2010-08-16 18:43:14', 2, 'student/pdf zadaca=33', 1),
(1966, '2010-08-16 19:09:52', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(1967, '2010-08-16 19:10:09', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(1968, '2010-08-16 19:10:09', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(1969, '2010-08-16 19:10:17', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1970, '2010-08-16 19:10:19', 2, 'student/pdf zadaca=33', 1),
(1971, '2010-08-16 19:10:23', 2, 'student/pdf zadaca=33', 1),
(1972, '2010-08-16 19:10:26', 2, 'student/pdf zadaca=33', 1),
(1973, '2010-08-16 19:10:27', 2, 'student/pdf zadaca=33', 1),
(1974, '2010-08-16 19:10:38', 2, 'student/pdf zadaca=33', 1),
(1975, '2010-08-16 20:23:55', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/predmet', 3),
(1976, '2010-08-16 22:05:03', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/predmet', 3),
(1977, '2010-08-16 22:06:00', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/predmet', 3),
(1978, '2010-08-16 22:28:43', 2, 'login', 1),
(1979, '2010-08-16 22:28:43', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(1980, '2010-08-16 22:28:46', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1981, '2010-08-16 22:28:47', 2, 'student/pdf zadaca=33', 1),
(1982, '2010-08-16 22:30:03', 2, 'student/pdf zadaca=33', 1),
(1983, '2010-08-16 22:33:22', 2, 'student/pdf zadaca=33', 1),
(1984, '2010-08-16 22:33:40', 2, 'student/pdf zadaca=33', 1),
(1985, '2010-08-16 22:33:45', 2, 'student/pdf zadaca=33', 1),
(1986, '2010-08-16 22:34:29', 2, 'student/pdf zadaca=33', 1),
(1987, '2010-08-16 22:37:34', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(1988, '2010-08-16 22:37:37', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(1989, '2010-08-16 22:38:11', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(1990, '2010-08-16 22:38:11', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(1991, '2010-08-16 22:39:21', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(1992, '2010-08-16 22:39:22', 2, 'student/pdf zadaca=33', 1),
(1993, '2010-08-16 22:43:21', 2, 'student/pdf zadaca=33', 1),
(1994, '2010-08-16 22:48:12', 2, 'student/pdf zadaca=33', 1),
(1995, '2010-08-16 22:48:20', 2, 'student/pdf zadaca=33', 1),
(1996, '2010-08-16 22:49:15', 2, 'student/pdf zadaca=33', 1),
(1997, '2010-08-16 22:51:36', 2, 'student/pdf zadaca=33', 1),
(1998, '2010-08-16 22:52:49', 2, 'student/pdf zadaca=33', 1),
(1999, '2010-08-16 22:54:07', 2, 'student/pdf zadaca=33', 1),
(2000, '2010-08-16 22:54:11', 2, 'student/pdf zadaca=33', 1),
(2001, '2010-08-16 22:59:55', 2, 'student/pdf zadaca=33', 1),
(2002, '2010-08-16 22:59:58', 2, 'student/pdf zadaca=33', 1),
(2003, '2010-08-16 23:00:01', 2, 'student/pdf zadaca=33', 1),
(2004, '2010-08-16 23:03:06', 2, 'student/pdf zadaca=32', 1),
(2005, '2010-08-16 23:03:10', 2, 'student/pdf zadaca=32', 1),
(2006, '2010-08-16 23:03:14', 2, 'student/pdf zadaca=32', 1),
(2007, '2010-08-16 23:04:36', 2, 'student/pdf zadaca=33', 1),
(2008, '2010-08-16 23:05:53', 2, 'student/pdf zadaca=33', 1),
(2009, '2010-08-16 23:07:26', 2, 'student/pdf zadaca=33', 1),
(2010, '2010-08-16 23:08:09', 2, 'student/pdf zadaca=33', 1),
(2011, '2010-08-16 23:08:57', 2, 'student/pdf zadaca=33', 1),
(2012, '2010-08-16 23:10:41', 2, 'student/pdf zadaca=33', 1),
(2013, '2010-08-16 23:12:20', 2, 'student/pdf zadaca=33', 1),
(2014, '2010-08-16 23:30:23', 2, 'student/pdf zadaca=33', 1),
(2015, '2010-08-16 23:33:17', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2016, '2010-08-16 23:33:30', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2017, '2010-08-16 23:33:30', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2018, '2010-08-16 23:33:44', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2019, '2010-08-16 23:33:52', 2, 'student/pdf zadaca=33', 1),
(2020, '2010-08-16 23:44:14', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2021, '2010-08-16 23:44:30', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2022, '2010-08-16 23:44:30', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2023, '2010-08-16 23:44:32', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2024, '2010-08-16 23:44:35', 2, 'student/pdf zadaca=33', 1),
(2025, '2010-08-16 23:44:38', 2, 'student/pdf zadaca=33', 1),
(2026, '2010-08-16 23:46:41', 2, 'student/pdf zadaca=33', 1),
(2027, '2010-08-16 23:47:05', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2028, '2010-08-16 23:47:43', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2029, '2010-08-16 23:47:43', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2030, '2010-08-16 23:49:00', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2031, '2010-08-16 23:49:02', 2, 'student/pdf zadaca=33', 1),
(2032, '2010-08-16 23:49:20', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2033, '2010-08-16 23:49:30', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2034, '2010-08-16 23:49:30', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2035, '2010-08-16 23:49:55', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2036, '2010-08-16 23:49:57', 2, 'student/pdf zadaca=33', 1),
(2037, '2010-08-16 23:52:06', 2, 'student/pdf zadaca=33', 1),
(2038, '2010-08-17 00:03:27', 2, 'student/pdf zadaca=33', 1),
(2039, '2010-08-17 00:31:57', 2, 'student/pdf zadaca=33', 1),
(2040, '2010-08-17 00:32:27', 2, 'student/pdf zadaca=33', 1),
(2041, '2010-08-17 00:33:04', 2, 'student/pdf zadaca=33', 1),
(2042, '2010-08-17 00:33:09', 2, 'student/pdf zadaca=33', 1),
(2043, '2010-08-17 10:18:05', 2, 'login', 1),
(2044, '2010-08-17 10:18:05', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2045, '2010-08-17 10:18:09', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2046, '2010-08-17 10:18:14', 2, 'student/pdf zadaca=33', 1),
(2047, '2010-08-17 10:19:17', 2, 'student/moodle predmet=1 ag=1', 1),
(2048, '2010-08-17 10:19:17', 2, 'ne postoji moodle ID za predmet pp1, ag1', 3),
(2049, '2010-08-17 12:26:17', 2, 'student/pdf zadaca=32', 1),
(2050, '2010-08-17 12:27:03', 2, 'student/pdf zadaca=32', 1),
(2051, '2010-08-17 12:30:04', 2, 'student/pdf zadaca=33', 1),
(2052, '2010-08-17 12:31:11', 2, 'student/pdf zadaca=33', 1),
(2053, '2010-08-17 12:32:46', 2, 'student/pdf zadaca=33', 1),
(2054, '2010-08-17 12:34:33', 2, 'student/pdf zadaca=33', 1),
(2055, '2010-08-17 12:36:50', 2, 'student/pdf zadaca=33', 1),
(2056, '2010-08-17 12:37:23', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2057, '2010-08-17 12:37:25', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2058, '2010-08-17 12:37:27', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2059, '2010-08-17 12:37:28', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2060, '2010-08-17 12:37:28', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2061, '2010-08-17 12:37:29', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2062, '2010-08-17 12:37:29', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2063, '2010-08-17 12:37:30', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2064, '2010-08-17 12:37:31', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2065, '2010-08-17 12:37:31', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2066, '2010-08-17 12:37:42', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2067, '2010-08-17 12:37:42', 2, 'greska kod attachmenta (zadaca z33, varijabla program je: c:/wamp/tmpphp1C3A.tmp)', 3),
(2068, '2010-08-17 12:37:52', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2069, '2010-08-17 12:37:54', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(2070, '2010-08-17 12:37:57', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=2', 1),
(2071, '2010-08-17 12:37:59', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(2072, '2010-08-17 12:38:02', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2073, '2010-08-17 12:38:03', 2, 'student/pdf zadaca=32', 1),
(2074, '2010-08-17 12:39:25', 2, 'student/pdf zadaca=32', 1),
(2075, '2010-08-17 12:41:09', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2076, '2010-08-17 12:41:27', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2077, '2010-08-17 12:41:27', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2078, '2010-08-17 12:42:05', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2079, '2010-08-17 12:42:06', 2, 'student/pdf zadaca=33', 1),
(2080, '2010-08-17 12:44:16', 2, 'student/pdf zadaca=33', 1),
(2081, '2010-08-17 12:46:39', 2, 'student/pdf zadaca=33', 1),
(2082, '2010-08-17 12:47:41', 2, 'student/pdf zadaca=33', 1),
(2083, '2010-08-17 12:50:25', 2, 'student/pdf zadaca=33', 1),
(2084, '2010-08-17 12:51:20', 2, 'student/pdf zadaca=33', 1),
(2085, '2010-08-17 12:55:17', 2, 'student/pdf zadaca=33', 1),
(2086, '2010-08-17 12:56:11', 2, 'student/pdf zadaca=33', 1),
(2087, '2010-08-17 12:59:11', 2, 'student/pdf zadaca=33', 1),
(2088, '2010-08-17 12:59:43', 2, 'student/pdf zadaca=33', 1),
(2089, '2010-08-17 13:01:44', 2, 'student/pdf zadaca=33', 1),
(2090, '2010-08-17 13:05:33', 2, 'student/pdf zadaca=33', 1),
(2091, '2010-08-17 13:06:06', 2, 'student/pdf zadaca=33', 1),
(2092, '2010-08-17 13:06:43', 2, 'student/pdf zadaca=33', 1),
(2093, '2010-08-17 13:07:42', 2, 'student/pdf zadaca=33', 1),
(2094, '2010-08-17 19:34:34', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2095, '2010-08-17 19:34:37', 2, 'student/pdf zadaca=33', 1),
(2096, '2010-08-17 20:07:10', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/predmet', 3),
(2097, '2010-08-17 20:15:54', 2, 'student/pdf zadaca=33', 1),
(2098, '2010-08-17 20:17:33', 2, 'student/pdf zadaca=33', 1),
(2099, '2010-08-17 20:18:29', 2, 'student/pdf zadaca=33', 1),
(2100, '2010-08-17 20:19:00', 2, 'student/pdf zadaca=32', 1),
(2101, '2010-08-17 20:19:42', 2, 'student/pdf zadaca=33', 1),
(2102, '2010-08-17 20:36:58', 2, 'student/pdf zadaca=33', 1),
(2103, '2010-08-17 20:38:08', 2, 'student/pdf zadaca=33', 1),
(2104, '2010-08-17 20:39:31', 2, 'student/pdf zadaca=33', 1),
(2105, '2010-08-17 20:41:46', 2, 'student/pdf zadaca=33', 1),
(2106, '2010-08-17 20:42:44', 2, 'student/pdf zadaca=33', 1),
(2107, '2010-08-17 20:43:27', 2, 'student/pdf zadaca=33', 1),
(2108, '2010-08-17 20:45:23', 2, 'student/pdf zadaca=33', 1),
(2109, '2010-08-17 20:46:02', 2, 'student/pdf zadaca=33', 1),
(2110, '2010-08-17 20:46:53', 2, 'student/pdf zadaca=33', 1),
(2111, '2010-08-17 20:47:23', 2, 'student/pdf zadaca=33', 1),
(2112, '2010-08-17 20:48:26', 2, 'student/pdf zadaca=33', 1),
(2113, '2010-08-17 20:48:28', 2, 'student/pdf zadaca=33', 1),
(2114, '2010-08-17 20:48:37', 2, 'student/pdf zadaca=33', 1),
(2115, '2010-08-17 20:49:48', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/predmet', 3),
(2116, '2010-08-17 23:35:49', 2, 'student/pdf zadaca=33', 1),
(2117, '2010-08-17 23:36:37', 2, 'student/pdf zadaca=33', 1),
(2118, '2010-08-17 23:37:26', 2, 'student/pdf zadaca=33', 1),
(2119, '2010-08-17 23:45:09', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2120, '2010-08-17 23:45:12', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2121, '2010-08-17 23:45:25', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2122, '2010-08-17 23:45:25', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2123, '2010-08-17 23:50:55', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2124, '2010-08-17 23:50:59', 2, 'student/pdf zadaca=33', 1),
(2125, '2010-08-17 23:52:26', 2, 'student/pdf zadaca=33', 1),
(2126, '2010-08-17 23:53:31', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2127, '2010-08-17 23:53:33', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2128, '2010-08-17 23:53:41', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2129, '2010-08-17 23:53:41', 2, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2130, '2010-08-17 23:53:44', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2131, '2010-08-17 23:58:39', 2, 'student/pdf zadaca=33', 1),
(2132, '2010-08-18 00:01:25', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2133, '2010-08-18 00:08:37', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2134, '2010-08-18 00:11:47', 2, 'logout', 1),
(2135, '2010-08-18 00:11:59', 3, 'login', 1),
(2136, '2010-08-18 00:11:59', 3, '/zamger41/index.php?loginforma=1 login=fahrudin', 1),
(2137, '2010-08-18 00:12:02', 3, 'student/zadaca zadaca=33 predmet=1 ag=1', 1),
(2138, '2010-08-18 00:12:05', 3, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2139, '2010-08-18 00:12:15', 3, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2140, '2010-08-18 00:12:15', 3, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2141, '2010-08-18 00:12:17', 3, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2142, '2010-08-18 00:12:20', 3, 'student/pdf zadaca=33', 1),
(2143, '2010-08-18 00:12:56', 3, 'logout', 1),
(2144, '2010-08-18 00:13:04', 2, 'login', 1),
(2145, '2010-08-18 00:13:04', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2146, '2010-08-18 00:13:06', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2147, '2010-08-18 00:17:12', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2148, '2010-08-18 00:17:20', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2149, '2010-08-18 00:17:22', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2150, '2010-08-18 00:17:25', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2151, '2010-08-18 00:17:27', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2152, '2010-08-18 00:19:50', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2153, '2010-08-18 00:59:10', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2154, '2010-08-18 00:59:12', 2, 'student/pdf zadaca=33', 1),
(2155, '2010-08-18 01:00:35', 2, 'student/pdf zadaca=33', 1),
(2156, '2010-08-18 01:00:50', 2, 'student/pdf zadaca=33', 1),
(2157, '2010-08-18 01:03:28', 2, 'student/pdf zadaca=33', 1),
(2158, '2010-08-18 01:05:42', 2, 'student/pdf zadaca=33', 1),
(2159, '2010-08-18 01:06:23', 2, 'student/pdf zadaca=33', 1),
(2160, '2010-08-18 01:11:02', 2, 'student/pdf zadaca=33', 1),
(2161, '2010-08-18 01:14:36', 2, 'student/pdf zadaca=33', 1),
(2162, '2010-08-18 01:15:33', 2, 'student/pdf zadaca=33', 1),
(2163, '2010-08-18 01:18:03', 2, 'student/pdf zadaca=33', 1),
(2164, '2010-08-18 01:25:55', 2, 'logout', 1),
(2165, '2010-08-18 01:26:07', 3, 'login', 1),
(2166, '2010-08-18 01:26:07', 3, '/zamger41/index.php?loginforma=1 login=fahrudin', 1),
(2167, '2010-08-18 01:26:10', 3, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2168, '2010-08-18 01:26:12', 3, 'student/pdf zadaca=33', 1),
(2169, '2010-08-18 01:29:02', 3, 'student/pdf zadaca=33', 1),
(2170, '2010-08-18 01:30:38', 3, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2171, '2010-08-18 01:30:48', 3, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2172, '2010-08-18 01:30:48', 3, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2173, '2010-08-18 01:32:02', 3, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2174, '2010-08-18 01:32:03', 3, 'student/pdf zadaca=33', 1),
(2175, '2010-08-18 01:33:47', 3, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2176, '2010-08-18 01:33:48', 3, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2177, '2010-08-18 01:34:02', 3, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=33 zadatak=1 labgrupa=', 1),
(2178, '2010-08-18 01:34:02', 3, 'poslana zadaca z33 zadatak 1 (attachment)', 2),
(2179, '2010-08-18 01:34:49', 3, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2180, '2010-08-18 01:34:51', 3, 'student/pdf zadaca=33', 1),
(2181, '2010-08-18 01:35:34', 3, 'student/pdf zadaca=33', 1),
(2182, '2010-08-18 01:46:27', 3, 'student/pdf zadaca=32', 1),
(2183, '2010-08-18 01:46:55', 3, 'student/pdf zadaca=33', 1),
(2184, '2010-08-18 02:11:13', 3, 'student/pdf zadaca=33', 1),
(2185, '2010-08-18 20:04:17', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/predmet', 3),
(2186, '2010-08-18 22:11:47', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  student/predmet', 3),
(2187, '2010-08-19 11:15:09', 2, 'login', 1),
(2188, '2010-08-19 11:15:09', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2189, '2010-08-19 11:15:11', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2190, '2010-08-19 11:15:14', 2, 'student/pdf zadaca=33', 1),
(2191, '2010-08-19 11:15:52', 2, 'student/pdf zadaca=32', 1),
(2192, '2010-08-19 11:16:46', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2193, '2010-08-19 11:17:10', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2194, '2010-08-19 11:17:13', 2, 'student/pdf zadaca=33', 1),
(2195, '2010-08-20 10:29:07', 5, 'login', 1),
(2196, '2010-08-20 10:29:07', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(2197, '2010-08-20 10:29:10', 5, 'saradnik/grupa id=2', 1),
(2198, '2010-08-20 10:29:24', 5, 'saradnik/zadaca student=2 zadaca=33 zadatak=1', 1),
(2199, '2010-08-20 10:29:31', 5, 'common/attachment student=2 zadaca=33 zadatak=1', 1),
(2200, '2010-08-20 10:30:30', 5, 'saradnik/zadaca student=3 zadaca=33 zadatak=1', 1),
(2201, '2010-08-20 10:30:52', 5, 'saradnik/zadaca student=2 zadaca=33 zadatak=1', 1),
(2202, '2010-08-20 10:30:59', 5, 'saradnik/zadaca student=2 zadaca=33 zadatak=1 akcija=slanje status=5 bodova=1 komentar=', 1),
(2203, '2010-08-20 10:30:59', 5, 'izmjena zadace (student u2 zadaca z33 zadatak 1)', 2),
(2204, '2010-08-20 10:31:03', 5, 'saradnik/grupa id=2', 1),
(2205, '2010-08-20 10:31:07', 5, 'logout', 1),
(2206, '2010-08-20 10:33:37', 2, 'login', 1),
(2207, '2010-08-20 10:33:37', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2208, '2010-08-20 10:33:39', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2209, '2010-08-20 10:33:43', 2, 'student/pdf zadaca=33', 1),
(2210, '2010-08-20 10:41:55', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(2211, '2010-08-20 11:20:51', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(2212, '2010-08-20 11:20:51', 2, 'poslana zadaca z32 zadatak 1 (attachment)', 2),
(2213, '2010-08-20 11:21:56', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(2214, '2010-08-20 11:21:56', 2, 'poslana zadaca z32 zadatak 1 (attachment)', 2),
(2215, '2010-08-20 11:23:08', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2216, '2010-08-20 11:23:10', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(2217, '2010-08-20 11:23:13', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(2218, '2010-08-20 11:23:13', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(2219, '2010-08-20 11:23:15', 2, 'logout', 1),
(2220, '2010-08-20 11:23:26', 5, 'login', 1),
(2221, '2010-08-20 11:23:26', 5, '/zamger41/index.php?loginforma=1 login=huse', 1),
(2222, '2010-08-20 11:23:28', 5, 'saradnik/grupa id=2', 1),
(2223, '2010-08-20 11:23:37', 5, 'saradnik/zadaca student=2 zadaca=32 zadatak=1', 1),
(2224, '2010-08-20 11:23:46', 5, 'logout', 1),
(2225, '2010-08-20 11:24:05', 2, 'login', 1),
(2226, '2010-08-20 11:24:05', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2227, '2010-08-20 11:24:12', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2228, '2010-08-20 11:24:14', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(2229, '2010-08-20 11:24:24', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(2230, '2010-08-20 11:24:24', 2, 'poslana zadaca z32 zadatak 1 (attachment)', 2),
(2231, '2010-08-20 11:26:00', 2, 'logout', 1),
(2232, '2010-08-20 11:26:07', 1, 'login', 1),
(2233, '2010-08-20 11:26:07', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2234, '2010-08-20 11:26:09', 1, 'saradnik/intro', 1),
(2235, '2010-08-20 11:26:11', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2236, '2010-08-20 11:26:12', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2237, '2010-08-20 11:27:23', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaca 11 zadataka=1 bodova=1 day=21 month=8 year=2010 sat=17 minuta=26 sekunda=12 aktivna=on attachment=1 dozvoljene_eks=doc,docx,zip,pdf,php,c', 1),
(2238, '2010-08-20 11:27:23', 1, 'kreirana nova zadaca z34', 2),
(2239, '2010-08-20 11:27:26', 1, 'logout', 1),
(2240, '2010-08-20 11:27:41', 2, 'login', 1),
(2241, '2010-08-20 11:27:41', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2242, '2010-08-20 11:27:45', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2243, '2010-08-20 11:28:28', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=1', 1),
(2244, '2010-08-20 11:28:49', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=34 zadatak=1 labgrupa=', 1),
(2245, '2010-08-20 11:28:49', 2, 'poslana zadaca z34 zadatak 1 (attachment)', 2),
(2246, '2010-08-20 11:36:35', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2247, '2010-08-20 11:36:38', 2, 'student/pdf zadaca=34', 1),
(2248, '2010-08-20 14:09:46', 2, 'login', 1),
(2249, '2010-08-20 14:09:46', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2250, '2010-08-20 14:09:49', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2251, '2010-08-20 14:10:10', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2252, '2010-08-20 14:10:14', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2253, '2010-08-20 14:10:16', 2, 'student/pdf zadaca=33', 1),
(2254, '2010-08-20 14:17:06', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(2255, '2010-08-20 14:17:13', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2256, '2010-08-20 14:17:14', 2, 'logout', 1),
(2257, '2010-08-20 14:17:26', 2, 'login', 1),
(2258, '2010-08-20 14:17:26', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2259, '2010-08-20 14:17:30', 2, 'logout', 1),
(2260, '2010-08-20 14:17:37', 1, 'login', 1),
(2261, '2010-08-20 14:17:37', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2262, '2010-08-20 14:17:50', 1, 'saradnik/intro', 1),
(2263, '2010-08-20 14:17:52', 1, 'saradnik/grupa id=2', 1),
(2264, '2010-08-20 14:17:54', 1, 'saradnik/intro', 1),
(2265, '2010-08-20 14:17:55', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2266, '2010-08-20 14:17:56', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2267, '2010-08-20 14:22:30', 1, 'logout', 1),
(2268, '2010-08-20 14:22:51', 1, 'login', 1),
(2269, '2010-08-20 14:22:51', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2270, '2010-08-20 14:22:55', 1, 'saradnik/intro', 1),
(2271, '2010-08-20 14:22:56', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2272, '2010-08-20 14:22:57', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2273, '2010-08-20 14:22:59', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2274, '2010-08-20 14:23:07', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=34 naziv=Zadaca 11 zadataka=1 bodova=1 day=21 month=8 year=2010 sat=17 minuta=26 sekunda=12 aktivna=on attachment=1 dozvoljene_eks=doc,docx,zip,pdf,php,c', 1),
(2275, '2010-08-20 14:23:07', 1, 'azurirana zadaca z34', 2),
(2276, '2010-08-20 14:23:09', 1, 'logout', 1),
(2277, '2010-08-20 14:23:17', 2, 'login', 1),
(2278, '2010-08-20 14:23:17', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2279, '2010-08-20 14:23:20', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2280, '2010-08-20 14:23:28', 2, 'logout', 1),
(2281, '2010-08-20 14:23:44', 0, 'index.php greska: Pogrešna šifra admin ', 3),
(2282, '2010-08-20 14:23:53', 1, 'login', 1),
(2283, '2010-08-20 14:23:53', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2284, '2010-08-20 14:23:54', 1, 'saradnik/intro', 1),
(2285, '2010-08-20 14:23:56', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2286, '2010-08-20 14:23:58', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2287, '2010-08-20 14:24:00', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2288, '2010-08-20 14:24:23', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=34 naziv=Zadaca 11 zadataka=2 bodova=2 day=21 month=8 year=2010 sat=17 minuta=26 sekunda=12 aktivna=on attachment=1 dozvoljene_eks=doc,docx,zip,pdf,php,c', 1),
(2289, '2010-08-20 14:24:23', 1, 'azurirana zadaca z34', 2),
(2290, '2010-08-20 14:24:25', 1, 'logout', 1),
(2291, '2010-08-20 14:24:33', 2, 'login', 1),
(2292, '2010-08-20 14:24:33', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2293, '2010-08-20 14:24:37', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2294, '2010-08-20 14:24:41', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=1', 1),
(2295, '2010-08-20 14:24:53', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=2', 1),
(2296, '2010-08-20 14:25:04', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2297, '2010-08-20 14:25:07', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=2', 1),
(2298, '2010-08-20 14:25:25', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=34 zadatak=2 labgrupa=', 1),
(2299, '2010-08-20 14:25:25', 2, 'poslana zadaca z34 zadatak 2 (attachment)', 2),
(2300, '2010-08-20 14:25:32', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=34 zadatak=2 labgrupa=', 1),
(2301, '2010-08-20 14:25:37', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2302, '2010-08-20 14:26:23', 2, 'student/pdf zadaca=34', 1),
(2303, '2010-08-20 14:28:30', 2, 'student/pdf zadaca=34', 1),
(2304, '2010-08-20 14:29:33', 2, 'student/pdf zadaca=34', 1),
(2305, '2010-08-20 15:02:15', 2, 'student/pdf zadaca=33', 1),
(2306, '2010-08-20 15:03:39', 2, 'student/pdf zadaca=33', 1),
(2307, '2010-08-20 15:04:20', 2, 'student/pdf zadaca=33', 1),
(2308, '2010-08-20 15:05:30', 2, 'student/pdf zadaca=33', 1),
(2309, '2010-08-20 15:05:47', 2, 'student/pdf zadaca=33', 1),
(2310, '2010-08-20 15:07:09', 2, 'student/pdf zadaca=33', 1),
(2311, '2010-08-20 15:07:45', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=2', 1),
(2312, '2010-08-20 15:07:49', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2313, '2010-08-20 15:07:50', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=1', 1),
(2314, '2010-08-20 15:07:53', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2315, '2010-08-20 15:07:55', 2, 'student/pdf zadaca=34', 1),
(2316, '2010-08-20 15:11:25', 2, 'student/pdf zadaca=34', 1),
(2317, '2010-08-20 15:11:48', 2, 'student/pdf zadaca=34', 1),
(2318, '2010-08-20 15:14:54', 2, 'student/pdf zadaca=34', 1),
(2319, '2010-08-20 15:15:32', 2, 'student/pdf zadaca=34', 1),
(2320, '2010-08-20 15:16:23', 2, 'student/pdf zadaca=34', 1),
(2321, '2010-08-20 15:19:11', 2, 'student/pdf zadaca=34', 1),
(2322, '2010-08-20 15:20:05', 2, 'student/pdf zadaca=34', 1),
(2323, '2010-08-20 15:20:54', 2, 'student/pdf zadaca=34', 1),
(2324, '2010-08-20 15:21:09', 2, 'student/pdf zadaca=34', 1),
(2325, '2010-08-20 15:21:48', 2, 'student/pdf zadaca=34', 1),
(2326, '2010-08-20 15:22:45', 2, 'student/pdf zadaca=34', 1),
(2327, '2010-08-20 15:23:14', 2, 'student/pdf zadaca=34', 1),
(2328, '2010-08-20 15:24:16', 2, 'student/pdf zadaca=34', 1),
(2329, '2010-08-20 15:25:33', 2, 'student/pdf zadaca=34', 1),
(2330, '2010-08-20 15:26:22', 2, 'student/pdf zadaca=34', 1),
(2331, '2010-08-20 15:27:50', 2, 'logout', 1),
(2332, '2010-08-20 15:28:04', 1, 'login', 1),
(2333, '2010-08-20 15:28:04', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2334, '2010-08-20 15:28:05', 1, 'saradnik/intro', 1),
(2335, '2010-08-20 15:28:07', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2336, '2010-08-20 15:28:09', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2337, '2010-08-20 15:28:58', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=Zadaca 12 zadataka=4 bodova=4 day=21 month=8 year=2010 sat=15 minuta=28 sekunda=09 aktivna=on attachment=1 dozvoljene_eks=zip', 1),
(2338, '2010-08-20 15:28:58', 1, 'kreirana nova zadaca z35', 2),
(2339, '2010-08-20 15:29:00', 1, 'logout', 1),
(2340, '2010-08-20 15:33:08', 2, 'login', 1),
(2341, '2010-08-20 15:33:08', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2342, '2010-08-20 15:33:10', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2343, '2010-08-20 15:33:12', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=1', 1),
(2344, '2010-08-20 15:33:32', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=35 zadatak=1 labgrupa=', 1),
(2345, '2010-08-20 15:33:32', 2, 'poslana zadaca z35 zadatak 1 (attachment)', 2),
(2346, '2010-08-20 15:33:35', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=2', 1),
(2347, '2010-08-20 15:33:48', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=35 zadatak=2 labgrupa=', 1),
(2348, '2010-08-20 15:33:48', 2, 'poslana zadaca z35 zadatak 2 (attachment)', 2),
(2349, '2010-08-20 15:33:49', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=3', 1),
(2350, '2010-08-20 15:34:16', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=35 zadatak=3 labgrupa=', 1),
(2351, '2010-08-20 15:34:16', 2, 'poslana zadaca z35 zadatak 3 (attachment)', 2),
(2352, '2010-08-20 15:34:23', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=4', 1),
(2353, '2010-08-20 15:34:36', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=35 zadatak=4 labgrupa=', 1),
(2354, '2010-08-20 15:34:36', 2, 'poslana zadaca z35 zadatak 4 (attachment)', 2),
(2355, '2010-08-20 15:34:38', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2356, '2010-08-20 15:34:42', 2, 'student/pdf zadaca=35', 1),
(2357, '2010-08-20 15:35:53', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=1', 1),
(2358, '2010-08-20 15:35:56', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=2', 1),
(2359, '2010-08-20 15:35:58', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=3', 1),
(2360, '2010-08-20 15:36:01', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=4', 1),
(2361, '2010-08-20 15:36:49', 2, 'student/pdf zadaca=35', 1),
(2362, '2010-08-20 15:38:30', 2, 'student/pdf zadaca=35', 1),
(2363, '2010-08-20 15:38:48', 2, 'student/pdf zadaca=35', 1),
(2364, '2010-08-20 15:39:57', 2, 'student/pdf zadaca=35', 1),
(2365, '2010-08-20 15:51:54', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2366, '2010-08-20 15:51:57', 2, 'student/pdf zadaca=35', 1),
(2367, '2010-08-20 15:52:55', 2, 'student/pdf zadaca=35', 1),
(2368, '2010-08-20 15:53:29', 2, 'student/pdf zadaca=35', 1),
(2369, '2010-08-20 15:54:39', 2, 'student/pdf zadaca=35', 1),
(2370, '2010-08-20 16:03:20', 2, 'student/pdf zadaca=35', 1),
(2371, '2010-08-20 16:04:11', 2, 'student/pdf zadaca=35', 1),
(2372, '2010-08-20 16:05:11', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=1', 1),
(2373, '2010-08-20 16:05:14', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=2', 1),
(2374, '2010-08-20 16:05:24', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2375, '2010-08-20 16:05:25', 2, 'student/pdf zadaca=34', 1),
(2376, '2010-08-20 16:07:10', 2, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(2377, '2010-08-20 16:07:13', 2, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(2378, '2010-08-20 16:07:18', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2379, '2010-08-20 16:07:21', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=1', 1),
(2380, '2010-08-20 16:07:23', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=2', 1),
(2381, '2010-08-20 16:07:25', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=1', 1),
(2382, '2010-08-20 16:07:28', 2, 'student/zadaca predmet=1 ag=1 zadaca=30 zadatak=2', 1),
(2383, '2010-08-20 16:07:30', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2384, '2010-08-20 16:07:32', 2, 'student/pdf zadaca=30', 1),
(2385, '2010-08-20 16:20:32', 2, 'student/pdf zadaca=34', 1),
(2386, '2010-08-20 16:21:01', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2387, '2010-08-20 16:21:01', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2388, '2010-08-20 16:21:02', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2389, '2010-08-20 16:21:02', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2390, '2010-08-20 16:21:02', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2391, '2010-08-20 16:21:02', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2392, '2010-08-20 16:21:02', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2393, '2010-08-20 16:21:02', 2, 'student/zadaca predmet=1 ag=1 zadaca=33 zadatak=1', 1),
(2394, '2010-08-20 16:21:02', 2, 'student/zadaca predmet=1 ag=1', 1),
(2395, '2010-08-20 16:21:09', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2396, '2010-08-20 16:21:12', 2, 'student/zadaca predmet=1 ag=1 zadaca=32 zadatak=1', 1),
(2397, '2010-08-20 16:21:31', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=32 zadatak=1 labgrupa=', 1),
(2398, '2010-08-20 16:21:31', 2, 'greska kod attachmenta (zadaca z32, varijabla program je: c:/wamp/tmpphp273A.tmp)', 3),
(2399, '2010-08-20 16:21:34', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2400, '2010-08-20 16:21:38', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=1', 1),
(2401, '2010-08-20 16:22:00', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=35 zadatak=1 labgrupa=', 1),
(2402, '2010-08-20 16:22:00', 2, 'greska kod attachmenta (zadaca z35, varijabla program je: c:/wamp/tmpphp9883.tmp)', 3),
(2403, '2010-08-20 16:22:03', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2404, '2010-08-20 16:22:05', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=1', 1),
(2405, '2010-08-20 16:22:24', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=34 zadatak=1 labgrupa=', 1),
(2406, '2010-08-20 16:22:24', 2, 'poslana zadaca z34 zadatak 1 (attachment)', 2),
(2407, '2010-08-20 16:22:32', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2408, '2010-08-20 16:22:36', 2, 'student/pdf zadaca=34', 1),
(2409, '2010-08-20 16:23:31', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=1', 1),
(2410, '2010-08-20 16:27:45', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2411, '2010-08-20 16:28:30', 2, 'logout', 1),
(2412, '2010-08-20 16:28:45', 1, 'login', 1),
(2413, '2010-08-20 16:28:45', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2414, '2010-08-20 16:28:49', 1, 'saradnik/intro', 1),
(2415, '2010-08-20 16:28:56', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2416, '2010-08-20 16:30:58', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2417, '2010-08-20 16:32:04', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2418, '2010-08-20 16:32:13', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(2419, '2010-08-20 16:32:16', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2420, '2010-08-20 16:32:18', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(2421, '2010-08-20 16:32:28', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2422, '2010-08-20 16:32:35', 1, 'student/pdf zadaca=1', 1),
(2423, '2010-08-20 16:32:37', 1, 'student/pdf zadaca=1', 1),
(2424, '2010-08-20 16:32:39', 1, 'student/pdf zadaca=1', 1),
(2425, '2010-08-20 16:32:41', 1, 'student/pdf zadaca=1', 1),
(2426, '2010-08-20 16:32:42', 1, 'student/pdf zadaca=1', 1),
(2427, '2010-08-20 16:32:49', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2428, '2010-08-20 16:32:51', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2429, '2010-08-20 16:32:52', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(2430, '2010-08-20 16:33:01', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2431, '2010-08-20 16:33:04', 1, 'student/pdf zadaca=1', 1),
(2432, '2010-08-20 16:33:06', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(2433, '2010-08-20 16:33:21', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2434, '2010-08-20 16:34:07', 1, 'izvjestaj/predmet predmet=1 ag=1', 1),
(2435, '2010-08-20 16:35:20', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2436, '2010-08-20 16:35:22', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2437, '2010-08-20 16:54:48', 1, 'logout', 1),
(2438, '2010-08-20 16:55:03', 2, 'login', 1),
(2439, '2010-08-20 16:55:03', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2440, '2010-08-20 16:56:40', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2441, '2010-08-20 17:10:47', 2, 'student/pdf zadaca=34', 1),
(2442, '2010-08-20 17:12:33', 2, 'student/pdf zadaca=35', 1),
(2443, '2010-08-20 17:13:32', 2, 'student/pdf zadaca=35', 1),
(2444, '2010-08-20 17:13:53', 2, 'student/pdf zadaca=35', 1),
(2445, '2010-08-20 17:15:08', 2, 'student/pdf zadaca=34', 1),
(2446, '2010-08-20 18:10:46', 2, 'student/pdf zadaca=35', 1),
(2447, '2010-08-20 21:19:08', 1, 'login', 1),
(2448, '2010-08-20 21:19:08', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2449, '2010-08-20 21:19:10', 1, 'saradnik/intro', 1),
(2450, '2010-08-20 21:19:12', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2451, '2010-08-20 21:19:18', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2452, '2010-08-20 21:36:50', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2453, '2010-08-20 21:36:58', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2454, '2010-08-20 21:43:28', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2455, '2010-08-20 21:43:49', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2456, '2010-08-20 21:45:21', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2457, '2010-08-20 21:45:26', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2458, '2010-08-20 21:45:28', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2459, '2010-08-20 21:45:30', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2460, '2010-08-20 21:49:34', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2461, '2010-08-20 21:49:45', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2462, '2010-08-20 21:50:00', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2463, '2010-08-20 21:50:03', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2464, '2010-08-20 21:50:05', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2465, '2010-08-20 21:50:06', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2466, '2010-08-20 21:50:07', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2467, '2010-08-20 21:54:22', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2468, '2010-08-20 21:54:32', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2469, '2010-08-20 21:56:58', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2470, '2010-08-20 21:57:52', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2471, '2010-08-20 22:45:22', 1, 'logout', 1),
(2472, '2010-08-20 22:45:29', 2, 'login', 1),
(2473, '2010-08-20 22:45:29', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2474, '2010-08-20 22:45:32', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2475, '2010-08-20 23:00:45', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=1', 1),
(2476, '2010-08-20 23:00:52', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2477, '2010-08-21 00:10:26', 2, 'student/pdf zadaca=34', 1),
(2478, '2010-08-21 00:11:15', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=1', 1),
(2479, '2010-08-21 00:11:30', 2, 'logout', 1),
(2480, '2010-08-21 00:11:40', 1, 'login', 1),
(2481, '2010-08-21 00:11:40', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2482, '2010-08-21 00:11:41', 1, 'saradnik/intro', 1),
(2483, '2010-08-21 00:11:43', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2484, '2010-08-21 00:11:44', 1, 'nastavnik/zadace predmet=1 ag=1', 1),
(2485, '2010-08-21 00:12:14', 1, 'nastavnik/zadace predmet=1 ag=1 akcija=edit zadaca=0 naziv=ZadacaPdf zadataka=1 bodova=1 day=22 month=8 year=2010 sat=00 minuta=11 sekunda=44 aktivna=on attachment=1 dozvoljene_eks=zip,pdf', 1),
(2486, '2010-08-21 00:12:14', 1, 'kreirana nova zadaca z36', 2),
(2487, '2010-08-21 00:12:16', 1, 'logout', 1),
(2488, '2010-08-21 00:12:22', 2, 'login', 1),
(2489, '2010-08-21 00:12:22', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2490, '2010-08-21 00:12:24', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2491, '2010-08-21 00:12:24', 2, 'SQL greska (studentpredmet.php : 59):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 1', 3),
(2492, '2010-08-21 00:14:13', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2493, '2010-08-21 00:15:35', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2494, '2010-08-21 00:16:57', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2495, '2010-08-21 00:18:14', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1);
INSERT INTO `log` (`id`, `vrijeme`, `userid`, `dogadjaj`, `nivo`) VALUES 
(2496, '2010-08-21 00:18:59', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2497, '2010-08-21 00:19:29', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2498, '2010-08-21 00:24:53', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2499, '2010-08-21 00:24:56', 2, 'student/pdf zadaca=36', 1),
(2500, '2010-08-21 00:26:17', 2, 'student/pdf zadaca=35', 1),
(2501, '2010-08-21 00:31:53', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2502, '2010-08-21 00:32:13', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2503, '2010-08-21 00:32:36', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2504, '2010-08-21 00:33:44', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2505, '2010-08-21 00:39:32', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2506, '2010-08-21 00:40:16', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2507, '2010-08-21 00:41:44', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2508, '2010-08-21 00:42:21', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2509, '2010-08-21 00:42:44', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2510, '2010-08-21 00:43:00', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2511, '2010-08-21 00:43:36', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2512, '2010-08-21 00:44:00', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2513, '2010-08-21 00:44:01', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2514, '2010-08-21 00:44:20', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2515, '2010-08-21 00:44:35', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2516, '2010-08-21 00:44:57', 2, 'student/zadaca akcija=slanje predmet=1 ag=1 zadaca=36 zadatak=1 labgrupa=', 1),
(2517, '2010-08-21 00:44:57', 2, 'poslana zadaca z36 zadatak 1 (attachment)', 2),
(2518, '2010-08-21 00:45:01', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2519, '2010-08-21 00:45:05', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2520, '2010-08-21 00:47:22', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2521, '2010-08-21 00:47:24', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2522, '2010-08-21 00:48:28', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2523, '2010-08-21 00:48:49', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2524, '2010-08-21 00:49:00', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2525, '2010-08-21 00:49:02', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2526, '2010-08-21 00:49:14', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2527, '2010-08-21 00:50:31', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2528, '2010-08-21 00:50:34', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2529, '2010-08-21 00:51:11', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2530, '2010-08-21 00:51:23', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2531, '2010-08-21 00:51:26', 2, 'common/attachment zadaca=36 zadatak=1', 1),
(2532, '2010-08-21 00:51:41', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2533, '2010-08-21 00:51:43', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2534, '2010-08-21 01:05:56', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2535, '2010-08-21 01:06:07', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2536, '2010-08-21 01:06:09', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2537, '2010-08-21 01:06:13', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2538, '2010-08-21 01:06:18', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2539, '2010-08-21 01:08:17', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2540, '2010-08-21 01:09:59', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2541, '2010-08-21 01:10:50', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2542, '2010-08-21 01:15:06', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2543, '2010-08-21 01:16:27', 2, 'student/pdf zadaca=35', 1),
(2544, '2010-08-21 11:21:34', 2, 'login', 1),
(2545, '2010-08-21 11:21:34', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2546, '2010-08-21 11:45:33', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2547, '2010-08-21 11:45:41', 2, 'student/pdf zadaca=35', 1),
(2548, '2010-08-21 12:10:40', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2549, '2010-08-21 12:10:42', 2, 'common/attachment zadaca=36 zadatak=1', 1),
(2550, '2010-08-21 12:10:46', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2551, '2010-08-21 12:10:49', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2552, '2010-08-21 12:14:14', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2553, '2010-08-21 12:14:19', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2554, '2010-08-21 12:14:48', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2555, '2010-08-21 12:14:54', 2, 'common/attachment zadaca=36 zadatak=1', 1),
(2556, '2010-08-21 12:15:08', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2557, '2010-08-21 12:15:26', 2, 'common/attachment zadaca=36 zadatak=1', 1),
(2558, '2010-08-21 12:15:32', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2559, '2010-08-21 12:15:34', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2560, '2010-08-21 12:15:40', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2561, '2010-08-21 12:16:11', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2562, '2010-08-21 12:16:23', 2, 'common/attachment zadaca=36 zadatak=1', 1),
(2563, '2010-08-21 12:18:50', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2564, '2010-08-21 12:18:53', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2565, '2010-08-21 12:30:19', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2566, '2010-08-21 12:30:21', 2, 'common/attachment zadaca=36 zadatak=1', 1),
(2567, '2010-08-21 12:40:56', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2568, '2010-08-21 12:40:58', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2569, '2010-08-21 12:43:48', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2570, '2010-08-21 12:43:50', 2, 'common/attachment zadaca=36 zadatak=1', 1),
(2571, '2010-08-21 12:44:10', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2572, '2010-08-21 12:44:11', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2573, '2010-08-21 12:45:55', 2, 'logout', 1),
(2574, '2010-08-21 12:53:35', 2, 'login', 1),
(2575, '2010-08-21 12:53:35', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(2576, '2010-08-21 12:53:38', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2577, '2010-08-21 12:55:21', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2578, '2010-08-21 12:56:07', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2579, '2010-08-21 12:56:10', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2580, '2010-08-21 12:57:07', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2581, '2010-08-21 12:57:47', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2582, '2010-08-21 12:59:18', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2583, '2010-08-21 12:59:21', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2584, '2010-08-21 12:59:55', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2585, '2010-08-21 13:00:42', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2586, '2010-08-21 13:02:56', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2587, '2010-08-21 13:09:21', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2588, '2010-08-21 13:09:41', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2589, '2010-08-21 13:10:26', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2590, '2010-08-21 13:13:31', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2591, '2010-08-21 13:13:56', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2592, '2010-08-21 13:14:35', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2593, '2010-08-21 13:16:17', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2594, '2010-08-21 13:16:31', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2595, '2010-08-21 13:17:32', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2596, '2010-08-21 13:17:52', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2597, '2010-08-21 13:18:28', 2, 'student/pdf zadaca=36', 1),
(2598, '2010-08-21 13:18:47', 2, 'student/pdf zadaca=36', 1),
(2599, '2010-08-21 13:19:07', 2, 'student/pdf zadaca=36', 1),
(2600, '2010-08-21 13:19:35', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2601, '2010-08-21 13:19:37', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2602, '2010-08-21 13:22:28', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2603, '2010-08-21 13:22:49', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2604, '2010-08-21 13:23:01', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2605, '2010-08-21 13:23:52', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2606, '2010-08-21 13:23:57', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2607, '2010-08-21 13:24:33', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2608, '2010-08-21 13:25:15', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2609, '2010-08-21 13:25:43', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2610, '2010-08-21 13:28:07', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2611, '2010-08-21 13:28:19', 2, 'student/zadaca predmet=1 ag=1 zadaca=34 zadatak=1', 1),
(2612, '2010-08-21 13:28:21', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2613, '2010-08-21 13:28:42', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2614, '2010-08-21 13:29:54', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2615, '2010-08-21 13:30:02', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2616, '2010-08-21 13:30:04', 2, 'common/attachment zadaca=36 zadatak=1', 1),
(2617, '2010-08-21 13:30:46', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2618, '2010-08-21 13:30:48', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2619, '2010-08-21 13:30:52', 2, 'common/attachment zadaca=36 zadatak=1&gt;', 1),
(2620, '2010-08-21 13:32:25', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2621, '2010-08-21 13:32:29', 2, 'common/attachment zadaca=36 zadatak=1&gt;', 1),
(2622, '2010-08-21 13:32:39', 2, 'student/pdf zadaca=35', 1),
(2623, '2010-08-21 13:34:28', 2, 'student/zadaca predmet=1 ag=1 zadaca=4 zadatak=1', 1),
(2624, '2010-08-21 13:34:46', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=1', 1),
(2625, '2010-08-21 13:34:54', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2626, '2010-08-21 13:38:41', 2, 'student/zadaca predmet=1 ag=1 zadaca=35 zadatak=1', 1),
(2627, '2010-08-21 13:38:43', 2, 'student/zadaca predmet=1 ag=1 zadaca=4 zadatak=1', 1),
(2628, '2010-08-21 13:38:44', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2629, '2010-08-21 13:38:46', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2630, '2010-08-21 13:38:49', 2, 'common/attachment zadaca=36 zadatak=5', 1),
(2631, '2010-08-21 13:38:49', 2, 'ne postoji attachment (zadaca 36 zadatak 5 student 2)', 3),
(2632, '2010-08-21 13:39:09', 2, 'common/attachment zadaca=36 zadatak=5', 1),
(2633, '2010-08-21 13:39:09', 2, 'ne postoji attachment (zadaca 36 zadatak 5 student 2)', 3),
(2634, '2010-08-21 13:40:27', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2635, '2010-08-21 13:40:30', 2, 'common/attachment zadaca=36 zadatak=0', 1),
(2636, '2010-08-21 13:40:30', 2, 'los poziv (zadaca 36 zadatak 0)', 3),
(2637, '2010-08-21 13:41:33', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2638, '2010-08-21 13:41:54', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2639, '2010-08-21 13:42:38', 2, 'student/zadaca predmet=1 ag=1 zadaca=36 zadatak=1', 1),
(2640, '2010-08-21 13:42:42', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2641, '2010-08-21 13:43:21', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2642, '2010-08-21 13:47:40', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2643, '2010-08-21 13:47:51', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2644, '2010-08-21 13:48:22', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2645, '2010-08-21 13:48:26', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2646, '2010-08-21 13:48:32', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2647, '2010-08-21 13:49:06', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2648, '2010-08-21 13:49:47', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2649, '2010-08-21 13:50:16', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2650, '2010-08-21 13:52:55', 2, 'logout', 1),
(2651, '2010-08-21 13:53:04', 1, 'login', 1),
(2652, '2010-08-21 13:53:04', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2653, '2010-08-21 13:53:17', 1, 'saradnik/intro', 1),
(2654, '2010-08-21 13:53:18', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2655, '2010-08-21 13:53:21', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2656, '2010-08-21 13:56:28', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2657, '2010-08-21 13:57:26', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2658, '2010-08-21 13:59:09', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2659, '2010-08-21 14:00:32', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2660, '2010-08-21 14:00:41', 1, 'izvjestaj/statistika_predmeta predmet=1 ag=1', 1),
(2661, '2010-08-21 14:01:06', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2662, '2010-08-21 14:01:07', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2663, '2010-08-21 14:01:15', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2664, '2010-08-21 14:01:44', 1, 'student/pdf zadaca=1', 1),
(2665, '2010-08-21 14:10:28', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2666, '2010-08-21 14:10:36', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2667, '2010-08-21 14:10:36', 1, 'nastavnik/pdfConverter.php', 1),
(2668, '2010-08-21 14:10:39', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2669, '2010-08-21 14:10:39', 1, 'nastavnik/pdfConverter.php', 1),
(2670, '2010-08-21 14:12:39', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2671, '2010-08-21 14:12:41', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2672, '2010-08-21 14:12:41', 1, 'nastavnik/pdfConverter.php', 1),
(2673, '2010-08-21 14:14:15', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2674, '2010-08-21 14:14:16', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2675, '2010-08-21 14:14:16', 1, 'nastavnik/pdfConverter.php', 1),
(2676, '2010-08-21 14:14:19', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2677, '2010-08-21 14:14:19', 1, 'nastavnik/pdfConverter.php', 1),
(2678, '2010-08-21 14:14:21', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2679, '2010-08-21 14:14:21', 1, 'nastavnik/pdfConverter.php', 1),
(2680, '2010-08-21 14:14:21', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2681, '2010-08-21 14:14:21', 1, 'nastavnik/pdfConverter.php', 1),
(2682, '2010-08-21 14:14:22', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2683, '2010-08-21 14:14:22', 1, 'nastavnik/pdfConverter.php', 1),
(2684, '2010-08-21 14:14:22', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2685, '2010-08-21 14:14:22', 1, 'nastavnik/pdfConverter.php', 1),
(2686, '2010-08-21 14:14:22', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2687, '2010-08-21 14:14:22', 1, 'nastavnik/pdfConverter.php', 1),
(2688, '2010-08-21 14:14:22', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2689, '2010-08-21 14:14:22', 1, 'nastavnik/pdfConverter.php', 1),
(2690, '2010-08-21 14:14:23', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2691, '2010-08-21 14:14:23', 1, 'nastavnik/pdfConverter.php', 1),
(2692, '2010-08-21 14:14:23', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2693, '2010-08-21 14:14:23', 1, 'nastavnik/pdfConverter.php', 1),
(2694, '2010-08-21 14:14:23', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2695, '2010-08-21 14:14:23', 1, 'nastavnik/pdfConverter.php', 1),
(2696, '2010-08-21 14:14:23', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2697, '2010-08-21 14:14:23', 1, 'nastavnik/pdfConverter.php', 1),
(2698, '2010-08-21 14:14:23', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter.php', 3),
(2699, '2010-08-21 14:14:23', 1, 'nastavnik/pdfConverter.php', 1),
(2700, '2010-08-21 14:14:26', 1, 'student/pdf zadaca=1', 1),
(2701, '2010-08-21 14:15:38', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2702, '2010-08-21 14:15:40', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2703, '2010-08-21 14:15:40', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2704, '2010-08-21 14:15:49', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2705, '2010-08-21 14:15:49', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2706, '2010-08-21 14:15:50', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2707, '2010-08-21 14:15:50', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2708, '2010-08-21 14:15:50', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2709, '2010-08-21 14:15:50', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2710, '2010-08-21 14:15:50', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2711, '2010-08-21 14:15:50', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2712, '2010-08-21 14:15:50', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2713, '2010-08-21 14:15:50', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2714, '2010-08-21 14:15:50', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2715, '2010-08-21 14:15:50', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2716, '2010-08-21 14:15:51', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2717, '2010-08-21 14:15:51', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2718, '2010-08-21 14:15:54', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2719, '2010-08-21 14:15:54', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2720, '2010-08-21 14:15:54', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2721, '2010-08-21 14:15:54', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2722, '2010-08-21 14:15:54', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2723, '2010-08-21 14:15:54', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2724, '2010-08-21 14:15:55', 1, 'student/pdf zadaca=1', 1),
(2725, '2010-08-21 14:15:55', 1, 'student/pdf zadaca=1', 1),
(2726, '2010-08-21 14:15:55', 1, 'student/pdf zadaca=1', 1),
(2727, '2010-08-21 14:15:57', 1, 'student/pdf zadaca=1', 1),
(2728, '2010-08-21 14:15:58', 1, 'student/pdf zadaca=1', 1),
(2729, '2010-08-21 14:15:58', 1, 'student/pdf zadaca=1', 1),
(2730, '2010-08-21 14:15:58', 1, 'student/pdf zadaca=1', 1),
(2731, '2010-08-21 14:16:20', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2732, '2010-08-21 14:16:22', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2733, '2010-08-21 14:16:22', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2734, '2010-08-21 14:16:26', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2735, '2010-08-21 14:16:26', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2736, '2010-08-21 14:16:26', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2737, '2010-08-21 14:16:26', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2738, '2010-08-21 14:16:27', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2739, '2010-08-21 14:16:27', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2740, '2010-08-21 14:16:27', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2741, '2010-08-21 14:16:27', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2742, '2010-08-21 14:16:28', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2743, '2010-08-21 14:16:28', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2744, '2010-08-21 14:16:28', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2745, '2010-08-21 14:16:28', 1, 'nastavnik/pdfConverter zadaca=1', 1),
(2746, '2010-08-21 14:17:09', 1, 'logout', 1),
(2747, '2010-08-21 14:17:17', 4, 'login', 1),
(2748, '2010-08-21 14:17:17', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(2749, '2010-08-21 14:17:19', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2750, '2010-08-21 14:17:28', 4, 'student/pdf zadaca=1', 1),
(2751, '2010-08-21 14:20:29', 4, 'logout', 1),
(2752, '2010-08-21 14:20:41', 1, 'login', 1),
(2753, '2010-08-21 14:20:41', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2754, '2010-08-21 14:20:44', 1, 'saradnik/intro', 1),
(2755, '2010-08-21 14:20:46', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2756, '2010-08-21 14:20:48', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2757, '2010-08-21 14:20:52', 1, 'student/pdf zadaca=1', 1),
(2758, '2010-08-21 14:20:57', 1, 'student/pdf zadaca=1', 1),
(2759, '2010-08-21 14:21:03', 1, 'student/pdf zadaca=1', 1),
(2760, '2010-08-21 14:21:04', 1, 'student/pdf zadaca=1', 1),
(2761, '2010-08-21 14:21:05', 1, 'student/pdf zadaca=1', 1),
(2762, '2010-08-21 14:21:05', 1, 'student/pdf zadaca=1', 1),
(2763, '2010-08-21 14:21:05', 1, 'student/pdf zadaca=1', 1),
(2764, '2010-08-21 14:21:06', 1, 'student/pdf zadaca=1', 1),
(2765, '2010-08-21 14:21:06', 1, 'student/pdf zadaca=1', 1),
(2766, '2010-08-21 14:21:23', 1, 'student/pdf zadaca=1', 1),
(2767, '2010-08-21 14:21:25', 1, 'student/pdf zadaca=1', 1),
(2768, '2010-08-21 14:21:25', 1, 'student/pdf zadaca=1', 1),
(2769, '2010-08-21 14:21:28', 1, 'logout', 1),
(2770, '2010-08-21 14:21:32', 4, 'login', 1),
(2771, '2010-08-21 14:21:32', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(2772, '2010-08-21 14:21:35', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2773, '2010-08-21 14:23:42', 4, 'logout', 1),
(2774, '2010-08-21 14:23:48', 1, 'login', 1),
(2775, '2010-08-21 14:23:48', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2776, '2010-08-21 14:23:50', 1, 'saradnik/intro', 1),
(2777, '2010-08-21 14:23:52', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2778, '2010-08-21 14:23:55', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2779, '2010-08-21 14:23:58', 1, 'student/pdf zadaca=1', 1),
(2780, '2010-08-21 14:23:59', 1, 'student/pdf zadaca=1', 1),
(2781, '2010-08-21 14:24:10', 1, 'student/pdf zadaca=1', 1),
(2782, '2010-08-21 14:24:32', 1, 'student/pdf zadaca=1', 1),
(2783, '2010-08-21 14:24:40', 1, 'student/pdf zadaca=1', 1),
(2784, '2010-08-21 14:24:40', 1, 'student/pdf zadaca=1', 1),
(2785, '2010-08-21 14:24:40', 1, 'student/pdf zadaca=1', 1),
(2786, '2010-08-21 14:24:41', 1, 'student/pdf zadaca=1', 1),
(2787, '2010-08-21 14:26:18', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2788, '2010-08-21 14:26:20', 1, 'izvjestaj/grupe predmet=1 ag=1 target=', 1),
(2789, '2010-08-21 14:26:41', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2790, '2010-08-21 14:26:44', 1, 'izvjestaj/grupe predmet=1 ag=1 target=', 1),
(2791, '2010-08-21 14:26:46', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2792, '2010-08-21 14:26:49', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2793, '2010-08-21 14:26:51', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2794, '2010-08-21 14:40:05', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2795, '2010-08-21 14:40:44', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2796, '2010-08-21 14:41:47', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2797, '2010-08-21 14:41:49', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2798, '2010-08-21 14:43:46', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2799, '2010-08-21 14:44:08', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2800, '2010-08-21 14:49:21', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2801, '2010-08-21 14:49:21', 1, 'SQL greska (avnikizvjestaji.php : 38):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 1', 3),
(2802, '2010-08-21 14:49:22', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2803, '2010-08-21 14:49:23', 1, 'saradnik/intro', 1),
(2804, '2010-08-21 14:51:41', 1, 'logout', 1),
(2805, '2010-08-21 14:51:46', 0, 'index.php greska: Pogrešna šifra admin ', 3),
(2806, '2010-08-21 14:51:55', 1, 'login', 1),
(2807, '2010-08-21 14:51:55', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2808, '2010-08-21 14:51:57', 1, 'student/intro', 1),
(2809, '2010-08-21 14:52:06', 1, 'login', 1),
(2810, '2010-08-21 14:52:06', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2811, '2010-08-21 14:52:08', 1, 'saradnik/intro', 1),
(2812, '2010-08-21 14:52:09', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2813, '2010-08-21 14:52:12', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2814, '2010-08-21 14:52:12', 1, 'SQL greska (avnikizvjestaji.php : 38):You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 1', 3),
(2815, '2010-08-21 14:52:35', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2816, '2010-08-21 14:52:38', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2817, '2010-08-21 14:54:32', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2818, '2010-08-21 14:54:34', 1, 'student/pdf zadaca=', 1),
(2819, '2010-08-21 14:54:46', 1, 'student/pdf zadaca=', 1),
(2820, '2010-08-21 14:55:18', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2821, '2010-08-21 14:55:19', 1, 'student/pdf zadaca=1', 1),
(2822, '2010-08-21 14:56:18', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2823, '2010-08-21 14:56:20', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2824, '2010-08-21 14:57:18', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2825, '2010-08-21 14:59:17', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2826, '2010-08-21 15:03:15', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2827, '2010-08-21 15:03:18', 1, 'student/pdf zadaca=1', 1),
(2828, '2010-08-21 15:05:00', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2829, '2010-08-21 15:05:02', 1, 'pristup nepostojecom modulu nastavnik/pdfConverter', 3),
(2830, '2010-08-21 15:05:02', 1, 'nastavnik/pdfConverter predmet=1 ag=1 grupa=2 target=', 1),
(2831, '2010-08-21 15:05:37', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2832, '2010-08-21 15:05:38', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2833, '2010-08-21 15:07:52', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2834, '2010-08-21 15:09:11', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2835, '2010-08-21 15:09:12', 1, 'pristup nepostojecom modulu nastavnik/pdf_converter', 3),
(2836, '2010-08-21 15:09:12', 1, 'nastavnik/pdf_converter predmet=1 ag=1 grupa=2 target=', 1),
(2837, '2010-08-21 15:09:22', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2838, '2010-08-21 15:09:53', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2839, '2010-08-21 15:11:11', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2840, '2010-08-21 15:11:13', 1, 'pristup nepostojecom modulu izvjestaj/pdf_converter', 3),
(2841, '2010-08-21 15:11:13', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2 target=', 1),
(2842, '2010-08-21 15:29:26', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2843, '2010-08-21 15:29:28', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2844, '2010-08-21 15:29:29', 1, 'pristup nepostojecom modulu izvjestaj/pdf_converter', 3),
(2845, '2010-08-21 15:29:29', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2846, '2010-08-21 15:31:19', 1, 'pristup nepostojecom modulu izvjestaj/pdf_converter', 3),
(2847, '2010-08-21 15:31:19', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2848, '2010-08-21 15:36:03', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2849, '2010-08-21 15:36:04', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2850, '2010-08-21 15:36:04', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2851, '2010-08-21 15:41:21', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2852, '2010-08-21 15:41:24', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2853, '2010-08-21 15:41:24', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2854, '2010-08-21 15:41:42', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2855, '2010-08-21 15:41:55', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2856, '2010-08-21 15:41:56', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2857, '2010-08-21 15:41:59', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2858, '2010-08-21 15:42:00', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2859, '2010-08-21 15:42:01', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2860, '2010-08-21 15:43:31', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2861, '2010-08-21 15:43:33', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2862, '2010-08-21 15:43:33', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2863, '2010-08-21 15:44:01', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2864, '2010-08-21 15:44:07', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2865, '2010-08-21 15:44:10', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2866, '2010-08-21 15:45:18', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2867, '2010-08-21 15:45:20', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2868, '2010-08-21 15:45:58', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2869, '2010-08-21 15:45:59', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2870, '2010-08-21 15:46:44', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2871, '2010-08-21 15:46:45', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2872, '2010-08-21 15:51:37', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2873, '2010-08-21 15:53:33', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2874, '2010-08-21 15:54:14', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2875, '2010-08-21 15:54:15', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2876, '2010-08-21 15:57:07', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2877, '2010-08-21 15:57:09', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2878, '2010-08-21 15:59:46', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2879, '2010-08-21 15:59:47', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2880, '2010-08-21 15:59:47', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2881, '2010-08-21 16:01:34', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2882, '2010-08-21 16:01:35', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2883, '2010-08-21 16:01:35', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2884, '2010-08-21 16:01:54', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2885, '2010-08-21 16:01:55', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2886, '2010-08-21 16:01:57', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2887, '2010-08-21 16:01:57', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2888, '2010-08-21 16:02:52', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2889, '2010-08-21 16:02:53', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2890, '2010-08-21 16:02:53', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2891, '2010-08-21 16:03:21', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2892, '2010-08-21 16:03:23', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2893, '2010-08-21 16:03:23', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2894, '2010-08-21 16:03:40', 1, 'login', 1),
(2895, '2010-08-21 16:03:40', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2 loginforma=1 login=admin', 1),
(2896, '2010-08-21 16:03:42', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2897, '2010-08-21 16:03:42', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2898, '2010-08-21 16:04:18', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2899, '2010-08-21 16:04:19', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2900, '2010-08-21 16:04:19', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2901, '2010-08-21 16:04:25', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2902, '2010-08-21 16:04:26', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2903, '2010-08-21 16:04:48', 1, '/zamger41/index.php?', 1),
(2904, '2010-08-21 16:04:53', 1, 'logout', 1),
(2905, '2010-08-21 16:04:57', 1, 'login', 1),
(2906, '2010-08-21 16:04:57', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2907, '2010-08-21 16:04:59', 1, 'saradnik/intro', 1),
(2908, '2010-08-21 16:05:01', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2909, '2010-08-21 16:05:03', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2910, '2010-08-21 16:05:04', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2911, '2010-08-21 16:05:04', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2912, '2010-08-21 16:05:54', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2913, '2010-08-21 16:05:56', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2914, '2010-08-21 16:06:19', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2915, '2010-08-21 16:06:31', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2916, '2010-08-21 16:06:35', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2917, '2010-08-21 16:07:36', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2918, '2010-08-21 16:07:38', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2919, '2010-08-21 16:07:38', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2920, '2010-08-21 16:10:00', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2921, '2010-08-21 16:10:02', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2922, '2010-08-21 16:10:04', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2923, '2010-08-21 16:10:05', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2924, '2010-08-21 16:10:06', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2925, '2010-08-21 16:10:17', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2926, '2010-08-21 16:10:17', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2927, '2010-08-21 16:13:21', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2928, '2010-08-21 16:13:23', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2929, '2010-08-21 16:13:23', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(2930, '2010-08-21 16:13:50', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2931, '2010-08-21 16:13:53', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2932, '2010-08-21 16:14:39', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2933, '2010-08-21 16:14:41', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2934, '2010-08-21 16:14:44', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2935, '2010-08-21 16:16:23', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2936, '2010-08-21 16:16:26', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2937, '2010-08-21 16:17:57', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2938, '2010-08-21 16:18:00', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2939, '2010-08-21 16:18:41', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2940, '2010-08-21 16:18:43', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2941, '2010-08-21 16:18:56', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2942, '2010-08-21 16:18:58', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2943, '2010-08-21 16:19:25', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2944, '2010-08-21 16:19:27', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2945, '2010-08-21 16:20:54', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2946, '2010-08-21 16:20:56', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2947, '2010-08-21 16:22:02', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2948, '2010-08-21 16:22:52', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2949, '2010-08-21 16:22:53', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2950, '2010-08-21 16:22:55', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2951, '2010-08-21 16:22:57', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2952, '2010-08-21 16:23:30', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2953, '2010-08-21 16:23:31', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2954, '2010-08-21 16:24:05', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2955, '2010-08-21 16:24:06', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2956, '2010-08-21 16:24:23', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2957, '2010-08-21 16:24:25', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2958, '2010-08-21 16:25:04', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2959, '2010-08-21 16:25:11', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2960, '2010-08-21 16:25:42', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2961, '2010-08-21 16:25:45', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2962, '2010-08-21 16:26:33', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2963, '2010-08-21 16:26:36', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2964, '2010-08-21 16:27:00', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2965, '2010-08-21 16:29:35', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2966, '2010-08-21 16:29:38', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2967, '2010-08-21 16:35:41', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2968, '2010-08-21 16:35:43', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2969, '2010-08-21 16:35:49', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2970, '2010-08-21 16:36:25', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2971, '2010-08-21 16:36:27', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2972, '2010-08-21 16:37:39', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2973, '2010-08-21 16:37:41', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2974, '2010-08-21 16:38:36', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2975, '2010-08-21 16:39:19', 1, 'logout', 1),
(2976, '2010-08-21 16:39:28', 4, 'login', 1),
(2977, '2010-08-21 16:39:28', 4, '/zamger41/index.php?loginforma=1 login=muris', 1),
(2978, '2010-08-21 16:39:30', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2979, '2010-08-21 16:39:35', 4, 'student/zadaca predmet=1 ag=1 zadaca=1 zadatak=1', 1),
(2980, '2010-08-21 16:39:37', 4, 'common/attachment zadaca=1 zadatak=1', 1),
(2981, '2010-08-21 16:39:58', 4, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(2982, '2010-08-21 16:47:38', 4, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2983, '2010-08-21 16:48:09', 4, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2984, '2010-08-21 16:48:38', 4, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2985, '2010-08-21 16:51:54', 4, 'Korisnik 4 (tip S) pokusao pristupiti izvjestaj/grupe sto zahtijeva NBA', 3),
(2986, '2010-08-21 16:51:54', 4, 'index.php greska: Pristup nije dozvoljen muris izvjestaj/grupe', 3),
(2987, '2010-08-21 16:51:54', 4, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2988, '2010-08-21 16:56:30', 4, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(2989, '2010-08-21 16:57:31', 4, 'Korisnik 4 (tip S) pokusao pristupiti izvjestaj/grupe sto zahtijeva NBA', 3),
(2990, '2010-08-21 16:57:31', 4, 'index.php greska: Pristup nije dozvoljen muris izvjestaj/grupe', 3),
(2991, '2010-08-21 16:57:31', 4, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2992, '2010-08-21 16:57:39', 4, 'logout', 1),
(2993, '2010-08-21 16:57:44', 1, 'login', 1),
(2994, '2010-08-21 16:57:44', 1, '/zamger41/index.php?loginforma=1 login=admin', 1),
(2995, '2010-08-21 16:57:45', 1, 'saradnik/intro', 1),
(2996, '2010-08-21 16:57:46', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(2997, '2010-08-21 16:57:49', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(2998, '2010-08-21 16:57:51', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(2999, '2010-08-21 16:57:53', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3000, '2010-08-21 16:59:10', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3001, '2010-08-21 16:59:38', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3002, '2010-08-21 16:59:55', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3003, '2010-08-21 17:00:16', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3004, '2010-08-21 17:00:49', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3005, '2010-08-21 17:01:08', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3006, '2010-08-21 17:01:54', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3007, '2010-08-21 17:02:34', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3008, '2010-08-21 17:02:35', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(3009, '2010-08-21 17:03:16', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3010, '2010-08-21 17:03:53', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(3011, '2010-08-21 17:03:57', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3012, '2010-08-21 17:04:02', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3013, '2010-08-21 17:06:46', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3014, '2010-08-21 17:08:28', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3015, '2010-08-21 17:08:57', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3016, '2010-08-21 17:09:25', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3017, '2010-08-21 17:09:53', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(3018, '2010-08-21 17:09:55', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3019, '2010-08-21 17:11:25', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3020, '2010-08-21 17:11:26', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(3021, '2010-08-21 17:11:33', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3022, '2010-08-21 17:11:33', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3),
(3023, '2010-08-21 17:19:17', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3024, '2010-08-21 17:26:46', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3025, '2010-08-21 17:28:47', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(3026, '2010-08-21 17:28:47', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3027, '2010-08-21 17:31:28', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3028, '2010-08-21 17:33:28', 1, '/zamger41/index.php?', 1),
(3029, '2010-08-21 17:33:28', 1, '/zamger41/index.php?', 1),
(3030, '2010-08-21 17:33:34', 1, 'saradnik/intro', 1),
(3031, '2010-08-21 17:33:36', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(3032, '2010-08-21 17:33:37', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(3033, '2010-08-21 17:33:39', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(3034, '2010-08-21 17:33:42', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(3035, '2010-08-21 17:35:20', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(3036, '2010-08-21 17:35:22', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3037, '2010-08-21 17:35:52', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3038, '2010-08-21 17:36:16', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3039, '2010-08-21 17:36:34', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3040, '2010-08-21 17:40:16', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3041, '2010-08-21 17:42:16', 1, '/zamger41/index.php?', 1),
(3042, '2010-08-21 17:42:34', 1, 'saradnik/intro', 1),
(3043, '2010-08-21 17:42:35', 1, 'nastavnik/predmet predmet=1 ag=1', 1),
(3044, '2010-08-21 17:42:37', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(3045, '2010-08-21 17:42:40', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(3046, '2010-08-21 17:42:43', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3047, '2010-08-21 17:45:38', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(3048, '2010-08-21 17:45:41', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3049, '2010-08-21 17:47:42', 1, 'izvjestaj/grupe predmet=1 ag=1 grupa=2', 1),
(3050, '2010-08-21 17:49:06', 1, 'nastavnik/izvjestaji predmet=1 ag=1', 1),
(3051, '2010-08-21 17:49:08', 1, 'izvjestaj/pdf_converter predmet=1 ag=1 grupa=2', 1),
(3052, '2010-08-21 17:52:21', 1, 'logout', 1),
(3053, '2010-08-21 17:52:37', 2, 'login', 1),
(3054, '2010-08-21 17:52:37', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(3055, '2010-08-21 17:52:39', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(3056, '2010-08-21 17:52:41', 2, 'student/pdf zadaca=35', 1),
(3057, '2010-08-21 17:53:10', 2, 'student/pdf zadaca=35', 1),
(3058, '2010-08-21 17:53:13', 2, 'student/pdf zadaca=35', 1),
(3059, '2010-08-21 17:55:39', 2, 'login', 1),
(3060, '2010-08-21 17:55:39', 2, '/zamger41/index.php?loginforma=1 login=jasmin', 1),
(3061, '2010-08-21 17:55:41', 2, 'student/predmet predmet=1 ag=1 sm_arhiva=0', 1),
(3062, '2010-08-21 17:55:43', 2, 'student/pdf zadaca=35', 1),
(3063, '2010-08-21 17:56:22', 2, 'student/pdf zadaca=33', 1),
(3064, '2010-08-22 00:04:34', 0, 'index.php greska: Vaša sesija je istekla. Molimo prijavite se ponovo.  izvjestaj/grupe', 3);

-- --------------------------------------------------------

-- 
-- Table structure for table `mjesto`
-- 

CREATE TABLE `mjesto` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(40) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=79 ;

-- 
-- Dumping data for table `mjesto`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `nacin_studiranja`
-- 

CREATE TABLE `nacin_studiranja` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(30) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `nacin_studiranja`
-- 

INSERT INTO `nacin_studiranja` (`id`, `naziv`) VALUES 
(1, 'Redovan'),
(2, 'Paralelan'),
(3, 'Redovan samofinansirajući'),
(4, 'Nepoznat status');

-- --------------------------------------------------------

-- 
-- Table structure for table `nastavnik_predmet`
-- 

CREATE TABLE `nastavnik_predmet` (
  `nastavnik` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `admin` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`nastavnik`,`akademska_godina`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `nastavnik_predmet`
-- 

INSERT INTO `nastavnik_predmet` (`nastavnik`, `akademska_godina`, `predmet`, `admin`) VALUES 
(5, 1, 1, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `odluka`
-- 

CREATE TABLE `odluka` (
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

CREATE TABLE `ogranicenje` (
  `nastavnik` int(11) NOT NULL default '0',
  `labgrupa` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `ogranicenje`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `osoba`
-- 

CREATE TABLE `osoba` (
  `id` int(11) NOT NULL,
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
  `treba_brisati` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `osoba`
-- 

INSERT INTO `osoba` (`id`, `ime`, `prezime`, `email`, `brindexa`, `datum_rodjenja`, `mjesto_rodjenja`, `drzavljanstvo`, `jmbg`, `adresa`, `adresa_mjesto`, `telefon`, `kanton`, `treba_brisati`) VALUES 
(1, 'Site', 'Admin', 'site@admin.com', '', '0000-00-00', 0, '', '', '', 0, '', 0, 0),
(2, 'Jasmin', 'Krčalo', '', '14888', '0000-00-00', 0, '', '', '', 0, '', 0, 0),
(3, 'Fahrudin', 'Halilović', '', '15888', '0000-00-00', 0, '', '', '', 0, '', 0, 0),
(4, 'Muris', 'Agić', '', '14887', '0000-00-00', 0, '', '', '', 0, '', -1, 0),
(5, 'Huse', 'Fatkić', '', '', '0000-00-00', 0, '', '', '', 0, '', 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `plan_studija`
-- 

CREATE TABLE `plan_studija` (
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
-- Table structure for table `ponudakursa`
-- 

CREATE TABLE `ponudakursa` (
  `id` int(11) NOT NULL auto_increment,
  `predmet` int(11) NOT NULL default '0',
  `studij` int(11) NOT NULL default '0',
  `semestar` int(11) NOT NULL default '0',
  `obavezan` tinyint(1) NOT NULL default '0',
  `akademska_godina` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `ponudakursa`
-- 

INSERT INTO `ponudakursa` (`id`, `predmet`, `studij`, `semestar`, `obavezan`, `akademska_godina`) VALUES 
(1, 1, 2, 1, 1, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `poruka`
-- 

CREATE TABLE `poruka` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `poruka`
-- 

INSERT INTO `poruka` (`id`, `tip`, `opseg`, `primalac`, `posiljalac`, `vrijeme`, `ref`, `naslov`, `tekst`) VALUES 
(1, 2, 7, 2, 5, '2010-08-03 23:36:33', 0, 'Usmeni Ispit', 'Kolega Jasmine nisi zadovoljio na usmenom ispitu. Dobili ste 5+'),
(2, 2, 7, 5, 2, '2010-08-03 23:38:25', 1, 'Re: Usmeni Ispit', 'A bre Fatkiću kako je to moguće, ja sam sve uradio.\r\n\r\n'),
(3, 1, 0, 0, 1, '2010-08-05 21:12:36', 0, 'dsjkdjksjdksjkdsjk', 'jdskjkdjskd');

-- --------------------------------------------------------

-- 
-- Table structure for table `predmet`
-- 

CREATE TABLE `predmet` (
  `id` int(11) NOT NULL auto_increment,
  `sifra` varchar(20) collate utf8_slovenian_ci NOT NULL,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `institucija` int(11) NOT NULL default '0',
  `kratki_naziv` varchar(10) collate utf8_slovenian_ci NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  `ects` float NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `predmet`
-- 

INSERT INTO `predmet` (`id`, `sifra`, `naziv`, `institucija`, `kratki_naziv`, `tippredmeta`, `ects`) VALUES 
(1, '1', 'Inženjerska  Matematika 1', 1, 'IM1', 1, 6);

-- --------------------------------------------------------

-- 
-- Table structure for table `predmet_projektni_parametri`
-- 

CREATE TABLE `predmet_projektni_parametri` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL default '0',
  `min_timova` tinyint(3) NOT NULL,
  `max_timova` tinyint(3) NOT NULL,
  `min_clanova_tima` tinyint(3) NOT NULL,
  `max_clanova_tima` tinyint(3) NOT NULL,
  `zakljucani_projekti` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`predmet`,`akademska_godina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `predmet_projektni_parametri`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `preference`
-- 

CREATE TABLE `preference` (
  `korisnik` int(11) NOT NULL,
  `preferenca` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `vrijednost` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`korisnik`,`preferenca`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `preference`
-- 

INSERT INTO `preference` (`korisnik`, `preferenca`, `vrijednost`) VALUES 
(1, 'mass-input-format', '2'),
(1, 'mass-input-separator', '0');

-- --------------------------------------------------------

-- 
-- Table structure for table `prijemni_prijava`
-- 

CREATE TABLE `prijemni_prijava` (
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

CREATE TABLE `prijemni_termin` (
  `id` int(11) NOT NULL auto_increment,
  `akademska_godina` int(11) NOT NULL,
  `datum` date NOT NULL,
  `ciklus_studija` tinyint(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `prijemni_termin`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `prisustvo`
-- 

CREATE TABLE `prisustvo` (
  `student` int(11) NOT NULL default '0',
  `cas` int(11) NOT NULL default '0',
  `prisutan` tinyint(1) NOT NULL default '0',
  `plus_minus` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`student`,`cas`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `prisustvo`
-- 

INSERT INTO `prisustvo` (`student`, `cas`, `prisutan`, `plus_minus`) VALUES 
(2, 1, 0, 0),
(4, 1, 0, 0),
(3, 1, 0, 0),
(2, 2, 1, 0),
(4, 2, 1, 0),
(3, 2, 1, 0),
(2, 3, 1, 0),
(4, 3, 1, 0),
(3, 3, 1, 0),
(2, 4, 1, 0),
(4, 4, 1, 0),
(3, 4, 1, 0),
(2, 5, 1, 0),
(4, 5, 1, 0),
(3, 5, 1, 0),
(2, 6, 1, 0),
(4, 6, 1, 0),
(3, 6, 1, 0),
(2, 7, 1, 0),
(4, 7, 1, 0),
(3, 7, 1, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `privilegije`
-- 

CREATE TABLE `privilegije` (
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
(1, 'nastavnik'),
(2, 'student'),
(3, 'student'),
(4, 'student'),
(5, 'nastavnik');

-- --------------------------------------------------------

-- 
-- Table structure for table `programskijezik`
-- 

CREATE TABLE `programskijezik` (
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
-- Table structure for table `projekat`
-- 

CREATE TABLE `projekat` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL default '0',
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `projekat`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_file`
-- 

CREATE TABLE `projekat_file` (
  `id` int(11) NOT NULL,
  `filename` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `revizija` tinyint(4) NOT NULL,
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  `file` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `projekat_file`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_file_diff`
-- 

CREATE TABLE `projekat_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `projekat_file_diff`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_link`
-- 

CREATE TABLE `projekat_link` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `url` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `projekat_link`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_rss`
-- 

CREATE TABLE `projekat_rss` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `url` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `projekat_rss`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `promjena_odsjeka`
-- 

CREATE TABLE `promjena_odsjeka` (
  `id` int(11) NOT NULL auto_increment,
  `osoba` int(11) NOT NULL,
  `iz_odsjeka` int(11) NOT NULL,
  `u_odsjek` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `promjena_odsjeka`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `promjena_podataka`
-- 

CREATE TABLE `promjena_podataka` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `promjena_podataka`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `ras_sati`
-- 

CREATE TABLE `ras_sati` (
  `idS` tinyint(1) NOT NULL auto_increment,
  `satS` varchar(13) NOT NULL,
  PRIMARY KEY  (`idS`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

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
-- Table structure for table `raspored`
-- 

CREATE TABLE `raspored` (
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
-- Table structure for table `raspored_sala`
-- 

CREATE TABLE `raspored_sala` (
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
-- Table structure for table `raspored_stavka`
-- 

CREATE TABLE `raspored_stavka` (
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
-- Table structure for table `rss`
-- 

CREATE TABLE `rss` (
  `id` varchar(15) collate utf8_slovenian_ci NOT NULL,
  `auth` int(11) NOT NULL,
  `access` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `rss`
-- 

INSERT INTO `rss` (`id`, `auth`, `access`) VALUES 
('sbyP0L0Nen', 2, '0000-00-00 00:00:00'),
('p6r2xwF6vk', 4, '0000-00-00 00:00:00'),
('FwhmtmJu3Y', 1, '0000-00-00 00:00:00'),
('MjQn2zEXsx', 3, '0000-00-00 00:00:00');

-- --------------------------------------------------------

-- 
-- Table structure for table `srednja_ocjene`
-- 

CREATE TABLE `srednja_ocjene` (
  `osoba` int(11) NOT NULL,
  `razred` tinyint(4) NOT NULL,
  `redni_broj` int(1) NOT NULL,
  `ocjena` tinyint(5) NOT NULL,
  `tipocjene` tinyint(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `srednja_ocjene`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `srednja_skola`
-- 

CREATE TABLE `srednja_skola` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=431 ;

-- 
-- Dumping data for table `srednja_skola`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `stdin`
-- 

CREATE TABLE `stdin` (
  `id` bigint(20) NOT NULL auto_increment,
  `zadaca` bigint(20) NOT NULL default '0',
  `redni_broj` int(11) NOT NULL default '0',
  `ulaz` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `stdin`
-- 

INSERT INTO `stdin` (`id`, `zadaca`, `redni_broj`, `ulaz`) VALUES 
(1, 3, 1, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `student_ispit_termin`
-- 

CREATE TABLE `student_ispit_termin` (
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

CREATE TABLE `student_labgrupa` (
  `student` int(11) NOT NULL default '0',
  `labgrupa` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `student_labgrupa`
-- 

INSERT INTO `student_labgrupa` (`student`, `labgrupa`) VALUES 
(2, 2),
(4, 2),
(3, 2);

-- --------------------------------------------------------

-- 
-- Table structure for table `student_predmet`
-- 

CREATE TABLE `student_predmet` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `student_predmet`
-- 

INSERT INTO `student_predmet` (`student`, `predmet`) VALUES 
(2, 1),
(3, 1),
(4, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `student_projekat`
-- 

CREATE TABLE `student_projekat` (
  `student` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`projekat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `student_projekat`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `student_studij`
-- 

CREATE TABLE `student_studij` (
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

INSERT INTO `student_studij` (`student`, `studij`, `semestar`, `akademska_godina`, `nacin_studiranja`, `ponovac`, `odluka`, `plan_studija`) VALUES 
(2, 2, 1, 1, 1, 0, 0, 0),
(4, 2, 1, 1, 1, 0, 0, 0),
(3, 2, 1, 1, 1, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `studentski_modul`
-- 

CREATE TABLE `studentski_modul` (
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
(2, 'student/zadaca', 'Slanje zadaće', 0),
(3, 'izvjestaj/predmet', 'Dnevnik', 1),
(4, 'student/projekti', 'Projekti', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `studentski_modul_predmet`
-- 

CREATE TABLE `studentski_modul_predmet` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `studentski_modul` int(11) NOT NULL,
  `aktivan` tinyint(1) NOT NULL,
  PRIMARY KEY  (`predmet`,`akademska_godina`,`studentski_modul`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `studentski_modul_predmet`
-- 

INSERT INTO `studentski_modul_predmet` (`predmet`, `akademska_godina`, `studentski_modul`, `aktivan`) VALUES 
(1, 1, 1, 1),
(1, 1, 2, 1),
(1, 1, 3, 1),
(1, 1, 4, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `studij`
-- 

CREATE TABLE `studij` (
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
(2, 'Računarstvo i informatika (BSc)', 6, 2, 'RI', 1, 2, 1),
(3, 'Automatika i elektronika (BSc)', 6, 3, 'AE', 1, 2, 1),
(4, 'Elektroenergetika (BSc)', 6, 4, 'EE', 1, 2, 1),
(5, 'Telekomunikacije (BSc)', 6, 5, 'TK', 1, 2, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `tipkomponente`
-- 

CREATE TABLE `tipkomponente` (
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

CREATE TABLE `tippredmeta` (
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

CREATE TABLE `tippredmeta_komponenta` (
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

CREATE TABLE `tipstudija` (
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

CREATE TABLE `ugovoroucenju` (
  `id` int(11) NOT NULL auto_increment,
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `studij` int(11) NOT NULL,
  `semestar` int(5) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `ugovoroucenju`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `ugovoroucenju_izborni`
-- 

CREATE TABLE `ugovoroucenju_izborni` (
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

CREATE TABLE `upis_kriterij` (
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

CREATE TABLE `uspjeh_u_srednjoj` (
  `osoba` int(11) NOT NULL,
  `srednja_skola` int(11) NOT NULL,
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

CREATE TABLE `zadaca` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=37 ;

-- 
-- Dumping data for table `zadaca`
-- 

INSERT INTO `zadaca` (`id`, `naziv`, `predmet`, `akademska_godina`, `zadataka`, `bodova`, `rok`, `aktivna`, `programskijezik`, `attachment`, `dozvoljene_ekstenzije`, `postavka_zadace`, `komponenta`, `vrijemeobjave`) VALUES 
(1, 'Zadaća1', 1, 1, 3, 2, '2010-08-04 16:42:20', 1, 0, 1, NULL, NULL, 6, '2010-08-03 23:43:02'),
(2, 'Zadaća2', 1, 1, 4, 3, '2010-08-04 23:44:47', 1, 0, 0, NULL, NULL, 6, '2010-08-03 23:45:12'),
(3, 'Zadaca3', 1, 1, 3, 2, '2010-10-04 12:34:23', 0, 1, 0, NULL, NULL, 6, '2010-08-04 12:36:49'),
(4, 'Zadaca 4', 1, 1, 2, 2, '2010-10-13 11:49:26', 1, 0, 1, NULL, NULL, 6, '2010-08-13 11:50:01'),
(31, 'Zadaca 8', 1, 1, 2, 4, '2010-10-14 13:38:03', 1, 1, 0, '', '', 6, '2010-08-14 13:38:31'),
(32, 'Zadaca 9', 1, 1, 2, 2, '2010-08-25 13:42:46', 1, 0, 1, 'doc,docx,zip,php', '', 6, '2010-08-14 13:44:13'),
(33, 'Zadaca 10', 1, 1, 1, 1, '2010-08-20 18:27:43', 1, 0, 1, 'zip,rar', '', 6, '2010-08-16 18:28:24'),
(34, 'Zadaca 11', 1, 1, 2, 2, '2010-08-21 17:26:12', 1, 0, 1, 'doc,docx,zip,pdf,php,c', '', 6, '2010-08-20 14:24:23'),
(35, 'Zadaca 12', 1, 1, 4, 4, '2010-08-21 15:28:09', 1, 0, 1, 'zip', '', 6, '2010-08-20 15:28:58'),
(36, 'ZadacaPdf', 1, 1, 1, 1, '2010-08-22 00:11:44', 1, 0, 1, 'zip,pdf', '', 6, '2010-08-21 00:12:14'),
(28, 'Zadaća 5', 1, 1, 2, 2, '2010-09-14 10:24:06', 1, 0, 1, 'doc,docx,zip', '', 6, '2010-08-14 10:27:00'),
(29, 'Zadaca 6', 1, 1, 2, 2, '2010-10-14 12:41:15', 1, 0, 1, 'php', '', 6, '2010-08-14 12:52:51'),
(30, 'Zadaca 7', 1, 1, 2, 1, '2010-09-14 13:27:27', 1, 0, 1, 'php,c', 'KrcaloJasmin.docx', 6, '2010-08-14 13:29:37');

-- --------------------------------------------------------

-- 
-- Table structure for table `zadatak`
-- 

CREATE TABLE `zadatak` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=64 ;

-- 
-- Dumping data for table `zadatak`
-- 

INSERT INTO `zadatak` (`id`, `zadaca`, `redni_broj`, `student`, `status`, `bodova`, `izvjestaj_skripte`, `vrijeme`, `komentar`, `filename`, `userid`) VALUES 
(1, 1, 1, 2, 4, 0, '', '2010-08-03 23:46:39', '', 'Greška.docx', 2),
(2, 1, 1, 2, 4, 0, '', '2010-08-04 11:37:02', '', 'Commtouch-Trend-Report-Q1_2010_0.pdf', 2),
(3, 1, 1, 2, 4, 0, '', '2010-08-04 11:38:42', '', 'Commtouch-Trend-Report-Q1_2010_0.pdf', 2),
(4, 1, 1, 4, 4, 0, '', '2010-08-04 11:46:50', '', 'Agic Muris DZ1 PKKS.pdf', 4),
(5, 1, 2, 4, 4, 0, '', '2010-08-04 11:50:10', '', 'Agic Muris.pdf', 4),
(6, 1, 1, 4, 4, 0, '', '2010-08-04 11:53:41', '', 'Agic Muris 14887 Vjezba5.pdf', 4),
(7, 1, 2, 4, 4, 0, '', '2010-08-04 11:53:58', '', 'Agic Muris.pdf', 4),
(8, 1, 2, 4, 4, 0, '', '2010-08-04 11:58:16', '', 'Agic Muris.pdf', 4),
(9, 1, 2, 4, 2, 0, '', '2010-08-04 12:19:37', 'Prepisao si \r\n', 'Agic Muris.pdf', 5),
(10, 1, 1, 4, 5, 2, '', '2010-08-04 12:20:08', '', 'Agic Muris 14887 Vjezba5.pdf', 5),
(11, 3, 1, 4, 1, 0, '', '2010-08-04 12:37:17', '', '1.c', 4),
(12, 4, 1, 4, 4, 0, '', '2010-08-13 11:50:33', '', 'Agic Muris 14887 Vjezba5.doc', 4),
(43, 33, 1, 2, 4, 0, '', '2010-08-16 23:44:30', '', 'proba.rar', 2),
(42, 33, 1, 2, 4, 0, '', '2010-08-16 23:33:30', '', 'anketa.zip', 2),
(41, 33, 1, 2, 4, 0, '', '2010-08-16 22:38:11', '', 'anketa.zip', 2),
(40, 33, 1, 2, 4, 0, '', '2010-08-16 19:10:09', '', 'proba.rar', 2),
(39, 33, 1, 2, 4, 0, '', '2010-08-16 18:28:53', '', 'anketa.zip', 2),
(38, 32, 1, 2, 4, 0, '', '2010-08-16 18:24:02', '', 'datum.php', 2),
(37, 32, 2, 2, 4, 0, '', '2010-08-14 14:44:03', '', 'KrcaloJasmin.docx', 2),
(36, 32, 2, 2, 4, 0, '', '2010-08-14 13:48:13', '', 'upload_file.php', 2),
(34, 31, 1, 2, 1, 0, '', '2010-08-14 13:39:47', '', '1.c', 2),
(35, 32, 1, 2, 4, 0, '', '2010-08-14 13:47:01', '', 'KrcaloJasmin.docx', 2),
(24, 1, 1, 2, 5, 2, '', '2010-08-14 10:02:13', '', 'Commtouch-Trend-Report-Q1_2010_0.pdf', 5),
(25, 28, 1, 2, 4, 0, '', '2010-08-14 10:29:13', '', 'Zadaca5.docx', 2),
(26, 29, 2, 2, 4, 0, '', '2010-08-14 12:53:25', '', 'anketa.php', 2),
(27, 29, 1, 2, 4, 0, '', '2010-08-14 12:59:47', '', 'anketa.php', 2),
(28, 30, 1, 2, 4, 0, '', '2010-08-14 13:30:58', '', '1.c', 2),
(29, 30, 2, 2, 4, 0, '', '2010-08-14 13:32:12', '', 'upload_file.php', 2),
(30, 29, 1, 2, 5, 1, '', '2010-08-14 13:34:24', 'Dobro je uradjeno', 'anketa.php', 5),
(31, 29, 2, 2, 5, 1, '', '2010-08-14 13:34:52', '', 'anketa.php', 5),
(32, 30, 1, 2, 5, 1, '', '2010-08-14 13:34:59', '', '1.c', 5),
(33, 30, 2, 2, 4, 0.5, '', '2010-08-14 13:35:21', '', 'upload_file.php', 5),
(44, 33, 1, 2, 4, 0, '', '2010-08-16 23:47:43', '', 'anketa.zip', 2),
(45, 33, 1, 2, 4, 0, '', '2010-08-16 23:49:30', '', 'anketa.zip', 2),
(46, 33, 1, 2, 4, 0, '', '2010-08-17 12:41:27', '', 'anketa.zip', 2),
(47, 33, 1, 2, 4, 0, '', '2010-08-17 23:45:25', '', 'proba.rar', 2),
(48, 33, 1, 2, 4, 0, '', '2010-08-17 23:53:41', '', 'anketa.zip', 2),
(49, 33, 1, 3, 4, 0, '', '2010-08-18 00:12:15', '', 'anketa.zip', 3),
(50, 33, 1, 3, 4, 0, '', '2010-08-18 01:30:48', '', '1.zip', 3),
(51, 33, 1, 3, 4, 0, '', '2010-08-18 01:34:02', '', 'anketa.zip', 3),
(52, 33, 1, 2, 5, 1, '', '2010-08-20 10:30:59', '', 'anketa.zip', 5),
(53, 32, 1, 2, 4, 0, '', '2010-08-20 11:20:51', '', 'rar.php', 2),
(54, 32, 1, 2, 4, 0, '', '2010-08-20 11:21:56', '', 'rar.php', 2),
(55, 32, 1, 2, 4, 0, '', '2010-08-20 11:24:24', '', 'rar.php', 2),
(56, 34, 1, 2, 4, 0, '', '2010-08-20 11:28:49', '', '1.zip', 2),
(57, 34, 2, 2, 4, 0, '', '2010-08-20 14:25:25', '', 'anketa.zip', 2),
(58, 35, 1, 2, 4, 0, '', '2010-08-20 15:33:32', '', 'kon1.zip', 2),
(59, 35, 2, 2, 4, 0, '', '2010-08-20 15:33:48', '', 'makezip.zip', 2),
(60, 35, 3, 2, 4, 0, '', '2010-08-20 15:34:16', '', 'firstpage.zip', 2),
(61, 35, 4, 2, 4, 0, '', '2010-08-20 15:34:36', '', '1.zip', 2),
(62, 34, 1, 2, 4, 0, '', '2010-08-20 16:22:24', '', 'Untitled 1.pdf', 2),
(63, 36, 1, 2, 4, 0, '', '2010-08-21 00:44:57', '', 'pdfMake.pdf', 2);

-- --------------------------------------------------------

-- 
-- Table structure for table `zadatakdiff`
-- 

CREATE TABLE `zadatakdiff` (
  `zadatak` bigint(11) NOT NULL default '0',
  `diff` text collate utf8_slovenian_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- 
-- Dumping data for table `zadatakdiff`
-- 

