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

echo "<h2>Setting up Football Management System Database</h2>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "<p>Database created successfully or already exists</p>";
} else {
    echo "<p>Error creating database: " . $conn->error . "</p>";
}

// Select the database
$conn->select_db($database);

// Read SQL file
$sql = file_get_contents('database.sql');

// Execute multi query
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // Check for more results
    } while ($conn->more_results() && $conn->next_result());
    
    echo "<p>Database setup completed successfully</p>";
    echo "<p>You can now <a href='login.php'>log in</a> with:</p>";
    echo "<p>Username: admin</p>";
    echo "<p>Password: admin123</p>";
} else {
    echo "<p>Error setting up database: " . $conn->error . "</p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Football Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h2 {
            color: #2c3e50;
        }
        p {
            margin-bottom: 10px;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <p>If you're seeing this page, the setup process has completed.</p>
    <p><a href="login.php">Go to login page</a></p>
</body>
</html>
