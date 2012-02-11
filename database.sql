-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 12, 2012 at 12:43 AM
-- Server version: 5.1.58
-- PHP Version: 5.3.6-13ubuntu3.5

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
  `type` enum('heading','plain','file') COLLATE utf8_bin NOT NULL DEFAULT 'heading',
  `createTime` datetime NOT NULL,
  `editTime` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parentID` (`parentID`,`isLeaf`),
  KEY `order` (`order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Dumping data for table `nodes`
--

INSERT INTO `nodes` (`id`, `parentID`, `order`, `isLeaf`, `type`, `createTime`, `editTime`, `title`, `timestamp`) VALUES
(1, 0, 0, 0, 'heading', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Schule', '2012-02-11 23:33:38'),
(2, 1, 0, 0, 'heading', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Physik', '2012-02-11 23:35:54'),
(3, 1, 1, 0, 'heading', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Mathematik', '2012-02-11 23:35:54');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=8 ;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `userID`, `key`, `startTime`, `lastAccessTime`, `ip`, `userAgent`, `loggedOut`, `timestamp`) VALUES
(1, 1, '35d60d05a04da0add75ba399cef352e329043116bc7506972f12aec9388825a2', '2012-02-09 20:54:03', '2012-02-09 21:06:49', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0', 1, '2012-02-10 18:06:24'),
(2, 1, '566a9df2f2fea9eae1ef21881ebc471d4a0629f4f6dcfd7e7ef10c7630a5ce64', '2012-02-10 18:06:24', '2012-02-10 23:43:52', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0', 1, '2012-02-10 23:43:52'),
(3, 1, 'eb86742bcfcf50009061da4446c2dc3db7a4fccdd2a0b37dd877a591185d75c2', '2012-02-10 23:50:06', '2012-02-10 23:57:00', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0', 1, '2012-02-10 23:57:00'),
(4, 1, '30e2287e7bb2aa1c4be3d9174eddf089b90c30e1fee4fa8b4bbc0caf438ec826', '2012-02-10 23:57:49', '2012-02-10 23:57:53', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0', 1, '2012-02-10 23:57:53'),
(5, 1, '7a6c21c27faf0073fc59d723bbb8cabfa12a2a0e05b22e4cd76d7b4753fea993', '2012-02-10 23:58:25', '2012-02-10 23:59:36', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0', 1, '2012-02-11 11:52:13'),
(6, 1, 'b32d1f03d7d0c30a8b69119ebea284d9767dc17af9a4aeff8c69d6cede7df227', '2012-02-11 11:52:13', '2012-02-11 13:30:12', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0', 1, '2012-02-11 18:44:01'),
(7, 1, '8b0269ab74487b7b58f5ab135467f0c56cf04b09c1efaa43d7b9afee5692cf33', '2012-02-11 18:44:01', '2012-02-11 23:41:10', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0', 0, '2012-02-11 23:41:10');

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
(1, 'admin', '', '1893c9a30af5dbe8523f5f0ee087d7717500cf491f16b9ed7151d1be142e965c', 'admin', 0, '2012-02-09 20:33:29', '2012-02-11 18:44:01', '2012-02-11 18:44:01');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
