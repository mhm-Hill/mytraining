<?php
session_start();
include 'db_connect.php';

// التحقق من صلاحية المستخدم
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'CompanySupervisor'){
    header("Location: login.php");
    exit;
}

$comp_supervisor_id = $_SESSION['user_id'];
$message = "";

// حفظ التقرير النهائي عند الإرسال
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $student_id = $_POST['student_id'];
    $summary = $_POST['summary'];
    $score = isset($_POST['score']) ? intval($_POST['score']) : null;
    $date = date("Y-m-d");

    $stmt = $conn->prepare("
        INSERT INTO finalreport (Student_ID, Comp_Supervisor_ID, Summary, Performance_Score, Date)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisis", $student_id, $comp_supervisor_id, $summary, $score, $date);

    if($stmt->execute()){
        $message = "✅ تم إنشاء التقرير النهائي بنجاح!";
    } else {
        $message = "❌ حدث خطأ أثناء الحفظ: " . $stmt->error;
    }
}

// جلب الطلاب
$students = $conn->query("SELECT User_ID, Name FROM users WHERE Role = 'Student'");

// جلب التقارير المسجلة
$reports = $conn->query("
    SELECT f.FinalReport_ID, u.Name AS StudentName, f.Summary, f.Performance_Score, f.Date
    FROM finalreport f
    JOIN users u ON f.Student_ID = u.User_ID
    ORDER BY f.Date DESC
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>التقرير النهائي</title>
<link rel="stylesheet" href="css/dashboard.css">
<style>
/* تحسين مظهر الفورم والجداول */
form { display:flex; flex-direction:column; margin-bottom:20px; }
label { margin-top:10px; font-weight:bold; }
input, select, textarea { padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:6px; width:100%; }
textarea { resize: vertical; height:100px; }
button { margin-top:15px; padding:12px; background:#28a745; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition:0.3s; }
button:hover { background:#218838; }
.message { text-align:center; margin-top:15px; font-weight:bold; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#28a745; color:white; }
a { display:inline-block; margin-top:15px; text-decoration:none; color:#007bff; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="container">
    <h1 style="text-align:center;">إعداد تقرير نهائي</h1>

    <?php if($message) echo "<p class='message'>$message</p>"; ?>

    <form method="POST">
        <label>اختر الطالب:</label>
        <select name="student_id" required>
            <option value="">-- اختر الطالب --</option>
            <?php while($s = $students->fetch_assoc()): ?>
                <option value="<?= $s['User_ID']; ?>"><?= htmlspecialchars($s['Name']); ?></option>
            <?php endwhile; ?>
        </select>

        <label>مُلخّص التقرير:</label>
        <textarea name="summary" required></textarea>

        <label>تقييم الأداء (0-100):</label>
        <input type="number" name="score" min="0" max="100" placeholder="ضع تقييم بين 0 و 100" required>

        <button type="submit">حفظ التقرير النهائي</button>
    </form>

    <h3>التقارير المسجلة:</h3>
    <table>
        <tr>
            <th>رقم التقرير</th>
            <th>اسم الطالب</th>
            <th>مُلخّص التقرير</th>
            <th>تقييم الأداء</th>
            <th>التاريخ</th>
        </tr>
        <?php while($r = $reports->fetch_assoc()): ?>
        <tr>
            <td><?= $r['FinalReport_ID']; ?></td>
            <td><?= htmlspecialchars($r['StudentName']); ?></td>
            <td><?= htmlspecialchars($r['Summary']); ?></td>
            <td><?= $r['Performance_Score'] !== null ? $r['Performance_Score'] : '-'; ?></td>
            <td><?= $r['Date']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <a href="company_dashboard.php">⬅ العودة للوحة التحكم</a>
</div>

</body>
</html>
