<?php
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

// SQL query to count students for specific academic years
// The trailing comma has been removed
$sql = "SELECT academic_year, COUNT(*) AS total_students FROM students WHERE academic_year IN ('2018/2019', '2019/2020', '2020/2021') GROUP BY academic_year";
$result = $conn->query($sql);

$conn->close();

$student_counts = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $student_counts[$row['academic_year']] = $row['total_students'];
    }
}
?>

<div class="registered-students-container">
    <h2>Student Registration Summary</h2>
    <div class="summary-grid">
        <div class="summary-item">
            <label>Total Students in Academic Year 2018/2019 (Year 4):</label>
            <input type="text" value="<?php echo isset($student_counts['2018/2019']) ? $student_counts['2018/2019'] : 0; ?>" readonly>
        </div>
        
        <div class="summary-item">
            <label>Total Students in Academic Year 2019/2020 (Year 3):</label>
            <input type="text" value="<?php echo isset($student_counts['2019/2020']) ? $student_counts['2019/2020'] : 0; ?>" readonly>
        </div>
        
        <div class="summary-item">
            <label>Total Students in Academic Year 2020/2021 (Year 2):</label>
            <input type="text" value="<?php echo isset($student_counts['2020/2021']) ? $student_counts['2020/2021'] : 0; ?>" readonly>
        </div>
    </div>
</div>

<style>
    .registered-students-container {
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 8px;
        margin: 20px;
    }
    .summary-grid {
        display: grid;
        gap: 20px;
        margin-top: 20px;
    }
    .summary-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .summary-item label {
        font-weight: bold;
        color: #5d285d;
        flex-basis: 250px;
    }
    .summary-item input {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: 100px;
        text-align: center;
        background-color: #f9f9f9;
    }
</style>