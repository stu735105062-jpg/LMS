<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
mysqli_set_charset($conn, "utf8mb4");

// 1. XỬ LÝ THÊM LỚP
if (isset($_POST['add_class'])) {
    $cname = mysqli_real_escape_string($conn, $_POST['class_name']);
    $tname = mysqli_real_escape_string($conn, $_POST['teacher_name']);
    
    $sql_add_class = "INSERT INTO classes (class_name, teacher_name) VALUES ('$cname', '$tname')";
    if (mysqli_query($conn, $sql_add_class)) {
        $new_class_id = mysqli_insert_id($conn);
        // Tự động tạo môn học tương ứng
        $sql_add_course = "INSERT INTO courses (class_id, course_name) VALUES ('$new_class_id', '$cname')";
        mysqli_query($conn, $sql_add_course);
        $msg = "Thêm lớp học thành công!";
    } else {
        $error = "Lỗi: " . mysqli_error($conn);
    }
}

// 2. XỬ LÝ XÓA LỚP
if (isset($_GET['delete'])) {
    $cid = intval($_GET['delete']);
    // Kiểm tra xem có sinh viên nào đang thuộc lớp này không
    $check = mysqli_query($conn, "SELECT id FROM student_info WHERE class_id = $cid");
    if (mysqli_num_rows($check) > 0) {
        $error = "Không thể xóa lớp đang có sinh viên chính quy!";
    } else {
        mysqli_query($conn, "DELETE FROM classes WHERE class_id = $cid");
        header("Location: manage_grades.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi" data-bs-theme="light" id="appHtml">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống Quản lý Lớp học</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary-color: #4361ee; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { width: 260px; height: 100vh; position: fixed; background: #fff; border-right: 1px solid #eee; padding: 2rem 1.5rem; }
        .main-content { margin-left: 260px; padding: 2.5rem; }
        .card-pro { background: #fff; border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .card-pro:hover { transform: translateY(-5px); }
        .class-icon { width: 45px; height: 45px; background: rgba(67, 97, 238, 0.1); color: var(--primary-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .nav-link.active { background: rgba(67, 97, 238, 0.1); color: var(--primary-color) !important; font-weight: 600; border-radius: 10px; }
    </style>
</head>
<body>

<div class="sidebar d-none d-lg-block">
    <div class="d-flex align-items-center mb-5 ps-2">
        <i class="bi bi- Mortarboard-fill fs-3 text-primary me-2"></i>
        <span class="fw-bold fs-4">SMS ADMIN</span>
    </div>
    <nav class="nav flex-column gap-2">
        <a href="../public/home.php" class="nav-link text-muted"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="manage_grades.php" class="nav-link active"><i class="bi bi-collection me-2"></i> Lớp học & Điểm</a>
        <a href="class_details.php" class="nav-link text-muted"><i class="bi bi-people me-2"></i> Sinh viên</a>
        <hr>
        <button onclick="toggleTheme()" class="btn btn-sm btn-light w-100 mt-2"><i class="bi bi-moon-stars me-2"></i>Đổi màu nền</button>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Quản lý Lớp học</h2>
            <p class="text-muted">Xem sĩ số và cập nhật điểm số cho từng lớp</p>
        </div>
        <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#addClass">
            <i class="bi bi-plus-lg me-2"></i>Tạo lớp mới
        </button>
    </div>

    <?php if(isset($msg)) echo "<div class='alert alert-success border-0 shadow-sm rounded-4'>$msg</div>"; ?>
    <?php if(isset($error)) echo "<div class='alert alert-danger border-0 shadow-sm rounded-4'>$error</div>"; ?>

    <div class="row g-4">
        <?php 
        // SQL NÂNG CẤP: Đếm cả sinh viên chính quy và sinh viên đăng ký tự chọn
        $sql = "SELECT c.*, 
                (SELECT COUNT(DISTINCT s.id) 
                 FROM student_info s 
                 LEFT JOIN course_registrations cr ON s.id = cr.student_id
                 LEFT JOIN courses co ON co.course_id = cr.course_id
                 WHERE s.class_id = c.class_id OR co.class_id = c.class_id) as total_students
                FROM classes c";
        
        $res = mysqli_query($conn, $sql);
        while($row = mysqli_fetch_assoc($res)):
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card-pro p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="class-icon"><i class="bi bi-book"></i></div>
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item text-danger" href="?delete=<?= $row['class_id'] ?>" onclick="return confirm('Xóa lớp này?')">Xóa lớp</a></li>
                        </ul>
                    </div>
                </div>
                
                <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($row['class_name']) ?></h5>
                <p class="text-muted small mb-3"><i class="bi bi-person me-1"></i> GV: <?= htmlspecialchars($row['teacher_name'] ?? 'Chưa rõ') ?></p>
                
                <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-4">
                    <div>
                        <span class="d-block fw-bold fs-5"><?= $row['total_students'] ?></span>
                        <span class="text-muted x-small" style="font-size: 0.7rem;">SINH VIÊN</span>
                    </div>
                    <a href="../public/class_details.php?id=<?= $row['class_id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3">Quản lý lớp</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="addClass" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <form method="POST">
                <div class="modal-header border-0 p-4">
                    <h5 class="fw-bold m-0">Thêm lớp học & Học phần</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">TÊN LỚP</label>
                        <input type="text" name="class_name" class="form-control rounded-3" placeholder="VD: CNTT K16" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">GIẢNG VIÊN</label>
                        <input type="text" name="teacher_name" class="form-control rounded-3" placeholder="Tên giảng viên..." required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" name="add_class" class="btn btn-primary w-100 py-2 fw-bold rounded-3">XÁC NHẬN TẠO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleTheme() {
        const body = document.body;
        body.style.backgroundColor = body.style.backgroundColor === 'rgb(33, 37, 41)' ? '#f8f9fa' : '#212529';
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>