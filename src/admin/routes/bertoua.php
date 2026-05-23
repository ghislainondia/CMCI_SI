<?php

use ChurchCRM\dto\SystemURLs;
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
    if (!empty($query['saved'])) {
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
