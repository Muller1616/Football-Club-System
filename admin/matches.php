<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $match_id = $_GET['delete'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM matches WHERE id = :id");
        $stmt->bindParam(':id', $match_id);
        $stmt->execute();
        
        $success = "Match deleted successfully";
    } catch(PDOException $e) {
        $error = "Error deleting match: " . $e->getMessage();
    }
}

// Get all matches
try {
    $stmt = $conn->query("
        SELECT m.*, 
               home.name as home_team_name, 
               away.name as away_team_name 
        FROM matches m
        JOIN teams home ON m.home_team_id = home.id
        JOIN teams away ON m.away_team_id = away.id
        ORDER BY m.match_date DESC
    ");
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching matches: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Match Management</h2>
    <a href="add_match.php" class="btn">Add New Match</a>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Teams</th>
                <th>Venue</th>
                <th>Ticket Price</th>
                <th>Available Tickets</th>
                <th>Status</th>
                <th>Score</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($matches)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No matches found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($matches as $match): ?>
                    <tr>
                        <td><?php echo format_date($match['match_date']); ?></td>
                        <td><?php echo $match['home_team_name'] . ' vs ' . $match['away_team_name']; ?></td>
                        <td><?php echo $match['venue']; ?></td>
                        <td>$<?php echo number_format($match['ticket_price'], 2); ?></td>
                        <td><?php echo $match['available_tickets']; ?></td>
                        <td><?php echo ucfirst($match['status']); ?></td>
                        <td>
                            <?php if ($match['status'] === 'completed'): ?>
                                <?php echo $match['home_score'] . ' - ' . $match['away_score']; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="action-buttons">
                            <a href="edit_match.php?id=<?php echo $match['id']; ?>" class="btn">Edit</a>
                            <a href="matches.php?delete=<?php echo $match['id']; ?>" class="btn btn-danger delete-btn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
