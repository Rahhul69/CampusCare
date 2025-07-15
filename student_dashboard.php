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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4A3AFF;
            --secondary-color: #6659FF;
            --accent-color: #00C896;
            --text-color: #2C3E50;
            --shadow-color: rgba(74, 58, 255, 0.15);
            --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #e9f0ff 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            position: relative;
            padding-bottom: 60px;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            transition: all 0.3s ease;
            font-size: 1.4rem;
        }

        .navbar-brand i {
            animation: floatIcon 3s ease-in-out infinite;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            padding: 0.8rem 1.5rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            background: rgba(74, 58, 255, 0.08);
            transform: translateY(-2px);
        }

        .welcome-section {
            animation: fadeInUp 0.8s ease-out;
            padding: 3rem 0;
        }

        .welcome-section h2 {
            color: var(--text-color);
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }

        .welcome-section p {
            font-size: 1.1rem;
            color: #64748b;
        }

        .module-card {
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 15px 35px var(--shadow-color);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        .module-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 45px var(--shadow-color);
        }

        .icon-container {
            height: 180px;
            background: var(--gradient);
            border-radius: 24px 24px 0 0;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-container::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(
                circle,
                rgba(255,255,255,0.2) 0%,
                rgba(255,255,255,0.1) 30%,
                transparent 70%
            );
            top: -50%;
            left: -50%;
            animation: rotate 20s linear infinite;
        }

        .icon-container i {
            font-size: 4rem;
            color: white;
            position: relative;
            z-index: 1;
            text-shadow: 0 4px 12px rgba(0,0,0,0.1);
            animation: floatIcon 3s ease-in-out infinite;
        }

        .card-body {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .card-title i {
            font-size: 1.25rem;
            color: var(--primary-color);
        }

        .card-text {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-size: 1rem;
        }

        .btn-primary i {
            font-size: 1.1rem;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 58, 255, 0.3);
        }

        .btn-primary:hover i {
            transform: translateX(5px);
        }

        footer {
            background: var(--gradient);
            color: white;
            padding: 1rem;
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .module-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(
                90deg,
                rgba(255,255,255, 0) 0%,
                rgba(255,255,255, 0.2) 50%,
                rgba(255,255,255, 0) 100%
            );
            transform: translateX(-100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .module-card:hover::after {
            animation: shimmer 0.8s ease-out;
            opacity: 1;
        }

        @media (max-width: 768px) {
            .welcome-section h2 {
                font-size: 2rem;
            }

            .module-card {
                margin-bottom: 2rem;
            }

            .icon-container {
                height: 180px;
            }

            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-hospital-user me-2"></i>
            CampusCare
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-circle me-2"></i>
                        <?php echo htmlspecialchars($fullName); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Dashboard Content -->
<div class="container mt-5">
    <div class="welcome-section text-center mb-5">
        <h2><i class="fas fa-smile me-2"></i>Welcome, <?php echo htmlspecialchars($fullName); ?>!</h2>
        <p class="text-muted">Select a option to proceed</p>
    </div>

    <div class="row justify-content-center mt-4 g-4">
        <!-- Lost and Found -->
        <div class="col-md-4">
            <div class="module-card">
                <div class="icon-container">
                    <i class="fas fa-search-location"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-search"></i>
                        Lost Items
                    </h5>
                    <p class="card-text">View for Lost Item within the campus</p>
                    <a href="lost_item.php" class="btn btn-primary">
                        Access Module
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Complaint Registration -->
        <div class="col-md-4">
            <div class="module-card">
                <div class="icon-container">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-exclamation-circle"></i>
                        Complaint Registration
                    </h5>
                    <p class="card-text">Report infrastructure-related issues or concerns</p>
                    <a href="complaints.php" class="btn btn-primary">
                        Access Module
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