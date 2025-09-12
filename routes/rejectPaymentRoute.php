<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once(__DIR__ . '/../config/db.php');

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? null;
$userId = $input['user_id'] ?? null;

if (!$id || !$userId) {
    echo json_encode(["success" => false, "message" => "Parameter tidak lengkap"]);
    exit;
}

// Ambil transaksi
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id=? AND user_id=?");
$stmt->execute([$id, $userId]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    echo json_encode(["success" => false, "message" => "Transaksi tidak ditemukan"]);
    exit;
}
if ($transaction['status'] !== 'PENDING') {
    echo json_encode(["success" => false, "message" => "Transaksi sudah diproses"]);
    exit;
}

// Update status jadi REJECTED
$stmt = $pdo->prepare("UPDATE transactions SET status='REJECTED', update_date=NOW() WHERE id=?");
$stmt->execute([$id]);

// Ambil user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$transaction['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kirim notifikasi ke user
$message = "⚠️ Pembayaran Anda ditolak.\n" .
    "Transaksi ID: $id\n" .
    "Produk: {$transaction['product_name']}\n" .
    "Silakan hubungi admin untuk informasi lebih lanjut.";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.fonnte.com/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'target' => $user['phone'],
        'message' => $message
    ],
    CURLOPT_HTTPHEADER => [
        "Authorization: ZG7VuhnuQ8RLjWtiCGae"
    ],
]);
$response = curl_exec($curl);
curl_close($curl);

echo json_encode(["success" => true, "message" => "Transaksi berhasil ditolak"]);
