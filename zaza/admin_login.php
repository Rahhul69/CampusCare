<?php 
// Hardcoded admin credentials
$admin_email = "acharirahul11@gmail.com";
$admin_password = "rahul69"; // This will be hashed with MD5 for comparison

if(isset($_POST['adminLogin'])){
   $email = $_POST['email'];
   $password = $_POST['password'];
   $password = md5($password);
   
   if($email === $admin_email && $password === md5($admin_password)){
    session_start();
    $_SESSION['admin_email'] = $email;
    header("Location: admin_dashboard.php");
    exit();
   }
   else{
    echo "Not Found, Incorrect Email or Password";
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <form method="POST" action="">
            <h2 class="form-title">Admin Login</h2>
            
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
                <label>Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
                <label>Password</label>
            </div>

            <button type="submit" name="adminLogin" class="btn">Login</button>

            <div class="links">
                <a href="index.php">Back to Home</a>
            </div>
        </form>
    </div>
</body>
</html> 