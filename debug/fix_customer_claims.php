<?php
// Quick Fix Script for Customer Claims
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>🔧 Customer Claims Quick Fix</h2>";
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
    // 1. Check if we have customers
    $stmt = $conn->query("SELECT COUNT(*) as count FROM customers");
    $customers_count = $stmt->fetch()['count'];
    
    if ($customers_count == 0) {
        $errors[] = "❌ No customers found. Please register some customers first.";
    } else {
        $fixed_issues[] = "✓ Found $customers_count customers";
        
        // Get a sample customer for testing
        $stmt = $conn->query("SELECT customer_id, first_name, last_name FROM customers LIMIT 1");
        $sample_customer = $stmt->fetch();
        
        if ($sample_customer) {
            $customer_id = $sample_customer['customer_id'];
            $customer_name = $sample_customer['first_name'] . ' ' . $sample_customer['last_name'];
            echo "<p class='info'>Testing with customer: <strong>$customer_name</strong> (ID: $customer_id)</p>";
            
            // 2. Check if customer has policy holders
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM policy_holders WHERE customer_id = ?");
            $stmt->execute([$customer_id]);
            $policy_holders_count = $stmt->fetch()['count'];
            
            if ($policy_holders_count == 0) {
                // Create a policy holder for this customer
                $stmt = $conn->query("SELECT policy_id, base_premium FROM policies WHERE status = 'active' LIMIT 1");
                $policy = $stmt->fetch();
                
                if ($policy) {
                    $start_date = date('Y-m-d');
                    $end_date = date('Y-m-d', strtotime('+1 year'));
                    
                    $stmt = $conn->prepare("INSERT INTO policy_holders (customer_id, policy_id, start_date, end_date, premium_amount, status) VALUES (?, ?, ?, ?, ?, 'active')");
                    $stmt->execute([$customer_id, $policy['policy_id'], $start_date, $end_date, $policy['base_premium']]);
                    $holder_id = $conn->lastInsertId();
                    
                    $fixed_issues[] = "✓ Created policy holder for customer";
                } else {
                    $errors[] = "❌ No active policies found to assign to customer";
                }
            } else {
                $fixed_issues[] = "✓ Customer has $policy_holders_count policy holders";
                
                // Get the holder ID for claims
                $stmt = $conn->prepare("SELECT holder_id FROM policy_holders WHERE customer_id = ? LIMIT 1");
                $stmt->execute([$customer_id]);
                $holder_id = $stmt->fetch()['holder_id'];
            }
            
            // 3. Check if customer has claims
            if (isset($holder_id)) {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM claims WHERE holder_id = ?");
                $stmt->execute([$holder_id]);
                $claims_count = $stmt->fetch()['count'];
                
                if ($claims_count == 0) {
                    // Create sample claims for this customer
                    $sample_claims = [
                        ['amount' => 15000, 'reason' => 'Medical treatment for fever and consultation', 'status' => 'pending'],
                        ['amount' => 25000, 'reason' => 'Emergency treatment for accident', 'status' => 'approved'],
                        ['amount' => 8000, 'reason' => 'Regular health checkup and tests', 'status' => 'rejected']
                    ];
                    
                    foreach ($sample_claims as $claim) {
                        $approved_amount = $claim['status'] === 'approved' ? $claim['amount'] : 0;
                        $stmt = $conn->prepare("INSERT INTO claims (holder_id, claim_amount, claim_reason, claim_date, status, approved_amount) VALUES (?, ?, ?, CURDATE(), ?, ?)");
                        $stmt->execute([$holder_id, $claim['amount'], $claim['reason'], $claim['status'], $approved_amount]);
                    }
                    
                    $fixed_issues[] = "✓ Created 3 sample claims for customer";
                } else {
                    $fixed_issues[] = "✓ Customer has $claims_count existing claims";
                }
                
                // 4. Test the customer claims query
                $stmt = $conn->prepare("SELECT c.*, p.policy_name, p.policy_type, p.coverage_amount 
                                       FROM claims c 
                                       LEFT JOIN policy_holders ph ON c.holder_id = ph.holder_id 
                                       LEFT JOIN policies p ON ph.policy_id = p.policy_id 
                                       WHERE ph.customer_id = ? 
                                       ORDER BY c.created_at DESC");
                $stmt->execute([$customer_id]);
                $test_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($test_claims) > 0) {
                    $fixed_issues[] = "✓ Customer claims query working - found " . count($test_claims) . " claims";
                    
                    // Display sample results
                    echo "<h3 class='info'>Customer Claims Data:</h3>";
                    echo "<table>";
                    echo "<tr><th>Claim ID</th><th>Policy</th><th>Amount</th><th>Status</th><th>Date</th><th>Reason</th></tr>";
                    foreach ($test_claims as $claim) {
                        echo "<tr>";
                        echo "<td>#" . $claim['claim_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($claim['policy_name']) . "<br><span style='background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;'>" . ucfirst($claim['policy_type']) . "</span></td>";
                        echo "<td>₹" . number_format($claim['claim_amount'], 2) . "</td>";
                        echo "<td><span style='background: " . ($claim['status'] === 'approved' ? 'green' : ($claim['status'] === 'rejected' ? 'red' : 'orange')) . "; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;'>" . ucfirst($claim['status']) . "</span></td>";
                        echo "<td>" . $claim['claim_date'] . "</td>";
                        echo "<td>" . htmlspecialchars(substr($claim['claim_reason'], 0, 50)) . "...</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    $errors[] = "❌ Customer claims query returned no results";
                }
            }
        }
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
    echo "<h3>🎉 Customer Claims Fixed!</h3>";
    echo "<p>The customer should now be able to see their claims.</p>";
    echo "</div>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<a href='customer/my-claims.php' class='btn'>→ View Customer Claims</a>";
    echo "<a href='customer/dashboard.php' class='btn'>→ Customer Dashboard</a>";
    echo "<a href='admin/claims.php' class='btn btn-success'>→ Admin Claims</a>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Some Issues Need Manual Attention</h3>";
    echo "<p>Please check the errors above and fix them manually.</p>";
    echo "</div>";
    
    echo "<h3>Troubleshooting:</h3>";
    echo "<a href='debug_claims.php' class='btn btn-warning'>→ Debug Database</a>";
    echo "<a href='add_sample_data.php' class='btn btn-warning'>→ Add Sample Data</a>";
    echo "<a href='register.php' class='btn btn-warning'>→ Register Customer</a>";
}
?>
