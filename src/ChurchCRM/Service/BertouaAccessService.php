<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\model\ChurchCRM\GroupQuery;
use ChurchCRM\model\ChurchCRM\Person2group2roleP2g2rQuery;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use Slim\Exception\HttpForbiddenException;

class BertouaAccessService
{
    private UserGroupScopeService $groupScope;

    private HouseAssemblyLeaderService $leaderService;

    public function __construct(
        ?UserGroupScopeService $groupScope = null,
        ?HouseAssemblyLeaderService $leaderService = null
    ) {
        $this->groupScope = $groupScope ?? new UserGroupScopeService();
        $this->leaderService = $leaderService ?? new HouseAssemblyLeaderService($this->groupScope);
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
     * House assemblies (groups) the current user may select for note entry.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function getAccessibleAssemblies(): array
    {
        if ($this->isAdmin()) {
            $assemblies = [];
            foreach (GroupQuery::create()->orderByName()->find() as $group) {
                $assemblies[] = [
                    'id' => (int) $group->getId(),
                    'name' => (string) $group->getName(),
                ];
            }

            return $assemblies;
        }

        $groupId = $this->groupScope->getCurrentUserGroupId();
        if ($groupId === null) {
            return [];
        }

        $group = GroupQuery::create()->findPk($groupId);
        if ($group === null) {
            return [];
        }

        return [[
            'id' => (int) $group->getId(),
            'name' => (string) $group->getName(),
        ]];
    }

    public function canAccessAssemblyGroup(int $groupId): bool
    {
        if ($groupId <= 0) {
            return false;
        }

        if ($this->isAdmin()) {
            return GroupQuery::create()->filterById($groupId)->count() > 0;
        }

        $scopedGroupId = $this->groupScope->getCurrentUserGroupId();

        return $scopedGroupId !== null && $scopedGroupId === $groupId;
    }

    public function assertCanAccessAssemblyGroup(int $groupId): void
    {
        if (!$this->canAccessAssemblyGroup($groupId)) {
            throw new HttpForbiddenException(
                null,
                gettext('You do not have access to this house assembly.')
            );
        }
    }

    public function isPersonInAssemblyGroup(int $personId, int $groupId): bool
    {
        if ($personId <= 0 || $groupId <= 0) {
            return false;
        }

        return Person2group2roleP2g2rQuery::create()
            ->filterByPersonId($personId)
            ->filterByGroupId($groupId)
            ->count() > 0;
    }

    /**
     * @return array<int, array{id: int, name: string, famId: int}>
     */
    public function getAssemblyMembers(int $groupId): array
    {
        $this->assertCanAccessAssemblyGroup($groupId);

        $query = PersonQuery::create()
            ->usePerson2group2roleP2g2rQuery()
                ->filterByGroupId($groupId)
            ->endUse()
            ->groupById()
            ->orderByLastName()
            ->orderByFirstName();

        if (!$this->isAdmin()) {
            $this->groupScope->applyPersonQueryScope($query);
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
}
