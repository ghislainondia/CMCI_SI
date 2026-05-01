-- Nettoyer les données de test existantes
DELETE FROM user_usr WHERE usr_UserName IN ('jean.resp', 'paul.resp');
DELETE FROM person2group2role_p2g2r WHERE p2g2r_per_ID IN (
    SELECT per_ID FROM person_per WHERE per_Email IN ('jean@test.com', 'marie@test.com', 'pierre@test.com', 'paul@test.com', 'jacques@test.com', 'thomas@test.com')
);
DELETE FROM person_per WHERE per_Email IN ('jean@test.com', 'marie@test.com', 'pierre@test.com', 'paul@test.com', 'jacques@test.com', 'thomas@test.com');
DELETE FROM group_grp WHERE grp_Name IN ('Groupe de chants', 'Groupe de jeunes', 'Conseil d''administration');

SELECT 'Test data cleaned successfully!' as Status;
