<?php
// Quick Fix Script for Claims List Issue
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>🔧 Claims List Quick Fix</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; }
    .btn-success { background: #28a745; }
    .btn-warning { background: #ffc107; color: black; }
</style>";

$fixed_issues = [];
$errors = [];

try {
    // 1. Check and create claims table if needed
    try {
        $conn->query("SELECT 1 FROM claims LIMIT 1");
        $fixed_issues[] = "✓ Claims table exists";
    } catch (PDOException $e) {
        // Create claims table
        $sql = "CREATE TABLE claims (
            claim_id INT AUTO_INCREMENT PRIMARY KEY,
            holder_id INT NOT NULL,
            claim_amount DECIMAL(12,2) NOT NULL,
            approved_amount DECIMAL(12,2) DEFAULT 0,
            claim_reason TEXT NOT NULL,
            claim_date DATE NOT NULL,
            documents TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            admin_notes TEXT,
            processed_by INT,
            processed_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        $fixed_issues[] = "✓ Claims table created";
    }
    
    // 2. Check if we have policy holders
    $stmt = $conn->query("SELECT COUNT(*) as count FROM policy_holders");
    $policy_holders_count = $stmt->fetch()['count'];
    
    if ($policy_holders_count == 0) {
        $errors[] = "❌ No policy holders found. You need customers with policies first.";
    } else {
        $fixed_issues[] = "✓ Found $policy_holders_count policy holders";
    }
    
    // 3. Check claims count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM claims");
    $claims_count = $stmt->fetch()['count'];
    
    if ($claims_count == 0 && $policy_holders_count > 0) {
        // Create sample claims
        $stmt = $conn->query("SELECT holder_id FROM policy_holders LIMIT 1");
        $holder = $stmt->fetch();
        
        if ($holder) {
            $holder_id = $holder['holder_id'];
            
            $sample_claims = [
                ['amount' => 25000, 'reason' => 'Emergency medical treatment for accident', 'status' => 'pending'],
                ['amount' => 15000, 'reason' => 'Dental surgery and treatment', 'status' => 'approved'],
                ['amount' => 8000, 'reason' => 'Regular health checkup and diagnostic tests', 'status' => 'rejected']
            ];
            
            foreach ($sample_claims as $claim) {
                $approved_amount = $claim['status'] === 'approved' ? $claim['amount'] : 0;
                $stmt = $conn->prepare("INSERT INTO claims (holder_id, claim_amount, claim_reason, claim_date, status, approved_amount) VALUES (?, ?, ?, CURDATE(), ?, ?)");
                $stmt->execute([$holder_id, $claim['amount'], $claim['reason'], $claim['status'], $approved_amount]);
            }
            
            $fixed_issues[] = "✓ Created 3 sample claims";
        }
    } else {
        $fixed_issues[] = "✓ Found $claims_count existing claims";
    }
    
    // 4. Test the claims query
    $stmt = $conn->prepare("SELECT c.*, 
                           COALESCE(cu.first_name, 'Unknown') as first_name, 
                           COALESCE(cu.last_name, 'Customer') as last_name,
                           COALESCE(u.email, 'No email') as email,
                           COALESCE(p.policy_name, 'Unknown Policy') as policy_name,
                           COALESCE(p.policy_type, 'unknown') as policy_type,
                           COALESCE(p.coverage_amount, 0) as coverage_amount
                           FROM claims c
                           LEFT JOIN policy_holders ph ON c.holder_id = ph.holder_id
                           LEFT JOIN customers cu ON ph.customer_id = cu.customer_id
                           LEFT JOIN users u ON cu.user_id = u.user_id
                           LEFT JOIN policies p ON ph.policy_id = p.policy_id
                           ORDER BY c.created_at DESC");
    $stmt->execute();
    $test_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($test_claims) > 0) {
        $fixed_issues[] = "✓ Claims query working - found " . count($test_claims) . " claims";
    } else {
        $errors[] = "❌ Claims query returned no results";
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
    echo "<h3>🎉 All Issues Fixed!</h3>";
    echo "<p>Your claims list should now be working properly.</p>";
    echo "</div>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<a href='admin/claims.php' class='btn'>→ View Claims List</a>";
    echo "<a href='admin/dashboard.php' class='btn btn-success'>→ Admin Dashboard</a>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Some Issues Need Manual Attention</h3>";
    echo "<p>Please check the errors above and fix them manually.</p>";
    echo "</div>";
    
    echo "<h3>Troubleshooting:</h3>";
    echo "<a href='debug_claims.php' class='btn btn-warning'>→ Debug Claims</a>";
    echo "<a href='add_sample_data.php' class='btn btn-warning'>→ Add Sample Data</a>";
    echo "<a href='setup.php' class='btn btn-warning'>→ Run Setup</a>";
}
?>
