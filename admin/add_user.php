<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize_input($_POST['role']);
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
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
                $stmt = $conn->prepare("
                    INSERT INTO users (username, email, password, role)
                    VALUES (:username, :email, :password, :role)
                ");
                
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':role', $role);
                
                if ($stmt->execute()) {
                    $success = "User added successfully";
                    // Clear form data
                    $username = $email = '';
                } else {
                    $error = "Error adding user";
                }
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Add New User</h2>
    <a href="users.php" class="btn">Back to Users</a>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container" style="max-width: 800px;">
    <form method="POST" action="add_user.php">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                    <small>Password must be at least 6 characters long</small>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="fan">Fan</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Add User</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
