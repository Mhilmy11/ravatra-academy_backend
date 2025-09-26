<?php
require_once(__DIR__ . '/auth.php');
require_once(__DIR__ . '/../config/db.php');

try {
    // ✅ Contoh query: ambil semua user
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, role FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Data dashboard berhasil diambil",
        "user_login" => $authUser, // data admin yg login
        "data" => $users
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}
