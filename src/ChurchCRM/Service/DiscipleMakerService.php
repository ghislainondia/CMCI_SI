<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\Utils\LoggerUtils;
use Propel\Runtime\Propel;

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

            if ($discipleMakerId !== null) {
                $discipleMaker = PersonQuery::create()->findPk($discipleMakerId);
                if ($discipleMaker === null) {
                    $this->logger->error("Disciple maker not found: $discipleMakerId");
                    return false;
                }

                if ($personId === $discipleMakerId) {
                    $this->logger->error("Person cannot be their own disciple maker: $personId");
                    return false;
                }
            }

            $this->persistDiscipleMakerId($personId, $discipleMakerId);

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
            $discipleMakerId = $this->getStoredDiscipleMakerId($personId);
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
                'photo' => $discipleMaker->getPhoto()->hasUploadedPhoto(),
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
                    'familyId' => $disciple->getFamId(),
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
            $total = PersonQuery::create()
                ->filterByDiscipleMakerId($discipleMakerId)
                ->count();

            return [
                'totalDisciples' => $total,
                'activeDisciples' => $total,
                'lastUpdated' => date('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error getting disciples stats: " . $e->getMessage());
            return [
                'totalDisciples' => 0,
                'activeDisciples' => 0,
                'lastUpdated' => null,
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
                        ->count(),
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
            $fromMaker = PersonQuery::create()->findPk($fromDiscipleMakerId);
            $toMaker = PersonQuery::create()->findPk($toDiscipleMakerId);

            if ($fromMaker === null || $toMaker === null) {
                $this->logger->error("One of the disciple makers not found");
                return 0;
            }

            if ($fromDiscipleMakerId === $toDiscipleMakerId) {
                $this->logger->error("Cannot transfer disciples to same person");
                return 0;
            }

            $connection = Propel::getConnection();
            $stmt = $connection->prepare(
                'UPDATE person_per SET per_DiscipleMakerID = :toId, per_EditedBy = :editedBy
                 WHERE per_DiscipleMakerID = :fromId'
            );
            $stmt->execute([
                'toId' => $toDiscipleMakerId,
                'fromId' => $fromDiscipleMakerId,
                'editedBy' => AuthenticationManager::getCurrentUser()->getId(),
            ]);
            $count = $stmt->rowCount();

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

    private function getStoredDiscipleMakerId(int $personId): ?int
    {
        $connection = Propel::getConnection();
        $stmt = $connection->prepare('SELECT per_DiscipleMakerID FROM person_per WHERE per_ID = :personId');
        $stmt->execute(['personId' => $personId]);
        $value = $stmt->fetchColumn();

        if ($value === false || $value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function persistDiscipleMakerId(int $personId, ?int $discipleMakerId): void
    {
        $connection = Propel::getConnection();
        $editedBy = AuthenticationManager::getCurrentUser()->getId();

        if ($discipleMakerId === null) {
            $stmt = $connection->prepare(
                'UPDATE person_per SET per_DiscipleMakerID = NULL, per_EditedBy = :editedBy WHERE per_ID = :personId'
            );
            $stmt->execute(['editedBy' => $editedBy, 'personId' => $personId]);
            return;
        }

        $stmt = $connection->prepare(
            'UPDATE person_per SET per_DiscipleMakerID = :makerId, per_EditedBy = :editedBy WHERE per_ID = :personId'
        );
        $stmt->execute([
            'makerId' => $discipleMakerId,
            'editedBy' => $editedBy,
            'personId' => $personId,
        ]);
    }
}
