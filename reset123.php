<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

$success = false;
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_password = '0192023a7bbd73250516f069df18b500';

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=1");
    $stmt->bind_param("s", $new_password);

    if ($stmt->execute()) {

        if ($stmt->affected_rows >= 0) {
            $success = true;
            $message = "✅ Password akun berhasil dikembalikan ke default.";
        } else {
            $message = "⚠️ ID 1 tidak ditemukan.";
        }

    } else {
        $message = "❌ Gagal reset password: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Akun Admin</title>
<style>
body{
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg,#4e73df,#1cc88a);
    margin:0;
    padding:0;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.card{
    background:#fff;
    width:95%;
    max-width:420px;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
    text-align:center;
}
h2{ margin-bottom:15px; }
.info{
    background:#f8f9fc;
    padding:15px;
    border-radius:8px;
    margin-bottom:20px;
    font-size:14px;
}
button{
    background:#e74a3b;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:8px;
    font-size:16px;
    cursor:pointer;
    width:100%;
}
button:hover{ opacity:0.9; }
.success{
    background:#d4edda;
    color:#155724;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}
.error{
    background:#f8d7da;
    color:#721c24;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}
.link-btn{
    display:block;
    margin-top:15px;
    background:#4e73df;
    color:white;
    padding:12px;
    border-radius:8px;
    text-decoration:none;
}
.link-btn:hover{ opacity:0.9; }
</style>
</head>
<body>

<div class="card">
    <h2>Reset Akun Admin</h2>

    <?php if ($message): ?>
        <div class="<?= $success ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <div class="info">
            Halaman ini akan mengembalikan akun admin ke pengaturan default:<br><br>
            <strong>Username:</strong> admin<br>
            <strong>Password:</strong> admin123
        </div>

        <form method="POST" onsubmit="return confirm('Yakin ingin mengembalikan akun ID 1 ke default?');">
            <button type="submit">Reset Sekarang</button>
        </form>
    <?php else: ?>
        <a href="https://youtu.be/pxYNBjroloQ" target="_blank" class="link-btn">
            ▶ Pelajari Selengkapnya
        </a>
    <?php endif; ?>

</div>

</body>
</html>