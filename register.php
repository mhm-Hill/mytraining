<?php
session_start();
include 'db_connect.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // التحقق من وجود البريد مسبقًا
    $check_email = $conn->prepare("SELECT User_ID FROM users WHERE Email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $res_email = $check_email->get_result();
    if($res_email->num_rows > 0){
        $error = "البريد الإلكتروني مستخدم بالفعل.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (Name, Email, Password, Role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            // إذا الطالب أو مشرف الجامعة يحتاجون تحديد الجامعة
            if ($role == "Student" || $role == "UniversitySupervisor") {
                $university_name = trim($_POST['university_name']);
                // تحقق إذا الجامعة موجودة
                $check_uni = $conn->prepare("SELECT University_ID FROM universities WHERE University_Name = ?");
                $check_uni->bind_param("s", $university_name);
                $check_uni->execute();
                $res_uni = $check_uni->get_result();

                if ($res_uni->num_rows > 0) {
                    $uni_row = $res_uni->fetch_assoc();
                    $university_id = $uni_row['University_ID'];
                } else {
                    // أضف الجامعة الجديدة
                    $insert_uni = $conn->prepare("INSERT INTO universities (University_Name) VALUES (?)");
                    $insert_uni->bind_param("s", $university_name);
                    $insert_uni->execute();
                    $university_id = $conn->insert_id;
                }
            }

            // تسجيل الطالب
            if ($role == "Student") {
                $major = trim($_POST['major']);
                $insert_student = $conn->prepare("INSERT INTO student_details (Student_ID, University_ID, Major) VALUES (?, ?, ?)");
                $insert_student->bind_param("iis", $user_id, $university_id, $major);
                $insert_student->execute();
            }

            // تسجيل مشرف الشركة
            elseif ($role == "CompanySupervisor") {
                $company_name = trim($_POST['company_name']);
                $position = trim($_POST['position']);
                $insert_comp = $conn->prepare("INSERT INTO company_supervisor_details (Comp_Supervisor_ID, Company_Name, Position) VALUES (?, ?, ?)");
                $insert_comp->bind_param("iss", $user_id, $company_name, $position);
                $insert_comp->execute();
            }

            // تسجيل مشرف الجامعة
            elseif ($role == "UniversitySupervisor") {
                $department = trim($_POST['department']);
                $insert_uni_sup = $conn->prepare("INSERT INTO university_supervisor_details (Uni_Supervisor_ID, University_ID, Department) VALUES (?, ?, ?)");
                $insert_uni_sup->bind_param("iis", $user_id, $university_id, $department);
                $insert_uni_sup->execute();
            }

            $success = "✅ تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.";
        } else {
            $error = "حدث خطأ أثناء التسجيل.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>إنشاء حساب جديد</title>
<link rel="stylesheet" href="css/login.css">
<script defer>
document.addEventListener("DOMContentLoaded", () => {
    const roleSelect = document.getElementById("role");
    const extraFields = document.getElementById("extraFields");

    roleSelect.addEventListener("change", () => {
        const role = roleSelect.value;
        let html = "";

        if (role === "Student") {
            html = `
                <label>اسم الجامعة:</label>
                <input type="text" name="university_name" placeholder="أدخل اسم الجامعة" required>
                <label>التخصص:</label>
                <input type="text" name="major" placeholder="أدخل التخصص" required>
            `;
        } else if (role === "CompanySupervisor") {
            html = `
                <label>اسم الشركة:</label>
                <input type="text" name="company_name" placeholder="أدخل اسم الشركة" required>
                <label>الوظيفة:</label>
                <input type="text" name="position" placeholder="أدخل الوظيفة" required>
            `;
        } else if (role === "UniversitySupervisor") {
            html = `
                <label>اسم الجامعة:</label>
                <input type="text" name="university_name" placeholder="أدخل اسم الجامعة" required>
                <label>القسم:</label>
                <input type="text" name="department" placeholder="أدخل القسم" required>
            `;
        }

        extraFields.innerHTML = html;
    });
});
</script>
</head>
<body>
<div class="login-container">
    <h2>إنشاء حساب جديد</h2>
    <form method="POST" id="registerForm">
        <label>الاسم الكامل:</label>
        <input type="text" name="name" placeholder="أدخل اسمك الكامل" required>

        <label>البريد الإلكتروني:</label>
        <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني" required>

        <label>كلمة المرور:</label>
        <input type="password" name="password" placeholder="أدخل كلمة المرور" required>

        <label>اختر الدور:</label>
        <select name="role" id="role" required>
            <option value="">-- اختر الدور --</option>
            <option value="Student">طالب</option>
            <option value="CompanySupervisor">مشرف الشركة</option>
            <option value="UniversitySupervisor">مشرف الجامعة</option>
        </select>

        <div id="extraFields"></div>

        <button type="submit">تسجيل</button>

        <p class="error"><?= $error ?></p>
        <p class="success"><?= $success ?></p>
    </form>
    <p>هل لديك حساب؟ <a href="login.php">تسجيل الدخول</a></p>
</div>
</body>
</html>
