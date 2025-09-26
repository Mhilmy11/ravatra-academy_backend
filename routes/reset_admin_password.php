<?php
require_once(__DIR__ . '/../config/db.php');

// email admin yang mau di-reset
$email = "admin@ravatraacademy.id";
// password baru (plain)
$newPassword = "Admin123!";

// hash password baru
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
    $stmt->execute([
        ":password" => $hashedPassword,
        ":email" => $email
    ]);

    if ($stmt->rowCount() > 0) {
        echo "✅ Password admin berhasil di-reset ke: $newPassword";
    } else {
        echo "⚠️ Email tidak ditemukan, password tidak diubah.";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
