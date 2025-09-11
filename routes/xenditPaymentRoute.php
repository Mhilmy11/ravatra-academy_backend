<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once(__DIR__ . '/../config/db.php');

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['transaction_id'])) {
    echo json_encode(["success" => false, "message" => "Transaction ID tidak ditemukan"]);
    exit;
}

$transaction_id = $data['transaction_id'];

try {
    $stmt = $pdo->prepare("UPDATE transactions SET status = 'PAYMENT SUCCESS', update_date = NOW() WHERE id = ?");
    $stmt->execute([$transaction_id]);

    $stmt = $pdo->prepare("SELECT t.*, u.email, u.phone FROM transactions t
                           JOIN users u ON t.user_id = u.id
                           WHERE t.id = ?");
    $stmt->execute([$transaction_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "message" => "Transaksi berhasil dibayar.",
        "data" => $data
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
}
