<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

require_once(__DIR__ . '/../config/db.php');      // koneksi $conn
require_once(__DIR__ . '/../config/config.php');  // $secret_key
require_once(__DIR__ . '/auth.php');              // authAdmin()

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// validasi token & role admin
$user = authAdmin();

// contoh response data dashboard
echo json_encode([
    "message" => "Selamat datang di Dashboard Admin",
    "user" => [
        "id" => $user->id ?? null,
        "email" => $user->email ?? null,
        "role" => $user->role ?? null,
        "name" => $user->name ?? null
    ],
    // Anda bisa menambahkan statistik / data lain di sini
]);
