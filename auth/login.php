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
$username = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $role = isset($_POST['role']) ? sanitize_input($_POST['role']) : 'fan';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        try {
            // Check if user exists with the given role
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = :username AND role = :role");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: ../admin/dashboard.php");
                    } else {
                        header("Location: ../user/dashboard.php");
                    }
                    exit();
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "No user found with the provided username and role";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="form-container">
    <h2>Login</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label>Login As</label>
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
            <button type="submit" class="btn">Login</button>
        </div>
        
        <div class="form-footer">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
