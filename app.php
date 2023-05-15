<?php

require_once __DIR__ . '/routes.php';

/**
 * Define routes here
 */

$route->post('/v1/login', function() {
    Login::get();
});

$route->post('/v1/register', function() {
    Register::get();
});

$route->get('/v1/transactions', function() {
    Wallet::getTransactions();
});

$route->post('/v1/deposit', function() {
    Wallet::deposit();
});

$route->post('/v1/withdraw', function() {
    Wallet::withdraw();
});

$route->post('/v1/transfer', function() {
    Wallet::transfer();
});

$route->get('/v1/balance', function() {
    Wallet::getBalance();
});

$route->submit();