<?php
session_start();
// Check if user is logged in and is player
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header("Location: ../login.php");
    exit();
}

include '../config/database.php';

// Get player information
$player_id = isset($_SESSION['player_id']) ? $_SESSION['player_id'] : 0;
$player = null;

if ($player_id > 0) {
    $result = $conn->query("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.id = $player_id");
    if ($result && $result->num_rows > 0) {
        $player = $result->fetch_assoc();
    }
}

// If player record not found, create a basic one
if (!$player) {
    $player = [
        'id' => $player_id,
        'name' => $_SESSION['username'],
        'age' => null,
        'position' => null,
        'team_id' => null,
        'team_name' => 'Not Assigned',
        'jersey_number' => null,
        'nationality' => null
    ];
}

// Process form submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only allow updating certain fields (not team assignment)
    $name = $_POST['name'];
    $nationality = $_POST['nationality'];
    
    if (empty($name)) {
        $error = "Name cannot be empty";
    } else {
        // Update player information
        $stmt = $conn->prepare("UPDATE players SET name = ?, nationality = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $nationality, $player_id);
        
        if ($stmt->execute()) {
            $success = "Profile updated successfully";
            // Refresh player data
            $result = $conn->query("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.id = $player_id");
            if ($result && $result->num_rows > 0) {
                $player = $result->fetch_assoc();
            }
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Football Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <button id="toggle-sidebar" class="btn">☰</button>
                <h2>My Profile</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Profile Form -->
            <div class="form-container">
                <form method="POST" action="profile.php">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" value="<?php echo $player['name']; ?>" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nationality">Nationality</label>
                                <input type="text" id="nationality" name="nationality" value="<?php echo $player['nationality']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="age">Age</label>
                                <input type="text" id="age" value="<?php echo $player['age']; ?>" disabled>
                                <p class="form-note">Age can only be updated by an administrator</p>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="text" id="position" value="<?php echo $player['position']; ?>" disabled>
                                <p class="form-note">Position can only be updated by an administrator</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="team">Team</label>
                                <input type="text" id="team" value="<?php echo $player['team_name']; ?>" disabled>
                                <p class="form-note">Team assignment can only be updated by an administrator</p>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="jersey_number">Jersey Number</label>
                                <input type="text" id="jersey_number" value="<?php echo $player['jersey_number']; ?>" disabled>
                                <p class="form-note">Jersey number can only be updated by an administrator</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="dashboard.php" class="btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
