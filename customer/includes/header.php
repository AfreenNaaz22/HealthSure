<?php
// Handle both customer and agent access
$user_role = $_SESSION['role'] ?? null;
$user_info = null;

if ($user_role === 'customer') {
    $user_info = get_customer_info($_SESSION['user_id'], $conn);
} elseif ($user_role === 'agent') {
    // Get agent info
    try {
        $stmt = $conn->prepare("SELECT a.*, u.email FROM agents a JOIN users u ON a.user_id = u.user_id WHERE a.user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $user_info = ['first_name' => 'Agent', 'last_name' => ''];
    }
}

// Fallback if no user info found
if (!$user_info) {
    $user_info = ['first_name' => 'User', 'last_name' => ''];
}
?>
<div class="navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 style="margin: 0; color: var(--text-dark);">
                <?php 
                $page_titles = [
                    'dashboard.php' => 'Dashboard',
                    'browse-policies.php' => 'Browse Policies',
                    'my-policies.php' => 'My Policies',
                    'my-claims.php' => 'My Claims',
                    'file-claim.php' => 'File New Claim',
                    'make-payment.php' => 'Make Payment',
                    'payment-history.php' => 'Payment History',
                    'profile.php' => 'My Profile',
                    'support.php' => 'Support'
                ];
                echo $page_titles[basename($_SERVER['PHP_SELF'])] ?? ($user_role === 'agent' ? 'Agent Portal' : 'Customer Portal');
                ?>
            </h4>
        </div>
        <div class="d-flex align-items-center" style="gap: 1rem;">
            <span style="color: var(--text-light);">Welcome, <?php echo htmlspecialchars($user_info['first_name'] ?? 'User'); ?></span>
            <div style="width: 40px; height: 40px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                <?php echo strtoupper(substr($user_info['first_name'] ?? 'U', 0, 1)); ?>
            </div>
        </div>
    </div>
</div>
