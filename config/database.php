<?php
/**
 * Database Configuration
 */

// Database credentials 
define('DB_HOST', 'localhost');
define('DB_NAME', 'fitlife_cms');  
define('DB_USER', 'root');          
define('DB_PASS', '');              
define('DB_CHARSET', 'utf8mb4');

/**
 * 
 * @return PDO Database connection object
 */
function getDBConnection() {
    static $pdo = null;
    
    // Return existing connection
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error in production, display in development
        error_log("Database Connection Error: " . $e->getMessage());
        die("Database connection failed. Please check your configuration.");
    }
}

/**
 * Testing database connection
 * @return bool True if connection successful
 */
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT 1");
        return $stmt !== false;
    } catch (Exception $e) {
        return false;
    }
}
?>