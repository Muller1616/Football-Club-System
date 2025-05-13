<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is fan
redirect_if_not_logged_in();
redirect_if_not_fan();

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
    <h2>Players</h2>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="player-grid">
    <?php if (empty($players)): ?>
        <p>No players found</p>
    <?php else: ?>
        <?php foreach ($players as $player): ?>
            <div class="player-card">
                <div class="player-image">
                    <img src="<?php echo $base_path; ?>assets/images/players/<?php echo $player['image'] ?? 'default_player.png'; ?>" alt="<?php echo $player['name']; ?>">
                </div>
                <div class="player-info">
                    <h3><?php echo $player['name']; ?></h3>
                    <p class="player-position"><?php echo $player['position']; ?> | #<?php echo $player['jersey_number']; ?></p>
                    <p><strong>Team:</strong> <?php echo $player['team_name'] ?? 'Not Assigned'; ?></p>
                    <p><strong>Age:</strong> <?php echo $player['age']; ?></p>
                    <p><strong>Nationality:</strong> <?php echo $player['nationality']; ?></p>
                </div>
                <div class="player-actions">
                    <a href="view_player.php?id=<?php echo $player['id']; ?>" class="btn">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .player-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .player-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .player-card:hover {
        transform: translateY(-5px);
    }
    
    .player-image {
        height: 200px;
        overflow: hidden;
    }
    
    .player-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .player-info {
        padding: 1.5rem;
    }
    
    .player-info h3 {
        color: #1a3a6c;
        margin-bottom: 0.5rem;
    }
    
    .player-position {
        color: #6c757d;
        margin-bottom: 1rem;
    }
    
    .player-actions {
        padding: 0 1.5rem 1.5rem;
    }
</style>

<?php include '../includes/footer.php'; ?>
