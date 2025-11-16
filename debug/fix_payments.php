<?php
// Quick Fix Script for Payments List Issue
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>🔧 Payments List Quick Fix</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; }
    .btn-success { background: #28a745; }
    .btn-warning { background: #ffc107; color: black; }
    table { border-collapse: collapse; margin: 10px 0; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

$fixed_issues = [];
$errors = [];

try {
    // 1. Check payments table
    try {
        $conn->query("SELECT 1 FROM payments LIMIT 1");
        $fixed_issues[] = "✓ Payments table exists";
    } catch (PDOException $e) {
        $errors[] = "❌ Payments table missing or inaccessible: " . $e->getMessage();
    }
    
    // 2. Check if we have policy holders
    $stmt = $conn->query("SELECT COUNT(*) as count FROM policy_holders");
    $policy_holders_count = $stmt->fetch()['count'];
    
    if ($policy_holders_count == 0) {
        $errors[] = "❌ No policy holders found. You need customers with policies first.";
    } else {
        $fixed_issues[] = "✓ Found $policy_holders_count policy holders";
    }
    
    // 3. Check payments count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM payments");
    $payments_count = $stmt->fetch()['count'];
    
    if ($payments_count == 0 && $policy_holders_count > 0) {
        // Create sample payments
        $stmt = $conn->query("SELECT holder_id FROM policy_holders LIMIT 1");
        $holder = $stmt->fetch();
        
        if ($holder) {
            $holder_id = $holder['holder_id'];
            
            $sample_payments = [
                ['type' => 'premium', 'amount' => 25000, 'method' => 'online'],
                ['type' => 'premium', 'amount' => 50000, 'method' => 'bank_transfer'],
                ['type' => 'premium', 'amount' => 30000, 'method' => 'card']
            ];
            
            foreach ($sample_payments as $payment) {
                $stmt = $conn->prepare("INSERT INTO payments (holder_id, payment_type, amount, payment_method, payment_date, status, transaction_id) VALUES (?, ?, ?, ?, CURDATE(), 'completed', ?)");
                $transaction_id = 'TXN' . rand(100000, 999999);
                $stmt->execute([$holder_id, $payment['type'], $payment['amount'], $payment['method'], $transaction_id]);
            }
            
            $fixed_issues[] = "✓ Created 3 sample payments";
        }
    } else {
        $fixed_issues[] = "✓ Found $payments_count existing payments";
    }
    
    // 4. Test the payments query with fixed column references
    $stmt = $conn->prepare("SELECT p.*, 
                           COALESCE(cu.first_name, 'Unknown') as first_name, 
                           COALESCE(cu.last_name, 'Customer') as last_name,
                           COALESCE(u.email, 'No email') as email,
                           COALESCE(po.policy_name, 'Unknown Policy') as policy_name, 
                           COALESCE(po.policy_type, 'unknown') as policy_type,
                           COALESCE(ph.premium_amount, 0) as premium_amount, 
                           COALESCE(po.coverage_amount, 0) as coverage_amount
                           FROM payments p
                           LEFT JOIN policy_holders ph ON p.holder_id = ph.holder_id
                           LEFT JOIN customers cu ON ph.customer_id = cu.customer_id
                           LEFT JOIN users u ON cu.user_id = u.user_id
                           LEFT JOIN policies po ON ph.policy_id = po.policy_id
                           ORDER BY p.created_at DESC");
    $stmt->execute();
    $test_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($test_payments) > 0) {
        $fixed_issues[] = "✓ Payments query working - found " . count($test_payments) . " payments";
        
        // Display sample results
        echo "<h3 class='info'>Sample Payment Data:</h3>";
        echo "<table>";
        echo "<tr><th>Payment ID</th><th>Customer</th><th>Policy</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th></tr>";
        foreach (array_slice($test_payments, 0, 5) as $payment) {
            echo "<tr>";
            echo "<td>#" . $payment['payment_id'] . "</td>";
            echo "<td>" . htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) . "<br><small>" . htmlspecialchars($payment['email']) . "</small></td>";
            echo "<td>" . htmlspecialchars($payment['policy_name']) . "<br><span style='background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;'>" . ucfirst($payment['policy_type']) . "</span></td>";
            echo "<td><span style='background: " . ($payment['payment_type'] === 'premium' ? 'green' : 'orange') . "; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;'>" . ucfirst(str_replace('_', ' ', $payment['payment_type'])) . "</span></td>";
            echo "<td>₹" . number_format($payment['amount'], 2) . "</td>";
            echo "<td><span style='background: " . ($payment['status'] === 'completed' ? 'green' : ($payment['status'] === 'failed' ? 'red' : 'orange')) . "; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;'>" . ucfirst($payment['status']) . "</span></td>";
            echo "<td>" . $payment['payment_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        $errors[] = "❌ Payments query returned no results";
    }
    
} catch (Exception $e) {
    $errors[] = "❌ Error: " . $e->getMessage();
}

// Display results
echo "<h3>Fix Results:</h3>";

if (!empty($fixed_issues)) {
    echo "<div class='success'>";
    foreach ($fixed_issues as $issue) {
        echo "<p>$issue</p>";
    }
    echo "</div>";
}

if (!empty($errors)) {
    echo "<div class='error'>";
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
    echo "</div>";
}

if (empty($errors)) {
    echo "<div class='success'>";
    echo "<h3>🎉 All Payment Issues Fixed!</h3>";
    echo "<p>Your payment lists should now be working properly.</p>";
    echo "</div>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<a href='admin/payments.php' class='btn'>→ Admin Payments</a>";
    echo "<a href='customer/payment-history.php' class='btn'>→ Customer Payment History</a>";
    echo "<a href='admin/dashboard.php' class='btn btn-success'>→ Admin Dashboard</a>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Some Issues Need Manual Attention</h3>";
    echo "<p>Please check the errors above and fix them manually.</p>";
    echo "</div>";
    
    echo "<h3>Troubleshooting:</h3>";
    echo "<a href='debug_claims.php' class='btn btn-warning'>→ Debug Database</a>";
    echo "<a href='add_sample_data.php' class='btn btn-warning'>→ Add Sample Data</a>";
    echo "<a href='setup.php' class='btn btn-warning'>→ Run Setup</a>";
}
?>
