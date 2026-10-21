
<?php

require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$route = $_GET['route'] ?? '';

if (!is_string($route)) {
    apiResponse(400, false, 'Invalid API route.');
}

try {
    switch ($route) {

        // GET /api/index.php?route=csrf
        case 'csrf':
            if ($method !== 'GET') {
                apiResponse(405, false, 'Method not allowed.');
            }

            if (empty($_SESSION['api_csrf'])) {
                $_SESSION['api_csrf'] = bin2hex(random_bytes(32));
            }

            apiResponse(200, true, 'Security token generated.', [
                'csrf_token' => $_SESSION['api_csrf']
            ]);
            break;

        // GET /api/index.php?route=dashboard
        
        case 'dashboard':
            if ($method !== 'GET') {
                apiResponse(405, false, 'Method not allowed.');
            }

            requireAdmin();

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

            // Get registration counts for the last 7 calendar days.
            $chartResult = $link->query(
                'SELECT DATE(created_at) AS registration_date,
                        COUNT(*) AS total
                 FROM students
                 WHERE created_at >= CURDATE() - INTERVAL 6 DAY
                   AND created_at < CURDATE() + INTERVAL 1 DAY
                 GROUP BY DATE(created_at)'
            );

            $dailyCounts = [];

            while ($row = $chartResult->fetch_assoc()) {
                $dailyCounts[$row['registration_date']] =
                    (int) $row['total'];
            }

            $chartLabels = [];
            $chartData = [];

            for ($i = 6; $i >= 0; $i--) {
                $day = date('Y-m-d', strtotime("-$i days"));

                $chartLabels[] = date('d M', strtotime($day));
                $chartData[] = $dailyCounts[$day] ?? 0;
            }

            apiResponse(200, true, 'Dashboard data loaded.', [
                'total_students' => $total,
                'today_students' => $today,
                'last_7_days' => $week,
                'chart_labels' => $chartLabels,
                'chart_data' => $chartData
            ]);
            break;


        // GET /api/index.php?route=students
        case 'students':
            if ($method !== 'GET') {
                apiResponse(405, false, 'Method not allowed.');
            }

            requireAdmin();

            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $page = ($page !== false && $page !== null && $page > 0)
                ? $page : 1;

            $perPage = 10;
            $offset = ($page - 1) * $perPage;

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

            $result = $stmt->get_result();
            $students = [];

            while ($student = $result->fetch_assoc()) {
                $students[] = $student;
            }

            apiResponse(200, true, 'Students loaded.', [
                'students' => $students,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $perPage)
                ]
            ]);
            break;

        // POST /api/index.php?route=register
        // Public student registration
        case 'register':
            if ($method !== 'POST') {
                apiResponse(405, false, 'Method not allowed.');
            }

            requireCsrfToken();

            $input = getJsonInput();;

            $name = trim($input['full_name'] ?? '');
            $email = strtolower(trim($input['email'] ?? ''));
            $mobile = trim($input['mobile'] ?? '');
            $password = $input['password'] ?? '';

            if (
                $name === '' ||
                strlen($name) > 100 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($email) > 150 ||
                !preg_match('/^[0-9+\-\s()]{7,20}$/', $mobile) ||
                !is_string($password) ||
                strlen($password) < 8
            ) {
                apiResponse(
                    422,
                    false,
                    'Enter valid details and a password of at least 8 characters.'
                );
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $link->prepare(
                'INSERT INTO students
                    (full_name, email, mobile, password_hash)
                 VALUES (?, ?, ?, ?)'
            );

            $stmt->bind_param('ssss', $name, $email, $mobile, $hash);
            $stmt->execute();

            apiResponse(201, true, 'Student registered successfully.', [
                'student_id' => $link->insert_id
            ]);
            break;

        // POST /api/index.php?route=login
        case 'login':
            if ($method !== 'POST') {
                apiResponse(405, false, 'Method not allowed.');
            }

            requireCsrfToken();

            $input = getJsonInput();

            $email = strtolower(trim($input['email'] ?? ''));
            $password = $input['password'] ?? '';

            if (
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                !is_string($password)
            ) {
                apiResponse(422, false, 'Enter a valid email and password.');
            }

            $stmt = $link->prepare(
                'SELECT id, name, email, password_hash
                 FROM admins WHERE email = ? LIMIT 1'
            );

            $stmt->bind_param('s', $email);
            $stmt->execute();

            $admin = $stmt->get_result()->fetch_assoc();

            if (
                !$admin ||
                !password_verify($password, $admin['password_hash'])
            ) {
                apiResponse(401, false, 'Invalid email or password.');
            }

            session_regenerate_id(true);

            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['api_csrf'] = bin2hex(random_bytes(32));

            apiResponse(200, true, 'Login successful.', [
                'admin' => [
                    'id' => (int) $admin['id'],
                    'name' => $admin['name'],
                    'email' => $admin['email']
                ],
                'csrf_token' => $_SESSION['api_csrf']
            ]);
            break;

        // POST /api/index.php?route=students-create
        case 'students-create':
            if ($method !== 'POST') {
                apiResponse(405, false, 'Method not allowed.');
            }

            requireAdmin();
            requireCsrfToken();

            $input = getJsonInput();

            $name = trim($input['full_name'] ?? '');
            $email = strtolower(trim($input['email'] ?? ''));
            $mobile = trim($input['mobile'] ?? '');
            $password = $input['password'] ?? '';

            if (
                $name === '' ||
                strlen($name) > 100 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($email) > 150 ||
                !preg_match('/^[0-9+\-\s()]{7,20}$/', $mobile) ||
                !is_string($password) ||
                strlen($password) < 8
            ) {
                apiResponse(422, false, 'Please check the student details.');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $link->prepare(
                'INSERT INTO students
                    (full_name, email, mobile, password_hash)
                 VALUES (?, ?, ?, ?)'
            );

            $stmt->bind_param('ssss', $name, $email, $mobile, $hash);
            $stmt->execute();

            apiResponse(201, true, 'Student added successfully.', [
                'student_id' => $link->insert_id
            ]);
            break;

        // POST /api/index.php?route=logout
        case 'logout':
            if ($method !== 'POST') {
                apiResponse(405, false, 'Method not allowed.');
            }

            requireAdmin();
            requireCsrfToken();

            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();

                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            session_destroy();

            apiResponse(200, true, 'Logged out successfully.');
            break;

        default:
            apiResponse(404, false, 'API route not found.');
    }

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        apiResponse(409, false, 'This email address is already registered.');
    }

    error_log('Central API database error: ' . $e->getMessage());

    apiResponse(500, false, 'A server error occurred.');
} catch (Throwable $e) {
    error_log('Central API error: ' . $e->getMessage());

    apiResponse(500, false, 'A server error occurred.');
}
