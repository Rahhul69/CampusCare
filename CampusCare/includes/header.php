<?php
// Check if session is not already started before starting it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header-container {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
        }

        .logo i {
            font-size: 2rem;
            animation: pulse 2s infinite;
        }

        .logo h1 {
            font-size: 1.8rem;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            transform: translateY(0);
        }

        .nav-links a:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        /* Add active link styling */
        .nav-links a.active {
            background-color: rgba(255,255,255,0.2);
            font-weight: 600;
        }

        /* Add margin to body to prevent content from hiding under fixed header */
        body {
            margin-top: 80px;
        }
    </style>
</head>
<body>
    <header class="header-container">
        <div class="header-content">
            <a href="<?php 
                if(isset($_SESSION['role'])) {
                    echo $_SESSION['role'] . '_dashboard.php';
                } else {
                    echo 'index.php';
                }
            ?>" class="logo">
                <i class="fas fa-hospital-user"></i>
                <h1>CampusCare</h1>
            </a>
            <nav class="nav-links">
                <?php 
                // Get current page name for active link highlighting
                $current_page = basename($_SERVER['PHP_SELF']);
                
                if(isset($_SESSION['role'])): 
                    // Dashboard link based on role
                    $dashboard = $_SESSION['role'] . '_dashboard.php';
                    $dashboardActive = ($current_page === $dashboard) ? 'active' : '';
                    echo "<a href='$dashboard' class='$dashboardActive'><i class='fas fa-home'></i> Dashboard</a>";

                    switch($_SESSION['role']):
                        case 'admin':
                            $complaintsActive = ($current_page === 'admin_complaints.php') ? 'active' : '';
                            $usersActive = ($current_page === 'manage_users.php') ? 'active' : '';
                            echo "<a href='admin_complaints.php' class='$complaintsActive'><i class='fas fa-tasks'></i> Complaints</a>";
                            echo "<a href='manage_users.php' class='$usersActive'><i class='fas fa-users'></i> Users</a>";
                            break;
                            
                        case 'student':
                            $newComplaintActive = ($current_page === 'submit_complaint.php') ? 'active' : '';
                            $myComplaintsActive = ($current_page === 'my_complaints.php') ? 'active' : '';
                            echo "<a href='submit_complaint.php' class='$newComplaintActive'><i class='fas fa-plus-circle'></i> New Complaint</a>";
                            echo "<a href='my_complaints.php' class='$myComplaintsActive'><i class='fas fa-list'></i> My Complaints</a>";
                            break;
                            
                        case 'staff':
                            $complaintsActive = ($current_page === 'staff_complaints.php') ? 'active' : '';
                            echo "<a href='staff_complaints.php' class='$complaintsActive'><i class='fas fa-clipboard-list'></i> View Complaints</a>";
                            break;
                    endswitch;

                    $profileActive = ($current_page === 'profile.php') ? 'active' : '';
                    echo "<a href='profile.php' class='$profileActive'><i class='fas fa-user'></i> Profile</a>";
                    echo "<a href='logout.php'><i class='fas fa-sign-out-alt'></i> Logout</a>";
                else:
                    $homeActive = ($current_page === 'index.php') ? 'active' : '';
                    $aboutActive = ($current_page === 'about.php') ? 'active' : '';
                    $contactActive = ($current_page === 'contact.php') ? 'active' : '';
                    echo "<a href='index.php' class='$homeActive'><i class='fas fa-home'></i> Home</a>";
                    echo "<a href='about.php' class='$aboutActive'><i class='fas fa-info-circle'></i> About</a>";
                    echo "<a href='contact.php' class='$contactActive'><i class='fas fa-envelope'></i> Contact</a>";
                    echo "<a href='select_role.php'><i class='fas fa-sign-in-alt'></i> Login</a>";
                endif; 
                ?>
            </nav>
        </div>
    </header>
</body>
</html>