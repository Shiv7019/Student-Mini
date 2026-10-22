
<?php
// api/index.php — front controller / router

require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$route  = $_GET['route'] ?? '';

if (!is_string($route) || $route === '') {
    apiResponse(400, false, 'Invalid API route.');
}

// route name => [file, allowed method]
$routes = [
    'csrf'            => ['csrf.php',            'GET'],
    'dashboard'       => ['dashboard.php',       'GET'],
    'students'        => ['students.php',        'GET'],
    'register'        => ['register.php',        'POST'],
    'login'           => ['login.php',           'POST'],
    'students-create' => ['students-create.php', 'POST'],
    'logout'          => ['logout.php',          'POST'],
];

if (!isset($routes[$route])) {
    apiResponse(404, false, 'API route not found.');
}

[$file, $allowedMethod] = $routes[$route];

if ($method !== $allowedMethod) {
    apiResponse(405, false, 'Method not allowed.');
}

try {
    require __DIR__ . '/routes/' . $file;
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        apiResponse(409, false, 'This email address is already registered.');
    }
    error_log('API database error: ' . $e->getMessage());
    apiResponse(500, false, 'A server error occurred.');
} catch (Throwable $e) {
    error_log('API error: ' . $e->getMessage());
    apiResponse(500, false, 'A server error occurred.');
}