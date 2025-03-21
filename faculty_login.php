<?php
session_start();
$_SESSION['role'] = 'faculty';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Login - College Maintenance System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #783392;
            --secondary: #6c757d;
            --success: #2ed8b6;
            --info: #98469A;
            --info-light: #9966cc;
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
            background: #f6f7fb;
            position: relative;
            line-height: 1.5;
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
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: var(--primary);
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
            color: var(--secondary);
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px 10px 40px;
            height: 45px;
            font-size: 14px;
            color: #333;
            border: 1px solid #e4e9f0;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(120, 51, 146, 0.25);
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
            background: var(--info);
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
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .auth-links a:hover {
            color: var(--info);
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
            color: var(--primary);
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
</body>
</html>