-- phpMyAdmin SQL Dump
-- version 3.1.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 09, 2009 at 09:58 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `latitudeRecorder`
--

CREATE TABLE IF NOT EXISTS `latitudeRecorder` (
  `id` int(11) NOT NULL auto_increment,
  `latitude` float(10,6) default NULL,
  `longitude` float(10,6) default NULL,
  `accurency` int(11) default NULL,
  `reversedLocation` varchar(60) default NULL,
  `timestamp` timestamp NULL default NULL,
  `_created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
