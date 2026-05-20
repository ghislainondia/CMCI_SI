<?php

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Service\MeetingService;
use ChurchCRM\Utils\DateTimeUtils;
use ChurchCRM\view\PageHeader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

$app->get('/list', function (Request $request, Response $response): Response {
    $meetingService = new MeetingService();
    $meetings = $meetingService->getAllMeetings();
    foreach ($meetings as &$meeting) {
        $meeting['formattedDateTime'] = DateTimeUtils::formatDate($meeting['meetingDateTime'], true);
        $full = $meetingService->getMeetingById($meeting['id']);
        $present = 0;
        $absent = 0;
        if ($full !== null) {
            foreach ($full['attendance'] as $row) {
                if ($row['isPresent']) {
                    $present++;
                } else {
                    $absent++;
                }
            }
        }
        $meeting['presentCount'] = $present;
        $meeting['absentCount'] = $absent;
    }
    unset($meeting);

    $renderer = new PhpRenderer(__DIR__ . '/../views/');

    return $renderer->render($response, 'list.php', [
        'sRootPath' => SystemURLs::getRootPath(),
        'sPageTitle' => gettext('Meeting List'),
        'sPageSubtitle' => gettext('All scheduled and past meetings'),
        'aBreadcrumbs' => PageHeader::breadcrumbs([
            [gettext('Meetings'), '/meetings/dashboard'],
            [gettext('Meeting List')],
        ]),
        'meetings' => $meetings,
        'canEdit' => AuthenticationManager::getCurrentUser()->isEditRecordsEnabled(),
    ]);
});
