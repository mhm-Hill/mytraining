<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    die("❌ ليس لديك صلاحية للوصول إلى هذه الصفحة.");
}

$student_id = $_SESSION['user_id'];

// جلب تقارير الشهرية الخاصة بالطالب
$stmt = $conn->prepare("
    SELECT r.Month, r.Work_Days, r.What_Student_Learned, r.Evaluation_Score, r.Comment, r.Date,
           u.Name AS SupervisorName
    FROM monthlyreport r
    JOIN users u ON r.Comp_Supervisor_ID = u.User_ID
    WHERE r.Student_ID = ?
    ORDER BY r.Date DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>التقرير الشهري</title>
<style>
body { font-family: 'Cairo', sans-serif; background:#f7f9fc; margin:0; padding:0; }
.container { width:90%; max-width:1000px; margin:30px auto; padding:20px; background:#fff; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
h2 { text-align:center; color:#333; margin-bottom:20px; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:10px; text-align:center; }
th { background:#28a745; color:white; }
tr:nth-child(even) { background:#f2f2f2; }
a { color:#007bff; text-decoration:none; margin-top:20px; display:inline-block; }
a:hover { text-decoration:underline; }
.message { text-align:center; margin-top:20px; font-weight:bold; color:#555; }
</style>
</head>
<body>
<div class="container">
    <h2>📄 التقارير الشهرية الخاصة بك</h2>

    <?php if($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>المشرف</th>
            <th>الشهر</th>
            <th>أيام العمل</th>
            <th>ما تعلمته</th>
            <th>تقييم الأداء</th>
            <th>تعليق المشرف</th>
            <th>تاريخ التقرير</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['SupervisorName']) ?></td>
            <td><?= htmlspecialchars($row['Month']) ?></td>
            <td><?= htmlspecialchars($row['Work_Days']) ?></td>
            <td><?= htmlspecialchars($row['What_Student_Learned']) ?></td>
            <td><?= htmlspecialchars($row['Evaluation_Score']) ?></td>
            <td><?= htmlspecialchars($row['Comment']) ?: '-' ?></td>
            <td><?= htmlspecialchars($row['Date']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p class="message">لا توجد تقارير شهرية حتى الآن.</p>
    <?php endif; ?>

    <a href="student_dashboard.php">⬅ العودة للوحة التحكم</a>
</div>
</body>
</html>
