<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include 'config.php';

$course_id = $_GET['id'];
$course = $conn->query("SELECT * FROM courses WHERE id = $course_id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $credit_unit = $_POST['credit_unit'];
    $level = $_POST['level'];

    $sql = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, credit_unit = ?, level = ? WHERE id = ?");
    $sql->bind_param("ssiii", $course_code, $course_name, $credit_unit, $level, $course_id);
    if ($sql->execute()) {
        echo "<script>alert('Course updated successfully!'); window.location='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location='admin_dashboard.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Course</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
        }
        .edit-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="edit-container">
    <h2 class="text-center">Edit Course</h2>
    <hr>

    <form method="POST">
        <div class="mb-3">
            <label>Course Code</label>
            <input type="text" name="course_code" class="form-control" value="<?php echo $course['course_code']; ?>" required>
        </div>
        <div class="mb-3">
            <label>Course Name</label>
            <input type="text" name="course_name" class="form-control" value="<?php echo $course['course_name']; ?>" required>
        </div>
        <div class="mb-3">
            <label>Credit Unit</label>
            <input type="number" name="credit_unit" class="form-control" value="<?php echo $course['credit_unit']; ?>" required>
        </div>
        <div class="mb-3">
            <label>Level</label>
            <select name="level" class="form-select" required>
                <option value="100" <?php echo $course['level'] == 100 ? 'selected' : ''; ?>>100L</option>
                <option value="200" <?php echo $course['level'] == 200 ? 'selected' : ''; ?>>200L</option>
                <option value="300" <?php echo $course['level'] == 300 ? 'selected' : ''; ?>>300L</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Update Course</button>
    </form>

    <div class="text-center mt-3">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

</body>
</html>