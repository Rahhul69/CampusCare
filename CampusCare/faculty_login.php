<?php
session_start();
$_SESSION['role'] = 'faculty';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<body>
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>
    <div class="bg-shape shape3"></div>
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-hospital-user"></i>
                <div class="logo-text">
                    <span class="logo-title">CampusCare</span>
                    <span class="logo-subtitle">Maintenance System</span>
                </div>
            </a>
        </div>
    </header>

    <!-- ...existing code... -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Login - College Maintenance System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #3d5afe;
            --primary-dark:rgb(91, 128, 237);
            --primary-light: #8187ff;
            --secondary: #6c757d;
            --success: #2ed8b6;
            --info: #536dfe;
            --info-light: #8699fe;
            --warning: #FFB64D;
            --danger: #FF5370;
            --light: #f8f9fa;
            --dark: #343a40;
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px 0 rgba(61, 90, 254, 0.37);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            padding-top: 90px;
            background: linear-gradient(135deg,rgb(112, 130, 234) 0%,rgb(125, 144, 255) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        .auth-wrapper {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-content {
            width: 400px;
            padding: 40px;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            position: relative;
            z-index: 1;
        }

        .card-title {
            color: #fff;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px 10px 40px;
            height: 45px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(61, 90, 254, 0.25);
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .auth-links {
            text-align: center;
            margin-top: 20px;
        }

        .auth-links p {
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .auth-links a {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .auth-links a:hover {
            color: var(--primary);
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--secondary);
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: var(--primary-light);
        }
.header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    padding: 1.5rem 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
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

/* Add these new styles for background shapes */
.bg-shape {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(45deg, var(--primary) 0%, var(--info) 100%);
    filter: blur(80px);
    opacity: 0.4;
    z-index: 0;
}

.shape1 {
    width: 500px;
    height: 500px;
    top: -250px;
    right: -100px;
    animation: float 8s ease-in-out infinite;
}

.shape2 {
    width: 400px;
    height: 400px;
    bottom: -200px;
    left: -100px;
    animation: float 10s ease-in-out infinite;
    animation-delay: -2s;
}

.shape3 {
    width: 300px;
    height: 300px;
    top: 50%;
    right: 20%;
    animation: float 12s ease-in-out infinite;
    animation-delay: -5s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-20px) scale(1.05);
    }
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
}
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-content">
            <h3 class="card-title">
                <i class="fas fa-user-tie"></i> Faculty Login
            </h3>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

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
                </div>

                <button type="submit" class="btn btn-primary" name="login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

           

            <div class="back-link">
                <a href="select_role.php">
                    <i class="fas fa-arrow-left"></i> Back to Role Selection
                </a>
            </div>
        </div>
    </div>
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>
    <div class="bg-shape shape3"></div>
</body>
</html>