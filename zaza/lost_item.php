<?php
session_start();
include 'connect.php';

// Debugging Session
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = false; // Default to non-admin
}

// Handle form submission (only for admin)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_item"]) && $_SESSION['admin']) {
    try {
        $item_name = mysqli_real_escape_string($conn, $_POST["item_name"]);
        $description = mysqli_real_escape_string($conn, $_POST["description"]);
        $location_found = mysqli_real_escape_string($conn, $_POST["location_found"]);
        $date_found = mysqli_real_escape_string($conn, $_POST["date_found"]);

        // Handle file upload
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES["image"]["type"], $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed.");
            }

            if ($_FILES["image"]["size"] > $max_size) {
                throw new Exception("File is too large. Maximum size is 5MB.");
            }

            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                throw new Exception("Failed to upload image.");
            }

            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO lost_items (item_name, description, image_path, date_found, location_found, status) VALUES (?, ?, ?, ?, ?, 'unclaimed')");
            $stmt->bind_param("sssss", $item_name, $description, $target_file, $date_found, $location_found);

            if (!$stmt->execute()) {
                throw new Exception("Failed to save item details.");
            }

            $_SESSION['success'] = "Lost item added successfully!";
            header("Location: lost_item.php");
            exit();
        } else {
            throw new Exception("Please upload an image.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: lost_item.php");
        exit();
    }
}

// Fetch lost items with pagination
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

$sql = "SELECT * FROM lost_items ORDER BY date_found DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found Items</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --text-color: #333;
            --border-color: #ddd;
        }

        .container {
            padding: 2rem;
        }

        .lost-items-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .lost-item {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .lost-item:hover {
            transform: translateY(-5px);
        }

        .lost-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .item-details {
            padding: 1rem;
        }

        .admin-form {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
        }

        .btn-submit {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-submit:hover {
            background: var(--secondary-color);
        }

        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Show Admin Form -->
        <?php if ($_SESSION['admin']): ?>
            <form class="admin-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="item_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label>Location Found</label>
                    <input type="text" name="location_found" required>
                </div>
                <div class="form-group">
                    <label>Date Found</label>
                    <input type="date" name="date_found" required>
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <button type="submit" name="add_item" class="btn-submit">
                    <i class="fas fa-plus"></i> Add Lost Item
                </button>
            </form>
        <?php endif; ?>

        <div class="lost-items-container">
            <?php while ($item = $result->fetch_assoc()): ?>
                <div class="lost-item">
                    <img src="<?= htmlspecialchars($item['image_path']); ?>" alt="<?= htmlspecialchars($item['item_name']); ?>">
                    <div class="item-details">
                        <h3><?= htmlspecialchars($item['item_name']); ?></h3>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
