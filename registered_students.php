<?php
// Note: $conn is inherited from dashboard.php

// Handle Year Filter
$selected_year = isset($_POST['filter_year']) ? $conn->real_escape_string($_POST['filter_year']) : '';

// Fetch distinct years for the dropdown
$years_query = "SELECT DISTINCT academic_year FROM students ORDER BY academic_year DESC";
$years_result = $conn->query($years_query);

// Fetch students based on filter
$sql = "SELECT * FROM students";
if ($selected_year != '') {
    $sql .= " WHERE academic_year = '$selected_year'";
}
$sql .= " ORDER BY academic_year DESC, student_id ASC";
$result = $conn->query($sql);
?>

<style>
    .table-container {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        width: 100%;
    }
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .filter-section select {
        padding: 8px 15px;
        border-radius: 4px;
        border: 1px solid #ccc;
        background-color: #5d2a5f;
        color: white;
    }
    .filter-section button {
        padding: 8px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    table th {
        background-color: #5d2a5f;
        color: white;
        text-align: left;
        padding: 12px;
    }
    table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        color: #333;
    }
    table tr:hover { background-color: #f9f9f9; }
    .no-data { text-align: center; padding: 20px; color: #666; }
</style>

<div class="table-container">
    <div class="table-header">
        <h2>Registered Students List</h2>
        
        <form method="POST" class="filter-section">
            <label>Filter by Year: </label>
            <select name="filter_year">
                <option value="">-- All Years --</option>
                <?php while($y = $years_result->fetch_assoc()): ?>
                    <option value="<?php echo $y['academic_year']; ?>" <?php if($selected_year == $y['academic_year']) echo 'selected'; ?>>
                        <?php echo $y['academic_year']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Reg No</th>
                <th>Full Name</th>
                <th>Academic Year</th>
                <th>Department</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $row['student_id']; ?></strong></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['academic_year']; ?></td>
                        <td><?php echo $row['department']; ?></td>
                        <td><?php echo $row['contact_number']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">No students found for the selected year.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>