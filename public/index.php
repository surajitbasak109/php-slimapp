<?php

require '../vendor/autoload.php';

$app = new \Slim\App;

require '../src/classes/db.php';
require '../src/routes/customers.php';

$app->run();
