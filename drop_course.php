<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $matric = $_SESSION['username'];

    // Fetch student ID
    $student = $conn->query("SELECT id FROM students WHERE matric_number = '$matric'")->fetch_assoc();
    $student_id = $student['id'];

    // Drop the course
    $sql = $conn->prepare("DELETE FROM student_courses WHERE student_id = ? AND course_id = ?");
    $sql->bind_param("ii", $student_id, $course_id);
    if ($sql->execute()) {
        echo "<script>alert('Course dropped successfully!'); window.location='student_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location='student_dashboard.php';</script>";
    }
}
?>