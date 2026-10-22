<?php
// GET /api/dashboard.php — dashboard stats + chart data.

require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiResponse(405, false, 'Method not allowed.');
}

requireAdmin();

try {
    $total = (int) $link
        ->query('SELECT COUNT(*) FROM students')
        ->fetch_row()[0];

    $today = (int) $link
        ->query(
            'SELECT COUNT(*) FROM students
             WHERE DATE(created_at) = CURDATE()'
        )
        ->fetch_row()[0];

    $week = (int) $link
        ->query(
            'SELECT COUNT(*) FROM students
             WHERE created_at >= CURDATE() - INTERVAL 6 DAY
               AND created_at < CURDATE() + INTERVAL 1 DAY'
        )
        ->fetch_row()[0];

    $chartResult = $link->query(
        'SELECT DATE(created_at) AS registration_date, COUNT(*) AS total
         FROM students
         WHERE created_at >= CURDATE() - INTERVAL 6 DAY
           AND created_at < CURDATE() + INTERVAL 1 DAY
         GROUP BY DATE(created_at)'
    );

    $dailyCounts = [];
    while ($row = $chartResult->fetch_assoc()) {
        $dailyCounts[$row['registration_date']] = (int) $row['total'];
    }

    $chartLabels = [];
    $chartData   = [];

    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i days"));
        $chartLabels[] = date('d M', strtotime($day));
        $chartData[]   = $dailyCounts[$day] ?? 0;
    }

    apiResponse(200, true, 'Dashboard data loaded.', [
        'total_students' => $total,
        'today_students' => $today,
        'last_7_days'    => $week,
        'chart_labels'   => $chartLabels,
        'chart_data'     => $chartData,
    ]);

} catch (Throwable $e) {
    error_log('api/dashboard.php error: ' . $e->getMessage());
    apiResponse(500, false, 'A server error occurred.');
}