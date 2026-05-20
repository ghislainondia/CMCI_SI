<?php
require_once __DIR__ . '/Include/LoadConfigs.php';

use Propel\Runtime\Propel;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Suppression totale - ChurchCRM</title>
    <style>body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; } pre { background: #f4f4f4; padding: 15px; border-radius: 5px; }</style>
</head>
<body>
    <h1>🗑️ Suppression de toutes les données</h1>
    <pre>
<?php

try {
    $con = Propel::getWriteConnection(\ChurchCRM\model\ChurchCRM\Map\PersonTableMap::DATABASE_NAME);
    
    echo "=== Suppression de toutes les données ===\n\n";
    
    // Compter avant
    $stmt = $con->query("SELECT COUNT(*) FROM person_per");
    $personCount = $stmt->fetchColumn();
    
    $stmt = $con->query("SELECT COUNT(*) FROM family_fam");
    $familyCount = $stmt->fetchColumn();
    
    echo "Avant suppression:\n";
    echo "  Personnes: $personCount\n";
    echo "  Familles: $familyCount\n\n";
    
    // Supprimer dans l'ordre correct
    echo "Suppression en cours...\n";
    
    $tables = [
        'person2group2role_p2g2r',
        'person_custom',
        'family_custom',
        'person_per',
        'family_fam',
    ];
    
    foreach ($tables as $table) {
        $con->exec("DELETE FROM $table");
        echo "✓ $table vidée\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ Toutes les données supprimées!\n";
    echo str_repeat("=", 50) . "\n";
    echo "\nLa base de données est maintenant vide.\n";
    echo "\n<strong>➡️</strong> <a href='/CleanAndImport.php'>Importer les membres du CSV</a>\n";
    
} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
?>
    </pre>
</body>
</html>
