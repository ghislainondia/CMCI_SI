<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;

/**
 * Family-based scope service for house assembly leaders.
 * Similar to UserGroupScopeService but filters by family_id instead of group_id.
 */
class UserFamilyScopeService
{
    /**
     * Restrict person visibility to members of the connected user's family.
     * Admin users bypass this scope.
     */
    public function applyPersonQueryScope(PersonQuery $personQuery): PersonQuery
    {
        $currentUser = AuthenticationManager::getCurrentUser();

        if ($currentUser->isAdmin()) {
            return $personQuery;
        }

        $familyId = $this->getCurrentUserFamilyId();
        if ($familyId === null) {
            // No family scope assigned: do not expose any member data.
            return $personQuery->filterById(-1);
        }

        return $personQuery->filterByFamId($familyId);
    }

    /**
     * Fetch family_id from user_usr for the connected user.
     */
    public function getCurrentUserFamilyId(): ?int
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        $connection = Propel::getConnection();

        $statement = $connection->prepare('SELECT fam_id FROM user_usr WHERE usr_per_ID = :userId LIMIT 1');
        $statement->bindValue(':userId', (int) $currentUser->getId(), \PDO::PARAM_INT);
        $statement->execute();

        $value = $statement->fetchColumn();
        if ($value === false || $value === null) {
            return null;
        }

        $familyId = (int) $value;
        return $familyId > 0 ? $familyId : null;
    }

    /**
     * Check if current user can access a specific person by family scope.
     */
    public function canAccessPersonId(int $personId): bool
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        if ($currentUser->isAdmin()) {
            return true;
        }

        $familyId = $this->getCurrentUserFamilyId();
        if ($familyId === null) {
            return false;
        }

        $person = PersonQuery::create()->findPk($personId);
        if ($person === null) {
            return false;
        }

        return $person->getFamId() == $familyId;
    }

    /**
     * Restrict family visibility to only the connected user's family.
     * Admin users bypass this scope.
     */
    public function applyFamilyQueryScope(FamilyQuery $familyQuery): FamilyQuery
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        if ($currentUser->isAdmin()) {
            return $familyQuery;
        }

        $familyId = $this->getCurrentUserFamilyId();
        if ($familyId === null) {
            return $familyQuery->filterById(-1);
        }

        return $familyQuery->filterById($familyId);
    }

    /**
     * Check if current user can access a specific family by family scope.
     */
    public function canAccessFamilyId(int $familyId): bool
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        if ($currentUser->isAdmin()) {
            return true;
        }

        $userFamilyId = $this->getCurrentUserFamilyId();
        if ($userFamilyId === null) {
            return false;
        }

        return $userFamilyId === $familyId;
    }

    /**
     * True if the current user may access this family via group_id OR fam_id scope.
     */
    public static function canUserAccessFamily(int $familyId): bool
    {
        $groupScope = new UserGroupScopeService();
        if ($groupScope->canAccessFamilyId($familyId)) {
            return true;
        }

        return (new self())->canAccessFamilyId($familyId);
    }
}
