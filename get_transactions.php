<?php
session_start();
require 'tools/db_money.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE sender_id = :id OR receiver_id = :id ORDER BY created_at DESC");
    $stmt->execute(['id' => $userId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($transactions);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
