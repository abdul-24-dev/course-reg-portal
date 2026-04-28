<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include 'config.php';

$admin_dept = $_SESSION['admin_dept'];
$is_super = $_SESSION['is_super'];

if ($is_super) {
    $students = $conn->query("
        SELECT s.*, d.name AS department_name 
        FROM students s 
        JOIN departments d ON s.department_id = d.id 
        ORDER BY s.id DESC
    ");
} else {
    $students = $conn->query("
        SELECT s.*, d.name AS department_name 
        FROM students s 
        JOIN departments d ON s.department_id = d.id 
        WHERE s.department_id = $admin_dept
        ORDER BY s.id DESC
    ");
}

$dept_name = $is_super ? 'All Departments' : '';
if ($admin_dept) {
    $dept_query = $conn->query("SELECT name FROM departments WHERE id = $admin_dept");
    $dept_row = $dept_query->fetch_assoc();
    if ($dept_row) $dept_name = $dept_row['name'];
}

// Check if download is requested
$download = isset($_GET['download']) && $_GET['download'] === '1';

if ($download) {
    // Generate downloadable file
    $filename = 'student_list_' . date('Y-m-d');
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="' . $filename . '.html"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
} else {
    // Show print preview page
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student List - Print Preview</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; background: #fff; }

.print-header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #1e3a5f; padding-bottom: 20px; }
.print-header h1 { color: #1e3a5f; margin-bottom: 10px; font-size: 28px; }
.print-header p { color: #666; font-size: 14px; margin: 5px 0; }

.btn-print {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-print:hover { background: #2563eb; transform: translateY(-2px); }
.btn-print i { font-size: 18px; }

.print-info {
    background: #f8fafc;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    border-left: 4px solid #2563eb;
}

.print-info p { margin: 0; color: #64748b; font-size: 14px; }

.table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.table th, .table td { border: 1px solid #e2e8f0; padding: 12px 10px; text-align: left; }
.table th { 
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: white; 
    font-weight: 600; 
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.table td { font-size: 14px; color: #334155; }
.table tbody tr:nth-child(even) { background: #f8fafc; }
.table tbody tr:hover { background: #eff6ff; }
.table td:first-child { text-align: center; color: #64748b; width: 50px; }
.table th:first-child { text-align: center; }

.student-name { font-weight: 600; color: #1e3a5f; }
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.badge-dept { background: #dbeafe; color: #2563eb; }
.badge-level { background: #dcfce7; color: #15803d; }

.empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
.empty-state i { font-size: 48px; margin-bottom: 15px; opacity: 0.5; }

.footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; border-top: 1px solid #e2e8f0; padding-top: 15px; }

@media print {
    .btn-print, .print-info, .no-print { display: none !important; }
    body { padding: 0; margin: 10px; }
    .table th { background: #1e3a5f !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .table tbody tr:nth-child(even) { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}

@media (max-width: 768px) {
    .btn-print { position: static; width: 100%; margin-bottom: 20px; justify-content: center; }
    .table { font-size: 12px; }
    .table th, .table td { padding: 8px 6px; }
}
</style>
</head>
<body>

<button class="btn-print no-print" onclick="window.print()">
    <i class="fas fa-print"></i> Print / Save as PDF
</button>

<div class="print-header">
    <h1><i class="fas fa-graduation-cap"></i> Student List</h1>
    <p><?php echo $dept_name; ?> | Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
</div>

<div class="print-info no-print">
    <p><i class="fas fa-info-circle"></i> Click "Print / Save as PDF" to download a proper PDF file</p>
    <p>Total: <?php echo $students->num_rows; ?> Students</p>
</div>

<?php if($students->num_rows > 0): ?>
<table class="table">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Matric Number</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Level</th>
            <th>Department</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $serial = 1;
        while($row = $students->fetch_assoc()): 
        ?>
        <tr>
            <td><?php echo $serial++; ?></td>
            <td><strong><?php echo $row['matric_number']; ?></strong></td>
            <td class="student-name"><?php echo $row['full_name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['phone_number']; ?></td>
            <td><span class="badge badge-level"><?php echo $row['level']; ?></span></td>
            <td><span class="badge badge-dept"><?php echo $row['department_name']; ?></span></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div class="footer">
    Student Portal | Total: <?php echo $students->num_rows; ?> students | <?php echo date('Y-m-d'); ?>
</div>

<?php else: ?>
<div class="empty-state">
    <i class="fas fa-user-graduate"></i>
    <p>No students found.</p>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    exit();
}

// For downloadable version, output the HTML content
echo '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student List</title>
<style>
body { font-family: Segoe UI, Arial, sans-serif; padding: 30px; background: #fff; }
.print-header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #1e3a5f; padding-bottom: 20px; }
.print-header h1 { color: #1e3a5f; margin-bottom: 10px; font-size: 26px; }
.print-header p { color: #666; font-size: 14px; }
.table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.table th, .table td { border: 1px solid #e2e8f0; padding: 10px 8px; text-align: left; }
.table th { background: #1e3a5f; color: white; font-weight: 600; font-size: 12px; text-transform: uppercase; }
.table td { font-size: 13px; color: #334155; }
.table tbody tr:nth-child(even) { background: #f8fafc; }
.student-name { font-weight: 600; color: #1e3a5f; }
.badge { display: inline-block; padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-dept { background: #dbeafe; color: #2563eb; }
.badge-level { background: #dcfce7; color: #15803d; }
.footer { text-align: center; margin-top: 30px; font-size: 11px; color: #999; border-top: 1px solid #e2e8f0; padding-top: 10px; }
</style>
</head>
<body>
<div class="print-header">
    <h1>Student List</h1>
    <p>' . $dept_name . ' | Generated: ' . date('Y-m-d H:i:s') . '</p>
</div>

<table class="table">
<tr><th>S/N</th><th>Matric Number</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Level</th><th>Department</th></tr>';

$serial = 1;
$students->data_seek(0);
while($row = $students->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . $serial++ . '</td>';
    echo '<td><strong>' . $row['matric_number'] . '</strong></td>';
    echo '<td class="student-name">' . $row['full_name'] . '</td>';
    echo '<td>' . $row['email'] . '</td>';
    echo '<td>' . $row['phone_number'] . '</td>';
    echo '<td><span class="badge badge-level">' . $row['level'] . '</span></td>';
    echo '<td><span class="badge badge-dept">' . $row['department_name'] . '</span></td>';
    echo '</tr>';
}

echo '</table>

<div class="footer">
    Student Portal | Total: ' . $students->num_rows . ' students | ' . date('Y-m-d') . '
</div>

</body>
</html>';
?>