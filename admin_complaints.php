<?php
session_start();
require_once 'connect.php';

// Improved admin authentication check


// Add session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: admin_login.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $complaint_id = filter_input(INPUT_POST, 'complaint_id', FILTER_SANITIZE_NUMBER_INT);
    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    $stmt = $conn->prepare("UPDATE complaints SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param('si', $new_status, $complaint_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Complaint status updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update complaint status";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get complaints count for stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM complaints";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get complaints with user details
$query = "SELECT c.*, 
          DATE_FORMAT(c.created_at, '%d %b %Y %h:%i %p') as formatted_date,
          CONCAT(u.fName, ' ', u.lName) as user_name,
          u.email as user_email
          FROM complaints c
          JOIN users u ON c.user_id = u.id
          ORDER BY 
            CASE c.status 
                WHEN 'pending' THEN 1
                WHEN 'in_progress' THEN 2
                WHEN 'resolved' THEN 3
            END,
            c.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints - CampusCare Admin</title>
    
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
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px var(--shadow-color);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .complaint-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .complaint-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .filters {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow-color);
            margin-bottom: 2rem;
        }

        .form-select, .form-control {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-select:focus, .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem var(--shadow-color);
        }

        .status-badge {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
        }

        .alert {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        @media (max-width: 768px) {
            .filters .row > div {
                margin-bottom: 1rem;
            }
        }

        .status-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 1rem;
            padding: 1.2rem;
            background: rgba(0,0,0,0.03);
            border-radius: 12px;
        }

        .status-btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            border: 2px solid transparent;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 120px;
            justify-content: center;
            position: relative;
            opacity: 0.7;
        }

        /* Pending Button States */
        .btn-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .btn-pending:hover {
            background-color: #ffe69c;
            opacity: 1;
        }

        .btn-pending.active {
            background-color: #ffc107;
            color: #000;
            border-color: #856404;
            opacity: 1;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }

        /* In Progress Button States */
        .btn-in-progress {
            background-color: #cce5ff;
            color: #004085;
        }

        .btn-in-progress:hover {
            background-color: #b8daff;
            opacity: 1;
        }

        .btn-in-progress.active {
            background-color: #0d6efd;
            color: #fff;
            border-color: #004085;
            opacity: 1;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        /* Resolved Button States */
        .btn-resolved {
            background-color: #d4edda;
            color: #155724;
        }

        .btn-resolved:hover {
            background-color: #c3e6cb;
            opacity: 1;
        }

        .btn-resolved.active {
            background-color: #198754;
            color: #fff;
            border-color: #155724;
            opacity: 1;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }

        /* Common active state styles */
        .status-btn.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background-color: currentColor;
            border-radius: 50%;
        }

        .status-btn:hover {
            transform: translateY(-2px);
        }

        .status-btn.active {
            transform: scale(1.05);
        }

        .status-btn i {
            font-size: 1.1rem;
        }

        /* Add this for better spacing in the card */
        .card-body {
            padding: 1.5rem;
        }

        @media (max-width: 768px) {
            .status-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .status-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tasks"></i> Manage Complaints</h2>
            <a href="admin_dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters">
            <div class="row">
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="categoryFilter" class="form-select">
                        <option value="">All Categories</option>
                        <option value="electrical">Electrical</option>
                        <option value="plumbing">Plumbing</option>
                        <option value="furniture">Furniture</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="network">Network</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search complaints...">
                </div>
            </div>
        </div>

        <!-- Complaints List -->
        <div class="complaints-list">
            <?php if ($result->num_rows === 0): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No complaints found.
                </div>
            <?php else: ?>
                <?php while ($complaint = $result->fetch_assoc()): ?>
                    <div class="card complaint-card" 
                         data-status="<?php echo $complaint['status']; ?>"
                         data-category="<?php echo $complaint['category']; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title">
                                        <i class="fas fa-tag"></i> 
                                        <?php echo htmlspecialchars(ucfirst($complaint['category'])); ?>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-user"></i> 
                                        <?php echo htmlspecialchars($complaint['user_name']); ?> 
                                        (<?php echo htmlspecialchars($complaint['user_email']); ?>)
                                    </p>
                                </div>
                                <div class="status-actions">
                                    <form method="POST" class="d-flex gap-2 align-items-center w-100">
                                        <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        
                                        <button type="submit" name="status" value="pending" 
                                                class="status-btn btn-pending <?php echo $complaint['status'] === 'pending' ? 'active' : ''; ?>">
                                            <i class="fas fa-exclamation-circle"></i> Pending
                                        </button>
                                        
                                        <button type="submit" name="status" value="in_progress" 
                                                class="status-btn btn-in-progress <?php echo $complaint['status'] === 'in_progress' ? 'active' : ''; ?>">
                                            <i class="fas fa-tools"></i> In Progress
                                        </button>
                                        
                                        <button type="submit" name="status" value="resolved" 
                                                class="status-btn btn-resolved <?php echo $complaint['status'] === 'resolved' ? 'active' : ''; ?>">
                                            <i class="fas fa-check-circle"></i> Resolved
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <p class="card-text mt-3">
                                <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <p class="mb-0">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($complaint['location']); ?>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo $complaint['formatted_date']; ?>
                                    </small>
                                </div>
                                <?php if ($complaint['image_path']): ?>
                                    <a href="<?php echo htmlspecialchars($complaint['image_path']); ?>" 
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-image"></i> View Image
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const categoryFilter = document.getElementById('categoryFilter');
            const searchInput = document.getElementById('searchInput');
            const complaints = document.querySelectorAll('.complaint-card');

            function filterComplaints() {
                const status = statusFilter.value.toLowerCase();
                const category = categoryFilter.value.toLowerCase();
                const search = searchInput.value.toLowerCase();

                complaints.forEach(complaint => {
                    const matchesStatus = status === '' || complaint.dataset.status === status;
                    const matchesCategory = category === '' || complaint.dataset.category === category;
                    const matchesSearch = search === '' || complaint.textContent.toLowerCase().includes(search);

                    complaint.style.display = 
                        matchesStatus && matchesCategory && matchesSearch ? 'block' : 'none';
                });
            }

            statusFilter.addEventListener('change', filterComplaints);
            categoryFilter.addEventListener('change', filterComplaints);
            searchInput.addEventListener('input', filterComplaints);
        });
    </script>
</body>
</html>
