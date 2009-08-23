-- phpMyAdmin SQL Dump
-- version 3.0.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 17, 2009 at 06:32 PM
-- Server version: 5.0.45
-- PHP Version: 5.2.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `clearhealth`
--

-- --------------------------------------------------------

--
-- Table structure for table `aclModules`
--

CREATE TABLE IF NOT EXISTS `aclModules` (
  `aclModuleId` int(11) NOT NULL,
  `aclModuleName` varchar(32) NOT NULL,
  PRIMARY KEY  (`aclModuleId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `aclPrivileges`
--

CREATE TABLE IF NOT EXISTS `aclPrivileges` (
  `aclPrivilegeId` int(11) NOT NULL,
  `aclResourceId` int(11) NOT NULL,
  `aclPrivilegeName` varchar(32) NOT NULL,
  PRIMARY KEY  (`aclPrivilegeId`),
  UNIQUE KEY `aclResourceId_2` (`aclResourceId`,`aclPrivilegeName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `aclResources`
--

CREATE TABLE IF NOT EXISTS `aclResources` (
  `aclResourceId` int(11) NOT NULL,
  `aclModuleId` int(11) NOT NULL,
  `aclResourceName` varchar(32) NOT NULL,
  PRIMARY KEY  (`aclResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `aclRolePrivileges`
--

CREATE TABLE IF NOT EXISTS `aclRolePrivileges` (
  `aclRoleId` int(11) NOT NULL,
  `aclPrivilegeId` int(11) NOT NULL,
  PRIMARY KEY  (`aclRoleId`,`aclPrivilegeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `aclRoles`
--

CREATE TABLE IF NOT EXISTS `aclRoles` (
  `aclRoleId` int(11) NOT NULL,
  `aclRoleName` varchar(32) NOT NULL,
  PRIMARY KEY  (`aclRoleId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `audits`
--

CREATE TABLE IF NOT EXISTS `audits` (
  `auditId` int(11) NOT NULL,
  `objectClass` varchar(255) NOT NULL,
  `objectId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `message` text NOT NULL,
  `dateTime` datetime NOT NULL,
  PRIMARY KEY  (`auditId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auditSequences`
--

CREATE TABLE IF NOT EXISTS `auditSequences` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auditValues`
--

CREATE TABLE IF NOT EXISTS `auditValues` (
  `auditValueId` int(11) NOT NULL,
  `auditId` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`auditValueId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `configId` varchar(32) NOT NULL,
  `value` varchar(255) default NULL,
  PRIMARY KEY  (`configId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `diagnosisCodesICD`
--

CREATE TABLE IF NOT EXISTS `diagnosisCodesICD` (
  `code` varchar(10) NOT NULL,
  `textShort` varchar(24) default NULL,
  `textLong` varchar(255) default NULL,
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `enumerations`
--

CREATE TABLE IF NOT EXISTS `enumerations` (
  `enumerationId` int(11) NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `key` char(10) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `parentId` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY  (`enumerationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `filterStates`
--

CREATE TABLE IF NOT EXISTS `filterStates` (
  `filterStateId` int(11) NOT NULL,
  `tabName` varchar(50) NOT NULL,
  `providerId` int(11) NOT NULL,
  `roomId` int(11) NOT NULL,
  `dateFilter` date NOT NULL,
  PRIMARY KEY  (`filterStateId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `nsdrDefinitionMethods`
--

CREATE TABLE IF NOT EXISTS `nsdrDefinitionMethods` (
  `uuid` char(36) NOT NULL,
  `methodName` char(50) default NULL,
  `method` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `nsdrDefinitions`
--

CREATE TABLE IF NOT EXISTS `nsdrDefinitions` (
  `uuid` char(36) NOT NULL,
  `namespace` char(255) default NULL,
  `aliasFor` char(255) default NULL,
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `orderId` int(11) NOT NULL,
  `providerId` int(11) default NULL,
  `dateStart` datetime default NULL,
  `dateStop` datetime default NULL,
  `orderText` varchar(255) default NULL,
  `service` varchar(32) default NULL,
  `status` varchar(16) default NULL,
  `eSignatureId` int(11) NOT NULL default '0',
  PRIMARY KEY  (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `problemListComments`
--

CREATE TABLE IF NOT EXISTS `problemListComments` (
  `problemListCommentId` int(11) NOT NULL,
  `problemListId` int(11) NOT NULL,
  `date` date default NULL,
  `comment` varchar(255) default NULL,
  `authorId` int(11) NOT NULL,
  PRIMARY KEY  (`problemListCommentId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `problemLists`
--

CREATE TABLE IF NOT EXISTS `problemLists` (
  `problemListId` int(11) NOT NULL,
  `code` varchar(10) default NULL,
  `codeTextShort` varchar(24) default NULL,
  `dateOfOnset` datetime default NULL,
  `service` varchar(255) default NULL,
  `personId` int(11) NOT NULL,
  `providerId` int(11) default NULL,
  `status` char(8) default NULL,
  `immediacy` char(9) default NULL,
  `lastUpdated` datetime default NULL,
  `flags` varchar(12) default NULL,
  `previousStatus` char(8) default NULL,
  PRIMARY KEY  (`problemListId`),
  KEY `personId` (`personId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `templatedText`
--

CREATE TABLE IF NOT EXISTS `templatedText` (
  `templateId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `template` longtext NOT NULL,
  PRIMARY KEY  (`templateId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vitalSignGroups`
--

CREATE TABLE IF NOT EXISTS `vitalSignGroups` (
  `vitalSignGroupId` int(11) NOT NULL,
  `personId` int(11) NOT NULL,
  `dateTime` datetime NOT NULL,
  `enteringUserId` int(11) NOT NULL,
  `visitId` int(11) NOT NULL,
  `vitalSignTemplateId` int(11) NOT NULL,
  PRIMARY KEY  (`vitalSignGroupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Table structure for table `vitalSignTemplates`
--

CREATE TABLE IF NOT EXISTS `vitalSignTemplates` (
  `vitalSignTemplateId` int(11) NOT NULL,
  `template` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vitalSignValueQualifiers`
--

CREATE TABLE IF NOT EXISTS `vitalSignValueQualifiers` (
  `vitalSignValueQualifierId` int(11) NOT NULL,
  `vitalSignValueId` int(11) NOT NULL,
  `qualifier` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`vitalSignValueQualifierId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `vitalSignValues`
--

CREATE TABLE IF NOT EXISTS `vitalSignValues` (
  `vitalSignValueId` int(11) NOT NULL,
  `vitalSignGroupId` int(11) NOT NULL,
  `unavailable` tinyint(4) NOT NULL,
  `refused` tinyint(4) NOT NULL,
  `vital` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `units` char(10) NOT NULL,
  PRIMARY KEY  (`vitalSignValueId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

ALTER TABLE `generic_notes` CHANGE `note` `note` TEXT NOT NULL;
ALTER TABLE `lab_order` ADD `encounter_id` INT NOT NULL AFTER `manual_order_date` ;
ALTER TABLE `problemLists` ADD `codeTextShort` VARCHAR( 24 ) NULL AFTER `code`;
#ALTER TABLE `provider` ADD `color` VARCHAR( 10 ) NOT NULL;
ALTER TABLE `provider` ADD `sureScriptsSPI` VARCHAR( 20 ) NOT NULL;

--
-- Table structure for table `mainmenu`
--

CREATE TABLE IF NOT EXISTS `mainmenu` (
  `menuId` varchar(36) NOT NULL,
  `siteSection` varchar(50) NOT NULL default 'default',
  `parentId` varchar(36) NOT NULL default '0',
  `dynamicKey` varchar(50) NOT NULL,
  `section` enum('children','more','dynamic') NOT NULL default 'children',
  `displayOrder` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `action` varchar(255) NOT NULL default '',
  `prefix` varchar(100) NOT NULL default 'main',
  `type` varchar(20) NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `typeValue` varchar(255) NOT NULL,
  PRIMARY KEY  (`menuId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mainmenu`
--

INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES('0', 'All', '0', '', 'children', 0, 'Menu', '', 'main', 'submenu', 1, '');
INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES('10', 'default', '0', '', 'children', 10, 'File', '', '', 'submenu', 1, '');
INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES('1099', 'default', '10', '', 'children', 1099, 'Quit', 'AuditLog/list', 'main', '', 1, '');
INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES('20', 'default', '0', '', 'children', 20, 'Edit', '', '', 'submenu', 1, '');
INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES('30', 'default', '0', '', 'children', 30, 'View', '', '', 'submenu', 1, '');
INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES('40', 'default', '0', '', 'children', 40, 'Action', '', '', 'submenu', 1, '');
INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES('50', 'default', '0', '', 'children', 50, 'Tools', '', '', 'submenu', 1, '');
INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES('60', 'default', '0', '', 'children', 60, 'Reports', '', '', 'submenu', 1, '');
INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES('70', 'default', '0', '', 'children', 70, 'Help', '', '', 'submenu', 1, '');
CREATE TABLE IF NOT EXISTS `appointments` (
  `appointmentId` int(11) NOT NULL,
  `arrived` tinyint(1) NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `reason` int(11) NOT NULL default '0',
  `walkin` tinyint(1) NOT NULL default '0',
  `createdDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastChangeId` int(11) NOT NULL default '0',
  `lastChangeDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `creatorId` int(11) NOT NULL default '0',
  `practiceId` int(11) NOT NULL default '0',
  `providerId` int(11) NOT NULL default '0',
  `patientId` int(11) NOT NULL default '0',
  `roomId` int(11) NOT NULL default '0',
  `appointmentCode` varchar(255) NOT NULL default '',
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  PRIMARY KEY  (`appointmentId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
