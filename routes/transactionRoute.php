<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once(__DIR__ . '/../config/db.php');

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['user_id']) || empty($data['product_id'])) {
    echo json_encode(["success" => false, "message" => "User ID atau Product ID tidak boleh kosong"]);
    exit;
}

$user_id = $data['user_id'];
$product_id = $data['product_id'];

try {
    // Ambil data produk
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Produk tidak ditemukan."]);
        exit;
    }

    // Insert transaksi
    $stmt = $pdo->prepare("INSERT INTO transactions (
        user_id, product_id, create_date, status, product_type,
        product_name, product_price, update_date
    ) VALUES (?, ?, NOW(), ?, ?, ?, ?, NULL)");

    $stmt->execute([
        $user_id,
        $product_id,
        'PENDING',
        $product['product_type'],
        $product['product_name'],
        $product['product_price']
    ]);

    $transaction_id = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "transaction_id" => $transaction_id,
        "product" => $product
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Server error.", "error" => $e->getMessage()]);
}
