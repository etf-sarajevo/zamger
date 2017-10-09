-- SCHEMA.SQL

-- Ovaj fajl sadrži DB schemu Zamgera. Sama schema nije dovoljna za
-- funkcionisanje sistema, potrebni su i određeni podaci, npr.
-- u mnogim modulima se pretpostavlja da postoji tačno jedna 
-- akademska godina koja je označena kao aktivna. Fajl seed.sql
-- sadrži neke default "demo" podatke.


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
  `pocetak_zimskog_semestra` date NOT NULL,
  `kraj_zimskog_semestra` date NOT NULL,
  `pocetak_ljetnjeg_semestra` date NOT NULL,
  `kraj_ljetnjeg_semestra` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `akademska_godina_predmet`
--

CREATE TABLE IF NOT EXISTS `akademska_godina_predmet` (
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `tippredmeta` int(11) NOT NULL,
  PRIMARY KEY (`akademska_godina`,`predmet`),
  KEY `tippredmeta` (`tippredmeta`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

-- --------------------------------------------------------

--
-- Table structure for table `angazman_status`
--

CREATE TABLE IF NOT EXISTS `angazman_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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

-- --------------------------------------------------------

--
-- Table structure for table `anketa_izbori_pitanja`
--

CREATE TABLE IF NOT EXISTS `anketa_izbori_pitanja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pitanje` int(11) NOT NULL,
  `izbor` text COLLATE utf8_slovenian_ci NOT NULL,
  `dopisani_odgovor` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_dopisani`
--

CREATE TABLE IF NOT EXISTS `anketa_odgovor_dopisani` (
  `rezultat` int(11) NOT NULL,
  `pitanje` int(11) NOT NULL,
  `odgovor` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`rezultat`,`pitanje`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anketa_odgovor_izbori`
--

CREATE TABLE IF NOT EXISTS `anketa_odgovor_izbori` (
  `rezultat` int(11) NOT NULL,
  `pitanje` int(11) NOT NULL,
  `izbor_id` int(11) NOT NULL,
  PRIMARY KEY  (`rezultat`,`pitanje`,`izbor_id`),
  KEY `pitanje` (`pitanje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
  `posljednji_pristup` datetime NOT NULL default '1970-01-01 00:00:00',
  PRIMARY KEY (`id`,`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `bb_post_text`
--

CREATE TABLE IF NOT EXISTS `bb_post_text` (
  `post` int(11) NOT NULL,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `ekstenzije`
--

CREATE TABLE IF NOT EXISTS `ekstenzije` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izvoz_ocjena`
--

CREATE TABLE IF NOT EXISTS `izvoz_ocjena` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`predmet`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izvoz_promjena_podataka`
--

CREATE TABLE IF NOT EXISTS `izvoz_promjena_podataka` (
  `student` int(11) NOT NULL,
  PRIMARY KEY  (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izvoz_upis_prva`
--

CREATE TABLE IF NOT EXISTS `izvoz_upis_prva` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`akademska_godina`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izvoz_upis_semestar`
--

CREATE TABLE IF NOT EXISTS `izvoz_upis_semestar` (
  `student` int(11) NOT NULL,
  `semestar` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`semestar`,`akademska_godina`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kanton`
--

CREATE TABLE IF NOT EXISTS `kanton` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `kratki_naziv` varchar(5) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
  `pasos_predmeta` int(11) default NULL,
  PRIMARY KEY (`student`,`predmet`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `odluka` (`odluka`),
  KEY `predmet` (`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `tip` enum('predavanja','vjezbe','tutorijali','vjezbe+tutorijali') collate utf8_slovenian_ci NOT NULL default 'vjezbe+tutorijali',
  `predmet` int(11) NOT NULL DEFAULT '0',
  `akademska_godina` int(11) NOT NULL DEFAULT '0',
  `virtualna` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `predmet` (`predmet`),
  KEY `akademska_godina` (`akademska_godina`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `log2`
--

CREATE TABLE IF NOT EXISTS `log2` (
  `id` int(11) NOT NULL auto_increment,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `userid` int(11) NOT NULL,
  `modul` int(11) NOT NULL,
  `dogadjaj` int(11) NOT NULL,
  `objekat1` int(11) NOT NULL,
  `objekat2` int(11) NOT NULL,
  `objekat3` int(11) NOT NULL,
  `ipaddress` varchar(16) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `objekat1` (`objekat1`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `log2_blob`
--

CREATE TABLE IF NOT EXISTS `log2_blob` (
  `log2` int(11) NOT NULL,
  `tekst` varchar(255) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`log2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log2_dogadjaj`
--

CREATE TABLE IF NOT EXISTS `log2_dogadjaj` (
  `id` int(11) NOT NULL auto_increment,
  `opis` varchar(255) collate utf8_slovenian_ci NOT NULL,
  `nivo` tinyint(2) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `opis` (`opis`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `log2_modul`
--

CREATE TABLE IF NOT EXISTS `log2_modul` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `naziv` (`naziv`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `mjesto`
--

CREATE TABLE IF NOT EXISTS `mjesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(40) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `drzava` int(11) NOT NULL,
  `opcina_van_bih` varchar(40) collate utf8_slovenian_ci NOT NULL default '',
  PRIMARY KEY (`id`),
  KEY `opcina` (`opcina`),
  KEY `drzava` (`drzava`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `moodle_predmet_id`
--

CREATE TABLE IF NOT EXISTS `moodle_predmet_id` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `moodle_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `nacin_studiranja`
--

CREATE TABLE IF NOT EXISTS `nacin_studiranja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `moguc_upis` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `nacionalnost`
--

CREATE TABLE IF NOT EXISTS `nacionalnost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `naucni_stepen`
--

CREATE TABLE IF NOT EXISTS `naucni_stepen` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `oblast`
--

CREATE TABLE IF NOT EXISTS `oblast` (
  `id` int(11) NOT NULL auto_increment,
  `institucija` int(11) NOT NULL,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `institucija` (`institucija`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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

-- --------------------------------------------------------

--
-- Table structure for table `ogranicenje`
--

CREATE TABLE IF NOT EXISTS `ogranicenje` (
  `nastavnik` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`nastavnik`,`labgrupa`),
  KEY `labgrupa` (`labgrupa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opcina`
--

CREATE TABLE IF NOT EXISTS `opcina` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `osoba`
--

CREATE TABLE IF NOT EXISTS `osoba` (
  `id` int(11) NOT NULL,
  `ime` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `prezime` varchar(30) COLLATE utf8_slovenian_ci NOT NULL,
  `imeoca` varchar(30) COLLATE utf8_slovenian_ci NOT NULL default '',
  `prezimeoca` varchar(30) COLLATE utf8_slovenian_ci NOT NULL default '',
  `imemajke` varchar(30) COLLATE utf8_slovenian_ci NOT NULL default '',
  `prezimemajke` varchar(30) COLLATE utf8_slovenian_ci NOT NULL default '',
  `spol` enum('M','Z','') COLLATE utf8_slovenian_ci NOT NULL default '',
  `brindexa` varchar(10) COLLATE utf8_slovenian_ci NOT NULL default '',
  `datum_rodjenja` date default NULL,
  `mjesto_rodjenja` int(11) default NULL,
  `nacionalnost` int(11) default NULL,
  `drzavljanstvo` int(11) default NULL,
  `boracke_kategorije` tinyint(1) NOT NULL default 0,
  `jmbg` varchar(14) COLLATE utf8_slovenian_ci NOT NULL default '',
  `adresa` varchar(50) COLLATE utf8_slovenian_ci NOT NULL default '',
  `adresa_mjesto` int(11) default NULL,
  `telefon` varchar(15) COLLATE utf8_slovenian_ci NOT NULL default '',
  `kanton` int(11) default NULL,
  `treba_brisati` tinyint(1) NOT NULL DEFAULT '0',
  `strucni_stepen` int(11) NOT NULL DEFAULT '5', -- 5 = srednja strucna sprema
  `naucni_stepen` int(11) NOT NULL DEFAULT '6', -- 6 = bez naucnog stepena
  `slika` varchar(50) COLLATE utf8_slovenian_ci NOT NULL default '',
  `nacin_stanovanja` INT default NULL,
  PRIMARY KEY (`id`),
  KEY `mjesto_rodjenja` (`mjesto_rodjenja`),
  KEY `adresa_mjesto` (`adresa_mjesto`),
  KEY `kanton` (`kanton`),
  KEY `nacionalnost` (`nacionalnost`),
  KEY `drzavljanstvo` (`drzavljanstvo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `osoba_posebne_kategorije`
--

CREATE TABLE IF NOT EXISTS `osoba_posebne_kategorije` (
  `osoba` int(11) NOT NULL,
  `posebne_kategorije` int(11) NOT NULL,
  `br_rjesenja` varchar(128) collate utf8_slovenian_ci NOT NULL,
  `datum_rjesenja` date NOT NULL,
  `organ_izdavanja` varchar(256) collate utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pasos_predmeta`
--

CREATE TABLE IF NOT EXISTS `pasos_predmeta` (
  `id` int(11) NOT NULL auto_increment,
  `predmet` int(11) NOT NULL,
  `usvojen` tinyint(4) NOT NULL,
  `predlozio` int(11) NOT NULL,
  `vrijeme_prijedloga` datetime NOT NULL,
  `komentar_prijedloga` varchar(255) collate utf8_slovenian_ci NOT NULL,
  `sifra` varchar(30) collate utf8_slovenian_ci NOT NULL,
  `naziv` text collate utf8_slovenian_ci NOT NULL,
  `naziv_en` text collate utf8_slovenian_ci NOT NULL,
  `ects` float NOT NULL,
  `sati_predavanja` int(11) NOT NULL,
  `sati_vjezbi` int(11) NOT NULL,
  `sati_tutorijala` int(11) NOT NULL,
  `cilj_kursa` text collate utf8_slovenian_ci NOT NULL,
  `cilj_kursa_en` text collate utf8_slovenian_ci NOT NULL,
  `program` text collate utf8_slovenian_ci NOT NULL,
  `program_en` text collate utf8_slovenian_ci NOT NULL,
  `obavezna_literatura` text collate utf8_slovenian_ci NOT NULL,
  `dopunska_literatura` text collate utf8_slovenian_ci NOT NULL,
  `didakticke_metode` text collate utf8_slovenian_ci NOT NULL,
  `didakticke_metode_en` text collate utf8_slovenian_ci NOT NULL,
  `nacin_provjere_znanja` text collate utf8_slovenian_ci NOT NULL,
  `nacin_provjere_znanja_en` text collate utf8_slovenian_ci NOT NULL,
  `napomene` text collate utf8_slovenian_ci NOT NULL,
  `napomene_en` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci COMMENT='Tip je MyISAM jer sa InnoDB se dobija Error 139' AUTO_INCREMENT=602 ;

-- --------------------------------------------------------

--
-- Table structure for table `plan_izborni_slot`
--

CREATE TABLE IF NOT EXISTS `plan_izborni_slot` (
  `id` int(11) NOT NULL,
  `pasos_predmeta` int(11) NOT NULL,
  PRIMARY KEY  (`id`,`pasos_predmeta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `plan_studija`
--

CREATE TABLE IF NOT EXISTS `plan_studija` (
  `id` int(11) NOT NULL auto_increment,
  `studij` int(11) NOT NULL,
  `godina_vazenja` int(11) default NULL,
  `usvojen` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `studij` (`studij`),
  KEY `godina_vazenja` (`godina_vazenja`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `plan_studija_permisije`
--

CREATE TABLE IF NOT EXISTS `plan_studija_permisije` (
  `plan_studija` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  PRIMARY KEY  (`plan_studija`,`predmet`,`osoba`),
  KEY `predmet` (`predmet`),
  KEY `osoba` (`osoba`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plan_studija_predmet`
--

CREATE TABLE IF NOT EXISTS `plan_studija_predmet` (
  `plan_studija` int(11) NOT NULL,
  `pasos_predmeta` int(11) default NULL,
  `plan_izborni_slot` int(11) default NULL,
  `semestar` tinyint(3) NOT NULL,
  `obavezan` tinyint(1) NOT NULL,
  `potvrdjen` tinyint(1) NOT NULL,
  KEY `plan_studija` (`plan_studija`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `podoblast`
--

CREATE TABLE IF NOT EXISTS `podoblast` (
  `id` int(11) NOT NULL auto_increment,
  `oblast` int(11) NOT NULL,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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

-- --------------------------------------------------------

--
-- Table structure for table `posebne_kategorije`
--

CREATE TABLE IF NOT EXISTS `posebne_kategorije` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `predmet`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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

-- --------------------------------------------------------

--
-- Table structure for table `preduvjeti`
--

CREATE TABLE IF NOT EXISTS `preduvjeti` (
  `predmet` int(11) NOT NULL,
  `preduvjet` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preference`
--

CREATE TABLE IF NOT EXISTS `preference` (
  `korisnik` int(11) NOT NULL,
  `preferenca` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `vrijednost` varchar(100) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `prijemni_termin`
--

CREATE TABLE IF NOT EXISTS `prijemni_termin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `akademska_godina` int(11) NOT NULL,
  `datum` date NOT NULL,
  `ciklus_studija` tinyint(2) NOT NULL,
  `predsjednik_komisije` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prijemni_vazni_datumi`
--

CREATE TABLE IF NOT EXISTS `prijemni_vazni_datumi` (
  `prijemni_termin` int(11) NOT NULL,
  `id_datuma` int(11) NOT NULL,
  `datum` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `privilegije`
--

CREATE TABLE IF NOT EXISTS `privilegije` (
  `osoba` int(11) NOT NULL,
  `privilegija` varchar(30) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `projekat_file_diff`
--

CREATE TABLE IF NOT EXISTS `projekat_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `strucni_stepen` int(11) NOT NULL DEFAULT '5', -- 5 = srednja strucna sprema
  `naucni_stepen` int(11) NOT NULL DEFAULT '6', -- 6 = bez naucnog stepena
  `slika` VARCHAR(50) NOT NULL,
  `vrijeme_zahtjeva` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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

-- --------------------------------------------------------

--
-- Table structure for table `rss`
--

CREATE TABLE IF NOT EXISTS `rss` (
  `id` varchar(15) COLLATE utf8_slovenian_ci NOT NULL,
  `auth` int(11) NOT NULL,
  `access` datetime NOT NULL default '1970-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `savjet_dana`
--

CREATE TABLE IF NOT EXISTS `savjet_dana` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tekst` text COLLATE utf8_slovenian_ci NOT NULL,
  `vrsta_korisnika` enum('nastavnik','student','studentska','siteadmin') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'nastavnik',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `septembar`
--

CREATE TABLE IF NOT EXISTS `septembar` (
  `student` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `srednja_skola`
--

CREATE TABLE IF NOT EXISTS `srednja_skola` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `domaca` tinyint(1) NOT NULL DEFAULT '1',
  `tipskole` enum('GIMNAZIJA','ELEKTROTEHNICKA','TEHNICKA','STRUCNA','MSS','ZANAT') collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `strucni_stepen`
--

CREATE TABLE IF NOT EXISTS `strucni_stepen` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `titula` varchar(15) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `student_ispit_termin`
--

CREATE TABLE IF NOT EXISTS `student_ispit_termin` (
  `student` int(11) NOT NULL,
  `ispit_termin` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`ispit_termin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_labgrupa`
--

CREATE TABLE IF NOT EXISTS `student_labgrupa` (
  `student` int(11) NOT NULL DEFAULT '0',
  `labgrupa` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`student`,`labgrupa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_predmet`
--

CREATE TABLE IF NOT EXISTS `student_predmet` (
  `student` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  PRIMARY KEY (`student`,`predmet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_projekat`
--

CREATE TABLE IF NOT EXISTS `student_projekat` (
  `student` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY (`student`,`projekat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `odluka` int(11) default NULL,
  `plan_studija` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`student`,`studij`,`semestar`,`akademska_godina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `studij`
--

CREATE TABLE IF NOT EXISTS `studij` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(100) COLLATE utf8_slovenian_ci NOT NULL DEFAULT '',
  `institucija` int(11) NOT NULL DEFAULT '0',
  `kratkinaziv` varchar(10) COLLATE utf8_slovenian_ci NOT NULL,
  `moguc_upis` tinyint(1) NOT NULL,
  `tipstudija` int(11) NOT NULL,
  `preduslov` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `svrha_potvrde`
--

CREATE TABLE IF NOT EXISTS `svrha_potvrde` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `tippredmeta`
--

CREATE TABLE IF NOT EXISTS `tippredmeta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(60) COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tippredmeta_komponenta`
--

CREATE TABLE IF NOT EXISTS `tippredmeta_komponenta` (
  `tippredmeta` int(11) NOT NULL,
  `komponenta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipstudija`
--

CREATE TABLE IF NOT EXISTS `tipstudija` (
  `id` int(11) NOT NULL,
  `naziv` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `ciklus` tinyint(2) NOT NULL,
  `trajanje` tinyint(3) NOT NULL,
  `ects` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tip_potvrde`
--

CREATE TABLE IF NOT EXISTS `tip_potvrde` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(100) collate utf8_slovenian_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `ugovoroucenju_izborni`
--

CREATE TABLE IF NOT EXISTS `ugovoroucenju_izborni` (
  `ugovoroucenju` int(11) NOT NULL,
  `predmet` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ugovoroucenju_kapacitet`
--

CREATE TABLE IF NOT EXISTS `ugovoroucenju_kapacitet` (
  `predmet` int(11) NOT NULL,
  `akademska_godina` int(11) NOT NULL,
  `kapacitet` int(11) NOT NULL default '-1' COMMENT '0 = predmet ne ide, -1 = nema ogranicenja',
  `kapacitet_izborni` int(11) NOT NULL default '-1' COMMENT '0 = niko ne moze izabrati, -1 = nema ogranicenja',
  `kapacitet_kolizija` int(11) NOT NULL default '-1' COMMENT '0 - predmet ne ide u koliziji',
  `kapacitet_drugi_odsjek` int(11) NOT NULL default '-1' COMMENT '0 - predmet ne mogu birati sa drugog odsjeka',
  `drugi_odsjek_zabrane` varchar(50) collate utf8_slovenian_ci NOT NULL COMMENT 'ako je prazno mogu svi, u suprotnom spisak odsjeka za koje je zabranjen'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `readonly` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `zadatakdiff`
--

CREATE TABLE IF NOT EXISTS `zadatakdiff` (
  `zadatak` bigint(11) NOT NULL DEFAULT '0',
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`zadatak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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
  `akademska_godina` int(11) NOT NULL,
  `besplatna` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `akademska_godina` (`akademska_godina`),
  KEY `tip_potvrde` (`tip_potvrde`),
  KEY `svrha_potvrde` (`svrha_potvrde`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni`
--

CREATE TABLE IF NOT EXISTS `zavrsni` (
  `id` int(11) NOT NULL auto_increment,
  `naslov` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `podnaslov` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `rad_na_predmetu` int(11) default NULL,
  `akademska_godina` varchar(10) collate utf8_slovenian_ci NOT NULL default '0',
  `kratki_pregled` text collate utf8_slovenian_ci NOT NULL,
  `literatura` text collate utf8_slovenian_ci NOT NULL,
  `sazetak` text collate utf8_slovenian_ci NOT NULL,
  `summary` text collate utf8_slovenian_ci NOT NULL,
  `mentor` int(11) NOT NULL,
  `drugi_mentor` int(11) default NULL,
  `student` int(11) default NULL,
  `kandidat_potvrdjen` tinyint(4) NOT NULL,
  `biljeska` text collate utf8_slovenian_ci NOT NULL,
  `predsjednik_komisije` int(11) default NULL,
  `clan_komisije` int(11) default NULL,
  `clan_komisije2` int(11) default NULL,
  `termin_odbrane` datetime NOT NULL,
  `broj_diplome` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `tema_odobrena` tinyint(4) NOT NULL default '0',
  `sala` varchar(20) collate utf8_slovenian_ci NOT NULL,
  `odluka` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `predmet` (`predmet`),
  KEY `rad_na_predmetu` (`rad_na_predmetu`),
  KEY `student` (`student`),
  KEY `mentor` (`mentor`),
  KEY `drugi_mentor` (`drugi_mentor`),
  KEY `predsjednik_komisije` (`predsjednik_komisije`),
  KEY `clan_komisije` (`clan_komisije`),
  KEY `clan_komisije2` (`clan_komisije2`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci AUTO_INCREMENT=2235 ;


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

-- --------------------------------------------------------

--
-- Table structure for table `zavrsni_file_diff`
--

CREATE TABLE IF NOT EXISTS `zavrsni_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text COLLATE utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `zvanje`
--

CREATE TABLE IF NOT EXISTS `zvanje` (
  `id` int(11) NOT NULL auto_increment,
  `naziv` varchar(50) collate utf8_slovenian_ci NOT NULL,
  `titula` varchar(10) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci ;


CREATE TABLE IF NOT EXISTS `kandidati` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ime` VARCHAR(64) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NOT NULL,
  `prezime` VARCHAR(64) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NOT NULL,
  `ime_oca` VARCHAR(64) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NULL,
  `prezime_oca` VARCHAR(64) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NULL,
  `ime_majke` VARCHAR(64) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NULL,
  `prezime_majke` VARCHAR(64) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NULL,
  `spol` ENUM('M','Z','') CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NULL,
  `datum_rodjenja` DATE NOT NULL,
  `mjesto_rodjenja` INT NOT NULL,
  `nacionalnost` INT NOT NULL,
  `drzavljanstvo` INT NOT NULL,
  `boracka_kategorija` INT NULL,
  `boracka_kategorija_br_rjesenja` VARCHAR(128) NULL,
  `boracka_kategorija_datum_rjesenja` DATE NULL,
  `boracka_kategorija_organ_izdavanja` VARCHAR(256) NULL,
  `jmbg` VARCHAR(64) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NOT NULL,
  `ulica_prebivalista` VARCHAR(100) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NULL,
  `mjesto_prebivalista` VARCHAR(128) NULL,
  `telefon` VARCHAR(15) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NULL,
  `kanton` INT NULL,
  `studijski_program` INT NOT NULL,
  `naziv_skole` VARCHAR(128) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NOT NULL,
  `opcina_skole` INT NOT NULL,
  `strana_skola` TINYINT(1) DEFAULT 0,
  `skolska_godina_zavrsetka` INT NOT NULL,
  `opci_uspjeh` FLOAT NOT NULL,
  `znacajni_predmeti` FLOAT NOT NULL,
  `datum_kreiranja` DATETIME NOT NULL,
  `email` VARCHAR(128) NULL,
  `prijava_potvrdjena` TINYINT(1) DEFAULT 0,
  `podaci_uvezeni` TINYINT(1) DEFAULT 0,
  `osoba` INT NULL,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_slovenian_ci;


CREATE TABLE IF NOT EXISTS `kandidati_ocjene` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `kandidat_id` INT NOT NULL, 
  `naziv_predmeta` VARCHAR(128) CHARACTER SET 'utf8' COLLATE 'utf8_slovenian_ci' NOT NULL,
  `prvi_razred` TINYINT NOT NULL,
  `drugi_razred` TINYINT NOT NULL,
  `treci_razred` TINYINT NOT NULL,
  `cetvrti_razred` TINYINT NOT NULL,
  `kljucni_predmet` TINYINT DEFAULT 0,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_slovenian_ci;


CREATE TABLE IF NOT EXISTS `kandidati_mjesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naziv` varchar(40) COLLATE utf8_slovenian_ci NOT NULL,
  `opcina` int(11) NOT NULL,
  `drzava` int(11) NOT NULL,
  `opcina_van_bih` varchar(40) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `opcina` (`opcina`),
  KEY `drzava` (`drzava`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;



-- --------------------------------------------------------
-- --------------------------------------------------------
-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

--
-- Constraints for table `akademska_godina_predmet`
--
ALTER TABLE `akademska_godina_predmet`
  ADD CONSTRAINT `akademska_godina_predmet_ibfk_11` FOREIGN KEY (`tippredmeta`) REFERENCES `tippredmeta` (`id`),
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

ALTER TABLE `kandidati` ADD FOREIGN KEY (`mjesto_rodjenja`) REFERENCES `kandidati_mjesto`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `kandidati` ADD FOREIGN KEY (`nacionalnost`) REFERENCES `nacionalnost`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `kandidati` ADD FOREIGN KEY (`drzavljanstvo`) REFERENCES `drzava`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kandidati` ADD FOREIGN KEY (`boracka_kategorija`) REFERENCES `posebne_kategorije`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `kandidati` ADD FOREIGN KEY (`opcina_skole`) REFERENCES `opcina`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kandidati` ADD FOREIGN KEY (`studijski_program`) REFERENCES `studij`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kandidati` ADD FOREIGN KEY (`skolska_godina_zavrsetka`) REFERENCES `akademska_godina`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `kandidati_ocjene` ADD FOREIGN KEY (`kandidat_id`) REFERENCES `kandidati`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `kandidati_mjesto` ADD FOREIGN KEY (`opcina`) REFERENCES `opcina`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kandidati_mjesto` ADD FOREIGN KEY (`drzava`) REFERENCES `drzava`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 

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
  ADD CONSTRAINT `zavrsni_ibfk_25` FOREIGN KEY (`clan_komisije2`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_18` FOREIGN KEY (`predmet`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_19` FOREIGN KEY (`rad_na_predmetu`) REFERENCES `predmet` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_20` FOREIGN KEY (`mentor`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_21` FOREIGN KEY (`drugi_mentor`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_22` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_23` FOREIGN KEY (`predsjednik_komisije`) REFERENCES `osoba` (`id`),
  ADD CONSTRAINT `zavrsni_ibfk_24` FOREIGN KEY (`clan_komisije`) REFERENCES `osoba` (`id`);
