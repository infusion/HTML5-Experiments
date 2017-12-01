

-- Create a new database
CREATE DATABASE advent;

USE advent;

-- Create the table for all the door entries
-- for a certain day (of December) and from a user to another user
CREATE TABLE `door` (
  `DID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DDay` tinyint(3) unsigned DEFAULT NULL,
  `UID_From` smallint(5) unsigned DEFAULT NULL,
  `UID_To` smallint(5) unsigned DEFAULT NULL,
  `DData` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`DID`),
  KEY `UID_From` (`UID_From`),
  KEY `UID_To` (`UID_To`)
) ENGINE=InnoDB;

-- If users have something in a window already for a certain day
-- they can get small gift boxes (that are placed randomly on the screen)
CREATE TABLE `gift` (
  `GID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `GDay` tinyint(3) unsigned NOT NULL,
  `UID_From` smallint(5) unsigned NOT NULL,
  `UID_To` smallint(5) unsigned NOT NULL,
  `GData` text DEFAULT NULL,
  PRIMARY KEY (`GID`),
  KEY `UID_From` (`UID_From`),
  KEY `UID_To` (`UID_To`)
) ENGINE=InnoDB;

-- The user table consists of a name, the password (in simple MD5 form)
-- and a theme, meaning the background image of the calendar. If it is unset, a default is used
CREATE TABLE `user` (
  `UID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `UName` varchar(64) NOT NULL,
  `UPass` varchar(32) NOT NULL,
  `UTheme` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB;
