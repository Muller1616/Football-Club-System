<?php
session_start();
// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Process registration form
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config/database.php';
    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'player'; // Default role is player
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email already exists";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // If registering as a player, create player record first
                    $player_id = null;
                    if ($role === 'player') {
                        // Insert basic player info - will be updated later
                        $player_name = $username; // Use username as initial name
                        $stmt = $conn->prepare("INSERT INTO players (name) VALUES (?)");
                        $stmt->bind_param("s", $player_name);
                        $stmt->execute();
                        $player_id = $conn->insert_id;
                    }
                    
                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, player_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role, $player_id);
                    $stmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    $success = "Registration successful! You can now log in.";
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $error = "Error: " . $e->getMessage();
                }
            }
        }
        
        $stmt->close();
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Football Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h1>Football Management System</h1>
            <h2>Register</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Register as</label>
                    <select id="role" name="role" required>
                        <option value="player">Player</option>
                        <option value="coach">Coach</option>
                    </select>
                    <p class="form-note">Note: Admin accounts can only be created by existing admins</p>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
                
                <div class="form-group text-center">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
