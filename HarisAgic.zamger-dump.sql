-- phpMyAdmin SQL Dump
-- version 2.9.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Apr 14, 2009 at 04:22 PM
-- Server version: 5.0.24
-- PHP Version: 5.1.6
-- 
-- Database: `zamger`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `bb_post`
-- 

CREATE TABLE IF NOT EXISTS `bb_post` (
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

CREATE TABLE IF NOT EXISTS `bb_post_text` (
  `post` int(11) NOT NULL,
  `naslov` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`post`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `bb_tema`
-- 

CREATE TABLE IF NOT EXISTS `bb_tema` (
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

CREATE TABLE IF NOT EXISTS `bl_clanak` (
  `id` int(11) NOT NULL,
  `naslov` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `tekst` text collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `osoba` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `bl_clanak_slika`
-- 

CREATE TABLE IF NOT EXISTS `bl_clanak_slika` (
  `id` int(11) NOT NULL,
  `filename` varchar(100) collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `clanak` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `osoba_tim`
-- 

CREATE TABLE IF NOT EXISTS `osoba_tim` (
  `student` int(11) NOT NULL,
  `tim` int(11) NOT NULL,
  PRIMARY KEY  (`student`,`tim`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `predmet_parametri`
-- 

CREATE TABLE IF NOT EXISTS `predmet_parametri` (
  `predmet` int(11) NOT NULL,
  `min_timova` tinyint(3) NOT NULL,
  `max_timova` tinyint(3) NOT NULL,
  `min_clanova_tima` tinyint(3) NOT NULL,
  `max_clanova_tima` tinyint(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `projekat`
-- 

CREATE TABLE IF NOT EXISTS `projekat` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `tim` int(11) NOT NULL,
  `predmet` int(11) NOT NULL,
  `opis` text collate utf8_slovenian_ci NOT NULL,
  `vrijeme` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_file`
-- 

CREATE TABLE IF NOT EXISTS `projekat_file` (
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

CREATE TABLE IF NOT EXISTS `projekat_file_diff` (
  `file` int(11) NOT NULL,
  `diff` text collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_link`
-- 

CREATE TABLE IF NOT EXISTS `projekat_link` (
  `id` int(11) NOT NULL,
  `url` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `poredak` tinyint(4) NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `projekat_rss`
-- 

CREATE TABLE IF NOT EXISTS `projekat_rss` (
  `id` int(11) NOT NULL,
  `url` varchar(200) collate utf8_slovenian_ci NOT NULL,
  `poredak` tinyint(4) NOT NULL,
  `projekat` int(11) NOT NULL,
  `osoba` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `tim`
-- 

CREATE TABLE IF NOT EXISTS `tim` (
  `id` int(11) NOT NULL,
  `naziv` varchar(200) collate utf8_slovenian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;