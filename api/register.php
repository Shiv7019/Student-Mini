<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON request.'
    ]);
    exit;
}

$name = trim($input['full_name'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$mobile = trim($input['mobile'] ?? '');
$password = $input['password'] ?? '';

if (
    $name === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !preg_match('/^[0-9+\-\s()]{7,20}$/', $mobile) ||
    !is_string($password) ||
    strlen($password) < 8
) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Enter a valid name, email, mobile and password (at least 8 characters).'
    ]);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $link->prepare(
        'INSERT INTO students (full_name, email, mobile, password_hash)
         VALUES (?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'ssss',
        $name,
        $email,
        $mobile,
        $passwordHash
    );

    $stmt->execute();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Student registered successfully.',
        'student_id' => $link->insert_id
    ]);
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'This email is already registered.'
        ]);
    } else {
        error_log('Registration API error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed due to a server error.'
        ]);
    }
}
