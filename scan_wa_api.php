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
  <title>Scan QR Siswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://unpkg.com/html5-qrcode"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
  <h2>📷 Scan QR Code Siswa Plus Kirim WhatsApp</h2>
  
  <!-- ✅ Tambahan keterangan -->
  <div class="alert alert-warning">
    <strong>ℹ️ Perhatian:</strong> Halaman ini akan mengirimkan notifikasi otomatis melalui layanan API WhatsApp Sidobe. 
    Untuk menghindari spam dan blokir, silakan gunakan <b>WhatsApp Bisnis</b>, dan pastikan Orang Tua/Wali Siswa sudah menyimpan nomor WA yang sedang digunakan ini. Agar dapat menggunakan fitur ini, silahkah seting key di sini. <p><a href="key_wa_sidobe.php" target="_blank" rel="noopener">Buka halaman Key WA Sidobe</a></p>

  </div>
  <!-- ✅ Akhir tambahan -->

  <a href="dashboard.php" class="btn btn-secondary mb-3">← Kembali</a>

  <div id="reader" style="width: 100%"></div>
  <div id="result" class="mt-3" style="max-height: 300px; overflow-y: auto;"></div>

  <!-- Suara beep -->
  <audio id="beepSound" src="beep.mp3" preload="auto"></audio>

  <script>
    function onScanSuccess(qrMessage) {
      fetch("rekam_absen_wa_api.php?nisn=" + qrMessage)
        .then(res => res.json())
        .then(data => {
          let result = document.getElementById("result");
          let alertDiv = document.createElement("div");
          alertDiv.className = "alert alert-info mb-2";
          alertDiv.innerHTML = data.message;

          if (data.wa_link) {
            // Jika ada nomor WA → tampilkan tombol WhatsApp
            alertDiv.innerHTML += `<br><a id="waBtn" href="${data.wa_link}" target="_blank" class="btn btn-success mt-2">📲 Kirim WhatsApp</a>`;
            
            // Klik otomatis setelah 1 detik
            setTimeout(() => {
              document.getElementById("waBtn").click();
            }, 1000);
          } else {
            // Jika no_wa kosong → tampilkan tombol Tambahkan nomor WA ke siswa.php
            alertDiv.innerHTML += `<br><a href="siswa.php" class="btn btn-warning mt-2">➕ Tambahkan Nomor WA</a>`;
          }

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
