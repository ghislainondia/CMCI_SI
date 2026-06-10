<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\model\ChurchCRM\Family;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Propel;

class HouseAssemblyLeaderService
{
    /** @deprecated Use getHomePath() — leaders land on their family profile page */
    public const DEFAULT_HOME_PATH = 'people/house-assembly';

    private UserFamilyScopeService $familyScope;

    public function __construct(?UserFamilyScopeService $familyScope = null)
    {
        $this->familyScope = $familyScope ?? new UserFamilyScopeService();
    }

    /**
     * Non-admin user with a primary fam_id on user_usr (house assembly scope).
     */
    public function isHouseAssemblyLeader(): bool
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        if ($currentUser === null || $currentUser->isAdmin()) {
            return false;
        }

        return $this->fetchFamIdFromUserRow((int) $currentUser->getId()) !== null;
    }

    public function getLeaderFamilyId(): ?int
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        if ($currentUser === null) {
            return null;
        }

        return $this->fetchFamIdFromUserRow((int) $currentUser->getId());
    }

    /**
     * Page d'accueil après connexion (fiche assemblée = capture d'écran attendue).
     */
    public function getHomePath(): ?string
    {
        if (!$this->isHouseAssemblyLeader()) {
            return null;
        }

        $familyId = $this->getLeaderFamilyId();
        if ($familyId === null) {
            return null;
        }

        return 'people/family/' . $familyId;
    }

    private function fetchFamIdFromUserRow(int $personId): ?int
    {
        try {
            $connection = Propel::getConnection();
            $statement = $connection->prepare('SELECT fam_id FROM user_usr WHERE usr_per_ID = :userId LIMIT 1');
            $statement->bindValue(':userId', $personId, \PDO::PARAM_INT);
            $statement->execute();
            $familyId = $statement->fetchColumn();

            if ($familyId !== false && $familyId !== null && (int) $familyId > 0) {
                return (int) $familyId;
            }
        } catch (\Exception $e) {
            // Colonne fam_id absente si migration 7.3.6 non appliquée
        }

        return null;
    }

    public function getScopedAssemblyFamily(): ?Family
    {
        $familyId = $this->getLeaderFamilyId();
        if ($familyId === null) {
            return null;
        }

        return FamilyQuery::create()->findPk($familyId);
    }

    /**
     * Members of the leader's scoped house assembly (family).
     *
     * @return ObjectCollection
     */
    public function getAssemblyMembers(): ObjectCollection
    {
        $familyId = $this->getLeaderFamilyId();
        if ($familyId === null) {
            return new ObjectCollection();
        }

        $query = PersonQuery::create()
            ->filterByFamId($familyId)
            ->orderByLastName()
            ->orderByFirstName();

        return $this->familyScope->applyPersonQueryScope($query)->find();
    }
}
