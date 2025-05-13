<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

session_start_if_not_started();

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../user/dashboard.php");
    }
    exit();
}

$error = '';
$success = '';
$username = '';
$email = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? sanitize_input($_POST['role']) : 'fan';
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        try {
            // Check if username already exists
            if (username_exists($conn, $username)) {
                $error = "Username already exists";
            } 
            // Check if email already exists
            elseif (email_exists($conn, $email)) {
                $error = "Email already exists";
            } 
            else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':role', $role);
                
                if ($stmt->execute()) {
                    $success = "Registration successful! You can now log in.";
                    $username = '';
                    $email = '';
                } else {
                    $error = "Error: Could not register user";
                }
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="form-container">
    <h2>Register</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <small>Password must be at least 6 characters long</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div class="form-group">
            <label>Register As</label>
            <div class="radio-group">
                <div class="radio-option">
                    <input type="radio" id="role-fan" name="role" value="fan" checked>
                    <label for="role-fan">Fan</label>
                </div>
                <div class="radio-option">
                    <input type="radio" id="role-admin" name="role" value="admin">
                    <label for="role-admin">Admin</label>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Register</button>
        </div>
        
        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
