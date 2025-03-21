<?php
session_start();
require_once 'connect.php';
require_once 'includes/Validator.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $validator = new Validator();
    
    // Define validation rules
    $rules = [
        'fName' => ['required' => true],
        'lName' => ['required' => true],
        'email' => ['required' => true, 'email' => true],
        'student_id' => [
            'required' => true, 
            'pattern' => '/^[U][G][0-9]{6}$/'  // Format: UG followed by 6 digits
        ],
        'phone' => ['required' => true, 'pattern' => '/^[0-9]{10}$/'],
        'password' => ['required' => true, 'min' => 8],
        'confirm_password' => ['required' => true, 'match' => 'password']
    ];

    if ($validator->validate($_POST, $rules)) {
        try {
            $conn->begin_transaction();

            // Check for existing email or student ID
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
            $stmt->bind_param("ss", $_POST['email'], $_POST['student_id']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("Email or Student ID already registered");
            }

            // Generate verification token
            $verification_token = bin2hex(random_bytes(32));
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (fName, lName, email, student_id, phone, password, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", 
                $_POST['fName'],
                $_POST['lName'],
                $_POST['email'],
                $_POST['student_id'],
                $_POST['phone'],
                $password_hash,
                $verification_token
            );

            if ($stmt->execute()) {
                // Send verification email (implement this part)
                // mail($_POST['email'], "Verify your account", "Token: $verification_token");
                
                $conn->commit();
                $_SESSION['success'] = "Registration successful! Please check your email to verify your account.";
                header("Location: student_login.php");
                exit();
            } else {
                throw new Exception("Registration failed");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['errors'] = $validator->getErrors();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - College Maintenance System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Copy existing CSS variables and base styles from student_login.php */
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
            width: 450px;
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

        .alert {
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background-color: var(--danger);
            color: white;
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            color: var(--secondary);
            font-size: 14px;
            text-decoration: none;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .form-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 12px;
            color: var(--secondary);
            padding-left: 40px;
        }

        .form-control:invalid {
            border-color: var(--danger);
        }

        .form-control:invalid:focus {
            border-color: var(--danger);
            box-shadow: 0 0 0 0.2rem rgba(255, 83, 112, 0.25);
        }
    </style>
</head>
<body>
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

            <h3 class="card-title">Student Registration</h3>
            <form method="post" action="">
                <!-- Add this after your form's opening tag -->
                <?php if(isset($_SESSION['errors'])): ?>
                    <?php foreach($_SESSION['errors'] as $error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" name="fName" 
                               placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" name="lName" 
                               placeholder="Last Name" required>
                    </div>
                </div>

                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" name="email" 
                           placeholder="Email" required>
                </div>

                <div class="form-group">
                    <i class="fas fa-id-card"></i>
                    <input type="text" class="form-control" name="student_id" 
                           placeholder="Student ID (e.g., UG224756)" 
                           pattern="^[U][G][0-9]{6}$"
                           title="Format: UG followed by 6 digits (e.g., UG224756)"
                           required>
                    <small class="form-text">
                        Format: UG followed by 6 digits (Example: UG224756)
                    </small>
                </div>

                <div class="form-group">
                    <i class="fas fa-phone"></i>
                    <input type="tel" class="form-control" name="phone" 
                           placeholder="Phone Number" required>
                </div>

                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" name="password" 
                           placeholder="Password" required>
                </div>

                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" name="confirm_password" 
                           placeholder="Confirm Password" required>
                </div>

                <button type="submit" class="btn btn-primary" name="register">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>

            <a href="student_login.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>