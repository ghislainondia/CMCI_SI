-- ChurchCRM 7.3.6
-- Add family_id to user_usr for house assembly leaders

-- Ajouter la colonne fam_id pour les dirigeants d'assemblée de maison
ALTER TABLE `user_usr`
  ADD COLUMN `fam_id` mediumint(9) unsigned DEFAULT NULL AFTER `group_id`;

-- Ajouter un index pour les performances
CREATE INDEX `idx_user_fam` ON `user_usr` (`fam_id`);

-- Ajouter la contrainte de clé étrangère
ALTER TABLE `user_usr`
  ADD CONSTRAINT `fk_user_family` FOREIGN KEY (`fam_id`) REFERENCES `family_fam` (`fam_ID`) ON DELETE SET NULL;
