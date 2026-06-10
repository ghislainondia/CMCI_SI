<?php

namespace ChurchCRM\Slim\Middleware\Api;

use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\Service\UserFamilyScopeService;

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

        if (!UserFamilyScopeService::canUserAccessFamily($familyId)) {
            return null;
        }

        return $family;
    }

    protected function getNotFoundMessage(): string
    {
        return gettext('Family not found');
    }
}
