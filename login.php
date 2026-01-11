<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['Password'])) {
            // تأكد من تجديد الجلسة لمنع أي لبس
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['name'] = $user['Name']; // اختياري للاستخدام في الترحيب بالمستخدم

            // إعادة التوجيه حسب الدور
            switch ($user['Role']) {
                case 'Student':
                    header("Location: student_dashboard.php");
                    break;
                case 'CompanySupervisor':
                    header("Location: company_dashboard.php");
                    break;
                case 'UniversitySupervisor':
                    header("Location: university_dashboard.php");
                    break;
                default:
                    $error = "دور المستخدم غير معروف.";
                    break;
            }
            exit;
        } else {
            $error = "كلمة المرور غير صحيحة.";
        }
    } else {
        $error = "البريد الإلكتروني غير موجود.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تسجيل الدخول</title>
<link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="login-container">
    <h2>تسجيل الدخول</h2>
    <form method="POST">
        <label>البريد الإلكتروني:</label>
        <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني" required>

        <label>كلمة المرور:</label>
        <input type="password" name="password" placeholder="أدخل كلمة المرور" required>

        <button type="submit">دخول</button>

        <p style="color:red;"><?php echo $error; ?></p>
    </form>
    <p class="register-link">ليس لديك حساب؟ <a href="register.php">سجل هنا</a></p>
</div>
</body>
</html>
