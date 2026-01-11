<?php
$servername = "localhost";
$username = "root"; // أو اسم المستخدم الخاص بك
$password = "";     // كلمة المرور الخاصة بك
$dbname = "mytraining";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
