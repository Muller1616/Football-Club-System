<div class="sidebar">
    <div class="sidebar-header">
        <h3>Football Club</h3>
        <p>Admin Panel</p>
    </div>
    <div class="sidebar-menu">
        <ul>
            <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="players.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'players.php' ? 'active' : ''; ?>">Player Management</a></li>
            <li><a href="teams.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'teams.php' ? 'active' : ''; ?>">Team Management</a></li>
            <li><a href="matches.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'matches.php' ? 'active' : ''; ?>">Match Management</a></li>
            <li><a href="tickets.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tickets.php' ? 'active' : ''; ?>">Ticket Sales</a></li>
            <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">User Management</a></li>
        </ul>
    </div>
</div>
