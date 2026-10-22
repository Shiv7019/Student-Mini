<?php
// POST /api/students-create.php — admin adds a student.

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../services/StudentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiResponse(405, false, 'Method not allowed.');
}

requireAdmin();
requireCsrfToken();

try {
    $service   = new StudentService($link);
    $studentId = $service->create(getJsonInput());

    apiResponse(201, true, 'Student added successfully.', [
        'student_id' => $studentId,
    ]);

} catch (InvalidArgumentException $e) {
    apiResponse(422, false, 'Please check the student details.');
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        apiResponse(409, false, 'This email address is already registered.');
    }
    error_log('api/students-create.php DB error: ' . $e->getMessage());
    apiResponse(500, false, 'A server error occurred.');
} catch (Throwable $e) {
    error_log('api/students-create.php error: ' . $e->getMessage());
    apiResponse(500, false, 'A server error occurred.');
}