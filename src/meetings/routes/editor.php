<?php

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\dto\ChurchVocabulary;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Service\MeetingService;
use ChurchCRM\Slim\SlimUtils;
use ChurchCRM\Utils\DateTimeUtils;
use ChurchCRM\Utils\InputUtils;
use ChurchCRM\view\PageHeader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

$app->get('/members', function (Request $request, Response $response): Response {
    $organizer = (string) ($request->getQueryParams()['organizer'] ?? '');
    $meetingService = new MeetingService();
    $parsed = $meetingService->parseOrganizerValue($organizer);

    if ($parsed === null) {
        return SlimUtils::renderJSON($response, ['members' => []]);
    }

    $members = $meetingService->getSuggestedMembers($parsed['type'], $parsed['id']);

    return SlimUtils::renderJSON($response, ['members' => $members]);
});

$requireEdit = static function (): void {
    AuthenticationManager::redirectHomeIfFalse(
        AuthenticationManager::getCurrentUser()->isEditRecordsEnabled(),
        'EditRecords'
    );
};

$app->get('/editor[/{meetingId:[0-9]+}]', function (Request $request, Response $response, array $args) use ($requireEdit): Response {
    $requireEdit();

    $meetingId = (int) ($args['meetingId'] ?? 0);
    $meetingService = new MeetingService();
    $meeting = null;
    $attendanceRows = [];

    if ($meetingId > 0) {
        $meeting = $meetingService->getMeetingById($meetingId);
        if ($meeting === null) {
            return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/meetings/list');
        }
        $attendanceRows = $meeting['attendance'];
    }

    $organizerOptions = $meetingService->getOrganizerOptions();

    $suggestedMembers = [];
    if ($meeting !== null) {
        $suggestedMembers = $meetingService->getSuggestedMembers(
            $meeting['organizerType'],
            $meeting['organizerId']
        );
    }

    $renderer = new PhpRenderer(__DIR__ . '/../views/');

    return $renderer->render($response, 'editor.php', [
        'sRootPath' => SystemURLs::getRootPath(),
        'sPageTitle' => $meeting ? gettext('Edit Meeting') : gettext('New Meeting'),
        'sPageSubtitle' => $meeting ? InputUtils::escapeHTML($meeting['name']) : gettext('Schedule a meeting and record attendance'),
        'aBreadcrumbs' => PageHeader::breadcrumbs([
            [ChurchVocabulary::meetings(), '/meetings/dashboard'],
            [$meeting ? gettext('Edit Meeting') : gettext('New Meeting')],
        ]),
        'meeting' => $meeting,
        'meetingId' => $meetingId,
        'organizerOptions' => $organizerOptions,
        'attendanceRows' => $attendanceRows,
        'suggestedMembers' => $suggestedMembers,
        'errors' => [],
    ]);
});

$app->post('/editor[/{meetingId:[0-9]+}]', function (Request $request, Response $response, array $args) use ($requireEdit): Response {
    $requireEdit();

    $meetingId = (int) ($args['meetingId'] ?? 0);
    $body = $request->getParsedBody();
    if (!is_array($body)) {
        $body = [];
    }

    $meetingService = new MeetingService();
    $errors = [];

    $name = trim((string) ($body['Name'] ?? ''));
    if ($name === '') {
        $errors[] = gettext('Meeting name is required.');
    }

    $meetingDateTimeRaw = trim((string) ($body['MeetingDateTime'] ?? ''));
    $meetingTimestamp = strtotime($meetingDateTimeRaw);
    $meetingDateTime = null;
    if ($meetingTimestamp === false || $meetingTimestamp <= 0) {
        $errors[] = gettext('A valid date and time is required.');
    } else {
        $meetingDateTime = date('Y-m-d H:i:s', $meetingTimestamp);
    }

    $organizerParsed = $meetingService->parseOrganizerValue((string) ($body['Organizer'] ?? ''));
    if ($organizerParsed === null) {
        $errors[] = gettext('Please select an organizer.');
    }

    $remarks = InputUtils::sanitizeText((string) ($body['Remarks'] ?? ''));

    $attendanceInput = [];
    if (isset($body['attendance']) && is_array($body['attendance'])) {
        foreach ($body['attendance'] as $personId => $status) {
            $attendanceInput[] = [
                'personId' => (int) $personId,
                'isPresent' => $status === 'present',
            ];
        }
    }

    $addPersonIds = $body['addPersonIds'] ?? [];
    if (is_array($addPersonIds)) {
        foreach ($addPersonIds as $personId) {
            $personId = (int) $personId;
            if ($personId > 0) {
                $attendanceInput[] = [
                    'personId' => $personId,
                    'isPresent' => true,
                ];
            }
        }
    }

    $attendance = $meetingService->normalizeAttendanceInput($attendanceInput);

    if (!empty($errors)) {
        $organizerOptions = $meetingService->getOrganizerOptions();
        $renderer = new PhpRenderer(__DIR__ . '/../views/');

        return $renderer->render($response, 'editor.php', [
            'sRootPath' => SystemURLs::getRootPath(),
            'sPageTitle' => $meetingId > 0 ? gettext('Edit Meeting') : gettext('New Meeting'),
            'sPageSubtitle' => gettext('Please correct the errors below'),
            'aBreadcrumbs' => PageHeader::breadcrumbs([
                [ChurchVocabulary::meetings(), '/meetings/dashboard'],
                [$meetingId > 0 ? gettext('Edit Meeting') : gettext('New Meeting')],
            ]),
            'meeting' => [
                'id' => $meetingId,
                'name' => $name,
                'meetingDateTime' => $meetingDateTimeRaw,
                'organizerValue' => (string) ($body['Organizer'] ?? ''),
                'remarks' => $remarks,
            ],
            'meetingId' => $meetingId,
            'organizerOptions' => $organizerOptions,
            'attendanceRows' => array_values(array_filter(array_map(static function (array $row): ?array {
                $person = PersonQuery::create()->findPk((int) $row['personId']);
                if ($person === null) {
                    return null;
                }
                return [
                    'personId' => (int) $row['personId'],
                    'isPresent' => $row['isPresent'],
                    'fullName' => $person->getFullName(),
                ];
            }, $attendance))),
            'suggestedMembers' => [],
            'errors' => $errors,
        ]);
    }

    $data = [
        'name' => $name,
        'meetingDateTime' => $meetingDateTime,
        'organizerType' => $organizerParsed['type'],
        'organizerId' => $organizerParsed['id'],
        'remarks' => $remarks,
    ];

    if ($meetingId > 0) {
        $meetingService->updateMeeting($meetingId, $data, $attendance);
    } else {
        $meetingId = $meetingService->createMeeting($data, $attendance);
    }

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/meetings/view/' . $meetingId);
});

$app->post('/delete/{meetingId:[0-9]+}', function (Request $request, Response $response, array $args) use ($requireEdit): Response {
    $requireEdit();

    $meetingId = (int) $args['meetingId'];
    $meetingService = new MeetingService();
    $meetingService->deleteMeeting($meetingId);

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/meetings/list');
});
