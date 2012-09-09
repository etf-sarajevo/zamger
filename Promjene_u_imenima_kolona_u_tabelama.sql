-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 08, 2012 at 11:51 PM
-- Server version: 5.5.24-log
-- PHP Version: 5.4.3

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
-- Table structure for table `defaultne_vrijednosti_plate`
--

CREATE TABLE IF NOT EXISTS `defaultne_vrijednosti_plate` (
  `koeficijent_opterecenja` float NOT NULL DEFAULT '0.9',
  `koeficijent_broja_studenata` float NOT NULL DEFAULT '0.1',
  `vrijednost_boda` int(11) NOT NULL DEFAULT '150',
  `penziono_i_invalidno_osiguranje` float NOT NULL DEFAULT '0.17',
  `zdravstveno_osiguranje` float NOT NULL DEFAULT '0.12',
  `zaposljavanje_na_teret_osiguranja` float NOT NULL DEFAULT '0.015',
  `broj_radnih_dana_u_mjesecu` int(11) NOT NULL DEFAULT '22',
  `dnevni_topli_obrok` int(11) NOT NULL DEFAULT '16'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `defaultne_vrijednosti_plate`
--

INSERT INTO `defaultne_vrijednosti_plate` (`koeficijent_opterecenja`, `koeficijent_broja_studenata`, `vrijednost_boda`, `penziono_i_invalidno_osiguranje`, `zdravstveno_osiguranje`, `zaposljavanje_na_teret_osiguranja`, `broj_radnih_dana_u_mjesecu`, `dnevni_topli_obrok`) VALUES
(0.9, 0.1, 150, 0.17, 0.12, 0.015, 22, 16);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


ALTER TABLE `predmet` CHANGE `br_predavanja` `sati_predavanja` INT(11) NULL DEFAULT NULL
ALTER TABLE `predmet` CHANGE `br_vjezbi` `sati_vjezbi` INT(11) NULL DEFAULT NULL
ALTER TABLE `predmet` CHANGE `br_tutorijala` `sati_tutorijala` INT(11) NULL DEFAULT NULL



ALTER TABLE  `kolicina_predavanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osoba_id` int(11) NOT NULL,
  `predmet_id` int(11) NOT NULL,
  `labgrupa_id` int(11) NOT NULL,
  `ak_godina` int(11) NOT NULL,
  `sati_predavanja` int(11) NOT NULL DEFAULT '0',
  `sati_vjezbi` int(11) NOT NULL DEFAULT '0',
  `sati_tutorijala` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `osoba_id` (`osoba_id`,`predmet_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=17 ;