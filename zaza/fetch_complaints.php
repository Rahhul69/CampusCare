<?php
session_start();
require_once 'database.php';
include("connect.php");

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

/**
 * Get statistics for user complaints
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array Statistics for different complaint statuses
 */
function getComplaintStats($conn, $user_id) {
    $stats = array();
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
        $stmt->close();
    }
    
    return $stats;
}

// Fetch user's complaints
try {
    $sql = "SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = getComplaintStats($conn, $user_id);
} catch (Exception $e) {
    error_log("Error fetching complaints: " . $e->getMessage());
    $error = "An error occurred while fetching complaints.";
}
?>

<!-- Page Structure -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - CampusCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="cstyle.css">
</head>
<body>
    <!-- Statistics Dashboard -->
    <div class="stats-container">
        <div class="stat-card total">
            <i class="fas fa-clipboard-list"></i>
            <div class="stat-value"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Complaints</div>
        </div>
        
        <div class="stat-card pending">
            <i class="fas fa-spinner fa-spin"></i>
            <div class="stat-value"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">Pending</div>
        </div>
        
        <div class="stat-card in-progress">
            <i class="fas fa-clock"></i>
            <div class="stat-value"><?php echo $stats['in_progress']; ?></div>
            <div class="stat-label">In Progress</div>
        </div>
        
        <div class="stat-card resolved">
            <i class="fas fa-check-circle"></i>
            <div class="stat-value"><?php echo $stats['resolved']; ?></div>
            <div class="stat-label">Resolved</div>
        </div>
    </div>

    <!-- Complaints List -->
    <div class="complaints-section">
        <h2><i class="fas fa-list"></i> My Complaints</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php elseif ($result->num_rows > 0): ?>
            <div class="complaints-container">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="complaint-card">
                        <div class="complaint-header">
                            <h3><?php echo htmlspecialchars($row['subject']); ?></h3>
                            <span class="status-badge status-<?php echo $row['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                            </span>
                        </div>
                        <div class="complaint-details">
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <p class="complaint-date">
                                <small>Submitted: <?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></small>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No complaints submitted yet.
            </div>
        <?php endif; ?>
    </div>

    <!-- Status Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Your complaint has been successfully submitted.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> There was an error submitting your complaint. Please try again.
        </div>
    <?php endif; ?>

    <script src="complaintjs.js" defer></script>
</body>
</html>

<?php
// Clean up
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
<?php
include("connect.php");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Get user's complaints
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($complaint = $result->fetch_assoc()) {
            ?>
            <div class="complaint-card">
                <div class="complaint-header">
                    <span class="category">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($complaint['category']); ?>
                    </span>
                    <span class="date">
                        <i class="fas fa-calendar"></i> 
                        <?php echo date('M d, Y', strtotime($complaint['created_at'])); ?>
                    </span>
                </div>
                <div class="complaint-body">
                    <p class="location">
                        <i class="fas fa-map-marker-alt"></i> 
                        <?php echo htmlspecialchars($complaint['location']); ?>
                    </p>
                    <p class="description">
                        <?php echo htmlspecialchars($complaint['description']); ?>
                    </p>
                    <?php if (!empty($complaint['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($complaint['image_path']); ?>" 
                             alt="Complaint Image" class="complaint-image">
                    <?php endif; ?>
                </div>
                <div class="complaint-status <?php echo $complaint['status']; ?>">
                    <i class="fas fa-circle"></i> <?php echo ucfirst($complaint['status']); ?>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<p class="no-complaints">No complaints registered yet.</p>';
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error in fetch_complaints.php: " . $e->getMessage());
    echo '<p class="error">Error loading complaints. Please try again later.</p>';
}

// Don't close the connection here as it might be needed elsewhere
?>