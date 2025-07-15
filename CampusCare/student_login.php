<?php
session_start();
$_SESSION['role'] = 'student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - College Maintenance System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #536dfe;
            --primary-light: #8599ff;
            --primary-dark: #1a43e8;
            --secondary: #6c757d;
            --success: #2ed8b6;
            --info: #738afe;
            --info-light: #a4b2ff;
            --warning: #FFB64D;
            --danger: #FF5370;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Poppins", sans-serif;
            font-size: 14px;
            color: #222;
            font-weight: 400;
            position: relative;
            line-height: 1.5;
            margin: 0;
            min-height: 100vh;
            background-color: #f0f4ff;
            overflow-x: hidden;
        }

        /* Dynamic Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .bg-animation span {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background: rgba(83, 109, 254, 0.2);
            animation: animate 25s linear infinite;
            bottom: -150px;
            border-radius: 50%;
        }

        .bg-animation span:nth-child(1) {
            left: 10%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
            animation-duration: 15s;
        }

        .bg-animation span:nth-child(2) {
            left: 20%;
            width: 40px;
            height: 40px;
            animation-delay: 2s;
            animation-duration: 20s;
        }

        .bg-animation span:nth-child(3) {
            left: 35%;
            width: 60px;
            height: 60px;
            animation-delay: 4s;
            animation-duration: 12s;
        }

        .bg-animation span:nth-child(4) {
            left: 50%;
            width: 100px;
            height: 100px;
            animation-delay: 0s;
            animation-duration: 18s;
        }

        .bg-animation span:nth-child(5) {
            left: 65%;
            width: 50px;
            height: 50px;
            animation-delay: 0s;
            animation-duration: 15s;
        }

        .bg-animation span:nth-child(6) {
            left: 75%;
            width: 90px;
            height: 90px;
            animation-delay: 3s;
            animation-duration: 12s;
        }

        .bg-animation span:nth-child(7) {
            left: 90%;
            width: 30px;
            height: 30px;
            animation-delay: 7s;
            animation-duration: 25s;
        }

        .bg-animation span:nth-child(8) {
            left: 30%;
            width: 70px;
            height: 70px;
            animation-delay: 15s;
            animation-duration: 45s;
        }

        @keyframes animate {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.8;
                border-radius: 30%;
            }
            50% {
                transform: translateY(-500px) rotate(180deg);
                opacity: 0.5;
                border-radius: 50%;
            }
            100% {
                transform: translateY(-1000px) rotate(360deg);
                opacity: 0;
                border-radius: 70%;
            }
        }

        /* Main gradient background */
        .bg-gradient {
            position: fixed;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #e6e9ff 0%, #c2c9ff 50%, #a4b2ff 100%);
            z-index: -2;
        }

        .auth-wrapper {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Glass effect for auth container */
        .auth-content {
            width: 400px;
            padding: 70px 40px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(83, 109, 254, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .auth-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(83, 109, 254, 0.3);
        }

        .auth-content .card-title {
            color: var(--primary);
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 16px;
            z-index: 1;
            transition: color 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px 10px 40px;
            height: 50px;
            font-size: 14px;
            color: #333;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(83, 109, 254, 0.25);
            outline: none;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control::placeholder {
            color: #6c757d;
            opacity: 0.8;
        }

        .btn {
            width: 100%;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 500;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 15px rgba(83, 109, 254, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(83, 109, 254, 0.4);
        }

        .text-center {
            text-align: center;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .text-muted {
            color: #555;
        }

        .text-primary {
            color: var(--primary);
            font-weight: 500;
        }

        a {
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: var(--primary-dark);
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            color: var(--secondary);
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(5px);
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.5);
            color: var(--primary);
        }

        .alert {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(5px);
            border-left: 4px solid var(--danger);
        }

        .alert-danger {
            color: var(--danger);
            background: rgba(255, 83, 112, 0.1);
        }
       
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 1.5rem 0;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 4px 20px rgba(83, 109, 254, 0.15);
            z-index: 1000;
            animation: slideDown 0.5s ease-out;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
        }

        .logo i {
            font-size: 2.5rem;
            color: var(--primary);
            animation: pulse 2s infinite;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.5px;
        }

        .logo-subtitle {
            font-size: 0.9rem;
            color: var(--secondary);
            font-weight: 500;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Update existing body style */
        body {
            padding-top: 90px; /* Add padding to account for fixed header */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        @media (max-width: 768px) {
            .header {
                padding: 1rem 0;
            }

            .header-content {
                padding: 0 1rem;
            }

            .logo i {
                font-size: 2rem;
            }

            .logo-title {
                font-size: 1.25rem;
            }

            .logo-subtitle {
                font-size: 0.8rem;
            }

            body {
                padding-top: 74px;
            }

            .auth-content {
                padding: 40px 30px;
                width: 90%;
            }
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            z-index: 2;
            pointer-events: auto;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-dark);
        }

        /* Add this new style for the password input to accommodate the icon */
        input[name="password"] {
            padding-right: 40px !important;
        }

        .password-toggle i {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="bg-animation">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-hospital-user"></i>
                <div class="logo-text">
                    <span class="logo-title">CampusCare</span>
                    <span class="logo-subtitle">A Unified College Maintenance System</span>
                </div>
            </a>
        </div>
    </header>
    
    <div class="auth-wrapper">
        <div class="auth-content">
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            <h3 class="card-title">Student Login</h3>
            <form method="post" action="login.php">
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" name="email" 
                           placeholder="Email" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" name="password" 
                           placeholder="Password" required>
                    <span class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <button type="submit" class="btn btn-primary" name="login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            <div class="text-center mt-4">
                <p class="text-muted mb-2">Don't have an account?</p>
                <a href="student_register.php" class="text-primary">Register Now</a>
            </div>
            <a href="select_role.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Role Selection
            </a>
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordInput = document.querySelector('input[name="password"]');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>