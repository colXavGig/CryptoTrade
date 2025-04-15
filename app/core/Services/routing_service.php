<?php


$routes = require_once 'route.php';

// Check if it's an API route
if (isset($_GET['route']) && strpos($_GET['route'], 'api/') === 0) {
    $route = $_GET['route'];

    if (!isset($routes[$route])) {
        echo json_encode(['error' => 'Invalid API endpoint'], JSON_PRETTY_PRINT);
        exit;
    }

    $target = $routes[$route];

    if (strpos($target, '@') !== false) {
        list($className, $method) = explode('@', $target);

        if (class_exists($className)) {
            $controller = new $className();

            if (method_exists($controller, $method)) {
                $controller->$method();
                exit;
            } else {
                echo json_encode(['error' => "Method '$method' not found in '$className'"], JSON_PRETTY_PRINT);
                exit;
            }
        } else {
            echo json_encode(['error' => "Controller class '$className' not found"], JSON_PRETTY_PRINT);
            exit;
        }
    }

    echo json_encode(['error' => 'Invalid API format'], JSON_PRETTY_PRINT);
    exit;
}

// ----------------------------------- FRONTEND ROUTING -----------------------------------

$route = isset($_GET['route']) ? preg_replace('/[^a-zA-Z0-9\/_-]/', '', $_GET['route']) : 'home';

if (!isset($routes[$route])) {
    $route = '404';
}

$page = $routes[$route];
