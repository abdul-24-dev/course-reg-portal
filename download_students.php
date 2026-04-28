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
    $result = $conn->query("SELECT s.*, d.name AS department_name FROM students s JOIN departments d ON s.department_id = d.id ORDER BY s.id DESC");
} else {
    $result = $conn->query("SELECT s.*, d.name AS department_name FROM students s JOIN departments d ON s.department_id = d.id WHERE s.department_id = $admin_dept ORDER BY s.id DESC");
}

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

if (!isset($_GET['format'])) {
    exit();
}

$format = $_GET['format'];

if ($format === 'word') {
    header("Content-Type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=students_" . date('Y-m-d') . ".doc");
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h2 style="color:#1e3a5f;text-align:center;">Student List</h2>';
    echo '<p style="text-align:center;color:#666;">Generated: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<table border="1" cellpadding="5" cellspacing="0" style="width:100%;border-collapse:collapse;">';
    echo '<tr style="background-color:#1e3a5f;color:white;font-weight:bold;"><th>S/N</th><th>Matric Number</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Level</th><th>Department</th></tr>';
    $i = 1;
    foreach ($students as $s) {
        $bg = ($i % 2 == 0) ? '#f8f8f8' : '#ffffff';
        echo "<tr style='background-color:$bg;'><td>$i</td><td>{$s['matric_number']}</td><td>{$s['full_name']}</td><td>{$s['email']}</td><td>{$s['phone_number']}</td><td>{$s['level']}</td><td>{$s['department_name']}</td></tr>";
        $i++;
    }
    echo '</table></body></html>';
} 
elseif ($format === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=students_" . date('Y-m-d') . ".xls");
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h2 style="color:#1e3a5f;text-align:center;">Student List</h2>';
    echo '<p style="text-align:center;color:#666;">Generated: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<table border="1" cellpadding="5" cellspacing="0" style="width:100%;border-collapse:collapse;">';
    echo '<tr style="background-color:#1e3a5f;color:white;font-weight:bold;"><th>S/N</th><th>Matric Number</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Level</th><th>Department</th></tr>';
    $i = 1;
    foreach ($students as $s) {
        $bg = ($i % 2 == 0) ? '#f8f8f8' : '#ffffff';
        echo "<tr style='background-color:$bg;'><td>$i</td><td>{$s['matric_number']}</td><td>{$s['full_name']}</td><td>{$s['email']}</td><td>{$s['phone_number']}</td><td>{$s['level']}</td><td>{$s['department_name']}</td></tr>";
        $i++;
    }
    echo '</table></body></html>';
}
elseif ($format === 'pdf') {
    // Generate styled PDF using basic structure
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=students_' . date('Y-m-d') . '.pdf');
    
    $pdf = createStyledPDF($students);
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
}

function createStyledPDF($students) {
    // Page dimensions (A4)
    $pageWidth = 595;
    $pageHeight = 842;
    $margin = 40;
    $colWidth = ($pageWidth - 2 * $margin) / 7;
    
    // Build PDF content stream
    $stream = '';
    
    // Title
    $stream .= 'BT /F1 18 Tf ' . ($pageWidth/2 - 50) . ' 780 Td (Student List) Tj ET' . "\n";
    
    // Subtitle
    $stream .= 'BT /F2 10 Tf ' . ($pageWidth/2 - 60) . ' 760 Td (Generated: ' . date('Y-m-d H:i:s') . ') Tj ET' . "\n";
    
    // Line under title
    $stream .= 'BT /F2 1 Tf ' . $margin . ' 745 Td (_____________________________________________________________) Tj ET' . "\n";
    
    // Table header
    $y = 720;
    $headers = ['S/N', 'Matric No.', 'Full Name', 'Email', 'Phone', 'Level', 'Dept.'];
    $colX = $margin;
    foreach ($headers as $header) {
        $stream .= 'BT /F3 9 Tf ' . $colX . ' ' . $y . ' Td (' . $header . ') Tj ET' . "\n";
        $colX += $colWidth;
    }
    
    // Header background
    $stream .= 'q /F3 9 Tf 0.2 0.2 0.6 rg ' . $margin . ' ' . ($y - 5) . ' ' . ($pageWidth - 2*$margin) . ' 15 re f Q' . "\n";
    
    // Table rows
    $y -= 25;
    $i = 1;
    foreach ($students as $s) {
        if ($y < 60) break; // End of page
        
        $colX = $margin;
        $rowData = [
            $i,
            substr($s['matric_number'], 0, 10),
            substr($s['full_name'], 0, 15),
            substr($s['email'], 0, 20),
            substr($s['phone_number'], 0, 12),
            $s['level'],
            substr($s['department_name'], 0, 10)
        ];
        
        $j = 0;
        foreach ($rowData as $val) {
            $stream .= 'BT /F2 8 Tf ' . $colX . ' ' . $y . ' Td (' . addslashes($val) . ') Tj ET' . "\n";
            $colX += $colWidth;
            $j++;
        }
        
        // Alternating row background
        if ($i % 2 == 0) {
            $stream .= 'q /F2 8 Tf 0.95 0.95 0.95 rg ' . $margin . ' ' . ($y - 3) . ' ' . ($pageWidth - 2*$margin) . ' 12 re f Q' . "\n";
            // Re-draw text on top
            $colX = $margin;
            foreach ($rowData as $val) {
                $stream .= 'BT /F2 8 Tf ' . $colX . ' ' . $y . ' Td (' . addslashes($val) . ') Tj ET' . "\n";
                $colX += $colWidth;
            }
        }
        
        $y -= 15;
        $i++;
    }
    
    // Footer
    $stream .= 'BT /F2 8 Tf ' . ($pageWidth/2 - 40) . ' 30 Td (Page 1 of 1) Tj ET' . "\n";
    $stream .= 'BT /F2 8 Tf ' . $margin . ' 30 Td (Total: ' . count($students) . ' students) Tj ET' . "\n";
    
    // Complete PDF structure
    $len = strlen($stream);
    
    $pdf = '%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $pageWidth . ' ' . $pageHeight . ']
/Resources << /Font << /F1 5 0 R /F2 6 0 R /F3 7 0 R >> >>
/Contents 4 0 R >>
endobj
4 0 obj
<< /Length ' . $len . ' >>
stream
' . $stream . '
endstream
endobj
5 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>
endobj
6 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
7 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>
endobj
xref
0 8
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000268 00000 n 
0000000421 00000 n 
0000000501 00000 n 
0000000575 00000 n 
trailer
<< /Size 8 /Root 1 0 R >>
startxref
' . (630 + $len) . '
%%EOF';
    
    return $pdf;
}
?>