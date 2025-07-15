<?php
session_start();
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Validate email domain
    $domain = substr(strrchr($email, "@"), 1);
    $allowed_domains = ['gmail.com'];
    
    if (!in_array($domain, $allowed_domains)) {
        $_SESSION['error'] = "Please use only gmail.com address";
        header("Location: " . $_SESSION['role'] . "_login.php");
        exit();
    }

    // Check user credentials
    $query = "SELECT * FROM users WHERE email = ? AND role = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $_SESSION['role']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Set all necessary session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['fName'] . ' ' . $user['lName'];
            
            // Redirect based on role
            switch($user['role']) {
                case 'student':
                    header("Location: student_dashboard.php");
                    break;
                case 'faculty':
                    header("Location: faculty_dashboard.php");
                    break;
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid password";
        }
    } else {
        $_SESSION['error'] = "Email not found or invalid role";
    }
    
    // If login fails, redirect back to login page
    header("Location: " . $_SESSION['role'] . "_login.php");
    exit();
}

// If not POST request, redirect to role selection
header("Location: select_role.php");
exit();
?>
