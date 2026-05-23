<?php

use ChurchCRM\Service\BertouaAccessService;
use ChurchCRM\Service\BertouaCatalogService;
use ChurchCRM\Service\BertouaNoteService;
use ChurchCRM\Slim\SlimUtils;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$requireBertouaAccess = static function (): void {
    $access = new BertouaAccessService();
    if (!$access->canAccessBertouaModule()) {
        throw new \Slim\Exception\HttpForbiddenException(
            null,
            gettext('You do not have access to the Bertoua Message module.')
        );
    }
};

$app->get('/api/lessons', function (Request $request, Response $response) use ($requireBertouaAccess): Response {
    $requireBertouaAccess();

    $moduleId = (int) ($request->getQueryParams()['moduleId'] ?? 0);
    if ($moduleId <= 0) {
        return SlimUtils::renderJSON($response, ['lessons' => []]);
    }

    $catalog = new BertouaCatalogService();
    if ($catalog->getModuleById($moduleId) === null) {
        return SlimUtils::renderJSON($response, ['lessons' => []]);
    }

    return SlimUtils::renderJSON($response, [
        'lessons' => $catalog->listLessons($moduleId),
    ]);
});

$app->get('/api/members', function (Request $request, Response $response) use ($requireBertouaAccess): Response {
    $requireBertouaAccess();

    $groupId = (int) ($request->getQueryParams()['groupId'] ?? 0);
    $access = new BertouaAccessService();

    try {
        $members = $access->getAssemblyMembers($groupId);
    } catch (\Slim\Exception\HttpForbiddenException $e) {
        return SlimUtils::renderErrorJSON($response, $e->getMessage(), [], 403);
    }

    return SlimUtils::renderJSON($response, ['members' => $members]);
});

$app->get('/api/notes', function (Request $request, Response $response) use ($requireBertouaAccess): Response {
    $requireBertouaAccess();

    $groupId = (int) ($request->getQueryParams()['groupId'] ?? 0);
    $lessonId = (int) ($request->getQueryParams()['lessonId'] ?? 0);
    $noteService = new BertouaNoteService();

    try {
        $notes = $noteService->getNotesForLesson($lessonId, $groupId);
    } catch (\Slim\Exception\HttpForbiddenException $e) {
        return SlimUtils::renderErrorJSON($response, $e->getMessage(), [], 403);
    }

    return SlimUtils::renderJSON($response, ['notes' => $notes]);
});

$app->post('/api/notes', function (Request $request, Response $response) use ($requireBertouaAccess): Response {
    $requireBertouaAccess();

    $body = $request->getParsedBody();
    if (!is_array($body)) {
        $body = json_decode((string) $request->getBody(), true) ?? [];
    }

    $groupId = (int) ($body['groupId'] ?? 0);
    $lessonId = (int) ($body['lessonId'] ?? 0);
    $notes = $body['notes'] ?? [];
    if (!is_array($notes)) {
        return SlimUtils::renderErrorJSON($response, gettext('Invalid notes payload.'), [], 400);
    }

    $noteService = new BertouaNoteService();

    try {
        $saved = $noteService->saveNotes($lessonId, $groupId, $notes);
    } catch (\Slim\Exception\HttpForbiddenException $e) {
        return SlimUtils::renderErrorJSON($response, $e->getMessage(), [], 403);
    }

    return SlimUtils::renderJSON($response, [
        'success' => true,
        'saved' => $saved,
        'message' => gettext('Notes saved successfully.'),
    ]);
});
