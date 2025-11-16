<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'healthsure_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                                $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Log error instead of dying to prevent redirect loops
            error_log("Database connection error: " . $exception->getMessage());
            return null;
        }
        return $this->conn;
    }
    
    public static function connect() {
        $database = new Database();
        return $database->getConnection();
    }
}

// Global database connection with error handling
try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // If connection fails, set $conn to null to prevent errors
    if (!$conn) {
        $conn = null;
        error_log("Failed to establish database connection");
    }
} catch(Exception $e) {
    $conn = null;
    error_log("Database initialization error: " . $e->getMessage());
}
?>