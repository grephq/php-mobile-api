<?php

require_once __DIR__ . '/Library/Router.php';
require_once __DIR__ . '/Library/Env.php';

use Project\Library\Router;
use Project\Library\Env;

foreach (glob(__DIR__ . '/Controller/*/*/*.php') as $file) {
    if ($file === __DIR__ . '/Controller/BaseController.php') continue;
    require_once $file;
}

// Load .env variables into environment variables
Env::load(__DIR__ . '/.env');

/** @var object $route Router class instance */
$route = new Router();