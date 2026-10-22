<?php
// api/_bootstrap.php
// Shared setup for every endpoint file in api/.
// Not directly reachable — guarded below.

// If someone hits _bootstrap.php directly in the browser, 404.
if (realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    http_response_code(404);
    exit('Not found.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function apiResponse(
    int $status,
    bool $success,
    string $message,
    array $data = []
): never {
    http_response_code($status);

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_id'])) {
        apiResponse(401, false, 'Please log in as an administrator.');
    }
}

function getJsonInput(): array
{
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true);

    if (!is_array($input)) {
        apiResponse(400, false, 'Invalid JSON request.');
    }

    return $input;
}

function requireCsrfToken(): void
{
    $token        = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['api_csrf'] ?? '';

    if (
        !is_string($token) ||
        !is_string($sessionToken) ||
        $sessionToken === '' ||
        !hash_equals($sessionToken, $token)
    ) {
        apiResponse(403, false, 'Invalid or expired security token.');
    }
}