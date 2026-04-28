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

    // Check if already registered
    $check = $conn->query("SELECT * FROM student_courses WHERE student_id = $student_id AND course_id = $course_id");
    if ($check->num_rows > 0) {
        echo "<script>alert('You are already registered for this course'); window.location='student_dashboard.php';</script>";
    } else {
        // Register the course
        $sql = $conn->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
        $sql->bind_param("ii", $student_id, $course_id);
        if ($sql->execute()) {
            echo "<script>alert('Course registered successfully!'); window.location='student_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "'); window.location='student_dashboard.php';</script>";
        }
    }
}
?>