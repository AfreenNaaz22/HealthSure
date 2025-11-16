<?php
// HealthSure Hosting Setup Script
// Run this ONCE after uploading files to create database tables

echo "<!DOCTYPE html>
<html>
<head>
    <title>HealthSure Hosting Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .step { margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 HealthSure Hosting Setup</h1>";

// Step 1: Check database connection
echo "<div class='step'>
        <h3>Step 1: Database Connection</h3>";

try {
    require_once 'config/database.php';
    echo "<p class='success'>✅ Database connection successful!</p>";
    
    // Step 2: Create tables if they don't exist
    echo "</div><div class='step'>
            <h3>Step 2: Database Tables</h3>";
    
    // Check if main tables exist
    $tables_to_check = ['users', 'customers', 'agents', 'policies', 'policy_holders', 'claims', 'payments'];
    $existing_tables = [];
    
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $conn->query("SELECT 1 FROM $table LIMIT 1");
            $existing_tables[] = $table;
            echo "<p class='success'>✅ Table '$table' exists</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Table '$table' missing</p>";
        }
    }
    
    if (count($existing_tables) < count($tables_to_check)) {
        echo "<p class='warning'>⚠️ Some tables are missing. You need to run the setup script.</p>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ol>
                <li>Go to <a href='setup/setup.php'>setup/setup.php</a> to create tables</li>
                <li>Then go to <a href='setup/add_sample_data.php'>setup/add_sample_data.php</a> to add test data</li>
                <li>Finally, test your site at <a href='index.php'>index.php</a></li>
              </ol>";
    } else {
        echo "<p class='success'>✅ All tables exist!</p>";
        
        // Step 3: Check for admin user
        echo "</div><div class='step'>
                <h3>Step 3: Admin User</h3>";
        
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $stmt->execute();
            $admin_count = $stmt->fetch()['count'];
            
            if ($admin_count > 0) {
                echo "<p class='success'>✅ Admin user exists</p>";
                echo "<p><strong>Default Login:</strong><br>
                        Email: admin@healthsure.com<br>
                        Password: password</p>";
            } else {
                echo "<p class='error'>❌ No admin user found</p>";
                echo "<p>Run <a href='setup/add_sample_data.php'>setup/add_sample_data.php</a> to create admin user</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Could not check admin user</p>";
        }
        
        echo "</div><div class='step'>
                <h3>🎉 Setup Complete!</h3>
                <p class='success'>Your HealthSure application is ready!</p>
                <p><strong>Test your site:</strong></p>
                <ul>
                    <li><a href='index.php' target='_blank'>Main Site (index.php)</a></li>
                    <li><a href='landing.php' target='_blank'>Landing Page</a></li>
                    <li><a href='auth/login.php' target='_blank'>Login Page</a></li>
                    <li><a href='admin/dashboard.php' target='_blank'>Admin Dashboard</a></li>
                </ul>
              </div>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed!</p>";
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Fix this by:</strong></p>";
    echo "<ol>
            <li>Update config/database.php with your hosting database details</li>
            <li>Make sure your database exists in your hosting control panel</li>
            <li>Check that your database credentials are correct</li>
          </ol>";
    echo "</div>";
}

echo "</div>
</body>
</html>";
?>
