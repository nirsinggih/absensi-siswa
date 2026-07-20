<?php
include "../config.php";

// ================== TAMBAH DATA ==================
if (isset($_POST['tambah'])) {
    $nama = $conn->real_escape_string($_POST['nama_pelanggaran']);
    $poin = intval($_POST['poin']);
    $sql = "INSERT INTO pelanggaran (nama_pelanggaran, poin) VALUES ('$nama', '$poin')";
    $conn->query($sql);
}

// ================== UPDATE DATA ==================
if (isset($_POST['update'])) {
    $id   = intval($_POST['id']);
    $nama = $conn->real_escape_string($_POST['nama_pelanggaran']);
    $poin = intval($_POST['poin']);
    $sql = "UPDATE pelanggaran SET nama_pelanggaran='$nama', poin='$poin' WHERE id=$id";
    $conn->query($sql);
}

// ================== HAPUS DATA ==================
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM pelanggaran WHERE id=$id");
}

// ================== AMBIL DATA ==================
$result = $conn->query("SELECT * FROM pelanggaran ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- penting untuk HP -->
  <title>Daftar Pelanggaran & Poin</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 10px; }
    h2 { font-size: 20px; margin-bottom: 10px; }
    h3 { font-size: 16px; margin-top: 15px; }
    form { margin: 5px 0; }
    input[type="text"], input[type="number"] {
      padding: 8px;
      margin: 3px 0;
      width: 100%;
      box-sizing: border-box;
    }
    .btn {
      padding: 8px 12px;
      margin-top: 5px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      display: inline-block;
      font-size: 14px;
    }
    .simpan { background: green; color: white; }
    .edit { background: orange; color: white; }
    .hapus { background: red; color: white; }
    .tabel-container { overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; min-width: 400px; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 14px; }
    th { background: #f0f0f0; }
    a { text-decoration: none; }
  </style>
</head>
<body>

<h2>📋 Daftar Pelanggaran & Poin</h2>

<!-- Form Tambah -->
<h3>Tambah Pelanggaran</h3>
<form method="post">
  <input type="text" name="nama_pelanggaran" placeholder="Nama Pelanggaran" required>
  <input type="number" name="poin" placeholder="Poin" required>
  <button type="submit" name="tambah" class="btn simpan">Tambah</button>
</form>
<a href="insert_pelanggaran.php" class="btn btn-primary">Contoh</a>

<!-- Tabel Data -->
<div class="tabel-container">
<table>
  <tr>
    <th>No</th>
    <th>Nama Pelanggaran</th>
    <th>Poin</th>
    <th>Aksi</th>
  </tr>
  <?php
  $no = 1;
  while ($row = $result->fetch_assoc()) {
      echo "<tr>";
      echo "<td>".$no++."</td>";
      echo "<td>";

      // Form edit
      if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) {
          echo '<form method="post">
                  <input type="hidden" name="id" value="'.$row['id'].'">
                  <input type="text" name="nama_pelanggaran" value="'.$row['nama_pelanggaran'].'" required>';
      } else {
          echo htmlspecialchars($row['nama_pelanggaran']);
      }

      echo "</td><td>";

      if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) {
          echo '<input type="number" name="poin" value="'.$row['poin'].'" required>';
      } else {
          echo $row['poin'];
      }

      echo "</td><td>";

      if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) {
          echo '<button type="submit" name="update" class="btn simpan">Simpan</button>
                <a href="pelanggaran.php" class="btn">Batal</a>
                </form>';
      } else {
          echo '<a href="pelanggaran.php?edit='.$row['id'].'" class="btn edit">Edit</a> ';
          echo '<a href="pelanggaran.php?hapus='.$row['id'].'" class="btn hapus" onclick="return confirm(\'Yakin hapus?\')">Hapus</a>';
      }

      echo "</td></tr>";
  }
  ?>
</table>
</div>

</body>
</html>
