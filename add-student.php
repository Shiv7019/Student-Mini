
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['student_csrf'])) {
    $_SESSION['student_csrf'] = bin2hex(random_bytes(32));
}

$name = '';
$email = '';
$mobile = '';
$message = '';
$messageType = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['student_csrf'], $token)) {
        $message = 'Your session expired. Refresh the page and try again.';
    } else {
        $name = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $mobile = trim($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';

        if (
            $name === '' ||
            strlen($name) > 100 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 150 ||
            !preg_match('/^[0-9+\-\s()]{7,20}$/', $mobile) ||
            !is_string($password) ||
            strlen($password) < 8
        ) {
            $message = 'Please enter valid details. Password must be at least 8 characters.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $link->prepare(
                    'INSERT INTO students
                    (full_name, email, mobile, password_hash)
                    VALUES (?, ?, ?, ?)'
                );

                $stmt->bind_param(
                    'ssss',
                    $name,
                    $email,
                    $mobile,
                    $hash
                );

                $stmt->execute();

                header('Location: dashboard.php?student_added=1');
                exit;

            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $message = 'This email address is already registered.';
                } else {
                    error_log('Add student error: ' . $e->getMessage());
                    $message = 'Unable to add student. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Student | StudentPro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
            color: #17243b;
        }

        .student-card {
            max-width: 720px;
            margin: 35px auto;
            border: 0;
            border-radius: 16px;
            box-shadow: 0 4px 20px #17243b0d;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control {
            padding: 11px 12px;
        }
    </style>
</head>
<body>

<div class="container px-3 py-3">
    <a href="dashboard.php" class="text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>

    <div class="card student-card">
        <div class="card-body p-4 p-md-5">

            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="bg-primary-subtle text-primary rounded-3 p-3 fs-3">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1">Add New Student</h3>
                    <p class="text-secondary mb-0">
                        Enter the student's information below.
                    </p>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?= h($messageType) ?>">
                    <?= h($message) ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token"
                       value="<?= h($_SESSION['student_csrf']) ?>">

                <div class="mb-3">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input class="form-control" id="full_name"
                           name="full_name" maxlength="100"
                           value="<?= h($name) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email Address</label>
                    <input class="form-control" type="email" id="email"
                           name="email" maxlength="150"
                           value="<?= h($email) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="mobile">Mobile Number</label>
                    <input class="form-control" type="tel" id="mobile"
                           name="mobile" maxlength="20"
                           value="<?= h($mobile) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="password">Initial Password</label>
                    <input class="form-control" type="password" id="password"
                           name="password" minlength="8"
                           autocomplete="new-password" required>
                    <div class="form-text">
                        At least 8 characters. Share it securely with the student.
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Save Student
                    </button>

                    <a href="dashboard.php" class="btn btn-light btn-lg">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
