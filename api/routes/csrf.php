<?php
// GET /api/csrf.php — generate or return a CSRF token.

require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiResponse(405, false, 'Method not allowed.');
}

if (empty($_SESSION['api_csrf'])) {
    $_SESSION['api_csrf'] = bin2hex(random_bytes(32));
}

apiResponse(200, true, 'Security token generated.', [
    'csrf_token' => $_SESSION['api_csrf'],
]);