<?php
$host = 'db'; // This should match the service name in docker-compose
$port = '5432'; // PostgreSQL default port
$dbname = 'money_transfer'; // Your database name
$username = 'root'; // Your PostgreSQL username
$password = 'surbhi@postgres'; // Your PostgreSQL password

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
?>
