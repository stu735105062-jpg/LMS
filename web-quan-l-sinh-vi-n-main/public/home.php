<?php
// 1. Khởi tạo session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Kết nối Database
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
if (!$conn) { die("Kết nối thất bại: " . mysqli_connect_error()); }
mysqli_set_charset($conn, "utf8mb4");

// 3. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit();
}

$u_id = (int)$_SESSION['user_id'];

// 4. Lấy thông tin user hiện tại (Sửa lỗi Null)
$user_res = mysqli_query($conn, "SELECT * FROM users WHERE id = $u_id");
$current_user = mysqli_fetch_assoc($user_res);

if (!$current_user) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$role = $current_user['role'] ?? 'student';
$username = $current_user['username'];
if ($role === 'student') {
    header("Location: ../models/student.php");
    exit();
}
// 5. Xử lý các hành động (Actions) - Chỉ Admin mới có quyền
$action = $_GET['action'] ?? '';

if ($role === 'admin') {

    // Đổi quyền
    if ($action === 'change_role' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $target_id = (int)$_POST['target_user_id'];
        $new_role = mysqli_real_escape_string($conn, $_POST['new_role']);

        if ($target_id !== $u_id) {
            mysqli_query($conn, "UPDATE users SET role = '$new_role' WHERE id = $target_id");
            $_SESSION['flash'] = ['msg' => 'Cập nhật quyền thành công!', 'type' => 'success'];
        } else {
            $_SESSION['flash'] = ['msg' => 'Bạn không thể tự đổi quyền!', 'type' => 'danger'];
        }
        header("Location: home.php");
        exit();
    }

    // Xóa sinh viên
    if ($action === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        mysqli_query($conn, "DELETE FROM student_info WHERE id = $id");
        $_SESSION['flash'] = ['msg' => 'Đã xóa sinh viên!', 'type' => 'warning'];
        header("Location: home.php");
        exit();
    }
}

// 6. Truy vấn dữ liệu cho Dashboard
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = !empty($search) ? " AND (s.name LIKE '%$search%' OR s.email LIKE '%$search%')" : "";

$query = "SELECT s.*, c.class_name, 
          (SELECT COALESCE(AVG(score), 0) FROM grades WHERE student_id = s.id) as gpa 
          FROM student_info s 
          LEFT JOIN classes c ON s.class_id = c.class_id 
          WHERE 1=1 $where";
$students = mysqli_fetch_all(mysqli_query($conn, $query), MYSQLI_ASSOC);

// Thống kê điểm danh
$att_res = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM attendance GROUP BY status");
$att_data = ['present' => 0, 'absent' => 0];
while($row = mysqli_fetch_assoc($att_res)) {
    $att_data[$row['status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="vi" data-bs-theme="light" id="htmlTag">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS PRO - Dashboard</title>
    <link rel="stylesheet" href="asset/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --sidebar-w: 260px; }
        body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; transition: 0.3s; }
        .sidebar { width: var(--sidebar-w); height: 100vh; position: fixed; background: #4e73df; color: white; z-index: 1000; }
        .main-content { margin-left: var(--sidebar-w); padding: 2rem; }
        .card { border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); border-radius: 12px; }
        .nav-link { color: rgba(255,255,255,0.8); margin: 5px 15px; border-radius: 8px; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.2); color: white; }
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar span, .sidebar h4, .sidebar hr { display: none; }
            .main-content { margin-left: 70px; }
        }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column p-3">
    <h4 class="text-center fw-bold py-3"><i class="bi bi-mortarboard-fill"></i> <span>SMS PRO</span></h4>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li><a href="home.php" class="nav-link active"><i class="bi bi-house-door me-2"></i> <span>Dashboard</span></a></li>
        <li><a href="../models/manage_grades.php" class="nav-link"><i class="bi bi-person-check me-2"></i> <span>Bảng điểm lớp</span></a></li>
        <?php if($role != 'student'): ?>
            <li><a href="export.php?action=export" class="nav-link"><i class="bi bi-cloud-download me-2"></i> <span>Xuất dữ liệu</span></a></li>
            <?php if($role == 'admin'): ?>
                <li><a href="register_student.php" class="nav-link text-white bg-success m-2"><i class="bi bi-person-plus me-2"></i> <span>Đăng ký SV</span></a></li>
                <li><a href="permissions.php" class="nav-link"><i class="bi bi-shield-lock me-2"></i> <span>Phân quyền tài khoản</span></a></li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
    
    <div class="px-3 mb-3 text-center">
        <button onclick="toggleTheme()" class="btn btn-light btn-sm w-100 mb-2">
            <i class="bi bi-moon-stars" id="themeIcon"></i> <span>Giao diện</span>
        </button>
        <span class="badge bg-warning text-dark text-uppercase px-3 py-2">Quyền: <?= $role ?></span>
    </div>
    <a href="logout.php" class="btn btn-danger btn-sm w-100"><i class="bi bi-box-arrow-right"></i> <span>Đăng xuất</span></a>
</div>

<div class="main-content">
    <?php if(isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show border-0 shadow-sm">
            <?= $_SESSION['flash']['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary m-0">Hệ Thống Quản Trị</h2>
            <p class="text-muted small">Chào mừng trở lại, <b><?= htmlspecialchars($username) ?></b></p>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <form class="d-flex" method="GET">
                <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" placeholder="Tìm sinh viên..." value="<?= htmlspecialchars($search) ?>">
            </form>
            <div class="bg-white p-2 rounded-circle shadow-sm">
                <i class="bi bi-person-circle fs-4 text-primary"></i>
            </div>
        </div>
    </header>

    <div class="row mb-4 g-4">
        <div class="col-lg-8">
            <div class="card p-4 h-100">
                <h6 class="fw-bold text-secondary mb-4"><i class="bi bi-bar-chart-line me-2"></i>Phân tích GPA Sinh viên</h6>
                <canvas id="gpaChart" height="150"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-4 h-100 text-center">
                <h6 class="fw-bold text-secondary mb-4"><i class="bi bi-pie-chart me-2"></i>Tình trạng Điểm danh</h6>
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-secondary">Danh sách sinh viên hệ thống</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light text-muted small uppercase">
                    <tr>
                        <th class="ps-4">Mã SV</th>
                        <th>Thông tin</th>
                        <th>Lớp học</th>
                        <th>GPA trung bình</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $s): ?>
                    <tr>
                        <td class="ps-4 fw-bold text-primary">#<?= $s['id'] ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($s['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($s['email']) ?></small>
                        </td>
                        <td><span class="badge bg-info-subtle text-info"><?= $s['class_name'] ?? 'N/A' ?></span></td>
                        <td><span class="badge bg-success fs-6"><?= number_format($s['gpa'], 2) ?></span></td>
                        <td class="text-end pe-4">
                            <?php if($role == 'admin'): ?>
                                <a href="home.php?action=delete&id=<?= $s['id'] ?>" class="btn btn-sm text-danger border-0" onclick="return confirm('Xóa sinh viên này?')"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                            <button class="btn btn-sm text-primary border-0"><i class="bi bi-pencil-square"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // 1. Chart GPA
    const gpaCtx = document.getElementById('gpaChart');
    new Chart(gpaCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($students, 'name')) ?>,
            datasets: [{
                label: 'GPA',
                data: <?= json_encode(array_column($students, 'gpa')) ?>,
                backgroundColor: '#4e73df',
                borderRadius: 8
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 10 } } }
    });

    // 2. Chart Điểm danh
    new Chart(document.getElementById('attendanceChart'), {
        type: 'doughnut',
        data: {
            labels: ['Có mặt', 'Vắng'],
            datasets: [{
                data: [<?= $att_data['present'] ?>, <?= $att_data['absent'] ?>],
                backgroundColor: ['#1cc88a', '#e74a3b'],
                borderWidth: 0
            }]
        },
        options: { cutout: '70%' }
    });

    // 3. Dark Mode
    const html = document.getElementById('htmlTag');
    const icon = document.getElementById('themeIcon');
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);

    function toggleTheme() {
        const current = html.getAttribute('data-bs-theme');
        const next = current === 'light' ? 'dark' : 'light';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
        icon.className = next === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars';
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>