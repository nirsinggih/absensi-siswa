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

  // Cek hari libur
  $cekLibur = mysqli_query($conn, "SELECT * FROM hari_libur WHERE tanggal='$tanggal'");
  if (mysqli_num_rows($cekLibur) > 0) {
    echo "⛔ Hari ini libur!";
    exit;
  }

  // Cek siswa
  $siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE nisn='$nisn'");
  if (mysqli_num_rows($siswa) == 0) {
    echo "❌ Siswa tidak ditemukan.";
    exit;
  }
  $s = mysqli_fetch_assoc($siswa);

  // Cek absensi hari ini
  $cekAbsen = mysqli_query($conn, "SELECT * FROM absensi WHERE siswa_id={$s['id']} AND tanggal='$tanggal'");
  
  if (mysqli_num_rows($cekAbsen) == 0) {
    // Belum ada data absen → buat baru langsung di jam_dzuhur
    mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, jam_dzuhur) 
                         VALUES ({$s['id']}, '$tanggal', '$jam')");
    echo "✅ Absen Dzuhur berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam Dzuhur: $jam";
  } else {
    // Sudah ada data absen hari ini
    $row = mysqli_fetch_assoc($cekAbsen);

    if (!empty($row['jam_dzuhur'])) {
      // Kalau jam_dzuhur sudah terisi → kasih info sudah absen
      echo "ℹ️ {$s['nama']} sudah absen Dzuhur hari ini.<br>🕒 Jam Dzuhur: {$row['jam_dzuhur']}";
    } else {
      // Kalau jam_dzuhur kosong → update dengan jam sekarang
      mysqli_query($conn, "UPDATE absensi SET jam_dzuhur='$jam' 
                           WHERE id={$row['id']}");
      echo "✅ Absen Dzuhur berhasil: {$s['nama']} ({$s['kelas']})<br>🕒 Jam Dzuhur: $jam";
    }
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Scan QR Siswa Sholat Dzuhur</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://unpkg.com/html5-qrcode"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
  <h2>Scan QR Code Siswa Sholat Dzuhur</h2>
  <a href="dashboard.php" class="btn btn-secondary mb-3">← Kembali</a>

  <div id="reader" style="width: 100%"></div>
  <div id="result" class="mt-3" style="max-height: 300px; overflow-y: auto;"></div>

  <!-- Suara beep -->
  <audio id="beepSound" src="beep.mp3" preload="auto"></audio>

  <script>
    function onScanSuccess(qrMessage) {
      fetch("jam_dzuhur.php?nisn=" + qrMessage + "&t=" + new Date().getTime())
        .then(res => res.text())
        .then(data => {
          let result = document.getElementById("result");
          let alertDiv = document.createElement("div");
          alertDiv.className = "alert alert-info mb-2";
          alertDiv.innerHTML = data;
          result.appendChild(alertDiv);

          document.getElementById("beepSound").play();
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
