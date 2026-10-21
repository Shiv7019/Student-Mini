
<?php
require_once __DIR__ . '/auth.php';

date_default_timezone_set('Asia/Kolkata');

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$todayDate = date('d M Y');
$currentYear = date('Y');
$studentAdded = isset($_GET['student_added']) && $_GET['student_added'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Dashboard | StudentPro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f5f7fb;
            color: #17243b;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            background: #17243b;
            min-height: 100vh;
        }

        .brand {
            color: #fff;
            font-size: 23px;
            font-weight: 700;
            text-decoration: none;
        }

        .sidebar a {
            display: block;
            color: #d7dfed;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 8px;
            margin: 5px 0;
            transition: background .2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2e4770;
            color: #fff;
        }

        .sidebar-label {
            color: #91a1bd;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 28px 14px 10px;
        }

        .stat-card, .panel {
            border: 0;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 3px 15px rgba(23,36,59,.05);
        }

        .stat-card {
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(23,36,59,.09);
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
            color: #17243b;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 23px;
            flex-shrink: 0;
        }

        .icon-blue { background: #eaf0ff; color: #2859c5; }
        .icon-green { background: #e4f8ee; color: #198754; }
        .icon-purple { background: #f0eaff; color: #7353ba; }

        .btn-primary {
            background: #315bd6;
            border-color: #315bd6;
        }

        .btn-primary:hover {
            background: #2448b2;
            border-color: #2448b2;
        }

        .table th {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            background: #f8faff;
        }

        .table td, .table th {
            padding: 14px 16px;
            vertical-align: middle;
        }

        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #eaf0ff;
            color: #2859c5;
            display: inline-grid;
            place-items: center;
            margin-right: 8px;
            font-weight: 700;
        }

        .chart-container {
            position: relative;
            height: 280px;
        }

        .topbar { min-height: 70px; }

        @media (max-width: 767px) {
            .sidebar { min-height: auto; }

            .sidebar nav {
                display: flex;
                flex-wrap: wrap;
                gap: 4px;
            }

            .sidebar nav a {
                padding: 9px 10px;
                font-size: 13px;
            }

            .sidebar-label, .sidebar-footer { display: none; }
            .stat-number { font-size: 25px; }
            .chart-container { height: 240px; }
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">

        <aside class="col-md-2 sidebar p-3">
            <a href="dashboard.php" class="brand d-block mb-4">
                <i class="bi bi-mortarboard-fill me-2"></i>StudentPro
            </a>

            <div class="sidebar-label">Main Menu</div>

            <nav>
                <a class="active" href="dashboard.php">
                    <i class="bi bi-grid-fill me-2"></i>Dashboard
                </a>

                <a href="add-student.php">
                    <i class="bi bi-person-plus-fill me-2"></i>Add Student
                </a>

                <a href="#recentStudents">
                    <i class="bi bi-people-fill me-2"></i>Recent Students
                </a>

                <a href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </nav>

            <div class="sidebar-footer text-white-50 small mt-5">
                <i class="bi bi-shield-check me-1"></i>
                Admin Management Panel
            </div>
        </aside>

        <main class="col-md-10 p-3 p-lg-4">

            <div class="topbar d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Dashboard</h2>
                    <p class="text-secondary mb-0">
                        Welcome back! Here's your student overview.
                    </p>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <a href="add-student.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus-fill me-2"></i>Add Student
                    </a>

                    <div class="text-md-end">
                        <div class="fw-semibold">
                            <i class="bi bi-person-circle me-1"></i><?= h($adminName) ?>
                        </div>
                        <small class="text-secondary"><?= h($todayDate) ?></small>
                    </div>
                </div>
            </div>

            <?php if ($studentAdded): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Student added successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div id="apiError" class="alert alert-danger d-none" role="alert">
                <span id="apiErrorText">Unable to load dashboard data.</span>
                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="retryButton">
                    Try again
                </button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="stat-card p-3 p-lg-4 h-100">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="text-secondary mb-2">Total Students</div>
                                <div class="stat-number" id="totalStudents">—</div>
                                <small class="text-secondary">All registered students</small>
                            </div>
                            <div class="stat-icon icon-blue">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="stat-card p-3 p-lg-4 h-100">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="text-secondary mb-2">Registered Today</div>
                                <div class="stat-number" id="todayStudents">—</div>
                                <small class="text-secondary">New registrations today</small>
                            </div>
                            <div class="stat-icon icon-green">
                                <i class="bi bi-person-plus-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="stat-card p-3 p-lg-4 h-100">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="text-secondary mb-2">Last 7 Days</div>
                                <div class="stat-number" id="weekStudents">—</div>
                                <small class="text-secondary">Recent registrations</small>
                            </div>
                            <div class="stat-icon icon-purple">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel p-3 p-md-4 mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Registration Activity</h5>
                        <p class="text-secondary small mb-0">
                            Student registrations over the last 7 days
                        </p>
                    </div>

                    <span class="badge rounded-pill text-bg-light">
                        <i class="bi bi-calendar3 me-1"></i>Last 7 days
                    </span>
                </div>

                <div class="chart-container">
                    <canvas id="registrationChart"></canvas>
                </div>
            </div>

            <div class="panel p-3 p-md-4 mb-4" id="recentStudents">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Recent Students</h5>
                        <p class="text-secondary small mb-0">Latest registrations</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill text-bg-primary" id="studentTotalBadge">
                            Loading…
                        </span>
                        <a href="add-student.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Add New
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Email Address</th>
                                <th>Mobile</th>
                                <th>Registered At</th>
                            </tr>
                        </thead>

                        <tbody id="studentsTableBody">
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Loading students…
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
                    <small class="text-secondary" id="paginationInfo">Loading…</small>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" id="previousPage" disabled>
                            <i class="bi bi-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="nextPage" disabled>
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <footer class="text-center text-secondary small py-3">
                <i class="bi bi-mortarboard-fill me-1"></i>
                StudentPro Admin Panel &middot; <?= h($currentYear) ?>
            </footer>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
    'use strict';

    const API_URL = 'api/index.php';
    const $ = (id) => document.getElementById(id);

    let currentPage = 1;
    let totalPages = 1;
    let registrationChart = null;

    function showError(message) {
        $('apiErrorText').textContent = message;
        $('apiError').classList.remove('d-none');
    }

    function hideError() {
        $('apiError').classList.add('d-none');
    }

    async function apiGet(route, page = null) {
        let url = `${API_URL}?route=${encodeURIComponent(route)}`;

        if (page !== null) {
            url += `&page=${encodeURIComponent(page)}`;
        }

        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        let result;

        try {
            result = await response.json();
        } catch {
            throw new Error('The API returned an invalid response. Check PHP errors.');
        }

        if (!response.ok || result.success !== true) {
            throw new Error(result.message || `Request failed (${response.status}).`);
        }

        return result.data;
    }

    function formatNumber(value) {
        const number = Number(value);
        return Number.isFinite(number) ? number.toLocaleString('en-IN') : '0';
    }

    async function loadDashboard() {
        const data = await apiGet('dashboard');

        $('totalStudents').textContent = formatNumber(data.total_students);
        $('todayStudents').textContent = formatNumber(data.today_students);
        $('weekStudents').textContent = formatNumber(data.last_7_days);
        $('studentTotalBadge').textContent =
            `${formatNumber(data.total_students)} total`;

        const labels = Array.isArray(data.chart_labels) ? data.chart_labels : [];
        const values = Array.isArray(data.chart_data) ? data.chart_data : [];

        if (registrationChart) {
            registrationChart.destroy();
        }

        const chartElement = $('registrationChart');

        if (typeof Chart !== 'undefined' && chartElement) {
            registrationChart = new Chart(chartElement, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'New students',
                        data: values,
                        borderColor: '#315bd6',
                        backgroundColor: 'rgba(49, 91, 214, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#315bd6',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#17243b',
                            padding: 12
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, stepSize: 1 },
                            grid: { color: '#edf0f6' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    }

    function createCell(text, className = '') {
        const cell = document.createElement('td');
        cell.textContent = text ?? '';
        if (className) cell.className = className;
        return cell;
    }

    function renderStudents(students) {
        const tbody = $('studentsTableBody');
        tbody.replaceChildren();

        if (!Array.isArray(students) || students.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');

            cell.colSpan = 5;
            cell.className = 'text-center py-5';

            const icon = document.createElement('i');
            icon.className = 'bi bi-people fs-1 text-secondary';

            const title = document.createElement('p');
            title.className = 'fw-semibold mt-2 mb-1';
            title.textContent = 'No students found';

            const description = document.createElement('p');
            description.className = 'text-secondary small';
            description.textContent = 'Start by adding your first student.';

            const link = document.createElement('a');
            link.href = 'add-student.php';
            link.className = 'btn btn-primary btn-sm';
            link.textContent = 'Add Student';

            cell.append(icon, title, description, link);
            row.appendChild(cell);
            tbody.appendChild(row);
            return;
        }

        students.forEach((student) => {
            const row = document.createElement('tr');

            row.appendChild(createCell(`#${student.id}`, 'text-secondary'));

            const nameCell = document.createElement('td');
            const avatar = document.createElement('span');
            avatar.className = 'student-avatar';

            const fullName = String(student.full_name ?? '');
            avatar.textContent = fullName.trim().charAt(0).toUpperCase() || '?';

            const name = document.createElement('span');
            name.className = 'fw-semibold';
            name.textContent = fullName;

            nameCell.append(avatar, name);
            row.appendChild(nameCell);

            row.appendChild(createCell(student.email));
            row.appendChild(createCell(student.mobile));
            row.appendChild(createCell(student.created_at, 'text-secondary small'));

            tbody.appendChild(row);
        });
    }

    async function loadStudents(page = 1) {
        $('studentsTableBody').innerHTML = `
            <tr><td colspan="5" class="text-center py-4">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Loading students…
            </td></tr>`;

        const data = await apiGet('students', page);
        const students = Array.isArray(data.students) ? data.students : [];
        const pagination = data.pagination || {};

        currentPage = Number(pagination.page) || page;
        totalPages = Math.max(1, Number(pagination.total_pages) || 1);

        renderStudents(students);

        $('studentTotalBadge').textContent =
            `${formatNumber(pagination.total)} total`;

        $('paginationInfo').textContent = pagination.total
            ? `Page ${currentPage} of ${totalPages}`
            : 'No student records';

        $('previousPage').disabled = currentPage <= 1;
        $('nextPage').disabled = currentPage >= totalPages;
    }

    async function loadAll() {
        hideError();
        $('retryButton').disabled = true;

        try {
            await Promise.all([
                loadDashboard(),
                loadStudents(currentPage)
            ]);
        } catch (error) {
            showError(error.message || 'Unable to load dashboard data.');

            $('studentsTableBody').innerHTML = `
                <tr><td colspan="5" class="text-center text-danger py-4">
                    Unable to load student records.
                </td></tr>`;
        } finally {
            $('retryButton').disabled = false;
        }
    }

    $('previousPage').addEventListener('click', async () => {
        if (currentPage > 1) {
            hideError();
            try {
                await loadStudents(currentPage - 1);
            } catch (error) {
                showError(error.message || 'Unable to load students.');
            }
        }
    });

    $('nextPage').addEventListener('click', async () => {
        if (currentPage < totalPages) {
            hideError();
            try {
                await loadStudents(currentPage + 1);
            } catch (error) {
                showError(error.message || 'Unable to load students.');
            }
        }
    });

    $('retryButton').addEventListener('click', loadAll);

    loadAll();
})();
</script>
</body>
</html>
