<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/database.php';

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM training_sessions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: training.php?message=Training session deleted successfully");
    exit();
}

// Get all training sessions
$result = $conn->query("SELECT ts.*, t.name as team_name 
                        FROM training_sessions ts 
                        LEFT JOIN teams t ON ts.team_id = t.id 
                        ORDER BY ts.session_date DESC");
$sessions = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Sessions - Football Management System</title>
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
                <h2>Training Sessions</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <!-- Success Message -->
            <?php if (isset($_GET['message'])): ?>
                <div class="success-message"><?php echo $_GET['message']; ?></div>
            <?php endif; ?>
            
            <!-- Add Training Session Button -->
            <div class="action-buttons" style="margin-bottom: 20px;">
                <a href="add_training.php" class="btn btn-primary">Add New Training Session</a>
            </div>
            
            <!-- Training Sessions Table -->
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?php echo $session['id']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($session['session_date'])); ?></td>
                            <td><?php echo $session['team_name'] ?? 'All Teams'; ?></td>
                            <td><?php echo $session['location']; ?></td>
                            <td><?php echo $session['session_type']; ?></td>
                            <td><?php echo $session['coach']; ?></td>
                            <td class="action-buttons">
                                <a href="view_training.php?id=<?php echo $session['id']; ?>" class="btn view-btn">View</a>
                                <a href="edit_training.php?id=<?php echo $session['id']; ?>" class="btn edit-btn">Edit</a>
                                <a href="training.php?delete=<?php echo $session['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this training session?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No training sessions found</td>
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
