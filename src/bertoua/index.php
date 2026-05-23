<?php

require_once __DIR__ . '/../Include/LoadConfigs.php';

use ChurchCRM\Slim\MvcAppFactory;

$app = MvcAppFactory::create('/bertoua', [
    'dashboardUrl' => '/bertoua/notes',
    'dashboardText' => gettext('Back to Bertoua Message'),
]);

require __DIR__ . '/routes/notes.php';
require __DIR__ . '/routes/api.php';

$app->run();
