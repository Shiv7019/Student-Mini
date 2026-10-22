<?php
// GET /api/students.php?page=N — paginated student list.

require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiResponse(405, false, 'Method not allowed.');
}

requireAdmin();

try {
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    $page = ($page !== false && $page !== null && $page > 0) ? $page : 1;

    $perPage = 10;
    $offset  = ($page - 1) * $perPage;

    $total = (int) $link
        ->query('SELECT COUNT(*) FROM students')
        ->fetch_row()[0];

    $stmt = $link->prepare(
        'SELECT id, full_name, email, mobile, created_at
         FROM students
         ORDER BY id DESC
         LIMIT ? OFFSET ?'
    );

    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();

    $result   = $stmt->get_result();
    $students = [];

    while ($student = $result->fetch_assoc()) {
        $students[] = $student;
    }

    apiResponse(200, true, 'Students loaded.', [
        'students'   => $students,
        'pagination' => [
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => (int) ceil($total / $perPage),
        ],
    ]);

} catch (Throwable $e) {
    error_log('api/students.php error: ' . $e->getMessage());
    apiResponse(500, false, 'A server error occurred.');
}