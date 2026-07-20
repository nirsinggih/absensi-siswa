<?php
// Cek apakah sedang update (maintenance mode aktif)
if (file_exists(__DIR__ . "/maintenance.flag")) {
    die("<h1>Sedang update, silakan coba beberapa menit lagi...</h1>");
}
include "config.php";

// Ambil data profil sekolah
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_sekolah, logo FROM profil_sekolah LIMIT 1"));
$nama_sekolah = $profil['nama_sekolah'] ?? 'Nama Sekolah';
$logo = $profil['logo'] ?? 'default.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(to right, #4CAF50, #2E7D32);
      margin: 0;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      flex-direction: column;
    }
    .login-container {
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      width: 100%;
      max-width: 400px;
      animation: fadeIn 0.5s ease-in-out;
    }
    .school-header {
      text-align: center;
      margin-bottom: 15px;
    }
    .school-header img {
      max-height: 70px;
      display: block;
      margin: 0 auto 8px;
    }
    .school-header h1 {
      font-size: 18px;
      color: #2E7D32;
      margin: 0;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    .info-box {
      background-color: #e8f5e9;
      border-left: 5px solid #4CAF50;
      padding: 10px;
      margin-bottom: 20px;
      font-size: 14px;
      color: #2E7D32;
      border-radius: 5px;
    }
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }
    button {
      width: 100%;
      padding: 12px;
      background-color: #4CAF50;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 10px;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #45a049;
    }
    .footer-links {
      margin-top: 15px;
      text-align: center;
      font-size: 14px;
      color: #fff;
    }
    .footer-links a {
      color: #ffffff;
      text-decoration: none;
      margin: 0 8px;
      font-weight: bold;
      transition: color 0.3s ease;
    }
    .footer-links a:hover {
      color: #ffeb3b;
    }
    .app-version {
      margin-top: 5px;
      text-align: center;
      font-size: 12px;
      color: #f1f1f1;
    }
    @media (max-width: 480px) {
      .login-container {
        padding: 15px;
      }
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="school-header">
      <img src="uploads/<?php echo htmlspecialchars($logo); ?>" alt="Logo Sekolah">
      <h1><?php echo htmlspecialchars($nama_sekolah); ?></h1>
    </div>

    <h2>Login Admin Absensi QR Code</h2>

    <div class="info-box">
      📌 <strong>Fokus Aplikasi:</strong> Mempercepat dan mempermudah pekerjaan <strong>Tenaga Administrasi</strong> atau yang ditugasi sesuai format <strong>Administrasi Kesiswaan</strong>, serta mendukung <strong>Tupoksi Wali Kelas/Guru Wali</strong> dalam pengelolaan data dan dokumen Asesmen siswa.
    </div>

   <div class="info-box">
      Orang tua / Wali Siswa dapat memantau langsung kehadiran siswa secara realtime. Coba login Username: NISN Password: NISN
    </div>

    <form method="post" action="cek.php">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Masuk</button>
    </form>
  </div>

  <div class="footer-links">
    <a href="tentang.html">Tentang</a> | 
    <a href="https://youtu.be/pxYNBjroloQ">Kontak</a> |
<a href="reset.php">Reset</a> | 
	<a href="http://lynk.id/tasadmin/44rky1qle4w6">Unduh</a>
| 
	<a href="https://wa.me/message/YKNTIHGAFQVGN1">QR Scanner</a>
    <div class="app-version">Versi Aplikasi 5.4.0</div>
  </div>
</body>
</html>
