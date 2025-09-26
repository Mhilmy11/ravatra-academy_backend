<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// ✅ Ambil Authorization Header
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token tidak ditemukan"]);
    exit;
}

// ✅ Format token: "Bearer <token>"
list($type, $token) = explode(" ", $authHeader, 2);

if (strcasecmp($type, "Bearer") != 0 || empty($token)) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Format token salah"]);
    exit;
}

try {
    // ✅ Decode JWT
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    $userData = (array) $decoded->data;

    // ✅ Pastikan role admin
    if ($userData['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Akses ditolak, hanya untuk admin"]);
        exit;
    }

    // ✅ Simpan data user agar bisa dipakai di file lain
    $authUser = $userData;

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token tidak valid: " . $e->getMessage()]);
    exit;
}
