<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    die("❌ الوصول مرفوض");
}

$student_id = $_SESSION['user_id'];

if(!isset($_GET['id'])){
    die("❌ خطأ: رقم الواجب غير موجود.");
}

$assignment_id = intval($_GET['id']);
$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['solution'])){
    $file = $_FILES['solution'];
    $target_dir = "uploads/";

    if(!is_dir($target_dir)){
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($file['name']);
    $file_path = $target_dir . $file_name;

    if(move_uploaded_file($file['tmp_name'], $file_path)){
        $check = $conn->prepare("
            SELECT Submission_ID 
            FROM assignment_submissions 
            WHERE Assignment_ID = ? AND Student_ID = ?
        ");
        $check->bind_param("ii", $assignment_id, $student_id);
        $check->execute();
        $res = $check->get_result();

        $today = date("Y-m-d");

        if($res->num_rows > 0){
            $row = $res->fetch_assoc();
            $update = $conn->prepare("
                UPDATE assignment_submissions 
                SET File_Path = ?, Submission_Date = ?
                WHERE Submission_ID = ?
            ");
            $update->bind_param("ssi", $file_path, $today, $row['Submission_ID']);
            $update->execute();
        } else {
            $insert = $conn->prepare("
                INSERT INTO assignment_submissions 
                (Assignment_ID, Student_ID, File_Path, Submission_Date)
                VALUES (?, ?, ?, ?)
            ");
            $insert->bind_param("iiss", $assignment_id, $student_id, $file_path, $today);
            $insert->execute();
        }

        $message = "✅ تم رفع الحل بنجاح!";
    } else {
        $message = "❌ فشل رفع الملف.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>رفع حل الواجب</title>
<link rel="stylesheet" href="css/dashboard.css">
<style>
.container { width:50%; margin:auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); text-align:center; }
h2 { color:#333; margin-bottom:20px; }
input[type="file"] { margin-top:10px; padding:6px; width:100%; }
button { margin-top:15px; padding:10px 15px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer; transition:0.3s; }
button:hover { background:#218838; }
.message { margin-top:15px; font-weight:bold; color:green; }
a { display:inline-block; margin-top:20px; color:#007bff; text-decoration:none; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="container">
<h2>رفع حل الواجب</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="solution" required>
    <button type="submit">رفع الحل</button>
</form>

<p class="message"><?php echo $message; ?></p>
<a href="view_assignments_student.php">⬅ العودة للواجبات</a>
</div>

</body>
</html>
