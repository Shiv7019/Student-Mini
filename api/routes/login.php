<?php
// POST /api/login.php — authenticate an admin.

require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiResponse(405, false, 'Method not allowed.');
}

requireCsrfToken();

try {
    $input    = getJsonInput();
    $email    = strtolower(trim($input['email'] ?? ''));
    $password = $input['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password)) {
        apiResponse(422, false, 'Enter a valid email and password.');
    }

    $stmt = $link->prepare(
        'SELECT id, name, email, password_hash
         FROM admins WHERE email = ? LIMIT 1'
    );

    $stmt->bind_param('s', $email);
    $stmt->execute();

    $admin = $stmt->get_result()->fetch_assoc();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        apiResponse(401, false, 'Invalid email or password.');
    }

    session_regenerate_id(true);

    $_SESSION['admin_id']    = (int) $admin['id'];
    $_SESSION['admin_name']  = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['api_csrf']    = bin2hex(random_bytes(32));

    apiResponse(200, true, 'Login successful.', [
        'admin' => [
            'id'    => (int) $admin['id'],
            'name'  => $admin['name'],
            'email' => $admin['email'],
        ],
        'csrf_token' => $_SESSION['api_csrf'],
    ]);

} catch (Throwable $e) {
    error_log('api/login.php error: ' . $e->getMessage());
    apiResponse(500, false, 'A server error occurred.');
}