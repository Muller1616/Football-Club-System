<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Get all ticket purchases
try {
    $stmt = $conn->query("
        SELECT t.*, u.username, m.match_date,
               home.name as home_team_name, 
               away.name as away_team_name 
        FROM tickets t
        JOIN users u ON t.user_id = u.id
        JOIN matches m ON t.match_id = m.id
        JOIN teams home ON m.home_team_id = home.id
        JOIN teams away ON m.away_team_id = away.id
        ORDER BY t.purchase_date DESC
    ");
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total revenue
    $stmt = $conn->query("SELECT SUM(total_price) as total_revenue FROM tickets");
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
    
    // Calculate total tickets sold
    $stmt = $conn->query("SELECT SUM(quantity) as total_tickets FROM tickets");
    $total_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['total_tickets'] ?? 0;
} catch(PDOException $e) {
    $error = "Error fetching tickets: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Ticket Sales</h2>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Revenue</h3>
        <p>$<?php echo number_format($total_revenue, 2); ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Total Tickets Sold</h3>
        <p><?php echo $total_tickets; ?></p>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>All Ticket Purchases</h3>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Purchase Date</th>
                <th>User</th>
                <th>Match</th>
                <th>Match Date</th>
                <th>Quantity</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No ticket purchases found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><?php echo format_date($ticket['purchase_date']); ?></td>
                        <td><?php echo $ticket['username']; ?></td>
                        <td><?php echo $ticket['home_team_name'] . ' vs ' . $ticket['away_team_name']; ?></td>
                        <td><?php echo format_date($ticket['match_date']); ?></td>
                        <td><?php echo $ticket['quantity']; ?></td>
                        <td>$<?php echo number_format($ticket['total_price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
