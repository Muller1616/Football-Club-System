<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Get all teams for dropdown
try {
    $stmt = $conn->query("SELECT id, name FROM teams ORDER BY name");
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching teams: " . $e->getMessage();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $home_team_id = (int)$_POST['home_team_id'];
    $away_team_id = (int)$_POST['away_team_id'];
    $match_date = $_POST['match_date'];
    $venue = sanitize_input($_POST['venue']);
    $ticket_price = (float)$_POST['ticket_price'];
    $available_tickets = (int)$_POST['available_tickets'];
    $status = sanitize_input($_POST['status']);
    
    // Set scores if match is completed
    $home_score = ($status === 'completed') ? (int)$_POST['home_score'] : null;
    $away_score = ($status === 'completed') ? (int)$_POST['away_score'] : null;
    
    // Validate input
    if (empty($home_team_id) || empty($away_team_id) || empty($match_date) || empty($venue) || empty($ticket_price)) {
        $error = "Please fill in all required fields";
    } elseif ($home_team_id === $away_team_id) {
        $error = "Home team and away team cannot be the same";
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO matches (home_team_id, away_team_id, match_date, venue, ticket_price, available_tickets, status, home_score, away_score) 
                VALUES (:home_team_id, :away_team_id, :match_date, :venue, :ticket_price, :available_tickets, :status, :home_score, :away_score)
            ");
            
            $stmt->bindParam(':home_team_id', $home_team_id);
            $stmt->bindParam(':away_team_id', $away_team_id);
            $stmt->bindParam(':match_date', $match_date);
            $stmt->bindParam(':venue', $venue);
            $stmt->bindParam(':ticket_price', $ticket_price);
            $stmt->bindParam(':available_tickets', $available_tickets);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':home_score', $home_score);
            $stmt->bindParam(':away_score', $away_score);
            
            if ($stmt->execute()) {
                $success = "Match added successfully";
                // Clear form data
                $home_team_id = $away_team_id = $venue = '';
                $ticket_price = $available_tickets = 0;
                $match_date = '';
                $status = 'upcoming';
            } else {
                $error = "Error adding match";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Add New Match</h2>
    <a href="matches.php" class="btn">Back to Matches</a>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container" style="max-width: 800px;">
    <form method="POST" action="add_match.php">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="home_team_id">Home Team *</label>
                    <select id="home_team_id" name="home_team_id" required>
                        <option value="">Select Home Team</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team['id']; ?>" <?php echo (isset($home_team_id) && $home_team_id == $team['id']) ? 'selected' : ''; ?>>
                                <?php echo $team['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="away_team_id">Away Team *</label>
                    <select id="away_team_id" name="away_team_id" required>
                        <option value="">Select Away Team</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team['id']; ?>" <?php echo (isset($away_team_id) && $away_team_id == $team['id']) ? 'selected' : ''; ?>>
                                <?php echo $team['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="match_date">Match Date & Time *</label>
                    <input type="datetime-local" id="match_date" name="match_date" value="<?php echo isset($match_date) ? $match_date : ''; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="venue">Venue *</label>
                    <input type="text" id="venue" name="venue" value="<?php echo isset($venue) ? $venue : ''; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="ticket_price">Ticket Price ($) *</label>
                    <input type="number" id="ticket_price" name="ticket_price" min="0" step="0.01" value="<?php echo isset($ticket_price) ? $ticket_price : ''; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="available_tickets">Available Tickets *</label>
                    <input type="number" id="available_tickets" name="available_tickets" min="0" value="<?php echo isset($available_tickets) ? $available_tickets : '1000'; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required onchange="toggleScoreFields()">
                        <option value="upcoming" <?php echo (isset($status) && $status === 'upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="completed" <?php echo (isset($status) && $status === 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo (isset($status) && $status === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div id="score-fields" style="display: none;">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="home_score">Home Team Score *</label>
                        <input type="number" id="home_score" name="home_score" min="0" value="<?php echo isset($home_score) ? $home_score : '0'; ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="away_score">Away Team Score *</label>
                        <input type="number" id="away_score" name="away_score" min="0" value="<?php echo isset($away_score) ? $away_score : '0'; ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Add Match</button>
        </div>
    </form>
</div>

<script>
    function toggleScoreFields() {
        const status = document.getElementById('status').value;
        const scoreFields = document.getElementById('score-fields');
        
        if (status === 'completed') {
            scoreFields.style.display = 'block';
            document.getElementById('home_score').required = true;
            document.getElementById('away_score').required = true;
        } else {
            scoreFields.style.display = 'none';
            document.getElementById('home_score').required = false;
            document.getElementById('away_score').required = false;
        }
    }
    
    // Call the function on page load
    document.addEventListener('DOMContentLoaded', toggleScoreFields);
</script>

<?php include '../includes/footer.php'; ?>
