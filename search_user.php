<?php

require 'tools/db_money.php';
if (!isset($pdo)) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

header('Content-Type: application/json');

try {
    $query = trim($_GET['term'] ?? '');
    $id = ctype_digit($query) ? intval($query) : 0;

    // Using ILIKE for case-insensitive search in PostgreSQL
    $stmt = $pdo->prepare("SELECT id, username, balance FROM users WHERE username ILIKE ? OR id = ?");
    $searchTerm = "%$query%"; // Correct way to use wildcard in prepared statements
    $stmt->execute([$searchTerm, $id]);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['success' => true, 'users' => $result]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No users found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
