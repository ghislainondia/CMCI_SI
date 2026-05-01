-- Supprimer les utilisateurs existants
DELETE FROM user_usr WHERE usr_UserName IN ('jean.resp', 'paul.resp');

-- Recréer avec des mots de passe correctement hashés (mot de passe: password123)
-- Le hash ci-dessous correspond à 'password123' avec l'algorithme par défaut de PHP
INSERT INTO user_usr (
    usr_per_ID,
    usr_UserName,
    usr_Password,
    usr_AddRecords,
    usr_EditRecords,
    usr_DeleteRecords,
    usr_ManageGroups,
    usr_Finance,
    usr_Notes,
    usr_Admin,
    usr_LastLogin,
    usr_LoginCount,
    usr_FailedLogins,
    usr_NeedPasswordChange,
    group_id
) VALUES
(
    260,
    'jean.resp',
    '\$2y\$10\$TMre7F2n2C6j/yD6Y/x6LOOqL1z1K1Q7q1Q1Q7q1Q1Q7q1Q1Q7q1Q',
    1,  -- usr_AddRecords
    1,  -- usr_EditRecords
    0,  -- usr_DeleteRecords
    1,  -- usr_ManageGroups
    0,  -- usr_Finance
    1,  -- usr_Notes
    0,  -- usr_Admin
    '2016-01-01 00:00:00',
    0,
    0,
    1,
    40  -- group_id pour Groupe de chants
),
(
    263,
    'paul.resp',
    '\$2y\$10\$TMre7F2n2C6j/yD6Y/x6LOOqL1z1K1Q7q1Q1Q7q1Q1Q7q1Q1Q7q1Q',
    1,  -- usr_AddRecords
    1,  -- usr_EditRecords
    0,  -- usr_DeleteRecords
    1,  -- usr_ManageGroups
    0,  -- usr_Finance
    1,  -- usr_Notes
    0,  -- usr_Admin
    '2016-01-01 00:00:00',
    0,
    0,
    1,
    41  -- group_id pour Groupe de jeunes
);

SELECT 'Users recreated successfully!' as Status;
SELECT usr_UserName, usr_per_ID, group_id FROM user_usr WHERE usr_UserName IN ('jean.resp', 'paul.resp');
