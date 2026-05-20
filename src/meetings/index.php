<?php

require_once __DIR__ . '/../Include/LoadConfigs.php';

use ChurchCRM\Slim\MvcAppFactory;

$app = MvcAppFactory::create('/meetings', [
    'dashboardUrl' => '/meetings/dashboard',
    'dashboardText' => gettext('Back to Meetings Dashboard'),
]);

require __DIR__ . '/routes/dashboard.php';
require __DIR__ . '/routes/list.php';
require __DIR__ . '/routes/editor.php';
require __DIR__ . '/routes/view.php';

$app->run();
