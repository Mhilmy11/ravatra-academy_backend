<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once(__DIR__ . '/../config/db.php');

// GET INPUT FROM UI
$input = json_decode(file_get_contents("php://input"), true);
$userId = $input['user_id'] ?? null;
$productId = $input['product_id'] ?? null;

if (!$userId || !$productId) {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
    exit;
}

try {
    // GET DATA PRODUCT
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Produk tidak ditemukan"]);
        exit;
    }

    // GET USER DATA
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "User tidak ditemukan"]);
        exit;
    }

    $id = "ORD" . time() . rand(100, 999);

    $uniqueCode = rand(001, 333);
    $rawPrice = $product['product_price'] + $uniqueCode;
    $uniquePrice = "Rp " . number_format($rawPrice, 0, ',', '.');

    $expiredAt = date("Y-m-d H:i:s", strtotime("+1 day"));

    $stmt = $pdo->prepare("INSERT INTO transactions 
        (id, user_id, product_id, product_name, product_type, product_price, status, expired_at, create_date) 
        VALUES (?,?,?,?,?,?,?,?,NOW())");

    $stmt->execute([
        $id,
        $userId,
        $productId,
        $product['product_name'],
        $product['product_type'],
        $uniquePrice,
        'PENDING',
        $expiredAt
    ]);

    // SEND TO ADMIN
    $adminPhone = "6282298605562";
    $approveLink = "http://ravatraacademy-dev.netlify.app/admin-approval/$id/$userId";

    $message = "🆕 Order Baru Masuk\n\n" .
        "ORDER_ID: $id\n" .
        "USER: {$user['first_name']} {$user['last_name']}\n" .
        "COMPANY: {$user['company']}\n" .
        "EMAIL: {$user['email']}\n" .
        "PHONE: {$user['phone']}\n" .
        "PRODUCT: {$product['product_name']}\n" .
        "PRODUCT: {$product['product_type']}\n" .
        "NOMINAL: $uniquePrice . \n" .
        "BATAS BAYAR: $expiredAt\n\n" .
        "✅ Approve Pembayaran:\n$approveLink";

    $curl = curl_init();
    curl_setopt_array($curl, [
        // CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'target' => $adminPhone,
            'message' => $message
        ],
        CURLOPT_HTTPHEADER => [
            // "Authorization: ZG7VuhnuQ8RLjWtiCGae"
        ],
    ]);
    $waResponse = curl_exec($curl);
    if (curl_errno($curl)) {
        echo json_encode(["success" => false, "message" => "WA gagal: " . curl_error($curl)]);
        curl_close($curl);
        exit;
    }
    curl_close($curl);

    // SUCCES RESPONSE TO FRONTEND
    echo json_encode([
        "success" => true,
        "message" => "Transaksi berhasil dibuat",
        "transaction" => [
            "id" => $id,
            "user_id" => $userId,
            "product_id" => $productId,
            "product_name" => $product['product_name'],
            "product_type" => $product['product_type'],
            "product_price" => $uniquePrice,
            "status" => "PENDING",
            "expired_at" => $expiredAt
        ]
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}