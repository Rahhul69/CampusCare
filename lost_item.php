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
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Update the SQL query to use reported_by instead of user_id
$sql = "SELECT l.*, u.fName, u.lName FROM lost_items l 
        JOIN users u ON l.reported_by = u.id 
        WHERE 1=1";
if ($category_filter) $sql .= " AND l.category = ?";
if ($status_filter) $sql .= " AND l.status = ?";
if ($search) $sql .= " AND (l.item_name LIKE ? OR l.description LIKE ?)";
$sql .= " ORDER BY l.date_found DESC";

$stmt = $conn->prepare($sql);

// Bind parameters based on filters
$types = '';
$params = [];
if ($category_filter) { $types .= 's'; $params[] = $category_filter; }
if ($status_filter) { $types .= 's'; $params[] = $status_filter; }
if ($search) { 
    $types .= 'ss'; 
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($types) {
    $stmt->bind_param($types, ...$params);
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
    <style>
        :root {
            --primary: #4e54c8;
            --secondary: #8f94fb;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --light: #f5f6fa;
            --dark: #2d3436;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .page-header {
            background: var(--gradient);
            padding: 2rem;
            border-radius: 15px;
            color: white;
            margin-bottom: 2rem;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-input {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            flex: 1;
            min-width: 200px;
        }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .item-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .item-card:hover {
            transform: translateY(-5px);
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .item-info {
            padding: 1.5rem;
        }

        .item-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .status-unclaimed { background: var(--warning); color: white; }
        .status-claimed { background: var(--success); color: white; }

        .report-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--gradient);
            color: white;
            padding: 1rem;
            border-radius: 50%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .report-btn:hover {
            transform: scale(1.1);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-search"></i> Lost & Found Items</h1>
            <p>Help return lost items to their owners</p>
        </div>

        <div class="filters">
            <input type="text" class="filter-input" placeholder="Search items...">
            <select class="filter-input">
                <option value="">All Categories</option>
                <option value="electronics">Electronics</option>
                <option value="documents">Documents</option>
                <option value="accessories">Accessories</option>
                <option value="others">Others</option>
            </select>
            <select class="filter-input">
                <option value="">All Status</option>
                <option value="unclaimed">Unclaimed</option>
                <option value="claimed">Claimed</option>
            </select>
        </div>

        <div class="items-grid">
            <?php while ($item = $result->fetch_assoc()): ?>
            <div class="item-card">
                <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" class="item-image">
                <div class="item-info">
                    <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                    <p><?= htmlspecialchars($item['description']) ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['location_found']) ?></p>
                    <p><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($item['date_found'])) ?></p>
                    <span class="item-status status-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <button class="report-btn" onclick="showReportModal()">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <!-- Report Modal -->
    <div id="reportModal" class="modal">
        <!-- Modal content here -->
    </div>

    <script>
        function showReportModal() {
            document.getElementById('reportModal').classList.add('active');
        }
    </script>
</body>
</html>
