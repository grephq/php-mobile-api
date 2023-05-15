<?php

namespace Project\Library;

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Code.php';

/**
 * Registers and verifies and requests URI and HTTP method
 */
class Router
{
    /** @var array $methods Valid HTTP methods */
    private $methods = array('GET', 'POST', 'DELETE', 'PUT');

    /** @var array $uriList Valid URI(s) */
    private $uriList = [
        'GET' => [],
        'POST' => [],
        'DELETE' => [],
        'PUT' => [],
    ];

    /** @var array $uriCallback Callbacks for URI(s) */
    private $uriCallback = [
        'GET' => [],
        'POST' => [],
        'DELETE' => [],
        'PUT' => [],
    ];

    /**
     * Registers URI(s) and their callbacks
     */
    public function __call($name, $arguments): void
    {
        $this->uriList[strtoupper($name)][] = $arguments[0];
        $this->uriCallback[strtoupper($name)][$arguments[0]] = $arguments[1];
    }

    /**
     * Parse route and trigger callback
     */
    public function submit(): void
    {
        // Verify request is coming from mobile app
        if (Auth::isUserAgentValid()) {
            // Verify HTTP request method is allowed
            if (!$this->isHTTPMethodValid($_SERVER['REQUEST_METHOD'])) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(405);
                echo json_encode(array(
                    "code" => Code::BAD_REQUEST,
                    "data" => [
                        "message" => Code::getMessage(Code::BAD_REQUEST)
                    ]
                ));
                return;
            }

            // Extract request URI from HTTP request
            $requestURI = explode('?', $_SERVER['REQUEST_URI'])[0];
            $foundURIMatch = false;
            
            // Checks if request URI exists
            foreach ($this->uriList[$_SERVER['REQUEST_METHOD']] as $uri) {
                if ($uri === $requestURI) {
                    $foundURIMatch = true;
                    break;
                }
            }

            if ($foundURIMatch) {
                call_user_func($this->uriCallback[strtoupper($_SERVER['REQUEST_METHOD'])][$requestURI]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(404);
                echo json_encode(array(
                    "code" => Code::NOT_FOUND,
                    "data" => [
                        "message" => Code::getMessage(Code::NOT_FOUND)
                    ]
                ));
            }
        } else {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(array(
                "code" => Code::UNAUTHORIZED,
                "data" => [
                    "message" => Code::getMessage(Code::UNAUTHORIZED)
                ]
            ));
        }
        
    }

    /**
     * Validates if HTTP method is acceptable
     * 
     * @param string $method
     */
    private function isHTTPMethodValid($method): bool
    {
        return in_array($method, $this->methods, true);
    }
}