<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\model\ChurchCRM\Family;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Slim\Exception\HttpForbiddenException;

class BertouaAccessService
{
    private UserGroupScopeService $groupScope;
    private UserFamilyScopeService $familyScope;

    private HouseAssemblyLeaderService $leaderService;

    public function __construct(
        ?UserGroupScopeService $groupScope = null,
        ?UserFamilyScopeService $familyScope = null,
        ?HouseAssemblyLeaderService $leaderService = null
    ) {
        $this->groupScope = $groupScope ?? new UserGroupScopeService();
        $this->familyScope = $familyScope ?? new UserFamilyScopeService();
        $this->leaderService = $leaderService ?? new HouseAssemblyLeaderService($this->familyScope);
    }

    public function isAdmin(): bool
    {
        return AuthenticationManager::getCurrentUser()->isAdmin();
    }

    public function canAccessBertouaModule(): bool
    {
        return $this->isAdmin() || $this->leaderService->isHouseAssemblyLeader();
    }

    /**
     * House assemblies (ChurchCRM families) the current user may select for note entry.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function getAccessibleAssemblies(): array
    {
        $familiesQuery = FamilyQuery::create()
            ->filterByDateDeactivated(null)
            ->orderByName();

        if (!$this->isAdmin()) {
            $this->familyScope->applyFamilyQueryScope($familiesQuery);
        }

        $assemblies = [];
        foreach ($familiesQuery->find() as $family) {
            $assemblies[] = [
                'id' => (int) $family->getId(),
                'name' => (string) $family->getName(),
            ];
        }

        if (!$this->isAdmin()) {
            $assemblies = $this->appendLeaderOwnFamilyIfMissing($assemblies);
        }

        return $assemblies;
    }

    /**
     * @param array<int, array{id: int, name: string}> $assemblies
     * @return array<int, array{id: int, name: string}>
     */
    private function appendLeaderOwnFamilyIfMissing(array $assemblies): array
    {
        $person = PersonQuery::create()->findPk((int) AuthenticationManager::getCurrentUser()->getId());
        if ($person === null) {
            return $assemblies;
        }

        $familyId = (int) $person->getFamId();
        if ($familyId <= 0) {
            return $assemblies;
        }

        foreach ($assemblies as $assembly) {
            if ((int) $assembly['id'] === $familyId) {
                return $assemblies;
            }
        }

        $family = FamilyQuery::create()
            ->filterById($familyId)
            ->filterByDateDeactivated(null)
            ->findOne();

        if ($family instanceof Family) {
            $assemblies[] = [
                'id' => (int) $family->getId(),
                'name' => (string) $family->getName(),
            ];
        }

        return $assemblies;
    }

    public function canAccessAssemblyFamily(int $familyId): bool
    {
        if ($familyId <= 0) {
            return false;
        }

        if ($this->isAdmin()) {
            return FamilyQuery::create()
                ->filterById($familyId)
                ->filterByDateDeactivated(null)
                ->count() > 0;
        }

        if ($this->familyScope->canAccessFamilyId($familyId)) {
            return true;
        }

        $person = PersonQuery::create()->findPk((int) AuthenticationManager::getCurrentUser()->getId());

        return $person !== null && (int) $person->getFamId() === $familyId;
    }

    public function assertCanAccessAssemblyFamily(int $familyId): void
    {
        if (!$this->canAccessAssemblyFamily($familyId)) {
            throw new HttpForbiddenException(
                null,
                gettext('You do not have access to this house assembly.')
            );
        }
    }

    /**
     * Members of the selected house assembly (family record).
     *
     * @return array<int, array{id: int, name: string, famId: int}>
     */
    public function getAssemblyMembers(int $familyId): array
    {
        $this->assertCanAccessAssemblyFamily($familyId);

        $query = PersonQuery::create()
            ->filterByFamId($familyId)
            ->filterByFamId(0, Criteria::NOT_EQUAL)
            ->orderByLastName()
            ->orderByFirstName();

        if (!$this->isAdmin()) {
            $person = PersonQuery::create()->findPk((int) AuthenticationManager::getCurrentUser()->getId());
            $leaderOwnFamily = $person !== null && (int) $person->getFamId() === $familyId;

            if (!$leaderOwnFamily) {
                $this->familyScope->applyPersonQueryScope($query);
            }
        }

        $members = [];
        foreach ($query->find() as $person) {
            $members[] = [
                'id' => (int) $person->getId(),
                'name' => $person->getFullName(),
                'famId' => (int) $person->getFamId(),
            ];
        }

        return $members;
    }

    /**
     * @deprecated Use canAccessAssemblyFamily()
     */
    public function canAccessAssemblyGroup(int $familyId): bool
    {
        return $this->canAccessAssemblyFamily($familyId);
    }

    /**
     * @deprecated Use assertCanAccessAssemblyFamily()
     */
    public function assertCanAccessAssemblyGroup(int $familyId): void
    {
        $this->assertCanAccessAssemblyFamily($familyId);
    }
}
