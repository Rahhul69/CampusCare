<?php
session_start();
require_once 'connect.php';

// Check authentication and role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
        $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        
        // Handle file upload
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/complaints/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $image_path = $upload_dir . $file_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                throw new Exception('Failed to upload image');
            }
        }
        
        // Insert complaint
        $stmt = $conn->prepare("INSERT INTO complaints (user_id, category, location, description, image_path, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param('issss', $_SESSION['user_id'], $category, $location, $description, $image_path);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to submit complaint');
        }
        
        $_SESSION['success'] = 'Complaint submitted successfully!';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Get complaint statistics
function getComplaintStats($conn, $user_id) {
    $stats = [];
    $statuses = ['total', 'pending', 'in_progress', 'resolved'];
    
    foreach ($statuses as $status) {
        $sql = $status === 'total' 
            ? "SELECT COUNT(*) as count FROM complaints WHERE user_id = ?"
            : "SELECT COUNT(*) as count FROM complaints WHERE user_id = ? AND status = ?";
        
        $stmt = $conn->prepare($sql);
        
        if ($status === 'total') {
            $stmt->bind_param('i', $user_id);
        } else {
            $stmt->bind_param('is', $user_id, $status);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stats[$status] = $result->fetch_assoc()['count'];
    }
    
    return $stats;
}

// Get complaints list
$user_id = $_SESSION['user_id'];
$stats = getComplaintStats($conn, $user_id);

$query = "SELECT *, DATE_FORMAT(created_at, '%d %b %Y %h:%i %p') as formatted_date 
          FROM complaints 
          WHERE user_id = ? 
          ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$complaints = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints Dashboard - CampusCare</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 20px var(--shadow-color);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px var(--shadow-color);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover i {
            transform: scale(1.1) rotate(10deg);
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .complaint-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            border: none;
            transition: all 0.3s ease;
        }

        .complaint-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var (--shadow-color);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .status-in_progress {
            background-color: rgba(74, 144, 226, 0.2);
            color: #004085;
            border: 1px solid rgba(74, 144, 226, 0.3);
        }

        .status-resolved {
            background-color: rgba(40, 167, 69, 0.2);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .card-header {
            background: var(--primary-color);
            border: none;
            padding: 1rem 1.5rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem var(--shadow-color);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .img-thumbnail {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .img-thumbnail:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .complaint-card {
            animation: fadeIn 0.5s ease forwards;
        }

        .alert {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .container {
            animation: fadeIn 0.5s ease;
        }

        .form-control-lg, .form-select-lg {
            font-size: 1rem;
            padding: 0.8rem 1rem;
        }

        .fw-500 {
            font-weight: 500;
        }

        .complaints-list {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #F5F7FA;
        }

        .complaints-list::-webkit-scrollbar {
            width: 6px;
        }

        .complaints-list::-webkit-scrollbar-track {
            background: #F5F7FA;
        }

        .complaints-list::-webkit-scrollbar-thumb {
            background-color: var(--primary-color);
            border-radius: 20px;
        }

        .complaint-card {
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .btn-lg {
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
        }

        .card {
            border: none;
        }

        .shadow-lg {
            box-shadow: 0 10px 30px var(--shadow-color) !important;
        }

        h2 {
            color: var(--dark-text);
            font-weight: 600;
        }

        h2 i {
            color: var(--primary-color);
            margin-right: 0.5rem;
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
<body class="bg-light">
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
    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <i class="fas fa-clipboard-list text-primary"></i>
                        <h3><?php echo $stats['total']; ?></h3>
                        <p class="mb-0">Total Complaints</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <i class="fas fa-clock text-warning"></i>
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p class="mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <i class="fas fa-tools text-info"></i>
                        <h3><?php echo $stats['in_progress']; ?></h3>
                        <p class="mb-0">In Progress</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success"></i>
                        <h3><?php echo $stats['resolved']; ?></h3>
                        <p class="mb-0">Resolved</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add this navigation section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-clipboard-list"></i> Complaints Dashboard</h2>
            <a href="<?php echo $_SESSION['role'] === 'student' ? 'student_dashboard.php' : 'faculty_dashboard.php'; ?>" 
               class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> 
                Back to <?php echo ucfirst($_SESSION['role']); ?> Dashboard
            </a>
        </div>

        <div class="row">
            <!-- Complaint Form - Now 6 columns -->
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> New Complaint</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="complaintForm" method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label fw-500">Category</label>
                                <select name="category" class="form-select form-select-lg" required>
                                    <option value="">Select Category</option>
                                    <option value="electrical">Electrical</option>
                                    <option value="plumbing">Plumbing</option>
                                    <option value="furniture">Furniture</option>
                                    <option value="cleaning">Cleaning</option>
                                    <option value="network">Network</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-500">Location</label>
                                <input type="text" name="location" class="form-control form-control-lg" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-500">Description</label>
                                <textarea name="description" class="form-control form-control-lg" rows="5" required 
                                          minlength="20" placeholder="Please describe the issue in detail..."></textarea>
                                <div class="form-text">Minimum 20 characters required</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-500">Image (optional)</label>
                                <input type="file" name="image" class="form-control form-control-lg" accept="image/*">
                                <div class="form-text">Max file size: 5MB</div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i> Submit Complaint
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Complaints List - Now 6 columns -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-list"></i> My Complaints</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="complaints-list" style="max-height: 600px; overflow-y: auto;">
                            <?php if ($complaints->num_rows === 0): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No complaints found.
                                </div>
                            <?php else: ?>
                                <?php while ($complaint = $complaints->fetch_assoc()): ?>
                                    <div class="complaint-card">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-1 d-flex align-items-center gap-2">
                                                <i class="fas fa-tag text-primary"></i> 
                                                <?php echo htmlspecialchars(ucfirst($complaint['category'])); ?>
                                            </h6>
                                            <span class="badge status-<?php echo $complaint['status']; ?>">
                                                <?php echo ucfirst($complaint['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i> 
                                            <?php echo htmlspecialchars($complaint['location']); ?>
                                        </p>
                                        
                                        <p class="small mb-2">
                                            <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
                                        </p>
                                        
                                        <?php if ($complaint['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($complaint['image_path']); ?>" 
                                                 class="img-thumbnail mb-2" 
                                                 style="max-width: 150px" 
                                                 alt="Complaint Image">
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i> 
                                                <?php echo $complaint['formatted_date']; ?>
                                            </small>
                                            <?php if ($complaint['image_path']): ?>
                                                <a href="<?php echo htmlspecialchars($complaint['image_path']); ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   target="_blank">
                                                    <i class="fas fa-expand-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('complaintForm').addEventListener('submit', function(e) {
            const description = this.querySelector('[name="description"]').value;
            const fileInput = this.querySelector('[name="image"]');
            
            if (description.length < 20) {
                e.preventDefault();
                alert('Description must be at least 20 characters long');
                return;
            }
            
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (file.size > 5 * 1024 * 1024) { // 5MB
                    e.preventDefault();
                    alert('File size must be less than 5MB');
                    return;
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>