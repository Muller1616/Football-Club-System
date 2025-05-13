<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Check if team ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: teams.php");
    exit();
}

$team_id = $_GET['id'];

// Get team details
try {
    $stmt = $conn->prepare("SELECT * FROM teams WHERE id = :id");
    $stmt->bindParam(':id', $team_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header("Location: teams.php");
        exit();
    }
    
    $team = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get team players
    $stmt = $conn->prepare("SELECT * FROM players WHERE team_id = :team_id ORDER BY position, name");
    $stmt->bindParam(':team_id', $team_id);
    $stmt->execute();
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get team matches
    $stmt = $conn->prepare("
        SELECT m.*, 
               home.name as home_team_name, 
               away.name as away_team_name 
        FROM matches m
        JOIN teams home ON m.home_team_id = home.id
        JOIN teams away ON m.away_team_id = away.id
        WHERE m.home_team_id = :team_id OR m.away_team_id = :team_id
        ORDER BY m.match_date DESC
        LIMIT 5
    ");
    $stmt->bindParam(':team_id', $team_id);
    $stmt->execute();
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching team data: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Team Details</h2>
    <div>
        <a href="edit_team.php?id=<?php echo $team_id; ?>" class="btn">Edit Team</a>
        <a href="teams.php" class="btn">Back to Teams</a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php else: ?>
    <div class="team-profile">
        <div class="team-header">
            <div class="team-logo">
                <img src="<?php echo $base_path; ?>assets/images/teams/<?php echo $team['logo'] ?? 'default_team.png'; ?>" alt="<?php echo $team['name']; ?>">
            </div>
            <div class="team-info">
                <h1><?php echo $team['name']; ?></h1>
                <p><strong>Coach:</strong> <?php echo $team['coach']; ?></p>
                <p><strong>Founded:</strong> <?php echo $team['founded_year']; ?></p>
                <p><strong>Players:</strong> <?php echo count($players); ?></p>
            </div>
        </div>
        
        <div class="team-description">
            <h3>About the Team</h3>
            <p><?php echo $team['description'] ?? 'No description available.'; ?></p>
        </div>
    </div>
    
    <h3>Team Players</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Jersey Number</th>
                    <th>Age</th>
                    <th>Nationality</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($players)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No players in this team</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($players as $player): ?>
                        <tr>
                            <td><?php echo $player['name']; ?></td>
                            <td><?php echo $player['position']; ?></td>
                            <td><?php echo $player['jersey_number']; ?></td>
                            <td><?php echo $player['age']; ?></td>
                            <td><?php echo $player['nationality']; ?></td>
                            <td>
                                <a href="view_player.php?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-info">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <h3>Recent Matches</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Teams</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($matches)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No matches for this team</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($matches as $match): ?>
                        <tr>
                            <td><?php echo format_date($match['match_date']); ?></td>
                            <td><?php echo $match['home_team_name'] . ' vs ' . $match['away_team_name']; ?></td>
                            <td><?php echo $match['venue']; ?></td>
                            <td><?php echo ucfirst($match['status']); ?></td>
                            <td>
                                <?php if ($match['status'] === 'completed'): ?>
                                    <?php echo $match['home_score'] . ' - ' . $match['away_score']; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
    .team-profile {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .team-header {
        display: flex;
        padding: 2rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .team-logo {
        width: 150px;
        height: 150px;
        overflow: hidden;
        margin-right: 2rem;
        border: 5px solid #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .team-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .team-info {
        flex: 1;
    }
    
    .team-info h1 {
        color: #1a3a6c;
        margin-bottom: 1rem;
    }
    
    .team-description {
        padding: 2rem;
    }
    
    .team-description h3 {
        color: #1a3a6c;
        margin-bottom: 1rem;
    }
    
    h3 {
        color: #1a3a6c;
        margin: 2rem 0 1rem;
    }
    
    @media (max-width: 768px) {
        .team-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .team-logo {
            margin-right: 0;
            margin-bottom: 1.5rem;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>
