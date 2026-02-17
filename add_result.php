<?php
session_start();

// Check if the user is logged in and is a lecturer/admin
// For this example, we assume a 'lecturer' role is required.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "lecturer") {
    // Redirect to login page if not authorized
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

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $reg_no = trim($_POST["reg_no"]);
    $course_code = trim($_POST["course_code"]);
    $course_name = trim($_POST["course_name"]);
    $credits = (int)trim($_POST["credits"]);
    $grade = trim($_POST["grade"]);
    $semester = trim($_POST["semester"]);

    // Check if the student exists
    $check_student_sql = "SELECT reg_no FROM students WHERE reg_no = ?";
    $stmt_check_student = $conn->prepare($check_student_sql);
    $stmt_check_student->bind_param("s", $reg_no);
    $stmt_check_student->execute();
    $student_result = $stmt_check_student->get_result();

    if ($student_result->num_rows == 0) {
        $message = "Error: Student with registration number " . htmlspecialchars($reg_no) . " does not exist.";
        $message_type = "error";
    } else {
        // First, check if the course already exists in the 'courses' table.
        // If not, insert it to prevent future 'unknown column' errors on the dashboard.
        $check_course_sql = "SELECT course_code FROM courses WHERE course_code = ?";
        $stmt_check_course = $conn->prepare($check_course_sql);
        $stmt_check_course->bind_param("s", $course_code);
        $stmt_check_course->execute();
        $course_result = $stmt_check_course->get_result();

        if ($course_result->num_rows == 0) {
            // Course does not exist, insert it
            $insert_course_sql = "INSERT INTO courses (course_code, course_name, credits) VALUES (?, ?, ?)";
            $stmt_insert_course = $conn->prepare($insert_course_sql);
            $stmt_insert_course->bind_param("ssi", $course_code, $course_name, $credits);
            $stmt_insert_course->execute();
            $stmt_insert_course->close();
        } else {
            // Course exists, but update the name and credits just in case
            $update_course_sql = "UPDATE courses SET course_name = ?, credits = ? WHERE course_code = ?";
            $stmt_update_course = $conn->prepare($update_course_sql);
            $stmt_update_course->bind_param("sis", $course_name, $credits, $course_code);
            $stmt_update_course->execute();
            $stmt_update_course->close();
        }

        // Now, insert the result into the 'results' table.
        // Check if a result for this student and course already exists.
        $check_result_sql = "SELECT * FROM results WHERE reg_no = ? AND course_code = ?";
        $stmt_check_result = $conn->prepare($check_result_sql);
        $stmt_check_result->bind_param("ss", $reg_no, $course_code);
        $stmt_check_result->execute();
        $existing_result = $stmt_check_result->get_result();

        if ($existing_result->num_rows > 0) {
            // Result exists, update it
            $update_result_sql = "UPDATE results SET grade = ?, semester = ? WHERE reg_no = ? AND course_code = ?";
            $stmt_update_result = $conn->prepare($update_result_sql);
            $stmt_update_result->bind_param("ssis", $grade, $semester, $reg_no, $course_code);
            
            if ($stmt_update_result->execute()) {
                $message = "Result for " . htmlspecialchars($reg_no) . " updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating result: " . $conn->error;
                $message_type = "error";
            }
            $stmt_update_result->close();
        } else {
            // Result does not exist, insert it
            $insert_result_sql = "INSERT INTO results (reg_no, course_code, grade, semester) VALUES (?, ?, ?, ?)";
            $stmt_insert_result = $conn->prepare($insert_result_sql);
            $stmt_insert_result->bind_param("sssi", $reg_no, $course_code, $grade, $semester);
            
            if ($stmt_insert_result->execute()) {
                $message = "Result for " . htmlspecialchars($reg_no) . " added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding result: " . $conn->error;
                $message_type = "error";
            }
            $stmt_insert_result->close();
        }
    }
    
    $stmt_check_student->close();
    $stmt_check_course->close();
    $stmt_check_result->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student Result</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .message-container {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* The following styles have been added to improve the spacing of the table headers and content */
        .results-table th, .results-table td {
            padding: 8px 12px;
            text-align: left;
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
                <li><a href="lecturer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="add_result.php" class="active"><i class="fas fa-plus-circle"></i> Add Result</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <header>
            <div class="header-nav">
                <div class="user-icon"><i class="fas fa-user-circle"></i></div>
            </div>
        </header>
        <div class="dashboard-content">
            <div class="welcome-message">
                <h1>Add Student Result</h1>
                <p>Use this form to add or update a student's course result.</p>
            </div>
            
            <form class="add-result-form" action="add_result.php" method="post">
                <div class="form-group">
                    <input type="text" id="reg_no" name="reg_no" placeholder="Student Registration Number" required>
                </div>
                <div class="form-group">
                    <input type="text" id="course_code" name="course_code" placeholder="Course Code" required>
                </div>
                <div class="form-group">
                    <input type="text" id="course_name" name="course_name" placeholder="Course Name" required>
                </div>
                <div class="form-group">
                    <input type="number" id="credits" name="credits" placeholder="Credits" required>
                </div>
                <div class="form-group">
                    <input type="text" id="grade" name="grade" placeholder="Grade (e.g., A+)" required>
                </div>
                <div class="form-group">
                    <input type="number" id="semester" name="semester" placeholder="Semester" required>
                </div>
                <?php if (!empty($message)): ?>
                    <div class="message-container <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <div class="form-actions">
                    <button type="submit" class="login-button">
                        <i class="fas fa-plus-circle"></i> Add/Update Result
                    </button>
                    <a href="lecturer_dashboard.php" class="back-button-login">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
