-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 15, 2012 at 10:20 PM
-- Server version: 5.1.58
-- PHP Version: 5.3.6-13ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `studybooks`
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `value` text COLLATE utf8_bin NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`name`, `value`, `timestamp`) VALUES
('general.cookiePrefix', '', '2012-02-09 20:21:52'),
('general.imprint', 'In some countries, public web sites require an imprint. You can configure this imprint by editing the configuration property <i>general.imprint</i>.', '2012-02-10 23:56:34'),
('general.isDebugMode', '0', '2011-07-17 22:36:59'),
('general.locale', 'en_US', '2012-02-09 20:29:03'),
('mail.greetings', 'Best regards.', '2012-02-09 17:38:41'),
('mail.welcomeBody', 'Welcome to StudyBooks!', '2012-02-09 17:40:22'),
('security.sessionLength', '18000', '2011-07-23 23:09:04'),
('site.email', 'webmaster@example.com', '2012-02-09 20:34:49'),
('site.title', 'StudyBooks', '2012-02-09 20:34:49');

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `message` text COLLATE utf8_bin NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `nodes`
--

CREATE TABLE IF NOT EXISTS `nodes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentID` int(10) unsigned NOT NULL,
  `order` int(10) unsigned NOT NULL,
  `isLeaf` tinyint(1) NOT NULL,
  `depth` int(10) unsigned NOT NULL,
  `type` enum('folder','book','heading','text','file') COLLATE utf8_bin NOT NULL DEFAULT 'heading',
  `createTime` datetime NOT NULL,
  `editTime` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parentID` (`parentID`,`isLeaf`),
  KEY `order` (`order`),
  KEY `depth` (`depth`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=14 ;

--
-- Dumping data for table `nodes`
--

INSERT INTO `nodes` (`id`, `parentID`, `order`, `isLeaf`, `depth`, `type`, `createTime`, `editTime`, `title`, `timestamp`) VALUES
(1, 0, 0, 0, 0, 'folder', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Schule', '2012-02-14 16:40:31'),
(2, 1, 0, 0, 1, 'folder', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Physik', '2012-02-15 21:00:03'),
(3, 1, 1, 0, 1, 'folder', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Mathematik', '2012-02-15 21:00:03'),
(4, 2, 0, 0, 2, 'book', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Quantenmechanik', '2012-02-15 21:00:29'),
(5, 4, 0, 0, 3, 'heading', '0000-00-00 00:00:00', '2012-02-14 20:40:36', '1 Der Photoeffekt', '2012-02-15 21:00:29'),
(6, 5, 0, 0, 4, 'heading', '0000-00-00 00:00:00', '2012-02-14 20:40:29', '1.1 Das Phänomen', '2012-02-15 21:00:29'),
(7, 5, 1, 0, 4, 'heading', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1.2 Die Energie der Photoelektronen', '2012-02-15 21:00:29'),
(8, 7, 2, 0, 5, 'heading', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'i) Beobachtungen zur Energie der Photoelektronen', '2012-02-15 21:00:29'),
(9, 7, 3, 0, 5, 'heading', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'ii) Einfluss der Intensität des Lichts', '2012-02-15 21:00:29'),
(10, 7, 4, 0, 5, 'heading', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'iii) Einfluss der Wellenlänge λ und der Frequenz f', '2012-02-15 21:00:29'),
(11, 6, 0, 1, 5, 'text', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Versuch: Auslösung von Photoelektronen', '2012-02-15 21:00:29'),
(12, 7, 0, 1, 5, 'text', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Vermutungen', '2012-02-15 21:00:29'),
(13, 7, 1, 1, 5, 'text', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Versuch: Bremsspannung', '2012-02-15 21:00:29');

-- --------------------------------------------------------

--
-- Table structure for table `nodesfile`
--

CREATE TABLE IF NOT EXISTS `nodesfile` (
  `nodeID` int(10) unsigned NOT NULL,
  `fileName` varchar(255) COLLATE utf8_bin NOT NULL,
  `size` bigint(20) NOT NULL,
  `type` varchar(255) COLLATE utf8_bin NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nodeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `nodestext`
--

CREATE TABLE IF NOT EXISTS `nodestext` (
  `nodeID` int(10) unsigned NOT NULL,
  `text` text COLLATE utf8_bin NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nodeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `nodestext`
--

INSERT INTO `nodestext` (`nodeID`, `text`, `timestamp`) VALUES
(11, 'Die Energie dies Lichts löst Photoelektronen aus der Zink-Platte.\r\n\r\nDas Licht besitzt jedoch nicht genügend Energie, um Elektronen aus einer Zinkoxid-Schicht auszulösen.\r\n\r\nFolgerung: Die *Ablöseenergie W<sub>A</sub>*, d.h. die Energie, die benötigt wird, um ein Elektron aus einer Metallschicht abzulösen, ist bei ZnO größer als bei Zn.\r\n\r\nW<sub>A,ZnO</sub> > W<sub>A,Zn</sub>', '2012-02-14 20:49:07'),
(12, 'Vermutungen:\r\n\r\n1. Je größer die Intensität des Lichts, desto größer die Energie der Photoelektronen\r\n\r\n2. Je größer die Ablöseenergie des beleuchteten Materials, desto kleiner die Energie der Photoelektronen.', '2012-02-14 20:41:50'),
(13, 'Das Feld, das aufgrund der Bremsspannung zwischen dem Ring und der Kaliumelektrode entsteht, bremst die Photoelektronen.', '2012-02-14 17:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `key` char(64) COLLATE utf8_bin NOT NULL,
  `startTime` datetime NOT NULL,
  `lastAccessTime` datetime NOT NULL,
  `ip` varchar(255) COLLATE utf8_bin NOT NULL,
  `userAgent` text COLLATE utf8_bin NOT NULL,
  `loggedOut` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`,`startTime`,`lastAccessTime`),
  KEY `loggedOut` (`loggedOut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `email` varchar(255) COLLATE utf8_bin NOT NULL,
  `password` char(64) COLLATE utf8_bin NOT NULL,
  `role` enum('guest','poster','admin') COLLATE utf8_bin NOT NULL DEFAULT 'poster',
  `isBanned` tinyint(1) NOT NULL,
  `createTime` datetime NOT NULL,
  `lastLoginTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `isBanned`, `createTime`, `lastLoginTime`, `timestamp`) VALUES
(1, 'admin', '', '1893c9a30af5dbe8523f5f0ee087d7717500cf491f16b9ed7151d1be142e965c', 'admin', 0, '2012-02-09 20:33:29', '2012-02-15 19:27:31', '2012-02-15 19:27:31');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
