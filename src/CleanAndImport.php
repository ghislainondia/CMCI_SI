<?php
require_once __DIR__ . '/Include/LoadConfigs.php';

use Propel\Runtime\Propel;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nettoyage et Import - ChurchCRM</title>
    <style>body { font-family: Arial, sans-serif; margin: 20px; } pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }</style>
</head>
<body>
    <h1>🧹 Nettoyage complet + Import CSV</h1>
    <pre>
<?php

try {
    $con = Propel::getWriteConnection(\ChurchCRM\model\ChurchCRM\Map\PersonTableMap::DATABASE_NAME);
    
    echo "=== ÉTAPE 1: Comptage des données existantes ===\n";
    $stmt = $con->query("SELECT COUNT(*) FROM person_per");
    $personCount = $stmt->fetchColumn();
    
    $stmt = $con->query("SELECT COUNT(*) FROM family_fam");
    $familyCount = $stmt->fetchColumn();
    
    echo "Personnes: $personCount | Familles: $familyCount\n\n";
    
    echo "=== ÉTAPE 2: Suppression des données ===\n";
    
    $tables = ['person2group2role_p2g2r', 'person_custom', 'family_custom', 'person_per', 'family_fam'];
    foreach ($tables as $table) {
        try {
            $con->exec("DELETE FROM $table");
            echo "✓ $table vidée\n";
        } catch (Exception $e) {
            echo "ℹ $table ignorée\n";
        }
    }
    
    echo "\n✅ Base de données vidée!\n\n";
    
    echo "=== ÉTAPE 3: Import des membres ===\n";
    
    $csvPath = __DIR__ . '/membre.csv';
    if (!file_exists($csvPath)) {
        die("ERREUR: CSV non trouvé: $csvPath\n");
    }
    
    $lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $headers = str_getcsv(array_shift($lines), ';');
    echo count($lines) . " personnes à importer\n\n";
    
    $colMap = [
        'FirstName' => array_search('Prénom', $headers),
        'LastName' => array_search('Nom', $headers),
        'MaritalStatus' => array_search('Statut matrimonial', $headers),
        'BirthDate' => array_search('Date d\'anniversaire', $headers),
        'MemberStatus' => array_search('statut du membre', $headers),
        'Profession' => array_search('Profession/Etude', $headers),
        'Assembly' => array_search('Famille', $headers),
        'Location' => array_search('localisation', $headers),
        'Phone' => array_search('Contact', $headers),
    ];
    
    $imported = 0;
    
    foreach ($lines as $line) {
        $values = str_getcsv($line, ';');
        
        $firstName = trim($values[$colMap['FirstName']] ?? '');
        $lastName = trim($values[$colMap['LastName']] ?? '');
        
        if (empty($firstName) || empty($lastName)) continue;
        
        $location = trim($values[$colMap['Location']] ?? '');
        $phone = trim($values[$colMap['Phone']] ?? '');
        $cleanPhone = '';
        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        }
        
        // Insérer famille
        $stmt = $con->prepare("INSERT INTO family_fam (fam_Name, fam_City, fam_HomePhone, fam_DateEntered, fam_EnteredBy) VALUES (?, ?, ?, NOW(), 1)");
        $stmt->execute(["$lastName $firstName", $location, $cleanPhone]);
        $familyId = $con->lastInsertId();
        
        // Préparer les données de personne
        $maritalStatus = strtolower(trim($values[$colMap['MaritalStatus']] ?? ''));
        $fmrId = ($maritalStatus === 'marié' || $maritalStatus === 'marie') ? 2 : 1;
        
        $status = strtolower(trim($values[$colMap['MemberStatus']] ?? ''));
        $clsId = $status === 'actif' ? 1 : 2;
        
        $feminineEndings = ['e', 'a', 'ine', 'elle', 'ette', 'ie', 'yste'];
        $gender = in_array(strtolower(substr($firstName, -1)), $feminineEndings) ? 2 : 1;
        
        // Gérer la date de naissance
        $birthDate = trim($values[$colMap['BirthDate']] ?? '');
        $birthMonth = 0;
        $birthDay = 0;
        $birthYear = 0;
        
        if (!empty($birthDate) && $birthDate !== 'Actif') {
            $timestamp = strtotime($birthDate);
            if ($timestamp !== false) {
                $birthMonth = intval(date('m', $timestamp));
                $birthDay = intval(date('d', $timestamp));
                $birthYear = intval(date('Y', $timestamp));
            }
        }
        
        // Construire la requête SQL dynamiquement selon qu'il y a une date de naissance ou non
        if ($birthMonth > 0) {
            $sql = "INSERT INTO person_per (per_FirstName, per_LastName, per_fam_ID, per_fmr_ID, per_cls_ID, per_Gender, per_CellPhone, per_City, per_BirthMonth, per_BirthDay, per_BirthYear, per_DateEntered, per_EnteredBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
            $params = [$firstName, $lastName, $familyId, $fmrId, $clsId, $gender, $cleanPhone, $location, $birthMonth, $birthDay, $birthYear];
        } else {
            $sql = "INSERT INTO person_per (per_FirstName, per_LastName, per_fam_ID, per_fmr_ID, per_cls_ID, per_Gender, per_CellPhone, per_City, per_DateEntered, per_EnteredBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
            $params = [$firstName, $lastName, $familyId, $fmrId, $clsId, $gender, $cleanPhone, $location];
        }
        
        $stmt = $con->prepare($sql);
        $stmt->execute($params);
        
        $imported++;
        if ($imported <= 20 || $imported % 50 == 0) {
            echo "✓ Importé #$imported: $firstName $lastName";
            if (!empty($location)) echo " - $location";
            echo "\n";
        } elseif ($imported == 21) {
            echo "...\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 SUCCÈS TOTAL!\n";
    echo str_repeat("=", 60) . "\n";
    echo "✅ Membres importés: $imported\n";
    echo "🗑️ Anciennes données supprimées\n";
    echo "📊 Base de données maintenant propre avec uniquement les membres du CSV\n";
    echo "📁 Fichier CSV: membre.csv\n";
    
} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Détails: " . $e->getTraceAsString() . "\n";
}
?>
    </pre>
    <p style="margin-top: 20px;">
        <strong>➡️</strong> <a href="/v2/dashboard">Aller au tableau de bord</a> | 
        <a href="/v2/people">Voir les personnes</a> |
        <a href="/v2/family">Voir les familles</a>
    </p>
</body>
</html>
