<?php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load(); // ✅ This makes $_ENV variables available

require __DIR__ . '/../src/db.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Include routes
require __DIR__ . '/../src/routes/lecturer.php';
require __DIR__ . '/../src/routes/student.php';
require __DIR__ . '/../src/routes/advisor.php';
require __DIR__ . '/../src/routes/admin.php';
$app->run();
