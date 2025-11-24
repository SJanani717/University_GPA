<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: student_login.php");
    exit;
}

// Include the FPDF library
require('fpdf/fpdf.php');

// Database credentials
$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "uov_gpa";
$port = 4306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in student's registration number
$reg_no = $_SESSION["reg_no"];

// Function to convert grade to GPA points
function getGpaPoints($grade) {
    switch (strtoupper(trim($grade))) {
        case 'A+': case 'A': case 'A-':
            return 4.0;
        case 'B+': case 'B':
            return 3.0;
        case 'B-': case 'C+':
            return 2.0;
        case 'C': case 'C-':
            return 1.0;
        case 'D':
            return 0.5;
        default:
            return 0.0;
    }
}

// Function to determine class based on GPA and semesters attended
function getFinalClass($gpa, $semesters_attended) {
    // We assume there are 4 academic semesters in total
    $total_academic_semesters = 4;
    
    // Check if the student has completed all required semesters
    if ($semesters_attended < $total_academic_semesters) {
        return "Not Eligible for a Class (Semesters not completed)";
    }
    
    if ($gpa >= 3.7) {
        return "First Class";
    } elseif ($gpa >= 3.3) {
        return "Second Class (Upper Division)";
    } elseif ($gpa >= 3.0) {
        return "Second Class (Lower Division)";
    } else {
        return "Not Eligible for a Class";
    }
}

// Initialize variables
$student_name = "N/A";
$results_by_semester = [];
$total_credits = 0;
$total_grade_points = 0;
$total_semesters_attended = 0;

// Query to get student details
$sql_student = "SELECT name FROM students WHERE reg_no = ?";
$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("s", $reg_no);
$stmt_student->execute();
$student_result = $stmt_student->get_result();

if ($student_result->num_rows > 0) {
    $student_row = $student_result->fetch_assoc();
    $student_name = $student_row['name'];
}
$stmt_student->close();

// Query to get all results for the student
$sql_results = "
    SELECT r.semester, r.course_code, r.grade, c.course_name, c.credits
    FROM results r
    JOIN courses c ON r.course_code = c.course_code
    WHERE r.reg_no = ?
    ORDER BY r.semester, r.course_code
";
$stmt_results = $conn->prepare($sql_results);
$stmt_results->bind_param("s", $reg_no);
$stmt_results->execute();
$results = $stmt_results->get_result();

if ($results->num_rows > 0) {
    while ($row = $results->fetch_assoc()) {
        // Group results by semester
        $semester = $row['semester'];
        if (!isset($results_by_semester[$semester])) {
            $results_by_semester[$semester] = [
                'courses' => [],
                'semester_credits' => 0,
                'semester_grade_points' => 0
            ];
        }

        // Add course to the semester group
        $grade_points = getGpaPoints($row['grade']);
        $credits = $row['credits'];
        $results_by_semester[$semester]['courses'][] = [
            'course_code' => $row['course_code'],
            'course_name' => $row['course_name'],
            'grade' => $row['grade'],
            'credits' => $credits,
            'gpa_points' => $grade_points
        ];

        // Update semester totals
        $results_by_semester[$semester]['semester_credits'] += $credits;
        $results_by_semester[$semester]['semester_grade_points'] += ($grade_points * $credits);

        // Update overall totals
        $total_credits += $credits;
        $total_grade_points += ($grade_points * $credits);
    }
}
$stmt_results->close();

$total_semesters_attended = count($results_by_semester);

// Calculate final GPA and class
$final_gpa = ($total_credits > 0) ? ($total_grade_points / $total_credits) : 0.0;
$final_class = getFinalClass($final_gpa, $total_semesters_attended);

$conn->close();

// FPDF Generation starts here
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Student Academic Performance Report',0,1,'C');
$pdf->Ln(10);

// Student Information
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,'Registration Number: ' . $reg_no,0,1);
$pdf->Cell(0,10,'Student Name: ' . $student_name,0,1);
$pdf->Ln(5);

// Overall GPA and Class
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Overall Performance',0,1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,'Final GPA: ' . number_format($final_gpa, 2),0,1);
$pdf->Cell(0,10,'Final Class: ' . $final_class,0,1);
$pdf->Ln(10);

// Semester-wise breakdown
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Semester-wise Breakdown',0,1);
$pdf->Ln(5);

if (!empty($results_by_semester)) {
    foreach ($results_by_semester as $semester => $data) {
        $semester_gpa = number_format($data['semester_grade_points'] / $data['semester_credits'], 2);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,'Semester ' . $semester . ' (GPA: ' . $semester_gpa . ')',0,1);
        $pdf->Ln(2);

        // Table header
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,7,'Course Code',1);
        $pdf->Cell(80,7,'Course Name',1);
        $pdf->Cell(30,7,'Credits',1);
        $pdf->Cell(30,7,'Grade',1);
        $pdf->Ln();

        // Table rows
        $pdf->SetFont('Arial','',10);
        foreach ($data['courses'] as $course) {
            $pdf->Cell(40,7,$course['course_code'],1);
            $pdf->Cell(80,7,$course['course_name'],1);
            $pdf->Cell(30,7,$course['credits'],1);
            $pdf->Cell(30,7,$course['grade'],1);
            $pdf->Ln();
        }
        $pdf->Ln(10);
    }
} else {
    $pdf->SetFont('Arial','I',12);
    $pdf->Cell(0,10,'No results found for this student.',0,1);
}

// Output the PDF
$pdf->Output('I', 'GPA_Report_' . $reg_no . '.pdf');
?>
