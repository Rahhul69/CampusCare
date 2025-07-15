<?php
session_start();
include("connect.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$email = $_SESSION['email'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$user = mysqli_fetch_assoc($query);
$fullName = $user['fName'] . ' ' . $user['lName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | College Maintenance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #4A3AFF;
            --secondary: #6659FF;
            --success: #00C896;
            --text-primary: #2C3E50;
            --text-secondary: #64748b;
            --bg-gradient: linear-gradient(135deg, #f5f7ff 0%, #e9f0ff 100%);
            --card-gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --shadow-sm: 0 4px 6px rgba(74, 58, 255, 0.08);
            --shadow-md: 0 15px 35px rgba(74, 58, 255, 0.15);
            --shadow-lg: 0 25px 45px rgba(74, 58, 255, 0.2);
            --icon-shadow: 0 8px 16px rgba(74, 58, 255, 0.2);
        }

        /* Animation Keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-10px) scale(1.05); }
        }

        @keyframes glowPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(74, 58, 255, 0.4); }
            50% { box-shadow: 0 0 30px 0 rgba(74, 58, 255, 0.2); }
        }

        /* Base Styles */
        body {
            background: var(--bg-gradient);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            position: relative;
        }

        /* Enhanced Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            padding: 1rem 0;
            animation: fadeInUp 0.6s ease-out;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--card-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .navbar-brand i {
            font-size: 2rem;
            animation: floatIcon 3s ease-in-out infinite;
        }

        .nav-link {
            padding: 0.75rem 1.25rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(74, 58, 255, 0.08);
            transform: translateY(-2px);
        }

        .nav-link i {
            font-size: 1.2rem;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Welcome Section */
        .welcome-section {
            text-align: center;
            padding: 3rem 0;
            animation: fadeInUp 0.8s ease-out;
        }

        .welcome-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        /* Enhanced Module Cards */
        .module-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
            animation: fadeInUp 0.6s ease-out;
        }

        .module-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        .icon-container {
            height: 200px;
            background: var(--card-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        .icon-container::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, 
                rgba(255,255,255,0.2) 0%,
                rgba(255,255,255,0.1) 30%,
                transparent 70%);
            animation: rotate 20s linear infinite;
        }

        .icon-container i {
            font-size: 4.5rem;
            color: white;
            z-index: 2;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            animation: floatIcon 3s ease-in-out infinite;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            box-shadow: var(--icon-shadow);
        }

        .card-body {
            padding: 2rem;
            text-align: center;
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .card-title i {
            font-size: 1.5rem;
            background: var(--card-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-right: 0.5rem;
        }

        .btn-primary {
            background: var(--card-gradient);
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary i {
            font-size: 1.1rem;
            margin-left: 0.5rem;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary:hover i {
            transform: translateX(5px);
        }

        /* Footer Enhancement */
        footer {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: var(--text-secondary);
            padding: 1rem;
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .welcome-section h2 {
                font-size: 2rem;
            }

            .navbar-brand {
                font-size: 1.25rem;
            }

            .module-card {
                margin-bottom: 2rem;
            }
        }

        /* Update icon classes in the cards */
        .module-card:nth-child(1) .icon-container i { animation-delay: 0s; }
        .module-card:nth-child(2) .icon-container i { animation-delay: 0.2s; }
        .module-card:nth-child(3) .icon-container i { animation-delay: 0.4s; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-hospital-user"></i>
            CampusCare
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($fullName); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="my_bookings.php">
                        <i class="fas fa-calendar-check"></i>
                        My Hall Bookings Status
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Dashboard Content -->
<div class="container mt-5">
    <div class="welcome-section text-center">
        <h2><i class="fas fa-smile me-2"></i>Welcome, <?php echo htmlspecialchars($fullName); ?>!</h2>
        <p class="text-muted">Select a Option to proceed</p>
    </div>

    <div class="row justify-content-center"> <!-- Added row class and justify-content-center -->
        <!-- Complaint Registration -->
        <div class="col-md-4 mb-4">
            <div class="module-card">
                <div class="icon-container">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title">
                        <i class="fas fa-exclamation-circle"></i>
                        Complaint Registration
                    </h5>
                    <p class="card-text">Report infrastructure-related issues.</p>
                    <a href="complaints.php" class="btn btn-primary">
                        Go to Complaints
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Hall Booking -->
        <div class="col-md-4 mb-4">
            <div class="module-card">
                <div class="icon-container">
                    <i class="fas fa-building-user"></i>
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-check"></i>
                        Hall Booking
                    </h5>
                    <p class="card-text">Book halls for events and programs.</p>
                    <a href="hall_booking.php" class="btn btn-primary">
                        Go to Hall Booking
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Lost Item Module -->
        <div class="col-md-4 mb-4">
            <div class="module-card">
                <div class="icon-container">
                    <i class="fas fa-magnifying-glass-location"></i>
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title">
                        <i class="fas fa-box-open"></i>
                        Lost Items
                    </h5>
                    <p class="card-text">View for lost items.</p>
                    <a href="lost_item.php" class="btn btn-primary">
                        Go to Lost & Found
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer>
    <i class="far fa-copyright me-1"></i> 2025 College Maintenance | All Rights Reserved
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>