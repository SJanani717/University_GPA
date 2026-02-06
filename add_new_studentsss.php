<?php
// Note: Database connection ($conn) is inherited from dashboard.php
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dept    = $conn->real_escape_string($_POST['department']);
    $year    = $conn->real_escape_string($_POST['academic_year']);
    $reg_no  = $conn->real_escape_string($_POST['student_id']);
    $name    = $conn->real_escape_string($_POST['full_name']);
    $gender  = $conn->real_escape_string($_POST['gender']);
    $father  = $conn->real_escape_string($_POST['fathers_name']);
    $contact = $conn->real_escape_string($_POST['contact_number']);

    // --- NEW: Check if Registration Number already exists ---
    $check_sql = "SELECT student_id FROM students WHERE student_id = '$reg_no'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // If ID exists, show a warning instead of crashing
        $message = "<div style='color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; border: 1px solid #ffeeba;'>⚠ Error: Registration Number ($reg_no) is already registered.</div>";
    } else {
        // If ID is new, proceed with insertion
        $sql = "INSERT INTO students (department, academic_year, student_id, full_name, gender, fathers_name, contact_number) 
                VALUES ('$dept', '$year', '$reg_no', '$name', '$gender', '$father', '$contact')";

        if ($conn->query($sql) === TRUE) {
            $message = "<div style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb;'>✔ Student Registered Successfully!</div>";
        } else {
            $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;'>❌ System Error: " . $conn->error . "</div>";
        }
    }
}
?>

<style>
    .form-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .form-card h2 { text-align: center; margin-bottom: 25px; color: #333; }
    .input-group { margin-bottom: 15px; }
    .input-group label { display: block; text-align: right; font-size: 12px; font-weight: bold; color: #666; margin-bottom: 5px; }
    .input-group input, .input-group select { 
        width: 100%; padding: 12px; border: none; border-radius: 4px; 
        background-color: #5d2a5f; color: white; box-sizing: border-box; 
    }
    .btn-register { 
        width: 100%; padding: 15px; background-color: #007bff; color: white; 
        border: none; border-radius: 5px; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 10px;
    }
    ::placeholder { color: #ccc; }
</style>

<div class="form-card">
    <h2>Add New Student</h2>
    <?php echo $message; ?>
    <form method="POST" action="dashboard.php?page=add_students">
        <div class="input-group">
            <label>Department Name</label>
            <input type="text" name="department" placeholder="e.g. Computer Science" required>
        </div>
        <div class="input-group">
            <label>Academic Year</label>
            <select name="academic_year" required>
                <option value="">-- Choose Year --</option>
                    <option value="2020/2021">2020/2021</option>
                    <option value="2021/2022">2021/2022</option>
                <option value="2022/2023">2022/2023</option>
                <option value="2023/2024">2023/2024</option>

            </select>
        </div>
        <div class="input-group">
            <label>Registration Number</label>
            <input type="text" name="student_id" placeholder="2020/ICTS/01" required>
        </div>
        <div class="input-group">
            <label>Student Full Name</label>
            <input type="text" name="full_name" placeholder="Enter full name" required>
        </div>
        <div class="input-group">
            <label>Gender</label>
            <select name="gender" required>
                <option value="">-- Select Gender --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="input-group">
            <label>Father's Name</label>
            <input type="text" name="fathers_name" placeholder="Enter father's name" required>
        </div>
        <div class="input-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" placeholder="+123 456 7890" required>
        </div>
        <button type="submit" class="btn-register">REGISTER STUDENT</button>
    </form>
</div>