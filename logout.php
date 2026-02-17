<?php
session_start();

// 1. Capture the role while the session still exists
$actual_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'User';

// 2. Destroy the session immediately
$_SESSION = [];
session_destroy();

// 3. If the role isn't in the URL yet, redirect to this page with the role attached
// This prevents the "unknown" error if the page is refreshed.
if (!isset($_GET['role'])) {
    header("Location: logout.php?role=" . urlencode($actual_role));
    exit();
}

// 4. Get the role from the URL for the display below
$display_role = $_GET['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - University of Vavuniya</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body style="background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

    <main class="user-role-content">
        <div class="role-card" style="padding: 50px; text-align: center; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            
            <div style="font-size: 50px; color: #528825; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1 style="color: #528825 !important; font-size: 2.2rem; margin-bottom: 10px;">Logged Out Successfully</h1>
            
            <p style="font-size: 1.2rem; color: #333;">
                Your role was: <strong style="color: #5d285d; text-transform: capitalize;">
                    <?php echo htmlspecialchars($display_role); ?>
                </strong>
            </p>
            
            <div class="role-buttons" style="margin-top: 30px; display: flex; flex-direction: column; gap: 15px; align-items: center;">
                <a href="user-role.html" class="role-btn-main" style="width: 100%; max-width: 300px; text-decoration: none;">Click here to login again</a>
                <a href="index.html" style="color: #5d285d; text-decoration: none; font-weight: 600; font-size: 0.9rem;">Back to Home</a>
            </div>
        </div>
    </main>

</body>
</html>