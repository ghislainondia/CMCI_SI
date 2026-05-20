<?php

/**
 * Script d'import de membres depuis un CSV avec suppression des doublons
 *
 * Usage: php scripts/import-members.php membre.csv
 */

require_once __DIR__ . '/../src/ChurchCRM/Bootstrapper.php';

use ChurchCRM\Bootstrapper;
use ChurchCRM\model\ChurchCRM\Person;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\model\ChurchCRM\Family;
use ChurchCRM\model\ChurchCRM\Person2group2roleP2g2r;
use ChurchCRM\Utils\LoggerUtils;

$logger = LoggerUtils::getAppLogger();

// Vérifier les arguments
if ($argc < 2) {
    echo "Usage: php {$argv[0]} <fichier.csv>\n";
    exit(1);
}

$csvFile = $argv[1];

if (!file_exists($csvFile)) {
    echo "Erreur: Le fichier $csvFile n'existe pas\n";
    exit(1);
}

echo "Lecture du fichier CSV: $csvFile\n";

// Lire le CSV
$lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (empty($lines)) {
    echo "Erreur: Le fichier CSV est vide\n";
    exit(1);
}

// Parser l'en-tête
$headers = str_getcsv(array_shift($lines));
echo "Colonnes trouvées: " . implode(', ', $headers) . "\n";
echo "Nombre de lignes à traiter: " . count($lines) . "\n\n";

// Index des colonnes importantes
$colMap = [
    'FirstName' => array_search('Prénom', $headers),
    'LastName' => array_search('Nom', $headers),
    'MaritalStatus' => array_search('Statut matrimonial', $headers),
    'BirthDate' => array_search('Date d\'anniversaire', $headers),
    'Phone' => array_search('Contact', $headers),
    'Address' => array_search('localisation', $headers),
    'Status' => array_search('statut du membre', $headers),
];

$toImport = [];
$duplicates = [];

// Identifier les doublons potentiels dans la base
echo "Recherche de doublons dans la base de données...\n";
foreach ($lines as $lineNum => $line) {
    $values = str_getcsv($line);

    $firstName = trim($values[$colMap['FirstName']] ?? '');
    $lastName = trim($values[$colMap['LastName']] ?? '');

    if (empty($firstName) || empty($lastName)) {
        continue;
    }

    // Rechercher les doublons (même nom + prénom)
    $existingPeople = PersonQuery::create()
        ->filterByFirstName($firstName)
        ->filterByLastName($lastName)
        ->find();

    if ($existingPeople->count() > 0) {
        echo "Doublon trouvé: $firstName $lastName ({$existingPeople->count()} occurrence(s))\n";
        foreach ($existingPeople as $person) {
            $duplicates[] = $person->getId();
            echo "  - ID: {$person->getId()}, Email: {$person->getEmail()}\n";
        }
    }

    $toImport[] = [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'maritalStatus' => trim($values[$colMap['MaritalStatus']] ?? ''),
        'birthDate' => trim($values[$colMap['BirthDate']] ?? ''),
        'phone' => trim($values[$colMap['Phone']] ?? ''),
        'address' => trim($values[$colMap['Address']] ?? ''),
        'status' => trim($values[$colMap['Status']] ?? ''),
    ];
}

echo "\n" . count($duplicates) . " doublon(s) à supprimer\n";
echo count($toImport) . " personnes à importer\n\n";

// Demander confirmation
if (count($duplicates) > 0) {
    echo "ATTENTION: Vous allez supprimer " . count($duplicates) . " personnes existantes.\n";
    echo "Voulez-vous continuer? (oui/non): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim(strtolower($line)) !== 'oui' && trim(strtolower($line)) !== 'yes' && trim(strtolower($line)) !== 'y') {
        echo "Annulation.\n";
        exit(0);
    }
}

// Supprimer les doublons
if (!empty($duplicates)) {
    echo "\nSuppression des doublons...\n";
    foreach (array_unique($duplicates) as $personId) {
        try {
            $person = PersonQuery::create()->findPk($personId);
            if ($person) {
                $familyId = $person->getFamId();
                echo "Suppression de {$person->getFullName()} (ID: $personId)... ";
                $person->delete();
                echo "OK\n";

                // Vérifier si la famille est maintenant vide
                if ($familyId) {
                    $family = FamilyQuery::create()->findPk($familyId);
                    if ($family && $family->countPeople() == 0) {
                        echo "  Famille vide (ID: $familyId), suppression... ";
                        $family->delete();
                        echo "OK\n";
                    }
                }
            }
        } catch (Exception $e) {
            echo "Erreur: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

// Importer les nouveaux membres
echo "Import des nouveaux membres...\n";
$con = \Propel\Runtime\Propel::getWriteConnection(\ChurchCRM\model\ChurchCRM\Map\PersonTableMap::DATABASE_NAME);
$con->beginTransaction();

try {
    $imported = 0;
    $skipped = 0;

    foreach ($toImport as $member) {
        $person = new Person();

        $person->setFirstName($member['firstName']);
        $person->setLastName($member['lastName']);

        // Téléphone
        if (!empty($member['phone'])) {
            $phone = preg_replace('/[^0-9+]/', '', $member['phone']);
            if (strlen($phone) >= 10) {
                $person->setCellPhone($phone);
            }
        }

        // Adresse - extraire la localisation
        if (!empty($member['address'])) {
            $person->setCity($member['address']);
        }

        // Date de naissance
        if (!empty($member['birthDate']) && $member['birthDate'] !== 'Actif') {
            $timestamp = strtotime($member['birthDate']);
            if ($timestamp !== false) {
                $person->setBirthMonth(date('m', $timestamp));
                $person->setBirthDay(date('d', $timestamp));
                $person->setBirthYear(date('Y', $timestamp));
            }
        }

        // Statut matrimonial (1=Célibataire, 2=Marié)
        if (!empty($member['maritalStatus'])) {
            $status = strtolower($member['maritalStatus']);
            if ($status === 'célibataire' || $status === 'celibataire') {
                $person->setFmrId(1); // Single
            } elseif ($status === 'marié' || $status === 'marie') {
                $person->setFmrId(2); // Married
            }
        }

        // Classification: "Actif" -> Member (1)
        if (!empty($member['status']) && strtolower($member['status']) === 'actif') {
            $person->setClsId(1); // Member
        } else {
            $person->setClsId(2); // Regular Attender
        }

        $person->setDateEntered(date('YmdHis'));
        $person->setEnteredBy(1); // Admin user
        $person->save($con);

        $imported++;
        echo "Importé: {$member['firstName']} {$member['lastName']}\n";
    }

    $con->commit();
    echo "\nImport terminé avec succès!\n";
    echo "Importés: $imported\n";
    echo "Ignorés: $skipped\n";

} catch (Exception $e) {
    $con->rollBack();
    echo "\nErreur lors de l'import: " . $e->getMessage() . "\n";
    exit(1);
}
