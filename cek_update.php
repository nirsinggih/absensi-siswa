<?php
$currentVersion = "5.3.7";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cek Update Aplikasi</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: #f4f6f9;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background: #fff;
      padding: 20px;
      max-width: 400px;
      width: 90%;
      text-align: center;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    h2 {
      margin-bottom: 15px;
      color: #333;
    }
    p {
      font-size: 16px;
      color: #555;
      margin-bottom: 20px;
    }
    .btn {
      display: inline-block;
      padding: 10px 20px;
      font-size: 16px;
      border-radius: 5px;
      text-decoration: none;
      transition: 0.3s;
      cursor: pointer;
    }
    .btn-update {
      background: #28a745;
      color: #fff;
    }
    .btn-update:hover {
      background: #218838;
    }
    .btn-ok {
      background: #007bff;
      color: #fff;
    }
    .btn-ok:hover {
      background: #0069d9;
    }
    /* Animasi Loading */
    .loading {
      margin-top: 15px;
      font-size: 14px;
      color: #555;
    }
    .spinner {
      border: 3px solid #f3f3f3;
      border-top: 3px solid #28a745;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      animation: spin 1s linear infinite;
      display: inline-block;
      vertical-align: middle;
      margin-right: 8px;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .info {
      margin-top: 15px;
      font-size: 13px;
      color: #777;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Cek Versi Aplikasi</h2>
    <div id="status">
      <div class="loading">
        <span class="spinner"></span> Sedang mengecek update...
      </div>
    </div>
  </div>

  <script>
    const currentVersion = "<?= $currentVersion ?>";
    const statusDiv = document.getElementById("status");

    // Ambil version.json dari GitHub
    fetch("https://raw.githubusercontent.com/nirsinggih/update-absensi-qr/main/version.json")
      .then(res => res.json())
      .then(data => {
        const latestVersion = data.version;
        const updateUrl = data.url;

        if(latestVersion > currentVersion){
          statusDiv.innerHTML = `
            <p>Versi baru tersedia: <b>${latestVersion}</b></p>
            <a id="btnUpdate" class="btn btn-update" href="update_aplikasi.php?file=${encodeURIComponent(updateUrl)}">Update Sekarang</a>
            <div id="loading" class="loading" style="display:none;">
              <span class="spinner"></span> Sedang memproses update...
            </div>
            <div class="info">Pelajari lebih lanjut aplikasi kunjungi www.tasadmin.id</div>
          `;

          const btn = document.getElementById("btnUpdate");
          const loading = document.getElementById("loading");
          btn.addEventListener("click", function(e){
            btn.style.display = "none";
            loading.style.display = "block";
          });
        } else {
          statusDiv.innerHTML = `
            <p>Aplikasi sudah versi terbaru <b>(${currentVersion})</b></p>
            <a class="btn btn-ok" href="dashboard.php">Kembali</a>
          `;
        }
      })
      .catch(err => {
        statusDiv.innerHTML = `<p style="color:red;">Gagal mengecek update!</p>`;
      });
  </script>
</body>
</html>
