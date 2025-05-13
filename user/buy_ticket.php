<?php
$base_path = '../';
include '../includes/db.php';
include '../includes/functions.php';

// Check if user is logged in and is fan
redirect_if_not_logged_in();
redirect_if_not_fan();

// Check if match ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: matches.php");
    exit();
}

$match_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get match details
try {
    $match = get_match_details($conn, $match_id);
    
    if (!$match || $match['status'] !== 'upcoming') {
        header("Location: matches.php");
        exit();
    }
} catch(PDOException $e) {
    $error = "Error fetching match: " . $e->getMessage();
}

// Process ticket purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)$_POST['quantity'];
    $total_price = (float)$_POST['total_price'];
    
    // Validate input
    if ($quantity <= 0) {
        $error = "Please select at least 1 ticket";
    } elseif ($quantity > $match['available_tickets']) {
        $error = "Not enough tickets available";
    } else {
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Insert ticket purchase
            $stmt = $conn->prepare("
                INSERT INTO tickets (match_id, user_id, quantity, total_price)
                VALUES (:match_id, :user_id, :quantity, :total_price)
            ");
            $stmt->bindParam(':match_id', $match_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':total_price', $total_price);
            $stmt->execute();
            
            // Update available tickets
            $new_available = $match['available_tickets'] - $quantity;
            $stmt = $conn->prepare("
                UPDATE matches
                SET available_tickets = :available_tickets
                WHERE id = :match_id
            ");
            $stmt->bindParam(':available_tickets', $new_available);
            $stmt->bindParam(':match_id', $match_id);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $success = "Tickets purchased successfully!";
            
            // Refresh match data
            $match = get_match_details($conn, $match_id);
        } catch(PDOException $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $error = "Error purchasing tickets: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h2>Buy Tickets</h2>
    <a href="matches.php" class="btn">Back to Matches</a>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="match-details-container">
    <div class="match-card">
        <div class="match-header">
            <h3><?php echo format_date($match['match_date']); ?></h3>
        </div>
        <div class="match-teams">
            <div class="match-team">
                <img src="<?php echo $base_path; ?>assets/images/teams/default_team.png" alt="<?php echo $match['home_team_name']; ?>">
                <h4><?php echo $match['home_team_name']; ?></h4>
            </div>
            <div class="match-vs">VS</div>
            <div class="match-team">
                <img src="<?php echo $base_path; ?>assets/images/teams/default_team.png" alt="<?php echo $match['away_team_name']; ?>">
                <h4><?php echo $match['away_team_name']; ?></h4>
            </div>
        </div>
        <div class="match-details">
            <p><strong>Venue:</strong> <?php echo $match['venue']; ?></p>
            <p><strong>Ticket Price:</strong> $<?php echo number_format($match['ticket_price'], 2); ?> per ticket</p>
            <p><strong>Available Tickets:</strong> <?php echo $match['available_tickets']; ?></p>
        </div>
    </div>
    
    <?php if ($match['available_tickets'] > 0): ?>
        <div class="ticket-purchase-form">
            <h3>Purchase Tickets</h3>
            <form method="POST" action="buy_ticket.php?id=<?php echo $match_id; ?>">
                <div class="form-group">
                    <label for="quantity">Number of Tickets</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $match['available_tickets']; ?>" value="1" class="ticket-quantity" data-price="<?php echo $match['ticket_price']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Total Price</label>
                    <p class="ticket-total-display">$<span class="ticket-total"><?php echo number_format($match['ticket_price'], 2); ?></span></p>
                    <input type="hidden" name="total_price" value="<?php echo $match['ticket_price']; ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Purchase Tickets</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="sold-out-message">
            <h3>Sold Out</h3>
            <p>Sorry, there are no more tickets available for this match.</p>
        </div>
    <?php endif; ?>
</div>

<style>
    .match-details-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    .ticket-purchase-form {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }
    
    .ticket-purchase-form h3 {
        color: #1a3a6c;
        margin-bottom: 1.5rem;
    }
    
    .ticket-total-display {
        font-size: 1.5rem;
        font-weight: bold;
        color: #1a3a6c;
    }
    
    .sold-out-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 2rem;
        border-radius: 8px;
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .match-details-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.querySelector('.ticket-quantity');
        const totalSpan = document.querySelector('.ticket-total');
        const totalInput = document.querySelector('input[name="total_price"]');
        
        if (quantityInput && totalSpan && totalInput) {
            quantityInput.addEventListener('change', function() {
                const quantity = parseInt(this.value);
                const price = parseFloat(this.dataset.price);
                
                if (!isNaN(quantity) && !isNaN(price)) {
                    const total = quantity * price;
                    totalSpan.textContent = total.toFixed(2);
                    totalInput.value = total.toFixed(2);
                }
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
