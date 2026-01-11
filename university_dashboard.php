<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'UniversitySupervisor'){
    header("Location: login.php");
    exit;
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>لوحة مشرف الجامعة</title>
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<div class="container">
    <h1>مرحباً، <?= htmlspecialchars($name) ?> 👋</h1>
    <a href="login.php" class="logout">تسجيل الخروج</a>

    <div class="actions">
        <a href="view_assignments_uni.php">📄 عرض الواجبات وحلول الطلاب</a>
        <a href="view_monthly_reports_uni.php">🗒️ عرض التقارير الشهرية</a>
        <a href="view_final_reports_uni.php">📑 عرض التقرير النهائي</a>
    </div>
</div>

</body>
</html>
