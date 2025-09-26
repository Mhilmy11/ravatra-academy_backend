<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once(__DIR__ . '/../config/db.php');     // koneksi pakai $pdo
require_once(__DIR__ . '/../config/config.php'); // secret_key
require_once(__DIR__ . '/../vendor/autoload.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Ambil token dari header Authorization
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token tidak ditemukan"]);
    exit;
}

$jwt = $matches[1];

try {
    // Decode token
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

    // Cek role admin
    if ($decoded->data->role !== 'admin') {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Akses ditolak, hanya admin yang bisa masuk"]);
        exit;
    }

    // Contoh query ke DB (jumlah user)
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "message" => "Selamat datang di dashboard admin",
        "user" => $decoded->data,
        "stats" => [
            "total_users" => $totalUsers['total_users']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token tidak valid", "error" => $e->getMessage()]);
}
