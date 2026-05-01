-- Supprimer les utilisateurs existants
DELETE FROM user_usr WHERE usr_UserName IN ('jean.resp', 'paul.resp');

-- Recréer avec des mots de passe correctement hashés (mot de passe: password123)
-- Hash généré avec password_hash('password123', PASSWORD_DEFAULT)
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
    '$2y$12$rvb384gGVeDpF/O4t2NZvOpArO5EJu3ewzAu2pqooWILkjvBD2Q7C',
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
    '$2y$12$rvb384gGVeDpF/O4t2NZvOpArO5EJu3ewzAu2pqooWILkjvBD2Q7C',
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

SELECT 'Users recreated with correct password hashes!' as Status;
SELECT usr_UserName, usr_per_ID, group_id FROM user_usr WHERE usr_UserName IN ('jean.resp', 'paul.resp');
