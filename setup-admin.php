
<?php
session_start();
require_once __DIR__ . '/config/database.php';

$count = (int) $link->query("SELECT COUNT(*) FROM admins")->fetch_row()[0];
$message = '';

if ($count > 0) {
    exit('An admin account already exists. Delete setup-admin.php and use admin-login.php.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !is_string($password) ||
        strlen($password) < 12
    ) {
        $message = 'Enter a valid name and email, and a password of at least 12 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $link->prepare(
            'INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('sss', $name, $email, $hash);
        $stmt->execute();

        header('Location: admin-login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Admin Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:500px">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h3 class="mb-3">Create First Admin</h3>
            <p class="text-secondary">Use a strong password of at least 12 characters.</p>

            <?php if ($message !== ''): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <label class="form-label">Admin Name</label>
                <input class="form-control mb-3" name="name" maxlength="100" required>

                <label class="form-label">Admin Email</label>
                <input class="form-control mb-3" type="email" name="email" maxlength="150" required>

                <label class="form-label">Password</label>
                <input class="form-control mb-3" type="password" name="password" minlength="12" autocomplete="new-password" required>

                <button class="btn btn-primary w-100">Create Admin</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
