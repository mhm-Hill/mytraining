<?php
session_start();
include 'db_connect.php';

// التحقق من تسجيل الدخول وصلاحية مشرف الجامعة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'UniversitySupervisor') {
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

// جلب الواجبات + التسليمات للطلاب من نفس الجامعة فقط
$stmt = $conn->prepare("
    SELECT 
        a.Assignment_ID,
        a.Title,
        a.Description,
        a.Week_Number,
        a.Date_Posted,
        s.Submission_ID,
        s.File_Path,
        s.Submission_Date,
        s.Comment AS Student_Comment,
        s.Grade,
        st.Student_ID,
        u.Name AS Student_Name
    FROM assignments a
    JOIN assignment_submissions s ON a.Assignment_ID = s.Assignment_ID
    JOIN student_details st ON s.Student_ID = st.Student_ID
    JOIN users u ON st.Student_ID = u.User_ID
    WHERE st.University_ID = ?
    ORDER BY a.Date_Posted DESC
");
$stmt->bind_param("i", $university_id);
$stmt->execute();
$assignments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>عرض الواجبات وحلول الطلاب</title>
<link rel="stylesheet" href="css/dashboard.css">
<style>
.container { width:90%; margin:auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
h2 { text-align:center; color:#333; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:10px; text-align:center; vertical-align:middle; }
th { background:#28a745; color:white; }
a { color:#007bff; text-decoration:none; }
a:hover { text-decoration:underline; }
button, .btn { padding:6px 12px; background:#17a2b8; color:white; border:none; border-radius:5px; cursor:pointer; transition:0.3s; }
button:hover, .btn:hover { background:#138496; }
textarea { width:100%; height:60px; }
</style>
</head>
<body>

<div class="container">
<h2>📄 الواجبات وحلول الطلاب من جامعتك</h2>

<?php if($assignments->num_rows == 0): ?>
    <p>لا يوجد تسليمات لطلاب جامعتك بعد.</p>
<?php else: ?>
<table>
<tr>
<th>اسم الطالب</th>
<th>عنوان الواجب</th>
<th>الوصف</th>
<th>ملف الحل</th>
<th>تاريخ التسليم</th>
<th>تعليق الشركة</th>
<th>علامة الشركة</th>
<th>تعليق المشرف الجامعي</th>
<th>إضافة/تعديل تعليق</th>
</tr>

<?php while($row = $assignments->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['Student_Name']) ?></td>
<td><?= htmlspecialchars($row['Title']) ?></td>
<td><?= htmlspecialchars($row['Description']) ?></td>
<td>
    <?php if($row['File_Path']): ?>
        <a href="<?= $row['File_Path'] ?>" target="_blank">📂 عرض الملف</a>
    <?php else: ?>
        لا يوجد ملف
    <?php endif; ?>
</td>
<td><?= $row['Submission_Date'] ?></td>
<td><?= $row['Student_Comment'] ?: '-' ?></td>
<td><?= $row['Grade'] ?: '-' ?></td>
<td>
    <?php
        $cstmt = $conn->prepare("SELECT Comment FROM university_supervisor_comments WHERE Submission_ID = ? AND Uni_Supervisor_ID = ?");
        $cstmt->bind_param("ii", $row['Submission_ID'], $uni_supervisor_id);
        $cstmt->execute();
        $cresult = $cstmt->get_result();
        echo $cresult->num_rows > 0 ? htmlspecialchars($cresult->fetch_assoc()['Comment']) : '-';
    ?>
</td>
<td>
    <form method="POST" action="submit_comment.php">
        <input type="hidden" name="submission_id" value="<?= $row['Submission_ID'] ?>">
        <textarea name="comment" required></textarea><br>
        <button type="submit" class="btn">✏️ إرسال التعليق</button>
    </form>
</td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

<a href="university_dashboard.php">⬅ العودة للوحة التحكم</a>
</div>

</body>
</html>
