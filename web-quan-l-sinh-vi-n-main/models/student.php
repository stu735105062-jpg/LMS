<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// 1. KIỂM TRA QUYỀN TRUY CẬP
if (!isset($_SESSION['authenticated']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$msg = ""; $error = "";

// 2. LẤY THÔNG TIN CHI TIẾT SINH VIÊN
$stu_query = $conn->prepare("
    SELECT s.*, c.class_name, c.teacher_name 
    FROM student_info s 
    LEFT JOIN classes c ON s.class_id = c.class_id 
    WHERE s.email = ?
");
$stu_query->bind_param("s", $username);
$stu_query->execute();
$student = $stu_query->get_result()->fetch_assoc();

if (!$student) {
    die("Không tìm thấy dữ liệu sinh viên.");
}

$student_id = $student['id'];

// 3. TRUY VẤN KẾT QUẢ HỌC TẬP (CHỈ LẤY MÔN ĐÃ ĐĂNG KÝ)
// Thay đổi: Sử dụng INNER JOIN với bảng course_registrations để lọc đúng môn sinh viên đã chọn
$sql_results = "
    SELECT c.course_id, c.course_name, g.score 
    FROM course_registrations cr
    INNER JOIN courses c ON cr.course_id = c.course_id
    LEFT JOIN grades g ON (g.course_id = c.course_id AND g.student_id = cr.student_id)
    WHERE cr.student_id = ?";

$stmt_res = $conn->prepare($sql_results);
$stmt_res->bind_param("i", $student_id);
$stmt_res->execute();
$results_data = $stmt_res->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. TÍNH TOÁN GPA
$total_score = 0;
$count = 0;
foreach($results_data as $r) {
    if($r['score'] !== null) {
        $total_score += $r['score'];
        $count++;
    }
}
$gpa = $count > 0 ? round($total_score / $count, 2) : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cổng thông tin sinh viên - <?= htmlspecialchars($student['name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .gpa-badge { font-size: 2.5rem; font-weight: 800; color: #0d6efd; }
        .nav-link { color: #6c757d; border: none !important; }
        .nav-link.active { border-bottom: 3px solid #0d6efd !important; font-weight: bold; color: #0d6efd !important; background: none !important; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center">
            <div class="col-md-8 d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-4" style="width: 70px; height: 70px;">
                    <i class="bi bi-person-badge fs-1"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($student['name']) ?></h3>
                    <p class="text-muted mb-0">MSSV: 2024<?= str_pad($student['id'], 4, '0', STR_PAD_LEFT) ?> | Lớp: <?= htmlspecialchars($student['class_name'] ?? 'Chưa xếp lớp') ?></p>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="register_course.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Đăng ký môn</a>
                <a href="../public/index.php" class="btn btn-outline-danger rounded-pill px-4 fw-bold">Đăng xuất</a>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4 justify-content-center border-0" id="studentTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab">HỒ SƠ CÁ NHÂN</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="transcript-tab" data-bs-toggle="tab" href="#transcript" role="tab">BẢNG ĐIỂM CHI TIẾT</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="profile" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-custom p-4 text-center bg-white h-100">
                        <p class="text-muted fw-bold mb-1 small">GPA TÍCH LŨY</p>
                        <div class="gpa-badge mb-2"><?= number_format($gpa, 2) ?></div>
                        <div>
                            <span class="badge rounded-pill <?= $gpa >= 8 ? 'bg-success' : ($gpa >= 6.5 ? 'bg-primary' : 'bg-warning text-dark') ?> px-3 py-2">
                                Xếp loại: <?= $gpa >= 8 ? 'Giỏi' : ($gpa >= 6.5 ? 'Khá' : ($gpa >= 5 ? 'Trung bình' : 'Yếu')) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-custom p-4 bg-white h-100">
                        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Thông tin chi tiết</h5>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="text-muted small fw-bold">EMAIL LIÊN HỆ</label>
                                <div><?= htmlspecialchars($student['email']) ?></div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="text-muted small fw-bold">SỐ ĐIỆN THOẠI</label>
                                <div><?= htmlspecialchars($student['phone'] ?? 'Chưa có') ?></div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="text-muted small fw-bold">CỐ VẤN HỌC TẬP</label>
                                <div><?= htmlspecialchars($student['teacher_name'] ?? 'Đang cập nhật') ?></div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="text-muted small fw-bold">ĐỊA CHỈ THƯỜNG TRÚ</label>
                                <div><?= htmlspecialchars($student['address'] ?? 'Chưa cập nhật') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="transcript" role="tabpanel">
            <div class="card card-custom bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th class="py-3">Mã học phần</th>
                                <th class="text-start py-3">Tên môn học</th>
                                <th class="py-3">Điểm số</th>
                                <th class="py-3">Điểm chữ</th>
                                <th class="py-3">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php if(count($results_data) > 0): ?>
                                <?php foreach($results_data as $row): ?>
                                <tr>
                                    <td class="text-muted">#CP<?= str_pad($row['course_id'], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td class="text-start fw-bold text-dark"><?= htmlspecialchars($row['course_name']) ?></td>
                                    <td class="fw-bold"><?= $row['score'] !== null ? number_format($row['score'], 1) : '<span class="text-muted small italic">Chưa có điểm</span>' ?></td>
                                    <td class="fw-bold text-primary">
                                        <?php 
                                            if($row['score'] === null) echo '-';
                                            elseif($row['score'] >= 8.5) echo 'A';
                                            elseif($row['score'] >= 7.0) echo 'B';
                                            elseif($row['score'] >= 5.5) echo 'C';
                                            elseif($row['score'] >= 4.0) echo 'D';
                                            else echo 'F';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if($row['score'] === null): ?>
                                            <span class="badge bg-info-subtle text-info border border-info">Đang học</span>
                                        <?php elseif($row['score'] >= 4): ?>
                                            <span class="badge bg-success-subtle text-success border border-success">Đạt</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger">Học lại</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-5">
                                        <i class="bi bi-journal-x fs-1 text-muted"></i>
                                        <p class="mt-2 text-muted">Bạn chưa đăng ký môn học nào trong học kỳ này.</p>
                                        <a href="register_course.php" class="btn btn-sm btn-outline-primary">Đăng ký ngay</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>