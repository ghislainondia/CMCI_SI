<?php

namespace ChurchCRM\Slim\Middleware\Api;

use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\Service\UserFamilyScopeService;
use ChurchCRM\Service\UserGroupScopeService;

class FamilyMiddleware extends AbstractEntityMiddleware
{
    protected function getRouteParamName(): string
    {
        return 'familyId';
    }

    protected function getAttributeName(): string
    {
        return 'family';
    }

    protected function loadEntity(string $id): mixed
    {
        $familyId = (int) $id;
        $family = FamilyQuery::create()->findPk($familyId);
        if ($family === null) {
            return null;
        }

        $groupScope = new UserGroupScopeService();
        $familyScope = new UserFamilyScopeService();
        if (!$groupScope->canAccessFamilyId($familyId) && !$familyScope->canAccessFamilyId($familyId)) {
            return null;
        }

        return $family;
    }

    protected function getNotFoundMessage(): string
    {
        return gettext('Family not found');
    }
}
