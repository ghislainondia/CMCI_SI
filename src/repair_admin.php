<?php
require_once __DIR__ . '/Include/LoadConfigs.php';

use Propel\Runtime\Propel;

try {
    $con = Propel::getWriteConnection(\ChurchCRM\model\ChurchCRM\Map\PersonTableMap::DATABASE_NAME);

    echo "=== Repair Admin ===\n";

    // Créer famille admin
    $con->exec("INSERT INTO family_fam (fam_Name, fam_DateEntered, fam_EnteredBy) VALUES ('Administrateur', NOW(), 1)");
    $familyId = $con->lastInsertId();
    echo "Famille créée: $familyId\n";

    // Créer personne admin
    $stmt = $con->prepare("INSERT INTO person_per (per_FirstName, per_LastName, per_fam_ID, per_Gender, per_Email, per_DateEntered, per_EnteredBy) VALUES ('Admin', 'Systeme', ?, 1, 'admin@churchcrm', NOW(), 1)");
    $stmt->execute([$familyId]);
    $personId = $con->lastInsertId();
    echo "Personne créée: $personId\n";

    // Associer l'utilisateur admin
    $stmt = $con->prepare("UPDATE user_usr SET usr_per_ID = ? WHERE usr_UserName = 'admin'");
    $stmt->execute([$personId]);
    echo "Admin associé\n";

    echo "\n✅ Réparé!\n";
    echo "Connectez-vous: http://localhost:8080/session/begin\n";
    echo "User: admin\n";
    echo "Pass: admin\n";

} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}
