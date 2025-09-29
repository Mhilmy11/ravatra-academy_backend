<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$route = $_GET['route'] ?? null;

if (!$route) {
    echo json_encode(["success" => false, "message" => "Route tidak ditemukan"]);
    exit;
}

$routes = [
    "login" => "routes/login.php",
    "dashboard" => "routes/dashboard.php",
    "products" => "routes/productsRoute.php",
    "register" => "routes/registerRoute.php",
    "getOrders" => "routes/getOrdersRoute.php",
    "getTransaction" => "routes/getTransactionRoute.php",
    "transaction" => "routes/transactionRoute.php",
    "createTransaction" => "routes/createTransactionRoute.php",
    "approvalPayment" => "routes/approvalPaymentRoute.php",
    "rejectPayment" => "routes/rejectPaymentRoute.php",
    "xenditPayment" => "routes/xenditPaymentRoute.php"
];

if (array_key_exists($route, $routes)) {
    require_once __DIR__ . "/" . $routes[$route];
} else {
    echo json_encode(["success" => false, "message" => "Route tidak valid"]);
    exit;
}
