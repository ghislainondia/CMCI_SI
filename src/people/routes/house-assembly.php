<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Service\HouseAssemblyLeaderService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/house-assembly', function (Request $request, Response $response): Response {
    $leaderService = new HouseAssemblyLeaderService();
    if (!$leaderService->isHouseAssemblyLeader()) {
        return $response
            ->withHeader('Location', SystemURLs::getRootPath() . '/people/dashboard')
            ->withStatus(302);
    }

    // Legacy URL: keep it valid, but use the canonical family profile.
    return $response
        ->withHeader('Location', SystemURLs::getRootPath() . '/' . $leaderService->getHomePath())
        ->withStatus(302);
});
