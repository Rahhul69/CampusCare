<?php
session_start();
require_once 'connect.php';

// Check if user is admin
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle faculty addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_faculty'])) {
    try {
        // Validate all required fields are present
        $required_fields = ['fName', 'lName', 'email', 'password', 'confirm_password'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("All fields are required");
            }
        }

        $fName = filter_var($_POST['fName'], FILTER_SANITIZE_STRING);
        $lName = filter_var($_POST['lName'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Enhanced validation
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        if (!$email) {
            throw new Exception("Invalid email format");
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already registered");
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert faculty with simplified fields
        $stmt = $conn->prepare("INSERT INTO users (fName, lName, email, password, role, status) VALUES (?, ?, ?, ?, 'faculty', 'active')");
        $stmt->bind_param("ssss", $fName, $lName, $email, $password_hash);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Faculty member added successfully";
            header("Location: admin_faculty.php");
            exit();
        } else {
            throw new Exception("Failed to add faculty member");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Add this with other POST handlers at the top of the file
// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    try {
        $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
        $password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_new_password'];

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'faculty'");
        $stmt->bind_param("si", $password_hash, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Password updated successfully";
            header("Location: admin_faculty.php");
            exit();
        } else {
            throw new Exception("Failed to update password");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Add this after the existing POST handler for adding faculty
// Handle edit faculty
if (isset($_GET['edit'])) {
    $edit_id = filter_var($_GET['edit'], FILTER_SANITIZE_NUMBER_INT);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'faculty'");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_faculty = $stmt->get_result()->fetch_assoc();
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_faculty'])) {
    try {
        $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
        $fName = filter_var($_POST['fName'], FILTER_SANITIZE_STRING);
        $lName = filter_var($_POST['lName'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

        // Check if email exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already registered to another user");
        }

        // Update user
        $stmt = $conn->prepare("UPDATE users SET fName = ?, lName = ?, email = ? WHERE id = ? AND role = 'faculty'");
        $stmt->bind_param("sssi", $fName, $lName, $email, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Faculty member updated successfully";
            header("Location: admin_faculty.php");
            exit();
        } else {
            throw new Exception("Failed to update faculty member");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Replace the existing delete handler
if(isset($_GET['delete'])) {
    try {
        $delete_id = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);
        
        // First check if faculty exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'faculty'");
        if(!$check_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $check_stmt->bind_param("i", $delete_id);
        if(!$check_stmt->execute()) {
            throw new Exception("Execute failed: " . $check_stmt->error);
        }
        
        $result = $check_stmt->get_result();
        if($result->num_rows === 0) {
            throw new Exception("Faculty member not found");
        }
        
        // Delete the faculty member
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'faculty'");
        if(!$delete_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $delete_stmt->bind_param("i", $delete_id);
        if(!$delete_stmt->execute()) {
            throw new Exception("Delete failed: " . $delete_stmt->error);
        }
        
        if($delete_stmt->affected_rows > 0) {
            $_SESSION['success'] = "Faculty member deleted successfully";
        } else {
            throw new Exception("No faculty member was deleted");
        }
        
        $delete_stmt->close();
        $check_stmt->close();
        
        header("Location: admin_faculty.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
        header("Location: admin_faculty.php");
        exit();
    }
}

// Fetch all faculty members
$sql = "SELECT * FROM users WHERE role = 'faculty' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6C63FF;
            --secondary-color: #4A90E2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #F5F7FA;
            --dark-text: #2C3E50;
            --shadow-color: rgba(108, 99, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #E4EfF9 100%);
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow-color);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .page-header {
            margin: -2rem -2rem 2rem -2rem;
            padding: 2rem;
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 20px 20px 0 0;
            animation: fadeIn 0.5s ease-out;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.75rem;
            margin: 0;
        }

        .page-header .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .page-header .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transition: all 0.5s ease;
        }

        .btn:hover::after {
            left: 100%;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            animation: slideInDown 0.5s ease-out;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #721c24;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .faculty-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .add-faculty-form {
            background: linear-gradient(145deg, #fff, #f8f9fa);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .add-faculty-form::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(108, 99, 255, 0.03) 0%, rgba(255,255,255,0) 70%);
            animation: rotate 15s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .add-faculty-form h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: var(--primary);
            width: 16px;
            text-align: center;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem 2.5rem;
            border: 2px solid #e4e9f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group .input-icon {
            position: absolute;
            left: 1rem;
            top: 2.75rem;
            color: var(--secondary-color);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--shadow-color);
            outline: none;
        }

        .form-group input:focus + .input-icon {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .faculty-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .faculty-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .faculty-card:hover::before {
            transform: scaleX(1);
        }

        .faculty-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px var(--shadow-color);
        }

        .faculty-card h4 {
            color: var(--primary-color);
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .faculty-info {
            margin-bottom: 1rem;
        }

        .faculty-info p {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary);
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
        }

        .faculty-info i {
            color: var(--primary-color);
            width: 20px;
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            position: relative;
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideInDown 0.3s ease-out;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-warning {
            background: var(--warning-color);
            color: #856404;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .form-group input[type="password"] {
            padding-right: 45px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h2>
                <i class="fas fa-user-tie"></i>
                <span>Manage Faculty</span>
            </h2>
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="add-faculty-form">
            <h3>
                <i class="fas <?= isset($edit_faculty) ? 'fa-user-edit' : 'fa-user-plus' ?>"></i>
                <?= isset($edit_faculty) ? 'Edit Faculty Member' : 'Add New Faculty Member' ?>
            </h3>
            <form method="post" class="needs-validation" novalidate>
                <?php if(isset($edit_faculty)): ?>
                    <input type="hidden" name="id" value="<?= $edit_faculty['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> First Name</label>
                    <input type="text" 
                           name="fName" 
                           class="form-control"
                           required
                           minlength="2"
                           pattern="[A-Za-z ]+"
                           title="Please enter a valid first name"
                           value="<?= isset($edit_faculty) ? htmlspecialchars($edit_faculty['fName']) : 
                                  (isset($_POST['fName']) ? htmlspecialchars($_POST['fName']) : '') ?>">
                    <i class="fas fa-user-edit input-icon"></i>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Last Name</label>
                    <input type="text" 
                           name="lName" 
                           class="form-control"
                           required
                           minlength="2"
                           pattern="[A-Za-z ]+"
                           title="Please enter a valid last name"
                           value="<?= isset($edit_faculty) ? htmlspecialchars($edit_faculty['lName']) : 
                                  (isset($_POST['lName']) ? htmlspecialchars($_POST['lName']) : '') ?>">
                    <i class="fas fa-user-edit input-icon"></i>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" 
                           name="email" 
                           class="form-control"
                           required
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                           title="Please enter a valid email address"
                           value="<?= isset($edit_faculty) ? htmlspecialchars($edit_faculty['email']) : 
                                  (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '') ?>">
                    <i class="fas fa-at input-icon"></i>
                </div>

                <?php if(!isset($edit_faculty)): ?>
                    <!-- Password fields only for new faculty -->
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="password" 
                               name="password" 
                               class="form-control"
                               required
                               minlength="8"
                               title="Password must be at least 8 characters long">
                        <i class="fas fa-key input-icon"></i>
                        <span class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-shield-alt"></i> Confirm Password</label>
                        <input type="password" 
                               name="confirm_password" 
                               class="form-control"
                               required
                               minlength="8"
                               title="Please confirm your password">
                        <i class="fas fa-check-circle input-icon"></i>
                        <span class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                <?php endif; ?>

                <button type="submit" name="<?= isset($edit_faculty) ? 'update_faculty' : 'add_faculty' ?>" 
                        class="btn btn-primary">
                    <i class="fas <?= isset($edit_faculty) ? 'fa-save' : 'fa-plus' ?>"></i>
                    <?= isset($edit_faculty) ? 'Update Faculty' : 'Add Faculty' ?>
                </button>

                <?php if(isset($edit_faculty)): ?>
                    <a href="admin_faculty.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="faculty-grid">
            <?php while($faculty = $result->fetch_assoc()): ?>
                <div class="faculty-card">
                    <h4><?= htmlspecialchars($faculty['fName'] . ' ' . $faculty['lName']) ?></h4>
                    <div class="faculty-info">
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($faculty['email']) ?></p>
                        <p><i class="fas fa-clock"></i> Added: <?= date('M d, Y', strtotime($faculty['created_at'])) ?></p>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary" onclick="editFaculty(<?= $faculty['id'] ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-warning" onclick="resetPassword(<?= $faculty['id'] ?>)">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                        <button class="btn btn-danger" onclick="deleteFaculty(<?= $faculty['id'] ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-key"></i> Reset Password</h3>
            <form method="post" class="reset-password-form">
                <input type="hidden" name="id" id="faculty_id">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" 
                           name="new_password" 
                           required 
                           minlength="8"
                           class="form-control">
                    <i class="fas fa-key input-icon"></i>
                    <span class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-shield-alt"></i> Confirm New Password</label>
                    <input type="password" 
                           name="confirm_new_password" 
                           required 
                           minlength="8"
                           class="form-control">
                    <i class="fas fa-check-circle input-icon"></i>
                    <span class="password-toggle" onclick="togglePassword('confirm_new_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="modal-actions">
                    <button type="submit" name="reset_password" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editFaculty(id) {
            window.location.href = `admin_faculty.php?edit=${id}`;
        }

        // Replace the existing deleteFaculty function
        function deleteFaculty(id) {
            if(confirm('Are you sure you want to delete this faculty member?\nThis action cannot be undone.')) {
                // Create form and submit it
                const form = document.createElement('form');
                form.method = 'GET';
                form.action = 'admin_faculty.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete';
                input.value = id;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Add to your existing <script> tag
        function resetPassword(id) {
            document.getElementById('faculty_id').value = id;
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('passwordModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Add to your existing <script> tag
        function togglePassword(inputName) {
            const input = document.querySelector(`input[name="${inputName}"]`);
            const icon = input.nextElementSibling.nextElementSibling.querySelector('i');
            
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
