<?php
$base_path = './';
include 'includes/header.php';
?>

<div class="hero">
    <h2>Welcome to the Heart of Football!</h2>
    <p>Easily handle matches, tickets, and club details with ease.</p>
    
    <?php if (!is_logged_in()): ?>
        <div class="hero-buttons">
            <a href="auth/login.php" class="btn">Login</a>
            <a href="auth/register.php" class="btn">Register</a>
        </div>
    <?php else: ?>
        <div class="hero-buttons">
            <?php if (is_admin()): ?>
                <a href="admin/dashboard.php" class="btn">Admin Dashboard</a>
            <?php else: ?>
                <a href="user/dashboard.php" class="btn">User Dashboard</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div class="features">
    <div class="feature-card">
        <i class="fas fa-futbol"></i>
        <h3>Match Management</h3>
        <p>Schedule and manage football matches with ease.</p>
    </div>
    
    <div class="feature-card">
        <i class="fas fa-users"></i>
        <h3>Player Profiles</h3>
        <p>Maintain detailed profiles of all players in the club.</p>
    </div>
    
    <div class="feature-card">
        <i class="fas fa-ticket-alt"></i>
        <h3>Ticket Booking</h3>
        <p>Allow fans to book tickets for upcoming matches.</p>
    </div>
    
    <div class="feature-card">
        <i class="fas fa-chart-line"></i>
        <h3>Statistics</h3>
        <p>Track and analyze team and player performance.</p>
    </div>
</div>

<style>
    .hero {
        background-color: #1a3a6c;
        color: #fff;
        padding: 3rem 2rem;
        text-align: center;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    
    .hero h2 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .hero p {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .hero-buttons {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }
    
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .feature-card {
        background-color: #fff;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    
    .feature-card i {
        font-size: 3rem;
        color: #1a3a6c;
        margin-bottom: 1rem;
    }
    
    .feature-card h3 {
        color: #1a3a6c;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .hero h2 {
            font-size: 2rem;
        }
        
        .hero p {
            font-size: 1rem;
        }
        
        .hero-buttons {
            flex-direction: column;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
