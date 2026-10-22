<?php
// POST /api/register.php — public student signup.

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../services/StudentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiResponse(405, false, 'Method not allowed.');
}

requireCsrfToken();

try {
    $service   = new StudentService($link);
    $studentId = $service->create(getJsonInput());

    apiResponse(201, true, 'Student registered successfully.', [
        'student_id' => $studentId,
    ]);

} catch (InvalidArgumentException $e) {
    apiResponse(422, false, 'Please check the student details.');
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        apiResponse(409, false, 'This email address is already registered.');
    }
    error_log('api/register.php DB error: ' . $e->getMessage());
    apiResponse(500, false, 'A server error occurred.');
} catch (Throwable $e) {
    error_log('api/register.php error: ' . $e->getMessage());
    apiResponse(500, false, 'A server error occurred.');
}