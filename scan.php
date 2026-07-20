<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include "config.php";
date_default_timezone_set("Asia/Jakarta");

if (isset($_GET['nisn'])) {
  $nisn = $_GET['nisn'];
  $tanggal = date("Y-m-d");
  $jam = date("H:i:s");

  $cekLibur = mysqli_query($conn, "SELECT * FROM hari_libur WHERE tanggal='$tanggal'");
  if (mysqli_num_rows($cekLibur) > 0) {
    echo "⛔ Hari ini libur!";
    exit;
  }

  $siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE nisn='$nisn'");
  if (mysqli_num_rows($siswa) == 0) {
    echo "❌ Siswa tidak ditemukan.";
    exit;
  }
  $s = mysqli_fetch_assoc($siswa);

  $cekAbsen = mysqli_query($conn, "SELECT * FROM absensi WHERE siswa_id={$s['id']} AND tanggal='$tanggal'");
  
  if (mysqli_num_rows($cekAbsen) == 0) {
    // Belum ada absen → catat jam masuk
    mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, jam, status) 
                         VALUES ({$s['id']}, '$tanggal', '$jam', 'H')");
    echo "✅ Absen berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam hadir: $jam";
  } else {
    // Sudah ada absen, cek apakah jam pulang sudah terisi
    $row = mysqli_fetch_assoc($cekAbsen);

    if (is_null($row['jam_pulang']) && $jam >= "09:00:00") {
      // Update jam pulang
      mysqli_query($conn, "UPDATE absensi SET jam_pulang='$jam' 
                           WHERE id={$row['id']}");
      echo "✅ Pulang berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam pulang: $jam";
    } else {
      // Sudah absen masuk & pulang
      echo "ℹ️ {$s['nama']} sudah absen hari ini.<br>🕒 Jam hadir: {$row['jam']}";
      if (!is_null($row['jam_pulang'])) {
        echo "<br>🕒 Jam pulang: {$row['jam_pulang']}";
      }
    }
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Scan QR Siswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://unpkg.com/html5-qrcode"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
  <h2>Scan QR Code Siswa</h2>
  <a href="dashboard.php" class="btn btn-secondary mb-3">← Kembali</a>

  <div id="reader" style="width: 100%"></div>
  <div id="result" class="mt-3" style="max-height: 300px; overflow-y: auto;"></div>

  <!-- Suara beep -->
  <audio id="beepSound" src="beep.mp3" preload="auto"></audio>

  <script>
    function onScanSuccess(qrMessage) {
      fetch("scan.php?nisn=" + qrMessage)
        .then(res => res.text())
        .then(data => {
          let result = document.getElementById("result");
          let alertDiv = document.createElement("div");
          alertDiv.className = "alert alert-info mb-2";
          alertDiv.innerHTML = data;
          result.appendChild(alertDiv);

          // Mainkan suara beep
          document.getElementById("beepSound").play();

          // Scroll otomatis ke bawah
          result.scrollTop = result.scrollHeight;
        });
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
      "reader",
      { fps: 10, qrbox: 250 },
      false
    );
    html5QrcodeScanner.render(onScanSuccess);
  </script>
</body>
</html>
