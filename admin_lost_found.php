<?php
session_start();
require_once 'connect.php';

// Add this after your database connection
$categories = [
    'electronics' => 'Electronics',
    'documents' => 'Documents',
    'accessories' => 'Accessories',
    'others' => 'Others'
];

// Check if user is admin
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle item status updates
if(isset($_POST['update_status'])) {
    $item_id = filter_var($_POST['item_id'], FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
    $admin_id = $_SESSION['admin_id'];
    
    $stmt = $conn->prepare("UPDATE lost_items SET 
        status = ?, 
        updated_by = ?, 
        updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?");
    $stmt->bind_param("sii", $status, $admin_id, $item_id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Item status updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update item status";
    }
    
    header("Location: admin_lost_found.php");
    exit();
}

// Handle item deletion
if(isset($_POST['delete_item'])) {
    $item_id = filter_var($_POST['item_id'], FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $conn->prepare("SELECT image_path FROM lost_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    // Delete image file if exists
    if($item && file_exists($item['image_path'])) {
        unlink($item['image_path']);
    }
    
    $stmt = $conn->prepare("DELETE FROM lost_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Item deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete item";
    }
    
    header("Location: admin_lost_found.php");
    exit();
}

// Update the SELECT query to properly handle admin submissions
$sql = "SELECT l.*, 
        CASE 
            WHEN l.reported_by IS NULL OR l.reported_by = 0 THEN 'Admin'
            ELSE CONCAT(COALESCE(u.fName, ''), ' ', COALESCE(u.lName, ''))
        END as reporter_name
        FROM lost_items l 
        LEFT JOIN users u ON l.reported_by = u.id AND l.reported_by > 0
        ORDER BY l.date_found DESC";
$result = $conn->query($sql);

// Handle form submission for new items
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_item"])) {
    try {
        $item_name = $_POST["item_name"];
        $description = $_POST["description"];
        $location_found = $_POST["location_found"];
        $date_found = $_POST["date_found"];
        $category = $_POST["category"];
        $status = 'unclaimed'; // Default status
        
        // File upload handling
        if (!isset($_FILES["image"]) || $_FILES["image"]["error"] != 0) {
            throw new Exception("Please select an image file.");
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES["image"]["type"], $allowed_types)) {
            throw new Exception("Only JPG, JPEG and PNG files are allowed.");
        }

        if ($_FILES["image"]["size"] > $max_size) {
            throw new Exception("File size must be less than 5MB.");
        }

        // Create upload directory if it doesn't exist
        $target_dir = "uploads/lost_items/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        // Upload file
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            throw new Exception("Failed to upload image.");
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO lost_items (item_name, description, category, image_path, date_found, location_found, status, reported_by) VALUES (?, ?, ?, ?, ?, ?, ?, NULL)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssssss", 
            $item_name, 
            $description, 
            $category, 
            $target_file, 
            $date_found, 
            $location_found, 
            $status
        );

        if (!$stmt->execute()) {
            // If insert fails, delete uploaded image
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            throw new Exception("Failed to save item details: " . $stmt->error);
        }

        $_SESSION['success'] = "Item added successfully!";
        header("Location: admin_lost_found.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: admin_lost_found.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lost & Found Items - Admin Dashboard</title>
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

        .container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            background: var(--gradient);
            padding: 2rem;
            border-radius: 15px;
            color: white;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--light);
        }

        th {
            background: var(--dark);
            color: white;
        }

        tr:hover {
            background: var(--light);
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: var(--success);
            color: white;
        }

        .alert-error {
            background: var(--danger);
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 10px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .file-upload {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .file-upload label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .image-preview {
            margin-top: 1rem;
        }

        .image-preview img {
            max-width: 100%;
            border-radius: 5px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .btn-secondary {
            background: var(--dark);
            color: white;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        /* Add to your existing CSS */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: var(--danger);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.1);
            outline: none;
        }

        .file-upload {
            position: relative;
            margin-bottom: 1rem;
        }

        .file-upload input[type="file"] {
            position: absolute;
            left: -9999px;
        }

        .file-upload label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: var(--light);
            border: 2px dashed #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload label:hover {
            border-color: var(--primary);
            background: rgba(78, 84, 200, 0.05);
        }

        .image-preview {
            max-width: 200px;
            margin-top: 1rem;
        }

        .image-preview img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-secondary {
            background: var(--light);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        /* Update the modal CSS */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.active {
            display: flex;
            opacity: 1;
            visibility: visible;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
            position: relative;
        }

        .modal.active .modal-content {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-search"></i> Manage Lost & Found Items</h1>
                <p>Manage and track all reported items</p>
            </div>
            <button class="btn btn-primary" onclick="showModal()">
                <i class="fas fa-plus"></i> Add New Item
            </button>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Item Name</th>
                        <th>Location</th>
                        <th>Date Found</th>
                        <th>Reported By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($item['item_name']) ?>" 
                                     class="item-image">
                            </td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['location_found']) ?></td>
                            <td><?= date('d M Y', strtotime($item['date_found'])) ?></td>
                            <td><?= htmlspecialchars($item['reporter_name']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" name="delete_item" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add this inside the modal div -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Add Lost Item</h2>
                <button type="button" class="close-modal" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="addItemForm">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="item_name" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
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
                    <label>Item Image</label>
                    <div class="file-upload">
                        <input type="file" name="image" id="imageUpload" accept="image/*" required>
                        <label for="imageUpload">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Choose Image</span>
                        </label>
                    </div>
                    <div id="imagePreview" class="image-preview"></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="add_item" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add to your existing JavaScript
        function showModal() {
            document.getElementById('reportModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('reportModal').classList.remove('active');
        }

        // Image preview
        document.getElementById('imageUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            }
        });

        // Close modal when clicking outside
        document.getElementById('reportModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        const modal = document.getElementById('reportModal');
        const modalContent = modal.querySelector('.modal-content');
        const imageUpload = document.getElementById('imageUpload');
        const imagePreview = document.getElementById('imagePreview');

        function showModal() {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
            // Reset form and preview
            document.getElementById('addItemForm').reset();
            imagePreview.innerHTML = '';
        }

        // Image preview handler
        imageUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            }
        });

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });

        // Prevent modal content clicks from closing modal
        modalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    </script>
</body>
</html>
