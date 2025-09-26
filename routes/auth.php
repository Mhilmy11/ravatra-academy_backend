<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

require_once(__DIR__ . '/../config/config.php');   // $secret_key
require_once(__DIR__ . '/../vendor/autoload.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * getBearerToken
 * Ambil token dari header Authorization (Bearer ...)
 */
function getBearerToken()
{
    $headers = null;

    // try several server methods to fetch Authorization header
    if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            return trim($requestHeaders['Authorization']);
        }
        // sometimes header keys are in different case
        foreach ($requestHeaders as $key => $value) {
            if (strtolower($key) === 'authorization')
                return trim($value);
        }
    }

    // fallback to $_SERVER
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
    }
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }

    return null;
}

/**
 * authAdmin
 * Validasi token dan pastikan role = 'admin'.
 * Mengembalikan decoded payload->data pada sukses.
 * Jika gagal, script langsung exit dengan http response code.
 */
function authAdmin()
{
    global $secret_key;

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $authHeader = getBearerToken();
    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(["message" => "Token tidak ditemukan"]);
        exit;
    }

    // bentuk header bisa "Bearer token" atau langsung token
    $parts = explode(" ", $authHeader);
    $jwt = count($parts) === 2 ? $parts[1] : $parts[0];

    if (empty($jwt)) {
        http_response_code(401);
        echo json_encode(["message" => "Token tidak valid"]);
        exit;
    }

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        // payload disimpan di $decoded->data sesuai login.php
        if (!isset($decoded->data->role) && !isset($decoded->data->role)) {
            http_response_code(403);
            echo json_encode(["message" => "Role tidak tersedia di token"]);
            exit;
        }

        $role = $decoded->data->role ?? null;
        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Akses ditolak: role bukan admin"]);
            exit;
        }

        // kembalikan data user dari token
        return $decoded->data;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Token tidak valid / expired", "error" => $e->getMessage()]);
        exit;
    }
}
