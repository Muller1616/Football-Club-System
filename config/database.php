<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'football_management';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if database exists, if not create it
$result = $conn->query("SHOW DATABASES LIKE '$database'");
if ($result->num_rows == 0) {
    // Database doesn't exist, create it
    $conn->query("CREATE DATABASE $database");
}

// Select the database
$conn->select_db($database);

// Check if tables exist
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    // Tables don't exist, redirect to setup
    header("Location: ../setup.php");
    exit();
}
?>
