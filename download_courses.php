<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$matric = strtoupper($_SESSION['username']);
// Fetch student details including the image column
$student = $conn->query("SELECT s.*, d.name AS department_name FROM students s JOIN departments d ON s.department_id = d.id WHERE UPPER(s.matric_number) = '$matric'")->fetch_assoc();

$registered_courses = $conn->query("SELECT c.course_code, c.course_name, c.credit_unit FROM student_courses sc JOIN courses c ON sc.course_id = c.id WHERE sc.student_id = '{$student['id']}'");

require('pdf/fpdf/fpdf.php');

class PDF extends FPDF {
    // Custom property to hold student data
    public $studentData;

    function Header() {
        // Logo
        if(file_exists('nsuk_logo.png')) {
            $this->Image('nsuk_logo.png', 10, 10, 22);
        }

        // --- IMAGE LOGIC START ---
        $display_photo = 'avatar.png'; // Default
        
        // Check if profile_image exists in database and folder
        if (!empty($this->studentData['profile_image'])) {
            $uploaded_path = 'uploads/' . $this->studentData['profile_image'];
            if (file_exists($uploaded_path)) {
                $display_photo = $uploaded_path;
            }
        }

        // Render the image (either the upload or the avatar)
        if (file_exists($display_photo)) {
            $this->Image($display_photo, 162, 10, 28, 32);
        }
        // --- IMAGE LOGIC END ---

        $this->SetY(12);
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 102, 153);
        $this->Cell(0, 10, 'Nasarawa State University, Keffi', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100);
        $this->Cell(0, 5, "Student's Course Registration Form", 0, 1, 'C');
        $this->Ln(18); 
    }

    function StudentInfoGrid($student) {
        $this->SetTextColor(0);
        $this->SetDrawColor(200, 200, 200);
        $this->SetFillColor(240, 235, 230); 
        
        $label_w = 28;
        $val_w = 67;
        $h = 7.5;

        $this->SetFont('Arial', 'B', 8); 
        $this->Cell($label_w, $h, ' Matric', 1, 0, 'L', true);
        $this->SetFont('Arial', '', 8); 
        $this->Cell($val_w, $h, ' '.$student['matric_number'], 1, 0, 'L', true);
        $this->SetFont('Arial', 'B', 8); 
        $this->Cell($label_w, $h, ' Name', 1, 0, 'L', true);
        $this->SetFont('Arial', '', 8); 
        $this->Cell($val_w, $h, ' '.strtoupper($student['full_name']), 1, 1, 'L', true);

        $this->SetFont('Arial', 'B', 8);
        $this->Cell($label_w, $h, ' Department', 1, 0, 'L', false);
        $this->SetFont('Arial', '', 8);
        $this->Cell($val_w, $h, ' '.$student['department_name'], 1, 0, 'L', false);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($label_w, $h, ' Programme', 1, 0, 'L', false);
        $this->SetFont('Arial', '', 8);
        $this->Cell($val_w, $h, ' B.Sc. '.$student['department_name'], 1, 1, 'L', false);

        $this->SetFont('Arial', 'B', 8);
        $this->Cell($label_w, $h, ' Level', 1, 0, 'L', true);
        $this->SetFont('Arial', '', 8);
        $this->Cell($val_w, $h, ' 200', 1, 0, 'L', true); 
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($label_w, $h, ' Session', 1, 0, 'L', true);
        $this->SetFont('Arial', '', 8);
        $this->Cell($val_w, $h, ' 2024/2025', 1, 1, 'L', true);

        $this->SetFont('Arial', 'B', 8);
        $this->Cell($label_w, $h, ' Semester', 1, 0, 'L', false);
        $this->SetFont('Arial', '', 8);
        $this->Cell($val_w + $label_w + $val_w, $h, ' First', 1, 1, 'L', false);
        
        $this->Ln(6); 
    }

    function CourseTable($registered_courses) {
        $this->SetFillColor(200, 190, 180); 
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetDrawColor(160, 160, 160);
        
        $w = array(18, 75, 18, 20, 35, 24);
        $header = array('Course Code', 'Title', 'Credit Load', 'Classification', 'Registration Date', "Lecturer's Sign.");

        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i], 8, $header[$i], 1, 0, 'L', true);
        $this->Ln();
        
        $this->SetFont('Arial', '', 7.5);
        $fill = true; 
        $this->SetFillColor(240, 235, 230); 

        while ($row = $registered_courses->fetch_assoc()) {
            $this->Cell($w[0], 8, ' '.$row['course_code'], 1, 0, 'L', $fill);
            $this->Cell($w[1], 8, ' '.$row['course_name'], 1, 0, 'L', $fill);
            $this->Cell($w[2], 8, ' '.$row['credit_unit'], 1, 0, 'C', $fill);
            $this->Cell($w[3], 8, ' C', 1, 0, 'C', $fill);
            $timestamp = date('Y-m-d H:i:s') . '.' . rand(100, 999);
            $this->Cell($w[4], 8, ' '.$timestamp, 1, 0, 'L', $fill);
            $this->Cell($w[5], 8, '', 1, 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill; 
        }
    }
}

$pdf = new PDF();
$pdf->studentData = $student; // Pass data to the PDF class
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->StudentInfoGrid($student);
$pdf->CourseTable($registered_courses);

// Signature Section
$pdf->Ln(15); 
$dept = $student['department_name'];
$sig_file = '';
switch ($dept) {
    case 'Microbiology': $sig_file = 'microbiology-sign.png'; break;
    case 'Computer Science': $sig_file = 'computer-science-sign.png'; break;
    case 'Physics': $sig_file = 'physics-sign.png'; break;
    case 'Chemistry': $sig_file = 'chemistry-sign.png'; break;
    default: $sig_file = 'default-sign.png'; break;
}

if($pdf->GetY() > 250) $pdf->AddPage();

$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(0);

if(!empty($sig_file) && file_exists($sig_file)) {
    $pdf->Image($sig_file, 12, $pdf->GetY() - 10, 30); 
}

$pdf->Cell(95, 5, "", 0, 0, 'L');
$pdf->Cell(95, 5, "", 0, 1, 'R');
$pdf->Cell(95, 5, "HOD's Signature", 0, 0, 'L');
$pdf->Cell(95, 5, "Student's Signature", 0, 1, 'R');

$pdf->Output('D', $student['matric_number'].'_Course_Registration.pdf');