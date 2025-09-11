<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once(__DIR__ . '/../config/db.php');

$id = $_GET['id'] ?? null;
$userId = $_GET['user_id'] ?? null;

if (!$id || !$userId) {
    echo json_encode(["success" => false, "message" => "Parameter tidak lengkap"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.first_name, u.last_name, u.email, u.phone, p.package_link
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN products p ON t.product_id = p.id
        WHERE t.id = ? AND t.user_id = ?
    ");
    $stmt->execute([$id, $userId]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        echo json_encode(["success" => false, "message" => "Transaksi tidak ditemukan"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "transaction" => $transaction
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}