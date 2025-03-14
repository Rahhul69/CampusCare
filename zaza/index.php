<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Maintenance System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="role-selection" id="roleSelection">
        <h1>Welcome to College Maintenance System</h1>
        <p>Please select your role to continue</p>
        <div class="role-buttons">
            <button class="role-btn" onclick="selectRole('student')">
                <i class="fas fa-user-graduate"></i>
                <span>Student</span>
            </button>
            <button class="role-btn" onclick="selectRole('faculty')">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Faculty</span>
            </button>
        </div>
        <div class="admin-link">
            <a href="admin_login.php">Admin Login</a>
        </div>
    </div>

    <!-- Existing containers with display:none -->
    <div class="container" id="signup" style="display:none;">
        <form method="post" action="register.php">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" 
                       name="fName" 
                       id="fName" 
                       pattern="[A-Za-z ]+" 
                       title="Please enter alphabets only"
                       placeholder="First Name" 
                       required>
                <span class="error-message" id="fNameError"></span>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" 
                       name="lName" 
                       id="lName" 
                       pattern="[A-Za-z ]+" 
                       title="Please enter alphabets only"
                       placeholder="Last Name" 
                       required>
                <span class="error-message" id="lNameError"></span>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="signupEmail" 
                       placeholder="Email (gmail.com or yahoo.com only)" 
                       title="Please use either gmail.com or yahoo.com email address"
                       required>
                <span class="error-message" id="signupEmailError"></span>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>
            <input type="submit" class="btn" value="Sign Up" name="signUp">
        </form>
    </div>

    <div class="container" id="signIn" style="display:none;">
        <h1 class="form-title">Sign In</h1>
        <form method="post" action="login.php" id="loginForm">
            <input type="hidden" name="selected_role" id="selected_role">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="signinEmail" 
                       placeholder="Email (gmail.com or yahoo.com only)" 
                       title="Please use either gmail.com or yahoo.com email address"
                       required>
                <span class="error-message" id="signinEmailError"></span>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn" name="login">Login</button>
        </form>
        <div class="links">
          <p>Don't have an account yet?</p>
          <button id="signUpButton">Sign Up</button>
        </div>
    </div>
      <style>
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .input-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .input-group select:focus {
            border-color: #007bff;
            outline: none;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .role-selection {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
        }

        .role-selection h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .role-selection p {
            color: #7f8c8d;
            margin-bottom: 2rem;
        }

        .role-buttons {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .role-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            border: 2px solid #007bff;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 160px;
        }

        .role-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .role-btn i {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 1rem;
        }

        .role-btn span {
            color: #2c3e50;
            font-weight: 600;
        }

        .admin-link {
            margin-top: 2rem;
        }

        .admin-link a {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .admin-link a:hover {
            color: #2c3e50;
        }

        .error-message {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .input-group input:invalid {
            border-color: #dc3545;
        }

        .input-group input:invalid + .error-message {
            display: block;
        }

        .input-group input:valid {
            border-color: #28a745;
        }
    </style>
      <script src="script.js"></script>
</body>
</html>
