<?php

namespace Project\Library;

/**
 * Generate public and private key for JWT encoding & decoding
 */

require_once __DIR__ . '/Env.php';

Env::load(__DIR__ . '/../.env');

$password = $_ENV['PRIVATE_KEY_PWD'];

$config = array(  
    "digest_alg" => "sha512",  
    "private_key_bits" => 2048,  
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);

// Create the keypair  
$key = openssl_pkey_new($config);    

// Generate the public key for the private key
$publicKey = openssl_pkey_get_details($key);

// Save the public key in public.pem file
file_put_contents('keys/public.pem', $publicKey['key']);

// Save the encrypted private key in private.pem file
openssl_pkey_export($key, $privateKey, $password);
file_put_contents('keys/private.pem', $privateKey);