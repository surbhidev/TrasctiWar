<?php
session_start();
require 'tools/db_money.php';

header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$senderId = $_SESSION['id'];
$receiverId = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : null;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if (!$receiverId || !$amount || $amount <= 0) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Prevent self-transfer
if ($senderId === $receiverId) {
    echo json_encode(['error' => 'Cannot transfer to yourself']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check sender balance
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :id FOR UPDATE");
    $stmt->execute(['id' => $senderId]);
    $sender = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sender || $sender['balance'] < $amount) {
        echo json_encode(['error' => 'Insufficient balance']);
        $pdo->rollBack();
        exit;
    }

    // Check receiver existence
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id");
    $stmt->execute(['id' => $receiverId]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['error' => 'Receiver not found']);
        $pdo->rollBack();
        exit;
    }

    // Deduct from sender
    $stmt = $pdo->prepare("UPDATE users SET balance = balance - :amount WHERE id = :id");
    $stmt->execute(['amount' => $amount, 'id' => $senderId]);

    // Credit to receiver
    $stmt = $pdo->prepare("UPDATE users SET balance = balance + :amount WHERE id = :id");
    $stmt->execute(['amount' => $amount, 'id' => $receiverId]);

    // Record transaction
    $stmt = $pdo->prepare("INSERT INTO transactions (sender_id, receiver_id, amount, comment) VALUES (:sender_id, :receiver_id, :amount, :comment)");
    $stmt->execute([
        'sender_id' => $senderId,
        'receiver_id' => $receiverId,
        'amount' => $amount,
        'comment' => $comment
    ]);

    $pdo->commit();
    echo json_encode(['success' => 'Transfer completed']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
