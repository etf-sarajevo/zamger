-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 24, 2012 at 11:35 PM
-- Server version: 5.1.36
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `zamger`
--

-- --------------------------------------------------------

--
-- Table structure for table `hr_kompetencije`
--

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
-- Dumping data for table `hr_kompetencije`
--

INSERT INTO `hr_kompetencije` (`id`, `fk_osoba`, `jezik`, `razumjevanje`, `govor`, `pisanje`) VALUES
(1, 1, 1, 1, 1, 1),
(2, 1, 11, 2, 3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `hr_mentorstvo`
--

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

CREATE TABLE IF NOT EXISTS `hr_nagrade_priznanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `datum` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  `opis` mediumtext COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=4 ;

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

-- --------------------------------------------------------

--
-- Table structure for table `hr_usavrsavanje`
--

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
  `djevojacko_prezime` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `maternji_jezik` int(11) NOT NULL,
  `vozacka_dozvola` int(11) NOT NULL,
  `mobilni_telefon` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `nacin_stanovanja` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `osoba`
--

INSERT INTO `osoba` (`id`, `ime`, `prezime`, `imeoca`, `prezimeoca`, `imemajke`, `prezimemajke`, `spol`, `email`, `brindexa`, `datum_rodjenja`, `mjesto_rodjenja`, `nacionalnost`, `drzavljanstvo`, `boracke_kategorije`, `jmbg`, `adresa`, `adresa_mjesto`, `telefon`, `kanton`, `treba_brisati`, `strucni_stepen`, `naucni_stepen`, `slika`, `djevojacko_prezime`, `maternji_jezik`, `vozacka_dozvola`, `mobilni_telefon`, `nacin_stanovanja`) VALUES
(1, 'Teo', 'Eterovic', 'sadsadas', 'sasadasd', 'saddsa', 'sadads', '', 'site@admin.com', '122112', '2012-12-12', 79, 6, 6, 0, '12132133123123', 'qqweqw 4', 1, '530594', 4, 0, 0, 0, '', 'Eterovic2', 5, 5, '2147483647', 2);

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_akademska_zvanja`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_akademska_zvanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `izborni_perion` int(11) NOT NULL,
  `period_reizbora` int(11) NOT NULL,
  `skracenica` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

--
-- Dumping data for table `sifrarnik_akademska_zvanja`
--

INSERT INTO `sifrarnik_akademska_zvanja` (`id`, `naziv`, `izborni_perion`, `period_reizbora`, `skracenica`) VALUES
(1, 'akademik', 0, 0, 'prof.'),
(2, 'redovni profesor', 0, 0, 'prof.'),
(3, 'vanredni profesor', 6, 6, 'van. prof.'),
(4, 'docent', 5, 5, 'doc.'),
(5, 'predavač', 5, 5, ''),
(6, 'viši asistent', 5, 5, 'v. ass.'),
(7, 'asistent', 4, 0, 'ass.'),
(8, 'profesor visoke škole', 0, 0, ''),
(9, 'predavač visoke škole', 5, 5, ''),
(10, 'viši umjetnički saradnik', 0, 0, ''),
(11, 'umjetnički saradnik', 0, 0, ''),
(12, 'viši lektor', 0, 0, ''),
(13, 'lektor', 5, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_banka`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_banka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=214 ;

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
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=28 ;

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
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=351 ;

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
  `fk_fascati` int(11) NOT NULL,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_maternji_jezik`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_maternji_jezik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=19 ;

--
-- Dumping data for table `sifrarnik_maternji_jezik`
--

INSERT INTO `sifrarnik_maternji_jezik` (`id`, `naziv`) VALUES
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
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10 ;

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
(7, 'u spcijaliziranoj ustanovi'),
(8, 'drugo'),
(9, 'kod roditelja');

-- --------------------------------------------------------

--
-- Table structure for table `sifrarnik_naucna_oblast`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_naucna_oblast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=359 ;

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
-- Table structure for table `sifrarnik_nivo_jezika`
--

CREATE TABLE IF NOT EXISTS `sifrarnik_nivo_jezika` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=7 ;

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
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=16 ;

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
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=10004 ;

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
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

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
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=5 ;

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
  `naziv` varchar(200) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=9 ;

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
  `naziv` varchar(150) COLLATE utf8_slovenian_ci NOT NULL,
  `fk_naucna_oblast` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=247 ;

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
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=14 ;

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

CREATE TABLE IF NOT EXISTS `sifrarnik_zvanje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `vozacka_dozvola`
--

CREATE TABLE IF NOT EXISTS `vozacka_dozvola` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_osoba` int(11) NOT NULL,
  `fk_vozacki_kategorija` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
