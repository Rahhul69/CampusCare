<?php
session_start();
require_once 'connect.php';

// Verify admin access
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle faculty addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_faculty'])) {
    try {
        $fName = filter_var($_POST['fName'], FILTER_SANITIZE_STRING);
        $lName = filter_var($_POST['lName'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $faculty_id = filter_var($_POST['faculty_id'], FILTER_SANITIZE_STRING);
        $department = filter_var($_POST['department'], FILTER_SANITIZE_STRING);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already registered");
        }

        // Insert faculty
        $stmt = $conn->prepare("INSERT INTO users (fName, lName, email, faculty_id, department, password, role, status) VALUES (?, ?, ?, ?, ?, ?, 'faculty', 'active')");
        $stmt->bind_param("ssssss", $fName, $lName, $email, $faculty_id, $department, $password);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Faculty member added successfully";
        } else {
            throw new Exception("Failed to add faculty member");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Fetch all faculty members
$sql = "SELECT * FROM users WHERE role = 'faculty' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CampusCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4e54c8;
            --primary-dark: #363995;
            --secondary: #8f94fb;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --info: #3498db;
            --light: #f5f6fa;
            --dark: #2d3436;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light);
            min-height: 100vh;
            overflow-x: hidden; /* Prevent horizontal scroll */
        }

        .dashboard-layout {
            display: flex; /* Change from grid to flex */
            min-height: 100vh;
            position: relative;
        }

        /* Fixed Sidebar */
        .sidebar {
            background: var(--gradient);
            padding: 2rem;
            position: fixed;
            height: 100vh;
            width: 280px;
            left: -280px;
            top: 0;
            transition: transform 0.3s ease-in-out;
            overflow-y: auto; /* Allow scroll for long menus */
            z-index: 1001; /* Ensure sidebar stays on top */
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }

        .sidebar.active {
            transform: translateX(280px);
        }

        .sidebar-header {
            text-align: center;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 2rem;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .nav-link i {
            width: 20px;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 1rem;
            background: var(--light);
            min-height: 100vh;
            width: 100%;
            transition: all 0.3s ease;
        }

        .dashboard-header {
            background: var(--gradient);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin: 1rem auto;
            max-width: 1400px;
            color: white;
            position: relative;
            z-index: 1;
            margin-left: calc(45px + 2rem); /* Width of menu toggle + some spacing */
            margin-right: 1rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-header h1 {
            color: white;
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .date-display {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .date-display:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .date-display i {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .date-info {
            display: flex;
            flex-direction: column;
        }

        .date-info .day {
            font-size: 1.1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.95);
        }

        .date-info .full-date {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Module Cards */
        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem auto; /* Center the grid */
            max-width: 1400px; /* Maximum width for larger screens */
            padding: 0 1rem;
        }

        .module-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%; /* Ensure equal height cards */
            display: flex;
            flex-direction: column;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .module-header {
            background: var(--gradient);
            padding: 1.5rem;
            color: white;
        }

        .module-title {
            font-size: 1.3rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin: 0;
        }

        .module-content {
            flex: 1; /* Allow content to expand */
            padding: 1.5rem;
        }

        .module-actions {
            margin-top: auto; /* Push actions to bottom */
            padding: 1.5rem;
            background: rgba(0,0,0,0.02);
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        /* Buttons */
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn i {
            font-size: 1.1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .module-grid {
                grid-template-columns: 1fr; /* Single column on mobile */
                padding: 0 1rem;
            }
        }

        /* Add mobile menu toggle */
        .menu-toggle {
            position: fixed;
            top: 1.5rem;
            left: 1.5rem;
            z-index: 1002;
            background: var(--primary);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .menu-toggle:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .menu-toggle i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .menu-toggle.active i {
            transform: rotate(90deg);
        }

        /* Stats Container */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            padding: 0 1rem;
            max-width: 1400px;
            margin: 2rem auto;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--primary);
        }

        .stat-info {
            flex: 1;
        }

        .stat-info h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dashboard-header {
                margin-top: calc(45px + 2rem); /* Height of menu toggle + spacing */
                margin-left: 1rem;
                padding: 1.5rem;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .date-display {
                padding: 0.6rem 1rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
                padding: 0 1rem;
            }

            .module-grid {
                grid-template-columns: 1fr;
                padding: 0 1rem;
            }
        }

        /* Add overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Faculty Management Styles */
        .faculty-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .faculty-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .faculty-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="dashboard-layout">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Campus Care</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin_complaints.php" class="nav-link">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Complaints</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_lost_found.php" class="nav-link">
                        <i class="fas fa-search"></i>
                        <span>Lost & Found</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_bookings.php" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Hall Booking</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="dashboard-header">
                <div class="header-content">
                    <h1>Admin Dashboard</h1>
                    
                    <div class="date-display">
                        <div class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout</a>
                    </div>
                        <i class="far fa-calendar-alt"></i>
                        <div class="date-info">
                            <span class="day"><?php echo date('l'); ?></span>
                            <span class="full-date"><?php echo date('F j, Y'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Complaints</h3>
                        <p class="stat-number">
                            <?php
                            $query = "SELECT COUNT(*) as count FROM complaints";
                            $result = $conn->query($query);
                            echo $result->fetch_assoc()['count'];
                            ?>
                        </p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Lost Items</h3>
                        <p class="stat-number">
                            <?php
                            $query = "SELECT COUNT(*) as count FROM lost_items";
                            $result = $conn->query($query);
                            echo $result->fetch_assoc()['count'];
                            ?>
                        </p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Hall Bookings</h3>
                        <p class="stat-number">
                            <?php
                            $query = "SELECT COUNT(*) as count FROM hall_bookings";
                            $result = $conn->query($query);
                            echo $result->fetch_assoc()['count'];
                            ?>
                        </p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p class="stat-number">
                            <?php
                            $query = "SELECT COUNT(*) as count FROM users";
                            $result = $conn->query($query);
                            echo $result->fetch_assoc()['count'];
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="module-grid">
                <!-- Complaints Module -->
                <div class="module-card">
                    <div class="module-header">
                        <h2 class="module-title">
                            <i class="fas fa-ticket-alt"></i>
                            Complaints Management
                        </h2>
                    </div>
                    <div class="module-content">
                        <p>Manage and respond to student complaints and issues.</p>
                    </div>
                    <div class="module-actions">
                        <a href="admin_complaints.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Manage Complaints
                        </a>
                    </div>
                </div>

                <!-- Lost & Found Module -->
                <div class="module-card">
                    <div class="module-header">
                        <h2 class="module-title">
                            <i class="fas fa-search"></i>
                            Lost & Found Items
                        </h2>
                    </div>
                    <div class="module-content">
                        <p>Track and manage lost and found items on campus.</p>
                    </div>
                    <div class="module-actions">
                        <a href="admin_lost_found.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Manage Items
                        </a>
                    </div>
                </div>

                <!-- Hall Booking Module -->
                <div class="module-card">
                    <div class="module-header">
                        <h2 class="module-title">
                            <i class="fas fa-calendar-alt"></i>
                            Hall Booking System
                        </h2>
                    </div>
                    <div class="module-content">
                        <p>Manage hall reservations and booking requests.</p>
                    </div>
                    <div class="module-actions">
                        <a href="admin_hall_bookings.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Manage Bookings
                        </a>
                    </div>
                </div>

                <!-- Faculty Management Module -->
                <div class="module-card">
                    <div class="module-header">
                        <h2 class="module-title">
                            <i class="fas fa-user-tie"></i>
                            Faculty Management
                        </h2>
                    </div>
                    <div class="module-content">
                        <p>Add and manage faculty members.</p>
                    </div>
                    <div class="module-actions">
                        <a href="admin_faculty.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Manage Faculty
                        </a>
                    </div>
                </div>
            </div>

            
    <?php
    if(isset($conn)) {
        $conn->close();
    }
    ?>

    <!-- Add JavaScript for mobile menu -->
    <script>
        // Get DOM elements
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        // Toggle sidebar function
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            menuToggle.classList.toggle('active');
        }

        // Close sidebar function
        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            menuToggle.classList.remove('active');
        }

        // Event listeners
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });

        overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        // Prevent sidebar clicks from closing
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    </script>
</body>
</html>
