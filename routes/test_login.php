<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: text/plain");

// Pastikan path sesuai
require_once __DIR__ . "/../config/db.php";

$email = isset($_GET['email']) ? $_GET['email'] : null;
$password = isset($_GET['password']) ? $_GET['password'] : null;

if (!$email || !$password) {
    die("❌ Gunakan format: test_login.php?email=EMAIL&password=PASSWORD");
}

try {
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        die("❌ Email tidak ditemukan di database");
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Hash dari DB: " . $user['password'] . "\n";

    if (password_verify($password, $user['password'])) {
        echo "✅ Password cocok!";
    } else {
        echo "❌ Password salah!";
    }
} catch (Exception $e) {
    echo "🔥 Error: " . $e->getMessage();
}
