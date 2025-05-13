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

// Get all teams for dropdown
try {
    $stmt = $conn->query("SELECT id, name FROM teams ORDER BY name");
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching teams: " . $e->getMessage();
}

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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $position = sanitize_input($_POST['position']);
    $jersey_number = (int)$_POST['jersey_number'];
    $age = (int)$_POST['age'];
    $nationality = sanitize_input($_POST['nationality']);
    $bio = sanitize_input($_POST['bio']);
    $team_id = !empty($_POST['team_id']) ? (int)$_POST['team_id'] : null;
    
    // Validate input
    if (empty($name) || empty($position) || empty($nationality)) {
        $error = "Please fill in all required fields";
    } else {
        $error = "Please fill in all required fields";
        try {
            $stmt = $conn->prepare("
                UPDATE players 
                SET name = :name, position = :position, jersey_number = :jersey_number, 
                    age = :age, nationality = :nationality, bio = :bio, team_id = :team_id
                WHERE id = :id
            ");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':position', $position);
            $stmt->bindParam(':jersey_number', $jersey_number);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':nationality', $nationality);
            $stmt->bindParam(':bio', $bio);
            $stmt->bindParam(':team_id', $team_id);
            $stmt->bindParam(':id', $player_id);
            
            if ($stmt->execute()) {
                $success = "Player updated successfully";
                
                // Refresh player data
                $stmt = $conn->prepare("
                    SELECT p.*, t.name as team_name 
                    FROM players p
                    LEFT JOIN teams t ON p.team_id = t.id
                    WHERE p.id = :id
                ");
                $stmt->bindParam(':id', $player_id);
                $stmt->execute();
                $player = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Error updating player";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Edit Player</h2>
    <a href="players.php" class="btn">Back to Players</a>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container" style="max-width: 800px;">
    <form method="POST" action="edit_player.php?id=<?php echo $player_id; ?>">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo $player['name']; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="position">Position *</label>
                    <select id="position" name="position" required>
                        <option value="">Select Position</option>
                        <option value="Goalkeeper" <?php echo ($player['position'] === 'Goalkeeper') ? 'selected' : ''; ?>>Goalkeeper</option>
                        <option value="Defender" <?php echo ($player['position'] === 'Defender') ? 'selected' : ''; ?>>Defender</option>
                        <option value="Midfielder" <?php echo ($player['position'] === 'Midfielder') ? 'selected' : ''; ?>>Midfielder</option>
                        <option value="Forward" <?php echo ($player['position'] === 'Forward') ? 'selected' : ''; ?>>Forward</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="jersey_number">Jersey Number *</label>
                    <input type="number" id="jersey_number" name="jersey_number" min="1" max="99" value="<?php echo $player['jersey_number']; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="age">Age *</label>
                    <input type="number" id="age" name="age" min="16" max="45" value="<?php echo $player['age']; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="nationality">Nationality *</label>
                    <input type="text" id="nationality" name="nationality" value="<?php echo $player['nationality']; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="team_id">Team</label>
                    <select id="team_id" name="team_id">
                        <option value="">Not Assigned</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team['id']; ?>" <?php echo ($player['team_id'] == $team['id']) ? 'selected' : ''; ?>>
                                <?php echo $team['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="bio">Biography</label>
            <textarea id="bio" name="bio" rows="4"><?php echo $player['bio']; ?></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Update Player</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
