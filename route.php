<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/JWT/JWT.php';
require_once __DIR__ . '/JWT/JWTException.php';


$requestUri = urldecode($_SERVER['REQUEST_URI'] ?? '/');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';


$baseDir = dirname($scriptName);
$baseDir = str_replace('\\', '/', $baseDir);
if (preg_match('/\.php$/', $scriptName) && $baseDir !== '/' && strpos($requestUri, $baseDir) === 0) {
    $path = substr($requestUri, strlen($baseDir));
} else {
    $path = $requestUri;
}


$path = explode('?', $path)[0];
$path = '/' . trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];


$configFile = __DIR__ . '/route.config.json';
$routes = json_decode(file_get_contents($configFile), true);

$matchedRoute = null;
$routeParams = [];

foreach ($routes as $route) {
    if (strtoupper($route['method']) !== $method) {
        continue;
    }

    $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '(?P<$1>[^/]+)', $route['path']);
    $pattern = '#^' . $pattern . '$#';

    if (preg_match($pattern, $path, $matches)) {
        $matchedRoute = $route;
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $routeParams[$key] = $value;
            }
        }
        break;
    }
}

if (!$matchedRoute) {
    http_response_code(404);
    exit;
}


$isPublicRoute = ($matchedRoute['path'] === '/login' || $matchedRoute['path'] === '/register');
if (!$isPublicRoute) {
    $authHeader = null;
    if (isset($_SERVER['Authorization'])) {
        $authHeader = $_SERVER['Authorization'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        exit;
    }

    $token = $matches[1];
    try {
        $payload = JWT::validate($token);
        $_REQUEST['user'] = $payload->toArray();
    } catch (Exception $e) {
        http_response_code(401);
        exit;
    }
}


$controllerName = ucfirst($matchedRoute['controller']) . 'Controller';
$controllerFile = __DIR__ . '/controller/' . $matchedRoute['controller'] . '.controller.php';

require_once $controllerFile;

$controllerInstance = new $controllerName();
$actionName = $matchedRoute['action'];


call_user_func_array([$controllerInstance, $actionName], array_values($routeParams));
