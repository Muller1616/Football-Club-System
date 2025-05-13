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

// Get upcoming matches - only if player has a team
$upcomingMatches = [];
if (!empty($player['team_id'])) {
    $team_id = $player['team_id'];
    $result = $conn->query("SELECT m.*, t1.name as team1_name, t2.name as team2_name 
                          FROM matches m 
                          LEFT JOIN teams t1 ON m.team1_id = t1.id 
                          LEFT JOIN teams t2 ON m.team2_id = t2.id 
                          WHERE (m.team1_id = $team_id OR m.team2_id = $team_id) 
                          AND m.match_date > NOW() AND m.status = 'Scheduled' 
                          ORDER BY m.match_date ASC 
                          LIMIT 5");
    if ($result) {
        $upcomingMatches = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Get upcoming training sessions - only if player has a team
$upcomingTraining = [];
if (!empty($player['team_id'])) {
    $team_id = $player['team_id'];
    $result = $conn->query("SELECT ts.*, t.name as team_name 
                           FROM training_sessions ts 
                           LEFT JOIN teams t ON ts.team_id = t.id 
                           WHERE (ts.team_id = $team_id OR ts.team_id IS NULL) 
                           AND ts.session_date > NOW() 
                           ORDER BY ts.session_date ASC 
                           LIMIT 5");
    if ($result) {
        $upcomingTraining = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Get player stats if available
$playerStats = [
    'matches_played' => 0,
    'goals' => 0,
    'assists' => 0
];

if ($player_id > 0) {
    $result = $conn->query("SELECT * FROM player_stats WHERE player_id = $player_id");
    if ($result && $result->num_rows > 0) {
        $stats = $result->fetch_assoc();
        $playerStats['matches_played'] = $stats['matches_played'];
        $playerStats['goals'] = $stats['goals'];
        $playerStats['assists'] = $stats['assists'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Dashboard - Football Management System</title>
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
                <h2>Player Dashboard</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <!-- Player Profile -->
            <div class="card-container">
                <div class="card" style="grid-column: span 2;">
                    <div class="card-header">
                        <h3>My Profile</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-col">
                                <p><strong>Name:</strong> <?php echo $player['name']; ?></p>
                                <p><strong>Age:</strong> <?php echo $player['age'] ? $player['age'] : 'Not set'; ?></p>
                                <p><strong>Position:</strong> <?php echo $player['position'] ? $player['position'] : 'Not set'; ?></p>
                            </div>
                            <div class="form-col">
                                <p><strong>Team:</strong> <?php echo $player['team_name']; ?></p>
                                <p><strong>Jersey Number:</strong> <?php echo $player['jersey_number'] ? $player['jersey_number'] : 'Not set'; ?></p>
                                <p><strong>Nationality:</strong> <?php echo $player['nationality'] ? $player['nationality'] : 'Not set'; ?></p>
                            </div>
                        </div>
                        <?php if (!$player['team_id']): ?>
                        <div class="info-message">
                            <p>Your profile is incomplete. Please contact an administrator to update your details and assign you to a team.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="profile.php" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Team</h3>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $player['team_name']; ?></h2>
                    </div>
                    <div class="card-footer">
                        <a href="team.php" class="btn btn-primary">View Team</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Performance</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Goals:</strong> <?php echo $playerStats['goals']; ?></p>
                        <p><strong>Assists:</strong> <?php echo $playerStats['assists']; ?></p>
                        <p><strong>Matches Played:</strong> <?php echo $playerStats['matches_played']; ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="statistics.php" class="btn btn-primary">View Statistics</a>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Matches -->
            <div class="table-container">
                <h3>Upcoming Matches</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Teams</th>
                            <th>Venue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingMatches as $match): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($match['match_date'])); ?></td>
                            <td><?php echo $match['team1_name'] . ' vs ' . $match['team2_name']; ?></td>
                            <td><?php echo $match['venue']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($upcomingMatches)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">
                                <?php echo $player['team_id'] ? 'No upcoming matches' : 'You need to be assigned to a team to view matches'; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Upcoming Training Sessions -->
            <div class="table-container">
                <h3>Upcoming Training Sessions</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Team</th>
                            <th>Location</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingTraining as $session): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($session['session_date'])); ?></td>
                            <td><?php echo $session['team_name'] ?? 'All Teams'; ?></td>
                            <td><?php echo $session['location']; ?></td>
                            <td><?php echo $session['session_type']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($upcomingTraining)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">
                                <?php echo $player['team_id'] ? 'No upcoming training sessions' : 'You need to be assigned to a team to view training sessions'; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
