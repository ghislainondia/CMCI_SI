<?php

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\dto\SystemConfig;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Service\MeetingService;
use ChurchCRM\Slim\SlimUtils;
use ChurchCRM\Utils\DateTimeUtils;
use ChurchCRM\Utils\InputUtils;
use ChurchCRM\view\PageHeader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

$app->get('/view/{meetingId:[0-9]+}', function (Request $request, Response $response, array $args): Response {
    $meetingId = (int) $args['meetingId'];
    $meetingService = new MeetingService();
    $meeting = $meetingService->getMeetingById($meetingId);

    if ($meeting === null) {
        return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/meetings/list');
    }

    $present = [];
    $absent = [];
    foreach ($meeting['attendance'] as $row) {
        if ($row['isPresent']) {
            $present[] = $row;
        } else {
            $absent[] = $row;
        }
    }

    $renderer = new PhpRenderer(__DIR__ . '/../views/');

    return $renderer->render($response, 'view.php', [
        'sRootPath' => SystemURLs::getRootPath(),
        'sPageTitle' => InputUtils::escapeHTML($meeting['name']),
        'sPageSubtitle' => DateTimeUtils::formatDate($meeting['meetingDateTime'], true),
        'aBreadcrumbs' => PageHeader::breadcrumbs([
            [gettext('Meetings'), '/meetings/dashboard'],
            [gettext('Meeting')],
        ]),
        'meeting' => $meeting,
        'presentAttendees' => $present,
        'absentAttendees' => $absent,
        'canEdit' => AuthenticationManager::getCurrentUser()->isEditRecordsEnabled(),
    ]);
});
