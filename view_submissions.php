<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'CompanySupervisor'){
    header("Location: login.php");
    exit;
}

$comp_supervisor_id = $_SESSION['user_id'];

// تحديث التعليق والدرجة إذا تم إدخاله
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submission_id'])){
    $submission_id = $_POST['submission_id'];
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    $grade = isset($_POST['grade']) ? intval($_POST['grade']) : null;

    $update = $conn->prepare("
        UPDATE assignment_submissions 
        SET Comment = ?, Grade = ?
        WHERE Submission_ID = ?
    ");
    $update->bind_param("sii", $comment, $grade, $submission_id);
    $update->execute();
}

// عرض الحلول
$query = $conn->prepare("
    SELECT s.Submission_ID, s.File_Path, s.Submission_Date, s.Comment, s.Grade, 
           a.Title AS AssignmentTitle, u.Name AS StudentName
    FROM assignment_submissions s
    JOIN assignments a ON s.Assignment_ID = a.Assignment_ID
    JOIN users u ON s.Student_ID = u.User_ID
    WHERE a.Comp_Supervisor_ID = ?
    ORDER BY s.Submission_Date DESC
");
$query->bind_param("i", $comp_supervisor_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>حلول الطلاب</title>
<link rel="stylesheet" href="css/dashboard.css">
<style>
.container { width:90%; margin:auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
h1, h2 { text-align:center; color:#333; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:10px; text-align:center; vertical-align:top; }
th { background:#28a745; color:white; }
textarea { width:90%; padding:6px; border:1px solid #ccc; border-radius:4px; }
input[type=number] { width:60px; padding:4px; border:1px solid #ccc; border-radius:4px; }
button { padding:6px 12px; background:#17a2b8; color:white; border:none; border-radius:5px; cursor:pointer; transition:0.3s; }
button:hover { background:#138496; }
a { color:#007bff; text-decoration:none; }
a:hover { text-decoration:underline; }
form { display:flex; flex-direction:column; align-items:center; }
.message { text-align:center; margin:10px 0; font-weight:bold; color:green; }
</style>
</head>
<body>

<div class="container">
<h1>حلول الواجبات المقدمة من الطلاب</h1>

<table>
<tr>
<th>الطالب</th>
<th>الواجب</th>
<th>الملف</th>
<th>تاريخ التسليم</th>
<th>التعليق & التقييم</th>
<th>الدرجة</th>
</tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['StudentName']) ?></td>
<td><?= htmlspecialchars($row['AssignmentTitle']) ?></td>
<td>
    <?php if($row['File_Path']): ?>
    <a href="<?= $row['File_Path'] ?>" target="_blank">📂 عرض الملف</a>
    <?php else: ?>
    -
    <?php endif; ?>
</td>
<td><?= $row['Submission_Date'] ?></td>
<td>
    <form method="POST">
        <input type="hidden" name="submission_id" value="<?= $row['Submission_ID'] ?>">
        <textarea name="comment" placeholder="اكتب تعليقك هنا..."><?= htmlspecialchars($row['Comment']) ?></textarea>
        <br>
        <label>الدرجة:</label>
        <input type="number" name="grade" min="0" max="100" value="<?= $row['Grade'] ?>" placeholder="ضع الدرجة">
        <br>
        <button type="submit">💬 حفظ</button>
    </form>
</td>
<td><?= $row['Grade'] !== null ? $row['Grade'] : '-' ?></td>
</tr>
<?php endwhile; ?>
</table>

<a href="company_dashboard.php">⬅ العودة للوحة التحكم</a>
</div>

</body>
</html>
