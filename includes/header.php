<?php
include_once __DIR__ . '/functions.php';
session_start_if_not_started();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Club Management System</title>
    <link rel="stylesheet" href="<?php echo isset($base_path) ? $base_path : ''; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>Football Club</h1>
        </div>
        <nav>
            <ul>
                <?php if (is_logged_in()): ?>
                    <?php if (is_admin()): ?>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>admin/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>admin/players.php">Players</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>admin/matches.php">Matches</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>admin/teams.php">Teams</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>admin/tickets.php">Tickets</a></li>
                    <?php elseif (is_fan()): ?>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>user/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>user/matches.php">Matches</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>user/tickets.php">My Tickets</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>user/players.php">Players</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php">Home</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>auth/login.php">Login</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container">
