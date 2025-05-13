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

// Get team information if player is assigned to a team
$team = null;
$teammates = [];

if (!empty($player['team_id'])) {
    $team_id = $player['team_id'];
    
    // Get team details
    $result = $conn->query("SELECT * FROM teams WHERE id = $team_id");
    if ($result && $result->num_rows > 0) {
        $team = $result->fetch_assoc();
        
        // Get teammates
        $result = $conn->query("SELECT * FROM players WHERE team_id = $team_id AND id != $player_id ORDER BY position, name");
        if ($result) {
            $teammates = $result->fetch_all(MYSQLI_ASSOC);
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Team - Football Management System</title>
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
                <h2>My Team</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <?php if (empty($player['team_id'])): ?>
                <div class="info-message">
                    <p>You are not currently assigned to any team. Please contact an administrator to be assigned to a team.</p>
                </div>
            <?php else: ?>
                <!-- Team Information -->
                <div class="card-container">
                    <div class="card" style="grid-column: span 2;">
                        <div class="card-header">
                            <h3><?php echo $team['name']; ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-col">
                                    <p><strong>Coach:</strong> <?php echo $team['coach']; ?></p>
                                    <p><strong>Founded:</strong> <?php echo $team['founded_year']; ?></p>
                                </div>
                                <div class="form-col">
                                    <p><strong>Stadium:</strong> <?php echo $team['stadium']; ?></p>
                                    <p><strong>Players:</strong> <?php echo count($teammates) + 1; ?></p>
                                </div>
                            </div>
                            <?php if (!empty($team['description'])): ?>
                                <p><strong>Description:</strong> <?php echo $team['description']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Teammates -->
                <div class="table-container">
                    <h3>Teammates</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Jersey Number</th>
                                <th>Age</th>
                                <th>Nationality</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Current player -->
                            <tr style="background-color: #f0f8ff;">
                                <td><?php echo $player['name']; ?> (You)</td>
                                <td><?php echo $player['position'] ?? 'Not set'; ?></td>
                                <td><?php echo $player['jersey_number'] ?? 'Not set'; ?></td>
                                <td><?php echo $player['age'] ?? 'Not set'; ?></td>
                                <td><?php echo $player['nationality'] ?? 'Not set'; ?></td>
                            </tr>
                            
                            <!-- Teammates -->
                            <?php foreach ($teammates as $teammate): ?>
                            <tr>
                                <td><?php echo $teammate['name']; ?></td>
                                <td><?php echo $teammate['position'] ?? 'Not set'; ?></td>
                                <td><?php echo $teammate['jersey_number'] ?? 'Not set'; ?></td>
                                <td><?php echo $teammate['age'] ?? 'Not set'; ?></td>
                                <td><?php echo $teammate['nationality'] ?? 'Not set'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($teammates)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No other teammates found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
