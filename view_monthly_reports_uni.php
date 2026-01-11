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
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$university_id = $row['University_ID'] ?? 0;

// جلب التقارير الشهرية للطلاب من نفس الجامعة فقط
$query = $conn->prepare("
    SELECT m.Month, m.Work_Days, m.What_Student_Learned, m.Evaluation_Score, m.Comment, 
           u.Name AS StudentName, s.Name AS SupervisorName
    FROM monthlyreport m
    JOIN student_details sd ON m.Student_ID = sd.Student_ID
    JOIN users u ON m.Student_ID = u.User_ID
    JOIN users s ON m.Comp_Supervisor_ID = s.User_ID
    WHERE sd.University_ID = ?
    ORDER BY m.Date DESC
");
$query->bind_param("i", $university_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>التقارير الشهرية لطلاب جامعتك</title>
<style>
body {
    font-family: 'Cairo', sans-serif;
    background-color: #f7f9fc;
    margin: 0;
    padding: 0;
}
.container {
    width: 90%;
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
table th, table td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}
table th {
    background-color: #f0f0f0;
    color: #333;
}
table tr:nth-child(even) {
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
    <h2>🗒️ التقارير الشهرية لطلاب جامعتك</h2>

    <?php if($result->num_rows == 0): ?>
        <p>لا توجد تقارير شهرية لطلاب جامعتك بعد.</p>
    <?php else: ?>
    <table>
        <tr>
            <th>اسم الطالب</th>
            <th>الشهر</th>
            <th>أيام العمل</th>
            <th>ما تعلمه الطالب</th>
            <th>التقييم</th>
            <th>التعليق</th>
            <th>المشرف</th>
        </tr>

        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['StudentName']); ?></td>
            <td><?= htmlspecialchars($row['Month']); ?></td>
            <td><?= htmlspecialchars($row['Work_Days']); ?></td>
            <td><?= htmlspecialchars($row['What_Student_Learned']); ?></td>
            <td><?= $row['Evaluation_Score'] !== null ? $row['Evaluation_Score'] : '-'; ?></td>
            <td><?= $row['Comment'] ?: '-'; ?></td>
            <td><?= htmlspecialchars($row['SupervisorName']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>

    <a class="back-link" href="university_dashboard.php">⬅ العودة للوحة التحكم</a>
</div>
</body>
</html>
