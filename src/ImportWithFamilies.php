<?php
require_once __DIR__ . '/Include/LoadConfigs.php';

use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use Propel\Runtime\Propel;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import avec Familles - ChurchCRM</title>
    <style>body { font-family: Arial, sans-serif; margin: 20px; } pre { background: #f4f4f4; padding: 15px; border-radius: 5px; }</style>
</head>
<body>
    <h1>🏠 Import avec structure familiale</h1>
    <pre>
<?php

try {
    $con = Propel::getWriteConnection(\ChurchCRM\model\ChurchCRM\Map\PersonTableMap::DATABASE_NAME);
    
    echo "=== ÉTAPE 1: Nettoyage de la base ===\n";
    foreach (['person2group2role_p2g2r', 'person_custom', 'family_custom', 'person_per', 'family_fam'] as $table) {
        $con->exec("DELETE FROM $table");
        echo "✓ $table vidée\n";
    }
    echo "\n";
    
    echo "=== ÉTAPE 2: Lecture du fichier Famille.csv ===\n";
    $familyPath = __DIR__ . '/Famille.csv';
    $memberPath = __DIR__ . '/membre.csv';
    
    if (!file_exists($familyPath)) {
        die("ERREUR: Famille.csv non trouvé\n");
    }
    if (!file_exists($memberPath)) {
        die("ERREUR: membre.csv non trouvé\n");
    }
    
    // Lire le fichier famille
    $familyLines = file($familyPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $familyHeaders = str_getcsv(array_shift($familyLines), ';');
    echo "Famille.csv: " . count($familyLines) . " lignes\n";
    
    // Lire le fichier membres
    $memberLines = file($memberPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $memberHeaders = str_getcsv(array_shift($memberLines), ';');
    echo "membre.csv: " . count($memberLines) . " lignes\n\n";
    
    // Créer un index des membres avec toutes leurs données
    $colMap = [
        'FirstName' => array_search('Prénom', $memberHeaders),
        'LastName' => array_search('Nom', $memberHeaders),
        'MaritalStatus' => array_search('Statut matrimonial', $memberHeaders),
        'BaptismDate' => array_search('Date de baptême', $memberHeaders),
        'BirthDate' => array_search('Date d\'anniversaire', $memberHeaders),
        'MemberStatus' => array_search('statut du membre', $memberHeaders),
        'Profession' => array_search('Profession/Etude', $memberHeaders),
        'Assembly' => array_search('Famille', $memberHeaders),
        'Location' => array_search('localisation', $memberHeaders),
        'Phone' => array_search('Contact', $memberHeaders),
    ];
    
    $membersData = [];
    foreach ($memberLines as $line) {
        $values = str_getcsv($line, ';');
        $firstName = trim($values[$colMap['FirstName']] ?? '');
        $lastName = trim($values[$colMap['LastName']] ?? '');
        
        if (empty($firstName) || empty($lastName)) continue;
        
        $membersData["$firstName $lastName"] = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'maritalStatus' => trim($values[$colMap['MaritalStatus']] ?? ''),
            'baptismDate' => trim($values[$colMap['BaptismDate']] ?? ''),
            'birthDate' => trim($values[$colMap['BirthDate']] ?? ''),
            'memberStatus' => trim($values[$colMap['MemberStatus']] ?? ''),
            'profession' => trim($values[$colMap['Profession']] ?? ''),
            'location' => trim($values[$colMap['Location']] ?? ''),
            'phone' => trim($values[$colMap['Phone']] ?? ''),
        ];
    }
    
    echo "Index créé: " . count($membersData) . " membres indexés\n\n";
    
    echo "=== ÉTAPE 3: Regroupement par famille ===\n";
    
    $families = [];
    foreach ($familyLines as $line) {
        $values = str_getcsv($line, ';');
        $familyName = trim($values[0]);
        $firstName = trim($values[1]);
        $lastName = trim($values[2]);
        
        if (empty($firstName) || empty($lastName)) continue;
        if (empty($familyName)) continue;
        
        $key = "$firstName $lastName";
        if (isset($membersData[$key])) {
            if (!isset($families[$familyName])) {
                $families[$familyName] = [];
            }
            $families[$familyName][] = $key;
        }
    }
    
    echo "Familles identifiées: " . count($families) . "\n";
    foreach ($families as $familyName => $members) {
        echo "  - $familyName: " . count($members) . " membres\n";
    }
    echo "\n";
    
    echo "=== ÉTAPE 4: Import des familles et membres ===\n";
    
    $importedFamilies = 0;
    $importedMembers = 0;
    
    foreach ($families as $familyName => $memberKeys) {
        // Créer la famille
        $stmt = $con->prepare("INSERT INTO family_fam (fam_Name, fam_DateEntered, fam_EnteredBy) VALUES (?, NOW(), 1)");
        $stmt->execute([$familyName]);
        $familyId = $con->lastInsertId();
        
        echo "✓ Famille créée: $familyName (ID: $familyId)\n";
        $importedFamilies++;
        
        // Créer les membres de cette famille
        foreach ($memberKeys as $memberKey) {
            $member = $membersData[$memberKey];
            
            $maritalStatus = strtolower($member['maritalStatus']);
            $fmrId = ($maritalStatus === 'marié' || $maritalStatus === 'marie') ? 2 : 1;
            
            $status = strtolower($member['memberStatus']);
            $clsId = ($status === 'actif') ? 1 : 2;
            
            $feminineEndings = ['e', 'a', 'ine', 'elle', 'ette', 'ie', 'yste'];
            $gender = in_array(strtolower(substr($member['firstName'], -1)), $feminineEndings) ? 2 : 1;
            
            // Nettoyer le téléphone
            $cleanPhone = '';
            if (!empty($member['phone'])) {
                $cleanPhone = preg_replace('/[^0-9+]/', '', $member['phone']);
            }
            
            // Date de naissance
            $birthMonth = 0;
            $birthDay = 0;
            $birthYear = 0;
            if (!empty($member['birthDate']) && $member['birthDate'] !== 'Actif') {
                $timestamp = strtotime($member['birthDate']);
                if ($timestamp !== false) {
                    $birthMonth = intval(date('m', $timestamp));
                    $birthDay = intval(date('d', $timestamp));
                    $birthYear = intval(date('Y', $timestamp));
                }
            }
            
            // Insérer la personne
            if ($birthMonth > 0) {
                $sql = "INSERT INTO person_per (per_FirstName, per_LastName, per_fam_ID, per_fmr_ID, per_cls_ID, per_Gender, per_CellPhone, per_City, per_BirthMonth, per_BirthDay, per_BirthYear, per_DateEntered, per_EnteredBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
                $params = [$member['firstName'], $member['lastName'], $familyId, $fmrId, $clsId, $gender, $cleanPhone, $member['location'], $birthMonth, $birthDay, $birthYear];
            } else {
                $sql = "INSERT INTO person_per (per_FirstName, per_LastName, per_fam_ID, per_fmr_ID, per_cls_ID, per_Gender, per_CellPhone, per_City, per_DateEntered, per_EnteredBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
                $params = [$member['firstName'], $member['lastName'], $familyId, $fmrId, $clsId, $gender, $cleanPhone, $member['location']];
            }
            
            $stmt = $con->prepare($sql);
            $stmt->execute($params);
            
            $importedMembers++;
            echo "  - {$member['firstName']} {$member['lastName']}\n";
        }
        
        echo "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 IMPORT TERMINÉ AVEC SUCCÈS!\n";
    echo str_repeat("=", 60) . "\n";
    echo "✅ Familles importées: $importedFamilies\n";
    echo "✅ Membres importés: $importedMembers\n";
    echo "\n📁 Fichiers utilisés:\n";
    echo "  - Famille.csv (structure familiale)\n";
    echo "  - membre.csv (données détaillées)\n";
    
} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
?>
    </pre>
    <p><strong>➡️</strong> <a href="/v2/family">Voir les familles</a> | <a href="/v2/people">Voir les personnes</a></p>
</body>
</html>
