-- phpMyAdmin SQL Dump
-- version 2.9.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: May 07, 2009 at 06:39 PM
-- Server version: 5.0.24
-- PHP Version: 5.1.6
-- 
-- Database: `zamger`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `bb_post`
-- 

DROP TABLE IF EXISTS `bb_post`;
CREATE TABLE `bb_post` (
  `id` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  `tema` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `bb_post_text`
-- 

DROP TABLE IF EXISTS `bb_post_text`;
CREATE TABLE `bb_post_text` (
  `post` int(11) NOT NULL,
  `naslov` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`post`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `bb_tema`
-- 

DROP TABLE IF EXISTS `bb_tema`;
CREATE TABLE `bb_tema` (
  `id` int(11) NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `naslov` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `prvi_post` int(11) NOT NULL,
  `zadnji_post` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `bl_clanak`
-- 

DROP TABLE IF EXISTS `bl_clanak`;
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

-- --------------------------------------------------------

-- 
-- Table structure for table `osoba_projekat`
-- 

DROP TABLE IF EXISTS `osoba_projekat`;
CREATE TABLE `osoba_projekat` (
  `student` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`projekat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `predmet_parametri`
-- 

DROP TABLE IF EXISTS `predmet_parametri`;
CREATE TABLE `predmet_parametri` (
  `predmet` int(11) NOT NULL,
  `min_timova` tinyint(3) NOT NULL,
  `max_timova` tinyint(3) NOT NULL,
  `min_clanova_tima` tinyint(3) NOT NULL,
  `max_clanova_tima` tinyint(3) NOT NULL,
  `zakljucani_projekti` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`predmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `projekat`
-- 

DROP TABLE IF EXISTS `projekat`;
CREATE TABLE `projekat` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `predmet` int(11) NOT NULL,
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_file`
-- 

DROP TABLE IF EXISTS `projekat_file`;
CREATE TABLE `projekat_file` (
  `id` int(11) NOT NULL,
  `naslov` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `filename` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `revizija` varchar(10) collate utf8_slovenian_ci NOT NULL,
  `osoba` int(11) NOT NULL,
  `projekat` int(11) NOT NULL,
  `file` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_file_diff`
-- 

DROP TABLE IF EXISTS `projekat_file_diff`;
CREATE TABLE `projekat_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_link`
-- 

DROP TABLE IF EXISTS `projekat_link`;
CREATE TABLE `projekat_link` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `url` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_rss`
-- 

DROP TABLE IF EXISTS `projekat_rss`;
CREATE TABLE `projekat_rss` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `url` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;
