<?php

namespace ChurchCRM\Slim\Middleware\Api;

use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\Service\UserFamilyScopeService;
use ChurchCRM\Service\UserGroupScopeService;

class PersonMiddleware extends AbstractEntityMiddleware
{
    public function __construct(private readonly string $routeParamName = 'personId') {}

    protected function getRouteParamName(): string
    {
        return $this->routeParamName;
    }

    protected function getAttributeName(): string
    {
        return 'person';
    }

    protected function loadEntity(string $id): mixed
    {
        $personId = (int) $id;
        $person = PersonQuery::create()->findPk($personId);
        if ($person === null) {
            return null;
        }

        $groupScope = new UserGroupScopeService();
        $familyScope = new UserFamilyScopeService();
        // Allow access if either group scope OR family scope permits it
        if (!$groupScope->canAccessPersonId($personId) && !$familyScope->canAccessPersonId($personId)) {
            return null;
        }

        return $person;
    }

    protected function getNotFoundMessage(): string
    {
        return gettext('Person not found');
    }
}
