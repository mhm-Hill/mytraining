<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    header("Location: login.php");
    exit;
}

// جلب اسم المستخدم من session أو من قاعدة البيانات إذا غير موجود
if(isset($_SESSION['name'])){
    $name = $_SESSION['name'];
} else {
    $student_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT Name FROM users WHERE User_ID = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $name = $row['Name'];

    // حفظ الاسم في session لتجنب جلبه كل مرة
    $_SESSION['name'] = $name;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>لوحة الطالب</title>
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<div class="container">
    <h1>مرحباً، <?= htmlspecialchars($name) ?> 👋</h1>
    <a href="login.php" class="logout">تسجيل الخروج</a>

    <div class="actions">
        <a href="view_assignments_student.php">📄 عرض الواجبات وتسليمها</a>
        <a href="view_monthly_report.php">🗒️ عرض التقرير الشهري</a>
    </div>
</div>

</body>
</html>
