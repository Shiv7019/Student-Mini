
<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_mini';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $link = new mysqli(
        $host,
        $username,
        $password,
        $database
    );

    $link->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check configuration.');
}
