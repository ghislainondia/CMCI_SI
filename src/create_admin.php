<?php
require_once __DIR__ . '/Include/LoadConfigs.php';

use Propel\Runtime\Propel;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Création Admin - ChurchCRM</title>
    <style>body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; } pre { background: #f4f4f4; padding: 15px; border-radius: 5px; }</style>
</head>
<body>
    <h1>👤 Création du compte Admin</h1>
    <pre>
<?php

try {
    $con = Propel::getWriteConnection(\ChurchCRM\model\ChurchCRM\Map\PersonTableMap::DATABASE_NAME);
    
    echo "=== Création du compte administrateur ===\n\n";
    
    // 1. Créer une famille pour l'admin
    echo "1. Création de la famille admin...\n";
    $stmt = $con->prepare("INSERT INTO family_fam (fam_Name, fam_DateEntered, fam_EnteredBy) VALUES ('Administrateur', NOW(), 1)");
    $stmt->execute();
    $familyId = $con->lastInsertId();
    echo "   ✓ Famille créée (ID: $familyId)\n";
    
    // 2. Créer une personne admin
    echo "2. Création de la personne admin...\n";
    $stmt = $con->prepare("INSERT INTO person_per (per_FirstName, per_LastName, per_fam_ID, per_Gender, per_Email, per_DateEntered, per_EnteredBy) VALUES ('Admin', 'Système', ?, 1, 'admin@churchcrm.local', NOW(), 1)");
    $stmt->execute([$familyId]);
    $personId = $con->lastInsertId();
    echo "   ✓ Personne créée (ID: $personId)\n";
    
    // 3. Créer l'utilisateur admin
    echo "3. Création de l'utilisateur admin...\n";
    $passwordHash = password_hash('admin', PASSWORD_DEFAULT);
    $stmt = $con->prepare("INSERT INTO user_usr (usr_per_ID, usr_UserName, usr_Password, usr_Admin, usr_AddRecords, usr_EditRecords, usr_DeleteRecords, usr_Finance, usr_Notes, usr_ManageGroups) VALUES (?, 'admin', ?, 1, 1, 1, 1, 1, 1, 1)");
    $stmt->execute([$personId, $passwordHash]);
    echo "   ✓ Utilisateur admin créé\n";
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ Compte admin créé avec succès!\n";
    echo str_repeat("=", 60) . "\n";
    echo "\n🔐 INFORMATIONS DE CONNEXION:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "URL:           http://localhost:8080/session/begin\n";
    echo "Utilisateur:   admin\n";
    echo "Mot de passe:  admin\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n⚠ N'oubliez pas de changer le mot de passe après connexion!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Détails: " . $e->getTraceAsString() . "\n";
}
?>
    </pre>
    <p style="margin-top: 20px;">
        <strong style="font-size: 18px;">➡️ <a href="/session/begin">Aller à la page de connexion</a></strong>
    </p>
</body>
</html>
