<?php
require_once(__DIR__ . '/../config/db.php');

$email = $_GET['email'] ?? '';
$password = $_GET['password'] ?? '';

if (!$email || !$password) {
    die("Email dan password harus diisi di query string");
}

// Query cek user
$query = "SELECT * FROM users WHERE email = :email LIMIT 1";
$stmt = $pdo->prepare($query);   // ✅ pakai $pdo
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    die("Email tidak ditemukan");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifikasi password
if (password_verify($password, $user['password'])) {
    echo "✅ Password cocok!";
} else {
    echo "❌ Password salah!";
}
