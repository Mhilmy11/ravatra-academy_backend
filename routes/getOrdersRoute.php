<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once(__DIR__ . '/../config/db.php');

try {
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
            p.product_price,
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

    echo json_encode([
        "success" => true,
        "orders" => $orders
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
