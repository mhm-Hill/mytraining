<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'UniversitySupervisor'){
    header("Location: login.php");
    exit;
}

$uni_supervisor_id = $_SESSION['user_id'];
$submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
$comment = $_POST['comment'] ?? '';

if(!$submission_id){
    die("خطأ: لا يوجد تسليم لهذا الواجب!");
}

// تأكد أن التسليم موجود
$subCheck = $conn->prepare("SELECT Submission_ID FROM assignment_submissions WHERE Submission_ID = ?");
$subCheck->bind_param("i", $submission_id);
$subCheck->execute();
$subResult = $subCheck->get_result();
if($subResult->num_rows == 0){
    die("خطأ: لا يوجد تسليم لهذا الواجب!");
}

$today = date("Y-m-d");

// تحقق إذا التعليق موجود مسبقاً
$check = $conn->prepare("SELECT Comment_ID FROM university_supervisor_comments WHERE Submission_ID = ? AND Uni_Supervisor_ID = ?");
$check->bind_param("ii", $submission_id, $uni_supervisor_id);
$check->execute();
$res = $check->get_result();

if($res->num_rows > 0){
    $row = $res->fetch_assoc();
    $update = $conn->prepare("UPDATE university_supervisor_comments SET Comment = ?, Date = ? WHERE Comment_ID = ?");
    $update->bind_param("ssi", $comment, $today, $row['Comment_ID']);
    $update->execute();
} else {
    $insert = $conn->prepare("INSERT INTO university_supervisor_comments (Submission_ID, Uni_Supervisor_ID, Comment, Date) VALUES (?, ?, ?, ?)");
    $insert->bind_param("iiss", $submission_id, $uni_supervisor_id, $comment, $today);
    $insert->execute();
}

header("Location: view_assignments_uni.php");
exit;
?>
