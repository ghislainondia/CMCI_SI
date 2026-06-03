<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\model\ChurchCRM\Family;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use Propel\Runtime\Collection\ObjectCollection;

class HouseAssemblyLeaderService
{
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
        if ($currentUser->isAdmin()) {
            return false;
        }

        return $this->familyScope->getCurrentUserFamilyId() !== null;
    }

    public function getLeaderFamilyId(): ?int
    {
        return $this->familyScope->getCurrentUserFamilyId();
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
