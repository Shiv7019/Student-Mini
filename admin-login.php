
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Student Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f1f5fb;
            padding: 20px;
        }

        .login-card {
            width: min(420px, 92vw);
            border: 0;
            border-radius: 18px;
        }

        #message {
            overflow-wrap: anywhere;
        }
    </style>
</head>
<body>

<div class="card login-card shadow-sm">
    <div class="card-body p-4 p-md-5">
        <h3 class="fw-bold">Welcome back</h3>
        <p class="text-secondary">
            Sign in to the admin dashboard.
        </p>

        <div
            id="message"
            class="alert d-none"
            role="alert"
            aria-live="polite"
        ></div>

        <form id="loginForm">
            <label for="email" class="form-label">
                Email Address
            </label>

            <input
                class="form-control mb-3"
                type="email"
                id="email"
                name="email"
                autocomplete="username"
                required
            >

            <label for="password" class="form-label">
                Password
            </label>

            <input
                class="form-control mb-4"
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button
                type="submit"
                id="loginButton"
                class="btn btn-primary btn-lg w-100"
            >
                Sign In
            </button>
        </form>
    </div>
</div>

<script>
const loginForm = document.getElementById('loginForm');
const messageBox = document.getElementById('message');
const loginButton = document.getElementById('loginButton');

function showMessage(message, success = false) {
    messageBox.textContent = message;
    messageBox.className =
        'alert ' + (success ? 'alert-success' : 'alert-danger');
}

loginForm.addEventListener('submit', async function (event) {
    event.preventDefault();

    loginButton.disabled = true;
    loginButton.textContent = 'Signing in...';
    messageBox.className = 'alert d-none';

    try {
        // 1. Request a CSRF token from the central API.
        const csrfResponse = await fetch(
            'api/index.php?route=csrf',
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            }
        );

        const csrfResult = await csrfResponse.json();

        if (
            !csrfResponse.ok ||
            !csrfResult.success ||
            !csrfResult.data?.csrf_token
        ) {
            throw new Error(
                csrfResult.message ||
                'Could not obtain the security token.'
            );
        }

        // 2. Submit login credentials to the central API.
        const response = await fetch(
            'api/index.php?route=login',
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfResult.data.csrf_token
                },
                body: JSON.stringify({
                    email: document.getElementById('email').value.trim(),
                    password: document.getElementById('password').value
                })
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            showMessage(
                result.message || 'Login failed. Check your credentials.'
            );
            return;
        }

        showMessage('Login successful. Redirecting...', true);

        // 3. The API has authenticated the admin and created the session.
        window.location.href = 'dashboard.php';

    } catch (error) {
        showMessage(
            error.message ||
            'Could not connect to the API. Check XAMPP Apache and PHP.'
        );
    } finally {
        loginButton.disabled = false;
        loginButton.textContent = 'Sign In';
    }
});
</script>

</body>
</html>
