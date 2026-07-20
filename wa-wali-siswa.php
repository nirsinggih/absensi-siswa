<?php
// ====== Konfigurasi dasar ======
include 'config.php'; // koneksi DB

$username = "NISN";
$password = "NISN";

// ====== Ambil nama sekolah ======
$qProfil = mysqli_query($conn, "SELECT nama_sekolah FROM profil_sekolah LIMIT 1");
$profil  = mysqli_fetch_assoc($qProfil);
$nama_sekolah = $profil['nama_sekolah'] ?? "Sekolah";

// ====== Bangun URL domain + direktori satu tingkat sebelum halaman ini ======
$httpsOn = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
$protocol = $httpsOn ? 'https://' : 'http://';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Ambil direktori 1 tingkat sebelum file ini
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$dirPath = rtrim(dirname($requestUri), '/\\') . '/';

$baseUrl = $protocol . $host . $dirPath;

// ====== Susun pesan default ======
$pesan = $baseUrl . "\n\n" .
    "Username: " . $username . "\n" .
    "Password: " . $password . "\n\n" .
    "Mohon izin menginformasikan bahwa kami dari " . $nama_sekolah . " telah menggunakan teknologi absen digital " .
    "yang dapat dipantau secara langsung oleh Bapak/Ibu Orang Tua/Wali Siswa. " .
    "Mohon simpan nomor ini agar kami bisa mengirim informasi dengan lancar.";
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Share Informasi Absensi via WhatsApp</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .card { border-radius: 1rem; }
    .btn-whatsapp { background-color: #25D366; color: #fff; }
    .btn-whatsapp:hover { filter: brightness(0.95); color:#fff; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3">Bagikan Informasi Absensi Digital</h1>

            <div class="mb-3">
              <label class="form-label">Nama Sekolah</label>
              <input type="text" class="form-control mono" value="<?php echo htmlspecialchars($nama_sekolah, ENT_QUOTES); ?>" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">URL yang akan dibagikan</label>
              <input type="text" class="form-control mono" value="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES); ?>" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Pesan WhatsApp (bisa diedit)</label>
              <textarea id="pesan" class="form-control mono" rows="7"><?php echo htmlspecialchars($pesan, ENT_QUOTES); ?></textarea>
            </div>

            <div class="d-grid gap-2">
              <button class="btn btn-whatsapp btn-lg" onclick="kirimWA()">
                Bagikan ke WhatsApp
              </button>
              <p> HARAP GUNAKAN NOMOR WHATSAPP BISNIS UNTUK MENGINDARI BLOKIR/SPAM </p>
              <button class="btn btn-outline-secondary" type="button" onclick="copyPesan()">Salin Pesan</button>
            </div>

            <hr class="my-4">
            <p class="text-muted small mb-0">
              *Halaman ini otomatis menggunakan <strong>domain + direktori satu tingkat sebelum file ini</strong>.<br>
              Contoh: <span class="mono">https://domain.com/absensi-qr/</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
function kirimWA() {
  const pesan = document.getElementById('pesan').value;
  const url = "https://wa.me/?text=" + encodeURIComponent(pesan);
  window.open(url, "_blank");
}

function copyPesan() {
  const txt = document.getElementById('pesan').value;
  navigator.clipboard.writeText(txt).then(() => {
    alert('Pesan disalin ke clipboard.');
  }).catch(() => {
    const ta = document.createElement('textarea');
    ta.value = txt; document.body.appendChild(ta);
    ta.select(); document.execCommand('copy');
    document.body.removeChild(ta);
    alert('Pesan disalin ke clipboard.');
  });
}
</script>
</body>
</html>
