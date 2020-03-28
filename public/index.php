<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH');

error_reporting(E_ALL);
ini_set("display_errors", 1);

require '../vendor/autoload.php';

$app = new \Slim\App;

require '../src/classes/db.php';
require '../src/routes/customers.php';

$app->run();
