<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'UniversitySupervisor'){
    header("Location: login.php");
    exit;
}

$uni_supervisor_id = $_SESSION['user_id'];

// جلب رقم الجامعة الخاصة بمشرف الجامعة
$stmt = $conn->prepare("SELECT University_ID FROM university_supervisor_details WHERE Uni_Supervisor_ID = ?");
$stmt->bind_param("i", $uni_supervisor_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$university_id = $row['University_ID'] ?? 0;

// جلب التقارير النهائية للطلاب من نفس الجامعة فقط
$query = $conn->prepare("
    SELECT 
        u.Name AS StudentName,
        uni.University_Name AS UniversityName,
        sd.Major AS Major,
        f.Summary AS SummaryText,
        f.Performance_Score AS Score,
        f.Date AS ReportDate,
        s.Name AS SupervisorName
    FROM finalreport f
    JOIN users u ON f.Student_ID = u.User_ID
    LEFT JOIN student_details sd ON u.User_ID = sd.Student_ID
    LEFT JOIN universities uni ON sd.University_ID = uni.University_ID
    JOIN users s ON f.Comp_Supervisor_ID = s.User_ID
    WHERE sd.University_ID = ?
    ORDER BY f.Date DESC
");
$query->bind_param("i", $university_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>التقارير النهائية لطلاب جامعتك</title>
<style>
body {
    font-family: 'Cairo', sans-serif;
    background-color: #f7f9fc;
    margin: 0;
    padding: 0;
}
.container {
    width: 95%;
    max-width: 1200px;
    margin: 30px auto;
    background: #fff;
    padding: 25px 30px;
    border-radius: 8px;
    box-shadow: 0 0 12px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
    vertical-align: middle;
}
th {
    background-color: #f0f0f0;
    color: #333;
}
tr:nth-child(even) {
    background-color: #fafafa;
}
.back-link {
    display: inline-block;
    margin-top: 20px;
    text-decoration: none;
    color: #555;
}
.back-link:hover {
    color: #000;
}
</style>
</head>
<body>
<div class="container">
    <h2>📑 التقارير النهائية لطلاب جامعتك</h2>

    <?php if($result->num_rows == 0): ?>
        <p>لا توجد تقارير نهائية لطلاب جامعتك بعد.</p>
    <?php else: ?>
    <table>
        <tr>
            <th>اسم الطالب</th>
            <th>الجامعة</th>
            <th>التخصص</th>
            <th>ملخص التقرير</th>
            <th>تقييم الأداء</th>
            <th>تاريخ التقرير</th>
            <th>المشرف</th>
        </tr>

        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['StudentName']); ?></td>
            <td><?= htmlspecialchars($row['UniversityName'] ?: '-'); ?></td>
            <td><?= htmlspecialchars($row['Major'] ?: '-'); ?></td>
            <td><?= htmlspecialchars($row['SummaryText']); ?></td>
            <td><?= $row['Score'] !== null ? $row['Score'] : '-'; ?></td>
            <td><?= htmlspecialchars($row['ReportDate']); ?></td>
            <td><?= htmlspecialchars($row['SupervisorName']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>

    <a class="back-link" href="university_dashboard.php">⬅ العودة للوحة التحكم</a>
</div>
</body>
</html>
