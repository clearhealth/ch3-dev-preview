ALTER TABLE `provider` ADD `sureScriptsSPI` VARCHAR( 20 ) NOT NULL;

INSERT INTO `config` VALUES ('enableCache', '0');

ALTER TABLE `generic_notes` CHANGE `note` `note` TEXT NOT NULL;

ALTER TABLE `provider` ADD `color` VARCHAR( 10 ) NOT NULL;

INSERT INTO `enumerations` (`enumerationId`, `uuid`, `name`, `key`, `active`, `type`, `parentId`, `lft`, `rgt`) VALUES
(0, '', 'root', '', 1, 0, 0, 0, 119),
(643, '', 'Demographics', '', 0, 0, 0, 85, 118),
(644, '', 'Vitals', '', 0, 0, 0, 1, 84),
(645, '', 'Gender', '', 0, 0, 643, 98, 117),
(646, '', 'Marital Status', '', 0, 0, 643, 90, 97),
(647, '', 'Confidentiality', '', 0, 0, 643, 86, 89),
(648, '', 'Units', '', 0, 0, 644, 2, 83),
(649, '', 'Default', 'DEFAULT', 0, 0, 647, 87, 88),
(650, '', 'Single', 'S', 0, 0, 646, 95, 96),
(651, '', 'Married', 'M', 0, 0, 646, 93, 94),
(652, '', 'Divorced', 'D', 0, 0, 646, 91, 92),
(705, '', 'Male', 'M', 0, 0, 645, 103, 104),
(706, '', 'Female', 'F', 0, 0, 645, 101, 102),
(707, '', 'Other', 'O', 0, 0, 645, 99, 100),
(708, '', 'Height', '', 0, 0, 648, 37, 40),
(709, '', 'Pain', '', 0, 0, 648, 11, 36),
(710, '', 'Temperature', '', 0, 0, 648, 7, 10),
(711, '', 'Weight', '', 0, 0, 648, 3, 6),
(712, '', 'Lb', 'LB', 0, 0, 711, 4, 5),
(713, '', 'F', 'F', 0, 0, 710, 8, 9),
(714, '', 'l', 'L', 0, 0, 708, 38, 39),
(715, '', '0 - No Pain', '', 0, 0, 709, 34, 35),
(716, '', '1 - Slightly uncomfortable', '', 0, 0, 709, 32, 33),
(717, '', '2', '', 0, 0, 709, 30, 31),
(718, '', '3', '', 0, 0, 709, 28, 29),
(719, '', '4', '', 0, 0, 709, 26, 27),
(720, '', '5', '', 0, 0, 709, 24, 25),
(721, '', '6', '', 0, 0, 709, 22, 23),
(722, '', '7', '', 0, 0, 709, 20, 21),
(723, '', '8', '', 0, 0, 709, 18, 19),
(724, '', '9', '', 0, 0, 709, 16, 17),
(725, '', '10 - Worst imaginable', '', 0, 0, 709, 14, 15),
(726, '', '99 - Unable to respond', '', 0, 0, 709, 12, 13);

INSERT INTO `vitalSignTemplates` (`vitalSignTemplateId`, `template`) VALUES
(1, '<vitalsTemplate>\r\n        <vital title="height" label="Height" type="text" units="Height"/>\r\n        <vital title="weight" label="Weight" type="text" units="Weight"/>  <vital title="bloodPressure" label="B/P" type="text" />\r\n        <vital title="pulse" label="Pulse" type="text" />\r\n        <vital title="respiration" label="Resp" type="text" />\r\n        <vital title="temperature" label="Temp" type="text" units="Temperature"/>\r\n        <vital title="pulseOxygenation" label="Pulse Ox." type="text" />\r\n</vitalsTemplate>');


ALTER TABLE `lab_order` ADD `encounter_id` INT NOT NULL AFTER `manual_order_date` ;
ALTER TABLE `problemLists` ADD `codeTextShort` VARCHAR( 24 ) NULL AFTER `code`;
ALTER TABLE `nsdrDefinitions` ADD `ORMClass` VARCHAR( 64 ) NULL;
ALTER TABLE `encounter` ADD `payer_group_id` INT NULL,
                        ADD `current_payer` INT NULL,
                        ADD `room_id` INT NULL,
                        ADD `practice_id` INT NULL;
ALTER TABLE `attachments` CHANGE `clinicalNoteId` `attachmentReferenceId` INT( 11 ) NOT NULL ;
ALTER TABLE `attachments` CHANGE `attachmentReferenceId` `attachmentReferenceId` CHAR( 36 ) NOT NULL ;
ALTER TABLE `attachments` ADD INDEX ( `attachmentReferenceId` ) ;
ALTER TABLE `clinicalNoteTemplates` ADD `guid` VARCHAR( 50 ) NULL;
ALTER TABLE `clinicalNoteDefinitions` ADD `active` TINYINT NULL;

ALTER TABLE `person` ADD `suffix` VARCHAR( 12 ) NOT NULL AFTER `middle_name` ;
ALTER TABLE `person` ADD `active` TINYINT NOT NULL AFTER `inactive` ;
ALTER TABLE `rooms` ADD `routing_station` CHAR( 4 ) NOT NULL ;
ALTER TABLE `provider` ADD `routing_station` CHAR( 4 ) NOT NULL ;
ALTER TABLE `clinicalNoteDefinitions` ADD `active` TINYINT NOT NULL ;
ALTER TABLE `medications` ADD `pharmacyId` INT NOT NULL ;

ALTER TABLE `config` CHANGE `value` `value` TEXT NULL DEFAULT NULL ;
INSERT INTO `config` (`configId`, `value`) VALUES
('defaultFormulary', 'formularyDefault'),
('formularyDefault', 'O:13:"FormularyItem":3:{s:8:"\0*\0_name";s:16:"formularyDefault";s:12:"\0*\0_isActive";b:1;s:13:"\0*\0_isDefault";b:1;}');

ALTER TABLE `enumerations` CHANGE `uuid` `guid` CHAR( 36 ) NOT NULL ,
ADD `category` VARCHAR( 255 ) NOT NULL ,
ADD `ormClass` VARCHAR( 255 ) NULL ,
ADD `ormId` INT NULL ,
ADD `ormEditMethod` VARCHAR( 64 ) NULL ;
ALTER TABLE `rooms` CHANGE `routing_station` `routing_station` CHAR( 4 ) NOT NULL ;
ALTER TABLE `provider` CHANGE `routing_station` `routing_station` CHAR( 4 ) NOT NULL ;

ALTER TABLE `patient` ADD `teamId` INT NOT NULL ;

ALTER TABLE `audits` ADD `startProcessing` DATETIME NOT NULL ,
ADD `endProcessing` DATETIME NOT NULL ;
