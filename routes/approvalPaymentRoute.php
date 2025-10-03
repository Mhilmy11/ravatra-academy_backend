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

$stmt = $pdo->prepare("UPDATE transactions SET status='PAID', update_date=NOW() WHERE id=?");
$stmt->execute([$id]);

$stmt = $pdo->prepare("UPDATE products SET pendaftar = pendaftar + 1 WHERE id=?");
$stmt->execute([$transaction['product_id']]);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$transaction['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$fullName = trim($user['first_name'] . ' ' . $user['last_name']);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$transaction['product_id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

$packageLink = $product['package_link'] ?? "Link belum tersedia";
$message = "🎉 Yeay Pembayaran anda Berhasil!\n" .
    "Dengan detail berikut:\n" .
    "Nama Pemesan: $fullName\n" .
    "Transaksi ID: $id\n" .
    "Nama Produk: {$product['product_name']}\n" .
    "Produk Type: {$product['product_type']}\n\n" .
    "Product Package Link: $packageLink\n\n" .
    "Terima kasih ka $fullName sudah bergabung di Ravatra Academy, Ditunggu kehadirannya 🙌";

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
        "Authorization: iFg4w1pnYnZF9hWFTJ6v"
    ],
]);
$response = curl_exec($curl);
curl_close($curl);

echo json_encode([
    "success" => true,
    "message" => "Transaksi berhasil di-approve & package terkirim"
]);