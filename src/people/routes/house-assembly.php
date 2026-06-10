<?php

use ChurchCRM\dto\ChurchVocabulary;
use ChurchCRM\dto\Photo;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\Service\HouseAssemblyLeaderService;
use ChurchCRM\Utils\InputUtils;
use ChurchCRM\view\PageHeader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

$app->get('/house-assembly', function (Request $request, Response $response): Response {
    $leaderService = new HouseAssemblyLeaderService();
    $homePath = $leaderService->getHomePath();
    if ($homePath !== null) {
        return $response
            ->withHeader('Location', SystemURLs::getRootPath() . '/' . $homePath)
            ->withStatus(302);
    }

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
    $familyId = $family !== null ? (int) $family->getId() : null;

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
        'familyId' => $familyId,
        'memberRows' => $memberRows,
        'memberCount' => count($memberRows),
    ];

    return $renderer->render($response, 'house-assembly-dashboard.php', $pageArgs);
});

// ─── POST: Remove a member from the house assembly ───────────────────────────
$app->post('/house-assembly/remove-member', function (Request $request, Response $response): Response {
    $leaderService = new HouseAssemblyLeaderService();
    if (!$leaderService->isHouseAssemblyLeader()) {
        return $response->withStatus(403);
    }

    $parsedBody = $request->getParsedBody();
    $personId = (int) ($parsedBody['personId'] ?? 0);

    if ($personId <= 0) {
        return $response->withStatus(400);
    }

    $person = PersonQuery::create()->findPk($personId);
    if ($person === null) {
        return $response->withStatus(404);
    }

    // Remove from house assembly by setting fam_id to null
    $person->setFamId(null);
    $person->save();

    return $response
        ->withHeader('Location', SystemURLs::getRootPath() . '/people/house-assembly')
        ->withStatus(302);
});
