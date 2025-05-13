<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/database.php';

// Get report parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : '';
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'all';

// Set date filter based on range
$date_filter = '';
if ($date_range === 'month') {
    $date_filter = "WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
} elseif ($date_range === 'quarter') {
    $date_filter = "WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
} elseif ($date_range === 'year') {
    $date_filter = "WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
}

// Get report data based on type
$report_data = [];
$report_title = '';

if ($report_type === 'players') {
    $report_title = 'Players Report';
    $query = "SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id ORDER BY p.name";
    $result = $conn->query($query);
    $report_data = $result->fetch_all(MYSQLI_ASSOC);
} elseif ($report_type === 'teams') {
    $report_title = 'Teams Report';
    $query = "SELECT t.*, COUNT(p.id) as player_count FROM teams t LEFT JOIN players p ON t.id = p.team_id GROUP BY t.id ORDER BY t.name";
    $result = $conn->query($query);
    $report_data = $result->fetch_all(MYSQLI_ASSOC);
} elseif ($report_type === 'matches') {
    $report_title = 'Matches Report';
    $query = "SELECT m.*, t1.name as team1_name, t2.name as team2_name FROM matches m LEFT JOIN teams t1 ON m.team1_id = t1.id LEFT JOIN teams t2 ON m.team2_id = t2.id ORDER BY m.match_date DESC";
    $result = $conn->query($query);
    $report_data = $result->fetch_all(MYSQLI_ASSOC);
} elseif ($report_type === 'training') {
    $report_title = 'Training Sessions Report';
    $query = "SELECT ts.*, t.name as team_name FROM training_sessions ts LEFT JOIN teams t ON ts.team_id = t.id ORDER BY ts.session_date DESC";
    $result = $conn->query($query);
    $report_data = $result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $report_title; ?> - Football Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-bottom: 30px;">
            <h1><?php echo $report_title; ?></h1>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
            <?php if ($date_range !== 'all'): ?>
                <p>Date Range: <?php echo ucfirst($date_range); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="window.print()" class="btn btn-primary">Print Report</button>
            <a href="statistics.php" class="btn">Back to Statistics</a>
        </div>
        
        <?php if ($report_type === 'players'): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Position</th>
                            <th>Team</th>
                            <th>Jersey Number</th>
                            <th>Nationality</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $player): ?>
                        <tr>
                            <td><?php echo $player['id']; ?></td>
                            <td><?php echo $player['name']; ?></td>
                            <td><?php echo $player['age']; ?></td>
                            <td><?php echo $player['position']; ?></td>
                            <td><?php echo $player['team_name'] ?? 'Not Assigned'; ?></td>
                            <td><?php echo $player['jersey_number']; ?></td>
                            <td><?php echo $player['nationality']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No data available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type === 'teams'): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Coach</th>
                            <th>Founded</th>
                            <th>Stadium</th>
                            <th>Players</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $team): ?>
                        <tr>
                            <td><?php echo $team['id']; ?></td>
                            <td><?php echo $team['name']; ?></td>
                            <td><?php echo $team['coach']; ?></td>
                            <td><?php echo $team['founded_year']; ?></td>
                            <td><?php echo $team['stadium']; ?></td>
                            <td><?php echo $team['player_count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No data available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type === 'matches'): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date & Time</th>
                            <th>Teams</th>
                            <th>Venue</th>
                            <th>Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $match): ?>
                        <tr>
                            <td><?php echo $match['id']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($match['match_date'])); ?></td>
                            <td><?php echo $match['team1_name'] . ' vs ' . $match['team2_name']; ?></td>
                            <td><?php echo $match['venue']; ?></td>
                            <td>
                                <?php 
                                if ($match['status'] === 'Completed') {
                                    echo $match['team1_score'] . ' - ' . $match['team2_score'];
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td><?php echo $match['status']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No data available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type === 'training'): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date & Time</th>
                            <th>Team</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Coach</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $session): ?>
                        <tr>
                            <td><?php echo $session['id']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($session['session_date'])); ?></td>
                            <td><?php echo $session['team_name'] ?? 'All Teams'; ?></td>
                            <td><?php echo $session['location']; ?></td>
                            <td><?php echo $session['session_type']; ?></td>
                            <td><?php echo $session['coach']; ?></td>
                            <td><?php echo $session['duration']; ?> min</td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No data available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="error-message">Invalid report type selected.</div>
        <?php endif; ?>
        
        <div class="footer" style="margin-top: 30px; text-align: center;">
            <p>Football Management System &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>
</body>
</html>
