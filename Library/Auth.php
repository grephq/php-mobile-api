<?php

namespace Project\Library;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;


require_once __DIR__ . '/../vendor/firebase/php-jwt/src/JWT.php';
require_once __DIR__ . '/../vendor/firebase/php-jwt/src/JWK.php';
require_once __DIR__ . '/../vendor/firebase/php-jwt/src/Key.php';
require_once __DIR__ . '/../vendor/firebase/php-jwt/src/ExpiredException.php';
require_once __DIR__ . '/../vendor/firebase/php-jwt/src/BeforeValidException.php';
require_once __DIR__ . '/../vendor/firebase/php-jwt/src/SignatureInvalidException.php';

require_once __DIR__ . '/../Controller/BaseController.php';

require_once __DIR__ . '/Database.php';

/**
 * Library for authenticating all requests
 */
class Auth
{

    /** @var string $accessTokenPrivateKeyPath Path of access token private key */
    private static $accessTokenPrivateKeyPath = __DIR__ . '/keys/access_token_private.pem';

    /** @var string $accessTokenPublicKeyPath Path of access token public key */
    private static $accessTokenPublicKeyPath = __DIR__ . '/keys/access_token_public.pem';

    /** @var string $refreshTokenPrivateKeyPath Path of refresh token private key */
    private static $refreshTokenPrivateKeyPath = __DIR__ . '/keys/refresh_token_private.pem';

    /** @var string $refreshTokenPublicKeyPath Path of refresh token public key */
    private static $refreshTokenPublicKeyPath = __DIR__ . '/keys/refresh_token_public.pem';

    /**
     * Validates if request is from recognised sender
     */
    public static function isUserAgentValid(): bool
    {
        return $_SERVER['HTTP_USER_AGENT'] === $_ENV['APP_USER_AGENT'];
    }

    /**
     * Validates JWT token and regenerates new token if expired
     */
    public static function isAccessTokenValid(): bool
    {
        // Check if Authorization and Refresh-Token headers are set
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && $this->isRefreshTokenValid())
        {
            list($title, $token) = explode(" ", $_SERVER['HTTP_AUTHORIZATION'], 2);
            // Check if authorization value takes format 'Bearer token'
            if ($title === 'Bearer') {
                // Decode token
                try {
                    $decoded = JWT::decode($token, new Key(Auth::getAccessTokenPublicKey(), 'RS256'));
                    $decoded = (array) $decoded;
                    
                    if ($decoded['iss'] === $_ENV['JWT_ISS'] && $decoded['aud'] === $_ENV['APP_USER_AGENT'] && $decoded['data']['username'] === $_GET['username']) {
                        return true;
                    }
                } catch (ExpiredException $e) {
                    $decoded = JWT::decode($_SERVER['HTTP_REFRESH_TOKEN'], new Key(Auth::getRefreshTokenPublicKey(), 'RS256'));
                    if ($decoded['data']['username'] === $_GET['username']) {
                        // Generate new token
                        BaseController::setAccessToken(self::generateAccessToken($decoded['data']['username'], $decoded['data']['email']));
                        return true;
                    }
                    return false;
                } catch (Exception $e) {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Verifies all credentials supplied by a signed in user
     */
    public static function authenticateUser(): bool
    {
        return Auth::isUserAgentValid() && Auth::isAccessTokenValid() && Auth::isFingerprintValid();
    }

    /**
     * Verifies if refresh token is valid
     */
    private function isRefreshTokenValid(): bool
    {
        if(!isset($_SERVER['HTTP_REFRESH_TOKEN'])) {
            return false;
        }

        if(!isset($_GET['username'])) {
            return false;
        }

        $pdo = Database::getpdo();
        $statement = $pdo->prepare("SELECT * FROM users WHERE username=?");
        $statement->execute(array($_GET['username']));
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data[0]['refresh_token'] === $_SERVER['HTTP_REFRESH_TOKEN'];
    }

    /**
     * Generate JWT access token
     * 
     * @param string $username
     * @param string $email
     */
    public static function generateAccessToken($username, $email): string
    {
        $currentTime = gettimeofday()['sec'];
        $expiration = Auth::getJWTExpirationTime($currentTime); 

        $payload = [
            "iss" => $_ENV['JWT_ISS'], // The issuer of the token
            "aud" => $_ENV['APP_USER_AGENT'], // The audience of the token
            "iat" => $currentTime,
            "exp" => $expiration, // This will define the expiration in NumericDate value. The expiration MUST be after the current date/time.
            "data" => [ // Change it with use case data
                "username" => $username,
                "email" => $email
            ]
        ];

        return JWT::encode($payload, Auth::getAccessTokenPrivateKey(), 'RS256');
    }

    /**
     * Generate JWT refresh token
     * 
     * @param string username
     * @param string email
     */
    public static function generateRefreshToken($username, $email): string
    {
        $payload = [
            "data" => [ 
                "username" => $username,
                "email" => $email
            ]
        ];

        return JWT::encode($payload, Auth::getRefreshTokenPrivateKey(), 'RS256');
    }

    /**
     * Generate fingerprint using device information
     */
    public static function generateFingerprint(): string
    {
        if (!isset($_SERVER['HTTP_MANUFACTURER'])) {
            throw new Exception("Missing device information");
        }

        if (!isset($_SERVER['HTTP_SERIAL_NO'])) {
            throw new Exception("Missing device information");
        }

        if (!isset($_SERVER['HTTP_MEMORY'])) {
            throw new Exception("Missing device information");
        }

        if (!isset($_SERVER['HTTP_STORAGE'])) {
            throw new Exception("Missing device information");
        }

        $payload = $_SERVER['HTTP_MANUFACTURER'] . $_SERVER['HTTP_SERIAL_NO'] . $_SERVER['HTTP_MEMORY'] . $_SERVER['HTTP_STORAGE'];

        return hash("sha256", $payload);
    }

    /**
     * Verifies if generated fingerprint matches users fingerprint
     * 
     * @param string fingerprint
     */
    public static function isFingerprintValid($fingerprint): bool
    {
        if (!isset($_SERVER['HTTP_MANUFACTURER'])) {
            return false;
        }

        if (!isset($_SERVER['HTTP_SERIAL_NO'])) {
            return false;
        }

        if (!isset($_SERVER['HTTP_MEMORY'])) {
            return false;
        }

        if (!isset($_SERVER['HTTP_STORAGE'])) {
            return false;
        }

        if(!isset($_GET['username'])) {
            return false;
        }

        $pdo = Database::getpdo();
        $statement = $pdo->prepare("SELECT * FROM users WHERE username=?");
        $statement->execute(array($_GET['username']));
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $fingerprint === $data[0]['fingerprint'];
    }

    /**
     * Loads access token public key used for decoding
     */
    private static function getAccessTokenPublicKey(): string
    {
        $publicKey = openssl_pkey_get_details(Auth::getAccessTokenPrivateKey())['key'];
        return $publicKey;
    }

    /**
     * Loads access token private key used for JWT encoding
     */
    private static function getAccessTokenPrivateKey()
    {
        $privateKey = openssl_get_privatekey(
            file_get_contents(self::$accessTokenPrivateKeyPath),
            $_ENV['PRIVATE_KEY_PWD']
        );
        return $privateKey;
    }

    /**
     * Loads refresh token public key used for decoding
     */
    private static function getRefreshTokenPublicKey(): string
    {
        $publicKey = openssl_pkey_get_details(Auth::getRefreshTokenPrivateKey())['key'];
        return $publicKey;
    }

    /**
     * Loads refresh token private key used for JWT encoding
     */
    private static function getRefreshTokenPrivateKey()
    {
        $privateKey = openssl_get_privatekey(
            file_get_contents(self::$refreshTokenPrivateKeyPath),
            $_ENV['PRIVATE_KEY_PWD']
        );
        return $privateKey;
    }

    /**
     * Generates JWT expiration time (+1hour)
     * 
     * @param int $currentTime
     */
    private static function getJWTExpirationTime($currentTime): int
    {
        return $currentTime + (60 * 60);
    }
}

// require_once __DIR__ .'/Env.php';
// Env::load(__DIR__.'/../.env');
// $a =Auth::generateAccessToken('tomiwa', 'email@email.com');
// echo $a;