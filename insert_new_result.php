<?php
// Note: $conn is inherited from dashboard.php
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and trim POST data
    $academic_year = trim($_POST['academic_year'] ?? '');
    $course_code   = trim($_POST['course_code'] ?? '');
    $year          = trim($_POST['year'] ?? '');
    $semester      = trim($_POST['semester'] ?? '');
    $reg_no        = trim($_POST['reg_no'] ?? ''); // This is the Student ID
    $grade         = trim($_POST['grade'] ?? '');

    // 1. Check if student exists in the 'students' table first
    // Use 'student_id' if that is what we named it in the previous step
    $checkStudentSql = "SELECT student_id, full_name FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($checkStudentSql);
    $stmt->bind_param("s", $reg_no);
    $stmt->execute();
    $studentResult = $stmt->get_result();

    if ($studentResult->num_rows === 0) {
        $message = "<div class='alert error'>❌ Student $reg_no not found. Please register the student first.</div>";
    } else {
        $studentData = $studentResult->fetch_assoc();
        $student_name = $studentData['full_name'];
        $stmt->close();

        // 2. Check if this specific result (Student + Course) already exists
        $checkResultSql = "SELECT * FROM results WHERE reg_no = ? AND course_code = ?";
        $stmt = $conn->prepare($checkResultSql);
        $stmt->bind_param("ss", $reg_no, $course_code);
        $stmt->execute();
        $resultCheck = $stmt->get_result();

        if ($resultCheck->num_rows > 0) {
            // Update
            $updateSql = "UPDATE results SET grade = ?, academic_year = ?, year = ?, semester = ? WHERE reg_no = ? AND course_code = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ssiiss", $grade, $academic_year, $year, $semester, $reg_no, $course_code);
            $action = "updated";
        } else {
            // Insert
            $insertSql = "INSERT INTO results (academic_year, course_code, year, semester, reg_no, name, grade) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("ssissss", $academic_year, $course_code, $year, $semester, $reg_no, $student_name, $grade);
            $action = "added";
        }

        if ($stmt->execute()) {
            $message = "<div class='alert success'>✔ Result for $reg_no $action successfully!</div>";
        } else {
            $message = "<div class='alert error'>❌ SQL Error: " . $conn->error . "</div>";
        }
    }
}
?>

<style>
    .result-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .result-card h2 { text-align: center; color: #333; margin-bottom: 20px; }
    .input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .input-group { margin-bottom: 15px; }
    .input-group label { display: block; font-size: 13px; font-weight: bold; color: #555; margin-bottom: 5px; text-align: right;}
    .input-group input, .input-group select { 
        width: 100%; padding: 12px; border: none; border-radius: 4px; 
        background-color: #5d2a5f; color: white; box-sizing: border-box; 
    }
    .full-width { grid-column: span 2; }
    .btn-submit { 
        width: 100%; padding: 15px; background-color: #28a745; color: white; 
        border: none; border-radius: 5px; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 10px;
    }
    .alert { padding: 15px; text-align: center; border-radius: 5px; margin-bottom: 20px; border: 1px solid transparent; }
    .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
</style>

<div class="result-card">
    <h2>Insert New Result</h2>
    <?php echo $message; ?>
    <form method="POST" action="dashboard.php?page=insert_result">
        <div class="input-grid">
            <div class="input-group">
                <label>Academic Year</label>
                <select name="academic_year" required>
                     <option value="2020/2021">2020/2021</option>
                      <option value="2021/2022">2021/2022</option>
                    <option value="2022/2023">2022/2023</option>
                    <option value="2023/2024">2023/2024</option>
                   
                </select>
            </div>
            <div class="input-group">
                <label>Course Code</label>
                <input type="text" name="course_code" placeholder="e.g. ICT1202" required>
            </div>
            <div class="input-group">
                <label>Year of Study</label>
                <select name="year" required>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>
            </div>
            <div class="input-group">
                <label>Semester</label>
                <select name="semester" required>
                    <option value="1">1st Semester</option>
                    <option value="2">2nd Semester</option>
                </select>
            </div>
            <div class="input-group full-width">
                <label>Registration Number (Student ID)</label>
                <input type="text" name="reg_no" placeholder="REG/XXX/000" required>
            </div>
            <div class="input-group full-width">
                <label>Grade</label>
                <select name="grade" required>
                    <option value="A">A</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B">B</option>
                    <option value="B-">B-</option>
                    <option value="C+">C+</option>
                    <option value="C">C</option>
                    <option value="C-">C-</option>
                    <option value="D">D</option>
                    <option value="E">E</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-submit">SAVE RESULT</button>
    </form>
</div>