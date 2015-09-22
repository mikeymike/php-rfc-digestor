<?php

$app = require_once __DIR__ . '/app.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;


// replace with mechanism to retrieve EntityManager in your app
$entityManager = $app['orm.em'];

return ConsoleRunner::createHelperSet($entityManager);
