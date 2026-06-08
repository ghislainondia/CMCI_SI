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

/**
 * Resolve house assembly id from request (family record, not group).
 */
$resolveFamilyId = static function (array $params): int {
    if (!empty($params['familyId'])) {
        return (int) $params['familyId'];
    }

    return (int) ($params['groupId'] ?? 0);
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

$app->get('/api/members', function (Request $request, Response $response) use ($requireBertouaAccess, $resolveFamilyId): Response {
    $requireBertouaAccess();

    $familyId = $resolveFamilyId($request->getQueryParams());
    $access = new BertouaAccessService();

    try {
        $members = $access->getAssemblyMembers($familyId);
    } catch (\Slim\Exception\HttpForbiddenException $e) {
        return SlimUtils::renderErrorJSON($response, $e->getMessage(), [], 403);
    }

    return SlimUtils::renderJSON($response, ['members' => $members]);
});

$app->get('/api/notes', function (Request $request, Response $response) use ($requireBertouaAccess, $resolveFamilyId): Response {
    $requireBertouaAccess();

    $params = $request->getQueryParams();
    $familyId = $resolveFamilyId($params);
    $lessonId = (int) ($params['lessonId'] ?? 0);
    $noteService = new BertouaNoteService();

    try {
        $notes = $noteService->getNotesForLesson($lessonId, $familyId);
    } catch (\Slim\Exception\HttpForbiddenException $e) {
        return SlimUtils::renderErrorJSON($response, $e->getMessage(), [], 403);
    }

    return SlimUtils::renderJSON($response, ['notes' => $notes]);
});

$app->post('/api/notes', function (Request $request, Response $response) use ($requireBertouaAccess, $resolveFamilyId): Response {
    $requireBertouaAccess();

    $body = $request->getParsedBody();
    if (!is_array($body)) {
        $body = json_decode((string) $request->getBody(), true) ?? [];
    }

    $familyId = $resolveFamilyId($body);
    $lessonId = (int) ($body['lessonId'] ?? 0);
    $notes = $body['notes'] ?? [];
    if (!is_array($notes)) {
        return SlimUtils::renderErrorJSON($response, gettext('Invalid notes payload.'), [], 400);
    }

    $noteService = new BertouaNoteService();

    try {
        $saved = $noteService->saveNotes($lessonId, $familyId, $notes);
    } catch (\Slim\Exception\HttpForbiddenException $e) {
        return SlimUtils::renderErrorJSON($response, $e->getMessage(), [], 403);
    }

    return SlimUtils::renderJSON($response, [
        'success' => true,
        'saved' => $saved,
        'message' => gettext('Notes saved successfully.'),
    ]);
});
