<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $player_id = $_GET['delete'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM players WHERE id = :id");
        $stmt->bindParam(':id', $player_id);
        $stmt->execute();
        
        $success = "Player deleted successfully";
    } catch(PDOException $e) {
        $error = "Error deleting player: " . $e->getMessage();
    }
}

// Get all players
try {
    $stmt = $conn->query("
        SELECT p.*, t.name as team_name 
        FROM players p
        LEFT JOIN teams t ON p.team_id = t.id
        ORDER BY p.name
    ");
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching players: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Player Management</h2>
    <a href="add_player.php" class="btn">Add New Player</a>
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
                <th>ID</th>
                <th>Name</th>
                <th>Position</th>
                <th>Jersey #</th>
                <th>Age</th>
                <th>Nationality</th>
                <th>Team</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($players)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No players found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($players as $player): ?>
                    <tr>
                        <td><?php echo $player['id']; ?></td>
                        <td><?php echo $player['name']; ?></td>
                        <td><?php echo $player['position']; ?></td>
                        <td><?php echo $player['jersey_number']; ?></td>
                        <td><?php echo $player['age']; ?></td>
                        <td><?php echo $player['nationality']; ?></td>
                        <td><?php echo $player['team_name'] ?? 'Not Assigned'; ?></td>
                        <td class="action-buttons">
                            <a href="view_player.php?id=<?php echo $player['id']; ?>" class="btn">View</a>
                            <a href="edit_player.php?id=<?php echo $player['id']; ?>" class="btn">Edit</a>
                            <a href="players.php?delete=<?php echo $player['id']; ?>" class="btn btn-danger delete-btn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
