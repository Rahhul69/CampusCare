<?php
session_start();
require_once 'connect.php';

// Check if user is admin
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle booking status updates
if(isset($_POST['update_status'])) {
    $booking_id = filter_var($_POST['booking_id'], FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
    $admin_remarks = filter_var($_POST['admin_remarks'], FILTER_SANITIZE_STRING);
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Update the booking status
        $stmt = $conn->prepare("UPDATE hall_bookings SET 
            status = ?, 
            admin_remarks = ?, 
            updated_by = ?, 
            updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?");
        $stmt->bind_param("ssii", $status, $admin_remarks, $_SESSION['admin_id'], $booking_id);
        
        if($stmt->execute()) {
            $conn->commit();
            $_SESSION['success'] = "Booking status updated successfully";
        } else {
            throw new Exception("Failed to update booking status");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: admin_hall_bookings.php");
    exit();
}

// Fetch all bookings with filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$hall_filter = isset($_GET['hall']) ? $_GET['hall'] : '';

$sql = "SELECT * FROM hall_bookings WHERE 1=1";
if($status_filter) $sql .= " AND status = ?";
if($date_filter) $sql .= " AND date = ?";
if($hall_filter) $sql .= " AND hall = ?";
$sql .= " ORDER BY date DESC, created_at DESC";

$stmt = $conn->prepare($sql);

// Bind parameters based on filters
$types = '';
$params = [];
if($status_filter) { $types .= 's'; $params[] = $status_filter; }
if($date_filter) { $types .= 's'; $params[] = $date_filter; }
if($hall_filter) { $types .= 's'; $params[] = $hall_filter; }

if($types) {
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
    <title>Manage Hall Bookings - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        body {
            background: #f5f6fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
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

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .bookings-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--dark);
            color: white;
            padding: 1rem;
            text-align: left;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending { background: var(--warning); color: white; }
        .status-approved { background: var(--success); color: white; }
        .status-rejected { background: var(--danger); color: white; }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }

        .btn-approve { background: var(--success); color: white; }
        .btn-reject { background: var(--danger); color: white; }

        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            background: var(--gradient);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .time-slot {
            background: var(--light);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-calendar-check"></i> Manage Hall Bookings</h1>
                <p>Review and manage hall booking requests</p>
            </div>
            <a href="admin_dashboard.php" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="filters">
            <form class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="<?= $date_filter ?>">
                </div>
                <div class="col-md-3">
                    <select name="hall" class="form-select">
                        <option value="">All Halls</option>
                        <option value="Eric Mathias Hall">Eric Mathias Hall</option>
                        <option value="Lcri Hall">Lcri Hall</option>
                        <option value="Gelge Hall">Gelge Hall</option>
                        <option value="Arupe Hall">Arupe Hall</option>
                        <option value="Joseph Willy Hall">Joseph Willy Hall</option>
                        <option value="Sanidhya Hall">Sanidhya Hall</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <div class="bookings-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Hall</th>
                        <th>Date</th>
                        <th>Time Slots</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $booking['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($booking['name']) ?><br>
                            <small><?= htmlspecialchars($booking['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($booking['hall']) ?></td>
                        <td><?= date('d M Y', strtotime($booking['date'])) ?></td>
                        <td>
                            <div class="time-slots">
                                <?php foreach(explode(',', $booking['time_slot']) as $slot): ?>
                                    <span class="time-slot"><?= trim($slot) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($booking['purpose']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $booking['status'] ?>">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="action-btn btn-approve" onclick="showUpdateModal(<?= $booking['id'] ?>, 'approved')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="action-btn btn-reject" onclick="showUpdateModal(<?= $booking['id'] ?>, 'rejected')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Booking Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="booking_id" id="booking_id">
                        <input type="hidden" name="status" id="status">
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Remarks</label>
                            <textarea name="admin_remarks" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showUpdateModal(bookingId, status) {
            document.getElementById('booking_id').value = bookingId;
            document.getElementById('status').value = status;
            const modal = new bootstrap.Modal(document.getElementById('updateModal'));
            modal.show();
        }
    </script>
</body>
