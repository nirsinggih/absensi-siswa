<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kirim WA Massal</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h2>📢 Kirim Pengumuman WA Massal</h2>
  <a href="dashboard.php" class="btn btn-secondary mb-3">← Kembali</a>

  <form method="POST" action="wa_kirim.php">
    <div class="mb-3">
      <label class="form-label">Format Pesan:</label>
      <textarea name="pesan" rows="5" class="form-control">
Assalamualaikum {nama} dari kelas {kelas}, besok ada pengumuman penting.
      </textarea>
      <div class="form-text">Gunakan {nama} dan {kelas} untuk otomatis diganti.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Jeda per pesan (detik):</label>
      <input type="number" name="delay" value="15" class="form-control" min="1">
    </div>

    <button type="submit" class="btn btn-primary">Kirim Sekarang</button>
  </form>
</body>
</html>
