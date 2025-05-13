<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/database.php';

// Get statistics
$playerCount = $conn->query("SELECT COUNT(*) as count FROM players")->fetch_assoc()['count'];
$teamCount = $conn->query("SELECT COUNT(*) as count FROM teams")->fetch_assoc()['count'];
$matchCount = $conn->query("SELECT COUNT(*) as count FROM matches")->fetch_assoc()['count'];
$trainingCount = $conn->query("SELECT COUNT(*) as count FROM training_sessions")->fetch_assoc()['count'];

// Get top scorers
$topScorers = $conn->query("SELECT p.name, p.position, t.name as team_name, ps.goals 
                           FROM player_stats ps 
                           JOIN players p ON ps.player_id = p.id 
                           LEFT JOIN teams t ON p.team_id = t.id 
                           ORDER BY ps.goals DESC 
                           LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Get recent matches
$recentMatches = $conn->query("SELECT m.*, t1.name as team1_name, t2.name as team2_name 
                              FROM matches m 
                              LEFT JOIN teams t1 ON m.team1_id = t1.id 
                              LEFT JOIN teams t2 ON m.team2_id = t2.id 
                              WHERE m.status = 'Completed' 
                              ORDER BY m.match_date DESC 
                              LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics & Reports - Football Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <button id="toggle-sidebar" class="btn">☰</button>
                <h2>Statistics & Reports</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <!-- Statistics Overview -->
            <div class="card-container">
                <div class="card">
                    <div class="card-header">
                        <h3>Players</h3>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $playerCount; ?></h2>
                        <p>Total Players</p>
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
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Matches</h3>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $matchCount; ?></h2>
                        <p>Total Matches</p>
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
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="form-row">
                <div class="form-col">
                    <div class="card">
                        <div class="card-header">
                            <h3>Player Positions Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="positionsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="card">
                        <div class="card-header">
                            <h3>Match Results</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="matchesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Scorers -->
            <div class="table-container">
                <h3>Top Scorers</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Position</th>
                            <th>Team</th>
                            <th>Goals</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topScorers as $player): ?>
                        <tr>
                            <td><?php echo $player['name']; ?></td>
                            <td><?php echo $player['position']; ?></td>
                            <td><?php echo $player['team_name'] ?? 'Not Assigned'; ?></td>
                            <td><?php echo $player['goals']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($topScorers)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No player statistics available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Recent Matches -->
            <div class="table-container">
                <h3>Recent Match Results</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Teams</th>
                            <th>Score</th>
                            <th>Venue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentMatches as $match): ?>
                        <tr>
                            <td><?php echo date('Y-m-d', strtotime($match['match_date'])); ?></td>
                            <td><?php echo $match['team1_name'] . ' vs ' . $match['team2_name']; ?></td>
                            <td><?php echo $match['team1_score'] . ' - ' . $match['team2_score']; ?></td>
                            <td><?php echo $match['venue']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recentMatches)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No recent matches available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Generate Reports Section -->
            <div class="form-container">
                <h3>Generate Reports</h3>
                <form method="GET" action="generate_report.php" target="_blank">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="report_type">Report Type</label>
                                <select id="report_type" name="report_type" required>
                                    <option value="">Select Report Type</option>
                                    <option value="players">Players Report</option>
                                    <option value="teams">Teams Report</option>
                                    <option value="matches">Matches Report</option>
                                    <option value="training">Training Sessions Report</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="date_range">Date Range</label>
                                <select id="date_range" name="date_range">
                                    <option value="all">All Time</option>
                                    <option value="month">Last Month</option>
                                    <option value="quarter">Last Quarter</option>
                                    <option value="year">Last Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        // Sample data for charts
        document.addEventListener('DOMContentLoaded', function() {
            // Positions Chart
            const positionsCtx = document.getElementById('positionsChart').getContext('2d');
            const positionsChart = new Chart(positionsCtx, {
                type: 'pie',
                data: {
                    labels: ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'],
                    datasets: [{
                        data: [3, 8, 10, 6],
                        backgroundColor: [
                            '#3498db',
                            '#2ecc71',
                            '#f1c40f',
                            '#e74c3c'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // Matches Chart
            const matchesCtx = document.getElementById('matchesChart').getContext('2d');
            const matchesChart = new Chart(matchesCtx, {
                type: 'bar',
                data: {
                    labels: ['Wins', 'Draws', 'Losses'],
                    datasets: [{
                        label: 'Match Results',
                        data: [12, 5, 8],
                        backgroundColor: [
                            '#2ecc71',
                            '#f1c40f',
                            '#e74c3c'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
