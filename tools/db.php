<?php
if (!function_exists('getDatabaseConnection')) {
    function getDatabaseConnection() {
        // Load database configuration from environment variables or a config file
        $servername = getenv('DB_HOST') ?: 'db'; // Default to 'db' if not set
        $port = getenv('DB_PORT') ?: '5432'; // Default to '5432' if not set
        $username = getenv('DB_USER') ?: 'root'; // Default to 'root' if not set
        $password = getenv('DB_PASSWORD') ?: 'surbhi@postgres'; // Default to 'surbhi@postgres' if not set
        $dbname = getenv('DB_NAME') ?: 'money_transfer'; // Default to 'money_transfer' if not set

        try {
            // Create a PDO instance
            $pdo = new PDO(
                "pgsql:host=$servername;port=$port;dbname=$dbname",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return associative arrays by default
                    PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            // Log the error and display a user-friendly message
            error_log("Database connection failed: " . $e->getMessage());
            die("Unable to connect to the database. Please try again later.");
        }
    }
}
?>