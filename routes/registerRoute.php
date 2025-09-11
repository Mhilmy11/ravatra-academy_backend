<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once(__DIR__ . '/../config/db.php');

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['first_name']) ||
    empty($data['last_name']) ||
    empty($data['company']) ||
    empty($data['email']) ||
    empty($data['phone']) ||
    empty($data['product_id'])
) {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap."]);
    exit;
}

$first_name = $data['first_name'];
$last_name = $data['last_name'];
$company = $data['company'];
$email = $data['email'];
$phone = $data['phone'];
$product_id = $data['product_id'];

function generateUserId()
{
    return "USR-" . str_pad(rand(0, 999999), 6, "0", STR_PAD_LEFT);
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $phone]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        $user_id = $existingUser['id'];
    } else {
        $user_id = generateUserId();

        $stmt = $pdo->prepare("INSERT INTO users (id, first_name, last_name, company, email, phone, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $first_name, $last_name, $company, $email, $phone]);
    }

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Produk tidak ditemukan."]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Registrasi berhasil.",
        "user_id" => $user_id,
        "product" => $product
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error.",
        "error" => $e->getMessage()
    ]);
}