<?php
session_start();
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Validate email domain
        $domain = substr(strrchr($email, "@"), 1);
        $allowed_domains = ['gmail.com', 'yahoo.com'];
        
        if (!in_array($domain, $allowed_domains)) {
            throw new Exception("Please use either gmail.com or yahoo.com email address");
        }
        
        $password = $_POST['password'];
        
        // Debug output
        error_log("Login attempt - Email: $email, Role: " . $_SESSION['selected_role']);
        
        // Query to check user credentials without role check first
        $query = "SELECT * FROM users WHERE email=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Debug output
            error_log("User found - DB Role: " . $user['role'] . ", Session Role: " . $_SESSION['selected_role']);
            
            if (password_verify($password, $user['password'])) {
                if ($user['role'] == $_SESSION['selected_role']) {
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['fname'] . ' ' . $user['lname'];
                    
                    // Debug output
                    error_log("Login successful - Redirecting to " . ($user['role'] == 'student' ? 'student_dashboard.php' : 'faculty_dashboard.php'));
                    
                    // Redirect based on role
                    if ($user['role'] == 'student') {
                        header('Location: student_dashboard.php');
                    } else if ($user['role'] == 'faculty') {
                        header('Location: faculty_dashboard.php');
                    }
                    exit();
                } else {
                    $_SESSION['error'] = "Invalid role selected for this account";
                    error_log("Role mismatch - User tried to login as " . $_SESSION['selected_role'] . " but account is " . $user['role']);
                }
            } else {
                $_SESSION['error'] = "Invalid password";
                error_log("Password verification failed");
            }
        } else {
            $_SESSION['error'] = "Email not found";
            error_log("No user found with email: $email");
        }
        
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        error_log("Exception: " . $e->getMessage());
        header('Location: index.php');
        exit();
    }
}
?>
