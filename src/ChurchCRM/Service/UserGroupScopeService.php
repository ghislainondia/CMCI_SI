<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\model\ChurchCRM\Person2group2roleP2g2rQuery;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;

class UserGroupScopeService
{
    /**
     * Restrict person visibility to members of the connected user's group_id.
     * Admin users bypass this scope.
     */
    public function applyPersonQueryScope(PersonQuery $personQuery): PersonQuery
    {
        $currentUser = AuthenticationManager::getCurrentUser();

        if ($currentUser->isAdmin()) {
            return $personQuery;
        }

        $groupId = $this->getCurrentUserGroupId();
        if ($groupId === null) {
            // No group scope assigned: do not expose any member data.
            return $personQuery->filterById(-1);
        }

        return $personQuery
            ->usePerson2group2roleP2g2rQuery()
                ->filterByGroupId($groupId)
            ->endUse()
            ->groupById();
    }

    /**
     * Fetch group_id from user_usr for the connected user.
     */
    public function getCurrentUserGroupId(): ?int
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        $connection = Propel::getConnection();

        $statement = $connection->prepare('SELECT group_id FROM user_usr WHERE usr_per_ID = :userId LIMIT 1');
        $statement->bindValue(':userId', (int) $currentUser->getId(), \PDO::PARAM_INT);
        $statement->execute();

        $value = $statement->fetchColumn();
        if ($value === false || $value === null) {
            return null;
        }

        $groupId = (int) $value;
        return $groupId > 0 ? $groupId : null;
    }

    /**
     * Check if current user can access a specific person by group scope.
     */
    public function canAccessPersonId(int $personId): bool
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        if ($currentUser->isAdmin()) {
            return true;
        }

        $groupId = $this->getCurrentUserGroupId();
        if ($groupId === null) {
            return false;
        }

        return Person2group2roleP2g2rQuery::create()
            ->filterByPersonId($personId)
            ->filterByGroupId($groupId)
            ->count() > 0;
    }

    /**
     * Restrict family visibility to families containing at least one person
     * in the connected user's group_id. Admin users bypass this scope.
     */
    public function applyFamilyQueryScope(FamilyQuery $familyQuery): FamilyQuery
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        if ($currentUser->isAdmin()) {
            return $familyQuery;
        }

        $groupId = $this->getCurrentUserGroupId();
        if ($groupId === null) {
            return $familyQuery->filterById(-1);
        }

        return $familyQuery
            ->usePersonQuery()
                ->usePerson2group2roleP2g2rQuery()
                    ->filterByGroupId($groupId)
                ->endUse()
            ->endUse()
            ->groupById();
    }

    /**
     * Check if current user can access a specific family by group scope.
     */
    public function canAccessFamilyId(int $familyId): bool
    {
        $currentUser = AuthenticationManager::getCurrentUser();
        if ($currentUser->isAdmin()) {
            return true;
        }

        $groupId = $this->getCurrentUserGroupId();
        if ($groupId === null) {
            return false;
        }

        return PersonQuery::create()
            ->filterByFamId($familyId)
            ->filterByFamId(0, Criteria::NOT_EQUAL)
            ->usePerson2group2roleP2g2rQuery()
                ->filterByGroupId($groupId)
            ->endUse()
            ->count() > 0;
    }
}
