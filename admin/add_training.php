<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/database.php';

// Get all teams for dropdown
$teams = $conn->query("SELECT id, name FROM teams ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = !empty($_POST['team_id']) ? $_POST['team_id'] : null;
    $session_date = $_POST['session_date'];
    $location = $_POST['location'];
    $session_type = $_POST['session_type'];
    $coach = $_POST['coach'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];
    
    // Insert training session
    $stmt = $conn->prepare("INSERT INTO training_sessions (team_id, session_date, location, session_type, coach, duration, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $team_id, $session_date, $location, $session_type, $coach, $duration, $description);
    
    if ($stmt->execute()) {
        header("Location: training.php?message=Training session added successfully");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Training Session - Football Management System</title>
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
                <h2>Add New Training Session</h2>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Add Training Session Form -->
            <div class="form-container">
                <form method="POST" action="add_training.php">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="team_id">Team</label>
                                <select id="team_id" name="team_id">
                                    <option value="">All Teams</option>
                                    <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo $team['id']; ?>"><?php echo $team['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="session_date">Session Date & Time</label>
                                <input type="datetime-local" id="session_date" name="session_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="session_type">Session Type</label>
                                <select id="session_type" name="session_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Fitness">Fitness</option>
                                    <option value="Tactical">Tactical</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Match Preparation">Match Preparation</option>
                                    <option value="Recovery">Recovery</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="coach">Coach</label>
                                <input type="text" id="coach" name="coach" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" id="duration" name="duration" min="15" max="240" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Add Training Session</button>
                        <a href="training.php" class="btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
