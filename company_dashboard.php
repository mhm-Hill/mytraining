<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'CompanySupervisor'){
    header("Location: login.php");
    exit;
}

$supervisor_id = $_SESSION['user_id'];

// بيانات المشرف والشركة
$stmt = $conn->prepare("
    SELECT u.Name, c.Company_Name, c.Position 
    FROM company_supervisor_details c 
    JOIN users u ON c.Comp_Supervisor_ID=u.User_ID 
    WHERE c.Comp_Supervisor_ID=?
");
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$supervisor = $stmt->get_result()->fetch_assoc();

// جلب الواجبات التي أنشأها
$stmt2 = $conn->prepare("
    SELECT a.Assignment_ID, a.Title, a.Description, a.Week_Number, a.Date_Posted
    FROM assignments a
    WHERE a.Comp_Supervisor_ID = ?
    ORDER BY a.Date_Posted DESC
");
$stmt2->bind_param("i", $supervisor_id);
$stmt2->execute();
$assignments = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>لوحة تحكم مشرف الشركة</title>
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<div class="container">
    <h1>مرحباً، <?= htmlspecialchars($supervisor['Name']) ?></h1>
    <a href="login.php" class="logout">تسجيل الخروج</a>

    <h2>بيانات الشركة</h2>
    <p><strong>اسم الشركة:</strong> <?= htmlspecialchars($supervisor['Company_Name']) ?></p>
    <p><strong>الوظيفة:</strong> <?= htmlspecialchars($supervisor['Position']) ?></p>

    <div class="actions">
        <a href="add_assignment.php">إضافة واجب جديد</a>
        <a href="add_monthly_report.php">إضافة تقرير شهري</a>
        <a href="add_final_report.php">إضافة التقرير النهائي</a>
    </div>

    <h2>الواجبات المنشورة</h2>
    <table>
        <tr>
            <th>عنوان الواجب</th>
            <th>الوصف</th>
            <th>الأسبوع</th>
            <th>تاريخ النشر</th>
            <th>إجراءات</th>
        </tr>
        <?php while($row = $assignments->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['Title']) ?></td>
            <td><?= htmlspecialchars($row['Description']) ?></td>
            <td><?= $row['Week_Number'] ?></td>
            <td><?= $row['Date_Posted'] ?></td>
            <td class="table-action">
                <a href="view_submissions.php?assignment_id=<?= $row['Assignment_ID'] ?>">عرض الحلول</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
