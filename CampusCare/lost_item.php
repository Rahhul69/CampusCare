<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_item"])) {
    try {
        $item_name = filter_var($_POST["item_name"], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST["description"], FILTER_SANITIZE_STRING);
        $location_found = filter_var($_POST["location_found"], FILTER_SANITIZE_STRING);
        $date_found = $_POST["date_found"];
        $category = filter_var($_POST["category"], FILTER_SANITIZE_STRING);

        // Handle file upload
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed_types = ['image/jpeg', 'image/png'];
            $max_size = 2 * 1024 * 1024; // 2MB

            if (!in_array($_FILES["image"]["type"], $allowed_types)) {
                throw new Exception("Only JPG and PNG files are allowed.");
            }

            if ($_FILES["image"]["size"] > $max_size) {
                throw new Exception("File size must be less than 2MB.");
            }

            $target_dir = "uploads/lost_items/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $file_name;

            // Update the insert query to use reported_by
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $stmt = $conn->prepare("INSERT INTO lost_items (item_name, description, category, image_path, date_found, location_found, status, reported_by) VALUES (?, ?, ?, ?, ?, ?, 'unclaimed', ?)");
                $stmt->bind_param("ssssssi", $item_name, $description, $category, $target_file, $date_found, $location_found, $_SESSION['user_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to save item details.");
                }

                $_SESSION['success'] = "Item reported successfully!";
                header("Location: lost_item.php");
                exit();
            } else {
                throw new Exception("Failed to upload image.");
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Fetch items with filters
$category_filter = isset($_GET['category']) && !empty($_GET['category']) ? $_GET['category'] : '';

// Modified SQL query to show both admin and user reported items
$sql = "SELECT l.*, 
        CASE 
            WHEN l.reported_by IS NULL THEN 'Admin'
            ELSE CONCAT(u.fName, ' ', u.lName)
        END as reporter_name
        FROM lost_items l 
        LEFT JOIN users u ON l.reported_by = u.id 
        WHERE 1=1";

if ($category_filter) {
    $sql .= " AND l.category = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $category_filter);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found - Campus Care</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        body {
            background: linear-gradient(135deg, var(--light-bg) 0%, #E4EfF9 100%);
            font-family: 'Poppins', sans-serif;
            color: var(--dark-text);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 2.5rem;
            border-radius: 20px;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px var(--shadow-color);
            animation: fadeIn 0.5s ease;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow-color);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            animation: slideUp 0.5s ease;
        }

        .filter-input {
            padding: 0.8rem 1.2rem;
            border: 2px solid #eee;
            border-radius: 10px;
            flex: 1;
            min-width: 200px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            background-color: white;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }

        .filter-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--shadow-color);
            outline: none;
        }

        .filter-input option {
            padding: 10px;
        }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            animation: fadeIn 0.5s ease;
        }

        .item-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px var(--shadow-color);
        }

        .item-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .item-card:hover .item-image {
            transform: scale(1.05);
        }

        .item-info {
            padding: 1.5rem;
        }

        .item-info h3 {
            color: var(--dark-text);
            font-size: 1.25rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .item-info p {
            color: #666;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .item-info i {
            color: var(--primary-color);
            width: 20px;
        }

        .item-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 1rem;
        }

        .status-unclaimed {
            background-color: rgba(255, 193, 7, 0.2);
            color: #856404;
        }

        .status-claimed {
            background-color: rgba(40, 167, 69, 0.2);
            color: #155724;
        }

        .report-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            box-shadow: 0 5px 15px var(--shadow-color);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .report-btn:hover {
            transform: scale(1.1) rotate(180deg);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Back button */
        .back-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
.header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    padding: 1.5rem 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    animation: slideDown 0.5s ease-out;
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
    color: var(--primary);
    animation: pulse 2s infinite;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    letter-spacing: -0.5px;
}

.logo-subtitle {
    font-size: 0.9rem;
    color: var(--secondary);
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

/* Update existing body style */
body {
    padding-top: 90px; /* Add padding to account for fixed header */
    background: linear-gradient(135deg, #f5f7ff 0%, #e9f0ff 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    font-family: 'Poppins', sans-serif;
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
    </style>
</head>
<body>
<body>
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

    <!-- ...existing code... -->
    <div class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-search-location"></i> 
                Lost & Found Items
            </h1>
            <p>Help return lost items to their owners</p>
            <a href="<?php echo $_SESSION['role'] === 'student' ? 'student_dashboard.php' : 'faculty_dashboard.php'; ?>" 
               class="back-btn">
                <i class="fas fa-arrow-left"></i> 
                Back to Dashboard
            </a>
        </div>

        <div class="filters">
            <select class="filter-input" name="category" onchange="filterItems(this.value)">
                <option value="">All Categories</option>
                <option value="electronics" <?php echo $category_filter === 'electronics' ? 'selected' : ''; ?>>Electronics</option>
                <option value="documents" <?php echo $category_filter === 'documents' ? 'selected' : ''; ?>>Documents</option>
                <option value="accessories" <?php echo $category_filter === 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                <option value="others" <?php echo $category_filter === 'others' ? 'selected' : ''; ?>>Others</option>
            </select>
        </div>

        <div class="items-grid">
            <?php while ($item = $result->fetch_assoc()): ?>
            <div class="item-card">
                <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                     alt="<?= htmlspecialchars($item['item_name']) ?>" 
                     class="item-image">
                <div class="item-info">
                    <h3>
                        <i class="fas fa-box"></i> 
                        <?= htmlspecialchars($item['item_name']) ?>
                    </h3>
                    <p>
                        <i class="fas fa-align-left"></i>
                        <?= htmlspecialchars($item['description']) ?>
                    </p>
                    <p>
                        <i class="fas fa-map-marker-alt"></i>
                        <?= htmlspecialchars($item['location_found']) ?>
                    </p>
                    <p>
                        <i class="fas fa-calendar-alt"></i>
                        <?= date('d M Y', strtotime($item['date_found'])) ?>
                    </p>
                    <p>
                        <i class="fas fa-user-circle"></i>
                        Reported by: <?= htmlspecialchars($item['reporter_name']) ?>
                    </p>
                    <span class="item-status status-<?= $item['status'] ?>">
                        <i class="fas fa-<?= $item['status'] === 'claimed' ? 'check-circle' : 'clock' ?>"></i>
                        <?= ucfirst($item['status']) ?>
                    </span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        

        <div id="reportModal" class="modal">
            <!-- Report Modal -->
            <!-- Modal content here -->
        </div>
    </div>
    <script>
        function showReportModal() {
            document.getElementById('reportModal').classList.add('active');
        }

        // Add this inside your existing <script> tag
        function filterItems(category) {
            const currentUrl = new URL(window.location.href);
            
            if (category) {
                currentUrl.searchParams.set('category', category);
            } else {
                currentUrl.searchParams.delete('category');
            }
            
            window.location.href = currentUrl.toString();
        }

        // Add active class to selected category
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const category = urlParams.get('category');
            
            if (category) {
                const select = document.querySelector('select[name="category"]');
                select.value = category;
            }
        });
    </script>
</body>
</html>