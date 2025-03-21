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
            --primary-color: #6C63FF;
            --secondary-color: #4A90E2;
            --text-color: #2C3E50;
            --shadow-color: rgba(108, 99, 255, 0.2);
        }

        body {
            background: linear-gradient(135deg, #F5F7FA 0%, #E4EfF9 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            position: relative;
            padding-bottom: 60px;
        }

        .navbar {
            background: rgba(44, 62, 80, 0.95) !important;
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 600;
            color: #fff !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            color: var(--primary-color) !important;
        }

        .nav-link {
            color: #fff !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        .welcome-section {
            animation: fadeIn 0.6s ease-out;
        }

        .module-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px var(--shadow-color);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px var(--shadow-color);
        }

        .icon-container {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            transition: all 0.3s ease;
        }

        .icon-container i {
            font-size: 5rem;
            transition: all 0.3s ease;
        }

        .module-card:hover .icon-container i {
            transform: scale(1.1);
        }

        .card-img-top {
            display: none;
        }

        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 2rem;
        }

        .card-title {
            color: var(--text-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        footer {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 15px;
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .text-muted {
            color: #6c757d !important;
            margin-bottom: 2rem;
        }

        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .row.justify-content-center {
            margin-left: -15px;
            margin-right: -15px;
        }

        .col-md-4 {
            padding-left: 15px;
            padding-right: 15px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-university me-2"></i>College Maintenance</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Dashboard Content -->
<div class="container mt-5">
    <div class="welcome-section text-center mb-5">
        <h2><i class="fas fa-smile me-2"></i>Welcome, <?php echo htmlspecialchars($fullName); ?>!</h2>
        <p class="text-muted">Select a module to proceed</p>
    </div>

    <div class="row justify-content-center mt-4">
        <!-- Lost and Found -->
        <div class="col-md-4 mb-4">
            <div class="module-card h-100">
                <div class="icon-container">
                    <i class="fas fa-search-location"></i>
                </div>
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title"><i class="fas fa-search me-2"></i>Lost & Found</h5>
                    <p class="card-text flex-grow-1">Report or claim lost items.</p>
                    <a href="lost_item.php" class="btn btn-primary mt-auto"><i class="fas fa-arrow-right me-1"></i>Go to Lost & Found</a>
                </div>
            </div>
        </div>

        <!-- Complaint Registration -->
        <div class="col-md-4 mb-4">
            <div class="module-card h-100">
                <div class="icon-container">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title"><i class="fas fa-exclamation-circle me-2"></i>Complaint Registration</h5>
                    <p class="card-text flex-grow-1">Report infrastructure-related issues.</p>
                    <a href="dashboard.php" class="btn btn-primary mt-auto"><i class="fas fa-arrow-right me-1"></i>Go to Complaints</a>
             +
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