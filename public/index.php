<?php

require_once '../app/core/Router.php';
require_once '../app/core/controller.php';
require_once '../app/core/database.php';

// Enable Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$app = new Router();
