<?php
/**
 * Script d'import direct de membres CSV dans ChurchCRM
 * Ce script lit le CSV membre.csv et importe les membres après avoir supprimé les doublons
 *
 * À placer dans: src/ImportMembers.php
 * À exécuter via: http://localhost:8080/ImportMembers.php
 */

require_once __DIR__ . '/ChurchCRM/Bootstrapper.php';

use ChurchCRM\Bootstrapper;
use ChurchCRM\model\ChurchCRM\Person;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\model\ChurchCRM\Family;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use Propel\Runtime\Propel;

// Initialiser l'application
Bootstrapper::initApp();

header('Content-Type: text/plain; charset=utf-8');

echo "=== Import de membres ChurchCRM ===\n\n";

// Chemins des fichiers
$csvPath = __DIR__ . '/../membre.csv';

if (!file_exists($csvPath)) {
    die("Erreur: Fichier $csvPath non trouvé\n");
}

// Fonction pour lire le CSV avec délimiteur ;
function readCsvWithSemicolon($file) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($lines)) {
        return [];
    }

    $headers = str_getcsv(array_shift($lines), ';');
    $data = [];

    foreach ($lines as $line) {
        $values = str_getcsv($line, ';');
        $row = [];
        foreach ($headers as $i => $header) {
            $row[$header] = $values[$i] ?? '';
        }
        $data[] = $row;
    }

    return ['headers' => $headers, 'data' => $data];
}

// Lire le CSV
echo "Lecture du fichier CSV...\n";
$csvData = readCsvWithSemicolon($csvPath);
echo "Trouvé " . count($csvData['data']) . " lignes\n\n";

// Identifier et supprimer les doublons
echo "Recherche des doublons dans la base...\n";
$con = Propel::getWriteConnection(\ChurchCRM\model\ChurchCRM\Map\PersonTableMap::DATABASE_NAME);
$con->beginTransaction();

$duplicatesFound = 0;
$duplicatesDeleted = 0;

foreach ($csvData['data'] as $row) {
    $firstName = trim($row['Prénom'] ?? '');
    $lastName = trim($row['Nom'] ?? '');

    if (empty($firstName) || empty($lastName)) {
        continue;
    }

    $existing = PersonQuery::create()
        ->filterByFirstName($firstName)
        ->filterByLastName($lastName)
        ->find($con);

    if ($existing->count() > 0) {
        $duplicatesFound++;
        foreach ($existing as $person) {
            $familyId = $person->getFamId();
            echo "Doublon trouvé: $firstName $lastName (ID: {$person->getId()}) - Suppression... ";
            $person->delete($con);
            echo "OK\n";
            $duplicatesDeleted++;

            // Supprimer la famille si vide
            if ($familyId) {
                $family = FamilyQuery::create()->findPk($familyId, $con);
                if ($family && $family->countPeople() == 0) {
                    echo "  Famille vide supprimée (ID: $familyId)\n";
                    $family->delete($con);
                }
            }
        }
    }
}

echo "\nDoublons trouvés: $duplicatesFound\n";
echo "Personnes supprimées: $duplicatesDeleted\n\n";

// Importer les nouveaux membres
echo "=== Import des nouveaux membres ===\n";

$imported = 0;
$skipped = 0;

foreach ($csvData['data'] as $row) {
    $firstName = trim($row['Prénom'] ?? '');
    $lastName = trim($row['Nom'] ?? '');

    if (empty($firstName) || empty($lastName)) {
        $skipped++;
        continue;
    }

    // Créer une famille pour chaque personne
    $family = new Family();
    $family->setName("$lastName $firstName");
    $family->setDateEntered(date('YmdHis'));
    $family->setEnteredBy(1);
    $family->save($con);

    // Créer la personne
    $person = new Person();
    $person->setFirstName($firstName);
    $person->setLastName($lastName);
    $person->setFamId($family->getId());
    $person->setFmrId(1); // Single
    $person->setClsId(1); // Member
    $person->setDateEntered(date('YmdHis'));
    $person->setEnteredBy(1);

    // Téléphone
    $phone = trim($row['Contact'] ?? '');
    if (!empty($phone)) {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        if (strlen($cleanPhone) >= 10) {
            $person->setCellPhone($cleanPhone);
            $family->setHomePhone($cleanPhone);
        }
    }

    // Localisation
    $location = trim($row['localisation'] ?? '');
    if (!empty($location)) {
        $person->setCity($location);
        $family->setCity($location);
    }

    // Date de naissance
    $birthDate = trim($row['Date d\'anniversaire'] ?? '');
    if (!empty($birthDate) && $birthDate !== 'Actif') {
        $timestamp = strtotime($birthDate);
        if ($timestamp !== false) {
            $person->setBirthMonth(date('m', $timestamp));
            $person->setBirthDay(date('d', $timestamp));
            $person->setBirthYear(date('Y', $timestamp));
        }
    }

    // Genre (basé sur des heuristiques simples)
    $gender = 0; // Unknown
    $feminineEndings = ['e', 'a', 'ine', 'elle', 'ette', 'ie', 'yste'];
    if (any($firstName, fn($s) => in_array(strtolower(substr($s, -1)), $feminineEndings))) {
        $gender = 2; // Female
    } else {
        $gender = 1; // Male
    }
    $person->setGender($gender);

    $family->save($con);
    $person->save($con);

    $imported++;
    echo "Importé: $firstName $lastName\n";
}

$con->commit();

echo "\n=== Import terminé ===\n";
echo "Importés: $imported\n";
echo "Ignorés: $skipped\n";
echo "Doublons supprimés: $duplicatesDeleted\n";

// Fonction helper
function any($str, $callback) {
    return $callback($str);
}
