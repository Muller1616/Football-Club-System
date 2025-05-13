<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Get statistics for dashboard
try {
    // Count total players
    $stmt = $conn->query("SELECT COUNT(*) as count FROM players");
    $player_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count total teams
    $stmt = $conn->query("SELECT COUNT(*) as count FROM teams");
    $team_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count total matches
    $stmt = $conn->query("SELECT COUNT(*) as count FROM matches");
    $match_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count total tickets sold
    $stmt = $conn->query("SELECT SUM(quantity) as count FROM tickets");
    $ticket_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Get upcoming matches
    $stmt = $conn->query("
        SELECT m.*, 
               home.name as home_team_name, 
               away.name as away_team_name 
        FROM matches m
        JOIN teams home ON m.home_team_id = home.id
        JOIN teams away ON m.away_team_id = away.id
        WHERE m.status = 'upcoming'
        ORDER BY m.match_date ASC
        LIMIT 5
    ");
    $upcoming_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent ticket purchases
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
        LIMIT 5
    ");
    $recent_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Admin Dashboard</h2>
    <div>
        <span>Welcome, <?php echo $_SESSION['username']; ?>!</span>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Players</h3>
        <p><?php echo $player_count; ?></p>
        <a href="players.php" class="btn">Manage Players</a>
    </div>
    
    <div class="stat-card">
        <h3>Teams</h3>
        <p><?php echo $team_count; ?></p>
        <a href="teams.php" class="btn">Manage Teams</a>
    </div>
    
    <div class="stat-card">
        <h3>Matches</h3>
        <p><?php echo $match_count; ?></p>
        <a href="matches.php" class="btn">Manage Matches</a>
    </div>
    
    <div class="stat-card">
        <h3>Tickets Sold</h3>
        <p><?php echo $ticket_count; ?></p>
        <a href="tickets.php" class="btn">View Tickets</a>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Upcoming Matches</h3>
        <a href="matches.php" class="btn">View All</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Teams</th>
                <th>Venue</th>
                <th>Ticket Price</th>
                <th>Available Tickets</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($upcoming_matches)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No upcoming matches</td>
                </tr>
            <?php else: ?>
                <?php foreach ($upcoming_matches as $match): ?>
                    <tr>
                        <td><?php echo format_date($match['match_date']); ?></td>
                        <td><?php echo $match['home_team_name'] . ' vs ' . $match['away_team_name']; ?></td>
                        <td><?php echo $match['venue']; ?></td>
                        <td>$<?php echo number_format($match['ticket_price'], 2); ?></td>
                        <td><?php echo $match['available_tickets']; ?></td>
                        <td class="action-buttons">
                            <a href="edit_match.php?id=<?php echo $match['id']; ?>" class="btn">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Recent Ticket Purchases</h3>
        <a href="tickets.php" class="btn">View All</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Match</th>
                <th>Quantity</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent_tickets)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No recent ticket purchases</td>
                </tr>
            <?php else: ?>
                <?php foreach ($recent_tickets as $ticket): ?>
                    <tr>
                        <td><?php echo format_date($ticket['purchase_date']); ?></td>
                        <td><?php echo $ticket['username']; ?></td>
                        <td><?php echo $ticket['home_team_name'] . ' vs ' . $ticket['away_team_name']; ?></td>
                        <td><?php echo $ticket['quantity']; ?></td>
                        <td>$<?php echo number_format($ticket['total_price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
