<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

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

// Query cek user
$query = "SELECT * FROM users WHERE email = :email LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    http_response_code(401);
    echo json_encode(["message" => "Email atau password salah"]);
    exit;
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifikasi password
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(["message" => "Email atau password salah"]);
    exit;
}

// ✅ Pastikan hanya role admin yang boleh login ke dashboard
if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Login hanya untuk admin"]);
    exit;
}

// Buat JWT token
$payload = [
    "iat" => time(),
    "exp" => time() + (60 * 60), // Token berlaku 1 jam
    "data" => [
        "id" => $user['id'],
        "email" => $user['email'],
        "role" => $user['role'],
        "name" => $user['first_name'] . " " . $user['last_name']
    ]
];

$jwt = JWT::encode($payload, $secret_key, 'HS256');

// Response sukses
http_response_code(200);
echo json_encode([
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
