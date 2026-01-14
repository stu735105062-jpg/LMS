<?php
session_start();

// 1. KẾT NỐI CƠ SỞ DỮ LIỆU
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// 2. CẤU HÌNH FILE EXCEL
$filename = "Bang_Diem_Chi_Tiet_" . date('Ymd_His') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// 3. TRUY VẤN DỮ LIỆU
// Sử dụng COALESCE trong SQL để đảm bảo score không bao giờ là NULL khi trả về PHP
$query = "SELECT 
            s.id AS student_id, 
            s.name AS student_name, 
            c.class_name, 
            co.course_name,
            COALESCE(g.score, 0) AS score,
            (SELECT COUNT(*) FROM attendance WHERE student_id = s.id AND status = 'present') AS total_present
          FROM student_info s 
          LEFT JOIN classes c ON s.class_id = c.class_id
          INNER JOIN grades g ON s.id = g.student_id
          INNER JOIN courses co ON g.course_id = co.course_id
          ORDER BY s.id ASC, co.course_name ASC";

$res = mysqli_query($conn, $query);
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    .table-style { border-collapse: collapse; width: 100%; }
    .table-style th { background-color: #4e73df; color: #ffffff; border: 1px solid #dee2e6; height: 35px; }
    .table-style td { border: 1px solid #dee2e6; text-align: center; padding: 8px; vertical-align: middle; }
    .header-title { font-size: 20px; font-weight: bold; color: #4e73df; text-align: center; }
    .bad-score { color: #e74a3b; font-weight: bold; } /* Điểm thấp chữ đỏ */
    .good-score { color: #1cc88a; }
</style>

<table>
    <tr>
        <td colspan="6" class="header-title">BẢNG THỐNG KÊ ĐIỂM SỐ VÀ CHUYÊN CẦN CHI TIẾT</td>
    </tr>
    <tr>
        <td colspan="6" style="text-align: center;">Ngày xuất: <?= date('d/m/Y H:i') ?></td>
    </tr>
    <tr><td></td></tr>
</table>

<table class="table-style" border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Họ và Tên</th>
            <th>Lớp Học</th>
            <th>Tên Môn Học</th>
            <th>Điểm Số</th>
            <th>Số Buổi Có Mặt</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($res && mysqli_num_rows($res) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td>#<?php echo $row['student_id']; ?></td>
                    <td style="text-align: left;"><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['class_name'] ?? 'N/A'); ?></td>
                    <td style="text-align: left;"><?php echo htmlspecialchars($row['course_name']); ?></td>
                    
                    <td class="<?php echo ($row['score'] < 5) ? 'bad-score' : 'good-score'; ?>">
                        <?php echo number_format((float)($row['score'] ?? 0), 2); ?>
                    </td>
                    
                    <td>
                        <?php echo $row['total_present']; ?> buổi
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="padding: 20px;">Không tìm thấy dữ liệu điểm số trong hệ thống.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>