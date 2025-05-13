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
} catch(PDOException $e) {
    $error = "Error fetching team: " . $e->getMessage();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $coach = sanitize_input($_POST['coach']);
    $founded_year = (int)$_POST['founded_year'];
    $description = sanitize_input($_POST['description']);
    
    // Validate input
    if (empty($name) || empty($coach) || empty($founded_year)) {
        $error = "Please fill in all required fields";
    } else {
        try {
            $stmt = $conn->prepare("
                UPDATE teams 
                SET name = :name, coach = :coach, founded_year = :founded_year, description = :description
                WHERE id = :id
            ");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':coach', $coach);
            $stmt->bindParam(':founded_year', $founded_year);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id', $team_id);
            
            if ($stmt->execute()) {
                $success = "Team updated successfully";
                
                // Refresh team data
                $stmt = $conn->prepare("SELECT * FROM teams WHERE id = :id");
                $stmt->bindParam(':id', $team_id);
                $stmt->execute();
                $team = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Error updating team";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Edit Team</h2>
    <a href="teams.php" class="btn">Back to Teams</a>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container" style="max-width: 800px;">
    <form method="POST" action="edit_team.php?id=<?php echo $team_id; ?>">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="name">Team Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo $team['name']; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="coach">Coach *</label>
                    <input type="text" id="coach" name="coach" value="<?php echo $team['coach']; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="founded_year">Founded Year *</label>
                    <input type="number" id="founded_year" name="founded_year" min="1800" max="<?php echo date('Y'); ?>" value="<?php echo $team['founded_year']; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo $team['description']; ?></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Update Team</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
