<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once(__DIR__ . '/../config/db.php');

try {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                echo json_encode(["success" => true, "data" => $product]);
            } else {
                echo json_encode(["success" => false, "message" => "Produk tidak ditemukan"]);
            }
            exit;
        }

        if (isset($_GET['type'])) {
            $type = $_GET['type'];
            $stmt = $pdo->prepare("SELECT * FROM products WHERE product_type = ?");
            $stmt->execute([$type]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $products]);
            exit;
        }

        $stmt = $pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $products]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}