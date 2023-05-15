<?php

require_once __DIR__ . '/../Library/Auth.php';

use Project\Libray\Auth;

/**
 * Implements common functionalities needed by controller classes
 */
class BaseController
{

    /** @var string $accessToken Set if JWT token is renewed */
    private static $acessToken;

    /**
     * Set JWT regenerated token
     * 
     * @param string $token
     */
    public static function setAccessToken($token): void
    {
        self::$accessToken = $token;
    }

    /**
     * Returns json output
     * 
     * @param array $data
     */
    public function json($code, $data): void
    {
        $payload = array();
        // Add new JWT token to json if token is expired
        if (isset(self::$acsessToken))
            $payload["access_token"] = self::$accessToken;

        $payload["code"] = $code;
        $payload["data"] = $data;

        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
        echo json_encode($payload);
    }
}