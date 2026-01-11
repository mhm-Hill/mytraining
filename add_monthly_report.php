<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'CompanySupervisor'){
    header("Location: login.php");
    exit;
}

$comp_supervisor_id = $_SESSION['user_id'];
$message = "";

// عند الإرسال
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $student_id = $_POST['student_id'];
    $month = $_POST['month'];
    $work_days = intval($_POST['work_days']);
    $learned = $_POST['learned'];
    $score = intval($_POST['score']);
    $comment = $_POST['comment'];
    $date = date("Y-m-d");

    $stmt = $conn->prepare("
        INSERT INTO monthlyreport 
        (Student_ID, Comp_Supervisor_ID, Month, Work_Days, What_Student_Learned, Evaluation_Score, Comment, Date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisissss", $student_id, $comp_supervisor_id, $month, $work_days, $learned, $score, $comment, $date);

    if($stmt->execute()){
        $message = "✅ تم حفظ التقرير الشهري بنجاح!";
    } else {
        $message = "❌ حدث خطأ أثناء الحفظ: " . $stmt->error;
    }
}

// جلب الطلاب المرتبطين بهذا المشرف
$students = $conn->query("SELECT User_ID, Name FROM users WHERE Role = 'Student'");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إنشاء تقرير شهري</title>
<style>
body { font-family:'Cairo',sans-serif; background:#f7f9fc; }
.container { width:50%; margin:auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
h1 { text-align:center; margin-bottom:20px; }
form { display:flex; flex-direction:column; }
label { margin-top:10px; font-weight:bold; }
input, select, textarea { padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:6px; width:100%; }
textarea { resize: vertical; height:100px; }
button { margin-top:20px; padding:12px; background:#28a745; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition:0.3s; }
button:hover { background:#218838; }
.message { text-align:center; margin-top:15px; font-weight:bold; color:green; }
a { display:inline-block; margin-top:15px; text-decoration:none; color:#007bff; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="container">
    <h1>إنشاء تقرير شهري</h1>

    <?php if($message) echo "<p class='message'>$message</p>"; ?>

    <form method="POST">
        <label>اختر الطالب:</label>
        <select name="student_id" required>
            <option value="">-- اختر الطالب --</option>
            <?php while($s = $students->fetch_assoc()): ?>
                <option value="<?= $s['User_ID']; ?>"><?= htmlspecialchars($s['Name']); ?></option>
            <?php endwhile; ?>
        </select>

        <label>الشهر:</label>
        <input type="text" name="month" placeholder="مثال: نوفمبر 2025" required>

        <label>أيام العمل:</label>
        <input type="number" name="work_days" min="0" placeholder="عدد أيام العمل" required>

        <label>ما تعلمه الطالب:</label>
        <textarea name="learned" placeholder="صف ما تعلمه الطالب" required></textarea>

        <label>تقييم الطالب:</label>
        <input type="number" name="score" min="0" max="100" placeholder="ضع تقييم بين 0 و 100" required>

        <label>تعليق المشرف:</label>
        <textarea name="comment" placeholder="يمكنك إضافة تعليق"></textarea>

        <button type="submit">حفظ التقرير</button>
    </form>

    <a href="company_dashboard.php">⬅ العودة للوحة التحكم</a>
</div>

</body>
</html>
