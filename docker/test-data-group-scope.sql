-- Script de test pour le système de scope de groupe ChurchCRM
-- Ce script crée des utilisateurs avec des group_ids différents et des personnes dans différents groupes

-- Insérer des groupes de test
INSERT INTO group_grp (grp_Name, grp_Description, grp_Type, grp_DefaultRole, grp_Active) VALUES
('Groupe de chants', 'Responsable: Jean - Membres: Marie, Pierre', 4, 2, 1),
('Groupe de jeunes', 'Responsable: Paul - Membres: Jacques, Thomas', 4, 2, 1),
('Conseil d''administration', 'Responsable: Admin - Tous les membres', 4, 2, 1);

-- Récupérer les IDs des groupes créés
SET @group_chants = LAST_INSERT_ID();
SET @group_jeunes = @group_chants + 1;
SET @group_admin = @group_chants + 2;

-- Créer des personnes de test
INSERT INTO person_per (per_FirstName, per_LastName, per_Email, per_Gender, per_Fmr_ID, per_Cls_ID, per_DateEntered, per_EnteredBy) VALUES
('Jean', 'Responsable', 'jean@test.com', 1, 1, 1, NOW(), 1),
('Marie', 'Membre', 'marie@test.com', 2, 2, 1, NOW(), 1),
('Pierre', 'Membre', 'pierre@test.com', 1, 2, 1, NOW(), 1),
('Paul', 'Responsable', 'paul@test.com', 1, 1, 1, NOW(), 1),
('Jacques', 'Membre', 'jacques@test.com', 1, 2, 1, NOW(), 1),
('Thomas', 'Membre', 'thomas@test.com', 1, 2, 1, NOW(), 1);

-- Récupérer les IDs des personnes créées
SET @person_jean = LAST_INSERT_ID();
SET @person_marie = @person_jean + 1;
SET @person_pierre = @person_jean + 2;
SET @person_paul = @person_jean + 3;
SET @person_jacques = @person_jean + 4;
SET @person_thomas = @person_jean + 5;

-- Ajouter les personnes aux groupes
-- Groupe de chants: Jean (responsable), Marie, Pierre
INSERT INTO person2group2role_p2g2r (p2g2r_per_ID, p2g2r_grp_ID, p2g2r_rle_ID) VALUES
(@person_jean, @group_chants, 1),    -- Jean est responsable du groupe de chants
(@person_marie, @group_chants, 2),   -- Marie est membre du groupe de chants
(@person_pierre, @group_chants, 2);  -- Pierre est membre du groupe de chants

-- Groupe de jeunes: Paul (responsable), Jacques, Thomas
INSERT INTO person2group2role_p2g2r (p2g2r_per_ID, p2g2r_grp_ID, p2g2r_rle_ID) VALUES
(@person_paul, @group_jeunes, 1),    -- Paul est responsable du groupe de jeunes
(@person_jacques, @group_jeunes, 2), -- Jacques est membre du groupe de jeunes
(@person_thomas, @group_jeunes, 2);  -- Thomas est membre du groupe de jeunes

-- Conseil d'administration: Admin
INSERT INTO person2group2role_p2g2r (p2g2r_per_ID, p2g2r_grp_ID, p2g2r_rle_ID) VALUES
(1, @group_admin, 1);  -- L'admin principal est responsable du conseil

-- Créer des utilisateurs avec des group_id différents
-- Mots de passe: tous "password123" (hashé avec password_hash)
INSERT INTO user_usr (usr_per_ID, usr_UserName, usr_Password, usr_AddRecords, usr_EditRecords, usr_DeleteRecords, usr_ManageGroups, usr_Finance, usr_Notes, usr_Admin, group_id) VALUES
(@person_jean, 'jean.resp', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 0, 1, 0, 1, 0, @group_chants),
(@person_paul, 'paul.resp', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 0, 1, 0, 1, 0, @group_jeunes);

-- Donner les permissions nécessaires aux utilisateurs de test
-- Les permissions sont déjà définies dans usr_AddRecords, usr_EditRecords, etc.



SELECT 'Test data created successfully!' as Status;
SELECT 'Users created:' as Info;
SELECT usr_UserName as username, group_id as assigned_group FROM user_usr WHERE usr_UserName IN ('jean.resp', 'paul.resp');
SELECT 'Group assignments:' as Info;
SELECT p.per_FirstName, p.per_LastName, g.grp_Name, r.lst_OptionName as role
FROM person_per p
JOIN person2group2role_p2g2r p2g ON p.per_ID = p2g.p2g2r_per_ID
JOIN group_grp g ON p2g.p2g2r_grp_ID = g.grp_ID
JOIN list_lst r ON p2g.p2g2r_rle_ID = r.lst_OptionID
WHERE p.per_FirstName IN ('Jean', 'Marie', 'Pierre', 'Paul', 'Jacques', 'Thomas')
ORDER BY g.grp_Name, p.per_FirstName;
