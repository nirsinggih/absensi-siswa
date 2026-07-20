<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include "../config.php";
date_default_timezone_set("Asia/Jakarta");

// === Ambil Token WA Sidobe ===
$q = mysqli_query($conn, "SELECT key_wa_sidobe FROM profil_sekolah WHERE id=1");
if (!$q) {
    die("<h3 style='color:red;'>❌ Query gagal:</h3><pre>" . mysqli_error($conn) . "</pre>");
}

$row = mysqli_fetch_assoc($q);
if (!$row || empty($row['key_wa_sidobe'])) {
    die("<h3 style='color:red;'>⚠️ Token WA Sidobe belum diset di tabel profil_sekolah (id=1).</h3>");
}
$token = trim($row['key_wa_sidobe']); // X-Secret-Key

// === Proses Kirim WA ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan'])) {
    $pesan = trim($_POST['pesan']);
    $delay = isset($_POST['delay']) ? intval($_POST['delay']) : rand(2, 5);

   // ===============================
// VALIDASI & KEAMANAN FILE PDF
// ===============================

if (empty($_FILES['pdf']['tmp_name'])) {
    die("<h3 style='color:red;'>⚠️ Harap pilih file PDF untuk dikirim.</h3>");
}

// 1️⃣ Batasi ukuran file (max 5MB)
$maxSize = 5 * 1024 * 1024; 
if ($_FILES['pdf']['size'] > $maxSize) {
    die("<h3 style='color:red;'>❌ Ukuran file terlalu besar (Maks 5MB).</h3>");
}

// 2️⃣ Validasi ekstensi
$ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    die("<h3 style='color:red;'>❌ Hanya file PDF yang diperbolehkan.</h3>");
}

// 3️⃣ Validasi MIME asli (anti rename php.jpg)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['pdf']['tmp_name']);
finfo_close($finfo);

if ($mime !== 'application/pdf') {
    die("<h3 style='color:red;'>❌ File bukan PDF valid.</h3>");
}

// 4️⃣ Cegah nama file mencurigakan
$filename = $_FILES['pdf']['name'];
if (preg_match('/\.php|\.phtml|\.phar|\.php[0-9]?/i', $filename)) {
    die("<h3 style='color:red;'>❌ Nama file tidak valid.</h3>");
}

// ===============================
// PROSES UPLOAD AMAN
// ===============================

$uploadDir = __DIR__ . "/../uploads_wa/";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// GANTI nama file jadi random (jangan pakai nama asli!)
$pdfName = bin2hex(random_bytes(16)) . ".pdf";
$uploadPath = $uploadDir . $pdfName;

if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadPath)) {
    die("<h3 style='color:red;'>❌ Gagal menyimpan file PDF ke server.</h3>");
}

chmod($uploadPath, 0644);

    // Pastikan URL publik untuk dokumen
    $serverUrl = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    $documentUrl = rtrim($serverUrl, '/') . "/../uploads_wa/" . $pdfName;

    // Ambil data siswa
    $siswaQ = mysqli_query($conn, "SELECT nama, kelas, no_wa FROM siswa WHERE no_wa <> ''");
    if (!$siswaQ) {
        die("<h3 style='color:red;'>❌ Query siswa gagal:</h3><pre>" . mysqli_error($conn) . "</pre>");
    }

    ob_implicit_flush(true);
    ob_end_flush();

    $count = 0;
    while ($s = mysqli_fetch_assoc($siswaQ)) {
        $count++;

        $pesanFinal = str_replace(
            ["{nama}", "{kelas}"],
            [$s['nama'], $s['kelas']],
            $pesan
        );

        // Normalisasi nomor
        $nomor = preg_replace('/[^0-9]/', '', $s['no_wa']);
        if (substr($nomor, 0, 1) === '0') {
            $nomor = '+62' . substr($nomor, 1);
        } elseif (substr($nomor, 0, 2) !== '62') {
            $nomor = '+62' . $nomor;
        } else {
            $nomor = '+' . $nomor;
        }

        // === Kirim pesan + dokumen ===
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.sidobe.com/wa/v1/send-message-doc",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "X-Secret-Key: $token",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode([
                "phone" => $nomor,
                "message" => $pesanFinal,
                "document_url" => $documentUrl,
                "document_name" => $pdfName
            ])
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "❌ Gagal kirim ke {$s['nama']} ($nomor): $err<br>";
        } else {
            $data = json_decode($response, true);
            if (!empty($data['is_success']) && $data['data']['status'] === 'SUCCESS') {
                echo "✅ Pesan & Dokumen terkirim ke {$s['nama']} ($nomor)<br>";
            } else {
                echo "⚠️ Gagal kirim ke {$s['nama']} ($nomor): " . htmlspecialchars($response) . "<br>";
            }
        }

        flush();
        sleep($delay);
    }

    echo "<hr><b>Selesai! Total pesan diproses: $count</b>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kirim WA Massal + File PDF (Sidobe API)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .spinner {
      display: inline-block;
      width: 2rem;
      height: 2rem;
      border: 3px solid rgba(0, 0, 0, 0.1);
      border-top: 3px solid #0d6efd;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-right: 10px;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    #loading {
      display: none;
      margin-top: 20px;
      text-align: center;
      color: #0d6efd;
      font-weight: 500;
    }
  </style>
</head>
<body class="container py-4">
  <h2>📎 Kirim WA Massal + Dokumen PDF (Sidobe API)</h2>
  <a href="../modif.php" class="btn btn-secondary mb-3">← Kembali</a>

  <form method="POST" enctype="multipart/form-data" onsubmit="showLoading()">
    <div class="mb-3">
      <label for="pesan" class="form-label">Format Pesan</label>
      <textarea name="pesan" id="pesan" rows="5" class="form-control" placeholder="Yth. Orang Tua/Wali Siswa {nama} dari kelas {kelas}, berikut surat pemberitahuan kegiatan sekolah." required></textarea>
      <div class="form-text">Gunakan <b>{nama}</b> dan <b>{kelas}</b> untuk otomatis diganti.</div>
    </div>

    <div class="mb-3">
      <label for="pdf" class="form-label">Pilih File PDF</label>
      <input type="file" name="pdf" id="pdf" class="form-control" accept="application/pdf" required>
      <div class="form-text">File akan di-upload ke server dan dikirim via URL.</div>
    </div>

    <div class="mb-3">
      <label for="delay" class="form-label">Jeda antar pesan (detik)</label>
      <input type="number" name="delay" id="delay" class="form-control" value="3" min="1" max="30">
    </div>

    <button type="submit" class="btn btn-primary">🚀 Kirim Pesan + Dokumen</button>

    <div id="loading">
      <div class="spinner"></div>
      <span>⏳ Mohon tunggu, sedang mengirim pesan... Jangan tutup halaman ini</span>
    </div>
  </form>

  <script>
    function showLoading() {
      document.getElementById('loading').style.display = 'block';
    }
  </script>
</body>
</html>
