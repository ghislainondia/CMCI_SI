-- Script complet pour recréer Jean, Paul et leurs disciples
-- Nettoyer d'abord les données existantes

-- Supprimer les comptes utilisateurs existants
DELETE FROM user_usr WHERE usr_UserName IN ('jean.resp', 'paul.resp');

-- Supprimer les personnes de test existantes
DELETE FROM person2group2role_p2g2r WHERE p2g2r_per_ID IN (
    SELECT per_ID FROM (SELECT per_ID FROM person_per WHERE per_Email IN (
        'jean@test.com', 'paul@test.com', 'marie@test.com', 'pierre@test.com',
        'jacques@test.com', 'thomas@test.com'
    )) as tmp
);

DELETE FROM person_per WHERE per_Email IN (
    'jean@test.com', 'paul@test.com', 'marie@test.com', 'pierre@test.com',
    'jacques@test.com', 'thomas@test.com'
);

-- Créer les groupes de test
DELETE FROM group_grp WHERE grp_Name IN ('Groupe de chants', 'Groupe de jeunes');

INSERT INTO group_grp (grp_Name, grp_Description, grp_Type, grp_DefaultRole, grp_Active) VALUES
('Groupe de chants', 'Responsable: Jean - Membres: Marie, Pierre', 4, 2, 1),
('Groupe de jeunes', 'Responsable: Paul - Membres: Jacques, Thomas', 4, 2, 1);

SET @group_chants = LAST_INSERT_ID();
SET @group_jeunes = @group_chants + 1;

-- Créer les personnes de test
INSERT INTO person_per (
    per_FirstName, per_LastName, per_Email, per_Gender, per_fmr_ID, per_cls_ID,
    per_DateEntered, per_EnteredBy, per_MembershipDate
) VALUES
('Jean', 'Responsable', 'jean@test.com', 1, 1, 1, NOW(), 1, CURDATE()),
('Marie', 'Membre', 'marie@test.com', 2, 2, 1, NOW(), 1, CURDATE()),
('Pierre', 'Membre', 'pierre@test.com', 1, 2, 1, NOW(), 1, CURDATE()),
('Paul', 'Responsable', 'paul@test.com', 1, 1, 1, NOW(), 1, CURDATE()),
('Jacques', 'Membre', 'jacques@test.com', 1, 2, 1, NOW(), 1, CURDATE()),
('Thomas', 'Membre', 'thomas@test.com', 1, 2, 1, NOW(), 1, CURDATE());

-- Récupérer les IDs des personnes créées
SET @jean = LAST_INSERT_ID();
SET @marie = @jean + 1;
SET @pierre = @jean + 2;
SET @paul = @jean + 3;
SET @jacques = @jean + 4;
SET @thomas = @jean + 5;

-- Ajouter les personnes aux groupes
INSERT INTO person2group2role_p2g2r (p2g2r_per_ID, p2g2r_grp_ID, p2g2r_rle_ID) VALUES
(@jean, @group_chants, 1),      -- Jean est responsable du groupe de chants
(@marie, @group_chants, 2),     -- Marie est membre du groupe de chants
(@pierre, @group_chants, 2),    -- Pierre est membre du groupe de chants
(@paul, @group_jeunes, 1),      -- Paul est responsable du groupe de jeunes
(@jacques, @group_jeunes, 2),   -- Jacques est membre du groupe de jeunes
(@thomas, @group_jeunes, 2);    -- Thomas est membre du groupe de jeunes

-- Assigner les relations de discipulat (FAISEURS DE DISCIPLE)
UPDATE person_per SET per_DiscipleMakerID = @jean WHERE per_ID IN (@marie, @pierre);
UPDATE person_per SET per_DiscipleMakerID = @paul WHERE per_ID IN (@jacques, @thomas);

-- Créer les comptes utilisateurs avec des group_id
INSERT INTO user_usr (
    usr_per_ID, usr_UserName, usr_Password,
    usr_AddRecords, usr_EditRecords, usr_DeleteRecords, usr_ManageGroups,
    usr_Finance, usr_Notes, usr_Admin, group_id
) VALUES
(@jean, 'jean.resp', '$2y$12$rvb384gGVeDpF/O4t2NZvOpArO5EJu3ewzAu2pqooWILkjvBD2Q7C',
    1, 1, 0, 1, 0, 1, 0, @group_chants),
(@paul, 'paul.resp', '$2y$12$rvb384gGVeDpF/O4t2NZvOpArO5EJu3ewzAu2pqooWILkjvBD2Q7C',
    1, 1, 0, 1, 0, 1, 0, @group_jeunes);

-- Afficher le résumé
SELECT '=== CRÉATION RÉUSSIE ===' as Info;

SELECT 'Comptes utilisateurs créés:' as Info;
SELECT usr_UserName as 'Utilisateur', usr_per_ID as 'Personne ID', group_id as 'Groupe ID'
FROM user_usr WHERE usr_UserName IN ('jean.resp', 'paul.resp');

SELECT 'Relations de discipulat créées:' as Info;
SELECT
    CONCAT(Disciple.per_FirstName, ' ', Disciple.per_LastName) as 'Disciple',
    Disciple.per_Email as 'Email',
    CONCAT(Maker.per_FirstName, ' ', Maker.per_LastName) as 'Faiseur de disciple',
    Maker.per_Email as 'Email Faiseur'
FROM person_per Disciple
JOIN person_per Maker ON Disciple.per_DiscipleMakerID = Maker.per_ID
WHERE Disciple.per_Email IN ('marie@test.com', 'pierre@test.com', 'jacques@test.com', 'thomas@test.com')
ORDER BY Maker.per_LastName, Disciple.per_LastName;

SELECT 'Groupes créés:' as Info;
SELECT grp_ID as 'Groupe ID', grp_Name as 'Nom', grp_Description as 'Description'
FROM group_grp WHERE grp_Name IN ('Groupe de chants', 'Groupe de jeunes');

SELECT '✅ Jean, Paul et leurs disciples ont été créés avec succès !' as Success;
