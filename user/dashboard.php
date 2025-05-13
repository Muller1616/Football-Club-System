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
        LIMIT 3
    ");
    $upcoming_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user's tickets
    $stmt = $conn->prepare("
        SELECT t.*, m.match_date,
               home.name as home_team_name, 
               away.name as away_team_name 
        FROM tickets t
        JOIN matches m ON t.match_id = m.id
        JOIN teams home ON m.home_team_id = home.id
        JOIN teams away ON m.away_team_id = away.id
        WHERE t.user_id = :user_id
        ORDER BY m.match_date ASC
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count user's tickets
    $total_tickets = 0;
    $total_spent = 0;
    foreach ($user_tickets as $ticket) {
        $total_tickets += $ticket['quantity'];
        $total_spent += $ticket['total_price'];
    }
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Fan Dashboard</h2>
    <div>
        <span>Welcome, <?php echo $_SESSION['username']; ?>!</span>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>My Tickets</h3>
        <p><?php echo $total_tickets; ?></p>
        <a href="tickets.php" class="btn">View My Tickets</a>
    </div>
    
    <div class="stat-card">
        <h3>Total Spent</h3>
        <p>$<?php echo number_format($total_spent, 2); ?></p>
    </div>
</div>

<h3>Upcoming Matches</h3>
<div class="card-grid">
    <?php if (empty($upcoming_matches)): ?>
        <p>No upcoming matches</p>
    <?php else: ?>
        <?php foreach ($upcoming_matches as $match): ?>
            <div class="match-card">
                <div class="match-header">
                    <h3><?php echo format_date($match['match_date']); ?></h3>
                </div>
                <div class="match-teams">
                    <div class="match-team">
                        <img src="<?php echo $base_path; ?>assets/images/teams/default_team.png" alt="<?php echo $match['home_team_name']; ?>">
                        <h4><?php echo $match['home_team_name']; ?></h4>
                    </div>
                    <div class="match-vs">VS</div>
                    <div class="match-team">
                        <img src="<?php echo $base_path; ?>assets/images/teams/default_team.png" alt="<?php echo $match['away_team_name']; ?>">
                        <h4><?php echo $match['away_team_name']; ?></h4>
                    </div>
                </div>
                <div class="match-details">
                    <p><strong>Venue:</strong> <?php echo $match['venue']; ?></p>
                    <p><strong>Ticket Price:</strong> $<?php echo number_format($match['ticket_price'], 2); ?></p>
                    <p><strong>Available Tickets:</strong> <?php echo $match['available_tickets']; ?></p>
                </div>
                <div class="match-actions">
                    <a href="buy_ticket.php?id=<?php echo $match['id']; ?>" class="btn">Buy Tickets</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="view-all-link">
    <a href="matches.php" class="btn">View All Matches</a>
</div>

<h3>My Recent Tickets</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Match</th>
                <th>Date</th>
                <th>Quantity</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($user_tickets)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">You haven't purchased any tickets yet</td>
                </tr>
            <?php else: ?>
                <?php foreach (array_slice($user_tickets, 0, 5) as $ticket): ?>
                    <tr>
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

<div class="view-all-link">
    <a href="tickets.php" class="btn">View All My Tickets</a>
</div>

<style>
    .view-all-link {
        text-align: center;
        margin: 1.5rem 0 2.5rem;
    }
    
    h3 {
        color: #1a3a6c;
        margin: 2rem 0 1rem;
    }
</style>

<?php include '../includes/footer.php'; ?>
