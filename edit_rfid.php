<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$q = mysqli_query($conn, "SELECT * FROM siswa WHERE id='$id'");
$siswa = mysqli_fetch_assoc($q);

if (!$siswa) {
    die("Data tidak ditemukan!");
}

// Simpan data RFID
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rfid = mysqli_real_escape_string($conn, $_POST['rfid_uid']);
    mysqli_query($conn, "UPDATE siswa SET rfid_uid='$rfid' WHERE id='$id'");
    header("Location: pengaturan_rfid.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit RFID Siswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

  <h3>Edit RFID untuk <?= htmlspecialchars($siswa['nama']) ?> (<?= htmlspecialchars($siswa['nis']) ?>)</h3>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">RFID UID</label>
      <input type="text" name="rfid_uid" class="form-control" 
             value="<?= htmlspecialchars($siswa['rfid_uid']) ?>" 
             placeholder="Tempelkan kartu di alat untuk otomatis terisi" autofocus required>
    </div>
    <button type="submit" class="btn btn-success">Simpan</button>
    <a href="pengaturan_rfid.php" class="btn btn-secondary">Kembali</a>
  </form>

</body>
</html>
