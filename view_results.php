<?php
session_start();

// 1. Database connection
$conn = new mysqli("localhost", "root", "", "uov_gpa");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Security Check
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'student') {
    header("Location: student_login.php");
    exit;
}

$reg_no = $_SESSION['username']; 

// 3. Grade Point Mapping
function getGradePoints($grade) {
    $scale = [
        'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D+' => 1.3, 'D' => 1.0, 'E' => 0.0, 'F' => 0.0
    ];
    return $scale[strtoupper(trim($grade))] ?? 0.0;
}

// 4. Fetch Student Details
$student_query = $conn->query("SELECT * FROM students WHERE student_id = '$reg_no'");
$student = $student_query->fetch_assoc();

// 5. Fetch Results with JOIN to get names from 'courses' table
$results_query = $conn->query("SELECT r.*, c.course_name AS official_name 
                               FROM results r 
                               LEFT JOIN courses c ON r.course_code = c.course_code 
                               WHERE r.reg_no = '$reg_no'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transcript - <?php echo htmlspecialchars($reg_no); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        .transcript-container { background: white; max-width: 950px; margin: auto; padding: 40px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        
        /* Header Layout with Logo */
        .header { border-bottom: 3px solid #5d2a5f; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo-area { flex: 0 0 100px; }
        .uni-info { flex: 1; text-align: center; }
        .uni-info h1 { margin: 0; color: #5d2a5f; font-size: 24px; }
        .student-info { text-align: right; line-height: 1.5; font-size: 14px; }

        .year-label { background: #f1f1f1; color: #5d2a5f; padding: 12px; border-left: 6px solid #5d2a5f; font-size: 20px; font-weight: bold; margin-top: 30px; }
        .semester-title { background: #5d2a5f; color: white; padding: 8px 15px; margin-top: 15px; display: inline-block; border-radius: 4px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        
        .sgpa-row { text-align: right; padding: 10px; font-weight: bold; color: #5d2a5f; background: #fcfcfc; border: 1px solid #ddd; border-top: none; }
        .summary-card { background: #5d2a5f; color: white; padding: 30px; border-radius: 10px; text-align: center; margin-top: 40px; }
        
        @media print { .no-print { display: none; } .transcript-container { box-shadow: none; border: 1px solid #ddd; } }
    </style>
</head>
<body>

<div class="transcript-container">
    <div class="header">
        <div class="logo-area">
            <img src="images/uov_logo.png" alt="UOV Logo" style="width: 90px;">
        </div>
        <div class="uni-info">
            <h1>UNIVERSITY OF VAVUNIYA</h1>
            <p>Faculty of Technological Studies</p>
            <p>Official Academic Transcript</p>
        </div>
        <div class="student-info">
            <p><b>Name:</b> <?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></p>
            <p><b>Reg No:</b> <?php echo htmlspecialchars($reg_no); ?></p>
            <p><b>Date:</b> <?php echo date("Y-m-d"); ?></p>
        </div>
    </div>

    <?php
    $total_weighted_points = 0; $total_credits_all = 0; $grouped_data = [];

    while($row = $results_query->fetch_assoc()) {
        $code = $row['course_code'];
        preg_match('/\d/', $code, $matches, PREG_OFFSET_CAPTURE);
        $year_num = !empty($matches) ? substr($code, $matches[0][1], 1) : "Unknown";
        $sem_num  = !empty($matches) ? substr($code, $matches[0][1] + 1, 1) : "Unknown";
        $grouped_data[$year_num][$sem_num][] = $row;
    }

    if (empty($grouped_data)) {
        echo "<p style='text-align:center;'>No results found.</p>";
    } else {
        ksort($grouped_data);
        foreach ($grouped_data as $year => $semesters) {
            echo "<div class='year-label'>YEAR $year</div>";
            ksort($semesters);
            foreach ($semesters as $sem => $courses) {
                echo "<div class='semester-title'>SEMESTER $sem</div>";
                echo "<table><thead><tr><th>Code</th><th>Course Name</th><th>Credits</th><th>Grade</th></tr></thead><tbody>";
                
                $sem_wp = 0; $sem_c = 0;
                foreach ($courses as $course) {
                    $c_code = $course['course_code'];
                    $name = !empty($course['official_name']) ? $course['official_name'] : ($course['course_name'] ?: "Course Name Missing");
                    $credits = (int)substr($c_code, -1) ?: 3; 
                    $pts = getGradePoints($course['grade']);
                    
                    $sem_wp += ($pts * $credits); $sem_c += $credits;

                    echo "<tr><td>$c_code</td><td>$name</td><td>$credits</td><td><b>{$course['grade']}</b></td></tr>";
                }
                $sgpa = $sem_c > 0 ? $sem_wp / $sem_c : 0;
                echo "</tbody></table><div class='sgpa-row'>SGPA: " . number_format($sgpa, 2) . "</div>";
                $total_weighted_points += $sem_wp; $total_credits_all += $sem_c;
            }
        }
        $cgpa = $total_credits_all > 0 ? $total_weighted_points / $total_credits_all : 0;
    ?>

    <div class="summary-card">
        <div style="font-size: 12px; letter-spacing: 2px;">FINAL CGPA</div>
        <div style="font-size: 48px; font-weight: bold;"><?php echo number_format($cgpa, 2); ?></div>
        <div style="font-size: 18px; margin-top: 10px;">Total Credits: <?php echo $total_credits_all; ?></div>
    </div>
    <?php } ?>

    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 25px; background: #5d2a5f; color: white; border: none; cursor: pointer; border-radius: 4px;">
            <i class="fas fa-print"></i> Print Transcript
        </button>
    </div>
</div>

</body>
</html>