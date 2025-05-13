<?php
session_start();
// Check if user is logged in and is coach
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header("Location: ../login.php");
    exit();
}

include '../config/database.php';

// Get counts for dashboard
$playerCount = $conn->query("SELECT COUNT(*) as count FROM players")->fetch_assoc()['count'];
$teamCount = $conn->query("SELECT COUNT(*) as count FROM teams")->fetch_assoc()['count'];
$matchCount = $conn->query("SELECT COUNT(*) as count FROM matches")->fetch_assoc()['count'];
$trainingCount = $conn->query("SELECT COUNT(*) as count FROM training_sessions")->fetch_assoc()['count'];

// Get upcoming matches
$upcomingMatches = $conn->query("SELECT m.*, t1.name as team1_name, t2.name as team2_name 
                                FROM matches m 
                                LEFT JOIN teams t1 ON m.team1_id = t1.id 
                                LEFT JOIN teams t2 ON m.team2_id = t2.id 
                                WHERE m.match_date > NOW() AND m.status = 'Scheduled' 
                                ORDER BY m.match_date ASC 
                                LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Get upcoming training sessions
$upcomingTraining = $conn->query("SELECT ts.*, t.name as team_name 
                                 FROM training_sessions ts 
                                 LEFT JOIN teams t ON ts.team_id = t.id 
                                 WHERE ts.session_date > NOW() 
                                 ORDER BY ts.session_date ASC 
                                 LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard - Football Management System</title>
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
                <h2>Coach Dashboard</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <!-- Dashboard Cards -->
            <div class="card-container">
                <div class="card">
                    <div class="card-header">
                        <h3>Players</h3>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $playerCount; ?></h2>
                        <p>Total Players</p>
                    </div>
                    <div class="card-footer">
                        <a href="players.php" class="btn btn-primary">View Players</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Teams</h3>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $teamCount; ?></h2>
                        <p>Total Teams</p>
                    </div>
                    <div class="card-footer">
                        <a href="teams.php" class="btn btn-primary">View Teams</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Matches</h3>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $matchCount; ?></h2>
                        <p>Total Matches</p>
                    </div>
                    <div class="card-footer">
                        <a href="matches.php" class="btn btn-primary">View Matches</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Training Sessions</h3>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $trainingCount; ?></h2>
                        <p>Total Training Sessions</p>
                    </div>
                    <div class="card-footer">
                        <a href="training.php" class="btn btn-primary">View Training</a>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingMatches as $match): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($match['match_date'])); ?></td>
                            <td><?php echo $match['team1_name'] . ' vs ' . $match['team2_name']; ?></td>
                            <td><?php echo $match['venue']; ?></td>
                            <td>
                                <a href="view_match.php?id=<?php echo $match['id']; ?>" class="btn view-btn">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($upcomingMatches)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No upcoming matches</td>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingTraining as $session): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($session['session_date'])); ?></td>
                            <td><?php echo $session['team_name'] ?? 'All Teams'; ?></td>
                            <td><?php echo $session['location']; ?></td>
                            <td><?php echo $session['session_type']; ?></td>
                            <td>
                                <a href="view_training.php?id=<?php echo $session['id']; ?>" class="btn view-btn">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($upcomingTraining)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No upcoming training sessions</td>
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
