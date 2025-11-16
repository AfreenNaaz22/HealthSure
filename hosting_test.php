<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthSure - Hosting Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="test-card">
        <h1>🏥 HealthSure Hosting Test</h1>
        <p>This page tests if your hosting environment is working correctly.</p>
    </div>

    <div class="test-card">
        <h3>✅ PHP Test</h3>
        <p class="success">PHP is working! Version: <?php echo phpversion(); ?></p>
        <p class="info">Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
        <p class="info">Host: <?php echo $_SERVER['HTTP_HOST'] ?? 'Unknown'; ?></p>
    </div>

    <div class="test-card">
        <h3>🗄️ Database Test</h3>
        <?php
        // Test database connection without including the main config
        try {
            // Try to include database config safely
            if (file_exists('config/database.php')) {
                // Don't include it directly to avoid errors
                echo '<p class="warning">Database config file exists</p>';
                
                // Test PDO availability
                if (class_exists('PDO')) {
                    echo '<p class="success">PDO extension is available</p>';
                    
                    // List available PDO drivers
                    $drivers = PDO::getAvailableDrivers();
                    if (in_array('mysql', $drivers)) {
                        echo '<p class="success">MySQL PDO driver is available</p>';
                    } else {
                        echo '<p class="error">MySQL PDO driver is NOT available</p>';
                    }
                } else {
                    echo '<p class="error">PDO extension is NOT available</p>';
                }
            } else {
                echo '<p class="error">Database config file not found</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">Database test error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <div class="test-card">
        <h3>📁 File System Test</h3>
        <?php
        $directories = ['auth', 'admin', 'customer', 'agent', 'assets', 'config'];
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                echo '<p class="success">✓ ' . $dir . '/ directory exists</p>';
            } else {
                echo '<p class="error">✗ ' . $dir . '/ directory missing</p>';
            }
        }
        ?>
    </div>

    <div class="test-card">
        <h3>🔧 Next Steps</h3>
        <ol>
            <li><strong>Update Database Config:</strong> Edit <code>config/database.php</code> with your hosting database credentials</li>
            <li><strong>Import Database:</strong> Import the <code>config/init_db.sql</code> file to your hosting database</li>
            <li><strong>Test Login:</strong> Try accessing <a href="auth/login.php">auth/login.php</a></li>
            <li><strong>Access Landing:</strong> Try accessing <a href="landing.php">landing.php</a></li>
        </ol>
    </div>

    <div class="test-card">
        <h3>🏠 Quick Links</h3>
        <p>
            <a href="landing.php" style="margin-right: 15px;">Landing Page</a>
            <a href="auth/login.php" style="margin-right: 15px;">Login</a>
            <a href="auth/register.php">Register</a>
        </p>
    </div>
</body>
</html>
