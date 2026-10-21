<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/config/database.php';

$result = $link->query(
    'SELECT COUNT(*) AS total FROM students'
);

$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
</head>
<body>
    <h1>Mini Ollato Student Management</h1>

    <p>PHP and MySQL connected successfully!</p>

    <p>Total students: <?= (int) $row['total'] ?></p>
</body>
</html>
