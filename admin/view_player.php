<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Check if player ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: players.php");
    exit();
}

$player_id = $_GET['id'];

// Get player details
try {
    $stmt = $conn->prepare("
        SELECT p.*, t.name as team_name 
        FROM players p
        LEFT JOIN teams t ON p.team_id = t.id
        WHERE p.id = :id
    ");
    $stmt->bindParam(':id', $player_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header("Location: players.php");
        exit();
    }
    
    $player = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching player: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Player Details</h2>
    <div>
        <a href="edit_player.php?id=<?php echo $player_id; ?>" class="btn">Edit Player</a>
        <a href="players.php" class="btn">Back to Players</a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php else: ?>
    <div class="player-profile">
        <div class="player-header">
            <div class="player-image">
                <img src="<?php echo $base_path; ?>assets/images/players/<?php echo $player['image'] ?? 'default_player.png'; ?>" alt="<?php echo $player['name']; ?>">
            </div>
            <div class="player-info">
                <h1><?php echo $player['name']; ?></h1>
                <p class="player-position"><?php echo $player['position']; ?> | #<?php echo $player['jersey_number']; ?></p>
                <p><strong>Team:</strong> <?php echo $player['team_name'] ?? 'Not Assigned'; ?></p>
                <p><strong>Age:</strong> <?php echo $player['age']; ?></p>
                <p><strong>Nationality:</strong> <?php echo $player['nationality']; ?></p>
            </div>
        </div>
        
        <div class="player-bio">
            <h3>Biography</h3>
            <p><?php echo $player['bio'] ?? 'No biography available.'; ?></p>
        </div>
    </div>
<?php endif; ?>

<style>
    .player-profile {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .player-header {
        display: flex;
        padding: 2rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .player-image {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 2rem;
        border: 5px solid #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .player-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .player-info {
        flex: 1;
    }
    
    .player-info h1 {
        color: #1a3a6c;
        margin-bottom: 0.5rem;
    }
    
    .player-position {
        color: #6c757d;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }
    
    .player-bio {
        padding: 2rem;
    }
    
    .player-bio h3 {
        color: #1a3a6c;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .player-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .player-image {
            margin-right: 0;
            margin-bottom: 1.5rem;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>
