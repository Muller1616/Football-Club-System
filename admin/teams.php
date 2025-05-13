<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $team_id = $_GET['delete'];
    
    try {
        // Check if team has players
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM players WHERE team_id = :team_id");
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        $player_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($player_count > 0) {
            $error = "Cannot delete team with assigned players";
        } else {
            // Delete team
            $stmt = $conn->prepare("DELETE FROM teams WHERE id = :team_id");
            $stmt->bindParam(':team_id', $team_id);
            
            if ($stmt->execute()) {
                $success = "Team deleted successfully";
            } else {
                $error = "Error deleting team";
            }
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all teams with player count
try {
    $stmt = $conn->query("
        SELECT t.*, COUNT(p.id) as player_count 
        FROM teams t 
        LEFT JOIN players p ON t.id = p.team_id 
        GROUP BY t.id 
        ORDER BY t.name
    ");
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching teams: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Team Management</h2>
    <a href="add_team.php" class="btn">Add New Team</a>
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
                <th>Coach</th>
                <th>Founded</th>
                <th>Players</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($teams)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No teams found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($teams as $team): ?>
                    <tr>
                        <td><?php echo $team['id']; ?></td>
                        <td><?php echo $team['name']; ?></td>
                        <td><?php echo $team['coach']; ?></td>
                        <td><?php echo $team['founded_year']; ?></td>
                        <td><?php echo $team['player_count']; ?></td>
                        <td class="action-buttons">
                            <a href="view_team.php?id=<?php echo $team['id']; ?>" class="btn btn-sm btn-info">View</a>
                            <a href="edit_team.php?id=<?php echo $team['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="teams.php?delete=<?php echo $team['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this team?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
