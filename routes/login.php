<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use \Firebase\JWT\JWT;

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["message" => "Email dan password harus diisi"]);
    exit;
}

$email = trim($data->email);
$password = trim($data->password);

try {
    // ✅ Query cek user by email
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        http_response_code(401);
        echo json_encode(["message" => "Email atau password salah"]);
        exit;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Verifikasi password
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["message" => "Email atau password salah"]);
        exit;
    }

    // ✅ Pastikan hanya admin
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(["message" => "Login hanya untuk admin"]);
        exit;
    }

    // ✅ Buat JWT token
    $payload = [
        "iat" => time(),
        "exp" => time() + (60 * 60), // 1 jam
        "data" => [
            "id" => $user['id'],
            "email" => $user['email'],
            "role" => $user['role'],
            "name" => $user['first_name'] . " " . $user['last_name']
        ]
    ];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');

    // ✅ Response sukses
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Login berhasil",
        "token" => $jwt,
        "user" => [
            "id" => $user['id'],
            "email" => $user['email'],
            "role" => $user['role'],
            "name" => $user['first_name'] . " " . $user['last_name'],
            "company" => $user['company'],
            "phone" => $user['phone']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}