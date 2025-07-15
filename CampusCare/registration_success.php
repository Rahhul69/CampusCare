<?php
session_start();

// Redirect if no success data
if (!isset($_SESSION['registration_data'])) {
    header('Location: student_register.php');
    exit();
}

$registration_data = $_SESSION['registration_data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - CampusCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #4A3AFF;
            --success: #00C896;
            --secondary: #6c757d;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(-45deg, #4A3AFF, #6659ff, #5D87FF, #7F7FD5);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            padding: 20px;
        }

        .success-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideIn 0.5s ease-out;
        }

        .success-icon {
            font-size: 5rem;
            color: var(--success);
            margin-bottom: 1.5rem;
            animation: scaleIn 0.5s ease-out;
        }

        .title {
            color: #fff;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .credentials-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeIn 0.5s ease-out 0.3s both;
        }

        .credential-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .credential-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .credential-item i {
            width: 24px;
            margin-right: 12px;
            color: rgba(255, 255, 255, 0.9);
        }

        .credential-item span {
            color: #fff;
            text-align: left;
        }

        .login-btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            margin-top: 1rem;
            animation: fadeIn 0.5s ease-out 0.6s both;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 58, 255, 0.35);
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="success-card">
        <i class="fas fa-check-circle success-icon"></i>
        <h1 class="title">Registration Successful!</h1>
        
        <div class="credentials-box">
            <div class="credential-item">
                <i class="fas fa-user"></i>
                <span><?php echo htmlspecialchars($registration_data['fName'] . ' ' . $registration_data['lName']); ?></span>
            </div>
            <div class="credential-item">
                <i class="fas fa-envelope"></i>
                <span><?php echo htmlspecialchars($registration_data['email']); ?></span>
            </div>
            <div class="credential-item">
                <i class="fas fa-id-card"></i>
                <span><?php echo htmlspecialchars($registration_data['student_id']); ?></span>
            </div>
        </div>

        <a href="student_login.php" class="login-btn">
            <i class="fas fa-sign-in-alt"></i>
            Proceed to Login
        </a>
    </div>
</body>
</html>