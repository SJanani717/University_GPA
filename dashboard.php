<?php
session_start();
$conn = new mysqli("localhost", "root", "", "uov_gpa"); // Shared connection

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html"); 
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$username = htmlspecialchars($_SESSION['username'] ?? 'Admin123'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --purple: #5d2a5f; --sidebar-width: 250px; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; display: flex; flex-direction: column; height: 100vh; }
        
        /* Top Header */
        header {
            background-color: var(--purple);
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        .header-nav { display: flex; width: 100%; align-items: center; }
        .menu-icon { font-size: 20px; width: var(--sidebar-width); }
        nav.top-links { flex-grow: 1; text-align: center; }
        nav.top-links a { color: white; text-decoration: none; margin: 0 15px; font-size: 14px; }
        .user-icon { white-space: nowrap; font-weight: bold; }

        /* Sidebar and Main Layout */
        .dashboard-container { display: flex; margin-top: 50px; flex-grow: 1; }
        
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--purple);
            height: calc(100vh - 50px);
            position: fixed;

            color: white;
            display: flex;
            flex-direction: column;
        }
        .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav li a {
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: 0.3s;
        }
        .sidebar-nav li a i { width: 30px; font-size: 18px; }
        .sidebar-nav li a.active, .sidebar-nav li a:hover { background-color: rgba(255,255,255,0.1); }
        
        .logout-section { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-section a { padding: 15px 20px; color: white; text-decoration: none; display: block; }

        /* Content Area */
        .dashboard-main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 40px;
            display: flex;
            justify-content: center;
        }
        #content-display { width: 100%; max-width: 800px; }
    </style>
</head>
<body>
    <header>
        <div class="header-nav">
            <div class="menu-icon"><i class="fas fa-bars"></i></div>
            <nav class="top-links">
                <a href="logout.php">Home</a> 
            <a href="dashboard.php?page=about_us">About us</a>
            <a href="dashboard.php?page=contact_us">Contact us</a>
            </nav>
            <div class="user-icon"><i class="fas fa-user-circle"></i> <?php echo $username; ?></div>
        </div>
    </header>

    <div class="dashboard-container">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                
                <ul>
                    <li><a href="dashboard.php?page=add_students" class="<?php echo $page == 'add_students' ? 'active' : ''; ?>"><i class="fas fa-user-plus"></i> Add New Students</a></li>
                    <li><a href="dashboard.php?page=insert_result" class="<?php echo $page == 'insert_result' ? 'active' : ''; ?>"><i class="fas fa-edit"></i> Insert New Result</a></li>
                    <li><a href="dashboard.php?page=registered_students" class="<?php echo $page == 'registered_students' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Registered Students</a></li>
                    <li><a href="dashboard.php?page=all_results" class="<?php echo $page == 'all_results' ? 'active' : ''; ?>"><i class="fas fa-list-ul"></i> All Students Result</a></li>
                    <li><a href="dashboard.php?page=manage_results" class="<?php echo $page == 'manage_results' ? 'active' : ''; ?>"><i class="fas fa-list-ul"></i> Manage  Students Result</a></li>
                <li><a href="dashboard.php?page=Edit_results" class="<?php echo $page == 'all_results' ? 'active' : ''; ?>"><i class="fas fa-list-ul"></i> Edit Students Result</a></li>
                </ul>
            </nav>
            <div class="logout-section">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
            </div>
        </aside>

        <main class="dashboard-main-content">
            <div id="content-display">
                <?php
                switch ($page) {
                    case 'add_students': include 'add_new_studentsss.php'; break;
                    case 'insert_result':include 'insert_new_result.php';break;
     // Updated to match your actual filename
                    case 'registered_students': include 'registered_students.php'; break;
                    case 'all_results': include 'allresult.php'; break;
                case 'manage_results': include 'manage_results.php'; break;
                    case 'edit_results': include 'edit_result.php'; break;

case 'about_us': include 'about_us.php'; break;
    case 'contact_us': include 'contact_us.php'; break;

                    default:
                    echo "<h1>Welcome, $username</h1><p>Select an option to manage the system.</p>";
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>