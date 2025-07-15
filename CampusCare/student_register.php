<?php
session_start();
require_once 'connect.php';
require_once 'includes/Validator.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $validator = new Validator();
    
    // Define validation rules
    $rules = [
        'fName' => [
            'required' => true,
            'pattern' => '/^[A-Za-z\s]+$/',
            'message' => 'Name should contain only letters and spaces'
        ],
        'lName' => [
            'required' => true,
            'pattern' => '/^[A-Za-z\s]+$/',
            'message' => 'Name should contain only letters and spaces'
        ],
        'email' => [
            'required' => true,
            'email' => true,
            'pattern' => '/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
            'message' => 'Please enter a valid Gmail address'
        ],
        'student_id' => [
            'required' => true, 
            'pattern' => '/^[U][G][0-9]{6}$/',
            'message' => 'Student ID must be in format UG followed by 6 digits'
        ],
        'phone' => [
            'required' => true,
            'pattern' => '/^[0-9]{10}$/',
            'message' => 'Phone number must be 10 digits'
        ],
        'password' => [
            'required' => true,
            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,255}$/',
            'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be 8-255 characters long'
        ],
        'confirm_password' => [
            'required' => true,
            'match' => 'password',
            'message' => 'Passwords do not match'
        ]
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
    :root {
        --primary: #4A3AFF;
        --secondary: #6c757d;
        --success: #00C896;
        --info: #0dcaf0;
        --warning: #FFB64D;
        --danger: #FF5370;
        --light: #f8f9fa;
        --dark: #343a40;
        --card-shadow: 0 8px 24px rgba(21, 48, 142, 0.15);
        --input-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    /* New background animation */
    @keyframes gradientAnimation {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    /* Moving shapes animation */
    @keyframes float {
        0% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
        100% { transform: translateY(0) rotate(0deg); }
    }

    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding-top: 90px;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        /* Enhanced gradient background with animation */
        background: linear-gradient(-45deg, #4A3AFF, #6659ff, #5D87FF, #7F7FD5);
        background-size: 400% 400%;
        animation: gradientAnimation 15s ease infinite;
        position: relative;
        overflow-x: hidden;
    }
    
    /* Add floating shapes for dynamic background */
    body::before,
    body::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        z-index: -1;
    }
    
    body::before {
        top: -100px;
        right: -100px;
        animation: float 8s ease-in-out infinite;
    }
    
    body::after {
        bottom: -150px;
        left: -100px;
        width: 400px;
        height: 400px;
        animation: float 10s ease-in-out infinite reverse;
    }
    
    /* Additional floating elements */
    .bg-shape {
        position: absolute;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 40%;
        z-index: -1;
    }
    
    .shape1 {
        width: 200px;
        height: 200px;
        top: 20%;
        left: 10%;
        animation: float 12s ease-in-out infinite;
    }
    
    .shape2 {
        width: 150px;
        height: 150px;
        bottom: 15%;
        right: 10%;
        animation: float 9s ease-in-out infinite 1s;
    }
    
    .shape3 {
        width: 120px;
        height: 120px;
        top: 40%;
        right: 20%;
        animation: float 7s ease-in-out infinite 2s;
    }

    .auth-wrapper {
        animation: fadeIn 0.6s ease-out;
        padding: 2rem 1rem;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        position: relative;
        z-index: 1;
    }

    /* Enhanced glass effect for the auth content */
    .auth-content {
        max-width: 500px;
        width: 100%;
        /* Glass morphism effect */
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
        animation: slideIn 0.5s ease-out;
        margin: auto;
    }

    .card-title {
        color: var(--primary);
        font-size: 28px;
        font-weight: 700;
        text-align: center;
        margin-bottom: 2rem;
        position: relative;
        padding-bottom: 1rem;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: #fff;
        border-radius: 2px;
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
        color: var(--primary);
        font-size: 18px;
        transition: all 0.3s ease;
    }

    .form-control {
        width: 100%;
        padding: 12px 45px;
        height: 50px;
        font-size: 15px;
        color: var(--dark);
        background: rgba(255, 255, 255, 0.8);
        border: 2px solid rgba(255, 255, 255, 0.5);
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: var(--input-shadow);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(74, 58, 255, 0.15);
        background: rgba(255, 255, 255, 0.95);
    }

    .form-control:focus + i {
        color: var(--primary);
        transform: translateY(-50%) scale(1.1);
    }

    .form-text {
        color: rgba(255, 255, 255, 0.8);
        font-size: 13px;
        margin-top: 0.5rem;
        padding-left: 45px;
        transition: all 0.3s ease;
    }

    .btn {
        width: 100%;
        padding: 14px 28px;
        font-size: 16px;
        font-weight: 600;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-primary {
        background: linear-gradient(45deg, var(--primary), #6659ff);
        color: #fff;
        box-shadow: 0 4px 15px rgba(74, 58, 255, 0.35);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(74, 58, 255, 0.45);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .alert {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-size: 14px;
        animation: slideIn 0.4s ease-out;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .alert-danger {
        background: rgba(255, 83, 112, 0.15);
        border: 1px solid rgba(255, 83, 112, 0.3);
        color: #fff;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #fff;
        font-weight: 500;
        text-decoration: none;
        margin-top: 1.5rem;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        gap: 0.75rem;
        color: rgba(255, 255, 255, 0.8);
    }

    .password-requirements {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.25rem;
        margin: 0.75rem 0 1rem 45px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(4px);
    }

    .password-requirements ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .password-requirements li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        font-size: 0.813rem;
        color: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
    }

    .password-requirements li:last-child {
        margin-bottom: 0;
    }

    .password-requirements i {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 12px;
        flex-shrink: 0;
    }

    .password-requirements .text-success i {
        background: rgba(0, 200, 150, 0.2);
        color: var(--success);
    }

    .password-requirements .text-danger i {
        background: rgba(255, 83, 112, 0.2);
        color: var(--danger);
    }

    @media (max-width: 768px) {
        .auth-wrapper {
            padding: 1rem;
        }
        
        .auth-content {
            padding: 2rem;
            margin: 1rem;
        }
        
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }

    .form-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-group {
        position: relative;
    }

    .password-info-popup {
        position: absolute;
        left: calc(100% + 20px);  /* Position to the right of input */
        top: 50%;
        transform: translateY(-50%) scale(0.95);
        width: 300px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 1.25rem;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 100;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .password-info-popup.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(-50%) scale(1);
    }

    @media (max-width: 992px) {
        .password-info-popup {
            left: 0;
            top: calc(100% + 10px);
            transform: scale(0.95);
            width: 100%;
        }

        .password-info-popup.show {
            transform: scale(1);
        }
    }

    .info-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 14px;
    }

    .info-title i {
        font-size: 16px;
    }

    .requirement-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
    }

    .requirement-item i {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 10px;
        transition: all 0.3s ease;
    }

    .requirement-item span {
        flex: 1;
    }

    .requirement-item.valid {
        color: var(--success);
    }

    .requirement-item.invalid {
        color: var(--danger);
    }

    .requirement-item.valid i {
        color: var(--success);
        background-color: rgba(0, 200, 150, 0.2);
    }

    .requirement-item.invalid i {
        color: var(--danger);
        background-color: rgba(255, 83, 112, 0.2);
    }
 
    .header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        padding: 1.5rem 0;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        animation: slideDown 0.5s ease-out;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
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
        color: #fff;
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        animation: pulse 2s infinite;
    }

    .logo-text {
        display: flex;
        flex-direction: column;
    }

    .logo-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: -0.5px;
    }

    .logo-subtitle {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.8);
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

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: rgba(255, 255, 255, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        z-index: 2;
        transition: all 0.3s ease;
    }

    .password-toggle:hover {
        color: rgba(255, 255, 255, 0.9);
    }

    .password-toggle i {
        position: static;
        transform: none;
        left: auto;
    }

    .form-control[type="password"],
    .form-control[type="text"] {
        padding-right: 45px;
    }
    </style>
</head>
<body>
    <!-- Background shapes -->
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
                               placeholder="First Name" 
                               pattern="[A-Za-z\s]+"
                               title="Only letters and spaces allowed"
                               required>
                        <small class="form-text">Only letters and spaces allowed</small>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" name="lName" 
                               placeholder="Last Name" 
                               pattern="[A-Za-z\s]+"
                               title="Only letters and spaces allowed"
                               required>
                        <small class="form-text">Only letters and spaces allowed</small>
                    </div>
                </div>

                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" name="email" 
                           placeholder="Email" 
                           pattern="[a-zA-Z0-9._%+-]+@gmail\.com$"
                           title="Please enter a valid Gmail address"
                           required>
                    <small class="form-text">Only Gmail addresses are accepted</small>
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
                           placeholder="Phone Number" 
                           pattern="[0-9]{10}"
                           title="Phone number must be 10 digits"
                           required>
                    <small class="form-text">10-digit phone number</small>
                </div>

                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" name="password" 
                           placeholder="Password" 
                           pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,255}"
                           title="Password must meet all requirements"
                           required>
                    <span class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                    <div class="password-info-popup">
                        <div class="info-title">
                            <i class="fas fa-shield-alt"></i>
                            Password Requirements
                        </div>
                        <ul>
                            <li class="requirement-item">
                                <i class="fas fa-circle"></i>
                                <span>Minimum 8 characters</span>
                            </li>
                            <li class="requirement-item">
                                <i class="fas fa-circle"></i>
                                <span>At least one uppercase letter</span>
                            </li>
                            <li class="requirement-item">
                                <i class="fas fa-circle"></i>
                                <span>At least one lowercase letter</span>
                            </li>
                            <li class="requirement-item">
                                <i class="fas fa-circle"></i>
                                <span>At least one number</span>
                            </li>
                            <li class="requirement-item">
                                <i class="fas fa-circle"></i>
                                <span>At least one special character (@$!%*?&)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" name="confirm_password" 
                           placeholder="Confirm Password" required>
                    <span class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <button type="submit" class="btn btn-primary" name="register">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="student_login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="confirm_password"]');
        
        // Update the password validation JavaScript
        function validatePassword() {
            const value = password.value;
            const checks = {
                length: value.length >= 8,
                upper: /[A-Z]/.test(value),
                lower: /[a-z]/.test(value),
                number: /[0-9]/.test(value),
                special: /[@$!%*?&]/.test(value)
            };
            
            const requirements = document.querySelectorAll('.password-requirements li');
            requirements.forEach((req, index) => {
                const icon = req.querySelector('i');
                const text = req.querySelector('span');
                const isValid = Object.values(checks)[index];
                
                // Update icon
                icon.className = isValid ? 'fas fa-check' : 'fas fa-times';
                icon.parentElement.className = isValid ? 'text-success' : 'text-danger';
                
                // Animate transition
                req.style.transform = isValid ? 'translateX(5px)' : 'translateX(0)';
                req.style.opacity = isValid ? '0.7' : '1';
            });
            
            // Update input border color based on overall validity
            const isValid = Object.values(checks).every(check => check);
            password.style.borderColor = isValid ? 'var(--success)' : value ? 'var(--danger)' : 'rgba(255, 255, 255, 0.5)';
            password.style.boxShadow = isValid ? 
                '0 0 0 4px rgba(0, 200, 150, 0.15)' : 
                value ? '0 0 0 4px rgba(255, 83, 112, 0.15)' : 'var(--input-shadow)';
            
            return isValid;
        }
        
        function validatePasswordMatch() {
            const match = password.value === confirmPassword.value;
            confirmPassword.setCustomValidity(match ? '' : 'Passwords do not match');
        }
        
        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePasswordMatch);
        password.addEventListener('input', validatePasswordMatch);

        // Add this inside your existing DOMContentLoaded event listener
        const passwordInput = document.querySelector('input[name="password"]');
        const passwordPopup = document.querySelector('.password-info-popup');
        const requirementItems = document.querySelectorAll('.requirement-item');

        // Show popup on password field focus
        passwordInput.addEventListener('focus', () => {
            passwordPopup.classList.add('show');
        });

        // Hide popup when clicking outside
        document.addEventListener('click', (e) => {
            if (!passwordInput.contains(e.target) && !passwordPopup.contains(e.target)) {
                passwordPopup.classList.remove('show');
            }
        });

        // Update requirements as user types
        passwordInput.addEventListener('input', () => {
            const value = passwordInput.value;
            const requirements = [
                { regex: /.{8,}/, index: 0 }, // min 8 chars
                { regex: /[A-Z]/, index: 1 }, // uppercase
                { regex: /[a-z]/, index: 2 }, // lowercase
                { regex: /[0-9]/, index: 3 }, // number
                { regex: /[@$!%*?&]/, index: 4 } // special char
            ];

            requirements.forEach(({ regex, index }) => {
                const isValid = regex.test(value);
                const requirementItem = requirementItems[index];
                const icon = requirementItem.querySelector('i');

                requirementItem.classList.toggle('valid', isValid);
                requirementItem.classList.toggle('invalid', !isValid);
                icon.className = `fas fa-${isValid ? 'check' : 'times'}`;
            });
        });
    });

    // Add this function for password toggle
    function togglePassword(inputName) {
        const input = document.querySelector(`input[name="${inputName}"]`);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
    </script>
</body>
</html>