<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Password - UOV</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .recovery-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
        }
        .recovery-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 400px;
            text-align: center;
        }
        .recovery-card h2 { color: #5d285d; margin-bottom: 20px; }
        .recovery-card p { font-size: 14px; color: #666; margin-bottom: 20px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn-recover {
            background-color: #5d285d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .btn-recover:hover { background-color: #4a1f4a; }
        .back-link { display: block; margin-top: 15px; text-decoration: none; color: #5d285d; font-size: 14px; }
        /* --- LOADING OVERLAY --- */
#loading-overlay {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    z-index: 9999;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #5d285d; /* Your signature purple */
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    margin-top: 15px;
    color: #5d285d;
    font-weight: bold;
    font-family: sans-serif;
}
    </style>
</head>
<body>

    <header>
        <div id="loading-overlay">
    <div class="spinner"></div>
    <p class="loading-text">Searching University Database...</p>
</div>
        <div class="header-nav">
            <div class="menu-icon"><i class="fas fa-bars"></i></div>
            
            <nav>
                <a href="index.html">Home</a>
                <a href="about_us.php">About us</a>
                <a href="contact_us.html">Contact us</a>
            </nav>
            
            <div class="user-icon"><i class="fas fa-user-circle"></i></div>
        </div>
    </header>

    <main class="recovery-container">
        <div class="recovery-card">
            <i class="fas fa-lock-open" style="font-size: 3rem; color: #5d285d; margin-bottom: 15px;"></i>
            <h2>Lost Password?</h2>
            <p>Enter your university email address and we'll send you a link to reset your password.</p>
            
            <form action="process_recovery.php" method="POST">
                <div class="input-group">
                    <input type="email" name="email" placeholder="sjanani712@gmail.com" required 
                           style="background-color: #eef4ff; border: 1px solid #d1d9e6;">
                </div>
                <button type="submit" class="btn-recover">Send Recovery Email</button>
            </form>

            <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </main>
    <script>
    document.querySelector('form').onsubmit = function() {
        // Show the loading screen
        document.getElementById('loading-overlay').style.display = 'flex';
        
        // Wait 2 seconds to simulate a "search," then let the form submit
        setTimeout(() => {
            this.submit();
        }, 2000);
        
        return false; // Prevent immediate submission
    };
</script>

</body>
</html>