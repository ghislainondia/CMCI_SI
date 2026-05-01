-- Créer des personnes de test pour le système de faiseur de disciple
-- Utiliser des personnes existantes dans la base de données

-- Trouver des personnes existantes pour créer les relations
SET @person1 = (SELECT per_ID FROM person_per WHERE per_Email IS NOT NULL AND per_Email != '' LIMIT 1);
SET @person2 = (SELECT per_ID FROM person_per WHERE per_Email IS NOT NULL AND per_Email != '' AND per_ID != @person1 LIMIT 1);
SET @person3 = (SELECT per_ID FROM person_per WHERE per_Email IS NOT NULL AND per_Email != '' AND per_ID NOT IN (@person1, @person2) LIMIT 1);
SET @person4 = (SELECT per_ID FROM person_per WHERE per_Email IS NOT NULL AND per_Email != '' AND per_ID NOT IN (@person1, @person2, @person3) LIMIT 1);

-- Créer des relations de discipulat
-- person1 et person2 seront les faiseurs de disciple
-- person3 sera disciple de person1
-- person4 sera disciple de person2

UPDATE person_per SET per_DiscipleMakerID = @person1 WHERE per_ID = @person3;
UPDATE person_per SET per_DiscipleMakerID = @person2 WHERE per_ID = @person4;

-- Afficher les relations créées
SELECT '=== SYSTÈME DE FAISEUR DE DISCIPLE TEST ===' as Info;

SELECT
    CONCAT(d.per_FirstName, ' ', d.per_LastName) as Disciple,
    d.per_Email as 'Email Disciple',
    CONCAT(m.per_FirstName, ' ', m.per_LastName) as 'Disciple Maker',
    m.per_Email as 'Email Faiseur'
FROM person_per d
JOIN person_per m ON d.per_DiscipleMakerID = m.per_ID
WHERE d.per_ID IN (@person3, @person4);

SELECT 'Relations créées avec succès!' as Status;
