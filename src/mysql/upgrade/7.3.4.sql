-- ChurchCRM 7.3.4
-- Meeting module (Réunions)

CREATE TABLE `meeting_mtg` (
  `mtg_ID` int(11) NOT NULL AUTO_INCREMENT,
  `mtg_Name` varchar(255) NOT NULL,
  `mtg_DateTime` datetime NOT NULL,
  `mtg_OrganizerType` varchar(20) NOT NULL DEFAULT 'church',
  `mtg_OrganizerId` int(11) NOT NULL DEFAULT 0,
  `mtg_Remarks` text DEFAULT NULL,
  `mtg_DateEntered` datetime DEFAULT NULL,
  `mtg_DateLastEdited` datetime DEFAULT NULL,
  `mtg_EnteredBy` smallint(5) unsigned NOT NULL DEFAULT 0,
  `mtg_EditedBy` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`mtg_ID`),
  KEY `idx_meeting_datetime` (`mtg_DateTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `meeting_attendance_mat` (
  `mat_ID` int(11) NOT NULL AUTO_INCREMENT,
  `mat_MeetingId` int(11) NOT NULL,
  `mat_PersonId` int(11) NOT NULL,
  `mat_IsPresent` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`mat_ID`),
  UNIQUE KEY `meeting_person` (`mat_MeetingId`,`mat_PersonId`),
  KEY `mat_MeetingId` (`mat_MeetingId`),
  KEY `mat_PersonId` (`mat_PersonId`),
  CONSTRAINT `fk_mat_meeting` FOREIGN KEY (`mat_MeetingId`) REFERENCES `meeting_mtg` (`mtg_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_mat_person` FOREIGN KEY (`mat_PersonId`) REFERENCES `person_per` (`per_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
