-- Script de test pour le système de faiseur de disciple
-- Assigner des relations de discipulat entre nos personnes de test

-- Récupérer les IDs des personnes de test existantes
SET @jean_resp = (SELECT per_ID FROM person_per WHERE per_Email = 'jean@test.com' LIMIT 1);
SET @paul_resp = (SELECT per_ID FROM person_per WHERE per_Email = 'paul@test.com' LIMIT 1);
SET @marie = (SELECT per_ID FROM person_per WHERE per_Email = 'marie@test.com' LIMIT 1);
SET @pierre = (SELECT per_ID FROM person_per WHERE per_Email = 'pierre@test.com' LIMIT 1);
SET @jacques = (SELECT per_ID FROM person_per WHERE per_Email = 'jacques@test.com' LIMIT 1);
SET @thomas = (SELECT per_ID FROM person_per WHERE per_Email = 'thomas@test.com' LIMIT 1);

-- Assigner Jean comme faiseur de disciple pour Marie et Pierre
UPDATE person_per SET per_DiscipleMakerID = @jean_resp WHERE per_ID IN (@marie, @pierre);

-- Assigner Paul comme faiseur de disciple pour Jacques et Thomas
UPDATE person_per SET per_DiscipleMakerID = @paul_resp WHERE per_ID IN (@jacques, @thomas);

-- Afficher les relations créées
SELECT
    disciple.per_ID as disciple_id,
    CONCAT(disciple.per_FirstName, ' ', disciple.per_LastName) as disciple_name,
    maker.per_ID as maker_id,
    CONCAT(maker.per_FirstName, ' ', maker.per_LastName) as maker_name
FROM person_per disciple
LEFT JOIN person_per maker ON disciple.per_DiscipleMakerID = maker.per_ID
WHERE disciple.per_Email IN ('jean@test.com', 'marie@test.com', 'pierre@test.com', 'paul@test.com', 'jacques@test.com', 'thomas@test.com')
ORDER BY maker.per_LastName, disciple.per_FirstName;

SELECT 'Test data created successfully! Disciple maker relationships established.' as Status;
