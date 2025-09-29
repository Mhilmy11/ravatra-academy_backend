<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once(__DIR__ . '/../config/db.php');

try {
    // Ambil semua transaksi (orders)
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            t.product_id,
            p.product_name,
            p.product_type,
            t.product_price,
            t.status,
            t.create_date,
            t.expired_at
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN products p ON t.product_id = p.id
        ORDER BY t.create_date DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtUsers = $pdo->prepare("SELECT id, first_name, last_name, email, phone FROM users");
    $stmtUsers->execute();
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    $stmtProducts = $pdo->prepare("SELECT id, product_name, product_type, product_price, pendaftar FROM products");
    $stmtProducts->execute();
    $products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "orders" => $orders,
        "users" => $users,
        "products" => $products
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
