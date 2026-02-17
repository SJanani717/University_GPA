<?php
// Handle Delete Logic
if (isset($_GET['del_id'])) {
    $del_id = intval($_GET['del_id']);
    if ($conn->query("DELETE FROM results WHERE id = $del_id")) {
        echo "<div style='color: green; padding: 10px; background: #e9f7ef; border-radius: 5px; margin-bottom: 15px;'>Result deleted successfully!</div>";
    }
}

// Get the search term from the URL
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
?>

<div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; box-sizing: border-box;">
    <h2 style="color: #5d2a5f; border-bottom: 2px solid #f4f7f6; padding-bottom: 15px; margin-bottom: 20px;">
        <i class="fas fa-tasks"></i> Manage Student Results
    </h2>

    <form method="GET" action="dashboard.php" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="hidden" name="page" value="manage_results">
        <input type="text" name="search" placeholder="Search by Reg No or Course Code..." 
               value="<?php echo htmlspecialchars($search); ?>" 
               style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px; outline: none;">
        <button type="submit" style="background: #5d2a5f; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if($search): ?>
            <a href="dashboard.php?page=manage_results" style="padding: 10px; color: #e74c3c; text-decoration: none;">Clear</a>
        <?php endif; ?>
    </form>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background-color: #5d2a5f; color: white;">
                    <th style="padding: 15px; text-align: left;">Reg No</th>
                    <th style="padding: 15px; text-align: left;">Course Code</th>
                    <th style="padding: 15px; text-align: left;">Course Name</th>
                    <th style="padding: 15px; text-align: center;">Grade</th>
                    <th style="padding: 15px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Modified Query with Search Logic
                $sql = "SELECT r.id, r.reg_no, r.course_code, c.course_name AS official_name, r.course_name AS backup_name, r.grade 
                        FROM results r
                        LEFT JOIN courses c ON r.course_code = c.course_code";
                
                if (!empty($search)) {
                    $sql .= " WHERE r.reg_no LIKE '%$search%' OR r.course_code LIKE '%$search%'";
                }
                
                $sql .= " ORDER BY r.id DESC";
                
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $display_name = !empty($row['official_name']) ? $row['official_name'] : (!empty($row['backup_name']) ? $row['backup_name'] : "Course Name Missing");
                        $style = (strpos($display_name, 'Missing') !== false) ? "color:#999; font-style:italic;" : "";
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($row['reg_no']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($row['course_code']); ?></td>
                            <td style="padding: 12px; <?php echo $style; ?>"><?php echo htmlspecialchars($display_name); ?></td>
                            <td style="padding: 12px; text-align: center;">
                                <span style="background: #eef2f7; padding: 4px 12px; border-radius: 4px; font-weight: bold; color: #5d2a5f;">
                                    <?php echo htmlspecialchars($row['grade']); ?>
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <a href="dashboard.php?page=edit_results&id=<?php echo $row['id']; ?>" style="color: #2ecc71; margin-right: 15px; font-size: 18px;"><i class="fas fa-edit"></i></a>
                                <a href="dashboard.php?page=manage_results&del_id=<?php echo $row['id']; ?>" style="color: #e74c3c; font-size: 18px;" onclick="return confirm('Delete this record?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='5' style='padding: 40px; text-align: center; color: #999;'>No matching results found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>