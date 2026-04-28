<?php
session_start();
include 'config.php';

$admin_dept = $_SESSION['admin_dept'] ?? null;
$is_super = $_SESSION['is_super'] ?? 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $credit_unit = $_POST['credit_unit'];
    $level = $_POST['level'];
    $semester_id = $_POST['semester_id'];
    $session_id = $_POST['session_id'];
    
    if ($is_super && $_POST['department_id'] === 'all') {
        $depts = $conn->query("SELECT id FROM departments");
        while ($dept = $depts->fetch_assoc()) {
            $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, credit_unit, level, semester_id, session_id, department_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiiii", $course_code, $course_name, $credit_unit, $level, $semester_id, $session_id, $dept['id']);
            $stmt->execute();
        }
    } else {
        if ($is_super) {
            $department_id = $_POST['department_id'];
        } else {
            $department_id = $admin_dept;
        }
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, credit_unit, level, semester_id, session_id, department_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiii", $course_code, $course_name, $credit_unit, $level, $semester_id, $session_id, $department_id);
        $stmt->execute();
    }
    
    header("Location: admin_dashboard.php?success=1");
    exit();
}
?>