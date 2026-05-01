<?php

namespace ChurchCRM\Slim\Middleware\Api;

use ChurchCRM\model\ChurchCRM\PersonQuery;
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

        $scopeService = new UserGroupScopeService();
        if (!$scopeService->canAccessPersonId($personId)) {
            return null;
        }

        return $person;
    }

    protected function getNotFoundMessage(): string
    {
        return gettext('Person not found');
    }
}
