<?php
// Configuration des en-têtes CORS pour la flexibilité de l'environnement de développement
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Gestion des requêtes de pré-vérification OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/JWT/JWT.php';
require_once __DIR__ . '/JWT/JWTException.php';

// 1. Récupération du chemin et de la méthode de la requête
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Suppression du dossier de base pour les déploiements dans des sous-dossiers
$baseDir = dirname($scriptName);
$baseDir = str_replace('\\', '/', $baseDir);
if (preg_match('/\.php$/', $scriptName) && $baseDir !== '/' && strpos($requestUri, $baseDir) === 0) {
    $path = substr($requestUri, strlen($baseDir));
} else {
    $path = $requestUri;
}

// Séparation du chemin et des paramètres de requête
$path = explode('?', $path)[0];
$path = '/' . trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

// 2. Chargement des routes
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

// 3. Sécurité (Authentification JWT)
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
        $_REQUEST['user'] = $payload->toArray();
    } catch (Exception $e) {
        http_response_code(401);
        exit;
    }
}

// 4. Instanciation du Contrôleur
$controllerName = ucfirst($matchedRoute['controller']) . 'Controller';
$controllerFile = __DIR__ . '/controller/' . $matchedRoute['controller'] . '.controller.php';

require_once $controllerFile;

$controllerInstance = new $controllerName();
$actionName = $matchedRoute['action'];

// 5. Appel de l'action avec arguments
call_user_func_array([$controllerInstance, $actionName], array_values($routeParams));
