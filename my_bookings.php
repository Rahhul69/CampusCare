<?php
session_start();
require_once 'connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header('Location: student_login.php');
    exit();
}

// Status badge configurations
$status_badges = [
    'pending' => ['class' => 'bg-warning text-dark', 'icon' => 'clock'],
    'approved' => ['class' => 'bg-success text-white', 'icon' => 'check-circle'],
    'rejected' => ['class' => 'bg-danger text-white', 'icon' => 'times-circle'],
    'cancelled' => ['class' => 'bg-secondary text-white', 'icon' => 'ban'],
    '' => ['class' => 'bg-secondary text-white', 'icon' => 'question-circle'] // Default for empty status
];

// Handle booking cancellation
if (isset($_POST['cancel_booking'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    try {
        // Check if booking exists and belongs to user
        $stmt = $conn->prepare("SELECT date FROM hall_bookings WHERE id = ? AND email = ?");
        $stmt->bind_param("is", $booking_id, $_SESSION['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($booking = $result->fetch_assoc()) {
            $hours_until_booking = (strtotime($booking['date']) - time()) / 3600;
            
            if ($hours_until_booking >= 48) {
                $delete_stmt = $conn->prepare("UPDATE hall_bookings SET status = 'cancelled' WHERE id = ?");
                $delete_stmt->bind_param("i", $booking_id);
                
                if ($delete_stmt->execute()) {
                    $message = ['type' => 'success', 'text' => 'Booking cancelled successfully!'];
                } else {
                    $message = ['type' => 'danger', 'text' => 'Error cancelling booking.'];
                }
            } else {
                $message = ['type' => 'warning', 'text' => 'Bookings can only be cancelled 48 hours before the scheduled time.'];
            }
        }
    } catch (Exception $e) {
        $message = ['type' => 'danger', 'text' => 'An error occurred while processing your request.'];
    }
}

// Add debugging before the try block
echo "<!-- Debug: User email from session: " . $_SESSION['email'] . " -->";

// Fetch user's bookings with debug info
try {
    // First, check if any bookings exist
    $check_query = "SELECT COUNT(*) as total FROM hall_bookings";
    $result = $conn->query($check_query);
    $total = $result->fetch_assoc()['total'];
    echo "<!-- Debug: Total bookings in database: $total -->";

    // Check bookings for current user
    $check_user = $conn->prepare("SELECT COUNT(*) as user_total FROM hall_bookings WHERE email = ?");
    $check_user->bind_param("s", $_SESSION['email']);
    $check_user->execute();
    $user_total = $check_user->get_result()->fetch_assoc()['user_total'];
    echo "<!-- Debug: Bookings for current user: $user_total -->";

    // Original query with debug output
    $stmt = $conn->prepare("
        SELECT *, 
            DATE_FORMAT(date, '%W, %D %M %Y') as formatted_date,
            DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as booking_time
        FROM hall_bookings 
        WHERE email = ? 
        ORDER BY date DESC, created_at DESC
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $_SESSION['email']);
    $success = $stmt->execute();
    
    if (!$success) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $bookings = $stmt->get_result();
    echo "<!-- Debug: Query results count: " . $bookings->num_rows . " -->";
    
    // Debug first row if exists
    if ($bookings->num_rows > 0) {
        $first_row = $bookings->fetch_assoc();
        echo "<!-- Debug: First booking data: " . print_r($first_row, true) . " -->";
        // Reset the result pointer
        $bookings->data_seek(0);
    }

} catch (Exception $e) {
    $message = ['type' => 'danger', 'text' => 'Unable to fetch bookings: ' . $e->getMessage()];
    echo "<!-- Debug Error: " . $e->getMessage() . " -->";
}

// Add this temporary debug output
echo "<div style='display:none'>Debug Info:<br>";
echo "Session Email: " . $_SESSION['email'] . "<br>";
echo "Connection Status: " . ($conn->ping() ? 'Connected' : 'Not Connected') . "<br>";
echo "</div>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - CampusCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .booking-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label i {
            color: #1e3c72;
            width: 16px;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
        }

        .cancel-btn {
            background: #ff5252;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .cancel-btn:hover:not(:disabled) {
            background: #ff1744;
            transform: translateY(-2px);
        }

        .cancel-btn:disabled {
            background: #bdbdbd;
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-calendar-check me-2"></i>My Bookings
            </h2>
            <a href="hall_booking.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Booking
            </a>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-<?= $message['type'] ?>" role="alert">
                <?= $message['text'] ?>
            </div>
        <?php endif; ?>

        <?php if ($bookings && $bookings->num_rows > 0): ?>
            <?php while ($booking = $bookings->fetch_assoc()): 
                $is_upcoming = strtotime($booking['date']) > time();
                $can_cancel = (strtotime($booking['date']) - time()) >= (48 * 3600);
                $status = !empty($booking['status']) ? $booking['status'] : 'pending';
                $status_info = $status_badges[$status] ?? $status_badges['pending']; // Fallback to pending if status not found
            ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?= $booking['formatted_date'] ?>
                        </h5>
                        <span class="badge <?= $status_info['class'] ?>">
                            <i class="fas fa-<?= $status_info['icon'] ?> me-2"></i>
                            <?= ucfirst($status) ?>
                        </span>
                    </div>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-building"></i>Hall:</span>
                            <span><?= htmlspecialchars($booking['hall']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-clock"></i>Time:</span>
                            <span><?= htmlspecialchars($booking['time_slot']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-users"></i>Seats:</span>
                            <span><?= htmlspecialchars($booking['seats_required']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-tasks"></i>Purpose:</span>
                            <span><?= htmlspecialchars($booking['purpose']) ?></span>
                        </div>
                    </div>
                    
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-alt fa-3x mb-3 text-muted"></i>
                <h4>No Bookings Found</h4>
                <p class="text-muted">You haven't made any hall bookings yet.</p>
                <a href="hall_booking.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Book a Hall
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>