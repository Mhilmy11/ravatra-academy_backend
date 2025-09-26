<?php
header("Content-Type: text/plain");
require_once "db.php"; // sesuaikan path koneksi DB

// Ambil dari query string (GET)
$email = isset($_GET['email']) ? $_GET['email'] : null;
$password = isset($_GET['password']) ? $_GET['password'] : null;

if (!$email || !$password) {
    die("❌ Gunakan format: test_login.php?email=EMAIL&password=PASSWORD");
}

$query = "SELECT * FROM users WHERE email = :email LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    die("❌ Email tidak ditemukan di database");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (password_verify($password, $user['password'])) {
    echo "✅ Password cocok! Hash di DB sesuai.";
} else {
    echo "❌ Password salah! Hash di DB tidak cocok.";
}
