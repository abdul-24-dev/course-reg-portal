<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$matric = strtoupper(trim($input['matric_number'] ?? ''));

if (!$matric) {
    echo json_encode(['error' => 'Matric number required']);
    exit();
}

$sql = $conn->prepare("SELECT id, full_name FROM students WHERE UPPER(matric_number) = ? AND (fingerprint_template IS NOT NULL OR face_encoding IS NOT NULL)");
$sql->bind_param("s", $matric);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    echo json_encode(['student' => $student]);
} else {
    echo json_encode(['error' => 'Student not found or biometric not enrolled']);
}
?>