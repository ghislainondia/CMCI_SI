<?php
require_once __DIR__ . '/Include/LoadConfigs.php';

use ChurchCRM\model\ChurchCRM\Person;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\model\ChurchCRM\Family;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use Propel\Runtime\Propel;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import de membres - ChurchCRM</title>
    <style>body { font-family: Arial, sans-serif; margin: 20px; } pre { background: #f4f4f4; padding: 10px; border-radius: 5px; font-size: 12px; }</style>
</head>
<body>
    <h1>🔄 Import de membres ChurchCRM (version simple)</h1>
    <pre>
<?php

$csvPath = __DIR__ . '/membre.csv';
if (!file_exists($csvPath)) {
    die("ERREUR: CSV non trouvé: $csvPath\n");
}

echo "=== Lecture du CSV ===\n";
$lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$headers = str_getcsv(array_shift($lines), ';');
echo count($lines) . " personnes à traiter\n\n";

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

$con = Propel::getWriteConnection(\ChurchCRM\model\ChurchCRM\Map\PersonTableMap::DATABASE_NAME);
$con->beginTransaction();

try {
    echo "=== Étape 1: Suppression des doublons ===\n";
    $duplicatesDeleted = 0;

    foreach ($lines as $line) {
        $values = str_getcsv($line, ';');
        $firstName = trim($values[$colMap['FirstName']] ?? '');
        $lastName = trim($values[$colMap['LastName']] ?? '');

        if (empty($firstName) || empty($lastName)) continue;

        $existing = PersonQuery::create()
            ->filterByFirstName($firstName)
            ->filterByLastName($lastName)
            ->find($con);

        if ($existing->count() > 0) {
            foreach ($existing as $person) {
                $familyId = $person->getFamId();
                echo "Doublon supprimé: $firstName $lastName\n";
                $person->delete($con);
                $duplicatesDeleted++;

                if ($familyId) {
                    $family = FamilyQuery::create()->findPk($familyId, $con);
                    if ($family && $family->countPeople() == 0) {
                        $family->delete($con);
                    }
                }
            }
        }
    }

    echo "Doublons supprimés: $duplicatesDeleted\n\n";

    echo "=== Étape 2: Import des membres ===\n";
    $imported = 0;

    foreach ($lines as $line) {
        $values = str_getcsv($line, ';');

        $firstName = trim($values[$colMap['FirstName']] ?? '');
        $lastName = trim($values[$colMap['LastName']] ?? '');

        if (empty($firstName) || empty($lastName)) continue;

        $family = new Family();
        $family->setName("$lastName $firstName");
        $family->setDateEntered(date('YmdHis'));
        $family->setEnteredBy(1);

        $location = trim($values[$colMap['Location']] ?? '');
        if (!empty($location)) $family->setCity($location);

        $phone = trim($values[$colMap['Phone']] ?? '');
        $cleanPhone = '';
        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
            if (strlen($cleanPhone) >= 10) $family->setHomePhone($cleanPhone);
        }

        $family->save($con);

        $person = new Person();
        $person->setFirstName($firstName);
        $person->setLastName($lastName);
        $person->setFamId($family->getId());
        $person->setDateEntered(date('YmdHis'));
        $person->setEnteredBy(1);

        $maritalStatus = strtolower(trim($values[$colMap['MaritalStatus']] ?? ''));
        $person->setFmrId(($maritalStatus === 'marié' || $maritalStatus === 'marie') ? 2 : 1);

        $status = strtolower(trim($values[$colMap['MemberStatus']] ?? ''));
        $person->setClsId($status === 'actif' ? 1 : 2);

        $feminineEndings = ['e', 'a', 'ine', 'elle', 'ette', 'ie', 'yste'];
        $person->setGender(in_array(strtolower(substr($firstName, -1)), $feminineEndings) ? 2 : 1);

        if (!empty($cleanPhone)) $person->setCellPhone($cleanPhone);
        if (!empty($location)) $person->setCity($location);

        $birthDate = trim($values[$colMap['BirthDate']] ?? '');
        if (!empty($birthDate) && $birthDate !== 'Actif') {
            $timestamp = strtotime($birthDate);
            if ($timestamp !== false) {
                $person->setBirthMonth(date('m', $timestamp));
                $person->setBirthDay(date('d', $timestamp));
                $person->setBirthYear(date('Y', $timestamp));
            }
        }

        $person->save($con);

        $imported++;
        echo "✓ Importé #$imported: $firstName $lastName";
        if (!empty($location)) echo " - $location";
        echo "\n";
    }

    $con->commit();

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 IMPORT TERMINÉ AVEC SUCCÈS!\n";
    echo str_repeat("=", 50) . "\n";
    echo "✅ Membres importés: $imported\n";
    echo "🗑️ Doublons supprimés: $duplicatesDeleted\n";
    echo "\n";

} catch (Exception $e) {
    $con->rollBack();
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Détails: " . $e->getTraceAsString() . "\n";
}
?>
    </pre>
    <p style="margin-top: 20px;">
        <strong>➡️</strong> <a href="/v2/dashboard">Aller au tableau de bord</a> |
        <a href="/v2/people">Voir les personnes</a>
    </p>
</body>
</html>
