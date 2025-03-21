<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role - College Maintenance System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .role-selection {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        p {
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
            text-decoration: none;
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
    </style>
</head>
<body>
    <div class="role-selection">
        <h1>Welcome to College Maintenance System</h1>
        <p>Please select your role to continue</p>
        <div class="role-buttons">
            <a href="student_login.php" class="role-btn">
                <i class="fas fa-user-graduate"></i>
                <span>Student</span>
            </a>
            <a href="faculty_login.php" class="role-btn">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Faculty</span>
            </a>
        </div>
        <div class="admin-link">
            <a href="admin_login.php">Admin Login</a>
        </div>
    </div>
</body>
</html>