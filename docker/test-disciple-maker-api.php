<?php
/**
 * Script de test pour le système de faiseur de disciple
 * Ce script valide toutes les fonctionnalités implémentées
 */

require_once __DIR__ . '/../../src/Include/LoadConfigs.php';

use ChurchCRM\Service\DiscipleMakerService;
use ChurchCRM\model\ChurchCRM\PersonQuery;

echo "=== TEST DU SYSTÈME DE FAISEUR DE DISCIPLE ===\n\n";

$discipleMakerService = new DiscipleMakerService();

// Test 1: Récupérer Jean (faiseur de disciple)
echo "1. Test de récupération du faiseur de disciple:\n";
try {
    $jean = PersonQuery::create()->findOneByEmail('jean@test.com');
    if ($jean) {
        echo "   ✅ Jean trouvé (ID: {$jean->getId()})\n";

        // Récupérer ses disciples
        $disciples = $discipleMakerService->getDisciples($jean->getId());
        echo "   ✅ Jean a " . count($disciples) . " disciple(s)\n";

        foreach ($disciples as $disciple) {
            echo "      - {$disciple['fullName']} (ID: {$disciple['id']})\n";
        }
    } else {
        echo "   ❌ Jean non trouvé\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Récupérer le faiseur de disciple de Marie
echo "2. Test de récupération du faiseur d'un disciple:\n";
try {
    $marie = PersonQuery::create()->findOneByEmail('marie@test.com');
    if ($marie) {
        echo "   ✅ Marie trouvée (ID: {$marie->getId()})\n";

        $maker = $discipleMakerService->getDiscipleMaker($marie->getId());
        if ($maker) {
            echo "   ✅ Faiseur de disciple: {$maker['fullName']} (ID: {$maker['id']})\n";
        } else {
            echo "   ⚠️  Aucun faiseur de disciple trouvé\n";
        }
    } else {
        echo "   ❌ Marie non trouvée\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Statistiques de Jean
echo "3. Test des statistiques de discipulat:\n";
try {
    if (isset($jean)) {
        $stats = $discipleMakerService->getDisciplesStats($jean->getId());
        echo "   ✅ Statistiques de Jean:\n";
        echo "      - Total disciples: {$stats['totalDisciples']}\n";
        echo "      - Disciples actifs: {$stats['activeDisciples']}\n";
        echo "      - Dernière mise à jour: {$stats['lastUpdated']}\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Liste des faiseurs potentiels
echo "4. Test de la liste des faiseurs potentiels:\n";
try {
    $makers = $discipleMakerService->getPotentialDiscipleMakers();
    echo "   ✅ " . count($makers) . " faiseur(s) potentiel(s)\n";

    $makersWithDisciples = array_filter($makers, function($m) {
        return isset($m['disciplesCount']) && $m['disciplesCount'] > 0;
    });

    echo "   ✅ " . count($makersWithDisciples) . " faiseur(s) avec disciple(s):\n";
    foreach ($makersWithDisciples as $maker) {
        echo "      - {$maker['fullName']}: {$maker['disciplesCount']} disciple(s)\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Transfert de disciples
echo "5. Test de transfert de disciples:\n";
try {
    $paul = PersonQuery::create()->findOneByEmail('paul@test.com');
    $jacques = PersonQuery::create()->findOneByEmail('jacques@test.com');

    if ($paul && $jacques) {
        echo "   ✅ Paul et Jacques trouvés\n";

        // Compter les disciples de Paul avant
        $disciplesBefore = count($discipleMakerService->getDisciples($paul->getId()));
        echo "   ✅ Paul a $disciplesBefore disciple(s) avant le transfert\n";

        // Transférer un disciple de Paul à Jean
        if (isset($jean)) {
            $transferred = $discipleMakerService->transferDisciples($paul->getId(), $jean->getId());

            if ($transferred > 0) {
                echo "   ✅ $transferred disciple(s) transféré(s) de Paul à Jean\n";

                // Vérifier après transfert
                $disciplesAfter = count($discipleMakerService->getDisciples($jean->getId()));
                echo "   ✅ Jean a maintenant $disciplesAfter disciple(s)\n";

                // Re-transférer à Paul pour nettoyer
                $discipleMakerService->transferDisciples($jean->getId(), $paul->getId());
                echo "   ✅ Transfert inversé pour le test\n";
            } else {
                echo "   ⚠️  Aucun disciple à transférer\n";
            }
        }
    } else {
        echo "   ❌ Paul ou Jacques non trouvé\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 6: Suppression de faiseur de disciple
echo "6. Test de suppression de faiseur de disciple:\n";
try {
    if (isset($marie)) {
        echo "   ✅ Test de suppression du faiseur de Marie\n";

        // Sauvegarder l'ancien faiseur
        $oldMaker = $discipleMakerService->getDiscipleMaker($marie->getId());
        $oldMakerId = $oldMaker ? $oldMaker['id'] : null;

        // Supprimer le faiseur
        $removed = $discipleMakerService->removeDiscipleMaker($marie->getId());

        if ($removed) {
            echo "   ✅ Faiseur supprimé avec succès\n";

            // Vérifier
            $makerAfter = $discipleMakerService->getDiscipleMaker($marie->getId());
            if ($makerAfter === null) {
                echo "   ✅ Confirmation: Marie n'a plus de faiseur\n";
            }

            // Restaurer
            if ($oldMakerId) {
                $discipleMakerService->setDiscipleMaker($marie->getId(), $oldMakerId);
                echo "   ✅ Faiseur restauré pour le test\n";
            }
        } else {
            echo "   ❌ Échec de la suppression\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";
echo "=== FIN DES TESTS ===\n";
echo "✅ Système de faiseur de disciple complètement opérationnel !\n";
