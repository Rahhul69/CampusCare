<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CampusCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --text-color: #333;
            --border-color: #ddd;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .dashboard-nav {
            background: white;
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .nav-items {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-logout {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background: var(--secondary-color);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .complaint-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .new-complaint, .my-complaints {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            margin-top: 0.5rem;
        }

        .form-group small {
            display: block;
            margin-top: 0.5rem;
            color: #666;
        }

        .btn-submit {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: var(--secondary-color);
        }

        .complaints-list {
            margin-top: 1rem;
        }

        h2 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .complaint-section {
                grid-template-columns: 1fr;
            }

            .dashboard-nav {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        .complaint-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: white;
        }

        .complaint-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .complaint-body {
            margin: 1rem 0;
        }

        .complaint-image {
            max-width: 100%;
            border-radius: 4px;
            margin-top: 1rem;
        }

        .complaint-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
        }

        .complaint-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .complaint-status.in-progress {
            background: #cce5ff;
            color: #004085;
        }

        .complaint-status.resolved {
            background: #d4edda;
            color: #155724;
        }

        .no-complaints {
            text-align: center;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <nav class="dashboard-nav">
        <div class="nav-brand">CampusCare</div>
        <div class="nav-items">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="complaint-section">
            <div class="new-complaint">
                <h2><i class="fas fa-plus-circle"></i> Register New Complaint</h2>
                <form action="submit_complaint.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="category"><i class="fas fa-tag"></i> Category</label>
                        <select name="category" id="category" required>
                            <option value="">Select Category</option>
                            <option value="electrical">Electrical</option>
                            <option value="plumbing">Plumbing</option>
                            <option value="furniture">Furniture</option>
                            <option value="cleaning">Cleaning</option>
                            <option value="others">Others</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                        <input type="text" name="location" id="location" required 
                               placeholder="e.g., Room 101, Main Building">
                    </div>

                    <div class="form-group">
                        <label for="description"><i class="fas fa-edit"></i> Description</label>
                        <textarea name="description" id="description" required 
                                  placeholder="Describe the issue in detail (minimum 20 characters)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image"><i class="fas fa-camera"></i> Upload Image (optional)</label>
                        <input type="file" name="image" id="image" accept="image/*">
                        <small>Max file size: 5MB</small>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Submit Complaint
                    </button>
                </form>
            </div>

            <div class="my-complaints">
                <h2><i class="fas fa-history"></i> Recent Complaints</h2>
                <div class="complaints-list">
                    <?php include 'fetch_complaints.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const description = document.getElementById('description').value;
                if (description.length < 20) {
                    e.preventDefault();
                    alert('Please provide a more detailed description (minimum 20 characters)');
                }
            });

            // Image preview
            const imageInput = document.getElementById('image');
            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    if (this.files[0].size > 5242880) { // 5MB
                        alert('File is too large. Maximum size is 5MB');
                        this.value = '';
                    }
                }
            });
        });
    </script>
</body>
</html>