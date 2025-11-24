<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: student_login.php");
    exit;
}

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
        case 'A+': case 'A': 
            return 4.0;
        case 'A-': 
            return 3.7;
        case 'B+': 
            return 3.3;
        case 'B':
            return 3.0;
        case 'B-':
            return 2.7;
        case 'C+': 
            return 2.3;
        case 'C':
            return 2.0;
        case 'D':
            return 1.0;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final GPA & Class</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .class-badge {
            display: inline-block;
            padding: 5px 15px;
            margin-top: 10px;
            border-radius: 20px;
            font-weight: bold;
            color: #fff;
            background-color: #4b2c7e;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2>UOV_GPA</h2>
        </div>
        <nav>
            <ul>
                <li><a href="student_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="final_gpa.php" class="active"><i class="fas fa-certificate"></i> Final GPA</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <header>
            <div class="header-nav">
                <div class="user-icon"><i class="fas fa-user-circle"></i></div>
            </div-nav>
        </header>

        <div class="dashboard-content">
            <div class="welcome-message">
                <h1>Final GPA & Class</h1>
                <p>Hello, <?php echo htmlspecialchars($student_name); ?>! Here is your final academic standing.</p>
            </div>

            <div class="card-container">
                <div class="card">
                    <h3>Final GPA</h3>
                    <p><?php echo number_format($final_gpa, 2); ?></p>
                    <?php if ($final_gpa > 0): ?>
                        <span class="class-badge"><?php echo htmlspecialchars($final_class); ?></span>
                    <?php endif; ?>
                </div>
                <div class="card">
                    <h3>Attended Semesters</h3>
                    <p><?php echo htmlspecialchars($total_semesters_attended); ?></p>
                </div>
            </div>

            <div class="results-table-container">
                <h3>Semester-wise Breakdown</h3>
                <?php if (!empty($results_by_semester)): ?>
                    <?php foreach ($results_by_semester as $semester => $data): ?>
                        <h4>Semester <?php echo htmlspecialchars($semester); ?> (GPA: <?php echo number_format($data['semester_grade_points'] / $data['semester_credits'], 2); ?>)</h4>
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Credits</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['courses'] as $course): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($course['credits']); ?></td>
                                        <td><?php echo htmlspecialchars($course['grade']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No results found for this student.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
