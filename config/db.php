<?php
$host = "id-dci-web1864.main-hosting.eu";
$dbname = "u388738159_ravatra_db";
$username = "u388738159_ravatra_admin";
$password = "123#A123x";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // aktifkan mode error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed", "error" => $e->getMessage()]);
    exit;
}
?>