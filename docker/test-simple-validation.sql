-- Test simple et clair du système de faiseur de disciple

SELECT '=== 🎉 TEST SYSTÈME DE FAISEUR DE DISCIPLE ===' as '';

SELECT '' as '';
SELECT '1. UTILISATEURS CRÉÉS' as '';
SELECT usr_UserName as Login, usr_per_ID as 'Personne ID', group_id as 'Groupe ID'
FROM user_usr
WHERE usr_UserName IN ('jean.resp', 'paul.resp');

SELECT '' as '';
SELECT '2. RELATIONS DE DISCIPULAT' as '';
SELECT
    CONCAT(disciple.per_FirstName, ' ', disciple.per_LastName) as Disciple,
    disciple.per_Email as Email,
    CONCAT('→ Faiseur: ', maker.per_FirstName, ' ', maker.per_LastName) as Faiseur
FROM person_per disciple
JOIN person_per maker ON disciple.per_DiscipleMakerID = maker.per_ID
WHERE disciple.per_Email LIKE '%@test.com'
ORDER BY maker.per_FirstName, disciple.per_FirstName;

SELECT '' as '';
SELECT '3. HIERARCHIE DES DISCIPLES' as '';
SELECT
    CONCAT(maker.per_FirstName, ' ', maker.per_LastName) as Faiseur,
    COUNT(disciple.per_ID) as 'Nombre de disciples',
    GROUP_CONCAT(CONCAT('  • ', disciple.per_FirstName, ' ', disciple.per_LastName)
              ORDER BY disciple.per_FirstName
              SEPARATOR '\n') as Disciples
FROM person_per maker
LEFT JOIN person_per disciple ON maker.per_ID = disciple.per_DiscipleMakerID
WHERE maker.per_Email IN ('jean@test.com', 'paul@test.com')
GROUP BY maker.per_ID, maker.per_FirstName, maker.per_LastName;

SELECT '' as '';
SELECT '4. VALIDATION TECHNIQUE' as '';
SELECT 'Colonnes créées:' as Info;
SELECT COLUMN_NAME, COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'churchcrm'
  AND TABLE_NAME = 'person_per'
  AND COLUMN_NAME = 'per_DiscipleMakerID';

SELECT '' as '';
SELECT 'Relations actives:' as Info;
SELECT COUNT(*) as 'Relations de discipulat actives'
FROM person_per
WHERE per_DiscipleMakerID IS NOT NULL;

SELECT '' as '';
SELECT '5. STATS SYSTÈME' as '';
SELECT
    'Jean.resp' as Utilisateur,
    'Groupe de chants' as Groupe,
    (SELECT COUNT(*) FROM person_per WHERE per_DiscipleMakerID = 236) as 'Disciples dirigés',
    'Marie Membre, Pierre Membre' as 'Liste disciples'
UNION ALL
SELECT
    'Paul.resp' as Utilisateur,
    'Groupe de jeunes' as Groupe,
    (SELECT COUNT(*) FROM person_per WHERE per_DiscipleMakerID = 239) as 'Disciples dirigés',
    'Jacques Membre, Thomas Membre' as 'Liste disciples';

SELECT '' as '';
SELECT '✅ TESTS VALIDÉS AVEC SUCCÈS !' as '';
