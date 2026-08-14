<?php

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\dto\ChurchVocabulary;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Service\MeetingService;
use ChurchCRM\Utils\DateTimeUtils;
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
    $now = DateTimeUtils::getToday();

    foreach ($meetings as $meeting) {
        $meetingDateTime = DateTimeUtils::createDateTime((string) $meeting['meetingDateTime']);
        $meeting['dayNumber'] = $meetingDateTime->format('d');
        $meeting['monthNumber'] = $meetingDateTime->format('m');
        $meeting['timeDisplay'] = $meetingDateTime->format('H:i');
        $meeting['formattedDateTime'] = DateTimeUtils::formatDate($meetingDateTime, true);

        if ($meetingDateTime >= $now) {
            $upcoming[] = $meeting;
        } else {
            $past[] = $meeting;
        }
    }

    usort($upcoming, static fn (array $a, array $b): int => strcmp($a['meetingDateTime'], $b['meetingDateTime']));

    $lastAttendance = ['present' => 0, 'absent' => 0];
    if ($past !== []) {
        $lastMeeting = $meetingService->getMeetingById((int) $past[0]['id']);
        if ($lastMeeting !== null) {
            foreach ($lastMeeting['attendance'] as $attendance) {
                if ($attendance['isPresent']) {
                    $lastAttendance['present']++;
                } else {
                    $lastAttendance['absent']++;
                }
            }
        }
    }

    $canEdit = AuthenticationManager::getCurrentUser()->isEditRecordsEnabled();

    $renderer = new PhpRenderer(__DIR__ . '/../views/');

    return $renderer->render($response, 'dashboard.php', [
        'sRootPath' => SystemURLs::getRootPath(),
        'sPageTitle' => ChurchVocabulary::meetings(),
        'sPageSubtitle' => gettext('Manage church meetings and attendance'),
        'aBreadcrumbs' => PageHeader::breadcrumbs([
            [ChurchVocabulary::meetings()],
        ]),
        'upcomingMeetings' => $upcoming,
        'pastMeetings' => array_slice($past, 0, 10),
        'totalCount' => count($meetings),
        'upcomingCount' => count($upcoming),
        'pastCount' => count($past),
        'nextMeeting' => $upcoming[0] ?? null,
        'lastAttendance' => $lastAttendance,
        'canEdit' => $canEdit,
    ]);
});
