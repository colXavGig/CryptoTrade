<?php
// Detect if it's an API request
if (isset($_GET['route']) && strpos($_GET['route'], 'api/') === 0) {

    $routes = require_once 'route.php';
    $route = $_GET['route'];

    if (!isset($routes[$route])) {

        echo json_encode(['error' => 'Invalid API endpoint'], JSON_PRETTY_PRINT);
        exit;
    }


    // Extract the target file and method for my @ notation in route.php
    $target = $routes[$route];

    if (strpos($target, '@') !== false) {
        list($file, $method) = explode('@', $target);
    
        if (file_exists($file)) {
            require_once $file;
    
            // Dynamically determine the class name based on the file
            $className = basename($file, '.php'); // Extracts 'transaction_controller' or 'user_controller'
            $className = str_replace('_controller', 'Controller', $className); // Converts it to 'TransactionController' or 'UserController'
    
            if (class_exists($className)) {
                $controller = new $className(); // Instantiate the correct controller
    
                if (method_exists($controller, $method)) {
                    $controller->$method(); // Call the correct method
                    exit;
                } else {
                    echo json_encode(['error' => "Method '$method' not found in '$className'"], JSON_PRETTY_PRINT);
                    exit;
                }
            } else {
                echo json_encode(['error' => "Controller class '$className' not found"], JSON_PRETTY_PRINT);
                exit;
            }
        } else {
            echo json_encode(['error' => "Controller file '$file' not found"], JSON_PRETTY_PRINT);
            exit;
        }
    }
    

    echo json_encode(['error' => 'Invalid API format'], JSON_PRETTY_PRINT);
    exit;
}

// Load frontend routes
$routes = require_once 'route.php';

// Sanitize route input
$route = isset($_GET['route']) ? preg_replace('/[^a-zA-Z0-9\/_-]/', '', $_GET['route']) : 'home';

if (!isset($routes[$route])) {
    $route = '404';
}

$page = $routes[$route];

?>