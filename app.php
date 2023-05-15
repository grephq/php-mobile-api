<?php

require_once __DIR__ . '/routes.php';

/**
 * Define routes here
 */

$route->post('/v1/login', function() {
    Login::get();
});


$route->submit();