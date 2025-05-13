<?php
// Start session if not already started
function session_start_if_not_started() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function is_logged_in() {
    session_start_if_not_started();
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    session_start_if_not_started();
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Check if user is fan
function is_fan() {
    session_start_if_not_started();
    return isset($_SESSION['role']) && $_SESSION['role'] == 'fan';
}

// Redirect if not logged in
function redirect_if_not_logged_in() {
    if (!is_logged_in()) {
        header("Location: ../auth/login.php");
        exit();
    }
}

// Redirect if not admin
function redirect_if_not_admin() {
    if (!is_admin()) {
        header("Location: ../auth/login.php");
        exit();
    }
}

// Redirect if not fan
function redirect_if_not_fan() {
    if (!is_fan()) {
        header("Location: ../auth/login.php");
        exit();
    }
}

// Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format date
function format_date($date) {
    return date("F j, Y, g:i a", strtotime($date));
}

// Get team name by ID
function get_team_name($conn, $team_id) {
    try {
        $stmt = $conn->prepare("SELECT name FROM teams WHERE id = :team_id");
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['name'] : 'Unknown Team';
    } catch(PDOException $e) {
        return 'Unknown Team';
    }
}

// Get match details by ID
function get_match_details($conn, $match_id) {
    try {
        $stmt = $conn->prepare("
            SELECT m.*, 
                   home.name as home_team_name, 
                   away.name as away_team_name 
            FROM matches m
            JOIN teams home ON m.home_team_id = home.id
            JOIN teams away ON m.away_team_id = away.id
            WHERE m.id = :match_id
        ");
        $stmt->bindParam(':match_id', $match_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return null;
    }
}

// Check if username exists
function username_exists($conn, $username) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

// Check if email exists
function email_exists($conn, $email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

// Display alert message
function display_alert($message, $type = 'success') {
    return '<div class="alert alert-' . $type . '">' . $message . '</div>';
}
?>
