<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// جلب كل الواجبات مع الحل المرفوع وتعليقات مشرف الشركة
$query = $conn->prepare("
    SELECT a.Assignment_ID, a.Title, a.Description, a.Date_Posted, 
           s.Submission_ID, s.File_Path AS SubmittedFile, s.Submission_Date, s.Comment AS CompanyComment, s.Grade AS CompanyGrade
    FROM assignments a
    LEFT JOIN assignment_submissions s 
        ON a.Assignment_ID = s.Assignment_ID AND s.Student_ID = ?
    ORDER BY a.Date_Posted DESC
");
$query->bind_param("i", $student_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الواجبات الموكلة إلي</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:0; }
.container { width:90%; margin:30px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
h2 { text-align:center; color:#333; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:10px; text-align:center; vertical-align:middle; }
th { background:#28a745; color:white; }
a { color:#007bff; text-decoration:none; }
a:hover { text-decoration:underline; }
button, .upload-link { padding:6px 12px; background:#17a2b8; color:white; border:none; border-radius:5px; cursor:pointer; transition:0.3s; text-decoration:none; display:inline-block; }
button:hover, .upload-link:hover { background:#138496; }
</style>
</head>
<body>

<div class="container">
<h2>📄 الواجبات الموكلة إلي</h2>

<table>
<tr>
    <th>عنوان الواجب</th>
    <th>الوصف</th>
    <th>تاريخ النشر</th>
    <th>الحل المرفوع</th>
    <th>تعليق مشرف الشركة</th>
    <th>تقييم مشرف الشركة</th>
    <th>تعليق مشرف الجامعة</th>
    <th>رفع الحل</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<?php
    // جلب تعليق مشرف الجامعة إذا وجد لهذا الطالب وحل هذا الواجب
    $uniComment = '-';
    if($row['Submission_ID']) {
        $stmt = $conn->prepare("SELECT Comment FROM university_supervisor_comments WHERE Submission_ID = ? ORDER BY Comment_ID DESC LIMIT 1");
        $stmt->bind_param("i", $row['Submission_ID']);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows > 0){
            $c = $res->fetch_assoc();
            $uniComment = $c['Comment'];
        }
    }
?>
<tr>
<td><?= htmlspecialchars($row['Title']) ?></td>
<td><?= htmlspecialchars($row['Description']) ?></td>
<td><?= $row['Date_Posted'] ?></td>
<td>
    <?php if($row['SubmittedFile']): ?>
        <a href="<?= $row['SubmittedFile'] ?>" target="_blank">📂 عرض الملف</a>
    <?php else: ?>
        لا يوجد حل
    <?php endif; ?>
</td>
<td><?= htmlspecialchars($row['CompanyComment'] ?: '-') ?></td>
<td><?= $row['CompanyGrade'] !== null ? $row['CompanyGrade'] : '-' ?></td>
<td><?= htmlspecialchars($uniComment) ?></td>
<td>
    <a class="upload-link" href="submit_assignment.php?id=<?= $row['Assignment_ID'] ?>">⬆ رفع حل</a>
</td>
</tr>
<?php endwhile; ?>

</table>

<a href="student_dashboard.php">⬅ العودة للوحة التحكم</a>
</div>

</body>
</html>
