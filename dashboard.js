/* 1. SIDEBAR LOGIC (Essential for the menu to open/close) */
function openNav() {
    document.getElementById("mySidenav").style.width = "250px";
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
}

/* 2. DASHBOARD FUNCTIONALITY (Logout and Links) */
document.addEventListener('DOMContentLoaded', () => {
    
    // --- LOGOUT CONFIRMATION ---
    // Note: This looks for the logout link specifically in the sidebar
    const logoutLink = document.querySelector('.sidenav a[href="logout.php"]');

    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = logoutLink.href;
            }
        });
    }

    // --- SIDEBAR ACTIVE STATE ---
    const sidebarLinks = document.querySelectorAll('.sidenav a');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Only add active class to main links, not the close button
            if (!this.classList.contains('closebtn')) {
                sidebarLinks.forEach(item => item.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
});