<?php

require_once __DIR__ . '/../../BaseController.php';
require_once __DIR__ . '/../../../Library/Auth.php';
require_once __DIR__ . '/../../../Library/Code.php';

use ScanPay\Library\Auth;
use ScanPay\Library\Code;

/**
 * User authentication
 */
class Login extends BaseController
{
    /**
     * Authenticate the user
     */
    public static function get(): void
    {
        try {
            if (Auth::isUserAgentValid() && $this->doesUserExist(Database::getpdo(), $_POST['email'], $_POST['password'])) {
                $accessToken = Auth::generateAccessToken($_POST['username'], $_POST['email']);
                $refreshToken = Auth::generateRefreshToken($_POST['username'], $_POST['email']);
                $this->saveFingerprint(Database::getpdo(), $_POST['email']);
                self::json(Code::SUCCESS, ['access_token' => $accessToken, 'refresh_token' => $refreshToken]);
            } else {
                self::json(Code::UNAUTHORIZED, ['message' => Code::getMessage(Code::UNAUTHORIZED)]);    
            }
        } catch (Exception $e) {
            self::json(Code::UNAUTHORIZED, ['message' => Code::getMessage(Code::UNAUTHORIZED)]);
        }
    }

    /**
     * Checks if the users record exists
     * 
     * @param object $pdo
     * @param string $email
     * @param string $password
     * 
     * @return bool
     */
    private function doesUserExist($pdo, $email, $password): bool
    {
        $statement = $pdo->prepare("SELECT * FROM users WHERE email=?");
        $statement->execute([$email]);
        
        if ($statement->rowCount() > 0) {
            $user = $statement->fetchAll(PDO::FETCH_ASSOC);
            if (password_verify($password, $user[0]['password']))
                return true;
        }

        return false;
    }

    /**
     * Save generated fingerprint in database
     * 
     * @param object $pdo
     * @param string $email
     */
    private function saveFingerprint($pdo, $email): void
    {
        try {
            $fingerprint = Auth::generateFingerprint();
            $statement = $pdo->prepare("UPDATE users SET fingerprint=? WHERE email=?");
            $statement->execute([$fingerprint, $email]);
        } catch (Exception $e) {
            throw $e;
        }
    }
}