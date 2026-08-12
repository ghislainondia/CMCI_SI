<?php

use ChurchCRM\dto\ChurchMetaData;
use ChurchCRM\dto\Photo;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\model\ChurchCRM\EventQuery;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\Service\HouseAssemblyLeaderService;
use ChurchCRM\Utils\DateTimeUtils;
use ChurchCRM\view\PageHeader;
use Propel\Runtime\ActiveQuery\Criteria;
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
    if ($family === null) {
        return $response
            ->withHeader('Location', SystemURLs::getRootPath() . '/people/family')
            ->withStatus(302);
    }

    $today = DateTimeUtils::getStartOfToday();
    $weekEnd = (clone $today)->modify('+7 days')->setTime(23, 59, 59);
    $upcomingPrograms = [];
    foreach (EventQuery::create()
        ->filterByInActive(1, Criteria::NOT_EQUAL)
        ->filterByStart(['min' => $today, 'max' => $weekEnd])
        ->orderByStart()
        ->limit(3)
        ->find() as $event) {
        $upcomingPrograms[] = [
            'title' => (string) $event->getTitle(),
            'when' => (string) $event->getStart('D d M · H:i'),
            'end' => (string) $event->getEnd('H:i'),
        ];
    }

    $recentProfiles = [];
    foreach (PersonQuery::create()
        ->filterByFamId((int) $family->getId())
        ->orderByDateLastEdited(Criteria::DESC)
        ->limit(5)
        ->find() as $person) {
        $photo = new Photo('Person', (int) $person->getId());
        $recentProfiles[] = [
            'id' => (int) $person->getId(),
            'name' => (string) $person->getFullName(),
            'initials' => strtoupper(mb_substr((string) $person->getFirstName(), 0, 1) . mb_substr((string) $person->getLastName(), 0, 1)),
            'photoUrl' => $photo->getPhotoURI(),
            'updatedAt' => $person->getDateLastEdited()?->format('d/m/Y') ?? '',
        ];
    }

    $renderer = new PhpRenderer(__DIR__ . '/../views/');
    return $renderer->render($response, 'house-assembly-dashboard.php', [
        'sRootPath' => SystemURLs::getRootPath(),
        'sPageTitle' => gettext('Dashboard'),
        'sPageSubtitle' => sprintf(gettext('Welcome to the dashboard for %s'), $family->getName()),
        'aBreadcrumbs' => PageHeader::breadcrumbs([[gettext('Dashboard')]]),
        'churchName' => ChurchMetaData::getChurchName() ?: 'ChurchCRM',
        'assemblyName' => (string) $family->getName(),
        'memberCount' => $leaderService->getAssemblyMembers()->count(),
        'upcomingPrograms' => $upcomingPrograms,
        'recentProfiles' => $recentProfiles,
        'familyUrl' => SystemURLs::getRootPath() . '/' . $leaderService->getFamilyPath(),
        'meetingsUrl' => SystemURLs::getRootPath() . '/meeting/dashboard',
        'calendarUrl' => SystemURLs::getRootPath() . '/event/calendars',
    ]);
});
