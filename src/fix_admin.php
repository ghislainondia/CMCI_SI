<?php
require_once __DIR__ . "/Include/LoadConfigs.php";

use Propel\Runtime\Propel;

header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Admin</title>
    <style>body { font-family: Arial, sans-serif; margin: 20px; } pre { background: #f4f4f4; padding: 15px; }</style>
</head>
<body>
    <h1>Creation Compte Admin</h1>
    <pre>
<?php
try {
    \$con = Propel::getWriteConnection(\ChurchCRM\model\ChurchCRM\Map\PersonTableMap::DATABASE_NAME);
    echo "=== Creation Admin ===\n\n";
    
    \$stmt = \$con->prepare("INSERT INTO family_fam (fam_Name, fam_DateEntered, fam_EnteredBy) VALUES (?, NOW(), 1)");
    \$stmt->execute(["Administrateur"]);
    \$familyId = \$con->lastInsertId();
    echo "1. Famille cree (ID: \$familyId)\n";
    
    \$stmt = \$con->prepare("INSERT INTO person_per (per_FirstName, per_LastName, per_fam_ID, per_Gender, per_Email, per_DateEntered, per_EnteredBy) VALUES (?, ?, ?, 1, ?, NOW(), 1)");
    \$stmt->execute(["Admin", "Systeme", \$familyId, "admin@churchcrm.local"]);
    \$personId = \$con->lastInsertId();
    echo "2. Personne cree (ID: \$personId)\n";
    
    \$stmt = \$con->query("SELECT usr_per_ID FROM user_usr WHERE usr_UserName = \"admin\"");
    \$result = \$stmt->fetch();
    if (\$result) {
        \$stmt = \$con->prepare("UPDATE user_usr SET usr_per_ID = ? WHERE usr_UserName = \"admin\"");
        \$stmt->execute([\$personId]);
        echo "3. Utilisateur admin mis a jour\n";
    } else {
        \$passwordHash = password_hash("admin", PASSWORD_DEFAULT);
        \$stmt = \$con->prepare("INSERT INTO user_usr (usr_per_ID, usr_UserName, usr_Password, usr_Admin) VALUES (?, \"admin\", ?, 1)");
        \$stmt->execute([\$personId, \$passwordHash]);
        echo "3. Utilisateur admin cree\n";
    }
    echo "\n✅ Compte admin pret!\n";
    echo "\nConnexion:\n";
    echo "URL: http://localhost:8080/session/begin\n";
    echo "Utilisateur: admin\n";
    echo "Mot de passe: admin\n";
} catch (Exception \$e) {
    echo "\nERREUR: " . \$e->getMessage() . "\n";
}
?>
    </pre>
    <p><a href="/session/begin">Se connecter</a></p>
</body>
</html>
