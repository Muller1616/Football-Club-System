<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
redirect_if_not_logged_in();
redirect_if_not_admin();

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// Get user details
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header("Location: users.php");
        exit();
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching user: " . $e->getMessage();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $role = sanitize_input($_POST['role']);
    $password = $_POST['password']; // Optional, only if changing password
    
    // Validate input
    if (empty($username) || empty($email) || empty($role)) {
        $error = "Please fill in all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        try {
            // Check if username already exists (excluding current user)
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error = "Username already exists";
            } else {
                // Check if email already exists (excluding current user)
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':id', $user_id);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $error = "Email already exists";
                } else {
                    // Update user
                    if (!empty($password)) {
                        // If password is provided, update it too
                        if (strlen($password) < 6) {
                            $error = "Password must be at least 6 characters long";
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            
                            $stmt = $conn->prepare("
                                UPDATE users 
                                SET username = :username, email = :email, password = :password, role = :role
                                WHERE id = :id
                            ");
                            
                            $stmt->bindParam(':username', $username);
                            $stmt->bindParam(':email', $email);
                            $stmt->bindParam(':password', $hashed_password);
                            $stmt->bindParam(':role', $role);
                            $stmt->bindParam(':id', $user_id);
                        }
                    } else {
                        // If no password provided, just update other fields
                        $stmt = $conn->prepare("
                            UPDATE users 
                            SET username = :username, email = :email, role = :role
                            WHERE id = :id
                        ");
                        
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':role', $role);
                        $stmt->bindParam(':id', $user_id);
                    }
                    
                    if (!isset($error) && $stmt->execute()) {
                        $success = "User updated successfully";
                        
                        // Refresh user data
                        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
                        $stmt->bindParam(':id', $user_id);
                        $stmt->execute();
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    } else if (!isset($error)) {
                        $error = "Error updating user";
                    }
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
    <h2>Edit User</h2>
    <a href="users.php" class="btn">Back to Users</a>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container" style="max-width: 800px;">
    <form method="POST" action="edit_user.php?id=<?php echo $user_id; ?>">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                    <small>Leave blank to keep current password</small>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="fan" <?php echo ($user['role'] === 'fan') ? 'selected' : ''; ?>>Fan</option>
                        <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Update User</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
