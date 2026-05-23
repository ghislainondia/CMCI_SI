<?php

use ChurchCRM\dto\ChurchVocabulary;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Service\BertouaAccessService;
use ChurchCRM\Service\BertouaCatalogService;
use ChurchCRM\Slim\SlimUtils;
use ChurchCRM\view\PageHeader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

$app->get('/', function (Request $request, Response $response): Response {
    return SlimUtils::renderRedirect($response, SystemURLs::getRootPath() . '/bertoua/notes');
});

$app->get('/notes', function (Request $request, Response $response): Response {
    $access = new BertouaAccessService();
    if (!$access->canAccessBertouaModule()) {
        return SlimUtils::renderRedirect(
            $response,
            SystemURLs::getRootPath() . '/v2/access-denied?role=EditRecords'
        );
    }

    $catalog = new BertouaCatalogService();
    $assemblies = $access->getAccessibleAssemblies();
    $modules = $catalog->listModules();

    $query = $request->getQueryParams();
    $selectedGroupId = (int) ($query['groupId'] ?? 0);
    $selectedModuleId = (int) ($query['moduleId'] ?? 0);
    $selectedLessonId = (int) ($query['lessonId'] ?? 0);

    if ($selectedGroupId > 0 && !$access->canAccessAssemblyGroup($selectedGroupId)) {
        $selectedGroupId = 0;
        $selectedLessonId = 0;
    }

    if (count($assemblies) === 1 && $selectedGroupId <= 0) {
        $selectedGroupId = (int) $assemblies[0]['id'];
    }

    $renderer = new PhpRenderer(__DIR__ . '/../views/');
    $pageArgs = [
        'sRootPath' => SystemURLs::getRootPath(),
        'sPageTitle' => gettext('Bertoua Message'),
        'sPageSubtitle' => gettext('Record lesson notes for house assembly members'),
        'aBreadcrumbs' => PageHeader::breadcrumbs([
            [gettext('Bertoua Message'), '/bertoua/notes'],
            [gettext('Notes')],
        ]),
        'assemblies' => $assemblies,
        'modules' => $modules,
        'selectedGroupId' => $selectedGroupId,
        'selectedModuleId' => $selectedModuleId,
        'selectedLessonId' => $selectedLessonId,
        'houseAssemblyLabel' => ChurchVocabulary::houseAssembly(),
        'isAdmin' => $access->isAdmin(),
    ];

    return $renderer->render($response, 'notes.php', $pageArgs);
});
