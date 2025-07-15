<?php
require_once 'connect.php';

// Check if session exists instead of starting it
if (!isset($_SESSION['user_id'])) {
    exit('No active session found');
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

// Fetch complaints with formatted date
$query = "SELECT 
            c.*,
            DATE_FORMAT(c.created_at, '%d %b %Y %h:%i %p') as formatted_date
          FROM complaints c
          WHERE c.user_id = ?
          ORDER BY c.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats = getComplaintStats($conn, $user_id);
?>

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

<?php
// Don't close connection here since parent file might need it
if (isset($stmt)) {
    $stmt->close();
}
?>