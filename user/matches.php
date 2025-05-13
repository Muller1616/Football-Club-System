<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is fan
redirect_if_not_logged_in();
redirect_if_not_fan();

// Get all matches
try {
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
    ");
    $upcoming_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get completed matches
    $stmt = $conn->query("
        SELECT m.*, 
               home.name as home_team_name, 
               away.name as away_team_name 
        FROM matches m
        JOIN teams home ON m.home_team_id = home.id
        JOIN teams away ON m.away_team_id = away.id
        WHERE m.status = 'completed'
        ORDER BY m.match_date DESC
    ");
    $completed_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching matches: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Matches</h2>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

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

<h3>Completed Matches</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Teams</th>
                <th>Venue</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($completed_matches)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No completed matches</td>
                </tr>
            <?php else: ?>
                <?php foreach ($completed_matches as $match): ?>
                    <tr>
                        <td><?php echo format_date($match['match_date']); ?></td>
                        <td><?php echo $match['home_team_name'] . ' vs ' . $match['away_team_name']; ?></td>
                        <td><?php echo $match['venue']; ?></td>
                        <td><?php echo $match['home_score'] . ' - ' . $match['away_score']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    h3 {
        color: #1a3a6c;
        margin: 2rem 0 1rem;
    }
</style>

<?php include '../includes/footer.php'; ?>
