<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Service\BertouaCatalogImportService;
use ChurchCRM\Service\BertouaCatalogService;
use ChurchCRM\Slim\SlimUtils;
use ChurchCRM\Utils\InputUtils;
use ChurchCRM\view\PageHeader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\PhpRenderer;

$app->group('/system/bertoua', function (RouteCollectorProxy $group): void {
    $group->get('', 'renderBertouaAdmin');
    $group->get('/', 'renderBertouaAdmin');

    $group->post('/module', 'createBertouaModule');
    $group->post('/module/{id:[0-9]+}/update', 'updateBertouaModule');
    $group->post('/module/{id:[0-9]+}/delete', 'deleteBertouaModule');
    $group->post('/modules/reorder', 'reorderBertouaModules');

    $group->post('/module/{moduleId:[0-9]+}/lesson', 'createBertouaLesson');
    $group->post('/lesson/{id:[0-9]+}/update', 'updateBertouaLesson');
    $group->post('/lesson/{id:[0-9]+}/delete', 'deleteBertouaLesson');
    $group->post('/module/{moduleId:[0-9]+}/lessons/reorder', 'reorderBertouaLessons');

    $group->get('/import-template.csv', 'downloadBertouaImportTemplate');
    $group->post('/import-csv', 'importBertouaCatalogCsv');
});

function renderBertouaAdmin(Request $request, Response $response, array $args): Response
{
    $catalog = new BertouaCatalogService();
    $modules = $catalog->listModules();
    $lessonsByModule = [];
    foreach ($modules as $module) {
        $lessonsByModule[$module['id']] = $catalog->listLessons((int) $module['id']);
    }

    $query = $request->getQueryParams();
    $message = '';
    $messageClass = 'success';
    if (!empty($query['imported'])) {
        $message = sprintf(
            gettext('Import complete: %1$d module(s) created, %2$d lesson(s) created (%3$d lesson(s) skipped as duplicates).'),
            (int) ($query['modulesCreated'] ?? 0),
            (int) ($query['lessonsCreated'] ?? 0),
            (int) ($query['lessonsSkipped'] ?? 0)
        );
        if (!empty($query['importErrors'])) {
            $message .= ' ' . InputUtils::escapeHTML((string) $query['importErrors']);
            $messageClass = 'warning';
        }
    } elseif (!empty($query['saved'])) {
        $message = gettext('Changes saved successfully.');
    } elseif (!empty($query['error'])) {
        $message = InputUtils::escapeHTML((string) $query['error']);
        $messageClass = 'danger';
    }

    $renderer = new PhpRenderer(__DIR__ . '/../views/');
    $pageArgs = [
        'sRootPath' => SystemURLs::getRootPath(),
        'sPageTitle' => gettext('Bertoua Message Administration'),
        'sPageSubtitle' => gettext('Manage modules and lessons for the Bertoua Message'),
        'aBreadcrumbs' => PageHeader::breadcrumbs([
            [gettext('Admin'), '/admin/'],
            [gettext('Bertoua Message')],
        ]),
        'modules' => $modules,
        'lessonsByModule' => $lessonsByModule,
        'sGlobalMessage' => $message,
        'sGlobalMessageClass' => $messageClass,
    ];

    return $renderer->render($response, 'bertoua-admin.php', $pageArgs);
}

function createBertouaModule(Request $request, Response $response, array $args): Response
{
    $body = $request->getParsedBody();
    $title = trim((string) ($body['title'] ?? ''));
    if ($title === '') {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/admin/system/bertoua?error=' . urlencode(gettext('Module title is required.'))
        );
    }

    $catalog = new BertouaCatalogService();
    $catalog->createModule($title);

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/admin/system/bertoua?saved=1');
}

function updateBertouaModule(Request $request, Response $response, array $args): Response
{
    $moduleId = (int) $args['id'];
    $body = $request->getParsedBody();
    $title = trim((string) ($body['title'] ?? ''));
    $sortOrder = isset($body['sortOrder']) ? (int) $body['sortOrder'] : null;

    if ($title === '') {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/admin/system/bertoua?error=' . urlencode(gettext('Module title is required.'))
        );
    }

    $catalog = new BertouaCatalogService();
    $catalog->updateModule($moduleId, $title, $sortOrder);

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/admin/system/bertoua?saved=1');
}

function deleteBertouaModule(Request $request, Response $response, array $args): Response
{
    $catalog = new BertouaCatalogService();
    $catalog->deleteModule((int) $args['id']);

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/admin/system/bertoua?saved=1');
}

function reorderBertouaModules(Request $request, Response $response, array $args): Response
{
    $body = $request->getParsedBody();
    $order = $body['order'] ?? [];
    if (is_string($order)) {
        $order = array_filter(array_map('intval', explode(',', $order)));
    }
    if (!is_array($order)) {
        $order = [];
    }

    $catalog = new BertouaCatalogService();
    $catalog->reorderModules(array_values(array_map('intval', $order)));

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/admin/system/bertoua?saved=1');
}

function createBertouaLesson(Request $request, Response $response, array $args): Response
{
    $moduleId = (int) $args['moduleId'];
    $body = $request->getParsedBody();
    $title = trim((string) ($body['title'] ?? ''));
    if ($title === '') {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/admin/system/bertoua?error=' . urlencode(gettext('Lesson title is required.'))
        );
    }

    $catalog = new BertouaCatalogService();
    if ($catalog->getModuleById($moduleId) === null) {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/admin/system/bertoua?error=' . urlencode(gettext('Module not found.'))
        );
    }

    $catalog->createLesson($moduleId, $title);

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/admin/system/bertoua?saved=1');
}

function updateBertouaLesson(Request $request, Response $response, array $args): Response
{
    $lessonId = (int) $args['id'];
    $body = $request->getParsedBody();
    $title = trim((string) ($body['title'] ?? ''));
    $sortOrder = isset($body['sortOrder']) ? (int) $body['sortOrder'] : null;

    if ($title === '') {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/admin/system/bertoua?error=' . urlencode(gettext('Lesson title is required.'))
        );
    }

    $catalog = new BertouaCatalogService();
    $catalog->updateLesson($lessonId, $title, $sortOrder);

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/admin/system/bertoua?saved=1');
}

function deleteBertouaLesson(Request $request, Response $response, array $args): Response
{
    $catalog = new BertouaCatalogService();
    $catalog->deleteLesson((int) $args['id']);

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/admin/system/bertoua?saved=1');
}

function reorderBertouaLessons(Request $request, Response $response, array $args): Response
{
    $moduleId = (int) $args['moduleId'];
    $body = $request->getParsedBody();
    $order = $body['order'] ?? [];
    if (is_string($order)) {
        $order = array_filter(array_map('intval', explode(',', $order)));
    }
    if (!is_array($order)) {
        $order = [];
    }

    $catalog = new BertouaCatalogService();
    $catalog->reorderLessons($moduleId, array_values(array_map('intval', $order)));

    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/admin/system/bertoua?saved=1');
}

function downloadBertouaImportTemplate(Request $request, Response $response, array $args): Response
{
    $content = BertouaCatalogImportService::getTemplateCsvContent();

    $response->getBody()->write($content);

    return $response
        ->withHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->withHeader('Content-Disposition', 'attachment; filename="bertoua-modules-lecons.csv"')
        ->withHeader('Content-Length', (string) strlen($content));
}

function importBertouaCatalogCsv(Request $request, Response $response, array $args): Response
{
    $uploadedFiles = $request->getUploadedFiles();
    if (!isset($uploadedFiles['csvFile'])) {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/admin/system/bertoua?error=' . urlencode(gettext('Please choose a CSV file to import.'))
        );
    }

    $upload = $uploadedFiles['csvFile'];
    if ($upload->getError() !== UPLOAD_ERR_OK) {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/admin/system/bertoua?error=' . urlencode(gettext('File upload failed.'))
        );
    }

    $clientName = $upload->getClientFilename() ?? 'import.csv';
    if (!preg_match('/\.csv$/i', $clientName)) {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/admin/system/bertoua?error=' . urlencode(gettext('Only .csv files are accepted.'))
        );
    }

    $tempPath = sys_get_temp_dir() . '/bertoua-import-' . uniqid('', true) . '.csv';
    $upload->moveTo($tempPath);

    $body = $request->getParsedBody();
    $replaceExisting = !empty($body['replaceExisting']);

    $importService = new BertouaCatalogImportService();
    $stats = $importService->importFromCsvFile($tempPath, $replaceExisting);
    @unlink($tempPath);

    if ($stats['rowsProcessed'] === 0 && $stats['errors'] !== []) {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/admin/system/bertoua?error=' . urlencode(implode(' ', $stats['errors']))
        );
    }

    $query = http_build_query([
        'imported' => 1,
        'modulesCreated' => $stats['modulesCreated'],
        'lessonsCreated' => $stats['lessonsCreated'],
        'lessonsSkipped' => $stats['lessonsSkipped'],
        'importErrors' => $stats['errors'] !== [] ? implode(' | ', $stats['errors']) : '',
    ]);

    return SlimUtils::renderRedirect(
        $response,
        SystemURLs::getRootPath() . '/admin/system/bertoua?' . $query
    );
}
