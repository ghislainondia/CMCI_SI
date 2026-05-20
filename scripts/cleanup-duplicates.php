<?php
/**
 * Script de nettoyage des doublons dans la base de données ChurchCRM
 * Lit un fichier CSV et supprime toutes les personnes correspondantes
 */

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Erreur: Impossible de trouver autoload.php\n");
}

require_once $autoloadPath;

use ChurchCRM\Bootstrapper;
use ChurchCRM\model\ChurchCRM\Person;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\model\ChurchCRM\FamilyQuery;

// Initialiser l'application
Bootstrapper::initApp();

echo "Script de nettoyage des doublons ChurchCRM\n";
echo "==========================================\n\n";

// Lire le fichier CSV
$csvFile = __DIR__ . '/../membre.csv';
if (!file_exists($csvFile)) {
    die("Erreur: Le fichier $csvFile n'existe pas\n");
}

$handle = fopen($csvFile, 'r');
if ($handle === false) {
    die("Erreur: Impossible d'ouvrir le fichier\n");
}

// Lire l'en-tête
$headers = fgetcsv($handle, 0, ';');
$firstNameIdx = array_search('Prénom', $headers);
$lastNameIdx = array_search('Nom', $headers);

if ($firstNameIdx === false || $lastNameIdx === false) {
    die("Erreur: Colonnes Prénom/Nom non trouvées\n");
}

$peopleToDelete = [];

// Lire les données
while (($data = fgetcsv($handle, 0, ';')) !== false) {
    $firstName = trim($data[$firstNameIdx]);
    $lastName = trim($data[$lastNameIdx]);

    if (empty($firstName) || empty($lastName)) {
        continue;
    }

    $peopleToDelete[] = ['first' => $firstName, 'last' => $lastName];
}

fclose($handle);

echo "Trouvé " . count($peopleToDelete) . " personnes dans le CSV\n\n";

// Rechercher et supprimer les doublons
$deleted = 0;
$notFound = 0;

foreach ($peopleToDelete as $person) {
    $existing = PersonQuery::create()
        ->filterByFirstName($person['first'])
        ->filterByLastName($person['last'])
        ->find();

    if ($existing->count() > 0) {
        foreach ($existing as $p) {
            $familyId = $p->getFamId();
            echo "Suppression: {$p->getFullName()} (ID: {$p->getId()})... ";
            $p->delete();
            echo "OK\n";
            $deleted++;

            // Vérifier si la famille est vide
            if ($familyId) {
                $family = FamilyQuery::create()->findPk($familyId);
                if ($family && $family->countPeople() == 0) {
                    echo "  Famille vide (ID: $familyId), suppression... ";
                    $family->delete();
                    echo "OK\n";
                }
            }
        }
    } else {
        $notFound++;
    }
}

echo "\n=== Résumé ===\n";
echo "Supprimées: $deleted\n";
echo "Non trouvées: $notFound\n";
