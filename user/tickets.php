<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is fan
redirect_if_not_logged_in();
redirect_if_not_fan();

// Get user's ticket purchases
try {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("
        SELECT t.*, m.match_date, m.status,
               home.name as home_team_name, 
               away.name as away_team_name 
        FROM tickets t
        JOIN matches m ON t.match_id = m.id
        JOIN teams home ON m.home_team_id = home.id
        JOIN teams away ON m.away_team_id = away.id
        WHERE t.user_id = :user_id
        ORDER BY m.match_date DESC
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count user's tickets
    $total_tickets = 0;
    $total_spent = 0;
    foreach ($tickets as $ticket) {
        $total_tickets += $ticket['quantity'];
        $total_spent += $ticket['total_price'];
    }
} catch(PDOException $e) {
    $error = "Error fetching tickets: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>My Tickets</h2>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Tickets</h3>
        <p><?php echo $total_tickets; ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Total Spent</h3>
        <p>$<?php echo number_format($total_spent, 2); ?></p>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Purchase Date</th>
                <th>Match</th>
                <th>Match Date</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">You haven't purchased any tickets yet</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><?php echo format_date($ticket['purchase_date']); ?></td>
                        <td><?php echo $ticket['home_team_name'] . ' vs ' . $ticket['away_team_name']; ?></td>
                        <td><?php echo format_date($ticket['match_date']); ?></td>
                        <td><?php echo $ticket['quantity']; ?></td>
                        <td>$<?php echo number_format($ticket['total_price'], 2); ?></td>
                        <td>
                            <?php if ($ticket['status'] === 'upcoming'): ?>
                                <span class="badge badge-success">Upcoming</span>
                            <?php elseif ($ticket['status'] === 'completed'): ?>
                                <span class="badge badge-secondary">Completed</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Cancelled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    .badge {
        display: inline-block;
        padding: 0.25rem
