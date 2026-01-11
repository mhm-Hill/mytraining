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
$message = "";

if(isset($_POST['submit'])){
    $title = $_POST['title'];
    $description = $_POST['description'];
    $week = $_POST['week'];
    $date_posted = date('Y-m-d');

    $file_path = NULL;

    // تجهيز مجلد الرفع
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // رفع الملف إذا موجود
    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){
        $file_name = time() . "_" . basename($_FILES["file"]["name"]);
        $target_file = $upload_dir . $file_name;

        if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)){
            $file_path = $target_file;
        } else {
            $message = "⚠️ فشل رفع الملف.";
        }
    }

    // إدخال البيانات في قاعدة البيانات
    $stmt = $conn->prepare("
        INSERT INTO assignments (Comp_Supervisor_ID, Title, Description, Week_Number, File_Path, Date_Posted)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ississ", $supervisor_id, $title, $description, $week, $file_path, $date_posted);

    if($stmt->execute()){
        $message = "✔ تم إضافة الواجب بنجاح!";
    } else {
        $message = "❌ حدث خطأ أثناء الحفظ.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إضافة واجب جديد</title>
<link rel="stylesheet" href="css/dashboard.css">
<style>
/* تحسين مظهر الفورم بشكل خاص */
form { display:flex; flex-direction:column; }
label { margin-top:10px; font-weight:bold; }
input, textarea, select { padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:6px; }
button { margin-top:20px; padding:12px; background:#28a745; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition:0.3s; }
button:hover { background:#218838; }
.message { text-align:center; margin-top:15px; font-weight:bold; }
</style>
</head>
<body>

<div class="container">
    <h1 style="text-align:center;">إضافة واجب جديد</h1>

    <?php if($message) echo "<p class='message'>$message</p>"; ?>

    <form method="post" enctype="multipart/form-data">
        <label>عنوان الواجب:</label>
        <input type="text" name="title" required>

        <label>الوصف:</label>
        <textarea name="description" rows="5" required></textarea>

        <label>رقم الأسبوع:</label>
        <input type="number" name="week" required>

        <label>رفع الملف (اختياري):</label>
        <input type="file" name="file">

        <button type="submit" name="submit">إضافة الواجب</button>
    </form>
</div>

</body>
</html>
