<?php
session_start();
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Check if role is set in session
        if (!isset($_SESSION['selected_role'])) {
            throw new Exception("Please select your role first");
        }

        $fName = mysqli_real_escape_string($conn, $_POST['fName']);
        $lName = mysqli_real_escape_string($conn, $_POST['lName']);
        
        // Validate names (allow only alphabets and spaces)
        if (!preg_match("/^[A-Za-z ]+$/", $fName)) {
            throw new Exception("First name should contain only alphabets");
        }
        
        if (!preg_match("/^[A-Za-z ]+$/", $lName)) {
            throw new Exception("Last name should contain only alphabets");
        }
        
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];
        $role = $_SESSION['selected_role'];

        // Validate email format and domain
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        $domain = substr(strrchr($email, "@"), 1);
        $allowed_domains = ['gmail.com', 'yahoo.com'];
        
        if (!in_array($domain, $allowed_domains)) {
            throw new Exception("Please use either gmail.com or yahoo.com email address");
        }

        // Check if email already exists
        $check_query = "SELECT email FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Email already registered");
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert query with role
        $query = "INSERT INTO users (fName, lName, email, password, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $fName, $lName, $email, $hashed_password, $role);

        if (!$stmt->execute()) {
            throw new Exception("Registration failed: " . $stmt->error);
        }

        $_SESSION['success'] = "Registration successful! Please login.";
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: index.php');
        exit();
    }
}

// Remove or update the old login code as it uses MD5 which is insecure
?>