<?php

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Service\MeetingService;
use ChurchCRM\view\PageHeader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

$app->get('/', function (Request $request, Response $response): Response {
    return $response
        ->withHeader('Location', SystemURLs::getRootPath() . '/meetings/dashboard')
        ->withStatus(302);
});

$app->get('/dashboard', function (Request $request, Response $response): Response {
    $meetingService = new MeetingService();
    $meetings = $meetingService->getAllMeetings();
    $upcoming = [];
    $past = [];
    $now = time();

    foreach ($meetings as $meeting) {
        $ts = strtotime($meeting['meetingDateTime']);
        if ($ts >= $now) {
            $upcoming[] = $meeting;
        } else {
            $past[] = $meeting;
        }
    }

    $canEdit = AuthenticationManager::getCurrentUser()->isEditRecordsEnabled();

    $renderer = new PhpRenderer(__DIR__ . '/../views/');

    return $renderer->render($response, 'dashboard.php', [
        'sRootPath' => SystemURLs::getRootPath(),
        'sPageTitle' => gettext('Meetings'),
        'sPageSubtitle' => gettext('Manage church meetings and attendance'),
        'aBreadcrumbs' => PageHeader::breadcrumbs([
            [gettext('Meetings')],
        ]),
        'upcomingMeetings' => $upcoming,
        'pastMeetings' => array_slice($past, 0, 10),
        'totalCount' => count($meetings),
        'canEdit' => $canEdit,
    ]);
});
