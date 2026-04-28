<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include 'config.php';

$course_id = $_GET['id'];

// Delete the course
$sql = $conn->prepare("DELETE FROM courses WHERE id = ?");
$sql->bind_param("i", $course_id);
if ($sql->execute()) {
    echo "<script>alert('Course deleted successfully!'); window.location='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Error: " . $conn->error . "'); window.location='admin_dashboard.php';</script>";
}
?>