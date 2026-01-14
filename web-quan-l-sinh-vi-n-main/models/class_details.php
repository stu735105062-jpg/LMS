<?php
// 1. KẾT NỐI DB
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
mysqli_set_charset($conn, "utf8mb4");

// 2. TRUY VẤN TẤT CẢ DỮ LIỆU LIÊN QUAN
// Truy vấn lấy thông tin sinh viên, lớp gốc của họ, môn học họ đăng ký, điểm và điểm danh hôm nay
$today = date('Y-m-d');
$sql = "SELECT 
            c.class_name as original_class, 
            co.course_name, 
            si.id as student_id, 
            si.name as student_name, 
            si.gender, 
            g.score, 
            att.status as attendance_status
        FROM student_info si
        LEFT JOIN classes c ON si.class_id = c.class_id
        LEFT JOIN course_registrations cr ON si.id = cr.student_id
        LEFT JOIN courses co ON cr.course_id = co.course_id
        LEFT JOIN grades g ON (si.id = g.student_id AND co.course_id = g.course_id)
        LEFT JOIN attendance att ON (si.id = att.student_id AND att.date = '$today')
        ORDER BY c.class_name ASC, co.course_name ASC, si.name ASC";

$result = mysqli_query($conn, $sql);

// 3. GOM NHÓM DỮ LIỆU
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $className = $row['original_class'] ?: 'Chưa phân lớp';
    $courseName = $row['course_name'] ?: 'Chưa đăng ký môn';
    $data[$className][$courseName][] = $row;
}

// 4. THIẾT LẬP FILE EXCEL
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Bao_Cao_Tong_Hop_" . date('Ymd_His') . ".xls");
echo "\xEF\xBB\xBF"; // UTF-8 BOM để hiển thị đúng tiếng Việt
?>

<style>
    .class-row { background-color: #4e73df; color: white; font-weight: bold; font-size: 14px; }
    .course-row { background-color: #f8f9fc; color: #2e59d9; font-weight: bold; font-style: italic; }
    th { background-color: #eaecf4; border: 1px solid #000; }
    td { border: 1px solid #000; }
</style>

<table border="1">
    <thead>
        <tr>
            <th colspan="5" style="font-size: 18px; padding: 10px;">BÁO CÁO TỔNG HỢP SINH VIÊN & ĐIỂM SỐ (<?php echo $today; ?>)</th>
        </tr>
        <tr>
            <th>Mã SV</th>
            <th>Họ Tên</th>
            <th>Giới Tính</th>
            <th>Điểm Số</th>
            <th>Trạng Thái</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($data)): ?>
            <tr><td colspan="5" align="center">Hệ thống chưa có dữ liệu sinh viên.</td></tr>
        <?php else: ?>
            <?php foreach ($data as $className => $courses): ?>
                <tr class="class-row">
                    <td colspan="5">LỚP: <?php echo htmlspecialchars($className); ?></td>
                </tr>

                <?php foreach ($courses as $courseName => $students): ?>
                    <tr class="course-row">
                        <td colspan="5">&nbsp;&nbsp;&nbsp;Môn học: <?php echo htmlspecialchars($courseName); ?></td>
                    </tr>

                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td align="center">#<?php echo $s['student_id']; ?></td>
                            <td><?php echo htmlspecialchars($s['student_name']); ?></td>
                            <td align="center"><?php echo $s['gender']; ?></td>
                            <td align="center">
                                <?php echo ($s['score'] !== null) ? $s['score'] : 'N/A'; ?>
                            </td>
                            <td align="center">
                                <?php echo ($s['attendance_status'] == 'present' ? 'Có mặt' : ($s['attendance_status'] == 'absent' ? 'Vắng' : '-')); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>