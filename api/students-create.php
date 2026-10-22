<?php
// POST ?route=students-create — admin adds a student

requireAdmin();
requireCsrfToken();

require_once __DIR__ . '/../../services/StudentService.php';

$service   = new StudentService($link);
$studentId = $service->create(getJsonInput());

apiResponse(201, true, 'Student added successfully.', [
    'student_id' => $studentId,
]);