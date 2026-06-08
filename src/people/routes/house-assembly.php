<?php

use ChurchCRM\dto\ChurchVocabulary;
use ChurchCRM\dto\Photo;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Service\HouseAssemblyLeaderService;
use ChurchCRM\Utils\InputUtils;
use ChurchCRM\view\PageHeader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

$app->get('/house-assembly', function (Request $request, Response $response): Response {
    $leaderService = new HouseAssemblyLeaderService();
    if (!$leaderService->isHouseAssemblyLeader()) {
        return $response
            ->withHeader('Location', SystemURLs::getRootPath() . '/people/dashboard')
            ->withStatus(302);
    }

    $family = $leaderService->getScopedAssemblyFamily();
    $members = $leaderService->getAssemblyMembers();

    $memberRows = [];
    foreach ($members as $person) {
        $photo = new Photo('Person', (int) $person->getId());
        $memberRows[] = [
            'id' => (int) $person->getId(),
            'name' => $person->getFullName(),
            'email' => (string) $person->getEmail(),
            'phone' => (string) $person->getHomePhone(),
            'photoUrl' => $photo->getPhotoURI(),
            'viewUrl' => SystemURLs::getRootPath() . '/people/view/' . (int) $person->getId(),
        ];
    }

    $assemblyName = $family !== null ? (string) $family->getName() : ChurchVocabulary::houseAssembly();

    $renderer = new PhpRenderer(__DIR__ . '/../views/');
    $pageArgs = [
        'sRootPath' => SystemURLs::getRootPath(),
        'sPageTitle' => ChurchVocabulary::houseAssemblyDashboard(),
        'sPageSubtitle' => sprintf(
            gettext('Members of %s'),
            $assemblyName
        ),
        'aBreadcrumbs' => PageHeader::breadcrumbs([
            [ChurchVocabulary::houseAssembly()],
        ]),
        'assemblyName' => $assemblyName,
        'memberRows' => $memberRows,
        'memberCount' => count($memberRows),
    ];

    return $renderer->render($response, 'house-assembly-dashboard.php', $pageArgs);
});
