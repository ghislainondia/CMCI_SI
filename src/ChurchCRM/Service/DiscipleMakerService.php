<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use ChurchCRM\Utils\LoggerUtils;

class DiscipleMakerService
{
    private $logger;

    public function __construct()
    {
        $this->logger = LoggerUtils::getAppLogger();
    }

    /**
     * Définir le faiseur de disciple pour une personne
     *
     * @param int $personId ID de la personne (disciple)
     * @param int|null $discipleMakerId ID du faiseur de disciple (null pour retirer)
     * @return bool Success
     */
    public function setDiscipleMaker(int $personId, ?int $discipleMakerId): bool
    {
        try {
            $person = PersonQuery::create()->findPk($personId);
            if ($person === null) {
                $this->logger->error("Person not found: $personId");
                return false;
            }

            // Vérifier que le faiseur de disciple existe si spécifié
            if ($discipleMakerId !== null) {
                $discipleMaker = PersonQuery::create()->findPk($discipleMakerId);
                if ($discipleMaker === null) {
                    $this->logger->error("Disciple maker not found: $discipleMakerId");
                    return false;
                }

                // Empêcher qu'une personne soit son propre faiseur de disciple
                if ($personId === $discipleMakerId) {
                    $this->logger->error("Person cannot be their own disciple maker: $personId");
                    return false;
                }
            }

            $person->setDiscipleMakerId($discipleMakerId);
            $person->save();

            $currentUser = AuthenticationManager::getCurrentUser();
            $person->setEditedBy($currentUser->getId());
            $person->save();

            $action = $discipleMakerId !== null ? "assigned to disciple maker $discipleMakerId" : "removed from disciple maker";
            $this->logger->info("Person $personId $action");

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error setting disciple maker: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir le faiseur de disciple d'une personne
     *
     * @param int $personId ID de la personne
     * @return array|null Informations du faiseur de disciple ou null
     */
    public function getDiscipleMaker(int $personId): ?array
    {
        try {
            $person = PersonQuery::create()->findPk($personId);
            if ($person === null) {
                return null;
            }

            $discipleMakerId = $person->getDiscipleMakerId();
            if ($discipleMakerId === null) {
                return null;
            }

            $discipleMaker = PersonQuery::create()->findPk($discipleMakerId);
            if ($discipleMaker === null) {
                return null;
            }

            return [
                'id' => $discipleMaker->getId(),
                'firstName' => $discipleMaker->getFirstName(),
                'lastName' => $discipleMaker->getLastName(),
                'fullName' => $discipleMaker->getFullName(),
                'email' => $discipleMaker->getEmail(),
                'photo' => $discipleMaker->getPhoto()->hasUploadedPhoto()
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error getting disciple maker: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir tous les disciples d'une personne (les personnes qu'elle discipule)
     *
     * @param int $discipleMakerId ID du faiseur de disciple
     * @return array Liste des disciples
     */
    public function getDisciples(int $discipleMakerId): array
    {
        try {
            $disciples = PersonQuery::create()
                ->filterByDiscipleMakerId($discipleMakerId)
                ->find();

            $result = [];
            foreach ($disciples as $disciple) {
                $result[] = [
                    'id' => $disciple->getId(),
                    'firstName' => $disciple->getFirstName(),
                    'lastName' => $disciple->getLastName(),
                    'fullName' => $disciple->getFullName(),
                    'email' => $disciple->getEmail(),
                    'phone' => $disciple->getCellPhone(),
                    'photo' => $disciple->getPhoto()->hasUploadedPhoto(),
                    'familyId' => $disciple->getFamId()
                ];
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Error getting disciples: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtenir les statistiques de discipulat pour une personne
     *
     * @param int $discipleMakerId ID du faiseur de disciple
     * @return array Statistiques
     */
    public function getDisciplesStats(int $discipleMakerId): array
    {
        try {
            $disciples = PersonQuery::create()
                ->filterByDiscipleMakerId($discipleMakerId)
                ->find();

            return [
                'totalDisciples' => count($disciples),
                'activeDisciples' => count($disciples), // Tous sont considérés actifs pour l'instant
                'lastUpdated' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error getting disciples stats: " . $e->getMessage());
            return [
                'totalDisciples' => 0,
                'activeDisciples' => 0,
                'lastUpdated' => null
            ];
        }
    }

    /**
     * Obtenir toutes les personnes disponibles comme faiseurs de disciple
     *
     * @return array Liste des faiseurs de disciple potentiels
     */
    public function getPotentialDiscipleMakers(): array
    {
        try {
            $people = PersonQuery::create()
                ->orderByLastName()
                ->orderByFirstName()
                ->find();

            $result = [];
            foreach ($people as $person) {
                $result[] = [
                    'id' => $person->getId(),
                    'firstName' => $person->getFirstName(),
                    'lastName' => $person->getLastName(),
                    'fullName' => $person->getFullName(),
                    'email' => $person->getEmail(),
                    'photo' => $person->getPhoto()->hasUploadedPhoto(),
                    'disciplesCount' => PersonQuery::create()
                        ->filterByDiscipleMakerId($person->getId())
                        ->count()
                ];
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Error getting potential disciple makers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Transférer tous les disciples d'un faiseur de disciple à un autre
     *
     * @param int $fromDiscipleMakerId ID du faiseur de disciple actuel
     * @param int $toDiscipleMakerId ID du nouveau faiseur de disciple
     * @return int Nombre de disciples transférés
     */
    public function transferDisciples(int $fromDiscipleMakerId, int $toDiscipleMakerId): int
    {
        try {
            // Vérifier que les deux personnes existent
            $fromMaker = PersonQuery::create()->findPk($fromDiscipleMakerId);
            $toMaker = PersonQuery::create()->findPk($toDiscipleMakerId);

            if ($fromMaker === null || $toMaker === null) {
                $this->logger->error("One of the disciple makers not found");
                return 0;
            }

            // Empêcher le transfert vers soi-même
            if ($fromDiscipleMakerId === $toDiscipleMakerId) {
                $this->logger->error("Cannot transfer disciples to same person");
                return 0;
            }

            // Mettre à jour tous les disciples
            $disciples = PersonQuery::create()
                ->filterByDiscipleMakerId($fromDiscipleMakerId)
                ->find();

            $count = 0;
            foreach ($disciples as $disciple) {
                $disciple->setDiscipleMakerId($toDiscipleMakerId);
                $disciple->save();
                $count++;
            }

            $this->logger->info("Transferred $count disciples from $fromDiscipleMakerId to $toDiscipleMakerId");

            return $count;
        } catch (\Exception $e) {
            $this->logger->error("Error transferring disciples: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Supprimer le faiseur de disciple d'une personne
     *
     * @param int $personId ID de la personne
     * @return bool Success
     */
    public function removeDiscipleMaker(int $personId): bool
    {
        return $this->setDiscipleMaker($personId, null);
    }
}
