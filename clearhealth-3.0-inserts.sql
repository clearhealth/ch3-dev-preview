INSERT INTO `user` ( `user_id` , `username` , `password` , `nickname` , `color` , `person_id` , `disabled` , `default_location_id` ) VALUES ('1', 'admin', 'admin', 'adm', '', NULL , 'no', '0');

INSERT INTO `config` VALUES ('enableCache', '0');
INSERT INTO `vitalSignTemplates` (`vitalSignTemplateId`, `template`) VALUES
(1, '<vitalsTemplate>\r\n        <vital title="height" label="Height" type="text" units="Height"/>\r\n        <vital title="weight" label="Weight" type="text" units="Weight"/>  <vital title="bloodPressure" label="B/P" type="text" />\r\n        <vital title="pulse" label="Pulse" type="text" />\r\n        <vital title="respiration" label="Resp" type="text" />\r\n        <vital title="temperature" label="Temp" type="text" units="Temperature"/>\r\n        <vital title="pulseOxygenation" label="Pulse Ox." type="text" />\r\n</vitalsTemplate>');

INSERT INTO `enumerations` (`enumerationId`, `guid`, `name`, `key`, `active`, `type`, `parentId`, `lft`, `rgt`, `category`, `ormClass`, `ormId`, `ormEditMethod`) VALUES 
(672638, '', 'Menu', 'menu', 1, 0, 0, 0, 0, 'System', 'MenuItem', 0, 'ormEditMethod'),
(672810, '', 'File', 'file', 1, 0, 0, 0, 0, 'System', 'MenuItem', 0, 'ormEditMethod'),
(672811, '', 'Action', 'action', 1, 0, 0, 0, 0, 'System', 'MenuItem', 0, 'ormEditMethod'),
(672812, '', 'Help', 'help', 1, 0, 0, 0, 0, 'System', 'MenuItem', 0, 'ormEditMethod'),
(672817, '', 'Select Patient', 'SelPat', 1, 0, 0, 0, 0, '', 'MenuItem', 0, 'ormEditMethod'),
(672819, '', 'Quit', 'Quit', 1, 0, 0, 0, 0, '', 'MenuItem', 0, 'ormEditMethod'),
(672820, '', 'Print', 'Print', 1, 0, 0, 0, 0, '', 'MenuItem', 0, 'ormEditMethod'),
(672821, '', 'Add Vitals', 'AddVitals', 1, 0, 0, 0, 0, '', 'MenuItem', 0, 'ormEditMethod');

INSERT INTO `enumerationsClosure` (`ancestor`, `descendant`, `depth`, `weight`) VALUES 
(672638, 672638, 0, 0),
(672638, 672810, 1, 0),
(672638, 672811, 1, 0),
(672638, 672812, 1, 0),
(672638, 672817, 2, 0),
(672638, 672819, 2, 0),
(672638, 672820, 2, 0),
(672638, 672821, 2, 0),
(672810, 672810, 0, 0),
(672810, 672817, 1, 0),
(672810, 672819, 1, 0),
(672811, 672811, 0, 0),
(672811, 672820, 1, 0),
(672811, 672821, 1, 0),
(672812, 672812, 0, 0),
(672817, 672817, 0, 0),
(672819, 672819, 0, 0),
(672820, 672820, 0, 0),
(672821, 672821, 0, 0);

INSERT INTO `mainmenu` (`menuId`, `siteSection`, `parentId`, `dynamicKey`, `section`, `displayOrder`, `title`, `action`, `prefix`, `type`, `active`, `typeValue`) VALUES 
('672813', 'all', '0', '', '', 1, 'Menu', '', '', 'submenu', 0, ''),
('672814', 'all', '', '', 'children', 100, 'File', '', '', 'submenu', 0, ''),
('672815', 'all', '', '', 'children', 200, 'Action', '', '', 'submenu', 0, ''),
('672816', 'all', '0', '', '', 900, 'Help', '', '', 'submenu', 0, ''),
('672818', 'all', '0', '', '', 100, 'Select Patient', '', '', 'freeform', 0, ''),
('672822', 'all', '0', '', '', 100, 'Add Vitals', '', '', 'freeform', 0, ''),
('672823', 'all', '0', '', '', 10, 'Print', '', '', 'freeform', 0, ''),
('672824', 'all', '0', '', '', 900, 'Quit', '', '', 'freeform', 0, '');

INSERT INTO `clinicalNoteDefinitions` (`clinicalNoteDefinitionId`, `title`, `clinicalNoteTemplateId`, `active`) VALUES 
(672826, 'Generic Note', 672825, 1);

INSERT INTO `clinicalNoteTemplates` (`clinicalNoteTemplateId`, `name`, `template`, `guid`) VALUES 
(672825, 'General Note', '<GeneralNote> \n	<question label="General"> \n		<dataPoint type="textarea" namespace="general" dbValue="text" value="test" label="General notes"/> \n	</question>\n\n</GeneralNote>', '2ce75a30-9690-11de-8a39-0800200c9a66');

INSERT INTO `patient` (`person_id`, `is_default_provider_primary`, `default_provider`, `record_number`, `employer_name`, `confidentiality`, `specialNeedsNote`, `specialNeedsTranslator`, `teamId`) VALUES 
(523523, 0, 0, 5678, '', 0, '', 0, 0);

INSERT INTO `person` (`person_id`, `salutation`, `last_name`, `first_name`, `middle_name`, `suffix`, `gender`, `initials`, `date_of_birth`, `summary`, `title`, `notes`, `email`, `secondary_email`, `has_photo`, `identifier`, `identifier_type`, `marital_status`, `inactive`, `active`, `primary_practice_id`) VALUES 
(523523, '', 'Clearhealth', 'Test', 'J', '', 1, '', '1970-01-01', '', '', '', '', '', '0', '111223333', 0, 0, 0, 0, 0);
INSERT INTO `sequences` ( `id` ) VALUES ( '1000000' );

INSERT INTO `dashboardComponent` (`dashboardComponentId`, `name`, `systemName`, `content`, `type`) VALUES 
('77F4AC36-1E99-11DE-B788-63A455D89594', 'Clinical Notes', 'clinicalNotes', 'function clinicalNotesJSDashboardComponent() { \r\n	this.grid = false;\r\n}\r\nclinicalNotesJSDashboardComponent.prototype.render = function(currentDC, personId) { \r\n	cnGrid = dashboardInnerLayout.cells(currentDC.cellId).attachGrid();\r\n	cnGrid.setImagePath("../img/");\r\n	cnGrid.setHeader('',Date,Title,Author,Location'');\r\n	cnGrid.setInitWidths("25,*,*,*,0");\r\n	cnGrid.setColTypes("img,ro,ro,ro,ro");\r\n	cnGrid.setSkin("xp");\r\n	cnGrid.attachEvent("onRowDblClicked",\r\n	function (clinicalNoteId) { \r\n		winCNVE = dashboardInnerLayout.dhxWins.createWindow(''windowViewEditId'',60,10,850,550);\r\n		dashboardInnerLayout.dhxWins.setImagePath(getBaseUrl() + "/img/");\r\n		dashboardInnerLayout.dhxWins.setSkin(''clear_silver'');\r\n		winCNVE.setText("View/Edit Clinical Note");\r\n		winCNVE.attachURL(getBaseUrl() + ''/clinical-notes-form/template?clinicalNoteId='' + clinicalNoteId,true);\r\n		winCNVE.attachEvent(''onClose'', function() {currentDC.refresh(dashboardInnerLayout.cells(currentDC.cellId));return true;});\r\n		winCNVE.setModal(true);\r\n		winCNVE.centerOnScreen();\r\n		\r\n	});\r\n	cnGrid.init();\r\n	cnGrid.load(getBaseUrl() + ''/clinical-notes.raw/list-notes?personId=''+personId,null,"json");\r\n}\r\nclinicalNotesJSDashboardComponent.prototype.refresh = function(currentDC, personId) {  \r\n	cnGrid.clearAll();\r\n	cnGrid.load(getBaseUrl() + ''/clinical-notes.raw/list-notes?personId='' + personId,null,"json");\r\n}\r\nclinicalNotesJSDashboardComponent.prototype.actionLink = function(currentDC) {\r\n	return false;\r\n}\r\nclinicalNotesJSDashboardComponent.prototype.actionLinkDisplay = function(strcallerCellId) {\r\n	return false\r\n}', 'JS');

