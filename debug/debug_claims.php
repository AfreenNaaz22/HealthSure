<?php
require_once 'config/database.php';

echo "<h2>Claims Debug & Fix Tool</h2>";
echo "<style>
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
</style>";

// Function to create claims table if it doesn't exist
function createClaimsTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS claims (
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (holder_id) REFERENCES policy_holders(holder_id),
        FOREIGN KEY (processed_by) REFERENCES users(user_id)
    )";
    
    try {
        $conn->exec($sql);
        return true;
    } catch (PDOException $e) {
        echo "<p class='error'>Error creating claims table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Check if claims table exists
try {
    $stmt = $conn->query("DESCRIBE claims");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3 class='success'>✓ Claims Table Structure:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Claims table does not exist. Creating it now...</p>";
    if (createClaimsTable($conn)) {
        echo "<p class='success'>✓ Claims table created successfully!</p>";
    }
}

// Check claims count
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM claims");
    $count = $stmt->fetch()['count'];
    echo "<h3>Total Claims in Database: $count</h3>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error counting claims: " . $e->getMessage() . "</p>";
}

// Check if there are any claims
try {
    $stmt = $conn->query("SELECT * FROM claims LIMIT 5");
    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Sample Claims Data:</h3>";
    if (empty($claims)) {
        echo "<p style='color: orange;'>No claims found in database</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr>";
        foreach (array_keys($claims[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        foreach ($claims as $claim) {
            echo "<tr>";
            foreach ($claim as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error fetching claims: " . $e->getMessage() . "</p>";
}

// Test the exact query from claims.php
try {
    $stmt = $conn->prepare("SELECT c.*, 
                           cu.first_name, cu.last_name, cu.phone,
                           u.email,
                           p.policy_name, p.policy_type,
                           ph.premium_amount, ph.coverage_amount,
                           a.first_name as agent_first_name, a.last_name as agent_last_name,
                           pu.email as processed_by_email
                           FROM claims c
                           JOIN policy_holders ph ON c.holder_id = ph.holder_id
                           JOIN customers cu ON ph.customer_id = cu.customer_id
                           JOIN users u ON cu.user_id = u.user_id
                           JOIN policies p ON ph.policy_id = p.policy_id
                           LEFT JOIN agents a ON cu.agent_id = a.agent_id
                           LEFT JOIN users pu ON c.processed_by = pu.user_id
                           ORDER BY c.created_at DESC");
    $stmt->execute();
    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Claims with JOIN query result: " . count($claims) . " records</h3>";
    if (!empty($claims)) {
        echo "<table border='1'>";
        echo "<tr><th>Claim ID</th><th>Customer</th><th>Policy</th><th>Status</th><th>Amount</th></tr>";
        foreach ($claims as $claim) {
            echo "<tr>";
            echo "<td>" . $claim['claim_id'] . "</td>";
            echo "<td>" . $claim['first_name'] . " " . $claim['last_name'] . "</td>";
            echo "<td>" . $claim['policy_name'] . "</td>";
            echo "<td>" . $claim['status'] . "</td>";
            echo "<td>" . $claim['claim_amount'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error with JOIN query: " . $e->getMessage() . "</p>";
}

// Check related tables
echo "<h3 class='info'>Related Tables Check:</h3>";
$tables = ['policy_holders', 'customers', 'users', 'policies'];
$table_counts = [];
foreach ($tables as $table) {
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        $table_counts[$table] = $count;
        echo "<p>$table: <strong>$count</strong> records</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>Error with $table: " . $e->getMessage() . "</p>";
        $table_counts[$table] = 0;
    }
}

// Auto-fix: Create sample data if needed
if ($table_counts['claims'] == 0 && $table_counts['policy_holders'] > 0) {
    echo "<h3 class='warning'>⚠️ No claims found. Creating sample claims...</h3>";
    
    try {
        // Get a policy holder
        $stmt = $conn->query("SELECT holder_id FROM policy_holders LIMIT 1");
        $holder = $stmt->fetch();
        
        if ($holder) {
            $holder_id = $holder['holder_id'];
            
            // Create sample claims
            $sample_claims = [
                ['amount' => 25000, 'reason' => 'Emergency medical treatment for accident', 'status' => 'pending'],
                ['amount' => 15000, 'reason' => 'Dental surgery and treatment', 'status' => 'approved'],
                ['amount' => 8000, 'reason' => 'Regular health checkup and diagnostic tests', 'status' => 'rejected']
            ];
            
            foreach ($sample_claims as $claim) {
                $approved_amount = $claim['status'] === 'approved' ? $claim['amount'] : 0;
                $stmt = $conn->prepare("INSERT INTO claims (holder_id, claim_amount, claim_reason, claim_date, status, approved_amount, created_at) VALUES (?, ?, ?, CURDATE(), ?, ?, NOW())");
                $stmt->execute([$holder_id, $claim['amount'], $claim['reason'], $claim['status'], $approved_amount]);
            }
            
            echo "<p class='success'>✓ Sample claims created successfully!</p>";
        } else {
            echo "<p class='warning'>⚠️ No policy holders found. Please create customers and policies first.</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Error creating sample claims: " . $e->getMessage() . "</p>";
    }
}

// Test the fixed query
echo "<h3 class='info'>Testing Fixed Claims Query:</h3>";
try {
    // Simplified query first
    $stmt = $conn->query("SELECT c.*, ph.customer_id FROM claims c LEFT JOIN policy_holders ph ON c.holder_id = ph.holder_id");
    $simple_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p class='success'>✓ Simple query returned " . count($simple_claims) . " claims</p>";
    
    // Full query with all JOINs
    $stmt = $conn->prepare("SELECT c.claim_id, c.claim_amount, c.claim_reason, c.status, c.claim_date,
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
    $full_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='success'>✓ Full JOIN query returned " . count($full_claims) . " claims</p>";
    
    if (!empty($full_claims)) {
        echo "<table>";
        echo "<tr><th>Claim ID</th><th>Customer</th><th>Policy</th><th>Amount</th><th>Status</th><th>Date</th></tr>";
        foreach ($full_claims as $claim) {
            echo "<tr>";
            echo "<td>#" . $claim['claim_id'] . "</td>";
            echo "<td>" . htmlspecialchars($claim['first_name'] . ' ' . $claim['last_name']) . "<br><small>" . htmlspecialchars($claim['email']) . "</small></td>";
            echo "<td>" . htmlspecialchars($claim['policy_name']) . "<br><span style='background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;'>" . ucfirst($claim['policy_type']) . "</span></td>";
            echo "<td>₹" . number_format($claim['claim_amount'], 2) . "</td>";
            echo "<td><span style='background: " . ($claim['status'] === 'approved' ? 'green' : ($claim['status'] === 'rejected' ? 'red' : 'orange')) . "; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;'>" . ucfirst($claim['status']) . "</span></td>";
            echo "<td>" . $claim['claim_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>Error with claims query: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3 class='info'>Quick Actions:</h3>";
echo "<p><a href='admin/claims.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>→ Go to Claims Page</a></p>";
echo "<p><a href='add_sample_data.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>→ Add More Sample Data</a></p>";
echo "<p><a href='admin/dashboard.php' style='background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>→ Admin Dashboard</a></p>";
?>
