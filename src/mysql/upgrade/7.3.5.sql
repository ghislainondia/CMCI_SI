-- ChurchCRM 7.3.5
-- Message de Bertoua module (modules, leçons, notes)

CREATE TABLE `bertoua_btm_module` (
  `btm_ID` int(11) NOT NULL AUTO_INCREMENT,
  `btm_Title` varchar(255) NOT NULL,
  `btm_SortOrder` int(11) NOT NULL DEFAULT 0,
  `btm_DateEntered` datetime DEFAULT NULL,
  `btm_DateLastEdited` datetime DEFAULT NULL,
  PRIMARY KEY (`btm_ID`),
  KEY `idx_bertoua_module_sort` (`btm_SortOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `bertoua_btl_lecon` (
  `btl_ID` int(11) NOT NULL AUTO_INCREMENT,
  `btl_ModuleId` int(11) NOT NULL,
  `btl_Title` varchar(255) NOT NULL,
  `btl_SortOrder` int(11) NOT NULL DEFAULT 0,
  `btl_DateEntered` datetime DEFAULT NULL,
  `btl_DateLastEdited` datetime DEFAULT NULL,
  PRIMARY KEY (`btl_ID`),
  KEY `idx_bertoua_lecon_module` (`btl_ModuleId`),
  KEY `idx_bertoua_lecon_sort` (`btl_SortOrder`),
  CONSTRAINT `fk_btl_module` FOREIGN KEY (`btl_ModuleId`) REFERENCES `bertoua_btm_module` (`btm_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `bertoua_btn_note` (
  `btn_ID` int(11) NOT NULL AUTO_INCREMENT,
  `btn_LeconId` int(11) NOT NULL,
  `btn_PersonId` int(11) NOT NULL,
  `btn_FamId` int(11) NOT NULL DEFAULT 0,
  `btn_Note` text DEFAULT NULL,
  `btn_SaisiePar` smallint(5) unsigned NOT NULL DEFAULT 0,
  `btn_DateSaisie` datetime NOT NULL,
  PRIMARY KEY (`btn_ID`),
  UNIQUE KEY `bertoua_note_lecon_person` (`btn_LeconId`,`btn_PersonId`),
  KEY `btn_LeconId` (`btn_LeconId`),
  KEY `btn_PersonId` (`btn_PersonId`),
  KEY `btn_FamId` (`btn_FamId`),
  CONSTRAINT `fk_btn_lecon` FOREIGN KEY (`btn_LeconId`) REFERENCES `bertoua_btl_lecon` (`btl_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_btn_person` FOREIGN KEY (`btn_PersonId`) REFERENCES `person_per` (`per_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
