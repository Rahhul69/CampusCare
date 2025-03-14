<?php
session_start();
if(!isset($_SESSION['admin_email'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .dashboard-title {
            font-size: 24px;
            color: #2c3e50;
        }
        .logout-btn {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Admin Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-number">
                    <?php
                    try {
                        include 'connect.php';
                        $sql = "SELECT COUNT(*) as count FROM users";
                        $result = $conn->query($sql);
                        if ($result) {
                            $row = $result->fetch_assoc();
                            echo $row['count'];
                        } else {
                            echo "Error fetching data";
                        }
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage();
                    }
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Total Admins</h3>
                <div class="stat-number">
                    <?php
                    try {
                        $sql = "SELECT COUNT(*) as count FROM admins";
                        $result = $conn->query($sql);
                        if ($result) {
                            $row = $result->fetch_assoc();
                            echo $row['count'];
                        } else {
                            echo "Error fetching data";
                        }
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage();
                    }
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Recent Activity</h3>
                <p>Welcome to the admin dashboard. This is where you can manage your website.</p>
            </div>
        </div>
    </div>
    <?php
    if(isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>