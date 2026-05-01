-- ChurchCRM 7.3.3
-- Add Disciple Maker support (Faiseur de disciple)

-- Ajouter la colonne pour le faiseur de disciple
ALTER TABLE `person_per`
  ADD COLUMN `per_DiscipleMakerID` mediumint(9) unsigned DEFAULT NULL AFTER `per_LinkedIn`;

-- Ajouter un index pour les performances
CREATE INDEX `idx_disciple_maker` ON `person_per` (`per_DiscipleMakerID`);

-- Ajouter la contrainte de clé étrangère (self-referencing)
ALTER TABLE `person_per`
  ADD CONSTRAINT `fk_disciple_maker` FOREIGN KEY (`per_DiscipleMakerID`) REFERENCES `person_per` (`per_ID`) ON DELETE SET NULL;
