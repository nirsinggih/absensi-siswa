<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['siswa_id']) || !isset($_SESSION['username'])) {
    die("Data siswa tidak ditemukan, silakan login ulang.");
}

$siswa_id = intval($_SESSION['siswa_id']);
$username = $_SESSION['username'];

// Ambil data siswa
$q = mysqli_query($conn, "SELECT nama, kelas, no_wa FROM siswa WHERE id = $siswa_id");
$siswa = mysqli_fetch_assoc($q);

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_wa = trim($_POST['no_wa']);
    $password = trim($_POST['password']);
    $password_md5 = !empty($password) ? md5($password) : "";

    // update nomor WA
    $stmt1 = mysqli_prepare($conn, "UPDATE siswa SET no_wa = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt1, "si", $no_wa, $siswa_id);
    $ok1 = mysqli_stmt_execute($stmt1);

    $ok2 = true;
    if (!empty($password_md5)) {
        // update password di tabel users
        $stmt2 = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = ?");
        mysqli_stmt_bind_param($stmt2, "ss", $password_md5, $username);
        $ok2 = mysqli_stmt_execute($stmt2);
    }

    if ($ok1 && $ok2) {
        $msg = "<div class='alert alert-success'>Data berhasil diperbarui!</div>";
        // Refresh data siswa
        $q = mysqli_query($conn, "SELECT nama, kelas, no_wa FROM siswa WHERE id = $siswa_id");
        $siswa = mysqli_fetch_assoc($q);
    } else {
        $msg = "<div class='alert alert-danger'>Gagal memperbarui data.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Ubah Password & No. WA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

  <h3>Ubah Password & Nomor WhatsApp</h3>
  <?= $msg ?>

  <form method="post" class="mt-3">
    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['nama']) ?>" readonly>
    </div>

    <div class="mb-3">
      <label class="form-label">Kelas</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['kelas']) ?>" readonly>
    </div>

    <div class="mb-3">
      <label class="form-label">Nomor WhatsApp Orang Tua/Wali</label>
      <input type="text" name="no_wa" class="form-control" value="<?= htmlspecialchars($siswa['no_wa']) ?>" required>
      <div class="form-text">Format: gunakan 62 untuk Indonesia (contoh: 6281234567890)</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Password Baru</label>
      <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
    </div>

    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
  </form>

</body>
</html>
