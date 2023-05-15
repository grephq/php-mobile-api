<?php

namespace Project\Library;

/**
 * Return codes and their meanings
 */
class Code
{
    const SUCCESS = 0;
    const LOGIN_FAILED = 1;
    const UNAUTHORIZED = 2;
    const BAD_REQUEST = 3;
    const NOT_FOUND = 4;
    const INVALID_PIN = 5;
    const INVALID_AMOUNT_MIN = 6;
    const INVALID_AMOUNT_MAX = 7;
    const FAILED = 8;

    /**
     * Returns meaning of error code
     * 
     * @param int $code
     */
    public static function getMessage($code): string
    {
        switch($code)
        {
            case Code::SUCCESS:
                return "Success";
            case Code::LOGIN_FAILED:
                return "Invalid username or password";
            case Code::UNAUTHORIZED:
                return "Unauthorized";
            case Code::BAD_REQUEST:
                return "Bad request";
            case Code::NOT_FOUND:
                return "Not found";
            case Code::INVALID_PIN:
                return "Invalid pin";
            case Code::INVALID_AMOUNT_MIN:
                return "Amount too small";
            case Code::INVALID_AMOUNT_MAX:
                return "Amount too large";
            case Code::FAILED:
                return "Failed";
            default:
                return null;
        }
    }
}