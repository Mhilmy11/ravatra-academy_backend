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

switch ($route) {
    case 'products':
        require_once("routes/productsRoute.php");
        break;

    case 'register':
        require_once("routes/registerRoute.php");
        break;

    case 'getTransaction':
        require_once(__DIR__ . "/routes/getTransactionRoute.php");
        break;

    case 'transaction':
        require_once("/routes/transactionRoute.php");
        break;

    case 'createTransaction':
        require_once("routes/createTransactionRoute.php");
        break;

    case 'approvalPayment':
        require_once(__DIR__ . "/routes/approvalPaymentRoute.php");
        break;

    case 'rejectPayment':
        require_once("routes/rejectPaymentRoute.php");
        break;

    case 'xenditPayment':
        require_once("routes/xenditPaymentRoute.php");
        break;


    default:
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Route tidak ditemukan"
        ]);
        break;
}
